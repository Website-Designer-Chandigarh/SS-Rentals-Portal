
            <!-- 'bank' => 'Bank Transactions', -->
<div>
    @php
        $tabs = [
            'pl' => 'P&L Report',
            'vehicles' => 'By Vehicle',
            'customers' => 'By Customer',
            'debtors' => 'Debtors',
        ];
    @endphp

    <div class="flex items-center justify-between mb-4">
        <div class="tabs" style="margin-bottom:0">
            @foreach($tabs as $key => $label)
                <button type="button" class="tab {{ $reportTab === $key ? 'active' : '' }}" wire:click="$set('reportTab','{{ $key }}')">
                    {{ $label }}
                    @if($key === 'bank' && count($bankTxns) > 0)
                        <span style="margin-left:6px;background:var(--red);color:#fff;font-size:9px;font-weight:700;padding:1px 5px;border-radius:8px">{{ count($bankTxns) }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        <div class="flex gap-2">
            <button type="button" class="btn btn-ghost btn-sm" onclick="window.print()">Export PDF</button>
            <button type="button" class="btn btn-ghost btn-sm" onclick="ssRentalsExportReportsExcel()" style="border-color:#217346;color:#217346">Export Excel</button>
        </div>
    </div>

    @if($reportTab === 'pl')
        @include('portal-sections.reports.pl')
    @elseif($reportTab === 'bank')
        @include('portal-sections.reports.bank')
    @elseif($reportTab === 'vehicles')
        @include('portal-sections.reports.vehicles')
    @elseif($reportTab === 'customers')
        @include('portal-sections.reports.customers')
    @elseif($reportTab === 'debtors')
        @include('portal-sections.reports.debtors')
    @endif

    @php
        $debtorExport = [
            'invoices' => array_values(array_filter($invoices, fn ($invoice) => ($invoice['status'] ?? '') === 'overdue')),
            'customers' => array_values(array_filter($customers, fn ($customer) => (float) ($customer['outstanding'] ?? 0) > 0)),
        ];
    @endphp

    <script>
        window.ssRentalsReportsExport = {
            tab: @json($reportTab),
            generatedAt: @json(now()->toDateString()),
            pl: @json($pnlData),
            vehicles: @json($trucks),
            customers: @json($customers),
            debtors: @json($debtorExport),
        };

        window.ssRentalsExportReportsExcel = function () {
            const data = window.ssRentalsReportsExport || {};
            const money = value => Number(value || 0).toFixed(2);
            const rowsForTab = {
                pl: [
                    ['Month', 'Revenue', 'RUC', 'Navman/GPS', 'Insurance', 'Flexi', 'Heartland', 'Repairs', 'Other', 'Total Expenses', 'Net Profit', 'Margin %'],
                    ...(data.pl || []).map(row => [
                        row.month || '',
                        money(row.revenue),
                        money(row.ruc),
                        money(row.navman_ruc),
                        money(row.insurance),
                        money(row.flexi),
                        money(row.heartland),
                        money(row.repairs),
                        money(row.other),
                        money(row.expenses),
                        money(row.net),
                        row.revenue > 0 ? Math.round((Number(row.net || 0) / Number(row.revenue || 0)) * 100) : 0,
                    ]),
                ],
                vehicles: [
                    ['Vehicle', 'Make / Model', 'Status', 'Revenue YTD', 'Maint Cost YTD', 'Gross Profit', 'Margin %', 'Downtime', 'Utilisation %'],
                    ...(data.vehicles || []).map(truck => {
                        const revenue = Number(truck.rev_ytd || 0);
                        const maintenance = Number(truck.maint_ytd || 0);
                        const grossProfit = revenue - maintenance;
                        const utilisation = truck.status === 'on_hire' ? Math.max(0, Math.min(100, Math.round(((150 - Number(truck.downtime || 0)) / 150) * 100))) : 0;

                        return [
                            truck.rego || '',
                            [truck.make || '', truck.model || ''].join(' ').trim(),
                            truck.status || '',
                            money(revenue),
                            money(maintenance),
                            money(grossProfit),
                            revenue > 0 ? Math.round((grossProfit / revenue) * 100) : 0,
                            Number(truck.downtime || 0),
                            utilisation,
                        ];
                    }),
                ],
                customers: [
                    ['Customer', 'Status', 'Weekly Rate', 'YTD Revenue', 'Outstanding', 'Credit Rating', 'Payment Terms'],
                    ...(data.customers || []).filter(customer => customer.status !== 'blacklisted').map(customer => [
                        customer.company || '',
                        customer.status || '',
                        money(Number(customer.weekly_truck || 0) + Number(customer.weekly_trailer || 0)),
                        money(customer.ytd_revenue),
                        money(customer.outstanding),
                        customer.credit_rating || '',
                        customer.payment_terms || '',
                    ]),
                ],
                debtors: [
                    ['Type', 'Customer', 'Reference', 'Due / Terms', 'Amount', 'Status'],
                    ...((data.debtors && data.debtors.invoices) || []).map(invoice => [
                        'Overdue Invoice',
                        invoice.customer || '',
                        invoice.id || '',
                        invoice.due || '',
                        money(invoice.total),
                        invoice.status || '',
                    ]),
                    ...((data.debtors && data.debtors.customers) || []).map(customer => [
                        'Customer Balance',
                        customer.company || '',
                        '',
                        customer.payment_terms || '',
                        money(customer.outstanding),
                        customer.status || '',
                    ]),
                ],
            };

            const rows = rowsForTab[data.tab] || rowsForTab.pl;
            const csv = rows.map(row => row.map(value => {
                const text = String(value ?? '');
                return /[",\r\n]/.test(text) ? '"' + text.replace(/"/g, '""') + '"' : text;
            }).join(',')).join('\r\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'SS_Rentals_' + (data.tab || 'reports') + '_' + (data.generatedAt || new Date().toISOString().slice(0, 10)) + '.csv';
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        };
    </script>
</div>
