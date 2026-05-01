<x-filament-panels::page>
    @include('filament.pages.reports.partials.filters')
    @php $stats = $this->getStats(); @endphp
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
                <tbody class="g-gray-50 dark:bg-gray-800">
                    @forelse($this->getTableData() as $row)
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
                        <td class="px-4 py-3 font-medium">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ number_format($row->total_orders) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->subtotal, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->tax, 2) }}</td>
                        <td class="px-4 py-3 text-red-500">₹{{ number_format($row->discount, 2) }}</td>
                        <td class="px-4 py-3 font-bold text-green-600">₹{{ number_format($row->revenue, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No data found for selected period.</td></tr>
                    @endforelse
                </tbody>
                @if($this->getTableData()->isNotEmpty())
                <tfoot class="bg-gray-100 dark:bg-gray-700 font-bold">
                    <tr>
                        <td class="px-4 py-3">Total</td>
                        <td class="px-4 py-3">{{ number_format($this->getTableData()->sum('total_orders')) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($this->getTableData()->sum('subtotal'), 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($this->getTableData()->sum('tax'), 2) }}</td>
                        <td class="px-4 py-3 text-red-500">₹{{ number_format($this->getTableData()->sum('discount'), 2) }}</td>
                        <td class="px-4 py-3 text-green-600">₹{{ number_format($this->getTableData()->sum('revenue'), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>