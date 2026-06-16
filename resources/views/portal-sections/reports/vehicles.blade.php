<div>
    @php $reportYear = $this->reportYearLabel(); @endphp
    <div class="card mb-4">
        <div class="card-header">
            <span class="card-title">Revenue vs Maintenance by Vehicle &mdash; YTD {{ $reportYear }}</span>
        </div>

        <div style="height:260px;padding:12px 16px 18px">
            <canvas id="vehicleRevenueReportChart"></canvas>
        </div>
    </div>

    <div class="card mb-4" style="padding:0;overflow:hidden">
        <div class="card-header" style="padding:16px 20px 0">
            <span class="card-title">Vehicle ROI Detail</span>
        </div>

        <div class="table-wrap" style="border:none;border-radius:0">
            <table>
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Make / Model</th>
                        <th>Status</th>
                        <th>Revenue YTD</th>
                        <th>Maint Cost YTD</th>
                        <th>Gross Profit</th>
                        <th>Margin</th>
                        <th>Downtime</th>
                        <th>Utilisation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trucks as $truck)
                        @php
                            $revenue = (float) ($truck['rev_ytd'] ?? 0);
                            $maintenance = (float) ($truck['maint_ytd'] ?? 0);
                            $grossProfit = $revenue - $maintenance;
                            $margin = $revenue > 0 ? round(($grossProfit / $revenue) * 100) : 0;
                            $utilisation = ($truck['status'] ?? '') === 'on_hire' ? max(0, min(100, round(((150 - (float) ($truck['downtime'] ?? 0)) / 150) * 100))) : 0;
                            $marginColor = $margin > 70 ? 'var(--green)' : ($margin > 50 ? 'var(--orange)' : 'var(--red)');
                        @endphp

                        <tr>
                            <td class="fw-700">{{ $truck['rego'] ?? '-' }}</td>
                            <td>{{ trim(($truck['make'] ?? '').' '.($truck['model'] ?? '')) ?: '-' }}</td>
                            <td><span class="{{ $this->statusClass($truck['status'] ?? null) }}">{{ $this->statusLabel($truck['status'] ?? null) }}</span></td>
                            <td style="font-weight:700;color:var(--green)">{{ $this->money($revenue) }}</td>
                            <td style="color:var(--orange)">{{ $this->money($maintenance) }}</td>
                            <td class="fw-700">{{ $this->money($grossProfit) }}</td>
                            <td style="color:{{ $marginColor }};font-weight:700">{{ $margin }}%</td>
                            <td>{{ (int) ($truck['downtime'] ?? 0) }} days</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="width:60px;height:6px;background:var(--bg3);border-radius:999px;overflow:hidden">
                                        <div style="height:100%;width:{{ $utilisation }}%;background:var(--blue);border-radius:999px"></div>
                                    </div>
                                    <span style="font-size:12px">{{ $utilisation }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        window.ssRentalsInitVehicleRevenueReport = function () {
            const canvas = document.getElementById('vehicleRevenueReportChart');
            if (!canvas || typeof Chart === 'undefined') return;

            if (window.ssRentalsVehicleRevenueReportChart) {
                window.ssRentalsVehicleRevenueReportChart.destroy();
            }

            const trucks = @json($trucks);
            window.ssRentalsVehicleRevenueReportChart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: trucks.map(truck => truck.rego || truck.id || ''),
                    datasets: [
                        {
                            label: 'Revenue',
                            data: trucks.map(truck => Number(truck.rev_ytd || 0)),
                            backgroundColor: '#E85D62',
                            borderRadius: 4,
                            borderSkipped: false,
                        },
                        {
                            label: 'Maint Cost',
                            data: trucks.map(truck => Number(truck.maint_ytd || 0)),
                            backgroundColor: '#F2A154',
                            borderRadius: 4,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 36,
                                boxHeight: 10,
                                color: '#555',
                                font: { size: 11 },
                            },
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.85)',
                            padding: 12,
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 11 },
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': $' + Number(context.parsed.y || 0).toLocaleString('en-US', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0,
                                    });
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => '$' + (value / 1000).toFixed(0) + 'k',
                                color: '#999',
                                font: { size: 11 },
                            },
                            grid: { color: '#eee', drawBorder: false },
                        },
                        x: {
                            ticks: { color: '#999', font: { size: 11 } },
                            grid: { color: '#eee', drawBorder: false },
                        },
                    },
                },
            });
        };

        window.ssRentalsInitVehicleRevenueReport();
        document.addEventListener('livewire:navigated', window.ssRentalsInitVehicleRevenueReport);
    </script>
</div>
