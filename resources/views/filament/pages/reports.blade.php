<x-filament-panels::page>
    @php
        $baseAmount = fn ($row): float => (float) ($row->base_total_amount ?? $row->total_amount ?? $row->total_bill ?? 0);
        $reportTotal = $this->reportData->sum(fn ($row): float => $baseAmount($row));
        $igstTotal = $this->reportData
            ->where('is_igst_applied', true)
            ->sum(fn ($row): float => $baseAmount($row) * 0.18);
    @endphp

    <form wire:submit="submit">
        {{ $this->form }}
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Total Revenue Card --}}
        <x-filament::section>
            <div class="text-sm text-gray-500">Total Revenue (Period)</div>
            <div class="text-2xl font-bold">{{ \Illuminate\Support\Number::currency($reportTotal, 'INR') }}</div>
        </x-filament::section>

        {{-- Occupancy Card --}}
        <x-filament::section>
            <div class="text-sm text-gray-500">Nights Sold</div>
            <div class="text-2xl font-bold">{{ $this->reportData->count() }}</div>
        </x-filament::section>

        {{-- Tax Card --}}
        <x-filament::section>
            <div class="text-sm text-gray-500">Estimated IGST</div>
            <div class="text-2xl font-bold text-danger-600">{{ \Illuminate\Support\Number::currency($igstTotal, 'INR') }}</div>
        </x-filament::section>
    </div>

    <x-filament::section>
        <x-slot name="heading">Detailed Transaction Log</x-slot>

        <table class="w-full text-left divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-2">Ref ID</th>
                    <th class="px-4 py-2">Guest</th>
                    <th class="px-4 py-2">Property</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($this->reportData as $row)
                <tr>
                    <td class="px-4 py-2">{{ $row->reservation_number }}</td>
                    <td class="px-4 py-2">{{ $row->reservationGuests->full_name ?? 'N/A' }}</td>
                    <td class="px-4 py-2 text-sm">{{ $row->hotel->name }}</td>
                    <td class="px-4 py-2">
                        <x-filament::badge :color="$row->status === 'checked_in' ? 'success' : 'gray'">
                            {{ ucfirst($row->status) }}
                        </x-filament::badge>
                    </td>
                    <td class="px-4 py-2 text-right font-mono">{{ \Illuminate\Support\Number::currency($baseAmount($row), 'INR') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::section>
</x-filament-panels::page>
