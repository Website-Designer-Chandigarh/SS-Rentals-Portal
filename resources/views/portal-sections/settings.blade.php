<div class="grid" style="grid-template-columns:1fr 1fr;gap:16px">
    <div class="card"><div class="card-header"><span class="card-title">Company Settings</span></div>
        <div class="form-group"><label>Company Name</label><input wire:model="settingsForm.company"></div>
        <div class="form-group"><label>GST Number</label><input wire:model="settingsForm.gst"></div>
        <div class="form-group"><label>Address</label><input wire:model="settingsForm.address"></div>
        <div class="form-group"><label>Default RUC Rate ($/km)</label><input wire:model="settingsForm.ruc_rate"></div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="saveSettings">Save Changes</button>
    </div>
    <div class="card"><div class="card-header"><span class="card-title">Invoice Settings</span></div>
        <div class="form-group"><label>Invoice Prefix</label><input wire:model="settingsForm.invoice_prefix"></div>
        <div class="form-group"><label>Default Payment Terms (days)</label><input wire:model="settingsForm.payment_terms"></div>
        <div class="form-group"><label>GST Rate (%)</label><input wire:model="settingsForm.gst_rate"></div>
        <div class="alert alert-orange">Google Sheets and Xero setup panels are intentionally backend-ready placeholders in this Livewire port.</div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="saveSettings">Save Changes</button>
    </div>
</div>
