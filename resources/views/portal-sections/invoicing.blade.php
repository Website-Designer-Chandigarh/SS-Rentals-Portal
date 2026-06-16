@php
    $allInvoices = $this->filteredInvoices();
    $allInvoiceTotal = array_sum(array_map(fn($i) => (float) ($i['total'] ?? 0), $invoices));
    $paidTotal = array_sum(array_map(fn($i) => ($i['status'] ?? '') === 'paid' ? (float) ($i['total'] ?? 0) : 0, $invoices));
    $outstandingTotal = array_sum(array_map(fn($i) => ($i['status'] ?? '') !== 'paid' ? (float) ($i['total'] ?? 0) : 0, $invoices));
    $overdueTotal = array_sum(array_map(fn($i) => ($i['status'] ?? '') === 'overdue' ? (float) ($i['total'] ?? 0) : 0, $invoices));
    $xeroSynced = count(array_filter($invoices, fn($i) => filled($i['xero_id'] ?? null)));
    $xero = \App\Models\AppSetting::query()->find('xero')?->payload;
    $xeroConnected = filled($xero['tenant_id'] ?? null);
@endphp

<div>
      @if(session('xero_status'))
        <div class="alert alert-green">{{ session('xero_status') }}</div>
    @endif
    @if(session('xero_error'))
        <div class="alert alert-red">{{ session('xero_error') }}</div>
    @endif

    <div class="xero-status-card mb-4">
        <div class="xero-check {{ $xeroConnected ? '' : 'disconnected' }}">{{ $xeroConnected ? '✓' : '!' }}</div>
        <div class="xero-status-copy">
            <div class="fw-700">Xero Integration — {{ $xeroConnected ? 'Connected' : 'Not Connected' }}</div>
            <div class="text-sm text-muted">
                @if($xeroConnected)
                    Organisation: {{ $xero['tenant_name'] ?? 'Xero' }} · Last sync: {{ isset($xero['last_sync_at']) ? \Illuminate\Support\Carbon::parse($xero['last_sync_at'])->format('d M Y h:i A') : 'not synced yet' }} · {{ $xeroSynced }}/{{ count($invoices) }} invoices linked
                @else
                    Connect Xero in Settings to push draft invoices and sync paid/sent statuses.
                @endif
            </div>
        </div>
        <div class="xero-actions">
            @if($xeroConnected)
                <form method="POST" action="{{ route('xero.sync') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">↻ Sync Now</button>
                </form>
                <span class="badge badge-green">Connected</span>
            @else
                <a href="{{ route('xero.connect') }}" class="btn btn-primary btn-sm">Connect Xero</a>
                <span class="badge badge-gray">Disconnected</span>
            @endif
        </div>
    </div>

    <div class="grid grid-4 mb-4">
        <div class="card card-sm"><div class="kpi-label">Total Invoiced (May)</div><div class="invoice-kpi">{{ $this->money($allInvoiceTotal) }}</div></div>
        <div class="card card-sm"><div class="kpi-label">Paid</div><div class="invoice-kpi">{{ $this->money($paidTotal) }}</div></div>
        <div class="card card-sm"><div class="kpi-label">Outstanding</div><div class="invoice-kpi">{{ $this->money($outstandingTotal) }}</div></div>
        <div class="card card-sm"><div class="kpi-label">Overdue</div><div class="invoice-kpi">{{ $this->money($overdueTotal) }}</div></div>
    </div>

    <div class="invoice-toolbar mb-4">
        <div class="tabs" style="margin-bottom:0">
            @foreach(['all' => 'All', 'draft' => 'Draft', 'sent' => 'Sent', 'overdue' => 'Overdue', 'paid' => 'Paid'] as $key => $label)
                <button type="button" class="tab {{ $invoiceTab === $key ? 'active' : '' }}" wire:click="$set('invoiceTab','{{ $key }}')">{{ $label }}</button>
            @endforeach
        </div>
        <button type="button" class="btn btn-add" wire:click="openModal('invoice')">+ Generate Weekly Invoices</button>
    </div>

    @if($invoiceTab === 'overdue' && $overdueTotal > 0)
        <div class="alert alert-red mb-4">
            <div><strong>{{ count(array_filter($invoices, fn($i) => ($i['status'] ?? '') === 'overdue')) }} invoices overdue</strong> — total overdue amount is {{ $this->money($overdueTotal) }}. Follow up directly with overdue customers.</div>
        </div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Period</th>
                    <th>Truck</th>
                    <th>Trailer</th>
                    <th>Mileage</th>
                    <th>RUC</th>
                    <th>Total</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($allInvoices as $inv)
                    <tr class="tr-link">
                        <td class="fw-700">{{ $inv['id'] }}</td>
                        <td>{{ $this->customerName($inv['customer'] ?? null) }}</td>
                        <td class="text-muted">{{ $inv['period'] ?: '—' }}</td>
                        <td>{{ $this->money($inv['truck_hire'] ?? 0) }}</td>
                        <td>{{ $this->money($inv['trailer_hire'] ?? 0) }}</td>
                        <td>{{ $this->money($inv['mileage'] ?? 0) }}</td>
                        <td>{{ $this->money($inv['ruc'] ?? 0) }}</td>
                        <td class="fw-700">{{ $this->money($inv['total'] ?? 0) }}</td>
                        <td class="{{ ($inv['status'] ?? '') === 'overdue' ? 'text-orange' : 'text-muted' }}">{{ $this->fmt($inv['due'] ?? null) }}</td>
                        <td><span class="{{ $this->statusClass($inv['status'] ?? null) }}">{{ $this->statusLabel($inv['status'] ?? null) }}</span></td>
                        <td>
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-ghost btn-sm icon-action" wire:click="openModal('invoice','{{ $inv['id'] }}')" title="View invoice">◉</button>
                                <button type="button" class="btn btn-primary btn-sm" wire:click="downloadInvoicePDF('{{ $inv['id'] }}')">PDF</button>
                                <form action="{{ route('invoice.send', ['invoice' => $inv['id']]) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Send invoice via email" onclick="return confirm('Send invoice {{ $inv[`id`] }} to customer email?')">✉ Email</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
