@extends('layouts.master')

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('order.cashier_performance_report_details')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.cashier_performance.list') }}">@lang('order.cashier_performance_reports')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('order.cashier_performance_report_details')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- END PAGE HEADER -->

    <!-- APP CONTENT -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start::row-1 -->
            <div class="row" id="printable-content">

                <div class="col-xl-12">
                    <!-- Customer Details -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('order.cashier_details')</div>
                            <div class="btn-list float-end">
                                <button type="button" class="d-inline-flex btn btn-primary btn-wave" onclick="printFormattedReport()">
                                    <i class="ri-printer-line me-1 align-middle"></i>@lang('order.print')
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($cashier)
                                <ul class="list-unstyled order-details-list">
                                    <li>
                                        <span class="text-muted">@lang('order.cashier_name'):</span>
                                        {{ $cashier->first_name. " " . $cashier->last_name ?? __('order.unknown') }}
                                    </li>
                                    <li>
                                        <span class="text-muted">@lang('order.email'):</span>
                                        {{ $cashier->email ?? __('order.unknown') }}
                                    </li>
                                    <li>
                                        <span class="text-muted">@lang('order.phone'):</span>
                                        {{ $cashier->phone_number ?? __('order.unknown') }}
                                    </li>
                                    <li>
                                        <span class="text-muted">@lang('order.branch'):</span>
                                        {{ $cashier->branch ? (app()->getLocale() === 'ar' ? $cashier->branch->name_ar : $cashier->branch->name_en) : __('order.none') }}
                                    </li>
                                </ul>
                            @else
                                <span class="text-danger">@lang('order.cashier_deleted')</span>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('order.performance')</div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>@lang('order.orders_count')</span>
                                <span>
                                    {{ $cashier->order_count }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>@lang('order.total')</span>
                                <span>{{ $cashier->total_order_price }}
                                    {{ $cashier->branch->country->currency_symbol }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- End::row-1 -->
        </div>
    </div>
    <!-- END APP CONTENT -->
@endsection

@section('scripts')
    <script>
        function printFormattedReport() {
            // Create a hidden iframe
            const iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.width = '0px';
            iframe.style.height = '0px';
            iframe.style.border = 'none';
            document.body.appendChild(iframe);

            const doc = iframe.contentWindow.document;

            const content = `
    <!DOCTYPE html>
    <html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@lang('order.cashier_performance_report_details')</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                direction: {{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }};
                margin: 20px;
                color: #333;
            }
            .print-header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #eee;
                padding-bottom: 10px;
            }
            .card {
                border: 1px solid #ddd;
                border-radius: 5px;
                margin-bottom: 20px;
                padding: 15px;
                page-break-inside: avoid;
            }
            .card-title {
                font-weight: bold;
                font-size: 18px;
                margin-bottom: 15px;
                color: #2a3547;
            }
            .list-unstyled {
                padding-left: 0;
                list-style: none;
            }
            .list-unstyled li {
                margin-bottom: 8px;
                display: flex;
                justify-content: space-between;
            }
            .text-muted {
                color: #6c757d;
                font-weight: bold;
            }
            @media print {
                body {
                    margin: 0;
                    padding: 15px;
                }
                .no-print {
                    display: none !important;
                }
            }
        </style>
    </head>
    <body>
        <div class="print-header">
            <h2>@lang('order.cashier_performance_report_details')</h2>
            <p>${new Date().toLocaleString()}</p>
        </div>

        <!-- Cashier Details -->
        <div class="card">
            <div class="card-title">@lang('order.cashier_details')</div>
            <ul class="list-unstyled">
                <li>
                    <span class="text-muted">@lang('order.cashier_name'):</span>
                    <span>${document.querySelector('.order-details-list li:first-child').textContent.split(':')[1].trim()}</span>
                </li>
                <li>
                    <span class="text-muted">@lang('order.email'):</span>
                    <span>${document.querySelector('.order-details-list li:nth-child(2)').textContent.split(':')[1].trim()}</span>
                </li>
                <li>
                    <span class="text-muted">@lang('order.phone'):</span>
                    <span>${document.querySelector('.order-details-list li:nth-child(3)').textContent.split(':')[1].trim()}</span>
                </li>
                <li>
                    <span class="text-muted">@lang('order.branch'):</span>
                    <span>${document.querySelector('.order-details-list li:last-child').textContent.split(':')[1].trim()}</span>
                </li>
            </ul>
        </div>

        <!-- Performance Summary -->
        <div class="card">
            <div class="card-title">@lang('order.performance')</div>
            <ul class="list-unstyled">
                <li>
                    <span class="text-muted">@lang('order.orders_count'):</span>
                    <span>${document.querySelector('.card-body .mb-2:first-child span:last-child').textContent.trim()}</span>
                </li>
                <li>
                    <span class="text-muted">@lang('order.total'):</span>
                    <span>${document.querySelector('.card-body .mb-2:last-child span:last-child').textContent.trim()}</span>
                </li>
            </ul>
        </div>
    </body>
    </html>
    `;

            doc.open();
            doc.write(content);
            doc.close();

            iframe.onload = function() {
                iframe.contentWindow.print();
                document.body.removeChild(iframe);
            };
        }
    </script>
@endsection
