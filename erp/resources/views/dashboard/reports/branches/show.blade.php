@extends('layouts.master')

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('branch_report.branch_details')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.branches.list') }}">@lang('sidebar.branches_reports')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch_report.branch_details')</li>
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
                                        @lang('branch_report.Branch') - <span class="text-primary">{{ app()->getLocale()== 'en'? $branch->name_en : $branch->name_ar }}</span>
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
{{--                                                    <p class="text-muted mb-4">--}}
{{--                                                        @lang('branch_report.Details'):--}}
{{--                                                    </p>--}}
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.Name')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ app()->getLocale()== 'en'? $branch->name_en : $branch->name_ar }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.Address')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ app()->getLocale()== 'en'? $branch->address_en : $branch->address_ar }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.Phone')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{$branch->phone }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.Email')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $branch->email}}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.Manager')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $branch->employess && $branch->employess->first_name ? ($branch->employess->first_name . ' ' . $branch->employess->last_name) : __('branch_report.None') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.IsMain')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $branch->is_default == 0 ? __('branch_report.No') :  __('branch_report.Yes')}}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.IsDelivery')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $branch->is_delivery == 0 ? __('branch_report.No') :  __('branch_report.Yes')}}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">

                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.TaxApplication')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $branch->tax_application == 0 ? __('branch_report.No') :  __('branch_report.Yes')}}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.CouponApplication')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $branch->coupon_application == 0 ? __('branch_report.No') :  __('branch_report.Yes')}}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.Latitude')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $branch->latitute }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.Longitude')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $branch->longitute }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.OrdersNumber')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $orders->order_count ?? 0}}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.TotalProfit')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $profit->total_revenue ?? 0 }} {{ is_object($branch->country) ? $branch->country->currency_symbol : ($branch->country ?? '') }}
                                                        </p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <p class="text-muted mb-1">
                                                            @lang('branch_report.totalRevenueAllBranches')
                                                        </p>
                                                        <p class="fw-bold mb-1">
                                                            {{ $totalRevenueAllBranches && count($totalRevenueAllBranches) > 0
                                                                                                    ? collect($totalRevenueAllBranches)->map(fn($revenue, $currency) => number_format($revenue, 2) . ' ' . $currency)->implode(', ')
                                                                                                    : '0'
                                                            }}
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
            // const iframe = document.createElement('iframe');

            // // Set the iframe to be invisible
            // iframe.style.position = 'absolute';
            // iframe.style.width = '0px';
            // iframe.style.height = '0px';
            // iframe.style.border = 'none';
            // document.body.appendChild(iframe);

            // // Get the iframe's document object
            // const doc = iframe.contentWindow.document;

            // Generate content for the iframe
            const content = `
        <!DOCTYPE html>
        <html lang="{{ app()->getLocale() }}">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{{ app()->getLocale() == 'en' ? 'Branch Report' : 'تقرير فرع' }}</title>
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
            <h1 class="title">@lang('print.Branch') - {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar}}</h1>
            <br><br>
            <div class="sub-title"><strong>@lang('print.BranchDetails')</strong></div>
                <p><strong>@lang('print.Address'):</strong> {{ $branch->address ? (app()->getLocale()=='en' ? $branch->address_en:$branch->address_ar ) : __('print.unknown') }}</p>
                <p><strong>@lang('print.Email'):</strong> {{ $branch->email ?? __('print.unknown') }}</p>
                <p><strong>@lang('print.Phone'):</strong> {{ $branch->phone ?? __('print.unknown') }}</p>
                <p><strong>@lang('print.Manager'):</strong> {{ $branch->employess && $branch->employess->first_name ? ($branch->employess->first_name . ' ' . $branch->employess->last_name) : __('print.unknown') }}</p>
                <p><strong>@lang('print.Latitude'):</strong> {{ $branch->latitute }}</p>
                <p><strong>@lang('print.Longitude'):</strong> {{ $branch->longitute }}</p>
                <p><strong>@lang('print.IsMain'):</strong> {{  $branch->is_default == 0 ? __('print.No') :  __('print.Yes') }}</p>
                <p><strong>@lang('print.IsDelivery'):</strong> {{ $branch->is_delivery == 0 ? __('print.No') :  __('print.Yes') }}</p>
                <p><strong>@lang('print.TaxApplication'):</strong> {{ $branch->tax_application == 0 ? __('print.No') :  __('print.Yes') }}</p>
                <p><strong>@lang('print.CouponApplication'):</strong> {{ $branch->coupon_application == 0 ? __('print.No') :  __('print.Yes') }}</p>
                <p><strong>@lang('print.OrdersNumber'):</strong> {{ $orders->order_count ?? 0 }}</p>
                <p><strong>@lang('print.TotalProfit'):</strong> {{ $profit->total_revenue ?? 0 }} {{ is_object($branch->country) ? $branch->country->currency_symbol : ($branch->country ?? '') }}</p>
                <p><strong>@lang('branch_report.totalRevenueAllBranches'):</strong> {{ $totalRevenueAllBranches && count($totalRevenueAllBranches) > 0
                                        ? collect($totalRevenueAllBranches)->map(fn($revenue, $currency) => number_format($revenue, 2) . ' ' . $currency)->implode(', ')
                                        : '0'
                                }}
            </p>
        </body>
        </html>
`;

            // Open a new window and write the content
            const printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write(content);
            printWindow.document.close();

            // Wait for the content to load before printing
            printWindow.onload = function() {
                printWindow.print();
            };
            // Write content to the iframe
            // doc.open();
            // doc.write(content);
            // doc.close();

            // // Wait for the content to load before printing
            // iframe.onload = function() {
            //     iframe.contentWindow.print();
            //     document.body.removeChild(iframe); // Remove iframe after printing
            // };
        }
    </script>
@endsection
