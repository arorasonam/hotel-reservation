<x-filament-panels::page>
    @include('filament.pages.reports.partials.filters')
    @php $stats = $this->getStats(); @endphp
    @include('filament.pages.reports.partials.stats')
 
    <x-filament::section heading="Cancelled Orders Detail">
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
                        <td class="px-4 py-3 font-mono text-xs font-bold">{{ $row->order_number }}</td>
                        <td class="px-4 py-3">{{ $row->outlet_name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $row->order_type === 'room_charge' ? 'bg-blue-100 text-blue-700' :
                                   ($row->order_type === 'walk_in' ? 'bg-green-100 text-green-700' :
                                   'bg-orange-100 text-orange-700') }}">
                                {{ str_replace('_', ' ', ucfirst($row->order_type)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $row->table_no ?? '—' }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->subtotal, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($row->tax_amount, 2) }}</td>
                        <td class="px-4 py-3 text-red-500">₹{{ number_format($row->discount_amount, 2) }}</td>
                        <td class="px-4 py-3 font-bold">₹{{ number_format($row->grand_total, 2) }}</td>
                        <td class="px-4 py-3">{{ $row->created_by }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($row->created_at)->format('d M, h:i A') }}</td>
                        <td class="px-4 py-3 text-xs text-red-400">{{ \Carbon\Carbon::parse($row->cancelled_at)->format('d M, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">No cancelled orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>