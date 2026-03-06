<!DOCTYPE html>
<html>

<head>
    <title>{{ trans('order.Delivery Receipt') }}</title>
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
        <h2>{{ trans('order.Restaurant Name') }}</h2>
        <p>{{ trans('order.Branch Address') }}: {{ $order->branch->country->address }}</p>
    </div>
    <div class="section">
        <p>{{ trans('order.Date') }}: {{ now()->format('d, M Y h:i A') }}</p>
        <p>{{ trans('order.Order Number') }}: {{ $order->order_number }}</p>
        <p>{{ trans('order.Delivery Address') }}: {{ $order->address->address }}</p>
        <p>{{ trans('order.Customer Name') }}: {{ $order->client->name }}</p>
        <p>{{ trans('order.Customer Contact') }}: {{ $order->client->phone }}</p>
        <p>{{ trans('order.Delivery Person') }}:
            {{ $order->delivery ? $order->delivery->first_name . '-' . $order->delivery->last_name : 'No Delivery' }}
        </p>
    </div>
    <div class="section">
        <h3>{{ trans('order.Order Details') }}</h3>
        <table>
            <thead>
                <tr>
                    <th>{{ trans('order.Item') }}</th>
                    <th>{{ trans('order.Qty') }}</th>
                    <th>{{ trans('order.Price') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderDetails as $detail)
                    <tr>
                        <td>{{ $detail->dish->name_en . ' | ' . $detail->dish->name_ar }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ $order->branch->country->currency_symbol }}{{ $detail->total }}</td>
                    </tr>
                @endforeach
                @foreach ($order['addons'] as $addon)
                    <tr>
                        <td>{{ $addon->Addon->addons->name_ar . ' | ' . $addon->Addon->addons->name_en }}
                        </td>
                        <td>{{ $addon->quantity }}</td>
                        <td>{{ $order->branch->country->currency_symbol }}{{ $addon->price_before_tax }}
                        </td>
                        <td> {{ $order->branch->country->currency_symbol }} {{ $addon->price_after_tax }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <h3>{{ trans('order.Total') }}:
            {{ $order->branch->country->currency_symbol }}{{ $order->total_price_after_tax }}</h3>
    </div>
    <div class="section">
        <p>{{ trans('order.ThankOrder') }}</p>
        <div class="col-xl-4 col-lg-4 col-md-6 ms-auto mt-sm-0 mt-3">
            {!! QrCode::size(100)->generate(route('order.change.status', $order->id)) !!}
        </div>
    </div>
    {{-- <div class="section">
        <p>Payment Status: {{ $order->payment_status }}</p>
    </div> --}}
</body>
<script>
    window.print();
</script>

</html>
