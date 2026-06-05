<div>
    <div class="tabs"><button type="button" class="tab {{ $fleetTab === 'trucks' ? 'active' : '' }}" wire:click="$set('fleetTab','trucks')">Trucks</button><button type="button" class="tab {{ $fleetTab === 'trailers' ? 'active' : '' }}" wire:click="$set('fleetTab','trailers')">Trailers</button><button type="button" class="btn btn-add btn-sm" style="margin-left:auto" wire:click="openModal('vehicle')">Add Vehicle</button></div>
    <div class="table-wrap"><table><thead><tr><th>Rego</th><th>Make / Model</th><th>Year</th><th>Status</th><th>Hirer</th><th>COF</th><th>Rego</th><th>Location</th><th></th></tr></thead><tbody>
        @foreach($fleetTab === 'trucks' ? $this->filteredTrucks() : $this->filteredTrailers() as $v)
            <tr><td class="fw-700">{{ $v['rego'] }}</td><td>{{ $v['make'] }} {{ $v['model'] }}</td><td>{{ $v['year'] }}</td><td><span class="{{ $this->statusClass($v['status']) }}">{{ $this->statusLabel($v['status']) }}</span></td><td>{{ $this->customerName($v['hirer'] ?? null) }}</td><td>{{ $this->fmt($v['cof_expiry']) }}</td><td>{{ $this->fmt($v['rego_expiry']) }}</td><td>{{ $v['location'] }}</td><td><button type="button" class="btn btn-ghost btn-sm" wire:click="openModal('vehicle','{{ $v['id'] }}')">Edit</button></td></tr>
        @endforeach
    </tbody></table></div>
</div>
