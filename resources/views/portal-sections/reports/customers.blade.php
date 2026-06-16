@php
    $reportCustomers = collect($customers)
        ->reject(fn ($customer) => ($customer['status'] ?? '') === 'blacklisted')
        ->sortBy('company')
        ->values();
@endphp

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Status</th>
                <th>Weekly Rate</th>
                <th>YTD Revenue</th>
                <th>Outstanding</th>
                <th>Credit Rating</th>
                <th>Payment Terms</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportCustomers as $customer)
                @php
                    $weeklyRate = (float) ($customer['weekly_truck'] ?? 0) + (float) ($customer['weekly_trailer'] ?? 0);
                    $creditRating = (string) ($customer['credit_rating'] ?? '-');
                    $creditClass = str_starts_with($creditRating, 'A') ? 'badge badge-blue' : ($creditRating === 'F' ? 'badge badge-red' : 'badge badge-orange');
                @endphp

                <tr>
                    <td class="fw-700">{{ $customer['company'] ?? '-' }}</td>
                    <td><span class="{{ $this->statusClass($customer['status'] ?? null) }}">{{ $this->statusLabel($customer['status'] ?? null) }}</span></td>
                    <td>{{ $weeklyRate > 0 ? $this->money($weeklyRate).'/wk' : '-' }}</td>
                    <td style="font-weight:700;color:var(--green)">{{ $this->money($customer['ytd_revenue'] ?? 0) }}</td>
                    <td style="font-weight:700;color:{{ ($customer['outstanding'] ?? 0) > 0 ? 'var(--red)' : 'var(--text2)' }}">{{ ($customer['outstanding'] ?? 0) > 0 ? $this->money($customer['outstanding']) : '-' }}</td>
                    <td><span class="{{ $creditClass }}">{{ $creditRating }}</span></td>
                    <td style="font-size:12px;color:var(--text2)">{{ $customer['payment_terms'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
