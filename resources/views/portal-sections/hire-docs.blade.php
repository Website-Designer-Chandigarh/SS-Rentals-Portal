@php
    $docHire = array_merge([
        'charge_type' => 'weekly', 'monthly_rate' => 0, 'eroad_rate' => 0, 'max_mileage' => '',
        'payment_method' => '', 'truck_vin' => '', 'truck_colour' => 'White', 'trailer_colour' => 'White',
        'guarantor_name' => '', 'guarantor_address' => '', 'guarantor_phone' => '',
    ], $docHire);
    $customer = $this->findById($customers, $docHire['customer'] ?? null) ?? [];
    $truck = $this->findById($trucks, $docHire['truck'] ?? null) ?? [];
    $trailer = $this->findById($trailers, $docHire['trailer'] ?? null);
    $isLease = ($docHire['charge_type'] ?? 'weekly') === 'monthly';
    $vehiclePrefix = $truck && $trailer ? 'Truck and Trailer' : ($truck ? 'Truck' : 'Trailer');
    $docAgreementDate = now()->format('jS F Y');
    $rateRow = $isLease
        ? ['Rate', $this->money($docHire['monthly_rate'] ?? 0).' per month plus GST']
        : ['Rate', $this->money(($docHire['weekly_truck'] ?? 0) + ($docHire['weekly_trailer'] ?? 0)).' per week plus GST'];
@endphp

