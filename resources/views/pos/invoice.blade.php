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
<th>Tax Percentage</th>
<th>Tax Amount</th>
<th>Total</th>
</tr>

</thead>

<tbody>

@foreach($order->items as $item)

<tr style="text-align:center">
<td>{{ $item->item->name }}</td>
<td>{{ $item->quantity }}</td>
<td>{{ $item->price }}</td>
<td>{{ $item->tax_percentage }}</td>
<td>{{ $item->tax_amount }}</td>
<td>{{ $item->total }}</td>
</tr>

@endforeach

</tbody>

</table>

<hr>

<p>Subtotal: Rs. {{ $order->subtotal }}</p>

@if($order->tax_amount)
<p>Tax: Rs. {{ $order->tax_amount }}</p>
@endif

@if($order->discount_amount)
<p>Discount: Rs.  {{ $order->discount_amount }}</p>
@endif

<p class="total">
Grand Total: Rs. {{ $order->grand_total }}
</p>

<hr>

<h4>Payments</h4>

@forelse($order->payments as $payment)
<p>
{{ ucfirst($payment->payment_method) }}
:
Rs. {{ $payment->amount }}
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
