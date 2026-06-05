@php
    $activeCustomers = array_values(array_filter($customers, fn($c) => ($c['status'] ?? '') === 'active'));
    $blacklistedCustomers = array_values(array_filter($customers, fn($c) => ($c['status'] ?? '') === 'blacklisted'));
    $inactiveCustomers = array_values(array_filter($customers, fn($c) => ($c['status'] ?? '') === 'inactive'));
    $activeHireCount = count($this->activeHires());
    $totalOutstanding = array_sum(array_map(fn($c) => (float) ($c['outstanding'] ?? 0), $customers));
    $totalYtd = array_sum(array_map(fn($c) => (float) ($c['ytd_revenue'] ?? 0), $customers));
@endphp

<div>
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2" style="flex-wrap:wrap">
            <button type="button" class="tab {{ $customerStatus === 'all' ? 'active' : '' }}" wire:click="$set('customerStatus','all')">All ({{ count($customers) }})</button>
            <button type="button" class="tab {{ $customerStatus === 'active' ? 'active' : '' }}" wire:click="$set('customerStatus','active')">Active ({{ count($activeCustomers) }})</button>
            <button type="button" class="tab {{ $customerStatus === 'blacklisted' ? 'active' : '' }}" wire:click="$set('customerStatus','blacklisted')">Blacklisted ({{ count($blacklistedCustomers) }})</button>
            <button type="button" class="tab {{ $customerStatus === 'inactive' ? 'active' : '' }}" wire:click="$set('customerStatus','inactive')">Inactive ({{ count($inactiveCustomers) }})</button>
        </div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="openModal('customer')">+ Add New Customer</button>
    </div>

    <div class="grid grid-4 mb-4">
        <div class="kpi-card"><div class="kpi-label">Total Customers</div><div class="kpi-value">{{ count($customers) }}</div></div>
        <div class="kpi-card"><div class="kpi-label">Active Hires</div><div class="kpi-value">{{ $activeHireCount }}</div></div>
        <div class="kpi-card"><div class="kpi-label">Total Outstanding</div><div class="kpi-value">{{ $this->money($totalOutstanding) }}</div></div>
        <div class="kpi-card"><div class="kpi-label">YTD Revenue</div><div class="kpi-value">{{ $this->money($totalYtd) }}</div></div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Director</th>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th>Active Hire</th>
                    <th>Weekly Rate</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->filteredCustomers() as $c)
                    @php
                        $activeHire = collect($this->activeHires())->firstWhere('customer', $c['id']);
                        $weeklyRate = (float) ($c['weekly_truck'] ?? 0) + (float) ($c['weekly_trailer'] ?? 0);
                    @endphp
                    <tr>
                        <td class="fw-700">{{ $c['company'] }}</td>
                        <td class="text-muted">{{ $c['director'] ?? '—' }}</td>
                        <td>{{ $c['contact'] ?: '—' }}</td>
                        <td>{{ $c['phone'] ?: '—' }}</td>
                        <td>{{ $activeHire ? $this->vehicleRego($activeHire['truck'] ?? null) : '—' }}</td>
                        <td>{{ $weeklyRate > 0 ? $this->money($weeklyRate).'/wk' : '—' }}</td>
                        <td class="{{ ($c['outstanding'] ?? 0) > 0 ? 'text-orange fw-700' : 'text-muted' }}">{{ ($c['outstanding'] ?? 0) > 0 ? $this->money($c['outstanding']) : '—' }}</td>
                        <td><span class="{{ $this->statusClass($c['status']) }}">{{ $this->statusLabel($c['status']) }}</span></td>
                        <td>
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-ghost btn-sm" wire:click="openModal('customer','{{ $c['id'] }}')" title="View">◉</button>
                                <button type="button" class="btn btn-ghost btn-sm" wire:click="openModal('customer','{{ $c['id'] }}')" title="Edit">✎</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