@if($docType === 'schedule')
<div class="schedule-print-doc" aria-hidden="true">
    <div class="print-quote-header">
        <div class="print-quote-logo"><img src="{{ asset('images/logo.png') }}" alt="SS Rentals"></div>
        <div class="print-quote-title">
            <h1>{{ $vehiclePrefix }} Schedule</h1>
            <p>Schedule {{ $docHire['id'] }} &nbsp;|&nbsp; Date: {{ $docAgreementDate }}</p>
        </div>
    </div>
    <p>This {{ $vehiclePrefix }} Schedule Agreement ("Agreement") is made and entered into on this <strong>{{ $docAgreementDate }}</strong>, by and between:</p>
    <div class="print-quote-info-grid">
        <div class="print-quote-box"><h3>Owner</h3><p><strong>SS Rentals</strong></p></div>
        <div class="print-quote-box"><h3>Hirer</h3><p><strong>{{ $customer['company'] ?? '-' }}</strong></p></div>
    </div>
    <p>The OWNER agrees to hire, and the HIRER agrees to take on and hire, the vehicle(s) described below and to pay the rental charges as outlined in this Agreement. This Schedule forms part of and incorporates all terms and conditions of the {{ $vehiclePrefix }} Lease/Rental Agreement made between the parties.</p>

    @if($truck)
        <h2>1. Truck Description</h2>
        <table>
            <tr><td>Manufacturer</td><td>{{ $truck['make'] ?? '-' }}</td></tr>
            <tr><td>Year of Manufacture</td><td>{{ $truck['year'] ?? '-' }}</td></tr>
            <tr><td>Registration Number</td><td>{{ $truck['rego'] ?? '-' }}</td></tr>
            <tr><td>VIN Number</td><td>{{ $docHire['truck_vin'] ?: '-' }}</td></tr>
            <tr><td>Description</td><td>{{ trim(($truck['make'] ?? '').' '.($truck['model'] ?? '')) ?: '-' }}</td></tr>
            <tr><td>Colour</td><td>{{ $docHire['truck_colour'] ?: 'White' }}</td></tr>
            <tr><td>Certificate of Fitness (COF) Expires</td><td>{{ $this->fmt($truck['cof_expiry'] ?? null) }}</td></tr>
            <tr><td>Registration Expires</td><td>{{ $this->fmt($truck['rego_expiry'] ?? null) }}</td></tr>
        </table>
    @endif

    @if($trailer)
        <h2>2. Trailer Description</h2>
        <table>
            <tr><td>Manufacturer</td><td>{{ $trailer['make'] ?? '-' }}</td></tr>
            <tr><td>Year of Manufacture</td><td>{{ $trailer['year'] ?? '-' }}</td></tr>
            <tr><td>Registration Number</td><td>{{ $trailer['rego'] ?? '-' }}</td></tr>
            <tr><td>Description</td><td>{{ trim(($trailer['make'] ?? '').' '.($trailer['model'] ?? '')) ?: '-' }}</td></tr>
            <tr><td>Colour</td><td>{{ $docHire['trailer_colour'] ?: 'White' }}</td></tr>
            <tr><td>Certificate of Fitness (COF) Expires</td><td>{{ $this->fmt($trailer['cof_expiry'] ?? null) }}</td></tr>
            <tr><td>Registration Expires</td><td>{{ $this->fmt($trailer['rego_expiry'] ?? null) }}</td></tr>
        </table>
    @endif

    <h2>3. Vehicle Location</h2>
    <table>
        <tr><td>Originating Premises</td><td>280a Peak Road, Cambridge</td></tr>
        <tr><td>Operating Location</td><td>New Zealand</td></tr>
    </table>

    <h2>4. Term of Contract</h2>
    <table>
        <tr><td>Commencement Date</td><td>{{ $this->fmt($docHire['start'] ?? null) }}</td></tr>
        <tr><td>Return Date</td><td>{{ $this->fmt($docHire['end'] ?? null) }}</td></tr>
    </table>

    <h2>5. Rental Charges</h2>
    <table>
        <tr><td>Payment Method</td><td>{{ $docHire['payment_method'] ?: '-' }}</td></tr>
        <tr><td>{{ $rateRow[0] }}</td><td>{{ $rateRow[1] }}</td></tr>
        @if($isLease && (float) ($docHire['eroad_rate'] ?? 0) > 0)
            <tr><td>Eroad GPS Monitoring</td><td>{{ $this->money($docHire['eroad_rate']) }} + GST per month</td></tr>
        @endif
        <tr><td>Mileage Charges</td><td>{{ rtrim(rtrim((string) ($docHire['mileage_rate'] ?? 0), '0'), '.') }} cents per km plus GST</td></tr>
        <tr><td>RUC Charges</td><td>{{ rtrim(rtrim((string) ($docHire['ruc_rate'] ?? 0), '0'), '.') }} cents per km</td></tr>
        <tr><td>Bond Money</td><td>{{ $this->money($docHire['bond'] ?? 0) }}</td></tr>
        <tr><td>Maximum Mileage</td><td>{{ $docHire['max_mileage'] ?: '-' }}</td></tr>
    </table>

    <h2>6. Date of First Payment</h2>
    <p>"Once agreement has been signed and first invoice has been issued"</p>

    <h2>7. Hubometer Readings</h2>
    <table>
        <tr><td>At Commencement</td><td>&nbsp;</td></tr>
        <tr><td>Upon Return</td><td>&nbsp;</td></tr>
    </table>

    <h2>8. Road User Charges (RUC)</h2>
    <table>
        <tr><td>At Commencement</td><td>&nbsp;</td></tr>
        <tr><td>Upon Return</td><td>&nbsp;</td></tr>
    </table>

    <div class="print-quote-sign">
        <p><strong>Signed by the Parties</strong></p>
        <p>SIGNED for and on behalf of SS Rentals: &nbsp;&nbsp; Name: Sukhdeep Singh &nbsp;&nbsp; Signature: _______________________________</p>
        <p>SIGNED for and on behalf of the Hirer ({{ $customer['company'] ?? '-' }}): &nbsp;&nbsp; Name: {{ $customer['contact'] ?? '' }} &nbsp;&nbsp; Signature: _______________________________</p>
        <p>Designation: _____________________________ &nbsp;&nbsp; Date: _____________________________</p>
    </div>
    <div class="print-quote-footer">SS Rentals &nbsp;|&nbsp; 280a Peak Road, Cambridge, New Zealand &nbsp;|&nbsp; All amounts in NZD, excluding GST</div>
</div>
@endif

