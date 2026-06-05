<div class="grid grid-auto">
    @foreach($trucks as $t)
        @php $weeks = ($t['weekly_km'] ?? 0) > 0 ? floor(($t['ruc_balance'] ?? 0) / $t['weekly_km']) : null; @endphp
        <div class="card card-sm">
            <div class="card-header"><span class="card-title">{{ $t['rego'] }}</span><span class="{{ $this->statusClass($t['status']) }}">{{ $this->statusLabel($t['status']) }}</span></div>
            <div class="flex justify-between mb-2"><span>Weekly KM</span><strong>{{ number_format($t['weekly_km'] ?? 0) }} km</strong></div>
            <div class="flex justify-between mb-2"><span>RUC Balance</span><strong>{{ number_format($t['ruc_balance'] ?? 0) }} km</strong></div>
            <div class="flex justify-between mb-3"><span>Weeks Left</span><strong>{{ $weeks === null ? '-' : '~'.$weeks }}</strong></div>
            <button type="button" class="btn btn-ghost btn-sm" wire:click="openModal('navman','{{ $t['id'] }}')">Update KM</button>
        </div>
    @endforeach
</div>
