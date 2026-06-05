<div>
    <div class="tabs">
        @foreach(['hires' => 'All Hires', 'active' => 'Active', 'completed' => 'Completed', 'quotes' => 'Quotes'] as $key => $label)
            <button type="button" class="tab {{ $hireTab === $key ? 'active' : '' }}" wire:click="$set('hireTab','{{ $key }}')">{{ $label }}</button>
        @endforeach
        <button type="button" class="btn btn-primary btn-sm" style="margin-left:auto" wire:click="openModal('hire')">New Hire</button>
    </div>
    @if($hireTab === 'quotes')
        <div class="card"><div class="card-header"><span class="card-title">Quotes</span></div><p class="text-muted">Quote builder from portal.html has been kept as a Livewire workspace placeholder. Use New Hire to convert approved quotes into agreements.</p></div>
    @else
        <div class="table-wrap"><table><thead><tr><th>Hire</th><th>Customer</th><th>Truck</th><th>Trailer</th><th>Start</th><th>End</th><th>Weekly</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach($this->filteredHires() as $h)
                <tr><td class="fw-700">{{ $h['id'] }}</td><td>{{ $this->customerName($h['customer']) }}</td><td>{{ $this->vehicleRego($h['truck']) }}</td><td>{{ $this->vehicleRego($h['trailer'] ?? null) }}</td><td>{{ $this->fmt($h['start']) }}</td><td>{{ $this->fmt($h['end']) }}</td><td>{{ $this->money(($h['weekly_truck'] ?? 0) + ($h['weekly_trailer'] ?? 0)) }}</td><td><span class="{{ $this->statusClass($h['status']) }}">{{ $this->statusLabel($h['status']) }}</span></td><td class="flex gap-2"><button type="button" class="btn btn-ghost btn-sm" wire:click="openModal('hire','{{ $h['id'] }}')">Edit</button><button type="button" class="btn btn-primary btn-sm" wire:click="generateInvoiceFromHire('{{ $h['id'] }}')">Invoice</button></td></tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>
