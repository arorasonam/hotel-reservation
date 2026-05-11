<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Tax</th>
            <th>Type</th>
            <th>Reference</th>
            <th>Debit</th>
            <th>Credit</th>
        </tr>
    </thead>
    <tbody>
        @forelse($entries as $entry)
            <tr>
                <td>{{ optional($entry->posted_at)->format('d M Y h:i A') }}</td>
                <td>{{ $entry->description }}</td>
                <td>
                    @if($entry->tax_breakdown)
                        @foreach($entry->tax_breakdown as $tax)
                            {{ strtoupper($tax['type']) }} - {{ $tax['name'] }} {{ number_format((float) $tax['percentage'], 2) }}%
                            ({{ number_format((float) $tax['amount'], 2) }})
                            @if(! $loop->last)<br>@endif
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td>{{ ucfirst($entry->entry_type) }}</td>
                <td>{{ $entry->reference }}</td>
                <td>{{ $entry->type === 'debit' ? number_format((float) $entry->amount, 2) : '-' }}</td>
                <td>{{ $entry->type === 'credit' ? number_format((float) $entry->amount, 2) : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No folio entries available.</td>
            </tr>
        @endforelse
    </tbody>
</table>
