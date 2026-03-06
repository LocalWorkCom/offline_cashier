@extends('layouts.master')

@section('styles')
    <style>
        @media print {
            * {
                color: #000 !important;
            }

            body {
                direction: {{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }};
            }

            .btn-list,
            .page-header-breadcrumb,
            nav.breadcrumb {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('complaints.ShowHangingOrdersDelivery')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.hanging-orders.list') }}">@lang('complaints.HangingOrdersDeliveries')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('complaints.ShowHangingOrdersDelivery')</li>
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
                            <div class="card-title">@lang('complaints.ShowHangingOrdersDelivery')</div>
                            <div class="btn-list float-end">
                                <button type="button" class="d-inline-flex btn btn-primary btn-wave"
                                    onclick="printFormattedReport()">
                                    <i class="ri-printer-line me-1 align-middle"></i>@lang('order.print')
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Client')</label>
                                    <p class="form-text">{{ $complaint->order->client_name ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.ClientPhone')</label>
                                    <p class="form-text">{{ $complaint->order->client_phone ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Delivery')</label>
                                    <p class="form-text">
                                        {{ $complaint->employee->first_name . ' ' . $complaint->employee->last_name ?? '' }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.DeliveryPhone')</label>
                                    <p class="form-text">{{ $complaint->employee->phone_number ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.OrderNum')</label>
                                    <p class="form-text">{{ $complaint->order->order_number ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.InvoiceNum')</label>
                                    <p class="form-text">{{ $complaint->order->invoice_number ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Branch')</label>
                                    <p class="form-text">
                                        {{ app()->getLocale() == 'en' ? $complaint->order?->branch?->name_en : $complaint->order?->branch?->name_ar }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Status')</label>
                                    <p class="form-text">
                                        @if ($complaint->status == 'done')
                                            @lang('complaints.Solved')
                                        @else
                                            @lang('complaints.hold')
                                        @endif
                                    </p>
                                </div>

                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('complaints.Complaint')</label>
                                    <p class="form-text">{{ $complaint->message }}</p>
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
    function printFormattedReport() {
        const printContent = document.querySelector('.main-content').innerHTML;
        const originalContent = document.body.innerHTML;

        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload(); // To reload the page after restoring original content
    }
</script>
