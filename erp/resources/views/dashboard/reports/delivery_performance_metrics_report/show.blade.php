@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('report.delivery_performance_metrics_report_Details')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('reports.delivery_performance_metrics_report.list') }}">
                            @lang('report.delivery_performance_metrics_report')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @lang('report.delivery_performance_metrics_report')
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
                            <div class="card-title">@lang('report.delivery_performance_metrics_report_Details')</div>
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

                                <!-- Total Completed Orders -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.totalCompletedOrders')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['total_completed_orders'] ?? 0 }}
                                    </p>
                                </div>

                                <!-- Total Cancelled Orders -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.TotalCancelledOrders')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['total_canceled_orders'] ?? 0 }}
                                    </p>
                                </div>

                                <!-- Total Hold Orders -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.TotalHoldOrders')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['total_hold_orders'] ?? 0 }}
                                    </p>
                                </div>

                                <!-- Delayed Delivery Percentage -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.DelayedDeliveryPercentage')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['delayed_delivery_percentage'] ?? 0 }}%
                                    </p>
                                </div>

                                <!-- Total Delayed Deliveries -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.TotalDelayedDeliveries')</label>
                                    <p class="form-text">
                                        {{ is_array($DeliveryReport['delivery']['delayed_orders'] ?? null) ? count($DeliveryReport['delivery']['delayed_orders']) : 0 }}
                                    </p>
                                </div>

                                <!-- Average Time of Day -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.Avg.TimeOfDay')</label>
                                    <p class="form-text">
                                        {{ $DeliveryReport['delivery']['avg_time_of_day'] ?? '-' }}
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
                                                    <th>@lang('report.CancelReason')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($DeliveryReport['delivery']['cancellations'] ?? [] as $cancellation)
                                                    <tr>
                                                        <td>{{ $cancellation['order_number'] ?? '' }}</td>
                                                        <td>{{ $cancellation['branch_name'] ?? '' }}</td>
                                                        <td>{{ $cancellation['cancel_reason'] ?? __('report.NotAvailable') }}</td>
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

                                <!-- Time of Day -->
                                <div class="col-xl-12">
                                    <label class="form-label">@lang('report.TimeOfDay')</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('report.OrderNumber')</th>
                                                    <th>@lang('report.EstimatedTime')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($DeliveryReport['delivery']['time_of_day'] ?? [] as $time)
                                                    <tr>
                                                        <td>{{ $time['order_number'] ?? '' }}</td>
                                                        <td>{{ $time['estimated_time'] ?? '' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2">@lang('report.NoTimeDataFound')</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- On Time Deliveries -->
                                <div class="col-xl-12">
                                    <label class="form-label">@lang('report.OnTimeDelivery')</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('report.OrderNumber')</th>
                                                    <th>@lang('report.DeliveredTime')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($DeliveryReport['delivery']['on_time_deliveries'] ?? [] as $deliveryItem)
                                                    <tr>
                                                        <td>{{ $deliveryItem['order_number'] ?? '' }}</td>
                                                        <td>{{ $deliveryItem['delivered_time'] ?? '' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2">@lang('report.NoOnTimeDeliveriesFound')</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Delayed Orders -->
                                <div class="col-xl-12">
                                    <label class="form-label">@lang('report.DelayedOrders')</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('report.OrderNumber')</th>
                                                    <th>@lang('report.EstimatedTime')</th>
                                                    <th>@lang('report.DeliveredTime')</th>
                                                    <th>@lang('report.DelayTime')</th>
                                                    <th>@lang('report.avg_delivery_time')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($DeliveryReport['delivery']['delayed_orders'] ?? [] as $delayed)
                                                    <tr>
                                                        <td>{{ $delayed['order_number'] ?? '' }}</td>
                                                        <td>{{ $delayed['estimated_time'] ?? '' }}</td>
                                                        <td>{{ $delayed['delivered_time'] ?? '' }}</td>
                                                        <td>{{ $delayed['delay_minutes'] ?? 0 }} @lang('report.Minutes')</td>
                                                        <td>{{ $delayed['avg_delivery_time'] ?? 0 }} @lang('report.Minutes')</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5">@lang('report.NoDelayedOrdersFound')</td>
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
                reportTitle: "Delivery Performance Metrics Report",
                deliveryID: "Delivery ID",
                deliveryName: "Delivery Name",
                totalCompletedOrders: "Total Completed Orders",
                totalCancelledOrders: "Total Cancelled Orders",
                totalHoldOrders: "Total Hold Orders",
                cancellationDetails: "Cancellation Details",
                orderNumber: "Order Number",
                branchName: "Branch Name",
                cancelReason: "Cancel Reason",
                timeOfDay: "Time of Day",
                estimatedTime: "Estimated Time",
                onTimeDelivery: "On Time Delivery",
                deliveredTime: "Delivered Time",
                delayedDeliveryPercentage: "Delayed Delivery Percentage",
                totalDelayedDeliveries: "Total Delayed Deliveries",
                avgTimeOfDay: "Average Time of Day",
                delayedOrders: "Delayed Orders",
                delayTime: "Delay Time",
                avgDeliveryTime: "Average Delivery Time",
                notAvailable: "Not Available",
                noCancellationsFound: "No Cancellations Found",
                noTimeDataFound: "No Time Data Found",
                noOnTimeDeliveriesFound: "No On Time Deliveries Found",
                noDelayedOrdersFound: "No Delayed Orders Found",
                minutes: "Minutes"
            },
            ar: {
                reportTitle: "تفاصيل تقرير الاداء للمندوب",
                deliveryID: "رقم المندوب",
                deliveryName: "اسم المندوب",
                totalCompletedOrders: "إجمالي الطلبات المكتملة",
                totalCancelledOrders: "إجمالي الطلبات الملغاة",
                totalHoldOrders: "إجمالي الطلبات المحتجزة",
                cancellationDetails: "تفاصيل الإلغاء",
                orderNumber: "رقم الطلب",
                branchName: "اسم الفرع",
                cancelReason: "سبب الإلغاء",
                timeOfDay: "الوقت المقدر لتسليم الطلبات",
                estimatedTime: "الوقت المقدر",
                onTimeDelivery: "وقت تسليم الطلبات",
                deliveredTime: "وقت التسليم",
                delayedDeliveryPercentage: "نسبة التأخير في التسليم",
                totalDelayedDeliveries: "إجمالي عمليات التسليم المتأخرة",
                avgTimeOfDay: "متوسط الوقت المقدر",
                delayedOrders: "الطلبات المتأخرة",
                delayTime: "وقت التأخير",
                avgDeliveryTime: "متوسط وقت التسليم",
                notAvailable: "غير متاح",
                noCancellationsFound: "لا توجد إلغاءات",
                noTimeDataFound: "لا توجد بيانات وقت",
                noOnTimeDeliveriesFound: "لا توجد تسليمات في الوقت المحدد",
                noDelayedOrdersFound: "لا توجد طلبات متأخرة",
                minutes: "دقائق"
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
            <td>${deliveryReport.delivery.total_completed_orders || 0}</td>
        </tr>
        <tr>
            <th>${t.totalCancelledOrders}</th>
            <td>${deliveryReport.delivery.total_canceled_orders || 0}</td>
        </tr>
        <tr>
            <th>${t.totalHoldOrders}</th>
            <td>${deliveryReport.delivery.total_hold_orders || 0}</td>
        </tr>
        <tr>
            <th>${t.delayedDeliveryPercentage}</th>
            <td>${deliveryReport.delivery.delayed_delivery_percentage || 0}%</td>
        </tr>
        <tr>
            <th>${t.totalDelayedDeliveries}</th>
            <td>${isArray(deliveryReport.delivery.delayed_orders) ? deliveryReport.delivery.delayed_orders.length : 0}</td>
        </tr>
        <tr>
            <th>${t.avgTimeOfDay}</th>
            <td>${deliveryReport.delivery.avg_time_of_day || '-'}</td>
        </tr>
    </table>

    <div class="section-title">${t.cancellationDetails}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>${t.orderNumber}</th>
                <th>${t.branchName}</th>
                <th>${t.cancelReason}</th>
            </tr>
        </thead>
        <tbody>
            ${deliveryReport.delivery.cancellations && deliveryReport.delivery.cancellations.length > 0 
                ? deliveryReport.delivery.cancellations.map(c => `
                    <tr>
                        <td>${c.order_number || ''}</td>
                        <td>${c.branch_name || ''}</td>
                        <td>${c.cancel_reason || ''}</td>
                    </tr>
                `).join('')
                : `<tr><td colspan="3">${t.noCancellationsFound}</td></tr>`}
        </tbody>
    </table>

    <div class="section-title">${t.timeOfDay}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>${t.orderNumber}</th>
                <th>${t.estimatedTime}</th>
            </tr>
        </thead>
        <tbody>
            ${deliveryReport.delivery.time_of_day && deliveryReport.delivery.time_of_day.length > 0 
                ? deliveryReport.delivery.time_of_day.map(t => `
                    <tr>
                        <td>${t.order_number || ''}</td>
                        <td>${t.estimated_time || ''}</td>
                    </tr>
                `).join('')
                : `<tr><td colspan="2">${t.noTimeDataFound}</td></tr>`}
        </tbody>
    </table>

    <div class="section-title">${t.onTimeDelivery}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>${t.orderNumber}</th>
                <th>${t.deliveredTime}</th>
            </tr>
        </thead>
        <tbody>
            ${deliveryReport.delivery.on_time_deliveries && deliveryReport.delivery.on_time_deliveries.length > 0 
                ? deliveryReport.delivery.on_time_deliveries.map(d => `
                    <tr>
                        <td>${d.order_number || ''}</td>
                        <td>${d.delivered_time || ''}</td>
                    </tr>
                `).join('')
                : `<tr><td colspan="2">${t.noOnTimeDeliveriesFound}</td></tr>`}
        </tbody>
    </table>

    <div class="section-title">${t.delayedOrders}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>${t.orderNumber}</th>
                <th>${t.estimatedTime}</th>
                <th>${t.deliveredTime}</th>
                <th>${t.delayTime}</th>
                <th>${t.avgDeliveryTime}</th>
            </tr>
        </thead>
        <tbody>
            ${deliveryReport.delivery.delayed_orders && deliveryReport.delivery.delayed_orders.length > 0 
                ? deliveryReport.delivery.delayed_orders.map(d => `
                    <tr>
                        <td>${d.order_number || ''}</td>
                        <td>${d.estimated_time || ''}</td>
                        <td>${d.delivered_time || ''}</td>
                        <td>${d.delay_minutes || 0} ${t.minutes}</td>
                        <td>${d.avg_delivery_time || 0} ${t.minutes}</td>
                    </tr>
                `).join('')
                : `<tr><td colspan="5">${t.noDelayedOrdersFound}</td></tr>`}
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

    function isArray(obj) {
        return Array.isArray(obj);
    }
</script>