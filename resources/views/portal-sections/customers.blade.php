<div>
    <div class="tabs">
        @foreach(['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive', 'blacklisted' => 'Blacklisted'] as $key => $label)
            <button type="button" class="tab {{ $customerStatus === $key ? 'active' : '' }}" wire:click="$set('customerStatus','{{ $key }}')">{{ $label }}</button>
        @endforeach
        <button type="button" class="btn btn-primary btn-sm" style="margin-left:auto" wire:click="openModal('customer')">Add Customer</button>
    </div>
    <div class="grid grid-auto">
        @foreach($this->filteredCustomers() as $c)
            <div class="card card-sm">
                <div class="card-header"><span class="card-title">{{ $c['company'] }}</span><span class="{{ $this->statusClass($c['status']) }}">{{ $this->statusLabel($c['status']) }}</span></div>
                <div class="text-sm text-muted mb-2">{{ $c['contact'] ?: '-' }} · {{ $c['phone'] ?: '-' }}</div>
                <div class="flex justify-between mb-2"><span>Outstanding</span><strong>{{ $this->money($c['outstanding'] ?? 0) }}</strong></div>
                <div class="flex justify-between mb-3"><span>YTD Revenue</span><strong>{{ $this->money($c['ytd_revenue'] ?? 0) }}</strong></div>
                <button type="button" class="btn btn-ghost btn-sm" wire:click="openModal('customer','{{ $c['id'] }}')">Edit</button>
            </div>
        @endforeach
    </div>
</div>
