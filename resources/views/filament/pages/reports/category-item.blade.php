<x-filament-panels::page>
    @include('filament.pages.reports.partials.filters')
    @php $stats = $this->getStats(); $data = $this->getTableData(); @endphp
    @include('filament.pages.reports.partials.stats')
 
    <x-filament::section heading="Sales by {{ ucfirst($this->group_by) }}">
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
                    @forelse($data as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $row->category_name }}</td>
                        @if($this->group_by === 'item')
                        <td class="px-4 py-3 font-medium">{{ $row->item_name }}</td>
                        <td class="px-4 py-3">{{ number_format($row->qty_sold) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->avg_price, 2) }}</td>
                        @else
                        <td class="px-4 py-3">{{ number_format($row->qty_sold) }}</td>
                        @endif
                        <td class="px-4 py-3">₹{{ number_format($row->subtotal, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->tax, 2) }}</td>
                        <td class="px-4 py-3 font-bold text-green-600">₹{{ number_format($row->revenue, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>