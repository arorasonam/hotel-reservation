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

        .folio-section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }

        .folio-heading {
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            padding: 10px 12px;
            margin-bottom: 8px;
        }

        .folio-heading h3 {
            font-size: 14px;
        }

        .folio-heading p {
            color: #4b5563;
            font-size: 11px;
            margin-top: 3px;
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

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    @php
        $folioScope = $folioScope ?? 'all';
        $folioTitle = $folioTitle ?? 'All Folios';
        $primaryGuest = $reservation->reservationGuests->firstWhere('is_primary', true) ?? $reservation->reservationGuests->first();
        $guestName = $primaryGuest
            ? trim($primaryGuest->first_name . ' ' . $primaryGuest->last_name)
            : trim(($reservation->first_name ?? '') . ' ' . ($reservation->last_name ?? ''));

        $roomSections = collect();
        $masterEntries = $folioScope === 'room'
            ? collect()
            : $reservation->folios
                ->whereNull('reservation_room_id')
                ->whereNull('reservation_room_detail_id');

        if ($folioScope === 'room') {
            $roomSections = collect([$selectedRoom]);
        } elseif ($folioScope === 'all') {
            $roomSections = $reservation->roomCategories->flatMap->roomDetails;
        }
    @endphp

    <div class="header">
        <h2>{{ $reservation->hotel->name ?? 'Hotel Reservation' }}</h2>
        <p>{{ $folioTitle }} #{{ $reservation->reservation_number ?? $reservation->id }}</p>
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
                <p>Folio: {{ $folioTitle }}</p>
            </td>
        </tr>
    </table>

    @foreach($roomSections as $room)
        @php
            $entries = $room->folios;
            $debits = (float) $entries->where('type', 'debit')->sum('amount');
            $credits = (float) $entries->where('type', 'credit')->sum('amount');
        @endphp

        <div class="folio-section">
            <div class="folio-heading">
                <h3>Room {{ $room->room_number ?: 'Auto' }} Charges</h3>
                <p>
                    {{ $room->category?->roomType?->name ?? 'Room' }}
                    @if($room->category?->mealPlan)
                        | {{ $room->category->mealPlan->name }}
                    @endif
                    | Balance {{ number_format($debits - $credits, 2) }}
                </p>
            </div>

            @include('reservations.partials.folio-entries', ['entries' => $entries])
        </div>
    @endforeach

    @if($masterEntries->isNotEmpty() || $folioScope === 'master')
        @php
            $debits = (float) $masterEntries->where('type', 'debit')->sum('amount');
            $credits = (float) $masterEntries->where('type', 'credit')->sum('amount');
        @endphp

        <div class="folio-section">
            <div class="folio-heading">
                <h3>Master Folio</h3>
                <p>Reservation-level entries | Balance {{ number_format($debits - $credits, 2) }}</p>
            </div>

            @include('reservations.partials.folio-entries', ['entries' => $masterEntries])
        </div>
    @endif

    @if($folioScope === 'all' && $reservation->folios->isEmpty())
        <table>
            <tbody>
                <tr>
                    <td>No folio entries available.</td>
                </tr>
            </tbody>
        </table>
    @endif

    @if($folioScope !== 'all' && ($folioScope === 'room' ? $selectedRoom->folios->isEmpty() : $masterEntries->isEmpty()))
        <table>
            <tbody>
                <tr>
                    <td>No folio entries available.</td>
                </tr>
            </tbody>
        </table>
    @endif

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
