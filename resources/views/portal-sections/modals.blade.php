@if($modal)
<div class="modal-overlay" wire:click.self="closeModal">
    <div class="modal modal-lg">
        <div class="modal-header"><span class="modal-title">{{ ucfirst($modal) }}</span><button type="button" class="icon-btn" wire:click="closeModal">×</button></div>
        <div class="modal-body">
            @if($modal === 'vehicle')
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
            <button type="button" class="btn btn-ghost" wire:click="closeModal">Cancel</button>
            @if($modal === 'vehicle')<button type="button" class="btn btn-primary" wire:click="saveVehicle">Save Vehicle</button>
            @elseif($modal === 'customer')<button type="button" class="btn btn-primary" wire:click="saveCustomer">Save Customer</button>
            @elseif($modal === 'hire')<button type="button" class="btn btn-primary" wire:click="saveHire">Save Hire</button>
            @elseif($modal === 'invoice')<button type="button" class="btn btn-primary" wire:click="saveInvoice">Save Invoice</button>
            @elseif($modal === 'navman')<button type="button" class="btn btn-primary" wire:click="saveNavman">Save Navman</button>@endif
        </div>
    </div>
</div>
@endif
