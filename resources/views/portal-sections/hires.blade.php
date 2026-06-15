<div>
    <div class="tabs">
        @foreach(['hires' => 'All Hires', 'active' => 'Active', 'completed' => 'Completed', 'quotes' => 'Quotes'] as $key => $label)
            <button type="button" class="tab {{ $hireTab === $key ? 'active' : '' }}" wire:click="$set('hireTab','{{ $key }}')">{{ $label }}</button>
        @endforeach
        <button type="button" class="btn btn-add btn-sm" style="margin-left:auto" wire:click="openModal('hire')">New Hire</button>
    </div>
    @if($hireTab === 'quotes')
        @php
            $quoteTemplates = $this->quoteTemplates();
            $selectedTemplate = $quoteForm['templateId'] ? collect($quoteTemplates)->firstWhere('id', $quoteForm['templateId']) : null;
            $quoteTypeLabel = [
                'truck_only' => 'Truck Only',
                'trailer_only' => 'Trailer Only',
                'truck_trailer' => 'Truck & Trailer',
            ][$quoteForm['vehicleType'] ?? 'truck_trailer'] ?? 'Truck & Trailer';
        @endphp

        @if($quoteStep === 0)
            <div class="quote-head">
                <div>
                    <div class="section-title">Select a Quote Template</div>
                    <p class="text-muted">Choose a draft template to start a new quote, or view saved quotes below.</p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm quote-purple" wire:click="startQuote">+ Blank Quote</button>
            </div>

            <div class="quote-template-grid">
                @foreach($quoteTemplates as $template)
                    <button type="button" class="quote-template-card" wire:click="startQuote('{{ $template['id'] }}')">
                        <span class="quote-template-icon">{{ $template['type'] === 'trailer_only' ? 'TRL' : 'TRK' }}</span>
                        <span class="quote-template-body">
                            <strong>{{ $template['name'] }}</strong>
                            <small>{{ ucfirst($template['chargeType']) }} · {{ ['truck_only' => 'Truck Only', 'trailer_only' => 'Trailer Only', 'truck_trailer' => 'Truck & Trailer'][$template['type']] }}</small>
                            @if($template['truckDesc'])<span>{{ $template['truckDesc'] }}</span>@endif
                            @if($template['trailerDesc'])<span>{{ $template['trailerDesc'] }}</span>@endif
                            <span class="quote-badges">
                                @if($template['weeklyRate'] > 0)<em class="badge badge-blue">{{ $this->money($template['weeklyRate']) }}/WK</em>@endif
                                @if($template['monthlyRate'] > 0)<em class="badge badge-blue">{{ $this->money($template['monthlyRate']) }}/MO</em>@endif
                                @if($template['rucRate'] > 0)<em class="badge badge-gray">RUC {{ $template['rucRate'] }}/KM</em>@endif
                            </span>
                            <span class="quote-template-action">Use this template -></span>
                        </span>
                    </button>
                @endforeach
            </div>

            @if(count($savedQuotes))
                <div class="section-title mt-4">Saved Quotes ({{ count($savedQuotes) }})</div>
                <div class="quote-template-grid">
                    @foreach($savedQuotes as $quote)
                        <div class="quote-saved-card">
                            <div>
                                <strong>{{ $quote['form']['companyName'] ?: 'Unnamed quote' }}</strong>
                                <small>{{ $quote['form']['quoteNumber'] }} · {{ date('d M Y', strtotime($quote['savedAt'])) }}</small>
                            </div>
                            <span class="text-muted">{{ ['truck_only' => 'Truck Only', 'trailer_only' => 'Trailer Only', 'truck_trailer' => 'Truck & Trailer'][$quote['form']['vehicleType'] ?? 'truck_trailer'] }}</span>
                            <div class="quote-saved-actions">
                                <button type="button" class="btn btn-primary btn-sm" wire:click="editQuote('{{ $quote['id'] }}')">Open & Edit</button>
                                <button type="button" class="btn btn-ghost btn-sm text-red" wire:click="deleteQuote('{{ $quote['id'] }}')">Delete</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            @php
                $quoteChargeRows = [];
                if (($quoteForm['chargeType'] ?? '') === 'daily' && (float) ($quoteForm['dailyRate'] ?? 0) > 0) {
                    $quoteChargeRows[] = ['Daily Hire Rate', $this->money($quoteForm['dailyRate'])];
                }
                if ((($quoteForm['chargeType'] ?? '') === 'weekly' || (float) ($quoteForm['weeklyRate'] ?? 0) > 0) && (float) ($quoteForm['weeklyRate'] ?? 0) > 0) {
                    $quoteChargeRows[] = ['Weekly Hire Rate', $this->money($quoteForm['weeklyRate'])];
                }
                if ((($quoteForm['chargeType'] ?? '') === 'monthly' || (float) ($quoteForm['monthlyRate'] ?? 0) > 0) && (float) ($quoteForm['monthlyRate'] ?? 0) > 0) {
                    $quoteChargeRows[] = ['Monthly Hire Rate', $this->money($quoteForm['monthlyRate'])];
                }
                if ((float) ($quoteForm['rucRate'] ?? 0) > 0) {
                    $quoteChargeRows[] = ['RUC Charges', rtrim(rtrim((string) $quoteForm['rucRate'], '0'), '.').' cents per km'];
                }
                if ((float) ($quoteForm['mileageRate'] ?? 0) > 0) {
                    $quoteChargeRows[] = ['Mileage Charges', rtrim(rtrim((string) $quoteForm['mileageRate'], '0'), '.').' cents per km'];
                }
                if ($quoteForm['maxMileage'] ?? null) {
                    $quoteChargeRows[] = ['Maximum Mileage', $quoteForm['maxMileage']];
                }
                $quoteTitle = 'SS Rentals '.(($quoteForm['chargeType'] ?? '') === 'monthly' && (float) ($quoteForm['weeklyRate'] ?? 0) <= 0 ? 'Lease' : 'Rental').' Quotation';
            @endphp
            <div class="quote-builder">
                <div class="quote-breadcrumb">
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="backToQuoteTemplates">Back to Templates</button>
                    <span>/</span>
                    <strong>{{ $selectedTemplate['name'] ?? 'New Quote' }}</strong>
                </div>

                <div class="quote-form-grid">
                    <div class="quote-column">
                        <div class="card">
                            <div class="section-title">Quote Details</div>
                            <div class="form-row-3">
                                <div class="form-group"><label>Quote Number</label><input wire:model.live="quoteForm.quoteNumber"></div>
                                <div class="form-group"><label>Issue Date</label><input type="date" wire:model.live="quoteForm.quoteDate"></div>
                                <div class="form-group"><label>Valid For (days)</label><input type="number" wire:model.live="quoteForm.validDays"></div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="section-title">Customer / Hirer</div>
                            <div class="segmented-control mb-4">
                                <button type="button" class="{{ ($quoteForm['customerMode'] ?? 'new') === 'existing' ? 'active' : '' }}" wire:click="setQuoteCustomerMode('existing')">Existing Customer</button>
                                <button type="button" class="{{ ($quoteForm['customerMode'] ?? 'new') === 'new' ? 'active' : '' }}" wire:click="setQuoteCustomerMode('new')">New Customer</button>
                            </div>

                            @if(($quoteForm['customerMode'] ?? 'new') === 'existing')
                                <div class="form-group">
                                    <label>Select Customer</label>
                                    <select wire:model.live="quoteForm.useExistingCustomer" wire:change="selectQuoteCustomer">
                                        <option value="">Select customer...</option>
                                        @foreach(collect($customers)->where('status', '!=', 'blacklisted')->sortBy('company') as $customer)
                                            <option value="{{ $customer['id'] }}">{{ $customer['company'] }}{{ $customer['contact'] ? ' · '.$customer['contact'] : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($quoteForm['companyName'])
                                    <div class="auto-panel">{{ $quoteForm['companyName'] }}{{ $quoteForm['contactName'] ? ' · '.$quoteForm['contactName'] : '' }}{{ $quoteForm['contactPhone'] ? ' · '.$quoteForm['contactPhone'] : '' }}</div>
                                @endif
                            @else
                                <div class="auto-panel mb-4">New customer details will be saved as a prospect when this quote is saved.</div>
                                <div class="form-row">
                                    <div class="form-group"><label>Company / Business Name</label><input wire:model.live="quoteForm.companyName" placeholder="e.g. GSR Transport Limited"></div>
                                    <div class="form-group"><label>Contact Person</label><input wire:model.live="quoteForm.contactName"></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group"><label>Phone / Mobile</label><input wire:model.live="quoteForm.contactPhone"></div>
                                    <div class="form-group"><label>Email</label><input type="email" wire:model.live="quoteForm.contactEmail"></div>
                                </div>
                                <div class="form-group"><label>Address</label><input wire:model.live="quoteForm.contactAddress"></div>
                            @endif
                        </div>

                        <div class="card">
                            <div class="section-title">Hire Details</div>
                            <div class="form-group">
                                <label>Vehicle Type</label>
                                <select wire:model.live="quoteForm.vehicleType">
                                    <option value="truck_trailer">Truck & Trailer</option>
                                    <option value="truck_only">Truck Only</option>
                                    <option value="trailer_only">Trailer Only</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Hire Duration</label><input type="number" wire:model.live="quoteForm.hireDuration"></div>
                                <div class="form-group"><label>Unit</label><select wire:model.live="quoteForm.hireDurationUnit"><option value="days">Days</option><option value="weeks">Weeks</option><option value="months">Months</option><option value="years">Years</option></select></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Start Date</label><input type="date" wire:model.live="quoteForm.startDate"></div>
                                <div class="form-group"><label>End Date</label><input type="date" wire:model.live="quoteForm.endDate"></div>
                            </div>
                        </div>
                    </div>

                    <div class="quote-column">
                        <div class="card">
                            <div class="section-title">Vehicle Description</div>
                            @if(in_array($quoteForm['vehicleType'] ?? 'truck_trailer', ['truck_only', 'truck_trailer']))
                                <div class="form-group">
                                    <label>Truck</label>
                                    <select wire:model.live="quoteForm.truckDesc">
                                        <option value="">Select truck or type below...</option>
                                        @foreach($trucks as $truck)
                                            <option value="{{ trim(($truck['year'] ?? '').' '.($truck['make'] ?? '').' '.($truck['model'] ?? '')) }}">{{ $truck['rego'] }} - {{ $truck['year'] }} {{ $truck['make'] }} {{ $truck['model'] }}</option>
                                        @endforeach
                                    </select>
                                    <input class="mt-2" wire:model.live="quoteForm.truckDesc" placeholder="Or type custom description">
                                </div>
                            @endif
                            @if(in_array($quoteForm['vehicleType'] ?? 'truck_trailer', ['trailer_only', 'truck_trailer']))
                                <div class="form-group">
                                    <label>Trailer</label>
                                    <select wire:model.live="quoteForm.trailerDesc">
                                        <option value="">Select trailer or type below...</option>
                                        @foreach($trailers as $trailer)
                                            <option value="{{ trim(($trailer['year'] ?? '').' '.($trailer['make'] ?? '').' '.($trailer['model'] ?? '')) }}">{{ $trailer['rego'] }} - {{ $trailer['year'] ?? '' }} {{ $trailer['make'] }} {{ $trailer['model'] }}</option>
                                        @endforeach
                                    </select>
                                    <input class="mt-2" wire:model.live="quoteForm.trailerDesc" placeholder="Or type custom description">
                                </div>
                            @endif
                        </div>

                        <div class="card">
                            <div class="section-title">Hire Charges</div>
                            <div class="form-group"><label>Primary Charge Type</label><select wire:model.live="quoteForm.chargeType"><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option></select></div>
                            <div class="form-row">
                                <div class="form-group quote-muted-field"><label>Daily Rate (NZD)</label><input type="number" step="0.01" wire:model.live="quoteForm.dailyRate"></div>
                                <div class="form-group quote-muted-field"><label>Weekly Rate (NZD)</label><input type="number" step="0.01" wire:model.live="quoteForm.weeklyRate"></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group quote-muted-field"><label>Monthly Rate (NZD)</label><input type="number" step="0.01" wire:model.live="quoteForm.monthlyRate"></div>
                                <div class="form-group"><label>RUC Rate ($/km)</label><input type="number" step="0.01" wire:model.live="quoteForm.rucRate"></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Mileage Rate ($/km)</label><input type="number" step="0.01" wire:model.live="quoteForm.mileageRate"></div>
                                <div class="form-group"><label>Maximum Mileage Allowance</label><input wire:model.live="quoteForm.maxMileage"></div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="section-title">Notes & Conditions</div>
                            <textarea rows="6" wire:model.live="quoteForm.notes"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card quote-summary">
                    <div class="quote-summary-head">
                        <strong>Quote Summary - {{ $quoteForm['quoteNumber'] }}</strong>
                        <span>Valid {{ $quoteForm['validDays'] }} days from issue</span>
                    </div>
                    <div class="quote-summary-grid">
                        <div><small>Customer</small><strong>{{ $quoteForm['companyName'] ?: '-' }}</strong></div>
                        <div><small>Contact</small><strong>{{ $quoteForm['contactName'] ?: '-' }}</strong></div>
                        <div><small>Type</small><strong>{{ $quoteTypeLabel }}</strong></div>
                        <div><small>Duration</small><strong>{{ $quoteForm['hireDuration'] ? $quoteForm['hireDuration'].' '.$quoteForm['hireDurationUnit'] : '-' }}</strong></div>
                    </div>
                    <div class="quote-actions">
                        <button type="button" class="btn btn-primary" wire:click="downloadQuotePDF">Download Quote PDF</button>
                        <button type="button" class="btn btn-ghost quote-green" wire:click="saveQuote">Save Quote</button>
                        <button type="button" class="btn btn-ghost" wire:click="startQuote('{{ $quoteForm['templateId'] }}')">Reset</button>
                    </div>
                </div>
            </div>

            <div class="quote-print-doc" aria-hidden="true">
                <div class="print-quote-header">
                    <div class="print-quote-logo"><img src="{{ asset('images/logo.png') }}" alt="SS Rentals"></div>
                    <div class="print-quote-title">
                        <h1>{{ $quoteTitle }}</h1>
                        <p>Quote No: {{ $quoteForm['quoteNumber'] }} &nbsp;|&nbsp; Date: {{ $quoteForm['quoteDate'] ? date('d F Y', strtotime($quoteForm['quoteDate'])) : '-' }}</p>
                    </div>
                </div>
                <div class="print-quote-info-grid">
                    <div class="print-quote-box">
                        <h3>Prepared For</h3>
                        <p><strong>{{ $quoteForm['companyName'] ?: '-' }}</strong></p>
                        <p>Attn: {{ $quoteForm['contactName'] ?: '-' }}</p>
                        @if($quoteForm['contactPhone'])<p>Phone: {{ $quoteForm['contactPhone'] }}</p>@endif
                        @if($quoteForm['contactEmail'])<p>Email: {{ $quoteForm['contactEmail'] }}</p>@endif
                    </div>
                    <div class="print-quote-box">
                        <h3>Hire Details</h3>
                        <p><strong>Type:</strong> {{ $quoteTypeLabel }}</p>
                        @if($quoteForm['hireDuration'])<p><strong>Duration:</strong> {{ $quoteForm['hireDuration'] }} {{ $quoteForm['hireDurationUnit'] }}</p>@endif
                        @if($quoteForm['startDate'])<p><strong>Start Date:</strong> {{ date('d F Y', strtotime($quoteForm['startDate'])) }}</p>@endif
                        @if($quoteForm['endDate'])<p><strong>End Date:</strong> {{ date('d F Y', strtotime($quoteForm['endDate'])) }}</p>@endif
                        @if($quoteForm['validDays'])<p><strong>Quote Valid:</strong> {{ $quoteForm['validDays'] }} days from issue</p>@endif
                    </div>
                </div>
                @if($quoteForm['validDays'])
                    <div class="print-quote-validity">This quotation is valid for <strong>{{ $quoteForm['validDays'] }} days</strong> from the issue date. Rates are subject to change after this period.</div>
                @endif
                <h2>Vehicle Details</h2>
                <table>
                    @if($quoteForm['truckDesc'])<tr><td>Truck</td><td>{{ $quoteForm['truckDesc'] }}</td></tr>@endif
                    @if($quoteForm['trailerDesc'])<tr><td>Trailer</td><td>{{ $quoteForm['trailerDesc'] }}</td></tr>@endif
                </table>
                <h2>{{ (($quoteForm['chargeType'] ?? '') === 'monthly' && (float) ($quoteForm['weeklyRate'] ?? 0) <= 0) ? 'Lease Charges' : 'Hire Charges' }}</h2>
                <table>
                    @forelse($quoteChargeRows as [$label, $value])
                        <tr><td>{{ $label }}</td><td>{{ $value }}</td></tr>
                    @empty
                        <tr><td>Charges</td><td>-</td></tr>
                    @endforelse
                </table>
                <h2>Notes & Conditions</h2>
                <div class="print-quote-notes">{{ $quoteForm['notes'] }}</div>
                <div class="print-quote-sign">
                    <p><strong>To accept this quotation, sign below:</strong></p>
                    <p>Name: _____________________________ &nbsp;&nbsp; Signature: _____________________________</p>
                    <p>Date: _____________________________ &nbsp;&nbsp; Position: _____________________________</p>
                </div>
                <div class="print-quote-footer">SS Rentals &nbsp;|&nbsp; 280a Peak Road, Cambridge, New Zealand &nbsp;|&nbsp; All amounts in NZD, excluding GST</div>
            </div>
        @endif
    @else
        <div class="table-wrap"><table><thead><tr><th>Hire</th><th>Customer</th><th>Truck</th><th>Trailer</th><th>Start</th><th>End</th><th>Weekly</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach($this->filteredHires() as $h)
                <tr><td class="fw-700">{{ $h['id'] }}</td><td>{{ $this->customerName($h['customer']) }}</td><td>{{ $this->vehicleRego($h['truck']) }}</td><td>{{ $this->vehicleRego($h['trailer'] ?? null) }}</td><td>{{ $this->fmt($h['start']) }}</td><td>{{ $this->fmt($h['end']) }}</td><td>{{ $this->money(($h['weekly_truck'] ?? 0) + ($h['weekly_trailer'] ?? 0)) }}</td><td><span class="{{ $this->statusClass($h['status']) }}">{{ $this->statusLabel($h['status']) }}</span></td><td class="flex gap-2"><button type="button" class="btn btn-ghost btn-sm" wire:click="openModal('hire','{{ $h['id'] }}')">Edit</button><button type="button" class="btn btn-add btn-sm" wire:click="generateInvoiceFromHire('{{ $h['id'] }}')">Invoice</button></td></tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>
