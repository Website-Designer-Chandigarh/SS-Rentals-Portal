@php
    $roles = [
        ['key' => 'super_admin_owner', 'label' => 'Super Admin / Owner', 'description' => 'Full access to users, settings, billing, fleet, reports, and all portal data.'],
        ['key' => 'operations_manager', 'label' => 'Operations Manager', 'description' => 'Manage fleet, customers, hires, maintenance, documents, and Navman operations.'],
        ['key' => 'accounts_staff', 'label' => 'Accounts Staff', 'description' => 'Manage invoices, reports, customer balances, payments, and financial exports.'],
    ];
    $roleLabels = collect($roles)->pluck('label', 'key');
@endphp

<div class="grid" style="grid-template-columns:1fr 1fr;gap:16px">
    <div class="card" style="grid-column:1 / -1">
        <div class="card-header">
            <span class="card-title">User &amp; Role Management</span>
            <span class="badge badge-blue">{{ \App\Models\User::count() }} Users</span>
        </div>
        <div class="grid grid-3 mb-4">
            @foreach($roles as $role)
                <div class="card card-sm" style="background:var(--bg2)">
                    <div class="card-title mb-2">{{ $role['label'] }}</div>
                    <div class="text-sm text-muted">{{ $role['description'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach(\App\Models\User::query()->orderBy('name')->get() as $user)
                        <tr>
                            <td class="fw-700">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge badge-blue">{{ $roleLabels[$user->role] ?? $user->role }}</span></td>
                            <td><span class="badge badge-green">Active</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
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
