<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }

        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .invoice-header img {
            max-width: 150px;
        }

        .invoice-header .title {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .invoice-header .order-number {
            font-size: 18px;
            color: #555;
        }

        /* Billing Information */
        .billing-info {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .billing-info p {
            margin: 5px 0;
        }

        .billing-info .fw-bold {
            font-weight: bold;
            color: #333;
        }

        /* Table Styles */
        .invoice-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .invoice-table th {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
        }

        .invoice-table .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .invoice-table .text-success {
            color: #28a745;
        }

        /* Footer */
        .invoice-footer {
            text-align: right;
            margin-top: 20px;
        }

        /* QR Code */
        .qr-code {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        /* PDF Export Styles */
        @media print {
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                border-radius: 0;
            }

            .print-hide {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div>
                <img src="{{ $order->site_logo }}" alt="Company Logo">
            </div>
            <div class="title">
                SALES INVOICE: <span class="order-number">{{ $order->order_number }}</span>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-info">
            <div>
                <p class="fw-bold">Billing To:</p>
                <p>{{ $order->client->name }}</p>
                <p>{{ $order->client->phone }}</p>
                <p>{{ $order->client->email }}</p>
                <p>{{ $order->address->city }} - {{ $order->address->address }}</p>
            </div>
            <div class="qr-code">
                <img src="data:image/png;base64,{{ $order['qr'] }}" alt="QR Code">
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <p><strong>Invoice ID:</strong> {{ $order->invoice_number }}</p>
            <p><strong>Date Issued:</strong> {{ \Carbon\Carbon::parse($order->date)->format('d, M Y') }} -
                <span class="text-muted">{{ \Carbon\Carbon::parse($order->time)->format('h:i A') }}</span>
            </p>
            <p><strong>Due Amount:</strong> ${{ $order->total_price_after_tax }}</p>
        </div>

        <!-- Items Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price Per Unit</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
             
                @php $temp_offer = 0; @endphp
                @foreach ($order['details'] as $detail)
                    @if ($detail->offer_id && $detail->offer_id != $temp_offer)
                        {{-- Display Offer Header --}}
                        @php $temp_offer = $detail->offer_id; @endphp
                        <tr> {{-- Offer row colored --}}
                            <td>عرض {{ $detail->offer->name_ar }}</td>
                            <td></td>
                            @if ($detail->offer->discount_type == 'fixed')
                                <td>${{ $detail->offer->discount_value }}</td>
                            @else
                                <td>${{ $detail->total }}</td>
                            @endif
                            @if ($detail->offer->discount_type == 'fixed')
                                <td>${{ $detail->offer->discount_value }}</td>
                            @else
                                <td>${{ $detail->price_after_tax }}</td>
                            @endif
                        </tr>
                        {{-- Display Offer Details --}}
                        @foreach ($detail->offer->details as $offer_detail)
                            <tr> {{-- Offer details row colored --}}
                                <td> {{ $offer_detail->dish->name_ar }}</td>
                                <td>{{ $offer_detail->count }}</td>
                                <td>{{ $detail->offer->discount_type == 'fixed' ? '$0' : $detail->total }}
                                </td>

                                <td>${{ $detail->price_after_tax }}</td>
                            </tr>
                        @endforeach
                    @elseif (!$detail->offer_id)
                        {{-- Display Individual Dish Details --}}
                        <tr class="table-default"> {{-- Default styling for individual dishes --}}
                            <td>{{ $detail->dish->name_ar . ' | ' . $detail->dish->name_en }}
                            </td>
                            <td>{{ $detail->quantity }}</td>
                            <td>${{ $detail->price_befor_tax }}</td>
                            <td>${{ $detail->total }}</td>

                        </tr>
                    @endif
                @endforeach
                @foreach ($order['addons'] as $addon)
                    <tr>
                        <td>{{ $addon->Addon->addons->name_ar }}</td>
                        <td>{{ $addon->quantity }}</td>
                        <td>${{ $addon->price_befor_tax }}</td>
                        <td>${{ $addon->total }}</td>
                    </tr>
                @endforeach
                <!-- Totals Section -->
                <tr class="total-row">
                    <td colspan="3" class="text-end">Total Before Tax:</td>
                    <td>${{ $order->total_price_befor_tax }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-end">Tax ({{ getSetting('tax_percentage') }}%):</td>
                    <td>${{ $order->tax_value }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-end">Fees:</td>
                    <td>${{ $order->fees }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-end">Total After Tax:</td>
                    <td class="text-success">${{ $order->total_price_after_tax }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
