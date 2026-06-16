@php
    $categoryLabels = [
        'income' => ['Hire Income', 'var(--green)'],
        'income_other' => ['Other Income', 'var(--green)'],
        'ruc' => ['RUC', 'var(--orange)'],
        'insurance' => ['Insurance', 'var(--blue)'],
        'flexi' => ['Flexi Loan', '#8E44AD'],
        'heartland' => ['Heartland Loan', '#9B59B6'],
        'navman' => ['Navman/GPS', '#2980B9'],
        'maintenance' => ['Repairs & Maintenance', 'var(--red)'],
        'fuel' => ['Fuel', '#E67E22'],
        'advertising' => ['Advertising', '#F39C12'],
        'gst_tax' => ['GST / Tax', '#C0392B'],
        'software' => ['Software & Subscriptions', '#7F8C8D'],
        'other_expense' => ['Other Expense', 'var(--text3)'],
    ];

    $categorySummary = collect($bankTxns)
        ->groupBy(fn ($txn) => $txn['category'] ?? 'other_expense')
        ->map(fn ($rows, $key) => [
            'key' => $key,
            'label' => $categoryLabels[$key][0] ?? ucwords(str_replace('_', ' ', $key)),
            'color' => $categoryLabels[$key][1] ?? 'var(--text3)',
            'total' => $rows->sum(fn ($txn) => abs((float) ($txn['amount'] ?? 0))),
            'count' => $rows->count(),
        ])
        ->sortByDesc('total')
        ->values();
@endphp

<div>
    <div class="card card-sm mb-4" style="border-left:3px solid var(--blue)">
        <div class="flex items-center justify-between" style="gap:14px;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <div style="font-weight:700;font-size:13px;margin-bottom:2px">ASB Bank Transactions</div>
                <div class="text-sm text-muted">
                    {{ count($bankTxns) > 0 ? count($bankTxns).' stored transactions' : 'No imported bank transactions are stored in this Livewire portal yet.' }}
                </div>
            </div>
        </div>
    </div>

    @if(count($bankTxns) === 0)
        <div class="card" style="text-align:center;padding:48px">
            <div style="font-weight:700;font-size:18px;margin-bottom:10px">Connect Your ASB Bank Data</div>
            <div style="font-size:13px;color:var(--text2);margin:0 auto 28px;max-width:560px">
                The original portal supports importing ASB CSV, Excel/CSV, PDF statements, Xero, and Google Sheets into browser storage. This Blade version is ready to display those transactions once a server-side import source is connected.
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;max-width:900px;margin:0 auto;text-align:left">
                <div style="padding:16px;background:var(--bg3);border-radius:8px">
                    <div style="font-weight:700;margin-bottom:6px;font-size:13px">Import ASB CSV</div>
                    <div style="font-size:12px;color:var(--text2);line-height:1.7">Use exported ASB statement data to populate the report table.</div>
                </div>
                <div style="padding:16px;background:var(--bg3);border-radius:8px">
                    <div style="font-weight:700;margin-bottom:6px;font-size:13px">Import Excel / CSV</div>
                    <div style="font-size:12px;color:var(--text2);line-height:1.7">Supports a normalized transaction format with date, payee, memo, amount, and category.</div>
                </div>
                <div style="padding:16px;background:var(--bg3);border-radius:8px">
                    <div style="font-weight:700;margin-bottom:6px;font-size:13px">Sync from Sheets</div>
                    <div style="font-size:12px;color:var(--text2);line-height:1.7">A server-side Google Sheets import can hydrate the same `$bankTxns` table.</div>
                </div>
            </div>
        </div>
    @else
        <div class="grid mb-4" style="grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px">
            @foreach($categorySummary as $category)
                <div class="card card-sm" style="border-left:3px solid {{ $category['color'] }}">
                    <div style="font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase;margin-bottom:3px;line-height:1.2">{{ $category['label'] }}</div>
                    <div style="font-size:17px;font-weight:800;color:{{ $category['color'] }}">{{ $this->money($category['total']) }}</div>
                    <div style="font-size:10px;color:var(--text3);margin-top:1px">{{ $category['count'] }} transactions</div>
                </div>
            @endforeach
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payee</th>
                        <th>Memo</th>
                        <th>Category</th>
                        <th>Source</th>
                        <th style="text-align:right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($bankTxns, 0, 300) as $txn)
                        @php
                            $category = $txn['category'] ?? 'other_expense';
                            $amount = (float) ($txn['amount'] ?? 0);
                            $label = $categoryLabels[$category][0] ?? ucwords(str_replace('_', ' ', $category));
                            $color = $categoryLabels[$category][1] ?? 'var(--text3)';
                        @endphp
                        <tr>
                            <td style="font-size:12px;white-space:nowrap;color:var(--text2)">{{ $this->fmt($txn['date'] ?? null) }}</td>
                            <td style="font-weight:600;font-size:13px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $txn['payee'] ?? '-' }}</td>
                            <td style="font-size:11px;color:var(--text2);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $txn['memo'] ?? '-' }}</td>
                            <td><span style="font-size:10px;font-weight:700;color:{{ $color }};background:var(--bg3);padding:2px 7px;border-radius:4px;white-space:nowrap">{{ $label }}</span></td>
                            <td style="font-size:10px;color:var(--text3)">{{ $txn['source'] ?? 'csv' }}</td>
                            <td style="text-align:right;font-weight:700;color:{{ $amount > 0 ? 'var(--green)' : 'var(--red)' }};white-space:nowrap">{{ $amount > 0 ? '+' : '' }}{{ $this->money(abs($amount)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(count($bankTxns) > 300)
                <div style="text-align:center;padding:10px;font-size:12px;color:var(--text3)">Showing 300 of {{ count($bankTxns) }} transactions</div>
            @endif
        </div>
    @endif
</div>
