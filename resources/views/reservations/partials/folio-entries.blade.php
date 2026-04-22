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
            <tr>
                <td>{{ optional($entry->posted_at)->format('d M Y h:i A') }}</td>
                <td>{{ $entry->description }}</td>
                <td>{{ ucfirst($entry->entry_type) }}</td>
                <td>{{ $entry->reference }}</td>
                <td>{{ $entry->type === 'debit' ? number_format((float) $entry->amount, 2) : '-' }}</td>
                <td>{{ $entry->type === 'credit' ? number_format((float) $entry->amount, 2) : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">No folio entries available.</td>
            </tr>
        @endforelse
    </tbody>
</table>
