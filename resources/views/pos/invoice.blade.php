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
    $money = fn ($amount, $currencyCode = null) => ($currencyCode ?: $order->currency_code ?: 'INR').' '.number_format((float) $amount, 2);
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
<td>{{ $money($item->price, $item->currency_code) }}</td>
<td>
@forelse($item->tax_breakdown as $tax)
{{ $tax['name'] }} {{ number_format($tax['percentage'], 2) }}% ({{ $money($tax['amount'], $item->currency_code) }})<br>
@empty
0%
@endforelse
</td>
<td>{{ $money($item->tax_amount, $item->currency_code) }}</td>
<td>{{ $money($item->total, $item->currency_code) }}</td>
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

@if($order->base_currency_code && $order->base_currency_code !== $order->currency_code)
<p>Base Total: {{ $money($order->base_amount, $order->base_currency_code) }}</p>
<p>Exchange Rate: 1 {{ $order->currency_code }} = {{ number_format((float) $order->exchange_rate, 8) }} {{ $order->base_currency_code }}</p>
@endif

<hr>

<h4>Payments</h4>

@forelse($order->payments as $payment)
<p>
{{ ucfirst($payment->payment_method) }}
:
{{ $money($payment->amount, $payment->currency_code) }}
@if($payment->base_currency_code && $payment->base_currency_code !== $payment->currency_code)
({{ $money($payment->base_amount, $payment->base_currency_code) }})
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
