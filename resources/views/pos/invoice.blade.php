<!DOCTYPE html>
<html>
<head>
<title>POS Invoice</title>

<style>
body{
    font-family: Arial;
    font-size:14px;
}

table{
    width:100%;
    border-collapse: collapse;
}

th, td{
    padding:6px;
    border-bottom:1px solid #ddd;
}

.header{
    text-align:center;
}

.total{
    font-weight:bold;
}
</style>

</head>

<body>

@php
    $orderCurrency = $order->currency_code ?? 'INR';
    $money = fn (float|int|string|null $amount, ?string $currency = null): string => \Illuminate\Support\Number::currency((float) $amount, $currency ?: $orderCurrency);
    $taxSummary = collect();

    foreach ($order->items as $lineItem) {
        foreach ($lineItem->tax_breakdown as $tax) {
            $key = $tax['name'].'|'.$tax['percentage'];
            $current = $taxSummary->get($key, [
                'name' => $tax['name'],
                'percentage' => $tax['percentage'],
                'amount' => 0,
            ]);
            $current['amount'] += $tax['amount'];
            $taxSummary->put($key, $current);
        }
    }
@endphp

<div class="header">
<h2>{{ $order->hotel->name ?? 'Hotel POS' }}</h2>
<h4>{{ $order->outlet->name ?? 'Hotel Outlet' }}</h4>
<p>Invoice #{{ $order->id }}</p>
<p>Date: {{ $order->created_at }}</p>
<p>Currency: {{ $orderCurrency }}</p>
</div>

<hr>

<p>
Guest:
{{ $order->guest->name ?? 'Walk-in Guest' }}
</p>

@if($order->table_no)
<p>
Table No:
{{ $order->table_no }}
</p>
@endif

@if($order->reservationRoomDetail)
<p>
Room:
{{ $order->reservationRoomDetail->room_number }}
</p>
@endif

<hr>

<table>

<thead>

<tr>
<th>Item</th>
<th>Qty</th>
<th>Price</th>
<th>Taxes</th>
<th>Tax Amount</th>
<th>Total</th>
</tr>

</thead>

<tbody>

@foreach($order->items as $item)

<tr style="text-align:center">
<td>{{ $item->item->name }}</td>
<td>{{ $item->quantity }}</td>
<td>{{ $money($item->price, $item->currency_code ?? $orderCurrency) }}</td>
<td>
@forelse($item->tax_breakdown as $tax)
{{ $tax['name'] }} {{ number_format($tax['percentage'], 2) }}% ({{ $money($tax['amount'], $item->currency_code ?? $orderCurrency) }})<br>
@empty
0%
@endforelse
</td>
<td>{{ $money($item->tax_amount, $item->currency_code ?? $orderCurrency) }}</td>
<td>{{ $money($item->total, $item->currency_code ?? $orderCurrency) }}</td>
</tr>

@endforeach

</tbody>

</table>

<hr>

<p>Subtotal: {{ $money($order->subtotal) }}</p>

@if($order->tax_amount)
@foreach($taxSummary as $tax)
<p>{{ $tax['name'] }} {{ number_format($tax['percentage'], 2) }}%: {{ $money($tax['amount']) }}</p>
@endforeach
<p>Tax Total: {{ $money($order->tax_amount) }}</p>
@endif

@if($order->discount_amount)
<p>Discount: {{ $money($order->discount_amount) }}</p>
@endif

<p class="total">
Grand Total: {{ $money($order->grand_total) }}
</p>

@if(($order->currency_code ?? 'INR') !== 'INR')
<p>Base Total: {{ $money($order->base_grand_total ?? $order->grand_total, 'INR') }}</p>
<p>Exchange Rate Used: {{ number_format((float) ($order->exchange_rate_used ?? 1), 6) }}</p>
@endif

<hr>

<h4>Payments</h4>

@forelse($order->payments as $payment)
<p>
{{ ucfirst($payment->payment_method) }}
:
{{ $money($payment->amount, $payment->currency_code ?? $orderCurrency) }}
@if(($payment->currency_code ?? $orderCurrency) !== 'INR')
    (Base: {{ $money($payment->base_amount ?? $payment->amount, 'INR') }})
@endif
</p>
@empty
<p>No payments recorded.</p>
@endforelse

<hr>

<p style="text-align:center">
Thank you for visiting!
</p>

</body>
</html>
