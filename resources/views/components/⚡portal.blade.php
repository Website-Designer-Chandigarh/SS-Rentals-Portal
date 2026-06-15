<?php

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Hire;
use App\Models\Invoice;
use App\Models\MaintenanceRecord;
use App\Models\MonthlyRevenue;
use App\Models\PnlDetail;
use App\Models\PortalDocument;
use App\Models\Vehicle;
use App\Models\WeeklyRevenue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use Livewire\Component;

new class extends Component
{
    public string $page = 'dashboard';
    public string $search = '';
    public string $fleetTab = 'trucks';
    public string $customerStatus = 'all';
    public string $hireTab = 'hires';
    public string $invoiceTab = 'all';
    public string $reportTab = 'pl';
    public string $maintenanceTab = 'log';
    public string $documentTab = 'library';
    public ?string $modal = null;
    public ?string $selectedId = null;
    public string $toast = '';
    public bool $mobileMenuOpen = false;
    public int $invoiceStep = 1;
    public string $invoiceType = 'weekly';
    public string $invoicePeriodFrom = '';
    public string $invoicePeriodTo = '';
    public int $quoteStep = 0;
    public ?string $selectedQuoteTemplate = null;

    public array $trucks = [];
    public array $trailers = [];
    public array $customers = [];
    public array $hires = [];
    public array $invoices = [];
    public array $maintenance = [];
    public array $documents = [];
    public array $weekly_revenue = [];
    public array $monthly_revenue = [];
    public array $pnl_detail = [];
    public array $savedQuotes = [];

    public array $vehicleForm = [];
    public array $customerForm = [];
    public array $hireForm = [];
    public array $invoiceForm = [];
    public array $quoteForm = [];
    public array $navmanForm = [];
    public array $settingsForm = [
        'company' => 'SS Rentals Ltd',
        'gst' => '123-456-789',
        'address' => 'Auckland, New Zealand',
        'ruc_rate' => '0.062',
        'invoice_prefix' => 'INV-2026-',
        'payment_terms' => '7',
        'gst_rate' => '15',
    ];

    public function mount(string $section = 'dashboard'): void
    {
        $data = $this->loadPortalData();

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        $this->page = $this->normalizePage($section);
        $this->resetForms();
        $this->savedQuotes = Schema::hasTable('app_settings') ? (AppSetting::query()->find('portal_quotes')?->payload ?: []) : [];
    }

    public function setPage(string $page): void
    {
        $this->redirectRoute($this->routeNameForPage($page), navigate: true);
    }

    private function normalizePage(string $section): string
    {
        return [
            'dashboard' => 'dashboard',
            'dashabord' => 'dashboard',
            'portal' => 'dashboard',
            'fleet' => 'fleet',
            'fleets' => 'fleet',
            'customers' => 'customers',
            'hires' => 'hires',
            'hire-management' => 'hires',
            'invoicing' => 'invoicing',
            'reports' => 'reports',
            'maintenance' => 'maintenance',
            'navman' => 'navman',
            'documents' => 'documents',
            'settings' => 'settings',
        ][$section] ?? 'dashboard';
    }

    private function routeNameForPage(string $page): string
    {
        return [
            'dashboard' => 'portal.dashboard',
            'fleet' => 'portal.fleets',
            'customers' => 'portal.customers',
            'hires' => 'portal.hire-management',
            'invoicing' => 'portal.invoicing',
            'reports' => 'portal.reports',
            'maintenance' => 'portal.maintenance',
            'navman' => 'portal.navman',
            'documents' => 'portal.documents',
            'settings' => 'portal.settings',
        ][$page] ?? 'portal.dashboard';
    }

    public function openMobileMenu(): void
    {
        $this->mobileMenuOpen = true;
    }

    public function closeMobileMenu(): void
    {
        $this->mobileMenuOpen = false;
    }

    public function openModal(string $modal, ?string $id = null): void
    {
        $this->modal = $modal;
        $this->selectedId = $id;

        if ($modal === 'vehicle') {
            $vehicle = $id ? $this->findVehicle($id) : null;
            $this->vehicleForm = $vehicle ? array_merge($this->emptyVehicle(), $vehicle) : $this->emptyVehicle();
            $this->vehicleForm['asset_type'] = $vehicle && str_starts_with($vehicle['id'], 'TR') ? 'trailer' : 'truck';
        }

        if ($modal === 'customer') {
            $customer = $id ? $this->findById($this->customers, $id) : null;
            $this->customerForm = $customer ? array_merge($this->emptyCustomer(), $customer) : $this->emptyCustomer();
        }

        if ($modal === 'hire') {
            $hire = $id ? $this->findById($this->hires, $id) : null;
            $this->hireForm = $hire ? array_merge($this->emptyHire(), $hire) : $this->emptyHire();
        }

        if ($modal === 'invoice') {
            $invoice = $id ? $this->findById($this->invoices, $id) : null;
            $this->invoiceForm = $invoice ? array_merge($this->emptyInvoice(), $invoice) : $this->emptyInvoice();
            $this->invoiceStep = $id ? 3 : 1;
            $this->invoiceType = 'weekly';
            $this->invoicePeriodFrom = now()->subWeek()->toDateString();
            $this->invoicePeriodTo = now()->toDateString();
        }

        if ($modal === 'navman') {
            $truck = $id ? $this->findById($this->trucks, $id) : null;
            $this->navmanForm = [
                'id' => $truck['id'] ?? '',
                'rego' => $truck['rego'] ?? '',
                'weekly_km' => $truck['weekly_km'] ?? 0,
                'ruc_balance' => $truck['ruc_balance'] ?? 0,
                'odometer' => $truck['odometer'] ?? 0,
            ];
        }
    }

    public function closeModal(): void
    {
        $this->modal = null;
        $this->selectedId = null;
        $this->invoiceStep = 1;
    }

    public function setVehicleAssetType(string $type): void
    {
        $this->vehicleForm['asset_type'] = $type === 'trailer' ? 'trailer' : 'truck';
    }

    public function setInvoiceType(string $type): void
    {
        $this->invoiceType = in_array($type, ['daily', 'weekly', 'monthly'], true) ? $type : 'weekly';
    }

    public function nextInvoiceStep(): void
    {
        if ($this->invoiceStep === 1 && blank($this->invoiceForm['hire'] ?? null)) {
            return;
        }

        if ($this->invoiceStep === 2) {
            $this->buildInvoicePreview();
        }

        $this->invoiceStep = min(3, $this->invoiceStep + 1);
    }

    public function previousInvoiceStep(): void
    {
        $this->invoiceStep = max(1, $this->invoiceStep - 1);
    }

    public function buildInvoicePreview(): void
    {
        $hire = $this->findById($this->hires, $this->invoiceForm['hire'] ?? null);
        if (! $hire) {
            return;
        }

        $truck = $this->findById($this->trucks, $hire['truck'] ?? null);
        $days = max(1, \Illuminate\Support\Carbon::parse($this->invoicePeriodFrom)->diffInDays(\Illuminate\Support\Carbon::parse($this->invoicePeriodTo), false));
        $weeks = max(1 / 7, $days / 7);
        $weeklyKm = (float) ($truck['weekly_km'] ?? 0);
        $periodKm = round($weeklyKm * $weeks);
        $truckHire = round((float) ($hire['weekly_truck'] ?? 0) * $weeks, 2);
        $trailerHire = round((float) ($hire['weekly_trailer'] ?? 0) * $weeks, 2);
        $mileage = round($periodKm * (float) ($hire['mileage_rate'] ?? 0), 2);
        $ruc = round($periodKm * (float) ($hire['ruc_rate'] ?? 0), 2);

        $this->invoiceForm = array_merge($this->invoiceForm, [
            'id' => $this->invoiceForm['id'] ?: 'INV-'.str_pad((string) (270 + count($this->invoices)), 4, '0', STR_PAD_LEFT),
            'customer' => $hire['customer'] ?? '',
            'hire' => $hire['id'],
            'date' => now()->toDateString(),
            'due' => now()->addDays(7)->toDateString(),
            'period' => $this->invoicePeriodFrom.' to '.$this->invoicePeriodTo,
            'truck_hire' => $truckHire,
            'trailer_hire' => $trailerHire,
            'mileage' => $mileage,
            'ruc' => $ruc,
            'total' => $truckHire + $trailerHire + $mileage + $ruc + (float) ($this->invoiceForm['damage'] ?? 0) + (float) ($this->invoiceForm['extras'] ?? 0),
            'status' => 'draft',
        ]);
    }

    public function saveVehicle(): void
    {
        $form = $this->vehicleForm;
        $isTrailer = ($form['asset_type'] ?? 'truck') === 'trailer';
        $target = $isTrailer ? 'trailers' : 'trucks';

        if (blank($form['id'])) {
            $form['id'] = $isTrailer ? 'TR'.str_pad((string) (count($this->trailers) + 1), 3, '0', STR_PAD_LEFT) : 'T'.str_pad((string) (count($this->trucks) + 1), 3, '0', STR_PAD_LEFT);
        }

        unset($form['asset_type']);
        $this->{$target} = $this->upsert($this->{$target}, $form);
        $this->persist($target);
        $this->flash('Vehicle saved');
        $this->closeModal();
    }

    public function saveCustomer(): void
    {
        $form = $this->customerForm;
        if (blank($form['id'])) {
            $form['id'] = 'C'.str_pad((string) (count($this->customers) + 1), 3, '0', STR_PAD_LEFT);
        }

        $this->customers = $this->upsert($this->customers, $form);
        $this->persist('customers');
        $this->flash('Customer saved');
        $this->closeModal();
    }

    public function saveHire(): void
    {
        $form = $this->hireForm;
        if (blank($form['id'])) {
            $form['id'] = 'H'.str_pad((string) (count($this->hires) + 1), 3, '0', STR_PAD_LEFT);
        }

        $form['weekly_truck'] = (float) ($form['weekly_truck'] ?? 0);
        $form['weekly_trailer'] = (float) ($form['weekly_trailer'] ?? 0);
        $form['mileage_rate'] = (float) ($form['mileage_rate'] ?? 0);
        $form['ruc_rate'] = (float) ($form['ruc_rate'] ?? 0);
        $form['bond'] = (float) ($form['bond'] ?? 0);

        $this->hires = $this->upsert($this->hires, $form);
        $this->syncHireToFleet($form);
        $this->persist('hires');
        $this->persist('trucks');
        $this->persist('trailers');
        $this->flash('Hire agreement saved');
        $this->closeModal();
    }

    public function saveInvoice(): void
    {
        $form = $this->invoiceForm;
        if (blank($form['id'])) {
            $next = 270 + count($this->invoices);
            $form['id'] = 'INV-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        }

        $form['truck_hire'] = (float) ($form['truck_hire'] ?? 0);
        $form['trailer_hire'] = (float) ($form['trailer_hire'] ?? 0);
        $form['mileage'] = (float) ($form['mileage'] ?? 0);
        $form['ruc'] = (float) ($form['ruc'] ?? 0);
        $form['damage'] = (float) ($form['damage'] ?? 0);
        $form['extras'] = (float) ($form['extras'] ?? 0);
        $form['total'] = $form['truck_hire'] + $form['trailer_hire'] + $form['mileage'] + $form['ruc'] + $form['damage'] + $form['extras'];

        $this->invoices = $this->upsert($this->invoices, $form);
        $this->persist('invoices');
        $this->flash('Invoice saved');
        $this->invoiceStep = 4;
    }

    public function startQuote(?string $templateId = null): void
    {
        $template = $templateId ? $this->quoteTemplate($templateId) : null;
        $this->selectedQuoteTemplate = $templateId;
        $this->quoteForm = $this->emptyQuote($template);
        $this->quoteStep = 2;
    }

    public function backToQuoteTemplates(): void
    {
        $this->quoteStep = 0;
    }

    public function setQuoteCustomerMode(string $mode): void
    {
        $this->quoteForm['customerMode'] = $mode === 'existing' ? 'existing' : 'new';
        $this->quoteForm['useExistingCustomer'] = '';

        if ($this->quoteForm['customerMode'] === 'existing') {
            foreach (['companyName', 'contactName', 'contactPhone', 'contactEmail', 'contactAddress'] as $field) {
                $this->quoteForm[$field] = '';
            }
        }
    }

    public function selectQuoteCustomer(): void
    {
        $customer = $this->findById($this->customers, $this->quoteForm['useExistingCustomer'] ?? null);
        if (! $customer) {
            return;
        }

        $this->quoteForm['companyName'] = $customer['company'] ?? '';
        $this->quoteForm['contactName'] = $customer['contact'] ?? '';
        $this->quoteForm['contactPhone'] = $customer['phone'] ?? '';
        $this->quoteForm['contactEmail'] = $customer['email'] ?? '';
        $this->quoteForm['contactAddress'] = $customer['address'] ?? '';
    }

    public function saveQuote(): void
    {
        if ($this->storeQuote()) {
            $this->flash('Quote saved');
        }
    }

    public function downloadQuotePDF(): void
    {
        if (! $this->storeQuote()) {
            return;
        }

        $this->flash('Quote saved');
        $this->js('setTimeout(() => window.print(), 100)');
    }

    private function storeQuote(): bool
    {
        if (blank($this->quoteForm['companyName'] ?? null)) {
            $this->flash('Enter a company name before saving quote');
            return false;
        }

        if (($this->quoteForm['customerMode'] ?? 'new') === 'new') {
            $existing = collect($this->customers)->first(fn ($customer) => mb_strtolower(trim($customer['company'] ?? '')) === mb_strtolower(trim($this->quoteForm['companyName'] ?? '')));
            if (! $existing) {
                $this->customers = $this->upsert($this->customers, array_merge($this->emptyCustomer(), [
                    'id' => 'C'.str_pad((string) (count($this->customers) + 1), 3, '0', STR_PAD_LEFT),
                    'company' => $this->quoteForm['companyName'] ?? '',
                    'contact' => $this->quoteForm['contactName'] ?? '',
                    'phone' => $this->quoteForm['contactPhone'] ?? '',
                    'email' => $this->quoteForm['contactEmail'] ?? '',
                    'address' => $this->quoteForm['contactAddress'] ?? '',
                    'status' => 'prospect',
                ]));
                $this->persist('customers');
            }
        }

        $id = $this->quoteForm['savedQuoteId'] ?: 'Q-'.now()->timestamp;
        $this->quoteForm['savedQuoteId'] = $id;
        $record = ['id' => $id, 'savedAt' => now()->toIso8601String(), 'form' => $this->quoteForm];
        $this->savedQuotes = array_values(array_filter($this->savedQuotes, fn ($quote) => ($quote['id'] ?? null) !== $id));
        array_unshift($this->savedQuotes, $record);
        $this->persistQuotes();

        return true;
    }

    public function editQuote(string $id): void
    {
        $record = collect($this->savedQuotes)->firstWhere('id', $id);
        if (! $record) {
            return;
        }

        $this->selectedQuoteTemplate = $record['form']['templateId'] ?? null;
        $this->quoteForm = array_merge($this->emptyQuote(null), $record['form'], ['savedQuoteId' => $id]);
        $this->quoteStep = 2;
    }

    public function deleteQuote(string $id): void
    {
        $this->savedQuotes = array_values(array_filter($this->savedQuotes, fn ($quote) => ($quote['id'] ?? null) !== $id));
        $this->persistQuotes();
        $this->flash('Quote deleted');
    }

    public function downloadInvoicePDF(string $invoiceId): void
    {
        $this->redirect(route('invoice.pdf', ['invoice' => $invoiceId]));
    }

    public function generateInvoiceFromHire(string $hireId): void
    {
        $hire = $this->findById($this->hires, $hireId);
        if (! $hire) {
            return;
        }

        $truck = $this->findById($this->trucks, $hire['truck']);
        $km = (float) ($truck['weekly_km'] ?? 0);
        $this->invoiceForm = array_merge($this->emptyInvoice(), [
            'id' => 'INV-'.str_pad((string) (270 + count($this->invoices)), 4, '0', STR_PAD_LEFT),
            'customer' => $hire['customer'],
            'hire' => $hire['id'],
            'date' => now()->toDateString(),
            'due' => now()->addDays(7)->toDateString(),
            'period' => $this->fmt($hire['start']).' - '.$this->fmt($hire['end']),
            'truck_hire' => (float) ($hire['weekly_truck'] ?? 0),
            'trailer_hire' => (float) ($hire['weekly_trailer'] ?? 0),
            'mileage' => $km * (float) ($hire['mileage_rate'] ?? 0),
            'ruc' => $km * (float) ($hire['ruc_rate'] ?? 0),
            'status' => 'draft',
        ]);
        $this->modal = 'invoice';
    }

    public function saveNavman(): void
    {
        foreach ($this->trucks as $i => $truck) {
            if ($truck['id'] === $this->navmanForm['id']) {
                $this->trucks[$i]['weekly_km'] = (float) $this->navmanForm['weekly_km'];
                $this->trucks[$i]['ruc_balance'] = (float) $this->navmanForm['ruc_balance'];
                $this->trucks[$i]['odometer'] = (float) $this->navmanForm['odometer'];
            }
        }

        $this->flash('Navman values updated');
        $this->persist('trucks');
        $this->closeModal();
    }

    public function saveSettings(): void
    {
        $this->persist('settingsForm', 'settings');
        $this->flash('Settings saved');
    }

    public function markPaid(string $invoiceId): void
    {
        foreach ($this->invoices as $i => $invoice) {
            if ($invoice['id'] === $invoiceId) {
                $this->invoices[$i]['status'] = 'paid';
            }
        }

        $this->flash('Invoice marked paid');
        $this->persist('invoices');
    }

    private function loadPortalData(): array
    {
        $defaults = json_decode(file_get_contents(resource_path('data/portal-data.json')), true);
        $defaults['settingsForm'] = $this->settingsForm;

        if (! Schema::hasTable('vehicles')) {
            return $defaults;
        }

        if (Vehicle::query()->doesntExist()) {
            return $defaults;
        }

        $defaults['trucks'] = Vehicle::query()->where('asset_type', 'truck')->orderBy('id')->get()->map(fn (Vehicle $vehicle) => $this->vehicleArray($vehicle))->all();
        $defaults['trailers'] = Vehicle::query()->where('asset_type', 'trailer')->orderBy('id')->get()->map(fn (Vehicle $vehicle) => $this->vehicleArray($vehicle))->all();
        $defaults['customers'] = Customer::query()->orderBy('id')->get()->map(fn (Customer $customer) => $this->customerArray($customer))->all();
        $defaults['hires'] = Hire::query()->orderBy('id')->get()->map(fn (Hire $hire) => $this->hireArray($hire))->all();
        $defaults['invoices'] = Invoice::query()->orderByDesc('date')->orderByDesc('id')->get()->map(fn (Invoice $invoice) => $this->invoiceArray($invoice))->all();
        $defaults['maintenance'] = MaintenanceRecord::query()->orderByDesc('date')->get()->map(fn (MaintenanceRecord $record) => $this->maintenanceArray($record))->all();
        $defaults['documents'] = PortalDocument::query()->orderByDesc('date')->get()->map(fn (PortalDocument $document) => $this->documentArray($document))->all();
        $defaults['weekly_revenue'] = WeeklyRevenue::query()->orderBy('id')->get()->map(fn (WeeklyRevenue $row) => $this->clean($row->toArray()))->all();
        $defaults['monthly_revenue'] = MonthlyRevenue::query()->orderBy('id')->get()->map(fn (MonthlyRevenue $row) => $this->clean($row->toArray()))->all();
        $defaults['pnl_detail'] = PnlDetail::query()->orderBy('month')->get()->map(fn (PnlDetail $row) => $this->clean($row->toArray()))->all();

        $settings = AppSetting::query()->find('portal');
        $defaults['settingsForm'] = array_merge($this->settingsForm, $settings?->payload ?: []);

        return $defaults;
    }

    private function persist(string $property, ?string $key = null): void
    {
        if (! Schema::hasTable('vehicles')) {
            return;
        }

        match ($key ?? $property) {
            'trucks' => $this->persistVehicles($this->trucks, 'truck'),
            'trailers' => $this->persistVehicles($this->trailers, 'trailer'),
            'customers' => collect($this->customers)->each(fn ($row) => Customer::query()->updateOrCreate(['id' => $row['id']], $this->customerRecord($row))),
            'hires' => collect($this->hires)->each(fn ($row) => Hire::query()->updateOrCreate(['id' => $row['id']], $this->hireRecord($row))),
            'invoices' => collect($this->invoices)->each(fn ($row) => Invoice::query()->updateOrCreate(['id' => $row['id']], $this->invoiceRecord($row))),
            'settings' => AppSetting::query()->updateOrCreate(['key' => 'portal'], ['payload' => $this->settingsForm]),
            default => null,
        };
    }

    private function persistQuotes(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        AppSetting::query()->updateOrCreate(['key' => 'portal_quotes'], ['payload' => $this->savedQuotes]);
    }

    private function persistVehicles(array $vehicles, string $assetType): void
    {
        foreach ($vehicles as $row) {
            Vehicle::query()->updateOrCreate(
                ['id' => $row['id']],
                $this->vehicleRecord($row, $assetType),
            );
        }
    }

    private function vehicleArray(Vehicle $vehicle): array
    {
        $row = $this->clean($vehicle->toArray());
        $row['hirer'] = $row['hirer_id'] ?? null;
        unset($row['asset_type'], $row['hirer_id']);

        return $row;
    }

    private function customerArray(Customer $customer): array
    {
        $row = $this->clean($customer->toArray());
        $row['truck'] = $row['truck_id'] ?? null;
        $row['trailer'] = $row['trailer_id'] ?? null;
        unset($row['truck_id'], $row['trailer_id']);

        return $row;
    }

    private function hireArray(Hire $hire): array
    {
        $row = $this->clean($hire->toArray());
        $row['customer'] = $row['customer_id'] ?? null;
        $row['truck'] = $row['truck_id'] ?? null;
        $row['trailer'] = $row['trailer_id'] ?? null;
        unset($row['customer_id'], $row['truck_id'], $row['trailer_id']);

        return $row;
    }

    private function invoiceArray(Invoice $invoice): array
    {
        $row = $this->clean($invoice->toArray());
        $row['customer'] = $row['customer_id'] ?? null;
        $row['hire'] = $row['hire_id'] ?? null;
        unset($row['customer_id'], $row['hire_id']);

        return $row;
    }

    private function maintenanceArray(MaintenanceRecord $record): array
    {
        $row = $this->clean($record->toArray());
        $row['vehicle'] = $row['vehicle_id'] ?? null;
        unset($row['vehicle_id']);

        return $row;
    }

    private function documentArray(PortalDocument $document): array
    {
        $row = $this->clean($document->toArray());
        $row['customer'] = $row['customer_id'] ?? null;
        $row['vehicle'] = $row['vehicle_id'] ?? null;
        unset($row['customer_id'], $row['vehicle_id']);

        return $row;
    }

    private function vehicleRecord(array $row, string $assetType): array
    {
        return array_merge(
            ['asset_type' => $assetType, 'hirer_id' => $row['hirer'] ?? null],
            Arr::only($row, [
                'rego', 'make', 'model', 'year', 'type', 'status', 'odometer', 'value', 'cof_expiry', 'rego_expiry',
                'service_due_km', 'next_service', 'insurance_expiry', 'ruc_balance', 'hire_start', 'weekly_rate',
                'location', 'weekly_km', 'rev_ytd', 'maint_ytd', 'downtime', 'instalment', 'note',
            ]),
        );
    }

    private function customerRecord(array $row): array
    {
        return array_merge(
            ['truck_id' => $row['truck'] ?? null, 'trailer_id' => $row['trailer'] ?? null],
            Arr::only($row, [
                'company', 'director', 'contact', 'phone', 'email', 'address', 'nzbn', 'status', 'weekly_truck', 'weekly_trailer',
                'mileage_rate', 'ruc_rate', 'outstanding', 'payment_terms', 'joined', 'ytd_revenue', 'credit_rating', 'notes',
            ]),
        );
    }

    private function hireRecord(array $row): array
    {
        return array_merge(
            ['customer_id' => $row['customer'] ?? null, 'truck_id' => $row['truck'] ?? null, 'trailer_id' => $row['trailer'] ?: null],
            Arr::only($row, [
                'start', 'end', 'status', 'weekly_truck', 'weekly_trailer', 'mileage_rate', 'ruc_rate', 'bond', 'bond_paid',
                'invoiced_to', 'next_invoice', 'signed', 'insurance_verified', 'checklist_done', 'notes',
            ]),
        );
    }

    private function invoiceRecord(array $row): array
    {
        return array_merge(
            ['customer_id' => $row['customer'] ?: null, 'hire_id' => $row['hire'] ?: null],
            Arr::only($row, [
                'date', 'due', 'period', 'truck_hire', 'trailer_hire', 'mileage', 'ruc', 'damage', 'extras', 'total', 'status', 'xero_id',
            ]),
        );
    }

    private function clean(array $row): array
    {
        return Arr::except($row, ['created_at', 'updated_at']);
    }

    private function resetForms(): void
    {
        $this->vehicleForm = $this->emptyVehicle();
        $this->customerForm = $this->emptyCustomer();
        $this->hireForm = $this->emptyHire();
        $this->invoiceForm = $this->emptyInvoice();
        $this->quoteForm = $this->emptyQuote(null);
    }

    public function quoteTemplates(): array
    {
        return [
            [
                'id' => 'QT001', 'name' => 'Truck & Trailer Rental Quote', 'type' => 'truck_trailer', 'chargeType' => 'weekly',
                'truckDesc' => '2019 Volvo 8x4 Curtainside Truck', 'trailerDesc' => '2017 Domett 5-Axle Curtainside Pull Trailer',
                'weeklyRate' => 2430.58, 'monthlyRate' => 0, 'rucRate' => 0.65, 'mileageRate' => 0, 'duration' => 52, 'durationUnit' => 'weeks',
                'notes' => "All prices are in NZD and exclude GST.\nRUC and mileage charges apply on top of weekly hire rates.\nContract maximum mileage is 130,000 km per annum.\nQuotes exclude GST, fuel, and insurance costs.\nThis is a fully maintained truck and trailer quote.",
            ],
            [
                'id' => 'QT002', 'name' => 'Truck Only Rental Quote', 'type' => 'truck_only', 'chargeType' => 'weekly',
                'truckDesc' => '2019 Volvo 8x4 Curtainside Truck', 'trailerDesc' => '',
                'weeklyRate' => 1716.99, 'monthlyRate' => 0, 'rucRate' => 0.45, 'mileageRate' => 0, 'duration' => 52, 'durationUnit' => 'weeks',
                'notes' => "All prices are in NZD and exclude GST.\nRUC and mileage charges apply on top of weekly hire rates.\nContract maximum mileage is 130,000 km per annum.\nQuotes exclude GST, fuel, and insurance costs.\nThis is a fully maintained truck quote.",
            ],
            [
                'id' => 'QT003', 'name' => 'Truck & Trailer Monthly Quote (4-Axle)', 'type' => 'truck_trailer', 'chargeType' => 'monthly',
                'truckDesc' => '2018 Hino 8x4 Curtainside Truck with Tail Lift', 'trailerDesc' => '2011 Fruehauf 4-Axle Curtainside Pull Trailer',
                'weeklyRate' => 0, 'monthlyRate' => 7830.58, 'rucRate' => 0.71, 'mileageRate' => 0, 'duration' => 12, 'durationUnit' => 'months',
                'notes' => "All prices are in NZD and exclude GST.\nRUC and mileage charges apply on top of monthly hire rates.\nContract maximum mileage is 130,000 km per annum.\nQuotes exclude GST, fuel, and insurance costs.\nThis is a fully maintained truck and trailer quote.",
            ],
            [
                'id' => 'QT004', 'name' => '5-Axle Pull Trailer Lease Quote', 'type' => 'trailer_only', 'chargeType' => 'monthly',
                'truckDesc' => '', 'trailerDesc' => '2022 22-Pallet TMC 5-Axle Curtainside Pull Trailer',
                'weeklyRate' => 0, 'monthlyRate' => 3390.85, 'rucRate' => 0.18, 'mileageRate' => 0, 'duration' => 12, 'durationUnit' => 'months',
                'notes' => "All prices are in NZD and exclude GST.\nTrailer will be fully branded to customer colours.\nRUC and mileage charges apply on top of monthly hire rates.\nContract maximum mileage is 192,000 km per annum.\nQuotes exclude GST and insurance costs.\nThis is a fully maintained trailer quote.",
            ],
            [
                'id' => 'QT005', 'name' => 'Swinglift Lease Quote (Multi-Term)', 'type' => 'truck_trailer', 'chargeType' => 'monthly',
                'truckDesc' => '2022 Volvo 8x4 Sleeper-Cab Tractor Unit', 'trailerDesc' => '2026 Patchell Quad-Axle Swinglift Trailer',
                'weeklyRate' => 0, 'monthlyRate' => 11189.33, 'rucRate' => 0, 'mileageRate' => 0, 'duration' => 12, 'durationUnit' => 'months',
                'notes' => "All prices are in NZD and exclude GST.\nRUC charges will be paid by hirer via DDB.\nMileage charges are included in the monthly lease payment.\nContract maximum mileage is 130,000 km per annum.\nQuotes exclude GST, fuel, and insurance costs.\nThis is a fully maintained truck and trailer lease quote.",
            ],
        ];
    }

    private function quoteTemplate(?string $id): ?array
    {
        return collect($this->quoteTemplates())->firstWhere('id', $id);
    }

    private function emptyQuote(?array $template): array
    {
        return [
            'savedQuoteId' => '',
            'templateId' => $template['id'] ?? null,
            'quoteNumber' => 'Q-'.now()->format('His'),
            'quoteDate' => now()->toDateString(),
            'validDays' => 14,
            'customerMode' => 'new',
            'useExistingCustomer' => '',
            'companyName' => '',
            'contactName' => '',
            'contactPhone' => '',
            'contactEmail' => '',
            'contactAddress' => '',
            'vehicleType' => $template['type'] ?? 'truck_trailer',
            'hireDuration' => $template['duration'] ?? '',
            'hireDurationUnit' => $template['durationUnit'] ?? 'weeks',
            'startDate' => now()->toDateString(),
            'endDate' => '',
            'truckDesc' => $template['truckDesc'] ?? '',
            'trailerDesc' => $template['trailerDesc'] ?? '',
            'chargeType' => $template['chargeType'] ?? 'weekly',
            'dailyRate' => 0,
            'weeklyRate' => $template['weeklyRate'] ?? 0,
            'monthlyRate' => $template['monthlyRate'] ?? 0,
            'rucRate' => $template['rucRate'] ?? 0,
            'mileageRate' => $template['mileageRate'] ?? 0,
            'maxMileage' => '130,000 km p.a. (10,833 km/month)',
            'notes' => $template['notes'] ?? "All prices are in NZD and exclude GST.\nRUC and mileage charges apply on top of hire rates.\nQuotes exclude GST, fuel, and insurance costs.",
        ];
    }

    private function emptyVehicle(): array
    {
        return [
            'asset_type' => 'truck', 'id' => '', 'rego' => '', 'make' => '', 'model' => '', 'year' => date('Y'),
            'type' => '', 'status' => 'available', 'odometer' => 0, 'value' => 0, 'cof_expiry' => now()->addMonths(6)->toDateString(),
            'rego_expiry' => now()->addMonths(6)->toDateString(), 'service_due_km' => 0, 'next_service' => now()->addMonth()->toDateString(),
            'insurance_expiry' => now()->addYear()->toDateString(), 'ruc_balance' => 0, 'hirer' => null, 'hire_start' => null,
            'weekly_rate' => 0, 'location' => 'Auckland - Yard', 'weekly_km' => 0, 'rev_ytd' => 0, 'maint_ytd' => 0,
            'downtime' => 0, 'instalment' => 0, 'note' => '',
        ];
    }

    private function emptyCustomer(): array
    {
        return [
            'id' => '', 'company' => '', 'director' => '', 'contact' => '', 'phone' => '', 'email' => '', 'address' => 'Auckland, NZ',
            'nzbn' => '', 'status' => 'active', 'truck' => null, 'trailer' => null, 'weekly_truck' => 0, 'weekly_trailer' => 0,
            'mileage_rate' => 0.25, 'ruc_rate' => 0.62, 'outstanding' => 0, 'payment_terms' => '7 days',
            'joined' => now()->toDateString(), 'ytd_revenue' => 0, 'credit_rating' => 'B+', 'notes' => '',
        ];
    }

    private function emptyHire(): array
    {
        return [
            'id' => '', 'customer' => '', 'truck' => '', 'trailer' => null, 'start' => now()->toDateString(),
            'end' => now()->addWeek()->toDateString(), 'status' => 'active', 'weekly_truck' => 0, 'weekly_trailer' => 0,
            'mileage_rate' => 0.25, 'ruc_rate' => 0.62, 'bond' => 0, 'bond_paid' => false, 'invoiced_to' => null,
            'next_invoice' => now()->addWeek()->toDateString(), 'signed' => false, 'insurance_verified' => false,
            'checklist_done' => false, 'notes' => '',
        ];
    }

    private function emptyInvoice(): array
    {
        return [
            'id' => '', 'customer' => '', 'hire' => '', 'date' => now()->toDateString(), 'due' => now()->addDays(7)->toDateString(),
            'period' => '', 'truck_hire' => 0, 'trailer_hire' => 0, 'mileage' => 0, 'ruc' => 0, 'damage' => 0,
            'extras' => 0, 'total' => 0, 'status' => 'draft', 'xero_id' => null,
        ];
    }

    private function upsert(array $items, array $item): array
    {
        foreach ($items as $i => $existing) {
            if (($existing['id'] ?? null) === ($item['id'] ?? null)) {
                $items[$i] = array_merge($existing, $item);
                return $items;
            }
        }

        array_unshift($items, $item);
        return $items;
    }

    private function syncHireToFleet(array $hire): void
    {
        foreach ($this->trucks as $i => $truck) {
            if ($truck['id'] === ($hire['truck'] ?? null)) {
                $this->trucks[$i]['status'] = $hire['status'] === 'active' ? 'on_hire' : 'available';
                $this->trucks[$i]['hirer'] = $hire['status'] === 'active' ? $hire['customer'] : null;
                $this->trucks[$i]['weekly_rate'] = (float) ($hire['weekly_truck'] ?? 0);
            }
        }

        foreach ($this->trailers as $i => $trailer) {
            if (($hire['trailer'] ?? null) && $trailer['id'] === $hire['trailer']) {
                $this->trailers[$i]['status'] = $hire['status'] === 'active' ? 'on_hire' : 'available';
                $this->trailers[$i]['hirer'] = $hire['status'] === 'active' ? $hire['customer'] : null;
            }
        }
    }

    private function flash(string $message): void
    {
        $this->toast = $message;
    }

    public function filteredTrucks(): array
    {
        return $this->filterRows($this->trucks, ['rego', 'make', 'model', 'type', 'location']);
    }

    public function filteredTrailers(): array
    {
        return $this->filterRows($this->trailers, ['rego', 'make', 'model', 'location']);
    }

    public function filteredCustomers(): array
    {
        $rows = $this->filterRows($this->customers, ['company', 'contact', 'phone', 'email', 'notes']);

        if ($this->customerStatus !== 'all') {
            $rows = array_values(array_filter($rows, fn ($row) => ($row['status'] ?? '') === $this->customerStatus));
        }

        return $rows;
    }

    public function filteredHires(): array
    {
        $rows = $this->filterRows($this->hires, ['id', 'notes']);

        if ($this->hireTab === 'active') {
            $rows = array_values(array_filter($rows, fn ($row) => ($row['status'] ?? '') === 'active'));
        } elseif ($this->hireTab === 'completed') {
            $rows = array_values(array_filter($rows, fn ($row) => ($row['status'] ?? '') === 'completed'));
        } elseif ($this->hireTab === 'quotes') {
            return [];
        }

        return $rows;
    }

    public function filteredInvoices(): array
    {
        $rows = $this->filterRows($this->invoices, ['id', 'period', 'status']);

        if ($this->invoiceTab !== 'all') {
            $rows = array_values(array_filter($rows, fn ($row) => ($row['status'] ?? '') === $this->invoiceTab));
        }

        return $rows;
    }

    private function filterRows(array $rows, array $fields): array
    {
        $search = mb_strtolower(trim($this->search));
        if ($search === '') {
            return $rows;
        }

        return array_values(array_filter($rows, function ($row) use ($fields, $search) {
            foreach ($fields as $field) {
                if (str_contains(mb_strtolower((string) ($row[$field] ?? '')), $search)) {
                    return true;
                }
            }

            return false;
        }));
    }

    public function allVehicles(): array
    {
        return array_merge($this->trucks, $this->trailers);
    }

    public function activeHires(): array
    {
        return array_values(array_filter($this->hires, fn ($hire) => ($hire['status'] ?? '') === 'active'));
    }

    public function findById(array $items, ?string $id): ?array
    {
        foreach ($items as $item) {
            if (($item['id'] ?? null) === $id) {
                return $item;
            }
        }

        return null;
    }

    public function findVehicle(?string $id): ?array
    {
        return $this->findById($this->trucks, $id) ?: $this->findById($this->trailers, $id);
    }

    public function customerName(?string $id): string
    {
        return $this->findById($this->customers, $id)['company'] ?? ($id ?: '-');
    }

    public function vehicleRego(?string $id): string
    {
        return $this->findVehicle($id)['rego'] ?? ($id ?: '-');
    }

    public function fmt(?string $date): string
    {
        return $date ? date('d M Y', strtotime($date)) : '-';
    }

    public function money(mixed $value): string
    {
        return '$'.number_format((float) $value, 2);
    }

    public function daysDiff(?string $date): int
    {
        return $date ? now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($date)->startOfDay(), false) : 0;
    }

    public function statusLabel(?string $status): string
    {
        return [
            'on_hire' => 'On Hire',
            'in_progress' => 'In Progress',
        ][$status] ?? ucwords(str_replace('_', ' ', (string) $status));
    }

    public function statusClass(?string $status): string
    {
        $map = [
            'on_hire' => 'blue', 'available' => 'green', 'maintenance' => 'orange', 'active' => 'green',
            'overdue' => 'red', 'paid' => 'green', 'sent' => 'blue', 'draft' => 'gray', 'completed' => 'green',
            'scheduled' => 'blue', 'blacklisted' => 'red', 'inactive' => 'gray', 'prospect' => 'orange',
        ];

        return 'badge badge-'.($map[$status] ?? 'gray');
    }

    public function navIcon(string $name, int $size = 18): string
    {
        $paths = [
            'dashboard' => 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
            'fleet' => 'M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z',
            'customers' => 'M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z',
            'hires' => 'M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z',
            'invoice' => 'M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z',
            'reports' => 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z',
            'maintenance' => 'M13.78 15.3L19.78 21.3L21.89 19.14L15.89 13.14L13.78 15.3ZM17.5 10C19.43 10 21 8.43 21 6.5C21 5.85 20.82 5.24 20.5 4.72L17.25 7.97L16.03 6.75L19.28 3.5C18.76 3.18 18.15 3 17.5 3C15.57 3 14 4.57 14 6.5C14 6.94 14.08 7.37 14.22 7.75L3 18.95C2.94 19 2.94 19.08 3 19.14L4.86 21C4.91 21.06 5 21.06 5.05 21L7.5 18.55L9.61 20.67L10.32 19.95L8.2 17.84L9.27 16.77L11.38 18.88L12.1 18.17L10 16.05L16.25 9.78C16.63 9.92 17.06 10 17.5 10Z',
            'navman' => 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
            'documents' => 'M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z',
            'settings' => 'M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z',
            'logout' => 'M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.1 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z',
            'menu' => 'M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z',
        ];

        $path = $paths[$name] ?? $paths['dashboard'];

        return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="'.$path.'"/></svg>';
    }

    public function summary(): array
    {
        $onHire = count(array_filter($this->trucks, fn ($t) => ($t['status'] ?? '') === 'on_hire'));
        $available = count(array_filter($this->trucks, fn ($t) => ($t['status'] ?? '') === 'available'));
        $util = count($this->trucks) ? round(($onHire / count($this->trucks)) * 100) : 0;
        $weekRev = (float) (end($this->weekly_revenue)['amount'] ?? 0);
        $month = end($this->monthly_revenue) ?: ['amount' => 0, 'net' => 0];
        $outstanding = array_sum(array_map(fn ($i) => ($i['status'] ?? '') !== 'paid' ? (float) ($i['total'] ?? 0) : 0, $this->invoices));
        $overdue = array_values(array_filter($this->invoices, fn ($i) => ($i['status'] ?? '') === 'overdue'));
        $weeklyKm = array_sum(array_map(fn ($t) => (float) ($t['weekly_km'] ?? 0), $this->trucks));

        return compact('onHire', 'available', 'util', 'weekRev', 'month', 'outstanding', 'overdue', 'weeklyKm');
    }
};
?>

