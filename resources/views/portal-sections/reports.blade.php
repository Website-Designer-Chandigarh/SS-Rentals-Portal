<div>
    <div class="tabs"><button type="button" class="tab {{ $reportTab === 'pl' ? 'active' : '' }}" wire:click="$set('reportTab','pl')">P&amp;L</button><button type="button" class="tab {{ $reportTab === 'bank' ? 'active' : '' }}" wire:click="$set('reportTab','bank')">Bank Transactions</button><button type="button" class="tab {{ $reportTab === 'fleet' ? 'active' : '' }}" wire:click="$set('reportTab','fleet')">Fleet ROI</button></div>
    @if($reportTab === 'pl')
        <div class="table-wrap"><table><thead><tr><th>Month</th><th>Revenue</th><th>Expenses</th><th>Net</th><th>Margin</th></tr></thead><tbody>@foreach($pnl_detail as $r)<tr><td class="fw-700">{{ $r['month'] }}</td><td>{{ $this->money($r['revenue']) }}</td><td>{{ $this->money($r['expenses']) }}</td><td class="fw-700 {{ $r['net'] >= 0 ? 'text-green' : 'text-red' }}">{{ $this->money($r['net']) }}</td><td>{{ $r['revenue'] ? round(($r['net'] / $r['revenue']) * 100) : 0 }}%</td></tr>@endforeach</tbody></table></div>
    @elseif($reportTab === 'bank')
        <div class="card"><div class="card-header"><span class="card-title">Bank Import</span></div><p class="text-muted">CSV, Excel, and PDF import controls from portal.html are represented here as a Livewire reports workspace. Add a backend parser when you are ready to persist bank transactions.</p></div>
    @else
        <div class="grid grid-auto">@foreach($trucks as $t)<div class="card card-sm"><div class="card-title mb-2">{{ $t['rego'] }} ROI</div><div class="flex justify-between"><span>Revenue YTD</span><strong>{{ $this->money($t['rev_ytd']) }}</strong></div><div class="flex justify-between"><span>Maintenance YTD</span><strong>{{ $this->money($t['maint_ytd']) }}</strong></div><div class="flex justify-between"><span>Asset Value</span><strong>{{ $this->money($t['value']) }}</strong></div></div>@endforeach</div>
    @endif
</div>
