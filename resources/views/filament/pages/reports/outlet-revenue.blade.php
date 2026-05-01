<x-filament-panels::page>
    @include('filament.pages.reports.partials.filters')
    @php $stats = $this->getStats(); @endphp
    @include('filament.pages.reports.partials.stats')

    <x-filament::section heading="Revenue by Outlet">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-600 dark:text-gray-300">
                    <tr>
                        @foreach($this->getTableColumns() as $col)
                            <th class="px-4 py-3">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->getTableData() as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 font-semibold">{{ $row->outlet_name }}</td>
                            <td class="px-4 py-3">{{ $row->currency_code }}</td>
                            <td class="px-4 py-3">{{ number_format($row->total_orders) }}</td>
                            <td class="px-4 py-3">{{ $this->money($row->subtotal, $row->currency_code) }}</td>
                            <td class="px-4 py-3">{{ $this->money($row->tax, $row->currency_code) }}</td>
                            <td class="px-4 py-3 text-red-500">{{ $this->money($row->discount, $row->currency_code) }}</td>
                            <td class="px-4 py-3 font-bold text-green-600">{{ $this->money($row->revenue, $row->currency_code) }}</td>
                            <td class="px-4 py-3 font-bold">{{ $this->money($row->base_revenue, $row->base_currency_code) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
