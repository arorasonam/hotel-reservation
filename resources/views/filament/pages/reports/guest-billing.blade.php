<x-filament-panels::page>
    @include('filament.pages.reports.partials.filters')
    @php $stats = $this->getStats(); @endphp
    @include('filament.pages.reports.partials.stats')
 
    <x-filament::section heading="Guest-wise Room Charge Billing">
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
                        <td class="px-4 py-3 font-semibold">{{ $row->guest_name }}</td>
                        <td class="px-4 py-3 text-blue-600 font-mono text-xs">{{ $row->reservation_number }}</td>
                        <td class="px-4 py-3">{{ $row->room_id }}</td>
                        <td class="px-4 py-3">{{ $row->outlet_name }}</td>
                        <td class="px-4 py-3">{{ $row->total_orders }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->subtotal, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->tax, 2) }}</td>
                        <td class="px-4 py-3 text-red-500">₹{{ number_format($row->discount, 2) }}</td>
                        <td class="px-4 py-3 font-bold text-green-600">₹{{ number_format($row->grand_total, 2) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($row->first_order)->format('d M, h:i A') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($row->last_order)->format('d M, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">No room charge orders found.</td></tr>
                    @endforelse
                </tbody>
                @if($this->getTableData()->isNotEmpty())
                <tfoot class="bg-gray-100 dark:bg-gray-700 font-bold">
                    <tr>
                        <td colspan="5" class="px-4 py-3">Totals</td>
                        <td class="px-4 py-3">₹{{ number_format($this->getTableData()->sum('subtotal'), 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($this->getTableData()->sum('tax'), 2) }}</td>
                        <td class="px-4 py-3 text-red-500">₹{{ number_format($this->getTableData()->sum('discount'), 2) }}</td>
                        <td class="px-4 py-3 text-green-600">₹{{ number_format($this->getTableData()->sum('grand_total'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>