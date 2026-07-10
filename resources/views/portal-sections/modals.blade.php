@if($modal)
<div class="modal-overlay" wire:click.self="closeModal">
    <div class="modal modal-lg">
        @php
            $isKpi = str_starts_with($modal, 'kpi-');
            $activeHires = $this->activeHires();
            $availableTrucks = array_values(array_filter($trucks, fn($t) => ($t['status'] ?? '') === 'available'));
            $maintenanceTrucks = count(array_filter($trucks, fn($t) => ($t['status'] ?? '') === 'maintenance'));
            $allVehicles = $this->allVehicles();
            $pendingInvoices = array_values(array_filter($invoices, fn($i) => ($i['status'] ?? '') !== 'paid'));
            $overdueInvoices = array_values(array_filter($invoices, fn($i) => ($i['status'] ?? '') === 'overdue'));
            $weeklyKm = array_sum(array_map(fn($t) => (float) ($t['weekly_km'] ?? 0), $trucks));
            $rucCost = $weeklyKm * 0.062;
            $latestPnl = end($pnl_detail) ?: [];
            $latestMonthlyRevenue = end($monthly_revenue) ?: [];
            $latestMonthlyLabel = $this->displayMonthLabel($latestMonthlyRevenue['month'] ?? null);
            $kpiTitles = [
                'kpi-active-hires' => 'Active Hires',
                'kpi-fleet-util' => 'Fleet Utilisation',
                'kpi-weekly-rev' => 'Weekly Revenue',
                'kpi-monthly-rev' => 'Monthly Revenue',
                'kpi-outstanding' => 'Outstanding Invoices',
                'kpi-overdue' => 'Overdue Payments',
                'kpi-mileage' => 'Weekly Mileage',
                'kpi-ruc' => 'RUC Cost This Week',
            ];
            $stat = function ($label, $value, $color = 'var(--text)') {
                return '<div class="kpi-modal-stat"><div style="font-size:22px;font-weight:800;color:'.$color.'">'.$value.'</div><div style="font-size:11px;color:var(--text2);font-weight:600;margin-top:4px">'.$label.'</div></div>';
            };
            $modalTitles = [
                'vehicle' => ($selectedId ? 'Edit Vehicle' : 'Add New Vehicle'),
                'customer' => ($selectedId ? 'Edit Customer' : 'Add New Customer'),
                'hire' => ($selectedId ? 'Edit Hire Agreement' : 'New Hire Agreement'),
                'invoice' => 'Generate Invoice',
                'navman' => 'Update Navman Values',
            ];
            $modalIcons = ['vehicle' => 'fleet', 'customer' => 'customers', 'hire' => 'hires', 'invoice' => 'invoice', 'navman' => 'navman'];
        @endphp
        <div class="modal-header"><span class="modal-title">@if(! $isKpi && isset($modalIcons[$modal]))<span class="modal-title-icon">{!! $this->navIcon($modalIcons[$modal]) !!}</span>@endif{{ $isKpi ? ($kpiTitles[$modal] ?? 'Details') : ($modalTitles[$modal] ?? ucfirst($modal)) }}</span><button type="button" class="icon-btn" wire:click="closeModal">×</button></div>
        <div class="modal-body">
            @if($modal === 'kpi-active-hires')
                <div class="kpi-modal-stats">{!! $stat('On Hire', count($activeHires), '#6A1B9A') !!}{!! $stat('Available', count($availableTrucks), '#27AE60') !!}{!! $stat('In Workshop', $maintenanceTrucks, '#FF6F00') !!}</div>
                <div class="section-title">Currently On Hire</div>
                <div class="table-wrap mb-4"><table><thead><tr><th>Hire ID</th><th>Truck</th><th>Customer</th><th>Start</th><th>End</th><th>Rate/wk</th></tr></thead><tbody>
                    @foreach($activeHires as $h)<tr><td class="fw-700">{{ $h['id'] }}</td><td>{{ $this->vehicleRego($h['truck']) }}</td><td>{{ $this->customerName($h['customer']) }}</td><td>{{ $this->fmt($h['start']) }}</td><td>{{ $this->fmt($h['end']) }}</td><td class="fw-700">{{ $this->money(($h['weekly_truck'] ?? 0) + ($h['weekly_trailer'] ?? 0)) }}</td></tr>@endforeach
                </tbody></table></div>
                <div class="section-title">Available Trucks</div>
                <div class="table-wrap"><table><thead><tr><th>Rego</th><th>Make / Model</th><th>Year</th><th>Odometer</th></tr></thead><tbody>
                    @foreach($availableTrucks as $t)<tr><td class="fw-700">{{ $t['rego'] }}</td><td>{{ $t['make'] }} {{ $t['model'] }}</td><td>{{ $t['year'] }}</td><td>{{ number_format($t['odometer']) }} km</td></tr>@endforeach
                </tbody></table></div>
            @elseif($modal === 'kpi-fleet-util')
                <div class="kpi-modal-stats">{!! $stat('Utilisation', count($trucks) ? round((count($activeHires) / count($trucks)) * 100).'%' : '0%', '#27AE60') !!}{!! $stat('Trucks', count($trucks), '#6A1B9A') !!}{!! $stat('Trailers', count($trailers), '#6A1B9A') !!}{!! $stat('Total Assets', count($allVehicles), '#6A1B9A') !!}</div>
                <div class="section-title">All Trucks</div>
                <div class="table-wrap mb-4"><table><thead><tr><th>Rego</th><th>Make / Model</th><th>Status</th><th>Hirer</th><th>Weekly Rate</th><th>Odometer</th></tr></thead><tbody>
                    @foreach($trucks as $t)<tr><td class="fw-700">{{ $t['rego'] }}</td><td>{{ $t['make'] }} {{ $t['model'] }} {{ $t['year'] }}</td><td><span class="{{ $this->statusClass($t['status']) }}">{{ $this->statusLabel($t['status']) }}</span></td><td>{{ ($t['hirer'] ?? null) ? $this->customerName($t['hirer']) : '—' }}</td><td>{{ ($t['weekly_rate'] ?? 0) > 0 ? $this->money($t['weekly_rate']) : '—' }}</td><td>{{ number_format($t['odometer']) }} km</td></tr>@endforeach
                </tbody></table></div>
                <div class="section-title">All Trailers</div>
                <div class="table-wrap"><table><thead><tr><th>Rego</th><th>Make / Model</th><th>Status</th><th>Hirer</th></tr></thead><tbody>
                    @foreach($trailers as $t)<tr><td class="fw-700">{{ $t['rego'] }}</td><td>{{ $t['make'] }} {{ $t['model'] }}</td><td><span class="{{ $this->statusClass($t['status']) }}">{{ $this->statusLabel($t['status']) }}</span></td><td>{{ ($t['hirer'] ?? null) ? $this->customerName($t['hirer']) : '—' }}</td></tr>@endforeach
                </tbody></table></div>
            @elseif($modal === 'kpi-weekly-rev')
                <div class="kpi-modal-stats">{!! $stat('This Week', $this->money(end($weekly_revenue)['amount'] ?? 0), '#6A1B9A') !!}{!! $stat('Trucks On Hire', count($activeHires), '#27AE60') !!}{!! $stat('Total Weekly KM', number_format($weeklyKm).' km', '#FF6F00') !!}</div>
                <div class="section-title">Weekly Revenue Trend</div>
                <div class="table-wrap mb-4"><table><thead><tr><th>Week</th><th>Revenue</th><th>vs Prior Week</th></tr></thead><tbody>
                    @foreach($weekly_revenue as $i => $w)
                        @php $prev = $weekly_revenue[$i - 1]['amount'] ?? null; $diff = $prev === null ? null : $w['amount'] - $prev; @endphp
                        <tr><td class="fw-700">{{ $w['week'] }}</td><td class="fw-700">{{ $this->money($w['amount']) }}</td><td class="{{ $diff === null ? 'text-muted' : ($diff >= 0 ? 'text-green' : 'text-orange') }} fw-700">{{ $diff === null ? '—' : ($diff >= 0 ? '↑ ' : '↓ ').$this->money(abs($diff)) }}</td></tr>
                    @endforeach
                </tbody></table></div>
                <div class="section-title">Active Hire Rates</div>
                <div class="table-wrap"><table><thead><tr><th>Customer</th><th>Truck</th><th>Weekly Rate</th><th>Mileage Rate</th><th>RUC Rate</th></tr></thead><tbody>
                    @foreach($activeHires as $h)<tr><td>{{ $this->customerName($h['customer']) }}</td><td>{{ $this->vehicleRego($h['truck']) }}</td><td class="fw-700">{{ $this->money($h['weekly_truck'] ?? 0) }}</td><td>{{ $this->money($h['mileage_rate'] ?? 0) }}/km</td><td>{{ $this->money($h['ruc_rate'] ?? 0) }}/km</td></tr>@endforeach
                </tbody></table></div>
            @elseif($modal === 'kpi-monthly-rev')
                <div class="kpi-modal-stats">{!! $stat($latestMonthlyLabel.' Revenue', $this->money($latestMonthlyRevenue['amount'] ?? 0), '#6A1B9A') !!}{!! $stat('Net Profit', $this->money($latestMonthlyRevenue['net'] ?? 0), '#27AE60') !!}{!! $stat('Total Expenses', $this->money($latestPnl['expenses'] ?? 0), '#FF6F00') !!}</div>
                <div class="section-title">Monthly Trend</div>
                <div class="table-wrap mb-4"><table><thead><tr><th>Month</th><th>Revenue</th><th>Expenses</th><th>Net Profit</th><th>Margin</th></tr></thead><tbody>
                    @foreach($monthly_revenue as $m) @php $margin = $m['amount'] ? round(($m['net'] / $m['amount']) * 100) : 0; @endphp <tr><td class="fw-700">{{ $m['month'] }}</td><td class="fw-700">{{ $this->money($m['amount']) }}</td><td>{{ $this->money($m['expenses']) }}</td><td class="{{ $m['net'] >= 0 ? 'text-green' : 'text-orange' }} fw-700">{{ $m['net'] >= 0 ? '+' : '' }}{{ $this->money($m['net']) }}</td><td class="{{ $margin >= 10 ? 'text-green' : 'text-orange' }} fw-700">{{ $margin }}%</td></tr> @endforeach
                </tbody></table></div>
                <div class="section-title">{{ $latestMonthlyLabel }} Expense Breakdown</div>
                <div class="table-wrap"><table><thead><tr><th>Category</th><th>Amount</th><th>% of Revenue</th></tr></thead><tbody>
                    @foreach(['insurance' => 'Insurance', 'navman_ruc' => 'Navman/RUC', 'ruc' => 'RUC Charges', 'other' => 'Other', 'repairs' => 'Repairs', 'flexi' => 'Flexi Finance', 'heartland' => 'Heartland Finance', 'advertising' => 'Advertising', 'gst' => 'GST/Tax'] as $key => $label)
                        @if(($latestPnl[$key] ?? 0) > 0)<tr><td>{{ $label }}</td><td class="fw-700">{{ $this->money($latestPnl[$key]) }}</td><td class="text-muted">{{ ($latestPnl['revenue'] ?? 0) ? round(($latestPnl[$key] / $latestPnl['revenue']) * 100) : 0 }}%</td></tr>@endif
                    @endforeach
                </tbody></table></div>
            @elseif($modal === 'kpi-outstanding')
                <div class="kpi-modal-stats">{!! $stat('Total Outstanding', $this->money(array_sum(array_column($pendingInvoices, 'total'))), '#FF6F00') !!}{!! $stat('Invoices Pending', count($pendingInvoices), '#6A1B9A') !!}{!! $stat('Overdue Amount', $this->money(array_sum(array_column($overdueInvoices, 'total'))), '#d32f2f') !!}</div>
                <div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Customer</th><th>Period</th><th>Due Date</th><th>Total</th><th>Status</th></tr></thead><tbody>
                    @foreach($pendingInvoices as $inv)<tr><td class="fw-700">{{ $inv['id'] }}</td><td>{{ $this->customerName($inv['customer'] ?? null) }}</td><td>{{ $inv['period'] }}</td><td class="{{ ($inv['status'] ?? '') === 'overdue' ? 'text-orange' : '' }}">{{ $this->fmt($inv['due']) }}</td><td class="fw-700">{{ $this->money($inv['total']) }}</td><td><span class="{{ $this->statusClass($inv['status']) }}">{{ $this->statusLabel($inv['status']) }}</span></td></tr>@endforeach
                </tbody></table></div>
            @elseif($modal === 'kpi-overdue')
                <div class="kpi-modal-stats">{!! $stat('Total Overdue', $this->money(array_sum(array_column($overdueInvoices, 'total'))), '#d32f2f') !!}{!! $stat('Overdue Count', count($overdueInvoices), '#FF6F00') !!}</div>
                <div class="table-wrap mb-4"><table><thead><tr><th>Invoice</th><th>Customer</th><th>Contact</th><th>Phone</th><th>Due Date</th><th>Days Overdue</th><th>Amount</th></tr></thead><tbody>
                    @foreach($overdueInvoices as $inv) @php $cust = $this->findById($customers, $inv['customer'] ?? null); @endphp <tr><td class="fw-700">{{ $inv['id'] }}</td><td>{{ $this->customerName($inv['customer'] ?? null) }}</td><td>{{ $cust['contact'] ?? '—' }}</td><td style="color:#6A1B9A;font-weight:600">{{ $cust['phone'] ?? '—' }}</td><td class="text-orange">{{ $this->fmt($inv['due']) }}</td><td class="text-red fw-700">{{ abs($this->daysDiff($inv['due'])) }} days</td><td class="fw-700">{{ $this->money($inv['total']) }}</td></tr> @endforeach
                </tbody></table></div>
                @if(count($overdueInvoices))
                    <div style="background:rgba(211,47,47,0.07);border:1px solid rgba(211,47,47,0.2);border-radius:10px;padding:14px"><div style="font-weight:700;font-size:13px;margin-bottom:6px;color:#d32f2f">Action Required</div>@foreach($overdueInvoices as $inv) @php $cust = $this->findById($customers, $inv['customer'] ?? null); @endphp <div style="font-size:13px;color:var(--text2);margin-bottom:4px">Call <strong style="color:var(--text)">{{ $cust['contact'] ?? '—' }}</strong> at <strong style="color:#6A1B9A">{{ $cust['phone'] ?? '—' }}</strong> re {{ $inv['id'] }} ({{ $this->money($inv['total']) }})</div> @endforeach</div>
                @endif
            @elseif($modal === 'kpi-mileage')
                <div class="kpi-modal-stats">{!! $stat('Total Weekly KM', number_format($weeklyKm).' km', '#6A1B9A') !!}{!! $stat('RUC Cost', $this->money($rucCost), '#FF6F00') !!}{!! $stat('Trucks Reporting', count(array_filter($trucks, fn($t) => ($t['weekly_km'] ?? 0) > 0)), '#27AE60') !!}</div>
                <div class="table-wrap"><table><thead><tr><th>Truck</th><th>Customer</th><th>Weekly KM</th><th>RUC Rate</th><th>Weekly RUC $</th><th>Mileage Rate</th><th>Weekly Mileage $</th></tr></thead><tbody>
                    @foreach($trucks as $t) @php $h = collect($activeHires)->firstWhere('truck', $t['id']); @endphp <tr><td class="fw-700">{{ $t['rego'] }}</td><td>{{ ($t['hirer'] ?? null) ? $this->customerName($t['hirer']) : 'Available' }}</td><td class="fw-700">{{ ($t['weekly_km'] ?? 0) > 0 ? number_format($t['weekly_km']).' km' : '—' }}</td><td>{{ $h ? $this->money($h['ruc_rate']).'/km' : '—' }}</td><td class="text-orange fw-700">{{ $h && ($t['weekly_km'] ?? 0) > 0 ? $this->money($t['weekly_km'] * $h['ruc_rate']) : '—' }}</td><td>{{ $h ? $this->money($h['mileage_rate']).'/km' : '—' }}</td><td class="fw-700">{{ $h && ($t['weekly_km'] ?? 0) > 0 ? $this->money($t['weekly_km'] * $h['mileage_rate']) : '—' }}</td></tr> @endforeach
                </tbody></table></div>
            @elseif($modal === 'kpi-ruc')
                <div class="kpi-modal-stats">{!! $stat('Total RUC Cost', $this->money($rucCost), '#6A1B9A') !!}{!! $stat('Total KM', number_format($weeklyKm).' km', '#27AE60') !!}{!! $stat('Avg Rate', '$0.062/km', 'var(--text2)') !!}</div>
                <div class="table-wrap mb-4"><table><thead><tr><th>Truck</th><th>Status</th><th>RUC Balance</th><th>Weekly KM</th><th>Weeks Remaining</th></tr></thead><tbody>
                    @foreach($trucks as $t) @php $weeks = ($t['weekly_km'] ?? 0) > 0 ? floor(($t['ruc_balance'] ?? 0) / $t['weekly_km']) : null; @endphp <tr><td class="fw-700">{{ $t['rego'] }}</td><td><span class="{{ $this->statusClass($t['status']) }}">{{ $this->statusLabel($t['status']) }}</span></td><td class="text-green fw-700">{{ number_format($t['ruc_balance'] ?? 0) }} km</td><td>{{ ($t['weekly_km'] ?? 0) > 0 ? number_format($t['weekly_km']).' km' : '—' }}</td><td class="{{ $weeks !== null && $weeks < 3 ? 'text-red fw-700' : '' }}">{{ $weeks === null ? '—' : '~'.$weeks.' wks' }}</td></tr> @endforeach
                </tbody></table></div>
                <div style="background:rgba(106,27,154,0.07);border:1px solid rgba(106,27,154,0.15);border-radius:10px;padding:14px;font-size:13px;color:var(--text2)">RUC must be purchased before trucks run out. Top up trucks with under 3 weeks remaining promptly.</div>
            @elseif($modal === 'vehicle')
                <div class="segmented-control mb-4">
                    <button type="button" class="{{ ($vehicleForm['asset_type'] ?? 'truck') === 'truck' ? 'active' : '' }}" wire:click="setVehicleAssetType('truck')"><span>{!! $this->navIcon('fleet', 16) !!}</span>Truck</button>
                    <button type="button" class="{{ ($vehicleForm['asset_type'] ?? 'truck') === 'trailer' ? 'active' : '' }}" wire:click="setVehicleAssetType('trailer')"><span>{!! $this->navIcon('fleet', 16) !!}</span>Trailer</button>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Registration Number <span class="req">*</span></label><input wire:model="vehicleForm.rego" placeholder="e.g. MRU490"></div>
                    <div class="form-group"><label>Make</label><input wire:model="vehicleForm.make" placeholder="e.g. Volvo"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Model</label><input wire:model="vehicleForm.model" placeholder="e.g. FH 540"></div>
                    <div class="form-group"><label>Year</label><input wire:model="vehicleForm.year"></div>
                </div>
                <div class="form-group"><label>Vehicle Type / Description</label><input wire:model="vehicleForm.type" placeholder="{{ ($vehicleForm['asset_type'] ?? 'truck') === 'truck' ? 'e.g. 8x4 Curtainside Truck' : 'e.g. 5-Axle Curtainsider' }}"></div>
                <div class="form-row">
                    <div class="form-group"><label>Odometer (km)</label><input type="number" wire:model="vehicleForm.odometer"></div>
                    <div class="form-group"><label>Vehicle Value ($)</label><input type="number" wire:model="vehicleForm.value"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>COF Expiry</label><input type="date" wire:model="vehicleForm.cof_expiry"></div>
                    <div class="form-group"><label>Rego Expiry</label><input type="date" wire:model="vehicleForm.rego_expiry"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Insurance Expiry</label><input type="date" wire:model="vehicleForm.insurance_expiry"></div>
                    <div class="form-group"><label>Next Service Due (km)</label><input type="number" wire:model="vehicleForm.service_due_km" placeholder="e.g. 360000"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Next Service Date</label><input type="date" wire:model="vehicleForm.next_service"></div>
                    @if(($vehicleForm['asset_type'] ?? 'truck') === 'truck')
                        <div class="form-group"><label>RUC Balance (km)</label><input type="number" wire:model="vehicleForm.ruc_balance"></div>
                    @else
                        <div class="form-group"><label>Status</label><select wire:model="vehicleForm.status"><option value="available">Available</option><option value="on_hire">On Hire</option><option value="maintenance">Maintenance</option></select></div>
                    @endif
                </div>
                @if(($vehicleForm['asset_type'] ?? 'truck') === 'truck')
                    <div class="form-group"><label>Status</label><select wire:model="vehicleForm.status"><option value="available">Available</option><option value="on_hire">On Hire</option><option value="maintenance">Maintenance</option></select></div>
                @endif
                <div class="form-group"><label>Location</label><input wire:model="vehicleForm.location"></div>
                <div class="form-group"><label>Notes</label><input wire:model="vehicleForm.note" placeholder="Any service, finance, or vehicle notes"></div>
            @elseif($modal === 'customer')
                <div class="form-row">
                    <div class="form-group"><label>Company Name <span class="req">*</span></label><input wire:model="customerForm.company" placeholder="e.g. Chardikala Limited"></div>
                    <div class="form-group"><label>Director Name</label><input wire:model="customerForm.director" placeholder="e.g. Gurpinder Singh"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Contact Person <span class="req">*</span></label><input wire:model="customerForm.contact" placeholder="e.g. Gurpinder Singh"></div>
                    <div class="form-group"><label>Cellphone / Contact Number <span class="req">*</span></label><input wire:model="customerForm.phone" placeholder="e.g. 027 510 0233"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Email Address</label><input type="email" wire:model="customerForm.email" placeholder="e.g. info@company.co.nz"></div>
                    <div class="form-group"><label>Physical Address</label><input wire:model="customerForm.address" placeholder="e.g. 123 Main St, Auckland"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>NZBN (optional)</label><input wire:model="customerForm.nzbn" placeholder="9429..."></div>
                    <div class="form-group"><label>Credit Rating</label><select wire:model="customerForm.credit_rating"><option>A+</option><option>A</option><option>B+</option><option>B</option><option>B-</option><option>C</option></select></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Payment Terms</label><select wire:model="customerForm.payment_terms"><option>Weekly</option><option>Fortnightly</option><option>Monthly</option><option>On Invoice</option><option>7 days</option></select></div>
                    <div class="form-group"><label>Status</label><select wire:model="customerForm.status"><option value="prospect">Prospect</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="blacklisted">Blacklisted</option></select></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Weekly Truck Rate (NZD)</label><input type="number" wire:model="customerForm.weekly_truck"></div>
                    <div class="form-group"><label>Weekly Trailer Rate (NZD)</label><input type="number" wire:model="customerForm.weekly_trailer"></div>
                </div>
                <div class="form-group"><label>Notes</label><textarea wire:model="customerForm.notes" rows="3" placeholder="Any relevant notes about this customer..."></textarea></div>
            @elseif($modal === 'hire')
                <div class="form-row mb-4">
                    <div class="form-group"><label>Customer</label>
                        @include('portal-sections.searchable-select', ['model' => 'hireForm.customer', 'current' => $hireForm['customer'] ?? '', 'placeholder' => 'Select customer...', 'options' => collect($customers)->map(fn($c) => ['value' => $c['id'], 'label' => $c['company'], 'sub' => $c['contact'] ?? null])->all()])
                    </div>
                    <div class="form-group"><label>Assign Truck</label>
                        @include('portal-sections.searchable-select', ['model' => 'hireForm.truck', 'current' => $hireForm['truck'] ?? '', 'placeholder' => 'Select truck...', 'options' => collect($trucks)->map(fn($t) => ['value' => $t['id'], 'label' => $t['rego'], 'sub' => trim(($t['make'] ?? '').' '.($t['model'] ?? ''))])->all()])
                    </div>
                </div>
                <div class="form-row mb-4">
                    <div class="form-group"><label>Assign Trailer (optional)</label>
                        @include('portal-sections.searchable-select', ['model' => 'hireForm.trailer', 'current' => $hireForm['trailer'] ?? '', 'placeholder' => 'No trailer', 'options' => collect($trailers)->map(fn($t) => ['value' => $t['id'], 'label' => $t['rego'], 'sub' => trim(($t['make'] ?? '').' '.($t['model'] ?? ''))])->all()])
                    </div>
                    <div class="form-group"><label>Hire Start Date</label><input type="date" wire:model="hireForm.start"></div>
                </div>
                <div class="form-row mb-4"><div class="form-group"><label>Hire End Date</label><input type="date" wire:model="hireForm.end"></div><div class="form-group"><label>Charge Type</label><select wire:model.live="hireForm.charge_type"><option value="weekly">Weekly</option><option value="monthly">Monthly (Lease)</option></select></div></div>
                @if(($hireForm['charge_type'] ?? 'weekly') === 'monthly')
                    <div class="form-row mb-4"><div class="form-group"><label>Monthly Lease Rate (NZD)</label><input type="number" wire:model="hireForm.monthly_rate"></div><div class="form-group"><label>Eroad GPS Monitoring ($/month)</label><input type="number" wire:model="hireForm.eroad_rate"></div></div>
                @else
                    <div class="form-row mb-4"><div class="form-group"><label>Weekly Truck Rate (NZD)</label><input type="number" wire:model="hireForm.weekly_truck"></div><div class="form-group"><label>Weekly Trailer Rate (NZD)</label><input type="number" wire:model="hireForm.weekly_trailer"></div></div>
                @endif
                <div class="form-row-3 mb-4"><div class="form-group"><label>Mileage Rate ($/km)</label><input type="number" step="0.01" wire:model="hireForm.mileage_rate"></div><div class="form-group"><label>RUC Rate ($/km)</label><input type="number" step="0.001" wire:model="hireForm.ruc_rate"></div><div class="form-group"><label>Maximum Mileage Allowance</label><input wire:model="hireForm.max_mileage" placeholder="e.g. 21,833 km/month"></div></div>
                <div class="form-row mb-4"><div class="form-group"><label>Bond Amount (NZD)</label><input type="number" wire:model="hireForm.bond"></div><div class="form-group"><label>Status</label><select wire:model="hireForm.status"><option value="active">Active</option><option value="completed">Completed</option><option value="draft">Draft</option></select></div></div>
                <div class="form-group mb-4"><label>Payment Method</label><input wire:model="hireForm.payment_method"></div>
                @if(($hireForm['charge_type'] ?? 'weekly') === 'monthly')
                    <div class="section-title mb-4">Personal Guarantee (Lease)</div>
                    <div class="form-row-3 mb-4"><div class="form-group"><label>Guarantor Full Name</label><input wire:model="hireForm.guarantor_name"></div><div class="form-group"><label>Guarantor Address</label><input wire:model="hireForm.guarantor_address"></div><div class="form-group"><label>Guarantor Phone</label><input wire:model="hireForm.guarantor_phone"></div></div>
                @endif
                <div class="form-row-3 mb-4"><div class="form-group"><label>Truck VIN</label><input wire:model="hireForm.truck_vin"></div><div class="form-group"><label>Truck Colour</label><input wire:model="hireForm.truck_colour"></div><div class="form-group"><label>Trailer Colour</label><input wire:model="hireForm.trailer_colour"></div></div>
                <div class="form-group mb-4"><label>Notes / Special Conditions</label><textarea wire:model="hireForm.notes" rows="3" placeholder="Enter any special hire conditions..."></textarea></div>
                <div class="check-grid">
                    <label><input type="checkbox" wire:model="hireForm.insurance_verified"> Insurance verified</label>
                    <label><input type="checkbox" wire:model="hireForm.checklist_done"> Pre-handover checklist completed</label>
                    <label><input type="checkbox" wire:model="hireForm.bond_paid"> Bond payment received</label>
                    <label><input type="checkbox" wire:model="hireForm.signed"> Digital signature obtained</label>
                </div>
            @elseif($modal === 'invoice')
                @php
                    $selectedHire = $this->findById($hires, $invoiceForm['hire'] ?? null);
                    $invoiceTruck = $selectedHire ? $this->findById($trucks, $selectedHire['truck'] ?? null) : null;
                    $invoiceTrailer = $selectedHire ? $this->findById($trailers, $selectedHire['trailer'] ?? null) : null;
                    $invoiceCustomer = $selectedHire ? $this->findById($customers, $selectedHire['customer'] ?? null) : null;
                    $periodDays = $invoicePeriodFrom && $invoicePeriodTo ? max(1, \Illuminate\Support\Carbon::parse($invoicePeriodFrom)->diffInDays(\Illuminate\Support\Carbon::parse($invoicePeriodTo), false)) : 7;
                    $periodKm = $invoiceTruck ? round((float) ($invoiceTruck['weekly_km'] ?? 0) * ($periodDays / 7)) : 0;
                @endphp
                <div class="wizard-steps">
                    @foreach([[1,'Vehicle'],[2,'Period'],[3,'Review'],[4,'Done']] as [$n,$label])
                        <div class="wizard-step {{ $invoiceStep >= $n ? 'active' : '' }} {{ $invoiceStep > $n ? 'done' : '' }}"><span>{{ $invoiceStep > $n ? '✓' : $n }}</span><small>{{ $label }}</small></div>
                        @if($n < 4)<div class="wizard-line {{ $invoiceStep > $n ? 'active' : '' }}"></div>@endif
                    @endforeach
                </div>
                @if($invoiceStep === 1)
                    <div class="wizard-title">Select Vehicle & Invoice Type</div>
                    <div class="form-group"><label>Vehicle on Hire</label>
                        @include('portal-sections.searchable-select', ['model' => 'invoiceForm.hire', 'current' => $invoiceForm['hire'] ?? '', 'placeholder' => '-- Select vehicle --', 'options' => collect($activeHires)->map(fn($h) => ['value' => $h['id'], 'label' => $this->vehicleRego($h['truck']), 'sub' => $this->customerName($h['customer'])])->all()])
                    </div>
                    @if($selectedHire && $invoiceCustomer)
                        <div class="auto-panel mb-4">
                            <div class="auto-panel-title">Auto-retrieved from hire record</div>
                            <div class="auto-grid">
                                <div><small>Customer</small><strong>{{ $invoiceCustomer['company'] }}</strong></div>
                                <div><small>Contact</small><strong>{{ $invoiceCustomer['contact'] ?: '-' }}</strong></div>
                                <div><small>Truck</small><strong>{{ $invoiceTruck ? $invoiceTruck['rego'].' '.$invoiceTruck['make'].' '.$invoiceTruck['model'] : '-' }}</strong></div>
                                <div><small>Trailer</small><strong>{{ $invoiceTrailer['rego'] ?? 'None' }}</strong></div>
                                <div><small>Weekly Rate</small><strong>{{ $this->money($selectedHire['weekly_truck'] ?? 0) }}</strong></div>
                                <div><small>Navman KM/wk</small><strong>{{ number_format($invoiceTruck['weekly_km'] ?? 0) }} km</strong></div>
                            </div>
                        </div>
                    @endif
                    <div class="form-group"><label>Invoice Type</label><div class="invoice-type-grid">@foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $type => $label)<button type="button" class="{{ $invoiceType === $type ? 'active' : '' }}" wire:click="setInvoiceType('{{ $type }}')">{{ $label }}</button>@endforeach</div></div>
                    <div class="wizard-actions end"><button type="button" class="btn btn-primary" wire:click="nextInvoiceStep" @disabled(blank($invoiceForm['hire'] ?? null))>Next: Set Period →</button></div>
                @elseif($invoiceStep === 2)
                    <div class="wizard-title">Set Invoice Period</div>
                    <div class="form-row mb-4"><div class="form-group"><label>Period From</label><input type="date" wire:model="invoicePeriodFrom"></div><div class="form-group"><label>Period To</label><input type="date" wire:model="invoicePeriodTo"></div></div>
                    <div class="auto-panel mb-4"><strong>Duration:</strong> {{ $periodDays }} days ({{ number_format($periodDays / 7, 1) }} weeks) &nbsp;·&nbsp; <strong>Navman KM:</strong> {{ number_format($periodKm) }} km estimated</div>
                    <div class="wizard-actions"><button type="button" class="btn btn-ghost" wire:click="previousInvoiceStep">← Back</button><button type="button" class="btn btn-primary" wire:click="nextInvoiceStep">Next: Preview Invoice →</button></div>
                @elseif($invoiceStep === 3)
                    <div class="wizard-title">Review & Edit - {{ $invoiceForm['id'] ?: 'Draft Invoice' }}</div>
                    <div class="form-row mb-4"><div class="summary-box"><small>Bill To</small><strong>{{ $this->customerName($invoiceForm['customer'] ?? null) }}</strong><span>{{ $this->vehicleRego($selectedHire['truck'] ?? null) }}{{ ($selectedHire['trailer'] ?? null) ? ' + '.$this->vehicleRego($selectedHire['trailer']) : '' }}</span></div><div class="summary-box"><small>Invoice Period</small><strong>{{ $invoiceForm['period'] ?: $invoicePeriodFrom.' to '.$invoicePeriodTo }}</strong><span>Due {{ $this->fmt($invoiceForm['due'] ?? null) }}</span></div></div>
                    <div class="invoice-lines">
                        <div class="invoice-lines-head"><span>Description</span><span>Amount</span></div>
                        <label><span>Truck Hire</span><input type="number" wire:model.live="invoiceForm.truck_hire"></label>
                        <label><span>Trailer Hire</span><input type="number" wire:model.live="invoiceForm.trailer_hire"></label>
                        <label><span>Mileage</span><input type="number" wire:model.live="invoiceForm.mileage"></label>
                        <label><span>RUC</span><input type="number" wire:model.live="invoiceForm.ruc"></label>
                        <label><span>Damage</span><input type="number" wire:model.live="invoiceForm.damage"></label>
                        <label><span>Extras</span><input type="number" wire:model.live="invoiceForm.extras"></label>
                    </div>
                    <div class="form-row mt-4"><div class="form-group"><label>Issue Date</label><input type="date" wire:model="invoiceForm.date"></div><div class="form-group"><label>Due Date</label><input type="date" wire:model="invoiceForm.due"></div></div>
                    <div class="form-group"><label>Status</label><select wire:model="invoiceForm.status"><option value="draft">Draft</option><option value="sent">Sent</option><option value="overdue">Overdue</option><option value="paid">Paid</option></select></div>
                @elseif($invoiceStep === 4)
                    <div class="done-state"><div class="done-mark">✓</div><h3>Invoice saved</h3><p>{{ $invoiceForm['id'] }} has been saved to the database.</p><button type="button" class="btn btn-primary" wire:click="closeModal">Done</button></div>
                @endif
            @elseif($modal === 'navman')
                <div class="form-row-3"><div class="form-group"><label>Weekly KM</label><input wire:model="navmanForm.weekly_km"></div><div class="form-group"><label>RUC Balance</label><input wire:model="navmanForm.ruc_balance"></div><div class="form-group"><label>Odometer</label><input wire:model="navmanForm.odometer"></div></div>
            @endif
        </div>
        <div class="modal-footer">
            @if($modal === 'invoice' && in_array($invoiceStep, [1, 2, 4]))
            @else
                <button type="button" class="btn btn-ghost" wire:click="closeModal">{{ $isKpi ? 'Close' : 'Cancel' }}</button>
                @if($isKpi)
                @elseif($modal === 'vehicle')<button type="button" class="btn {{ $selectedId ? 'btn-primary' : 'btn-add' }}" wire:click="saveVehicle">✓ {{ $selectedId ? 'Save Vehicle' : 'Add Vehicle' }}</button>
                @elseif($modal === 'customer')<button type="button" class="btn {{ $selectedId ? 'btn-primary' : 'btn-add' }}" wire:click="saveCustomer">✓ {{ $selectedId ? 'Save Changes' : 'Add Customer' }}</button>
                @elseif($modal === 'hire')<button type="button" class="btn {{ $selectedId ? 'btn-primary' : 'btn-add' }}" wire:click="saveHire">✓ {{ $selectedId ? 'Save Hire Agreement' : 'Create Hire Agreement' }}</button>
                @elseif($modal === 'invoice')<button type="button" class="btn btn-primary" wire:click="saveInvoice">✓ Save Invoice</button>
                @elseif($modal === 'navman')<button type="button" class="btn btn-primary" wire:click="saveNavman">✓ Save Navman</button>@endif
            @endif
        </div>
    </div>
</div>
@endif
