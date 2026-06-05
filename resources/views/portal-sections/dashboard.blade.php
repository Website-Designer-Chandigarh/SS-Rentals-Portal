<div>
    <div class="grid grid-4 mb-4">
        <div class="kpi-card" style="--accent:var(--blue)"><div class="kpi-label">Active Hires</div><div class="kpi-value">{{ $s['onHire'] }}</div><div class="kpi-sub">{{ $s['available'] }} available</div><div class="kpi-trend up">Live fleet status</div></div>
        <div class="kpi-card" style="--accent:var(--green)"><div class="kpi-label">Fleet Utilisation</div><div class="kpi-value">{{ $s['util'] }}%</div><div class="kpi-sub">{{ count($trucks) }} trucks · {{ count($trailers) }} trailers</div><div class="kpi-trend up">On target</div></div>
        <div class="kpi-card" style="--accent:var(--red)"><div class="kpi-label">Weekly Revenue</div><div class="kpi-value">{{ $this->money($s['weekRev']) }}</div><div class="kpi-sub">Week ending 31 May 2026</div><div class="kpi-trend up">From portal.html data</div></div>
        <div class="kpi-card" style="--accent:#8E44AD"><div class="kpi-label">Monthly Revenue</div><div class="kpi-value">{{ $this->money($s['month']['amount']) }}</div><div class="kpi-sub">Net {{ $this->money($s['month']['net']) }}</div><div class="kpi-trend up">May 2026</div></div>
    </div>
    <div class="grid grid-4 mb-4">
        <div class="kpi-card" style="--accent:var(--orange)"><div class="kpi-label">Outstanding Invoices</div><div class="kpi-value">{{ $this->money($s['outstanding']) }}</div><div class="kpi-sub">All unpaid invoices</div></div>
        <div class="kpi-card" style="--accent:var(--red)"><div class="kpi-label">Overdue Payments</div><div class="kpi-value">{{ $this->money(array_sum(array_column($s['overdue'], 'total'))) }}</div><div class="kpi-sub">{{ count($s['overdue']) }} invoices overdue</div></div>
        <div class="kpi-card" style="--accent:var(--blue)"><div class="kpi-label">Weekly Mileage Total</div><div class="kpi-value">{{ number_format($s['weeklyKm']) }} km</div><div class="kpi-sub">All trucks combined</div></div>
        <div class="kpi-card" style="--accent:var(--text3)"><div class="kpi-label">RUC Cost This Week</div><div class="kpi-value">{{ $this->money($s['weeklyKm'] * 0.062) }}</div><div class="kpi-sub">@ $0.062/km avg</div></div>
    </div>
    <div class="grid mb-4" style="grid-template-columns:2fr 1fr">
        <div class="card">
            <div class="card-header"><span class="card-title">Monthly Revenue - 2026</span><span class="badge badge-green">YTD</span></div>
            <div class="chart-wrap">
                <div style="display:flex;align-items:end;gap:10px;height:100%">
                    @foreach($monthly_revenue as $row)
                        @php $h = max(8, min(100, round(($row['amount'] / 125000) * 100))); @endphp
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px">
                            <div title="{{ $this->money($row['amount']) }}" style="width:100%;height:{{ $h }}%;background:rgba(106,27,154,.8);border-radius:5px 5px 0 0"></div>
                            <span class="text-xs text-muted">{{ $row['month'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Fleet Status</span></div>
            @foreach(['on_hire' => 'On Hire', 'available' => 'Available', 'maintenance' => 'Maintenance'] as $key => $label)
                @php $count = count(array_filter($this->allVehicles(), fn($v) => ($v['status'] ?? '') === $key)); @endphp
                <div class="compliance-row"><span class="dot dot-{{ $key === 'available' ? 'green' : ($key === 'on_hire' ? 'orange' : 'red') }}"></span><span style="flex:1">{{ $label }}</span><strong>{{ $count }}</strong></div>
            @endforeach
        </div>
    </div>
    <div class="grid mb-4" style="grid-template-columns:1fr 1fr">
        <div class="card" style="padding:0;overflow:hidden">
            <div class="card-header" style="padding:16px 20px 0"><span class="card-title">Recent Invoices</span><button type="button" class="btn btn-ghost btn-sm" wire:click="setPage('invoicing')">View All</button></div>
            <div class="table-wrap" style="border:none;border-radius:0"><table><thead><tr><th>Invoice</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead><tbody>
                @foreach(array_slice($invoices, 0, 5) as $inv)
                    <tr><td class="fw-600">{{ $inv['id'] }}</td><td>{{ $this->customerName($inv['customer'] ?? null) }}</td><td class="fw-700">{{ $this->money($inv['total']) }}</td><td><span class="{{ $this->statusClass($inv['status']) }}">{{ $this->statusLabel($inv['status']) }}</span></td></tr>
                @endforeach
            </tbody></table></div>
        </div>
        <div class="card" style="padding:0;overflow:hidden">
            <div class="card-header" style="padding:16px 20px 0"><span class="card-title">Active Hires</span><button type="button" class="btn btn-ghost btn-sm" wire:click="setPage('hires')">View All</button></div>
            <div class="table-wrap" style="border:none;border-radius:0"><table><thead><tr><th>Truck</th><th>Customer</th><th>Rate/wk</th><th>End Date</th></tr></thead><tbody>
                @foreach($this->activeHires() as $hire)
                    <tr><td class="fw-600">{{ $this->vehicleRego($hire['truck']) }}</td><td>{{ $this->customerName($hire['customer']) }}</td><td>{{ $this->money(($hire['weekly_truck'] ?? 0) + ($hire['weekly_trailer'] ?? 0)) }}</td><td>{{ $this->fmt($hire['end']) }}</td></tr>
                @endforeach
            </tbody></table></div>
        </div>
    </div>
    <div class="card" style="border-left:3px solid var(--red);background:linear-gradient(135deg,var(--card) 0%,var(--bg3) 100%)">
        <div class="card-header"><span class="card-title">AI Operational Summary - 24 May 2026</span><span class="badge badge-blue">AI Generated</span></div>
        <p style="font-size:13px;color:var(--text2);line-height:1.8"><strong style="color:var(--text)">Fleet:</strong> {{ $s['onHire'] }} trucks currently on active hire. <strong style="color:var(--text)">Revenue Alert:</strong> May revenue is {{ $this->money($s['month']['amount']) }}. <strong style="color:var(--text)">Action:</strong> monitor overdue invoices and high-km hires closely.</p>
    </div>
</div>
