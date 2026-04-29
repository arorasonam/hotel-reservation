<x-filament-panels::page>
    @include('filament.pages.reports.partials.filters')
    @php $stats = $this->getStats(); @endphp
    @include('filament.pages.reports.partials.stats')
 
    <x-filament::section heading="GST Breakup by Tax Slab & Category">
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
                        <td class="px-4 py-3 font-medium">{{ $row->category_name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-bold">
                                {{ $row->tax_percentage }}%
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ number_format($row->orders) }}</td>
                        <td class="px-4 py-3">{{ number_format($row->qty_sold) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->taxable_amount, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->cgst, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->sgst, 2) }}</td>
                        <td class="px-4 py-3 font-semibold">₹{{ number_format($row->tax_collected, 2) }}</td>
                        <td class="px-4 py-3 font-bold text-green-600">₹{{ number_format($row->gross_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No data found.</td></tr>
                    @endforelse
                </tbody>
                @if($this->getTableData()->isNotEmpty())
                <tfoot class="bg-gray-100 dark:bg-gray-700 font-bold">
                    <tr>
                        <td colspan="4" class="px-4 py-3">Totals</td>
                        <td class="px-4 py-3">₹{{ number_format($this->getTableData()->sum('taxable_amount'), 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($this->getTableData()->sum('cgst'), 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($this->getTableData()->sum('sgst'), 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($this->getTableData()->sum('tax_collected'), 2) }}</td>
                        <td class="px-4 py-3 text-green-600">₹{{ number_format($this->getTableData()->sum('gross_amount'), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>