<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Type</th>
            <th>Reference</th>
            <th>Debit</th>
            <th>Credit</th>
        </tr>
    </thead>
    <tbody>
        @forelse($entries as $entry)
            @php
                $money = fn ($amount, $currencyCode = null) => ($currencyCode ?: $entry->currency_code ?: 'INR') . ' ' . number_format((float) $amount, 2);
            @endphp
            <tr>
                <td>{{ optional($entry->posted_at)->format('d M Y h:i A') }}</td>
                <td>{{ $entry->description }}</td>
                <td>{{ ucfirst($entry->entry_type) }}</td>
                <td>{{ $entry->reference }}</td>
                <td>{{ $entry->type === 'debit' ? $money($entry->amount) : '-' }}</td>
                <td>{{ $entry->type === 'credit' ? $money($entry->amount) : '-' }}</td>
            </tr>
            @if($entry->base_currency_code && $entry->base_currency_code !== $entry->currency_code)
                <tr>
                    <td></td>
                    <td colspan="3">Base amount @ {{ number_format((float) $entry->exchange_rate, 8) }}</td>
                    <td>{{ $entry->type === 'debit' ? $money($entry->base_amount, $entry->base_currency_code) : '-' }}</td>
                    <td>{{ $entry->type === 'credit' ? $money($entry->base_amount, $entry->base_currency_code) : '-' }}</td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="6">No folio entries available.</td>
            </tr>
        @endforelse
    </tbody>
</table>
