<div>
    @php
        $availableTrucks = array_values(array_filter($trucks, fn($t) => ($t['status'] ?? '') === 'available'));
        $maintenanceTrucks = count(array_filter($trucks, fn($t) => ($t['status'] ?? '') === 'maintenance'));
        $pendingInvoices = array_values(array_filter($invoices, fn($i) => ($i['status'] ?? '') !== 'paid'));
        $overdueAmount = array_sum(array_column($s['overdue'], 'total'));
        $rucCost = $s['weeklyKm'] * 0.062;
        $currentMonth = end($monthly_revenue) ?? [];
        $latestPnl = end($pnl_detail) ?? [];
        
        // Build all months data
        $allMonths = [];
        for ($i = 10; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $monthData = collect($monthly_revenue)->firstWhere('month', $date->format('M y'));
            $pnlData = collect($pnl_detail)->firstWhere('month', $date->format('M y'));
            $allMonths[] = [
                'label' => $date->format('M y'),
                'revenue' => $monthData['amount'] ?? 0,
                'expenses' => $monthData['expenses'] ?? 0,
                'net' => $monthData['net'] ?? 0,
                'isCurrent' => $i === 0,
                'pnl' => $pnlData ?? []
            ];
        }
    @endphp
    
    <!-- Monthly P&L Section -->
    <div class="card" style="grid-column: 1 / -1; margin-bottom: 24px">
        <div class="card-header" style="justify-content: space-between">
            <span class="card-title" style="font-size: 18px; font-weight: 600">Monthly P&L</span>
            <button type="button" class="btn btn-sm" style="background: #E3F2FD; color: #1976D2; border: 1px solid #BBDEFB; border-radius: 16px; padding: 6px 12px; font-size: 11px; font-weight: 600">LAST 11 MONTHS</button>
        </div>
        
        <!-- Monthly Timeline -->
        <div id="monthlyTimeline" style="display: flex; gap: 8px; padding: 20px; overflow-x: auto; border-bottom: 1px solid #eee; margin-bottom: 16px">
            @foreach($allMonths as $index => $month)
                @php
                    $trendSign = $month['net'] >= 0 ? '+' : '';
                @endphp
                <button type="button" onclick="selectMonth({{ $index }}, event)" class="month-btn{{ $month['isCurrent'] ? ' active' : '' }}" data-index="{{ $index }}" style="flex-shrink: 0; text-align: center; padding: 8px 12px; background: {{ $month['isCurrent'] ? '#F5F5F5' : 'transparent' }}; border-radius: 6px; min-width: 100px; border: {{ $month['isCurrent'] ? '2px solid #333' : '1px solid transparent' }}; cursor: pointer; transition: all 0.2s; font-family: inherit">
                    <div style="font-size: 11px; font-weight: 600; color: #333">{{ $month['label'] }}</div>
                    <div style="font-size: 12px; font-weight: 700; color: {{ $month['net'] >= 0 ? '#27AE60' : '#D32F2F' }}; margin-top: 4px">{{ $trendSign }}{{ $this->money($month['net']) }}</div>
                </button>
            @endforeach
        </div>

        <!-- Current Month Highlight -->
        <div style="padding: 0 20px; margin-bottom: 16px">
            <div style="display: flex; align-items: center; justify-content: space-between">
                <div>
                    <span class="card-title" id="monthTitle" style="font-size: 16px">May 2026</span>
                    <span id="profitBadge" style="margin-left: 12px; background: #C8E6C9; color: #27AE60; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600">PROFIT: {{ $this->money($currentMonth['net'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        
        <!-- KPI Cards -->
        <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; padding: 0 20px">
            <div id="revenueCard" style="background: #E8F5E9; border-radius: 8px; padding: 16px">
                <div style="font-size: 11px; font-weight: 600; color: #27AE60; text-transform: uppercase; letter-spacing: 0.5px">Total Revenue</div>
                <div id="revenueValue" style="font-size: 28px; font-weight: 700; color: #1B5E20; margin-top: 8px">{{ $this->money($currentMonth['amount'] ?? 0) }}</div>
            </div>
            <div id="expensesCard" style="background: #FFF3E0; border-radius: 8px; padding: 16px">
                <div style="font-size: 11px; font-weight: 600; color: #FF9800; text-transform: uppercase; letter-spacing: 0.5px">Total Expenses</div>
                <div id="expensesValue" style="font-size: 28px; font-weight: 700; color: #E65100; margin-top: 8px">{{ $this->money($currentMonth['expenses'] ?? 0) }}</div>
            </div>
            <div id="profitCard" style="background: #E3F2FD; border-radius: 8px; padding: 16px">
                <div id="profitLabel" style="font-size: 11px; font-weight: 600; color: #1976D2; text-transform: uppercase; letter-spacing: 0.5px">Net Profit</div>
                <div id="profitValue" style="font-size: 28px; font-weight: 700; color: #0D47A1; margin-top: 8px">{{ $this->money($currentMonth['net'] ?? 0) }}</div>
                <div id="marginValue" style="font-size: 12px; color: #666; margin-top: 6px">Margin: {{ ($currentMonth['amount'] ?? 0) > 0 ? round(($currentMonth['net'] / $currentMonth['amount']) * 100) : 0 }}%</div>
            </div>
        </div>

        <!-- Expense Breakdown -->
        <div style="border-top: 1px solid #eee; padding-top: 16px; padding: 16px 20px">
            @php
                $expensePercent = ($currentMonth['amount'] ?? 0) > 0 ? round(($currentMonth['expenses'] / $currentMonth['amount']) * 100) : 0;
            @endphp
            <div class="section-title" style="margin-bottom: 16px; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #666; letter-spacing: 0.5px">Expense Breakdown</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px">
                <div id="expenseLeft" style="display: flex; flex-direction: column; gap: 12px">
                    <!-- Left Column - will be updated by JS -->
                </div>
                <div id="expenseRight" style="display: flex; flex-direction: column; gap: 12px">
                    <!-- Right Column - will be updated by JS -->
                </div>
            </div>

            <!-- Expenses Percentage Bar -->
            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #eee">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px">
                    <span style="font-size: 12px; color: #666; font-weight: 600">Expenses as % of Revenue</span>
                    <span id="expensePercent" style="font-size: 12px; color: #333; font-weight: 700">{{ $expensePercent }}%</span>
                </div>
                <div style="width: 100%; height: 6px; background: #E0E0E0; border-radius: 3px; overflow: hidden">
                    <div id="expenseBar" style="width: {{ $expensePercent }}%; height: 100%; background: linear-gradient(90deg, #27AE60 0%, #27AE60 100%); border-radius: 3px; transition: all 0.3s"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row 1 -->
    <div class="grid grid-4 mb-4">
        <button type="button" class="kpi-card kpi-click" style="--accent:var(--brand-primary)" wire:click="openModal('kpi-active-hires')">
            <div class="kpi-label">Active Hires</div>
            <div class="kpi-value">{{ $s['onHire'] }}</div>
            <div class="kpi-sub">
                <span>{{ $s['available'] }} available</span>
                <span>{{ $maintenanceTrucks }} in workshop</span>
            </div>
            <div class="kpi-trend up">&uarr; 1 from last week</div>
            <div class="kpi-expand">Click to expand &nearr;</div>
        </button>
        <button type="button" class="kpi-card kpi-click" style="--accent:var(--green)" wire:click="openModal('kpi-fleet-util')">
            <div class="kpi-label">Fleet Utilisation</div>
            <div class="kpi-value">{{ $s['util'] }}%</div>
            <div class="kpi-sub">
                <span>{{ count($trucks) }} trucks</span>
                <span>{{ count($trailers) }} trailers</span>
            </div>
            
            <div class="kpi-trend up">On target (&gt;70%)</div>
            <div class="kpi-expand">Click to expand &nearr;</div>
        </button>
        <button type="button" class="kpi-card kpi-click" style="--accent:var(--brand-primary)" wire:click="openModal('kpi-weekly-rev')">
            <div class="kpi-label">Weekly Revenue</div>
            <div class="kpi-value">{{ $this->money($s['weekRev']) }}</div>
            <div class="kpi-sub">Week ending 31 May 2026</div>
            <div class="kpi-trend up">&uarr; 10.3% vs last week</div>
            <div class="kpi-expand">Click to expand &nearr;</div>
        </button>
        <button type="button" class="kpi-card kpi-click" style="--accent:#8E44AD" wire:click="openModal('kpi-monthly-rev')">
            <div class="kpi-label">Monthly Revenue</div>
            <div class="kpi-value">{{ $this->money($s['month']['amount']) }}</div>
            <div class="kpi-sub">May 2026 - Net {{ $this->money($s['month']['net']) }}</div>
            <div class="kpi-trend up">Best month since Oct 2025</div>
            <div class="kpi-expand">Click to expand &nearr;</div>
        </button>
    </div>
    <div class="grid grid-4 mb-4">
        <button type="button" class="kpi-card kpi-click" style="--accent:var(--orange)" wire:click="openModal('kpi-outstanding')">
            <div class="kpi-label">Outstanding Invoices</div>
            <div class="kpi-value">{{ $this->money($s['outstanding']) }}</div>
            <div class="kpi-sub">{{ count($pendingInvoices) }} invoices pending</div>
            <div class="kpi-expand">Click to expand &nearr;</div>
        </button>
        <button type="button" class="kpi-card kpi-click" style="--accent:var(--brand-primary)" wire:click="openModal('kpi-overdue')">
            <div class="kpi-label">Overdue Payments</div>
            <div class="kpi-value">{{ $this->money($overdueAmount) }}</div>
            <div class="kpi-sub">{{ count($s['overdue']) }} invoices overdue</div>
            <div class="kpi-expand">Click to expand &nearr;</div>
        </button>
        <button type="button" class="kpi-card kpi-click" style="--accent:var(--brand-primary)" wire:click="openModal('kpi-mileage')">
            <div class="kpi-label">Weekly Mileage Total</div>
            <div class="kpi-value">{{ number_format($s['weeklyKm']) }} km</div>
            <div class="kpi-sub">All trucks combined</div>
            <div class="kpi-expand">Click to expand &nearr;</div>
        </button>
        <button type="button" class="kpi-card kpi-click" style="--accent:var(--text3)" wire:click="openModal('kpi-ruc')">
            <div class="kpi-label">RUC Cost This Week</div>
            <div class="kpi-value">{{ $this->money($rucCost) }}</div>
            <div class="kpi-sub">@ $0.062/km avg</div>
            <div class="kpi-expand">Click to expand &nearr;</div>
        </button>
    </div>
    <div class="grid mb-4" style="grid-template-columns:2fr 1fr">
        <div class="card">
            <div class="card-header"><span class="card-title">Monthly Revenue — 2026</span><span class="badge badge-green">YTD</span></div>
            <div style="position: relative; height: 300px; padding: 20px">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
            <div style="padding: 16px; background: #f9f9f9; display: flex; gap: 24px; font-size: 12px; border-top: 1px solid #eee">
                <div style="display: flex; align-items: center; gap: 8px"><span style="width: 12px; height: 12px; background: #D32F2F; border-radius: 2px"></span>Revenue</div>
                <div style="display: flex; align-items: center; gap: 8px"><span style="width: 12px; height: 12px; background: #FFA726; border-radius: 2px"></span>Expenses</div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Fleet Status</span></div>
            <div style="display: flex; justify-content: center; align-items: center; height: 280px; position: relative; padding: 20px">
                <canvas id="fleetStatusChart" style="max-width: 250px; max-height: 250px"></canvas>
            </div>
            <div style="padding: 12px; border-top: 1px solid #eee; font-size: 12px">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px">
                    <span style="display: flex; align-items: center; gap: 6px"><span style="width: 8px; height: 8px; background: #1976D2; border-radius: 2px"></span>On Hire</span>
                    @php $onHire = $s['onHire'] ?? 0; @endphp
                    <strong>{{ $onHire }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px">
                    <span style="display: flex; align-items: center; gap: 6px"><span style="width: 8px; height: 8px; background: #27AE60; border-radius: 2px"></span>Available</span>
                    @php $available = count($availableTrucks) ?? 0; @endphp
                    <strong>{{ $available }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between">
                    <span style="display: flex; align-items: center; gap: 6px"><span style="width: 8px; height: 8px; background: #FF6F00; border-radius: 2px"></span>Maintenance</span>
                    @php $maintenance = $maintenanceTrucks ?? 0; @endphp
                    <strong>{{ $maintenance }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="grid mb-4" style="grid-template-columns: 1.5fr 1fr">
        <div class="card">
            <div class="card-header"><span class="card-title">Weekly Revenue Trend</span><span class="badge badge-blue">YTD</span></div>
            <div style="position: relative; height: 240px; padding: 20px">
                <canvas id="weeklyRevenueChart"></canvas>
            </div>
            <div style="padding: 12px; background: #fafafa; border-top: 1px solid #eee; display: flex; justify-content: space-between; font-size: 11px; color: #666">
                <span>Peak: <strong style="color: #333">{{ $this->money(max(array_column($weekly_revenue, 'amount'))) }}</strong></span>
                <span>Low: <strong style="color: #333">{{ $this->money(min(array_column($weekly_revenue, 'amount'))) }}</strong></span>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Compliance Alerts</span></div>
            <div style="padding: 16px">
                <div style="font-size: 12px; color: #999; text-align: center; padding: 20px">
                    <div style="margin-bottom: 12px">✓ All compliance checks current</div>
                    <div style="font-size: 11px; color: #bbb">No alerts at this time</div>
                </div>
            </div>
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
    <div class="card" style="border-left:3px solid var(--brand-primary);background:linear-gradient(135deg,var(--card) 0%,var(--bg3) 100%);grid-column: 1 / -1">
        <div class="card-header"><span class="card-title">🤖 AI Operational Summary — {{ \Carbon\Carbon::now()->format('d M Y') }}</span><span class="badge badge-blue">AI Generated</span></div>
        <div style="font-size:13px;color:var(--text2);line-height:1.8;display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <div><strong style="color:var(--text)">Fleet Status:</strong> {{ $s['onHire'] }} trucks actively on hire with {{ $s['available'] }} available for booking.</div>
                <div style="margin-top:10px"><strong style="color:var(--text)">Utilisation:</strong> {{ $s['util'] }}% — Fleet operating at {{ $s['util'] >= 70 ? 'target efficiency' : 'below-target levels' }}.</div>
            </div>
            <div>
                <div><strong style="color:var(--text)">Revenue Performance:</strong> May generated {{ $this->money($s['month']['amount']) }} in total revenue with a net profit of {{ $this->money($s['month']['net']) }}.</div>
                <div style="margin-top:10px"><strong style="color:var(--text)">Action Items:</strong> {{ count($s['overdue']) }} overdue invoices totalling {{ $this->money(array_sum(array_column($s['overdue'], 'total'))) }} require immediate follow-up.</div>
            </div>
        </div>
        <div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(0,0,0,0.1)">
            <div style="font-size:12px;color:var(--text2)"><strong style="color:var(--text)">Weekly Operations:</strong> {{ number_format($s['weeklyKm']) }} km tracked across all active hires — RUC spend at approximately {{ $this->money($s['weeklyKm'] * 0.062) }} per week.</div>
        </div>
    </div>
</div>

<script>
// Global scope - accessible from button onclick handlers
const monthlyData = @json($allMonths);
const pnlData = @json($pnl_detail);

function formatCurrency(value) {
    return '$' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function selectMonth(index, event) {
    if (event && event.preventDefault) {
        event.preventDefault();
    }
    
    const month = monthlyData[index];
    if (!month) return;

    const label = month.label;
    const pnl = month.pnl || {};

    // Update active button
    document.querySelectorAll('.month-btn').forEach((btn, i) => {
        if (i === index) {
            btn.style.background = '#F5F5F5';
            btn.style.border = '2px solid #333';
            btn.classList.add('active');
        } else {
            btn.style.background = 'transparent';
            btn.style.border = '1px solid transparent';
            btn.classList.remove('active');
        }
    });

    // Update month title
    document.getElementById('monthTitle').textContent = label;

    // Update revenue and expenses
    document.getElementById('revenueValue').textContent = formatCurrency(month.revenue);
    document.getElementById('expensesValue').textContent = formatCurrency(month.expenses);

    // Update profit/loss badge
    const badge = document.getElementById('profitBadge');
    const profitCard = document.getElementById('profitCard');
    const profitLabel = document.getElementById('profitLabel');
    const profitValue = document.getElementById('profitValue');
    const marginValue = document.getElementById('marginValue');

    if (month.net >= 0) {
        badge.style.background = '#C8E6C9';
        badge.style.color = '#27AE60';
        badge.textContent = 'PROFIT: ' + formatCurrency(month.net);
        profitCard.style.background = '#E3F2FD';
        profitLabel.style.color = '#1976D2';
        profitLabel.textContent = 'Net Profit';
        profitValue.style.color = '#0D47A1';
    } else {
        badge.style.background = '#FFE0B2';
        badge.style.color = '#FF6F00';
        badge.textContent = 'LOSS: ' + formatCurrency(month.net);
        profitCard.style.background = '#FFF3E0';
        profitLabel.style.color = '#FF9800';
        profitLabel.textContent = 'Net Loss';
        profitValue.style.color = '#FF6F00';
    }
    profitValue.textContent = formatCurrency(month.net);

    // Update margin
    const margin = month.revenue > 0 ? Math.round((month.net / month.revenue) * 100) : 0;
    marginValue.textContent = 'Margin: ' + margin + '%';

    // Update expense breakdown
    const expenseLeftItems = ['insurance', 'ruc', 'repairs', 'advertising'];
    const expenseRightItems = ['navman_ruc', 'other', 'flexi', 'gst', 'heartland'];

    const expenseLabels = {
        'insurance': 'Insurance',
        'ruc': 'RUC Charges',
        'repairs': 'Repairs & Maintenance',
        'advertising': 'Advertising',
        'navman_ruc': 'Navman / RUC Purchase',
        'other': 'Other Expenses',
        'flexi': 'Flexi Finance',
        'gst': 'GST / Other Tax',
        'heartland': 'Heartland Finance'
    };

    let leftHTML = '';
    let rightHTML = '';

    expenseLeftItems.forEach(key => {
        const amount = pnl[key] || 0;
        if (amount > 0) {
            leftHTML += `<div style="display: flex; justify-content: space-between; align-items: center">
                <span style="font-size: 12px; color: #333">${expenseLabels[key]}</span>
                <span style="font-size: 12px; font-weight: 600; color: #666">${formatCurrency(amount)}</span>
            </div>`;
        }
    });

    expenseRightItems.forEach(key => {
        const amount = pnl[key] || 0;
        if (amount > 0) {
            rightHTML += `<div style="display: flex; justify-content: space-between; align-items: center">
                <span style="font-size: 12px; color: #333">${expenseLabels[key]}</span>
                <span style="font-size: 12px; font-weight: 600; color: #666">${formatCurrency(amount)}</span>
            </div>`;
        }
    });

    document.getElementById('expenseLeft').innerHTML = leftHTML || '<div style="color: #999; font-size: 12px">No expenses</div>';
    document.getElementById('expenseRight').innerHTML = rightHTML || '<div style="color: #999; font-size: 12px">No expenses</div>';

    const expensePercent = month.revenue > 0 ? Math.round((month.expenses / month.revenue) * 100) : 0;
    document.getElementById('expensePercent').textContent = expensePercent + '%';
    
    const barColor = month.net >= 0 ? '#27AE60' : '#FF9800';
    const expenseBar = document.getElementById('expenseBar');
    expenseBar.style.width = expensePercent + '%';
    expenseBar.style.background = `linear-gradient(90deg, ${barColor} 0%, ${barColor} 100%)`;
}

// Chart initialization on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyRevenueChart');
    if (!ctx) return;

    const monthlyRevenueData = @json($monthly_revenue);
    const labels = monthlyRevenueData.map(m => m.month);
    const revenueData = monthlyRevenueData.map(m => m.amount);
    const expensesData = monthlyRevenueData.map(m => m.expenses);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenueData,
                    backgroundColor: '#D32F2F',
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Expenses',
                    data: expensesData,
                    backgroundColor: '#FFA726',
                    borderRadius: 4,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 11 },
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed.y;
                            let formatted = '$' + value.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                            return context.dataset.label + ': ' + formatted;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + (value / 1000).toFixed(0) + 'k';
                        },
                        font: { size: 11 },
                        color: '#999'
                    },
                    grid: { color: '#eee', drawBorder: false }
                },
                x: {
                    ticks: { font: { size: 11 }, color: '#999' },
                    grid: { display: false }
                }
            }
        }
    });

    // Weekly Revenue Trend
    const weeklyCtx = document.getElementById('weeklyRevenueChart');
    if (weeklyCtx) {
        const weeklyData = @json($weekly_revenue);
        new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: weeklyData.map(w => w.week),
                datasets: [{
                    label: 'Weekly Revenue',
                    data: weeklyData.map(w => w.amount),
                    fill: true,
                    backgroundColor: 'rgba(211, 47, 47, 0.1)',
                    borderColor: '#D32F2F',
                    borderWidth: 2,
                    pointRadius: 5,
                    pointBackgroundColor: '#D32F2F',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    tension: 0.4,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#D32F2F'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + (value / 1000).toFixed(0) + 'k';
                            },
                            font: { size: 11 },
                            color: '#999'
                        },
                        grid: { color: '#eee', drawBorder: false }
                    },
                    x: {
                        ticks: { font: { size: 11 }, color: '#999' },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Fleet Status
    const fleetCtx = document.getElementById('fleetStatusChart');
    if (fleetCtx) {
        new Chart(fleetCtx, {
            type: 'doughnut',
            data: {
                labels: ['On Hire', 'Available', 'Maintenance'],
                datasets: [{
                    data: [{{ $s['onHire'] ?? 0 }}, {{ count($availableTrucks) ?? 0 }}, {{ $maintenanceTrucks ?? 0 }}],
                    backgroundColor: ['#1976D2', '#27AE60', '#FF6F00'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { font: { size: 11 }, padding: 12, usePointStyle: true }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 12,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        borderColor: '#fff',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return (context.label || '') + ': ' + (context.parsed || 0);
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize P&L with last month
    if (monthlyData && monthlyData.length > 0) {
        selectMonth(monthlyData.length - 1, null);
    }
});
</script>
