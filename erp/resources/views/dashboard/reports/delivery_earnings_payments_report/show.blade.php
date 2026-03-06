@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('report.DeliveryDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('reports.delivery_earnings_payments_report.list') }}">
                            @lang('report.delivery_earnings_payments_report')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @lang('report.DeliveryDetails')
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('report.DeliveryDetails')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <!-- Delivery ID -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('category.ID')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['delivery_id'] ?? __('report.NotAvailable') }}
                                    </p>
                                </div>

                                <!-- Delivery Name -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.DeliveryName')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['first_name'] ?? '' }}
                                        {{ $DeliveryReport['delivery']['last_name'] ?? '' }}
                                    </p>
                                </div>

                                <!-- Total Orders -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.TotalCompletedOrders')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['total_orders'] ?? __('report.NotAvailable') }}
                                    </p>
                                </div>

                                <!-- Total Earnings -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.TotalEarnings')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['total_earnings'] ?? '0.00' }}
                                    </p>
                                </div>

                                <!-- BranchName -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.BranchName')</label>
                                    <p class="form-text">
                                        @if (isset($DeliveryReport['delivery']['completed_orders']) &&
                                                count($DeliveryReport['delivery']['completed_orders']) > 0)
                                            @php
                                                // Get unique branch names from all completed orders
                                                $branches = collect($DeliveryReport['delivery']['completed_orders'])
                                                    ->pluck('branch_name')
                                                    ->unique()
                                                    ->implode(', ');
                                            @endphp
                                            {{ $branches ?: '-' }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>


                                <!-- Completed Orders -->
                                <div class="col-xl-12">
                                    <label class="form-label">@lang('report.CompletedOrders')</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('report.OrderNumber')</th>
                                                    <th>@lang('report.Date')</th>
                                                    <th>@lang('report.total_price')</th>
                                                    <th>@lang('report.PaymentMethod')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($DeliveryReport['delivery']['completed_orders'] ?? [] as $order)
                                                    <tr>
                                                        <td>{{ $order['order_number'] ?? '' }}</td>
                                                        <td>{{ $order['date'] ?? '' }}</td>
                                                        <td>{{ $order['total_price'] ?? '0.00' }}</td>
                                                        <td>{{ $order['payment_method'] }}
                                                        </td>

                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5">@lang('report.NoCompletedOrdersFound')</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Print Button -->
                        <div class="card-footer border-top-0">
                            <div class="btn-list float-end">
                                <button type="button" class="d-inline-flex btn btn-primary btn-wave print-button"
                                    onclick="printFormattedReport({{ json_encode($DeliveryReport) }})">
                                    <i class="ri-printer-line me-1 align-middle"></i>@lang('reservations.print')
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

<script>
    function printFormattedReport(deliveryReport) {
        if (!deliveryReport || !deliveryReport.delivery) {
            alert("No delivery report data available.");
            return;
        }

        // Create an iframe for printing
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0px';
        iframe.style.height = '0px';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;

        // Language detection
        const lang = document.documentElement.lang || 'en';

        // Translations
        const translations = {
            en: {
                reportTitle: "Delivery Order Report",
                deliveryID: "Delivery ID",
                deliveryName: "Delivery Name",
                totalCompletedOrders: "Total Completed Orders",
                totalEarnings: "Total Earnings",
                orderDetails: "Order Details",
                orderNumber: "Order Number",
                date: "Date",
                branchName: "Branch Name",
                amount: "Amount",
                paymentMethod: "Payment Method",
                notAvailable: "Not Available",
                noOrdersFound: "No Completed Orders Found",
            },
            ar: {
                reportTitle: "تقرير طلبات التوصيل المكتملة",
                deliveryID: " التسلسل",
                deliveryName: "اسم المندوب",
                totalCompletedOrders: "إجمالي الطلبات المكتملة",
                totalEarnings: "إجمالي الأرباح",
                orderDetails: "تفاصيل الطلبات",
                orderNumber: "رقم الطلب",
                date: "التاريخ",
                branchName: "اسم الفرع",
                amount: "المبلغ",
                paymentMethod: "طريقة الدفع",
                notAvailable: "غير متاح",
                noOrdersFound: "لا توجد طلبات مكتملة",
            }
        };

        const t = translations[lang];

        const content = `
<!DOCTYPE html>
<html lang="${lang}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${t.reportTitle}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: ${lang === 'ar' ? 'rtl' : 'ltr'};
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: ${lang === 'ar' ? 'right' : 'left'};
        }
        .info-table th {
            background-color: #f2f2f2;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: ${lang === 'ar' ? 'right' : 'left'};
        }
        .data-table th {
            background-color: #f2f2f2;
        }
        .section-title {
            margin-top: 30px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>${t.reportTitle}</h1>
    </div>
    
    <table class="info-table">
        <tr>
            <th>${t.deliveryID}</th>
            <td>${deliveryReport.delivery.delivery_id || t.notAvailable}</td>
        </tr>
        <tr>
            <th>${t.deliveryName}</th>
            <td>${deliveryReport.delivery.first_name || ''} ${deliveryReport.delivery.last_name || ''}</td>
        </tr>
        <tr>
            <th>${t.totalCompletedOrders}</th>
            <td>${deliveryReport.delivery.total_orders || t.notAvailable}</td>
        </tr>
        <tr>
            <th>${t.totalEarnings}</th>
            <td>${deliveryReport.delivery.total_earnings || '0.00'}</td>
        </tr>
    </table>

    <div class="section-title">${t.orderDetails}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>${t.orderNumber}</th>
                <th>${t.date}</th>
                <th>${t.branchName}</th>
                <th>${t.amount}</th>
                <th>${t.paymentMethod}</th>
            </tr>
        </thead>
        <tbody>
            ${deliveryReport.delivery.completed_orders && deliveryReport.delivery.completed_orders.length > 0 
                ? deliveryReport.delivery.completed_orders.map(order => `
                    <tr>
                        <td>${order.order_number || ''}</td>
                        <td>${order.date || ''}</td>
                        <td>${order.branch_name || ''}</td>
                        <td>${order.total_price || '0.00'}</td>
                        <td>${order.payment_method || '-'}</td>
                    </tr>
                `).join('')
                : `<tr><td colspan="5">${t.noOrdersFound}</td></tr>`}
        </tbody>
    </table>
</body>
</html>
`;

        doc.open();
        doc.write(content);
        doc.close();

        // Wait for iframe to load before printing
        iframe.onload = function() {
            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                document.body.removeChild(iframe);
            }, 300);
        };
    }
</script>
