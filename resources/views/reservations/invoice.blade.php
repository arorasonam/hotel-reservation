<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation Invoice</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            margin: 24px;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .header,
        .meta,
        .summary {
            margin-bottom: 24px;
        }

        .grid {
            width: 100%;
        }

        .grid td {
            vertical-align: top;
            padding: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 12px;
        }

        th:last-child,
        td:last-child {
            text-align: right;
        }

        .summary-table {
            width: 320px;
            margin-left: auto;
        }

        .summary-table td {
            border: none;
            padding: 4px 0;
            font-size: 13px;
        }

        .summary-table tr:last-child td {
            font-weight: 700;
            border-top: 1px solid #d1d5db;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    @php
        $primaryGuest = $reservation->reservationGuests->firstWhere('is_primary', true) ?? $reservation->reservationGuests->first();
        $guestName = $primaryGuest
            ? trim($primaryGuest->first_name . ' ' . $primaryGuest->last_name)
            : trim(($reservation->first_name ?? '') . ' ' . ($reservation->last_name ?? ''));
    @endphp

    <div class="header">
        <h2>{{ $reservation->hotel->name ?? 'Hotel Reservation' }}</h2>
        <p>Reservation Invoice #{{ $reservation->reservation_number ?? $reservation->id }}</p>
        <p>Issued {{ now()->format('d M Y h:i A') }}</p>
    </div>

    <table class="grid meta">
        <tr>
            <td>
                <h3>Guest</h3>
                <p>{{ $guestName }}</p>
                <p>{{ $primaryGuest?->email ?? $reservation->email }}</p>
                <p>{{ $primaryGuest?->phone ?? $reservation->phone }}</p>
            </td>
            <td>
                <h3>Stay</h3>
                <p>Check-in: {{ optional($reservation->check_in)->format('d M Y') ?? $reservation->check_in }}</p>
                <p>Check-out: {{ optional($reservation->check_out)->format('d M Y') ?? $reservation->check_out }}</p>
                <p>Room: {{ $reservation->room_no ?: 'N/A' }}</p>
            </td>
        </tr>
    </table>

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
            @forelse($reservation->folios as $entry)
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

    <table class="summary-table summary">
        <tr>
            <td>Total Debits</td>
            <td>{{ number_format($summary['debits'], 2) }}</td>
        </tr>
        <tr>
            <td>Total Credits</td>
            <td>{{ number_format($summary['credits'], 2) }}</td>
        </tr>
        <tr>
            <td>Balance</td>
            <td>{{ number_format($summary['balance'], 2) }}</td>
        </tr>
    </table>
</body>
</html>
