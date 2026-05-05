@php
    $showBase = $showBase ?? false;
    $money = fn (float|int|string|null $amount, ?string $currency = 'INR'): string => \Illuminate\Support\Number::currency((float) $amount, $currency ?: 'INR');
@endphp

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Type</th>
            <th>Reference</th>
            <th>Debit</th>
            <th>Credit</th>
            @if($showBase)
                <th>Base Amount</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse($entries as $entry)
            @php
                $currencyCode = $entry->currency_code ?? 'INR';
                $baseAmount = $entry->base_amount ?? $entry->amount;
            @endphp
            <tr>
                <td>{{ optional($entry->posted_at)->format('d M Y h:i A') }}</td>
                <td>{{ $entry->description }}</td>
                <td>{{ ucfirst($entry->entry_type) }}</td>
                <td>{{ $entry->reference }}</td>
                <td>{{ $entry->type === 'debit' ? $money($entry->amount, $currencyCode) : '-' }}</td>
                <td>{{ $entry->type === 'credit' ? $money($entry->amount, $currencyCode) : '-' }}</td>
                @if($showBase)
                    <td>{{ $money($baseAmount, 'INR') }}</td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $showBase ? 7 : 6 }}">No folio entries available.</td>
            </tr>
        @endforelse
    </tbody>
</table>
