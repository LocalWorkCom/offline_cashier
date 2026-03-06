@extends('layouts.master')

@section('styles')
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0"> @lang('print.CustomerDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.most.customers.list') }}">@lang('reports.reports')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('print.CustomerDetails') </li>
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
                            <div class="h5 mb-0 d-sm-flex d-block align-items-center">

                            </div>
                            <div class="ms-auto mt-md-0 mt-2">
                                <button class="btn btn-secondary me-1" onclick="printFormattedReport()"> @lang('reports.print')<i
                                        class="ri-printer-line ms-1 align-middle d-inline-flex"></i></button>
                                {{--                                        <button class="btn btn-primary" onclick="window.location.href='{{ route('reports.savepdf', ['id' => $Clientdetail->client_id]) }}'"> --}}
                                {{--                                            @lang('reports.savepdf') --}}
                                {{--                                            <i class="ri-file-pdf-line ms-1 align-middle d-inline-flex"></i> --}}
                                {{--                                        </button> --}}

                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-2">
                                            <p class="fw-semibold text-muted mb-1">@lang('reports.logo'):</p>
                                            <p class="fs-15 mb-1">
                                                <img src="{{ asset('build/assets/images/brand-logos/desktop.png') }}"
                                                    alt="logo" class="desktop-dark">
                                            </p>
                                        </div>
                                        <div class="col-xl-2">
                                            <p class="fw-semibold text-muted mb-1">@lang('reports.mainbranch'):</p>
                                            <p class="fs-15 mb-1"> {{ $mainbranchName ?? '' }}</p>
                                        </div>
                                        <div class="col-xl-3">
                                            <p class="fw-semibold text-muted mb-1">@lang('reports.mainbranchaddress'):</p>
                                            <p class="fs-15 mb-1"> {{ $mainbranchAddress ?? '' }}</p>
                                        </div>
                                        <div class="col-xl-3">
                                            <p class="fw-semibold text-muted mb-1">@lang('reports.mainbranchphone'):</p>
                                            <p class="fs-15 mb-1"> {{ $mainbranch->phone ?? '' }}</p>
                                        </div>
                                        <div class="col-xl-2">
                                            <p class="fw-semibold text-muted mb-1">@lang('reports.date') :</p>
                                            <p class="fs-15 mb-1">{{ $formattedDate }}</p>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                            <p class="text-muted mb-2">
                                                @lang('reports.customerdetail'):
                                            </p>
                                            <p class="fw-bold mb-1">
                                                {{ $Clientdetail->Client ? $Clientdetail->Client->name : '' }}
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ $Clientdetail->Client ? $Clientdetail->Client->birth_date : '' }}
                                            </p>
                                            <p class="mb-1 text-muted">
                                                @if ($defaultAddress)
                                                    {{ $defaultAddress->building . ' - ' . $defaultAddress->address_en . ' - ' . $defaultAddress->city . ' - ' . $defaultAddress->state }}
                                                @else
                                                    @lang('reports.noaddress')
                                                @endif
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ $Clientdetail->Client ? $Clientdetail->Client->email : '' }}
                                            </p>
                                            <p class="mb-1 text-muted">
                                                @if ($Clientdetail->Client)
                                                    {{ $Clientdetail->Client->country_code ? '(' . $Clientdetail->Client->country_code . ')' : '' }}
                                                    {{ $Clientdetail->Client->phone ?? '' }}
                                                @endif
                                            </p>
                                            <p class="text-muted"> @lang('reports.morinfo') <a
                                                    href="{{ route('client.show', $Clientdetail->client_id) }}"
                                                    class="text-primary fw-semibold"><u>
                                                        @lang('reports.user')</u></a> @lang('report.detail').</p>
                                        </div>
                                        @if ($branchDetail)
                                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 ms-auto mt-sm-0 mt-3">
                                                <p class="text-muted mb-2">
                                                    @lang('reports.mostbranchordered') :
                                                </p>
                                                <p class="fw-bold mb-1">
                                                    {{ $branchName }}
                                                </p>
                                                <p class="text-muted mb-1">
                                                    {{ $branchAddress }}
                                                </p>
                                                <p class="text-muted mb-1">
                                                    {{ $branchDetail->email }}
                                                </p>
                                                <p class="text-muted">
                                                    {{ $branchDetail->phone }}
                                                </p>
                                            </div>
                                        @endif

                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <div class="table-responsive">
                                        <table class="table nowrap text-nowrap border mt-4">
                                            <thead>
                                                <tr>
                                                    <th>@lang('reports.ordernum')</th>
                                                    <th>@lang('reports.date')</th>
                                                    <th>@lang('reports.useanydis')</th>
                                                    <th>@lang('reports.branchname')</th>
                                                    <th>@lang('reports.paymentmethod')</th>
                                                    <th>@lang('reports.total')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($orders)
                                                    @foreach ($orders as $order)
                                                        <tr>
                                                            <td>
                                                                <div class="fw-semibold">
                                                                    {{ $order->order_number }}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="text-muted">
                                                                    {{ $order->date }}
                                                                </div>
                                                            </td>
                                                            <td class="product-quantity-container">
                                                                {{ $order->coupon_id || $order->discount_id ? __('reports.has') : __('reports.nothas') }}
                                                            </td>
                                                            <td>
                                                                @if (App::getLocale() == 'ar')
                                                                    {{ $order->Branch->name_ar }}
                                                                @else
                                                                    {{ $order->Branch->name_en }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ $order->orderTransactions->isNotEmpty() ? __('reports.' . $order->orderTransactions->first()->payment_method) : '' }}
                                                            </td>
                                                            <td>
                                                                {{ $order->total_price_after_tax . $order->Branch->country->currency_symbol }} </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <td colspan="2">
                                                        <table class="table table-sm text-nowrap mb-0 table-borderless">
                                                            <tbody>

                                                                <tr>
                                                                    <th scope="row">
                                                                        <p class="mb-0 fs-14">@lang('reports.totalprice') :</p>
                                                                    </th>
                                                                    <td>
                                                                        <p class="mb-0 fw-semibold fs-16 text-success">
                                                                            {{ $Clientdetail->total_price }}</p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @if ($mostUsedPaymentMethod)
                                    <div class="col-xl-12">
                                        <div>
                                            <label for="invoice-note" class="form-label">@lang('reports.note') :</label>
                                            <textarea class="form-control form-control-light" id="invoice-note" rows="3">

                                            @if ($mostUsedPaymentMethod->payment_method === 'cash')