<div class="app" wire:key="portal-app">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-mark">
                <img src="{{ asset('images/logo.png') }}" alt="SS Rentals" style="width:55px;height:55px;border-radius:8px;object-fit:contain;background:#fff;padding:2px;flex-shrink:0">
                <div>
                    <div class="logo-text" style="color:#1a0533">SS Rentals</div>
                    <div class="logo-sub" style="color:#9b7ab5">Fleet Platform</div>
                </div>
            </div>
        </div>

        @php
            $nav = [
                'main' => [['dashboard','Dashboard','dashboard'],['fleet','Fleet','fleet'],['customers','Customers','customers'],['hires','Hire Management','hires']],
                'finance' => [['invoicing','Invoicing','invoice'],['reports','Financial Reports','reports']],
                'operations' => [['maintenance','Maintenance','maintenance'],['navman','Navman GPS','navman'],['documents','Documents','documents']],
                'system' => [['settings','Settings','settings']],
            ];
            $s = $this->summary();
            $badges = [
                'fleet' => count(array_filter($this->allVehicles(), fn($v) => $this->daysDiff($v['cof_expiry'] ?? null) <= 30 || $this->daysDiff($v['rego_expiry'] ?? null) <= 30)),
                'hires' => count(array_filter($this->activeHires(), fn($h) => $this->daysDiff($h['end'] ?? null) <= 7 && $this->daysDiff($h['end'] ?? null) >= 0)),
                'invoicing' => count($s['overdue']),
                'maintenance' => count(array_filter($maintenance, fn($m) => ($m['status'] ?? '') === 'scheduled' && $this->daysDiff($m['date'] ?? null) <= 30)),
            ];
        @endphp

        @foreach($nav as $section => $items)
            <div class="sidebar-section">
                <div class="sidebar-section-label">{{ $section }}</div>
                @foreach($items as [$id, $label, $icon])
                    <button type="button" class="nav-item {{ in_array($id, ['dashboard', 'fleet', 'customers', 'hires']) ? 'mobile-primary-nav' : 'mobile-secondary-nav' }} {{ $page === $id ? 'active' : '' }}" wire:click="setPage('{{ $id }}')">
                        <span class="nav-icon">{!! $this->navIcon($icon) !!}</span>
                        <span>{{ $label }}</span>
                        @if(($badges[$id] ?? 0) > 0)
                            <span class="nav-badge">{{ $badges[$id] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endforeach

        <button type="button" class="nav-item mobile-menu-nav {{ ! in_array($page, ['dashboard', 'fleet', 'customers', 'hires']) ? 'active' : '' }}" wire:click="openMobileMenu">
            <span class="nav-icon">{!! $this->navIcon('menu') !!}</span>
            <span>Menu</span>
        </button>

        <div class="sidebar-footer">
            <div style="display:flex;align-items:center;gap:10px;padding:0 8px">
                <div class="avatar">SD</div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:600;color:#6A1B9A;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Sukhdeep Singh</div>
                    <div style="font-size:11px;color:var(--text3)">Director / Admin</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
                @csrf
                <button type="submit" class="nav-item logout-nav">
                    <span class="nav-icon">{!! $this->navIcon('logout') !!}</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="mobile-menu-overlay {{ $mobileMenuOpen ? 'open' : '' }}" wire:click.self="closeMobileMenu">
        <nav class="mobile-menu-panel">
            <div class="mobile-menu-header">
                <div>
                    <div class="mobile-menu-title">Menu</div>
                    <div class="mobile-menu-sub">SS Rentals Portal</div>
                </div>
                <button type="button" class="icon-btn" wire:click="closeMobileMenu">×</button>
            </div>
            @foreach($nav as $section => $items)
                <div class="mobile-menu-section">
                    <div class="sidebar-section-label">{{ $section }}</div>
                    @foreach($items as [$id, $label, $icon])
                        <button type="button" class="mobile-menu-item {{ $page === $id ? 'active' : '' }}" wire:click="setPage('{{ $id }}')">
                            <span class="nav-icon">{!! $this->navIcon($icon) !!}</span>
                            <span>{{ $label }}</span>
                            @if(($badges[$id] ?? 0) > 0)
                                <span class="nav-badge">{{ $badges[$id] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endforeach
            <form method="POST" action="{{ route('logout') }}" class="mobile-menu-logout">
                @csrf
                <button type="submit" class="mobile-menu-item logout-nav">
                    <span class="nav-icon">{!! $this->navIcon('logout') !!}</span>
                    <span>Logout</span>
                </button>
            </form>
        </nav>
    </div>

    <div class="main">
        <header class="header">
            <div style="flex:1">
                <div class="header-title">{{ [
                    'dashboard' => 'Dashboard', 'fleet' => 'Fleet Management', 'customers' => 'Customers', 'hires' => 'Hire Management',
                    'invoicing' => 'Invoicing', 'reports' => 'Financial Reports', 'maintenance' => 'Maintenance', 'navman' => 'Navman GPS',
                    'documents' => 'Documents', 'settings' => 'Settings'
                ][$page] ?? 'Dashboard' }}</div>
                <div class="header-sub">{{ now()->format('l, d F Y') }}</div>
            </div>
            <div class="header-actions">
                @if($toast)
                    <span class="badge badge-green">{{ $toast }}</span>
                @endif
                <button type="button" class="btn btn-add btn-sm" wire:click="openModal('hire')">+ New Hire</button>
            </div>
        </header>

        <main class="content">
            @if(in_array($page, ['fleet','customers','hires','documents']))
                <div class="card card-sm mb-4">
                    <input type="search" wire:model.live.debounce.250ms="search" placeholder="Search {{ $page }}...">
                </div>
            @endif

            @if($page === 'dashboard')
                @include('portal-sections.dashboard', ['s' => $s])
            @elseif($page === 'fleet')
                @include('portal-sections.fleet')
            @elseif($page === 'customers')
                @include('portal-sections.customers')
            @elseif($page === 'hires')
                @include('portal-sections.hires')
            @elseif($page === 'invoicing')
                @include('portal-sections.invoicing')
            @elseif($page === 'reports')
                @include('portal-sections.reports')
            @elseif($page === 'maintenance')
                @include('portal-sections.maintenance')
            @elseif($page === 'navman')
                @include('portal-sections.navman')
            @elseif($page === 'documents')
                @include('portal-sections.documents')
            @elseif($page === 'settings')
                @include('portal-sections.settings')
            @endif
        </main>
    </div>

    @include('portal-sections.modals')
</div>
