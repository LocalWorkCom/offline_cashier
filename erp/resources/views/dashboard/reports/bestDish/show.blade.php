@extends('layouts.master')

@section('styles')
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0"> @lang('report.best_seller_dish_report_details')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.best_seller_dish.list') }}">@lang('report.reports')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('report.best_seller_dish_report_details') </li>
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
                    <div class="card custom-card">
                        <div class="card-header d-md-flex d-block">
                            <div class="h5 mb-0 d-sm-flex d-bllock align-items-center">

                            </div>
                            <div class="ms-auto mt-md-0 mt-2">
                                <button class="btn btn-secondary" onclick="printFormattedReport()">
                                    @lang('reports.print')
                                    <i class="ri-printer-line ms-1 align-middle d-inline-flex"></i>
                                </button>
                              
                               
                                {{--                                <button class="btn btn-secondary me-1" onclick="window.open('{{ route('reports.best_seller_dish.print', ['id' => $dish->id]) }}', '_blank')"> --}}
                                {{--                                    @lang('reports.print') --}}
                                {{--                                    <i class="ri-printer-line ms-1 align-middle d-inline-flex"></i> --}}
                                {{--                                </button> --}}
                                {{--                                <button class="btn btn-primary" --}}
                                {{--                                    onclick="window.location.href='{{ route('reports.savepdf', ['id' => $dish->id]) }}'"> --}}
                                {{--                                    @lang('reports.savepdf') --}}
                                {{--                                    <i class="ri-file-pdf-line ms-1 align-middle d-inline-flex"></i> --}}
                                {{--                                </button> --}}
                            </div>


                            <!-- After your table or wherever you want to show the totals -->

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="stats-box">
                                        <span class="badge bg-secondary">@lang('order.quantity'):
                                           {{ $totals['currency_symbol'] }} {{ number_format($totals['total_quantity']) }}</span>
                                        <span class="badge bg-success">@lang('order.total_before_tax'):
                                            {{ $totals['currency_symbol'] }}
                                            {{ number_format($totals['total_before_tax'], 2) }}</span>
                                        <span class="badge bg-primary">@lang('order.tax_value'):
                                            {{ $totals['currency_symbol'] }}

                                            {{ number_format($totals['total_tax_value'], 2) }}</span>
                                        <span class="badge bg-info">@lang('order.total_after_tax'):
                                            {{ $totals['currency_symbol'] }}
                                            {{ number_format($totals['total_after_tax'], 2) }}</span>

                                    </div>
                                </div>
                               
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                            <p class="text-muted mb-2">
                                                @lang('report.details'):
                                            </p>
                                            <p class="fw-bold mb-1">
                                                @lang('dishes.NameArabic')
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ $dish->name_ar }}
                                            </p>
                                            <p class="fw-bold mb-1">
                                                @lang('dishes.NameEnglish')
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ $dish->name_en }}
                                            </p>
                                            <p class="fw-bold mb-1">
                                                @lang('dishes.DescriptionArabic')
                                            </p>
                                            <p class="mb-1 text-muted">
                                                <td>{{ $dish->description_ar }}</td>
                                            </p>
                                            <p class="fw-bold mb-1">
                                                @lang('dishes.DescriptionEnglish')
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ $dish->description_en }}
                                            </p>

                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 ms-auto mt-sm-0 mt-3">
                                            <p class="text-muted mb-2">
                                            </p>
                                            <p class="fw-bold mb-1">
                                                @lang('dishes.Category')
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ $dish->dishCategory ? (app()->getLocale() == 'en' ? $dish->dishCategory->name_en : $dish->dishCategory->name_ar) : 'N/A' }}
                                            </p>
                                            <p class="fw-bold mb-1">
                                                @lang('dishes.Cuisine')
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ $dish->cuisine ? (app()->getLocale() == 'en' ? $dish->cuisine->name_en : $dish->cuisine->name_ar) : 'N/A' }}
                                            </p>
                                            <p class="fw-bold mb-1">
                                                @lang('dishes.Price')
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ $dish->base_price ?? '-' }}
                                            </p>


                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 ms-auto mt-sm-0 mt-3">

                                            <p class="fw-bold mb-1">
                                                @lang('dishes.Image')
                                            </p>
                                            <p class="mb-1 text-muted">
                                                <img src="{{ asset($dish->image) }}" alt="Dish Image" width="200"
                                                    height="200">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="col-xl-3">
                                    <p class="fw-semibold text-muted mb-1">Invoice ID :</p>
                                    <p class="fs-15 mb-1">#SPK120219890</p>
                                </div>
                                <div class="col-xl-3">
                                    <p class="fw-semibold text-muted mb-1">Date Issued :</p>
                                    <p class="fs-15 mb-1">29,Nov 2022 - <span class="text-muted fs-12">12:42PM</span></p>
                                </div>
                                <div class="col-xl-3">
                                    <p class="fw-semibold text-muted mb-1">Due Date :</p>
                                    <p class="fs-15 mb-1">29,Dec 2022</p>
                                </div>
                                <div class="col-xl-3">
                                    <p class="fw-semibold text-muted mb-1">Due Amount :</p>
                                    <p class="fs-16 mb-1 fw-semibold">$2,570.42</p>
                                </div> --}}
                                <div class="col-xl-12">
                                    <div class="table-responsive">
                                        <table class="table nowrap text-nowrap border mt-4">
                                            <thead>
                                                <tr>
                                                    <th>@lang('branch.id')</th>
                                                    <th>@lang('branch.ArabicName')</th>
                                                    <th>@lang('branch.EnglishName')</th>
                                                    <th>@lang('order.currency_symbol')</th>
                                                    <th>@lang('branch.Address')</th>
                                                    <th>@lang('order.size')</th> <!-- Column for size -->
                                                    <th>@lang('order.price')</th> <!-- Column for size -->
                                                    <th>@lang('order.order_addons')</th> <!-- Column for addons -->
                                                    <th>@lang('order.quantity')</th>
                                                    <th>@lang('order.total_before_tax')</th>
                                                    <th>@lang('order.total_after_tax')</th>
                                                    <th>@lang('order.tax_value')</th>
                                                    <th>@lang('order.note')</th>
                                                    <th>@lang('order.total')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($branches as $index => $branch)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $branch->name_ar }}</td>
                                                        <td>{{ $branch->name_en }}</td>
                                                        <td>{{ $branch->currency_symbol }}</td>
                                                        <td>{{ app()->getLocale() == 'en' ? $branch->address_en : $branch->address_ar }}
                                                        </td>

                                                        <!-- Fetch the corresponding size for this branch from $order['details'] -->
                                                        {{-- <td>
                                                            @php
                                                                $detail = $order['details']->firstWhere('branch_id', $branch->branch_id);
                                                            @endphp
                                                            {{ $detail ? $detail->size_name_ar : 'N/A' }}
                                                        </td> <!-- Show the size if available --> --}}
                                                        <td>{{ app()->getLocale() == 'en' ? $branch->size_name_en : $branch->size_name_ar }}
                                                        </td>

                                                        {{-- <td>{{ $branch->size_name_ar}}</td> <!-- Show the addons --> --}}
                                                        {{-- <td>{{ $branch->dish_size_price}}</td> <!-- Show the addons --> --}}
                                                        <td>{{ $branch->dish_price ?? '-' }}</td>
                                                        </td>
                                                        <td>{{ $branch->addon_names }}</td> <!-- Show the addons -->
                                                        <td>{{ $branch->quantity }}</td>

                                                        {{-- <td>{{ $detail->quantity }}</td> --}}

                                                        <td>{{ $branch->price_befor_tax }}</td>
                                                        <td>{{ $branch->price_after_tax }}</td>
                                                        <td>{{ $branch->tax_value }}</td>
                                                        <td>{{ $branch->note }}</td>
                                                        <td>
                                                            {{-- @if ($branch->tax_apply == 1) --}}
                                                            {{-- @if ($branch->tax_application == 1) --}}
                                                            {{ $branch->price_after_tax }}
                                                            {{-- @else
                                                                    {{ $branch->price_after_tax }}
                                                                @endif
                                                            @else
                                                                {{ $branch->price_after_tax }}
                                                            @endif --}}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>


                                    </div>
                                </div>


                                {{-- <div class="col-xl-12">
                                    <div>
                                        <label for="invoice-note" class="form-label">Note:</label>
                                        <textarea class="form-control form-control-light" id="invoice-note" rows="3">Once the invoice has been verified by the accounts payable team and recorded, the only task left is to send it for approval before releasing the payment</textarea>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                        {{-- <div class="card-footer text-end">
                            <button class="btn btn-success">Download <i
                                    class="ri-download-2-line ms-1 align-middle"></i></button>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END APP CONTENT -->
@endsection
@section('scripts')
    <script>
        function printFormattedReport() {
            // Get the HTML content to print
            const content = `
            <!DOCTYPE html>
            <html lang="{{ app()->getLocale() }}">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>{{ app()->getLocale() == 'en' ? 'Best Seller Report' : 'تقرير الاكثر مبيعا' }}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        direction: {{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }};
                        margin: 30px;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .dish-info {
                        margin-bottom: 20px;
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
                        font-weight: bold;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .img-container {
                        text-align: center;
                        margin: 10px 0;
                    }
                    .dish-img {
                        max-width: 150px;
                        max-height: 150px;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>@lang('report.best_seller_dish_report_details')</h1>
                </div>
                
                <div class="dish-info">
                    <div class="img-container">
                        <img src="{{ asset($dish->image) }}" alt="Dish Image" class="dish-img">
                    </div>
                    <h2>@lang('report.details')</h2>
                    <p><strong>@lang('dishes.NameArabic'):</strong> {{ $dish->name_ar }}</p>
                    <p><strong>@lang('dishes.NameEnglish'):</strong> {{ $dish->name_en }}</p>
                    <p><strong>@lang('dishes.DescriptionArabic'):</strong> {{ $dish->description_ar }}</p>
                    <p><strong>@lang('dishes.DescriptionEnglish'):</strong> {{ $dish->description_en }}</p>
                     <p><strong>@lang('dishes.Category'):</strong> {{ $dish->dishCategory ? (app()->getLocale() == 'en' ? $dish->dishCategory->name_en : $dish->dishCategory->name_ar) : 'N/A' }}</p>
                    <p><strong>@lang('dishes.Cuisine'):</strong> {{ $dish->cuisine ? (app()->getLocale() == 'en' ? $dish->cuisine->name_en : $dish->cuisine->name_ar) : 'N/A' }}</p>
                    <p><strong>@lang('dishes.Price'):</strong> {{ $dish->base_price ?? '-' }} {{ $dish->currency_symbol }}</p>
                </div>

                <h2>@lang('order.order_details')</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('branch.ArabicName')</th>
                            <th>@lang('branch.EnglishName')</th>
                            <th>@lang('branch.Address')</th>
                            <th>@lang('order.size')</th>
                            <th>@lang('order.price')</th>
                            <th>@lang('order.order_addons')</th>
                            <th>@lang('order.quantity')</th>
                            <th>@lang('order.total_before_tax')</th>
                            <th>@lang('order.total_after_tax')</th>
                            <th>@lang('order.tax_value')</th>
                            <th>@lang('order.note')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($branches as $index => $branch)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $branch->name_ar }}</td>
                            <td>{{ $branch->name_en }}</td>
                            <td>{{ app()->getLocale() == 'en' ? $branch->address_en : $branch->address_ar }}</td>
                            <td>{{ app()->getLocale() == 'en' ? $branch->size_name_en : $branch->size_name_ar }}</td>
                            <td>{{ $branch->dish_price ?? '-' }} {{ $branch->currency_symbol }}</td>
                            <td>{{ $branch->addon_names }}</td>
                            <td>{{ $branch->quantity }}</td>
                            <td>{{ $branch->price_befor_tax }} {{ $branch->currency_symbol }}</td>
                            <td>{{ $branch->price_after_tax }} {{ $branch->currency_symbol }}</td>
                            <td>{{ $branch->tax_value }} {{ $branch->currency_symbol }}</td>
                            <td>{{ $branch->note }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="footer" style="margin-top: 30px; text-align: center;">
                    <p>@lang('reports.printed_on') {{ now()->format('Y-m-d H:i:s') }}</p>
                </div>
                <h2>@lang('report.summary')</h2>
                <table class="table" style="margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th>@lang('report.metric')</th>
                            <th>@lang('report.value')</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>@lang('order.quantity')</td>
                            <td>{{ number_format($totals['total_quantity']) }}</td>
                        </tr>
                        <tr>
                            <td>@lang('order.total_before_tax')</td>
                            <td>{{ $totals['currency_symbol'] }} {{ number_format($totals['total_before_tax'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>@lang('order.tax_value')</td>
                            <td>{{ $totals['currency_symbol'] }} {{ number_format($totals['total_tax_value'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>@lang('order.total_after_tax')</td>
                            <td>{{ $totals['currency_symbol'] }} {{ number_format($totals['total_after_tax'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
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
        }
    </script>
@endsection
