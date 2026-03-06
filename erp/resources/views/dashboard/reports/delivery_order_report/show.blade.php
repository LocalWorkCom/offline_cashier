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
                        <a href="{{ route('reports.delivery_order_report.list') }}">
                            @lang('report.DeliveryDetails')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @lang('report.DeliveryDetails')
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-1 -->
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
                                    <label class="form-label">@lang('report.TotalCancelledOrders')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['total_orders'] ?? __('report.NotAvailable') }}
                                    </p>
                                </div>

                                <!-- Cancellations -->
                                <div class="col-xl-12">
                                    <label class="form-label">@lang('report.CancellationDetails')</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('report.OrderNumber')</th>
                                                    <th>@lang('report.BranchName')</th>
                                                    <th>@lang('report.complain')</th>
                                                    <th>@lang('report.CancelReason')</th>
                                                    <th>@lang('report.CancelMassage')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($DeliveryReport['delivery']['cancellations'] ?? [] as $cancellation)
                                                    <tr>
                                                        <td>{{ $cancellation['order_number'] ?? '' }}</td>
                                                        <td>{{ $cancellation['branch_name'] ?? '' }}</td>
                                                        <td>{{ $cancellation['massage'] ?? __('report.NotAvailable')  }}</td>
                                                        <td>{{ $cancellation['reason_text'] ?? __('report.NotAvailable')  }}</td>
                                                        <td>{{ $cancellation['reason'] ?? __('report.NotAvailable')  }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3">@lang('report.NoCancellationsFound')</td>
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
        <!-- End:: row-1 -->
    </div>
    </div>
    <!-- APP-CONTENT CLOSE -->
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
                reportTitle: "Delivery Order Cancellation Report",
                deliveryID: "Delivery ID",
                deliveryName: "Delivery Name",
                totalCancelledOrders: "Total Cancelled Orders",
                cancellationDetails: "Cancellation Details",
                orderNumber: "Order Number",
                branchName: "Branch Name",
                cancelReason: "Cancel Reason",
                complain: "complain",
                CancelMassage: "Cancel Massage",
                notAvailable: "Not Available",
                noCancellationsFound: "No Cancellations Found"
            },
            ar: {
                reportTitle: "تقرير إلغاء طلبات التوصيل",
                deliveryID: "معرف المندوب",
                deliveryName: "اسم المندوب",
                totalCancelledOrders: "إجمالي الطلبات الملغاة",
                cancellationDetails: "تفاصيل الإلغاء",
                orderNumber: "رقم الطلب",
                branchName: "اسم الفرع",
                cancelReason: "سبب الإلغاء",
                complain: "الشكوي",
                CancelMassage: "رساله الالغاء ",
                notAvailable: "غير متاح",
                noCancellationsFound: "لا توجد إلغاءات"
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
            <th>${t.totalCancelledOrders}</th>
            <td>${deliveryReport.delivery.total_orders || t.notAvailable}</td>
        </tr>
    </table>

    <div class="section-title">${t.cancellationDetails}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>${t.orderNumber}</th>
                <th>${t.branchName}</th>
                <th>${t.cancelReason}</th>
                <th>${t.complain}</th>
                <th>${t.CancelMassage}</th>

            </tr>
        </thead>
        <tbody>
            ${deliveryReport.delivery.cancellations && deliveryReport.delivery.cancellations.length > 0 
                ? deliveryReport.delivery.cancellations.map(c => `
                    <tr>
                        <td>${c.order_number || ''}</td>
                        <td>${c.branch_name || ''}</td>
                        <td>${c.reason_text || ''}</td>
                        <td>${c.massage || ''}</td>
                        <td>${c.reason || ''}</td>

                    </tr>
                `).join('')
                : `<tr><td colspan="3">${t.noCancellationsFound}</td></tr>`}
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
