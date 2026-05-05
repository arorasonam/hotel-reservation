<div class="block w-full" style="min-width: 100%;">
    @php
        $currencyCode = $record?->currency_code ?? 'INR';
    @endphp

    <div class="space-y-2">

        <div class="flex items-center w-full" style="display: flex; justify-content: space-between; width: 100%;">
            <span class="text-sm text-gray-600">Room Charges</span>
            <span class="text-sm text-gray-900 tabular-nums text-right" style="text-align: right;">
                {{ \Illuminate\Support\Number::currency($record->base_price ?? $record->total_room_charge ?? 0, $currencyCode) }}
            </span>
        </div>
        <div class="flex items-center w-full" style="display: flex; justify-content: space-between; width: 100%;">
            <span class="text-sm text-gray-600">Addons</span>
            <span class="text-sm text-gray-900 tabular-nums text-right" style="text-align: right;">
                {{ \Illuminate\Support\Number::currency($record->pos_orders_sum_total ?? 0, $currencyCode) }}
            </span>
        </div>
        <div class="w-full border-t border-dotted border-gray-300 my-2"></div>
        <div class="flex items-center w-full" style="display: flex; justify-content: space-between; width: 100%;">
            <span class="text-sm text-gray-600">Discount</span>
            <span class="text-sm text-gray-900 tabular-nums text-right" style="text-align: right;">
                ({{ \Illuminate\Support\Number::currency($record->discount_amount ?? 0, $currencyCode) }})
            </span>
        </div>

        <div class="w-full border-t border-dotted border-gray-300 my-2"></div>

        <div class="flex items-center w-full" style="display: flex; justify-content: space-between; width: 100%;">
            <span class="text-sm text-gray-800 font-bold">Sub Total</span>
            <span class="text-sm text-gray-900 font-bold tabular-nums text-right" style="text-align: right;">
                {{ \Illuminate\Support\Number::currency($record->total_amount ?? $record->sub_total ?? 0, $currencyCode) }}
            </span>
        </div>

        <div class="w-full border-t-2 border-double border-gray-800 mt-4 pt-2">
            <div class="flex items-center w-full" style="display: flex; justify-content: space-between; width: 100%;">
                <span class="text-base font-bold text-black uppercase">Net Payable at Hotel</span>
                <span class="text-base font-bold text-black tabular-nums text-right" style="text-align: right;">
                    {{ \Illuminate\Support\Number::currency($record->remaining_balance ?? $record->folio_balance ?? 0, 'INR') }}
                </span>
            </div>
        </div>

    </div>
</div>
