<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class XeroController extends Controller
{
    private const TOKEN_URL = 'https://identity.xero.com/connect/token';
    private const AUTHORIZE_URL = 'https://login.xero.com/identity/connect/authorize';
    private const CONNECTIONS_URL = 'https://api.xero.com/connections';
    private const INVOICES_URL = 'https://api.xero.com/api.xro/2.0/Invoices';

    public function connect(Request $request): RedirectResponse
    {
        if (! config('services.xero.client_id') || ! config('services.xero.client_secret')) {
            return back()->with('xero_error', 'Add XERO_CLIENT_ID and XERO_CLIENT_SECRET to .env first.');
        }

        $state = Str::random(40);
        $request->session()->put('xero_oauth_state', $state);

        return redirect()->away(self::AUTHORIZE_URL.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.xero.client_id'),
            'redirect_uri' => config('services.xero.redirect_uri'),
            'scope' => 'openid profile email accounting.contacts accounting.invoices offline_access',
            'state' => $state,
        ]));
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->string('state')->toString() !== $request->session()->pull('xero_oauth_state')) {
            return redirect()->route('portal.settings')->with('xero_error', 'Xero connection failed: invalid state.');
        }

        if (! $request->filled('code')) {
            return redirect()->route('portal.settings')->with('xero_error', 'Xero connection cancelled.');
        }

        $token = $this->requestToken([
            'grant_type' => 'authorization_code',
            'code' => $request->string('code')->toString(),
            'redirect_uri' => config('services.xero.redirect_uri'),
        ]);

        if (! $token) {
            return redirect()->route('portal.settings')->with('xero_error', 'Xero token exchange failed.');
        }

        $connection = $this->firstConnection($token['access_token']);
        if (! $connection) {
            return redirect()->route('portal.settings')->with('xero_error', 'Xero connected but no organisation was found.');
        }

        $this->saveXeroPayload($this->tokenPayload($token) + [
            'tenant_id' => $connection['tenantId'] ?? null,
            'tenant_name' => $connection['tenantName'] ?? $connection['organisationName'] ?? 'Xero',
            'connected_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('portal.invoicing')->with('xero_status', 'Xero connected.');
    }

    public function sync(): RedirectResponse
    {
        $xero = $this->freshToken($this->xeroPayload() ?: []);
        if (! $xero) {
            return back()->with('xero_error', 'Connect Xero before syncing invoices.');
        }

        $response = Http::withToken($xero['access_token'])
            ->withHeaders(['Xero-tenant-id' => $xero['tenant_id']])
            ->acceptJson()
            ->get(self::INVOICES_URL, [
                'where' => 'Type=="ACCREC"',
                'page' => 1,
                'pageSize' => 100,
            ]);

        if (! $response->successful()) {
            return back()->with('xero_error', 'Xero sync failed: '.$response->body());
        }

        $xeroInvoices = collect($response->json('Invoices', []));
        $updated = 0;

        Invoice::query()->get()->each(function (Invoice $invoice) use ($xeroInvoices, &$updated) {
            $match = $xeroInvoices->first(function (array $xeroInvoice) use ($invoice) {
                return ($xeroInvoice['InvoiceID'] ?? null) === $invoice->xero_id
                    || ($xeroInvoice['InvoiceNumber'] ?? null) === $invoice->xero_id
                    || ($xeroInvoice['Reference'] ?? null) === $invoice->id;
            });

            if (! $match) {
                return;
            }

            $invoice->xero_id = $match['InvoiceID'] ?? $match['InvoiceNumber'] ?? $invoice->xero_id;
            $invoice->status = $this->localStatus($match['Status'] ?? $invoice->status);
            $invoice->save();
            $updated++;
        });

        $xero['last_sync_at'] = now()->toIso8601String();
        $this->saveXeroPayload($xero);

        return back()->with('xero_status', $updated.' invoice(s) synced from Xero.');
    }

    public function pushInvoice(Invoice $invoice): RedirectResponse
    {
        $xero = $this->freshToken($this->xeroPayload() ?: []);
        if (! $xero) {
            return back()->with('xero_error', 'Connect Xero before pushing invoices.');
        }

        if ($invoice->xero_id) {
            return back()->with('xero_status', $invoice->id.' is already linked to Xero.');
        }

        $customer = Customer::query()->find($invoice->customer_id);

        $response = Http::withToken($xero['access_token'])
            ->withHeaders(['Xero-tenant-id' => $xero['tenant_id']])
            ->acceptJson()
            ->post(self::INVOICES_URL, [
                'Invoices' => [[
                    'Type' => 'ACCREC',
                    'Status' => 'DRAFT',
                    'Contact' => ['Name' => $customer?->company ?: 'SS Rentals Customer'],
                    'Date' => optional($invoice->date)->toDateString(),
                    'DueDate' => optional($invoice->due)->toDateString(),
                    'Reference' => $invoice->id,
                    'LineAmountTypes' => 'Exclusive',
                    'LineItems' => $this->lineItems($invoice),
                ]],
            ]);

        if (! $response->successful()) {
            return back()->with('xero_error', 'Could not push '.$invoice->id.' to Xero: '.$response->body());
        }

        $created = $response->json('Invoices.0', []);
        $invoice->xero_id = $created['InvoiceID'] ?? $created['InvoiceNumber'] ?? null;
        $invoice->status = 'draft';
        $invoice->save();

        $xero['last_sync_at'] = now()->toIso8601String();
        $this->saveXeroPayload($xero);

        return back()->with('xero_status', $invoice->id.' pushed to Xero as draft.');
    }

    public function disconnect(): RedirectResponse
    {
        AppSetting::query()->whereKey('xero')->delete();

        return back()->with('xero_status', 'Xero disconnected.');
    }

    private function requestToken(array $form): ?array
    {
        $response = Http::asForm()
            ->withBasicAuth(config('services.xero.client_id'), config('services.xero.client_secret'))
            ->post(self::TOKEN_URL, $form);

        return $response->successful() ? $response->json() : null;
    }

    private function firstConnection(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)->acceptJson()->get(self::CONNECTIONS_URL);

        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    private function freshToken(array $xero): ?array
    {
        if (isset($xero['expires_at']) && Carbon::parse($xero['expires_at'])->isFuture() && ! empty($xero['access_token'])) {
            return $xero;
        }

        if (empty($xero['refresh_token'])) {
            return null;
        }

        $token = $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $xero['refresh_token'],
        ]);

        if (! $token) {
            return null;
        }

        $fresh = array_merge($xero, $this->tokenPayload($token));
        $this->saveXeroPayload($fresh);

        return $fresh;
    }

    private function tokenPayload(array $token): array
    {
        return [
            'access_token' => $token['access_token'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 1800) - 60)->toIso8601String(),
        ];
    }

    private function xeroPayload(): ?array
    {
        return AppSetting::query()->find('xero')?->payload;
    }

    private function saveXeroPayload(array $payload): void
    {
        AppSetting::query()->updateOrCreate(['key' => 'xero'], ['payload' => $payload]);
    }

    private function localStatus(string $xeroStatus): string
    {
        return [
            'DRAFT' => 'draft',
            'SUBMITTED' => 'sent',
            'AUTHORISED' => 'sent',
            'PAID' => 'paid',
            'VOIDED' => 'voided',
        ][strtoupper($xeroStatus)] ?? 'draft';
    }

    private function lineItems(Invoice $invoice): array
    {
        $items = collect([
            ['Truck hire', $invoice->truck_hire],
            ['Trailer hire', $invoice->trailer_hire],
            ['Mileage charges', $invoice->mileage],
            ['RUC charges', $invoice->ruc],
            ['Damage charges', $invoice->damage],
            ['Extras', $invoice->extras],
        ])->filter(fn (array $item) => (float) $item[1] > 0);

        if ($items->isEmpty()) {
            $items = collect([['Hire charges', $invoice->total]]);
        }

        return $items->map(fn (array $item) => [
            'Description' => $item[0].' - '.$invoice->period,
            'Quantity' => 1,
            'UnitAmount' => round((float) $item[1], 2),
            'AccountCode' => config('services.xero.sales_account_code'),
            'TaxType' => config('services.xero.tax_type'),
        ])->values()->all();
    }
}
