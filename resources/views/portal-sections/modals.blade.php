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
        @endphp
        <div class="modal-header"><span class="modal-title">{{ $isKpi ? ($kpiTitles[$modal] ?? 'Details') : ucfirst($modal) }}</span><button type="button" class="icon-btn" wire:click="closeModal">×</button></div>
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
                <div class="kpi-modal-stats">{!! $stat('May 2026 Revenue', $this->money(end($monthly_revenue)['amount'] ?? 0), '#6A1B9A') !!}{!! $stat('Net Profit', $this->money(end($monthly_revenue)['net'] ?? 0), '#27AE60') !!}{!! $stat('Total Expenses', $this->money($latestPnl['expenses'] ?? 0), '#FF6F00') !!}</div>
                <div class="section-title">Monthly Trend</div>
                <div class="table-wrap mb-4"><table><thead><tr><th>Month</th><th>Revenue</th><th>Expenses</th><th>Net Profit</th><th>Margin</th></tr></thead><tbody>
                    @foreach($monthly_revenue as $m) @php $margin = $m['amount'] ? round(($m['net'] / $m['amount']) * 100) : 0; @endphp <tr><td class="fw-700">{{ $m['month'] }}</td><td class="fw-700">{{ $this->money($m['amount']) }}</td><td>{{ $this->money($m['expenses']) }}</td><td class="{{ $m['net'] >= 0 ? 'text-green' : 'text-orange' }} fw-700">{{ $m['net'] >= 0 ? '+' : '' }}{{ $this->money($m['net']) }}</td><td class="{{ $margin >= 10 ? 'text-green' : 'text-orange' }} fw-700">{{ $margin }}%</td></tr> @endforeach
                </tbody></table></div>
                <div class="section-title">May Expense Breakdown</div>
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
                <div class="form-row"><div class="form-group"><label>Type</label><select wire:model="vehicleForm.asset_type"><option value="truck">Truck</option><option value="trailer">Trailer</option></select></div><div class="form-group"><label>Rego</label><input wire:model="vehicleForm.rego"></div></div>
                <div class="form-row"><div class="form-group"><label>Make</label><input wire:model="vehicleForm.make"></div><div class="form-group"><label>Model</label><input wire:model="vehicleForm.model"></div></div>
                <div class="form-row-3"><div class="form-group"><label>Year</label><input wire:model="vehicleForm.year"></div><div class="form-group"><label>Status</label><select wire:model="vehicleForm.status"><option value="available">Available</option><option value="on_hire">On Hire</option><option value="maintenance">Maintenance</option></select></div><div class="form-group"><label>Value</label><input wire:model="vehicleForm.value"></div></div>
                <div class="form-row"><div class="form-group"><label>COF Expiry</label><input type="date" wire:model="vehicleForm.cof_expiry"></div><div class="form-group"><label>Rego Expiry</label><input type="date" wire:model="vehicleForm.rego_expiry"></div></div>
                <div class="form-group"><label>Location</label><input wire:model="vehicleForm.location"></div>
            @elseif($modal === 'customer')
                <div class="form-row"><div class="form-group"><label>Company</label><input wire:model="customerForm.company"></div><div class="form-group"><label>Status</label><select wire:model="customerForm.status"><option value="active">Active</option><option value="inactive">Inactive</option><option value="blacklisted">Blacklisted</option><option value="prospect">Prospect</option></select></div></div>
                <div class="form-row"><div class="form-group"><label>Contact</label><input wire:model="customerForm.contact"></div><div class="form-group"><label>Phone</label><input wire:model="customerForm.phone"></div></div>
                <div class="form-row"><div class="form-group"><label>Email</label><input wire:model="customerForm.email"></div><div class="form-group"><label>Credit Rating</label><input wire:model="customerForm.credit_rating"></div></div>
                <div class="form-group"><label>Notes</label><textarea wire:model="customerForm.notes"></textarea></div>
            @elseif($modal === 'hire')
                <div class="form-row"><div class="form-group"><label>Customer</label><select wire:model="hireForm.customer"><option value="">Select</option>@foreach($customers as $c)<option value="{{ $c['id'] }}">{{ $c['company'] }}</option>@endforeach</select></div><div class="form-group"><label>Status</label><select wire:model="hireForm.status"><option value="active">Active</option><option value="completed">Completed</option><option value="draft">Draft</option></select></div></div>
                <div class="form-row"><div class="form-group"><label>Truck</label><select wire:model="hireForm.truck"><option value="">Select</option>@foreach($trucks as $t)<option value="{{ $t['id'] }}">{{ $t['rego'] }}</option>@endforeach</select></div><div class="form-group"><label>Trailer</label><select wire:model="hireForm.trailer"><option value="">None</option>@foreach($trailers as $t)<option value="{{ $t['id'] }}">{{ $t['rego'] }}</option>@endforeach</select></div></div>
                <div class="form-row"><div class="form-group"><label>Start</label><input type="date" wire:model="hireForm.start"></div><div class="form-group"><label>End</label><input type="date" wire:model="hireForm.end"></div></div>
                <div class="form-row-3"><div class="form-group"><label>Weekly Truck</label><input wire:model="hireForm.weekly_truck"></div><div class="form-group"><label>Mileage Rate</label><input wire:model="hireForm.mileage_rate"></div><div class="form-group"><label>RUC Rate</label><input wire:model="hireForm.ruc_rate"></div></div>
                <div class="form-group"><label>Notes</label><textarea wire:model="hireForm.notes"></textarea></div>
            @elseif($modal === 'invoice')
                <div class="form-row"><div class="form-group"><label>Customer</label><select wire:model="invoiceForm.customer"><option value="">Select</option>@foreach($customers as $c)<option value="{{ $c['id'] }}">{{ $c['company'] }}</option>@endforeach</select></div><div class="form-group"><label>Status</label><select wire:model="invoiceForm.status"><option value="draft">Draft</option><option value="sent">Sent</option><option value="overdue">Overdue</option><option value="paid">Paid</option></select></div></div>
                <div class="form-row"><div class="form-group"><label>Date</label><input type="date" wire:model="invoiceForm.date"></div><div class="form-group"><label>Due</label><input type="date" wire:model="invoiceForm.due"></div></div>
                <div class="form-group"><label>Period</label><input wire:model="invoiceForm.period"></div>
                <div class="form-row-3"><div class="form-group"><label>Truck Hire</label><input wire:model="invoiceForm.truck_hire"></div><div class="form-group"><label>Mileage</label><input wire:model="invoiceForm.mileage"></div><div class="form-group"><label>RUC</label><input wire:model="invoiceForm.ruc"></div></div>
                <div class="form-row"><div class="form-group"><label>Damage</label><input wire:model="invoiceForm.damage"></div><div class="form-group"><label>Extras</label><input wire:model="invoiceForm.extras"></div></div>
            @elseif($modal === 'navman')
                <div class="form-row-3"><div class="form-group"><label>Weekly KM</label><input wire:model="navmanForm.weekly_km"></div><div class="form-group"><label>RUC Balance</label><input wire:model="navmanForm.ruc_balance"></div><div class="form-group"><label>Odometer</label><input wire:model="navmanForm.odometer"></div></div>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" wire:click="closeModal">{{ $isKpi ? 'Close' : 'Cancel' }}</button>
            @if($isKpi)
            @elseif($modal === 'vehicle')<button type="button" class="btn btn-primary" wire:click="saveVehicle">Save Vehicle</button>
            @elseif($modal === 'customer')<button type="button" class="btn btn-primary" wire:click="saveCustomer">Save Customer</button>
            @elseif($modal === 'hire')<button type="button" class="btn btn-primary" wire:click="saveHire">Save Hire</button>
            @elseif($modal === 'invoice')<button type="button" class="btn btn-primary" wire:click="saveInvoice">Save Invoice</button>
            @elseif($modal === 'navman')<button type="button" class="btn btn-primary" wire:click="saveNavman">Save Navman</button>@endif
        </div>
    </div>
</div>
@endif
