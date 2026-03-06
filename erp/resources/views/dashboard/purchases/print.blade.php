@extends('layouts.print')

@section('style')
    <style>
        body {
            width: 80mm;
            font-size: 12px;
        }

        .printable-area {
            width: 100%;
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
    </style>
@endsection

@section('content')
    <div class="printable-area">
        <h4>Purchase Invoice: #{{ $purchase->id }}</h4>
        <p>Vendor: {{ $purchase->vendor->name_en }}</p>
        <p>Date: {{ \Carbon\Carbon::parse($purchase->created_at)->format('d,M Y h:i A') }}</p>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->purchaseInvoicesDetails as $detail)
                    <tr>
                        <td>{{ $detail->product->name_ar }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ $detail->price }}</td>
                        <td>{{ $detail->total_price }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p>Total Amount: ${{ $purchase->purchaseInvoicesDetails->sum('total_price') }}</p>
    </div>
    <script>
        window.print();
        window.onafterprint = function() {
            window.close();
        };
    </script>
@endsection
