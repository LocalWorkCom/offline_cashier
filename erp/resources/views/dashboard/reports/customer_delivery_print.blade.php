<!DOCTYPE html>
<html>

<head>
    <title>Customer Receipt (Delivery)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 80mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        .section {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="section">
        <h2>Restaurant Name</h2>
        <p>Branch Address</p>
        <p>Contact Info</p>
    </div>
    <div class="section">
        <p>Date: {{ now()->format('d, M Y h:i A') }}</p>
        <p>Order Number: {{ $order->order_number }}</p>
    </div>
    <div class="section">
        <p>Delivery Address: {{ $order->address->address }}</p>
        <p>Customer Name: {{ $order->client->name }}</p>
        <p>Contact: {{ $order->client->phone }}</p>
        <p>Delivery Name: [Delivery Person's Name]</p>
    </div>
    <div class="section">
        <h3>Order Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderDetails as $detail)
                    <tr>
                        <td>{{ $detail->dish->name_ar }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>${{ $detail->price_befor_tax }}</td>
                        <td>${{ $detail->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p>Subtotal: ${{ $order->total_price_befor_tax }}</p>
        <p>Discounts: ${{ $order->discount }}</p>
        <p>Tax: ${{ $order->tax_value }}</p>
        <p>Delivery Fee: ${{ $order->fees }}</p>
        <h3>Total: ${{ $order->total_price_after_tax }}</h3>
    </div>
    <div class="section">
        <p>Thank you for your order!</p>
        <div class="col-xl-4 col-lg-4 col-md-6 ms-auto mt-sm-0 mt-3">
            {!! QrCode::size(100)->generate(route('order.change.status', $order->id)) !!}
        </div>
    </div>
</body>
<script>
    window.print();
</script>

</html>
