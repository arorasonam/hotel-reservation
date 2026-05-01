<x-filament-panels::page>
    @include('filament.pages.reports.partials.filters')
    @php $stats = $this->getStats(); $data = $this->getTableData(); @endphp
    @include('filament.pages.reports.partials.stats')

    <x-filament::section heading="Daily Breakdown">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                    <tr>
                        @foreach($this->getTableColumns() as $col)
                            <th class="px-4 py-3">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-gray-50 dark:bg-gray-800">
                    @forelse($data as $row)
                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
                            <td class="px-4 py-3 font-medium">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $row->currency_code }}</td>
                            <td class="px-4 py-3">{{ number_format($row->total_orders) }}</td>
                            <td class="px-4 py-3">{{ $this->money($row->subtotal, $row->currency_code) }}</td>
                            <td class="px-4 py-3">{{ $this->money($row->tax, $row->currency_code) }}</td>
                            <td class="px-4 py-3 text-red-500">{{ $this->money($row->discount, $row->currency_code) }}</td>
                            <td class="px-4 py-3 font-bold text-green-600">{{ $this->money($row->revenue, $row->currency_code) }}</td>
                            <td class="px-4 py-3 font-bold">{{ $this->money($row->base_revenue, $row->base_currency_code) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No data found for selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
