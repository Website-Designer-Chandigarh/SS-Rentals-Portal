<div>
    <div class="tabs"><button type="button" class="tab {{ $documentTab === 'library' ? 'active' : '' }}" wire:click="$set('documentTab','library')">Library</button><button type="button" class="tab {{ $documentTab === 'agreements' ? 'active' : '' }}" wire:click="$set('documentTab','agreements')">Agreements</button></div>
    <div class="table-wrap"><table><thead><tr><th>Name</th><th>Type</th><th>Customer</th><th>Vehicle</th><th>Date</th><th>Size</th><th>Signed</th></tr></thead><tbody>
        @foreach($documents as $d)
            <tr><td class="fw-700">{{ $d['name'] }}</td><td>{{ $d['type'] }}</td><td>{{ $this->customerName($d['customer'] ?? null) }}</td><td>{{ $this->vehicleRego($d['vehicle'] ?? null) }}</td><td>{{ $this->fmt($d['date']) }}</td><td>{{ $d['size'] }}</td><td><span class="{{ $d['signed'] ? 'badge badge-green' : 'badge badge-gray' }}">{{ $d['signed'] ? 'Signed' : 'File' }}</span></td></tr>
        @endforeach
    </tbody></table></div>
</div>