@if($docType === 'agreement')
<div class="agreement-print-doc" aria-hidden="true">
    <div class="print-quote-header">
        <div class="print-quote-logo"><img src="{{ asset('images/logo.png') }}" alt="SS Rentals"></div>
        <div class="print-quote-title">
            <h1>{{ $vehiclePrefix }} {{ $isLease ? 'Lease' : 'Rental' }} Agreement</h1>
            <p>{{ $docHire['id'] }} &nbsp;|&nbsp; Date: {{ $docAgreementDate }}</p>
        </div>
    </div>

    <p>THIS {{ $isLease ? 'LEASE' : 'RENTAL' }} AGREEMENT ("Agreement") is made and entered into on <strong>{{ $docAgreementDate }}</strong> between:</p>
    <div class="print-quote-box mb-4">
        <p><strong>{{ $isLease ? 'LESSOR' : 'OWNER' }}:</strong> &nbsp; SS Rentals, 280a Peak Road, Cambridge, New Zealand ("SS Rentals" or "Owner")</p>
        <p><strong>{{ $isLease ? 'LESSEE' : 'HIRER' }}:</strong> &nbsp; <strong>{{ $customer['company'] ?? '-' }}</strong> of New Zealand ("Hirer")</p>
        <p><strong>CONTACT:</strong> &nbsp; {{ $customer['contact'] ?? '-' }}</p>
    </div>
    <p>SS Rentals and the Hirer agree that SS Rentals will {{ $isLease ? 'lease' : 'hire' }} to the Hirer the vehicle(s) described in the Schedule attached hereto ("Schedule"), on the terms and conditions set out in this Agreement.</p>

    <h2>1. Definitions</h2>
    <p>"Agreement" means this {{ $isLease ? 'Lease' : 'Rental' }} Agreement including all Schedules.</p>
    <p>"Commencement Date" means the date the Hirer takes possession of the vehicle(s).</p>
    <p>"{{ $isLease ? 'Lease' : 'Hire' }} Term" means the period from the Commencement Date to the Return Date as specified in the Schedule.</p>
    <p>"Vehicle(s)" means the truck and/or trailer described in the Schedule.</p>
    <p>"Rental Charges" means all fees, rates, RUC charges, mileage charges, and other amounts payable under this Agreement.</p>

    <h2>2. {{ $isLease ? 'Lease' : 'Hire' }} of Vehicle</h2>
    <p><strong>2.1</strong> SS Rentals agrees to {{ $isLease ? 'lease' : 'hire' }} the Vehicle(s) to the Hirer for the {{ $isLease ? 'Lease' : 'Hire' }} Term, subject to the terms of this Agreement.</p>
    <p><strong>2.2</strong> The Hirer acknowledges that title to the Vehicle(s) remains with SS Rentals at all times. This Agreement creates a security interest in favour of SS Rentals under the Personal Property Securities Act 1999 (PPSA).</p>
    <p><strong>2.3</strong> On request of SS Rentals, the Hirer shall promptly execute any documents required to ensure the security interest is perfected.</p>
    <p><strong>2.4</strong> The Hirer waives its rights under sections 114(1)(a), 116, 117, 119, 120(2), 121, 125, 129, 131, 132, 133, 134 and 148 of the PPSA.</p>

    <h2>3. {{ $isLease ? 'Lease' : 'Hire' }} Term and Rental Charges</h2>
    <p><strong>3.1</strong> The {{ $isLease ? 'Lease' : 'Hire' }} Term is set out in the Schedule. The Hirer agrees to pay the Rental Charges as specified in the Schedule.</p>
    <p>
        @if($isLease)
            <strong>Monthly Lease Rate:</strong> NZD {{ $this->money($docHire['monthly_rate'] ?? 0) }} per month + GST
        @else
            <strong>Weekly Rate:</strong> NZD {{ $this->money(($docHire['weekly_truck'] ?? 0) + ($docHire['weekly_trailer'] ?? 0)) }} per week + GST
        @endif
    </p>
    <p><strong>3.2</strong> Payments shall be made {{ $isLease ? 'monthly' : 'weekly' }} in advance by direct debit from the Hirer's nominated bank account. Invoices will be issued at the commencement of each {{ $isLease ? 'month' : 'week' }}.</p>
    <p><strong>3.3</strong> A Direct Debit Dishonour Fee of 5% of the total invoice amount will be charged if any scheduled payment is declined or dishonoured.</p>
    @if($isLease && (float) ($docHire['eroad_rate'] ?? 0) > 0)
        <p><strong>3.4</strong> In addition to the Monthly Lease Rate, the Hirer shall pay a monthly charge of {{ $this->money($docHire['eroad_rate']) }} + GST for Eroad GPS tracking and fleet monitoring services fitted to the Vehicle(s). This charge is payable concurrently with each monthly lease payment.</p>
    @endif
    <p><strong>3.5</strong> All charges under this Agreement are exclusive of GST unless otherwise stated. GST will be added at the prevailing rate.</p>

    @if($isLease)
        <h2>4. Obligation to Pay Remaining {{ $isLease ? 'Lease' : 'Hire' }} Term</h2>
        <p class="print-doc-highlight"><strong>4.1 Critical Obligation:</strong> If the Hirer terminates this Agreement before the agreed {{ $isLease ? 'Lease' : 'Hire' }} Term end date, for any reason whatsoever, the Hirer remains <strong>fully liable</strong> to pay the <strong>total remaining balance of the Rental Charges</strong> for the entire unexpired portion of the {{ $isLease ? 'Lease' : 'Hire' }} Term.</p>
        <p><strong>4.2</strong> This obligation applies regardless of the reason for early termination, including but not limited to: voluntary termination, business closure, financial hardship, sale of the Hirer's business, or any other circumstance.</p>
        <p><strong>4.3</strong> All remaining Rental Charges shall become immediately due and payable upon the date of early termination.</p>
        <p><strong>4.4</strong> SS Rentals reserves the right to recover all outstanding amounts plus any costs incurred in enforcing this clause, including legal fees and debt recovery costs.</p>
    @endif

    <h2>5. Hirer's Obligations</h2>
    <p><strong>5.1</strong> The Hirer shall ensure the Vehicle(s) are operated only by qualified, licensed personnel in accordance with applicable laws and manufacturer's instructions.</p>
    <p><strong>5.2</strong> The Hirer shall insure the Vehicle(s) for full replacement value with SS Rentals noted as an interested party, and provide proof of insurance before the Commencement Date.</p>
    <p><strong>5.3</strong> The Hirer shall perform daily checks (tyres, lights, fluids) and report defects immediately to SS Rentals.</p>
    <p><strong>5.4</strong> The Hirer must purchase Road User Charges (RUC) as required during the {{ $isLease ? 'Lease' : 'Hire' }} Term.</p>
    <p><strong>5.5</strong> The Hirer shall not assign, pledge, sublease, or part with possession of the Vehicle(s) without prior written consent from SS Rentals.</p>
    <p><strong>5.6</strong> The Hirer shall maintain the Vehicle(s) in a clean condition and return them in the same condition as at commencement, fair wear and tear excepted.</p>
    <p><strong>5.7</strong> The Vehicle(s) must be returned with the same level of Diesel and AdBlue as at commencement. Shortfalls will be recharged at cost plus a 15% surcharge.</p>

    <h2>6. Maintenance and Repairs</h2>
    <p><strong>6.1</strong> SS Rentals will deliver the Vehicle(s) in good working condition, roadworthy, and compliant with all applicable regulatory standards.</p>
    <p><strong>6.2</strong> SS Rentals is responsible for scheduled maintenance unless otherwise agreed.</p>
    <p><strong>6.3</strong> The Hirer may carry out minor repairs not exceeding $100 without prior approval. All major repairs require prior written consent from SS Rentals.</p>
    <p><strong>6.4</strong> Repairs required due to the Hirer's negligence, misuse, overloading, or breach of law are the sole responsibility of the Hirer.</p>
    <p><strong>6.5</strong> Normal tyre wear is SS Rentals' responsibility. Puncture repairs and damage due to misuse are at the Hirer's cost.</p>
    <p><strong>6.6</strong> In the event of an accident, the Hirer must immediately notify SS Rentals and comply with all reporting and insurance procedures.</p>

    <h2>6A. Breakdown and Replacement Vehicle</h2>
    <p><strong>6A.1</strong> In the event that the {{ $isLease ? 'leased' : 'hired' }} Vehicle(s) become inoperable due to mechanical breakdown or failure, SS Rentals shall use reasonable endeavours to arrange the necessary repairs as soon as practicable.</p>
    <p><strong>6A.2</strong> Where the anticipated repair period exceeds three (3) consecutive calendar days from the date SS Rentals is notified of the breakdown, SS Rentals shall, subject to availability, provide the Hirer with a replacement vehicle of a similar class and operational capability. For the avoidance of doubt, no obligation to provide a replacement vehicle arises where the repair period is three (3) days or fewer.</p>
    <p><strong>6A.3</strong> Any replacement vehicle is conditional upon: (a) the Hirer being in compliance with all obligations under this Agreement; (b) the breakdown not being attributable to the Hirer's negligence or breach; and (c) a suitable replacement being available in SS Rentals' fleet.</p>
    <p><strong>6A.4</strong> Any replacement vehicle shall be subject to the full terms of this Agreement. The Hirer remains responsible for all obligations in respect of the replacement vehicle during the period of its supply.</p>
    <p><strong>6A.5</strong> SS Rentals shall bear no liability for consequential loss, loss of income, or business interruption arising from any period during which the Vehicle(s) are unavailable due to breakdown or repair.</p>

    <h2>7. Insurance and Liability</h2>
    <p><strong>7.1 Hirer's Obligation to Insure.</strong> The Hirer shall, at its sole cost and expense, procure, maintain and keep current throughout the entire {{ $isLease ? 'Lease' : 'Hire' }} Term comprehensive motor vehicle insurance and third-party liability insurance over each Vehicle in amounts no less than: (a) the full replacement value of each Vehicle for own-damage cover; and (b) $5,000,000 (five million dollars) per occurrence for third-party bodily injury and property damage liability. The Hirer shall ensure that SS Rentals is noted on each policy as an interested party and loss payee.</p>
    <p><strong>7.2 Policy Requirements.</strong> Each insurance policy must be placed with a reputable and solvent insurer licensed in New Zealand and approved in writing by SS Rentals, remain continuously in force for the full duration of the {{ $isLease ? 'Lease' : 'Hire' }} Term, and provide that the insurer will give SS Rentals not less than thirty (30) days' prior written notice of any material alteration, cancellation or lapse of coverage.</p>
    <p><strong>7.3 Evidence of Insurance.</strong> The Hirer shall deliver to SS Rentals a current certificate of currency within seven (7) days of the Commencement Date, and a renewal certificate within seven (7) days of each subsequent policy renewal.</p>
    <p><strong>7.4 Excess and Uninsured Losses.</strong> The Hirer is solely and absolutely responsible for the payment of all insurance excesses, deductibles and any uninsured losses arising during the {{ $isLease ? 'Lease' : 'Hire' }} Term.</p>
    <p><strong>7.5 Prohibited Use.</strong> The Hirer must not use, or permit the use of, any Vehicle in any manner that would void, prejudice or render voidable any insurance policy.</p>
    <p><strong>7.6 Claims Procedure.</strong> Upon the occurrence of any accident, theft, damage, fire or third-party claim, the Hirer must immediately (within 24 hours) notify SS Rentals in writing with full particulars and cooperate fully in the investigation and resolution of any claim.</p>
    <p><strong>7.7 Indemnity.</strong> The Hirer shall fully indemnify, defend and hold harmless SS Rentals from and against all actions, claims, demands, losses, liabilities, damages, costs and expenses arising out of or in connection with the Hirer's failure to procure or maintain insurance, or any excess, deductible or uninsured portion of any claim.</p>
    <p><strong>7.8 Survival.</strong> The Hirer's obligations under this clause 7 shall survive the expiry or earlier termination of this Agreement.</p>

    <h2>8. Fines, Penalties, and Violations</h2>
    <p>The Hirer is solely responsible for all fines, penalties, and charges incurred during the {{ $isLease ? 'Lease' : 'Hire' }} Term, including speeding infringements, overweight violations, parking fines, toll violations, and any other traffic or regulatory infractions arising from the Hirer's use of the Vehicle(s).</p>

    <h2>9. Bond</h2>
    <p>The Hirer shall pay a security bond of <strong>{{ $this->money($docHire['bond'] ?? 0) }}</strong> prior to taking possession of the Vehicle(s). The bond shall be held by SS Rentals as security against damage, loss, unpaid fines, cleaning charges, or any other costs incurred under this Agreement. The bond shall be returned within 14 days of the end of the {{ $isLease ? 'Lease' : 'Hire' }} Term, less any deductions.</p>

    <h2>10. Termination by SS Rentals</h2>
    <p><strong>10.1</strong> SS Rentals may terminate this Agreement immediately if the Hirer breaches any material term, appears insolvent, or takes any action that prejudices SS Rentals' rights.</p>
    <p><strong>10.2</strong> Upon termination, all unpaid Rental Charges become immediately due and payable.</p>
    <p><strong>10.3</strong> The Hirer must return the Vehicle(s) immediately upon termination.</p>
    <p><strong>10.4</strong> Termination by SS Rentals does not affect any accrued rights or remedies.</p>

    <h2>11. Debt Recovery</h2>
    <p>If the Hirer fails to make any payment due under this Agreement and the debt is referred to a debt collection agency or legal representative, SS Rentals shall be entitled to recover all reasonable costs of recovery from the Hirer, including collection fees, legal costs, and court filing fees.</p>

    <h2>12. Dispute Resolution</h2>
    <p>In the event of a dispute, both parties agree to first attempt to resolve the matter by good faith negotiation. If unresolved within 14 days, the parties may refer the dispute to mediation before commencing legal proceedings.</p>

    <h2>13. General</h2>
    <p><strong>13.1</strong> This Agreement constitutes the entire agreement between the parties and supersedes all prior agreements and representations.</p>
    <p><strong>13.2</strong> Variations to this Agreement must be agreed in writing (including by email) by both parties.</p>
    <p><strong>13.3</strong> This Agreement is governed by the laws of New Zealand.</p>
    <p><strong>13.4</strong> If any provision is found to be unenforceable, the remaining provisions continue in full force.</p>

    @if($isLease)
        <h2>14. Personal Guarantee</h2>
        <div class="print-doc-guarantee">
            <p><strong>PERSONAL GUARANTEE AND INDEMNITY</strong></p>
            <p>In consideration of SS Rentals entering into this Lease Agreement with the Hirer, I/we, the undersigned ("Guarantor"), unconditionally and irrevocably guarantee to SS Rentals the due and punctual performance by the Hirer of all of its obligations under this Agreement, including the payment of all Rental Charges, early termination payments, bond, fines, repair costs, and any other amounts owing.</p>
            <p><strong>14.1</strong> This guarantee is a continuing guarantee and will remain in force until all obligations under this Agreement have been fully discharged.</p>
            <p><strong>14.2</strong> The Guarantor liability is joint and several with the Hirer.</p>
            <p><strong>14.3</strong> This guarantee applies even if the Agreement is varied, extended, or modified.</p>
            <p><strong>14.4</strong> SS Rentals may enforce this guarantee without first proceeding against the Hirer.</p>
            <p><strong>14.5</strong> The Guarantor waives any right to require SS Rentals to pursue the Hirer first.</p>
            <p><strong>Guarantor Name:</strong> {{ $docHire['guarantor_name'] ?: '_______________________________________' }}</p>
            <p><strong>Guarantor Signature:</strong> ____________________________________</p>
            <p><strong>Date:</strong> ______________________________________________</p>
            <p><strong>Address:</strong> {{ $docHire['guarantor_address'] ?: '_______________________________________' }}</p>
            <p><strong>Phone:</strong> {{ $docHire['guarantor_phone'] ?: '_______________________________________' }}</p>
            <p><strong>Relationship to Hirer:</strong> ____________________________________</p>
        </div>
    @endif

    <div class="print-doc-sig-grid">
        <div>
            <p><strong>SIGNED for and on behalf of SS Rentals ({{ $isLease ? 'Lessor' : 'Owner' }}):</strong></p>
            <p>Signature: _______________________________</p>
            <p>Name: Sukhdeep Singh</p>
            <p>Position: Managing Director</p>
            <p>Date: {{ $docAgreementDate }}</p>
        </div>
        <div>
            <p><strong>SIGNED for and on behalf of the Hirer ({{ $customer['company'] ?? '-' }}):</strong></p>
            <p>Signature: _______________________________</p>
            <p>Name: {{ $customer['contact'] ?? '' }}</p>
            <p>Position: _____________________________</p>
            <p>Date: _________________________________</p>
        </div>
    </div>
    <div class="print-quote-footer">SS Rentals &nbsp;|&nbsp; 280a Peak Road, Cambridge, New Zealand &nbsp;|&nbsp; All amounts in NZD, excluding GST</div>
</div>
@endif
