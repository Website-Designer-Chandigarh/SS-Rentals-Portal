<div>
    @php
        $reportYear = $this->reportYearLabel();
        $yearTxnsCount = count(array_filter($bankTxns, fn ($txn) => str_starts_with((string) ($txn['date'] ?? ''), $reportYear.'-')));
    @endphp

    <div class="grid grid-4 mb-4">
        <div class="card card-sm">
            <div class="kpi-label">Revenue YTD {{ $reportYear }}</div>
            <div style="font-size:20px;font-weight:800;margin-top:4px;color:var(--green)">{{ $this->money($totalRev) }}</div>
        </div>

        <div class="card card-sm">
            <div class="kpi-label">Expenses YTD {{ $reportYear }}</div>
            <div style="font-size:20px;font-weight:800;margin-top:4px;color:var(--orange)">{{ $this->money($totalExp) }}</div>
        </div>

        <div class="card card-sm">
            <div class="kpi-label">Net Profit YTD</div>
            <div style="font-size:20px;font-weight:800;margin-top:4px;color:{{ $totalNet >= 0 ? 'var(--blue)' : 'var(--red)' }}">{{ $this->money($totalNet) }}</div>
        </div>

        <div class="card card-sm">
            <div class="kpi-label">Net Margin</div>
            <div style="font-size:20px;font-weight:800;margin-top:4px">{{ $totalRev > 0 ? number_format(($totalNet / $totalRev) * 100, 1) : 0 }}%</div>
        </div>
    </div>

    <div class="grid grid-4 mb-4">
        <div class="card card-sm">
            <div class="kpi-label">RUC Costs YTD</div>
            <div style="font-size:18px;font-weight:800;margin-top:4px">{{ $this->money($totalRUC) }}</div>
        </div>

        <div class="card card-sm">
            <div class="kpi-label">Repairs & Maintenance</div>
            <div style="font-size:18px;font-weight:800;margin-top:4px">{{ $this->money($totalMaint) }}</div>
        </div>

        <div class="card card-sm">
            <div class="kpi-label">Loan Repayments</div>
            <div style="font-size:18px;font-weight:800;margin-top:4px">{{ $this->money($totalLoans) }}</div>
        </div>

        <div class="card card-sm">
            <div class="kpi-label">Total Transactions</div>
            <div style="font-size:18px;font-weight:800;margin-top:4px">{{ count($bankTxns) > 0 ? $yearTxnsCount.' txns' : 'Estimated' }}</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <span class="card-title">Monthly P&L - {{ count($bankTxns) > 0 ? 'From Bank Transactions' : 'Estimated' }}</span>
        </div>

        <div class="table-wrap" style="border:none;border-radius:0;margin:0 -20px">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Revenue</th>
                        <th>RUC</th>
                        <th>Navman</th>
                        <th>Insurance</th>
                        <th>Flexi</th>
                        <th>Heartland</th>
                        <th>Repairs</th>
                        <th>Other</th>
                        <th>Total Exp</th>
                        <th style="font-weight:800">Net Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pnlData as $row)
                        <tr style="background:{{ ($row['net'] ?? 0) < 0 ? 'rgba(217,43,43,0.04)' : 'transparent' }}">
                            <td class="fw-700">{{ $row['month'] ?? '-' }}</td>
                            <td style="font-weight:700;color:var(--green)">{{ $this->money($row['revenue'] ?? 0) }}</td>
                            <td style="color:var(--orange);font-size:12px">{{ ($row['ruc'] ?? 0) > 0 ? $this->money($row['ruc']) : '-' }}</td>
                            <td style="font-size:12px">{{ ($row['navman_ruc'] ?? 0) > 0 ? $this->money($row['navman_ruc']) : '-' }}</td>
                            <td style="font-size:12px">{{ ($row['insurance'] ?? 0) > 0 ? $this->money($row['insurance']) : '-' }}</td>
                            <td style="font-size:12px">{{ ($row['flexi'] ?? 0) > 0 ? $this->money($row['flexi']) : '-' }}</td>
                            <td style="font-size:12px">{{ ($row['heartland'] ?? 0) > 0 ? $this->money($row['heartland']) : '-' }}</td>
                            <td style="font-size:12px">{{ ($row['repairs'] ?? 0) > 0 ? $this->money($row['repairs']) : '-' }}</td>
                            <td style="font-size:12px;color:var(--text3)">{{ ($row['other'] ?? 0) > 0 ? $this->money($row['other']) : '-' }}</td>
                            <td style="color:var(--orange);font-weight:700">{{ $this->money($row['expenses'] ?? 0) }}</td>
                            <td style="font-weight:800;font-size:15px;color:{{ ($row['net'] ?? 0) >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $this->money($row['net'] ?? 0) }}</td>
                        </tr>
                    @endforeach

                    <tr style="border-top:2px solid var(--border2);background:var(--bg3)">
                        <td style="font-weight:800">TOTAL</td>
                        <td style="font-weight:800;color:var(--green)">{{ $this->money(array_sum(array_map(fn ($row) => (float) ($row['revenue'] ?? 0), $pnlData))) }}</td>
                        <td style="font-weight:700">{{ $this->money(array_sum(array_map(fn ($row) => (float) ($row['ruc'] ?? 0), $pnlData))) }}</td>
                        <td style="font-weight:700">{{ $this->money(array_sum(array_map(fn ($row) => (float) ($row['navman_ruc'] ?? 0), $pnlData))) }}</td>
                        <td style="font-weight:700">{{ $this->money(array_sum(array_map(fn ($row) => (float) ($row['insurance'] ?? 0), $pnlData))) }}</td>
                        <td style="font-weight:700">{{ $this->money(array_sum(array_map(fn ($row) => (float) ($row['flexi'] ?? 0), $pnlData))) }}</td>
                        <td style="font-weight:700">{{ $this->money(array_sum(array_map(fn ($row) => (float) ($row['heartland'] ?? 0), $pnlData))) }}</td>
                        <td style="font-weight:700">{{ $this->money(array_sum(array_map(fn ($row) => (float) ($row['repairs'] ?? 0), $pnlData))) }}</td>
                        <td style="font-weight:700">{{ $this->money(array_sum(array_map(fn ($row) => (float) ($row['other'] ?? 0), $pnlData))) }}</td>
                        <td style="font-weight:800;color:var(--orange)">{{ $this->money(array_sum(array_map(fn ($row) => (float) ($row['expenses'] ?? 0), $pnlData))) }}</td>
                        @php $allNet = array_sum(array_map(fn ($row) => (float) ($row['net'] ?? 0), $pnlData)); @endphp
                        <td style="font-weight:800;font-size:15px;color:{{ $allNet >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $this->money($allNet) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
