@php
    $overdueInvoices = array_values(array_filter($invoices, fn ($invoice) => ($invoice['status'] ?? '') === 'overdue'));
    $overdueTotal = array_sum(array_map(fn ($invoice) => (float) ($invoice['total'] ?? 0), $overdueInvoices));
    $customerDebtors = collect($customers)->filter(fn ($customer) => (float) ($customer['outstanding'] ?? 0) > 0)->values();
@endphp

<div>
    @if(count($overdueInvoices) > 0)
        <div class="alert alert-red mb-4">
            <div><strong>{{ $this->money($overdueTotal) }} overdue</strong> - {{ count($overdueInvoices) }} invoice(s) outstanding.</div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <span class="card-title">Overdue Invoices</span>
        </div>

        <div class="table-wrap" style="border:none;border-radius:0;margin:0 -20px">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Invoice</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Days Overdue</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overdueInvoices as $invoice)
                        <tr>
                            <td class="fw-700">{{ $this->customerName($invoice['customer'] ?? null) }}</td>
                            <td style="font-weight:600;font-size:12px">{{ $invoice['id'] ?? '-' }}</td>
                            <td style="color:var(--red);font-weight:600">{{ $this->fmt($invoice['due'] ?? null) }}</td>
                            <td style="font-weight:800">{{ $this->money($invoice['total'] ?? 0) }}</td>
                            <td><span style="color:var(--red);font-weight:700">{{ abs($this->daysDiff($invoice['due'] ?? null)) }} days</span></td>
                            <td><button type="button" class="btn btn-primary btn-sm">Send Reminder</button></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted" style="text-align:center;padding:18px">No overdue invoices.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Outstanding Customer Balances</span>
        </div>

        <div class="table-wrap" style="border:none;border-radius:0;margin:0 -20px">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Terms</th>
                        <th>Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerDebtors as $customer)
                        <tr>
                            <td class="fw-700">{{ $customer['company'] ?? '-' }}</td>
                            <td>{{ $customer['payment_terms'] ?? '7 days' }}</td>
                            <td class="text-red fw-700">{{ $this->money($customer['outstanding'] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-muted" style="text-align:center;padding:18px">No customer balances outstanding.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
