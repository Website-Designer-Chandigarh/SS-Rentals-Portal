<div>
    <div class="tabs"><button type="button" class="tab {{ $maintenanceTab === 'log' ? 'active' : '' }}" wire:click="$set('maintenanceTab','log')">Log</button><button type="button" class="tab {{ $maintenanceTab === 'scheduled' ? 'active' : '' }}" wire:click="$set('maintenanceTab','scheduled')">Scheduled</button></div>
    <div class="table-wrap"><table><thead><tr><th>ID</th><th>Vehicle</th><th>Type</th><th>Description</th><th>Date</th><th>Cost</th><th>Status</th><th>Workshop</th></tr></thead><tbody>
        @foreach(array_filter($maintenance, fn($m) => $maintenanceTab === 'log' || ($m['status'] ?? '') === 'scheduled') as $m)
            <tr><td class="fw-700">{{ $m['id'] }}</td><td>{{ $m['rego'] }}</td><td>{{ $m['type'] }}</td><td>{{ $m['description'] }}</td><td>{{ $this->fmt($m['date']) }}</td><td>{{ $this->money($m['cost']) }}</td><td><span class="{{ $this->statusClass($m['status']) }}">{{ $this->statusLabel($m['status']) }}</span></td><td>{{ $m['workshop'] }}</td></tr>
        @endforeach
    </tbody></table></div>
</div>
