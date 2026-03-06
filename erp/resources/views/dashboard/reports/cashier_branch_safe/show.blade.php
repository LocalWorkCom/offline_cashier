@extends('layouts.master')

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('cashier_balances_reports.cashierBalanceDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.cashier_balances.list') }}">@lang('sidebar.cashier_balances_reports')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('cashier_balances_reports.cashierBalanceDetails')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- END PAGE HEADER -->

    <!-- APP CONTENT -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <!-- Cashier Balance Details -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="card-title">
                                        @lang('cashier_balances_reports.CashierBalance') - <span class="text-primary">{{ $balance['cashier_name'] }}</span>
                                    </div>
                                    <div class="btn-list float-end">
                                        <button type="button" class="d-inline-flex btn btn-primary btn-wave" onclick="printFormattedReport()">
                                            <i class="ri-printer-line me-1 align-middle"></i>@lang('order.print')
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row gy-3">
                                        <div class="col-xl-12">
                                            <div class="row">
                                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.Cashier')</p>
                                                        <p class="fw-bold mb-1">{{ $balance['cashier_name'] }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.Branch')</p>
                                                        <p class="fw-bold mb-1">{{ $balance['branch'] }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.StartShift')</p>
                                                        <p class="fw-bold mb-1">{{ $balance['start_shift'] }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.shiftId')</p>
                                                        <p class="fw-bold mb-1">{{ $balance['shiftId'] }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.EndShift')</p>
                                                        <p class="fw-bold mb-1">{{ $balance['end_shift'] }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.OrdersNumber')</p>
                                                        <p class="fw-bold mb-1">{{ $balance['orders_number'] }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.TotalBefore')</p>
                                                        <p class="fw-bold mb-1">{{ number_format($balance['totalbeforTax'], 2) }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.TotalAfter')</p>
                                                        <p class="fw-bold mb-1">{{ number_format($balance['totalafterTax'], 2) }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.Service')</p>
                                                        <p class="fw-bold mb-1">{{ number_format($balance['service'], 2) }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.tax')</p>
                                                        <p class="fw-bold mb-1">{{ number_format($balance['Tax'], 2) }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.CashTotal')</p>
                                                        <p class="fw-bold mb-1">{{ number_format($balance['cashTotal'], 2) }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.VisaTotal')</p>
                                                        <p class="fw-bold mb-1">{{ number_format($balance['visaTotal'], 2) }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.totalSales')</p>
                                                        <p class="fw-bold mb-1">{{ number_format($balance['totalSales'], 2) }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.CouponTotalFixed')</p>
                                                        <p class="fw-bold mb-1">{{ number_format($balance['couponTotalFixed'], 2) }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">@lang('cashier_balances_reports.TotalAllPrice')</p>
                                                        <p class="fw-bold mb-1">{{ number_format($balance['totalAllPrice'], 2) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                          
                                            <!-- categories Section -->
                                            <div class="mt-4">
                                                <h5>@lang('cashier_balances_reports.categories')</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>@lang('cashier_balances_reports.Name')</th>
                                                            <th>@lang('cashier_balances_reports.Quantity')</th>
                                                            <th>@lang('cashier_balances_reports.Price')</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($balance['categories'] as $dish)
                                                            <tr>
                                                                <td>{{ $dish['name'] }}</td>
                                                                <td>{{ $dish['quantity'] }}</td>
                                                                <td>{{ number_format($dish['price'], 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
            // Create an iframe element dynamically
            const iframe = document.createElement('iframe');

            // Set the iframe to be invisible
            iframe.style.position = 'absolute';
            iframe.style.width = '0px';
            iframe.style.height = '0px';
            iframe.style.border = 'none';
            document.body.appendChild(iframe);

            // Get the iframe's document object
            const doc = iframe.contentWindow.document;

            // Generate content for the iframe
            const content = `
                <!DOCTYPE html>
                <html lang="{{ app()->getLocale() }}">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>{{ app()->getLocale() == 'en' ? 'Cashier Balance Report' : 'تقرير ميزانية كاشير' }}</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            direction: {{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }};
                            margin: 30px;
                        }
                        .table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                        }
                        .table th, .table td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
                        }
                        .table th {
                            background-color: #f2f2f2;
                        }
                    </style>
                </head>
                <body>
                    <h1 class="title">@lang('print.CashierBalance') - {{ $balance['cashier_name'] }}</h1>
                    <br><br>
                    <div class="sub-title"><strong>@lang('print.cashierBalanceDetails')</strong></div>
                    <p><strong>@lang('print.Cashier'):</strong> {{ $balance['cashier_name'] }}</p>
                    <p><strong>@lang('print.Branch'):</strong> {{ $balance['branch'] }}</p>
                    <p><strong>@lang('print.shiftId'):</strong> {{ $balance['shiftId'] }}</p>
                    <p><strong>@lang('print.StartShift'):</strong> {{ $balance['start_shift'] }}</p>
                    <p><strong>@lang('print.EndShift'):</strong> {{ $balance['end_shift'] }}</p>
                    <p><strong>@lang('print.OrdersNumber'):</strong> {{ $balance['orders_number'] }}</p>
                    <p><strong>@lang('print.TotalBefore'):</strong> {{ number_format($balance['totalbeforTax'], 2) }}</p>
                    <p><strong>@lang('print.TotalAfter'):</strong> {{ number_format($balance['totalafterTax'], 2) }}</p>
                    <p><strong>@lang('print.Service'):</strong> {{ number_format($balance['service'], 2) }}</p>
                    <p><strong>@lang('print.Tax'):</strong> {{ number_format($balance['Tax'], 2) }}</p>
                    <p><strong>@lang('print.CashTotal'):</strong> {{ number_format($balance['cashTotal'], 2) }}</p>
                    <p><strong>@lang('print.VisaTotal'):</strong> {{ number_format($balance['visaTotal'], 2) }}</p>
                    <p><strong>@lang('print.CouponTotalFixed'):</strong> {{ number_format($balance['couponTotalFixed'], 2) }}</p>
                    <p><strong>@lang('print.TotalAllPrice'):</strong> {{ number_format($balance['totalAllPrice'], 2) }}</p>
                    
                    <h3>@lang('print.categories')</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>@lang('print.Name')</th>
                                <th>@lang('print.Quantity')</th>
                                <th>@lang('print.Price')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($balance['categories'] as $dish)
                                <tr>
                                    <td>{{ $dish['name'] }}</td>
                                    <td>{{ $dish['quantity'] }}</td>
                                    <td>{{ number_format($dish['price'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </body>
                </html>
            `;

            // Write content to the iframe
            doc.open();
            doc.write(content);
            doc.close();

            // Wait for the content to load before printing
            iframe.onload = function() {
                iframe.contentWindow.print();
                document.body.removeChild(iframe); // Remove iframe after printing
            };
        }
    </script>
@endsection