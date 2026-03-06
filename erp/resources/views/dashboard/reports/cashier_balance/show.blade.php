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
                    <!-- Order Details -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="card-title">
                                        @lang('cashier_balances_reports.CashierBalance') - <span class="text-primary">{{ app()->getLocale()== 'en'? $balance->cashierMachines->name_en : $balance->cashierMachines->name_ar }}</span>
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
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.Machine')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ app()->getLocale() == 'en' ? $balance->cashierMachines->name_en : $balance->cashierMachines->name_ar }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.Cashier')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $balance->employees && $balance->employees->first_name ? ($balance->employees->first_name . ' ' . $balance->employees->last_name) : __('cashier_balances_reports.None') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.RealStartShift')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $shiftStart  ? $shiftStart : __('cashier_balances_reports.None') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.RealEndShift')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{  $endStart  ? $endStart : __('cashier_balances_reports.None') }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.StartShift')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $startshiftreal ? \Carbon\Carbon::parse($startshiftreal)->format('H:i:s') : __('cashier_balances_reports.None') }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.EndShift')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{  $balance->type == 2 ? \Carbon\Carbon::parse($endshiftreal)->format('H:i:s') : __('cashier_balances_reports.noclose') }}</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.OpenCash')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $openCash . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.OpenVisa')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $openVisa . ' ' . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.CloseCash')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($balance?->close_cash ?? 0). " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.CloseVisa')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($balance?->close_visa ?? 0). " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.Type')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{$balance->type == 1 ? __('cashier_balances_reports.Open') : __('cashier_balances_reports.Close')}}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.Branch')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ app()->getLocale() == 'en' ? $balance->employees?->branch?->name_en : $balance->employees?->branch?->name_ar}}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.RealOpenCash')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($lastbalance?->close_cash ?? 0). " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.RealOpenVisa')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($lastbalance?->close_visa ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.RealCloseCash')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($closeCash + ($balance?->open_cash ?? 0) - $branchSafeCash) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.RealCloseVisa')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($closeVisa + ($balance?->open_visa ?? 0) - $branchSafeVisa) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.DeficitCash')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($balance?->deficit_cash ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.DeficitVisa')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($balance?->deficit_visa ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.DeficitCashClose')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($balance?->deficit_cash_close ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.DeficitVisaClose')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ ($balance?->deficit_visa_close ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.Date')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $balance->date}}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('cashier_balances_reports.Time')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $balance->time}}
                                                        </p>
                                                    </div>
                                                </div>
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
            <title>{{ app()->getLocale() == 'en' ? 'CashierBalance Report' : 'تقرير ميزانية كاشير' }}</title>
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
            <h1 class="title">@lang('print.CashierBalance') - {{ app()->getLocale() == 'en' ? $balance->cashierMachines->name_en : $balance->cashierMachines->name_ar }}</h1>
            <br><br>
            <div class="sub-title"><strong>@lang('print.cashierBalanceDetails')</strong></div>
                <p><strong>@lang('print.Machine'):</strong> {{ app()->getLocale() == 'en' ? $balance->cashierMachines->name_en : $balance->cashierMachines->name_ar }}</p>
                <p><strong>@lang('print.Cashier'):</strong> {{ $balance->employees && $balance->employees->first_name ? ($balance->employees->first_name . ' ' . $balance->employees->last_name) : __('print.unknown') }}</p>
                <p><strong>@lang('print.Branch'):</strong> {{ app()->getLocale() == 'en' ? $balance->employees?->branch?->name_en : $balance->employees?->branch?->name_ar }}</p>
                <p><strong>@lang('print.OpenCash'):</strong> {{ $openCash . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}</p>
                <p><strong>@lang('print.OpenVisa'):</strong> {{ $openVisa . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}</p>
                <p><strong>@lang('print.CloseCash'):</strong> {{ $balance->close_cash . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}</p>
                <p><strong>@lang('print.CloseVisa'):</strong> {{ $balance->close_visa . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}</p>
                <p><strong>@lang('print.RealOpenCash'):</strong> {{ ($lastbalance?->close_cash ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}</p>
                <p><strong>@lang('print.RealOpenVisa'):</strong> {{ ($lastbalance?->close_visa ?? 0) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}</p>
                <p><strong>@lang('print.RealCloseCash'):</strong> {{ ($closeCash + ($lastbalance?->close_cash ?? 0)) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}</p>
                <p><strong>@lang('print.RealCloseVisa'):</strong> {{ ($closeVisa + ($lastbalance?->close_visa ?? 0)) . " " . ($balance?->cashierMachines?->branches?->country?->currency_symbol ?? '') }}</p>
                <p><strong>@lang('print.Date'):</strong> {{ $balance->date }}</p>
                <p><strong>@lang('print.Time'):</strong> {{ $balance->time }}</p>
                <p><strong>@lang('print.Type'):</strong> {{ $balance->type == 1 ? __('cashier_balances_reports.Open') : __('cashier_balances_reports.Close') }}</p>
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