@lang('reports.usemostusecashmethod')
@elseif ($mostUsedPaymentMethod->payment_method === 'credit_card')
@lang('reports.usemostusecreditmethod')
@endif
                                        </textarea>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>

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
        <title>{{ app()->getLocale() == 'en' ? 'Customer Report' : 'تقرير عميل' }}</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                direction: {{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }};
                margin: 30px;
            }
            .header, .customer-details, .branch-details, .notes {
                margin-bottom: 20px;
            }
            .header img {
                max-width: 150px;
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
            .details-table {
                width: 100%;
                border-collapse: collapse;
            }
            .details-table td {
                padding: 5px;
                vertical-align: top;
            }
            .fw-bold {
                font-weight: bold;
            }
            .text-muted {
                color: #6c757d;
            }
            .text-success {
                color: #28a745;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="{{ asset('build/assets/images/brand-logos/desktop.png') }}" alt="Logo">
            <h1>{{ app()->getLocale() == 'en' ? 'Customer Report' : 'تقرير عميل' }}</h1>
            <p>{{ $formattedDate }}</p>
        </div>
        <h2>@lang('print.CustomerDetails')</h2>
        
        <!-- Customer and Branch Details -->
        <table class="details-table">
            <tr>
                <td style="width: 50%;">
                    <p class="text-muted mb-2">@lang('reports.customerdetail'):</p>
                    <p class="fw-bold mb-1">{{ $Clientdetail->Client ? $Clientdetail->Client->name : '' }}</p>
                    <p class="text-muted mb-1">{{ $Clientdetail->Client ? $Clientdetail->Client->birth_date : '' }}</p>
                    <p class="text-muted mb-1">
                        @if ($defaultAddress)
                            {{ $defaultAddress->building . ' - ' . $defaultAddress->address_en . ' - ' . $defaultAddress->city . ' - ' . $defaultAddress->state }}
                        @else
                            @lang('reports.noaddress')
                        @endif
                    </p>
                    <p class="text-muted mb-1">{{ $Clientdetail->Client ? $Clientdetail->Client->email : '' }}</p>
                    <p class="text-muted mb-1">
                        @if ($Clientdetail->Client)
                            {{ $Clientdetail->Client->country_code ? '(' . $Clientdetail->Client->country_code . ')' : '' }}
                            {{ $Clientdetail->Client->phone ?? '' }}
                        @endif
                    </p>
                </td>
                @if ($branchDetail)
                <td style="width: 50%;">
                    <p class="text-muted mb-2">@lang('reports.mostbranchordered'):</p>
                    <p class="fw-bold mb-1">{{ $branchName }}</p>
                    <p class="text-muted mb-1">{{ $branchAddress }}</p>
                    <p class="text-muted mb-1">{{ $branchDetail->email }}</p>
                    <p class="text-muted mb-1">{{ $branchDetail->phone }}</p>
                </td>
                @endif
            </tr>
        </table>

        <!-- Main Branch Details -->
        <div class="branch-details">
            <p class="text-muted"><strong>@lang('reports.mainbranch'):</strong> {{ $mainbranchName ?? '' }}</p>
            <p class="text-muted"><strong>@lang('reports.mainbranchaddress'):</strong> {{ $mainbranchAddress ?? '' }}</p>
            <p class="text-muted"><strong>@lang('reports.mainbranchphone'):</strong> {{ $mainbranch->phone ?? '' }}</p>
        </div>

        <!-- Orders Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>@lang('reports.ordernum')</th>
                    <th>@lang('reports.date')</th>
                    <th>@lang('reports.useanydis')</th>
                    <th>@lang('reports.branchname')</th>
                    <th>@lang('reports.paymentmethod')</th>
                    <th>@lang('reports.total')</th>
                </tr>
            </thead>
            <tbody>
                @if ($orders)
                    @foreach ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->date }}</td>
                            <td>{{ $order->coupon_id || $order->discount_id ? (app()->getLocale() == 'en' ? 'Yes' : 'نعم') : (app()->getLocale() == 'en' ? 'No' : 'لا') }}</td>
                            <td>{{ app()->getLocale() == 'en' ? $order->Branch->name_en : $order->Branch->name_ar }}</td>
                            <td>{{ $order->orderTransactions->isNotEmpty() ? __('reports.' . $order->orderTransactions->first()->payment_method) : '' }}</td>
                            <td>{{ $order->total_price_after_tax . ' ' . $order->Branch->country->currency_symbol }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3"></td>
                        <td colspan="2">
                            <table class="table-borderless">
                                <tr>
                                    <th scope="row">@lang('reports.totalprice'):</th>
                                    <td class="text-success fw-bold">{{ $Clientdetail->total_price }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="6">@lang('reports.noorders')</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Notes Section -->
        @if ($mostUsedPaymentMethod)
        <div class="notes">
            <h3>@lang('reports.note'):</h3>
            <p>
                @if ($mostUsedPaymentMethod->payment_method === 'cash')
                    @lang('reports.usemostusecashmethod')
                @elseif ($mostUsedPaymentMethod->payment_method === 'credit_card')
                    @lang('reports.usemostusecreditmethod')
                @endif
            </p>
        </div>
        @endif
    </body>
    </html>
`;

            // Write content to the iframe
            // doc.open();
            // doc.write(content);
            // doc.close();

            // // Wait for the content to load before printing
            // iframe.onload = function() {
            //     iframe.contentWindow.print();
            //     document.body.removeChild(iframe); // Remove iframe after printing
            // };
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
