<div>
    <div class="tabs">
        @foreach(['all' => 'All', 'draft' => 'Draft', 'overdue' => 'Overdue', 'paid' => 'Paid'] as $key => $label)
            <button type="button" class="tab {{ $invoiceTab === $key ? 'active' : '' }}" wire:click="$set('invoiceTab','{{ $key }}')">{{ $label }}</button>
        @endforeach
        <button type="button" class="btn btn-primary btn-sm" style="margin-left:auto" wire:click="openModal('invoice')">Generate Invoice</button>
    </div>
    <div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Customer</th><th>Period</th><th>Due</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
        @foreach($this->filteredInvoices() as $inv)
            <tr><td class="fw-700">{{ $inv['id'] }}</td><td>{{ $this->customerName($inv['customer'] ?? null) }}</td><td>{{ $inv['period'] }}</td><td>{{ $this->fmt($inv['due']) }}</td><td class="fw-700">{{ $this->money($inv['total']) }}</td><td><span class="{{ $this->statusClass($inv['status']) }}">{{ $this->statusLabel($inv['status']) }}</span></td><td class="flex gap-2"><button type="button" class="btn btn-ghost btn-sm" wire:click="openModal('invoice','{{ $inv['id'] }}')">Edit</button>@if($inv['status'] !== 'paid')<button type="button" class="btn btn-primary btn-sm" wire:click="markPaid('{{ $inv['id'] }}')">Paid</button>@endif</td></tr>
        @endforeach
    </tbody></table></div>
</div>
