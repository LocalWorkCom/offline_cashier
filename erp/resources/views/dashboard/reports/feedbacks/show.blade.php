@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('complaints.ShowComplaint')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.feedbacks.list') }}">@lang('complaints.Complaints')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('complaints.ShowComplaint')</li>
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
                            <div class="card-title">@lang('complaints.ShowComplaint')</div>
                            <div class="btn-list float-end">
                                <button type="button" class="d-inline-flex btn btn-primary btn-wave print-button"
                                    onclick="printFormattedReport()">
                                    <i class="ri-printer-line me-1 align-middle"></i>@lang('order.print')
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Client')</label>
                                    <p class="form-text">{{ $complaint['client_name'] ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Phone')</label>
                                    <p class="form-text">{{ $complaint['client_phone'] ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Rate')</label>
                                    <p class="form-text">{{ $complaint['rate'] ?? 0 }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.OrderNum')</label>
                                    <p class="form-text">{{ $complaint['order_number'] ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.InvoiceNum')</label>
                                    <p class="form-text">{{ $complaint['invoice_number'] ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Branch')</label>
                                    <p class="form-text">
                                        @if (app()->getLocale() == 'en')
                                            {{ $complaint['branch']['name_en'] ?? '' }}
                                        @else
                                            {{ $complaint['branch']['name_ar'] ?? '' }}
                                        @endif
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Status')</label>
                                    <p class="form-text">
                                        @if (($complaint['status'] ?? '') == 'solved')
                                            @lang('complaints.Solved')
                                        @elseif(($complaint['status'] ?? '') == 'inprogress')
                                            @lang('complaints.InProgress')
                                        @else
                                            @lang('complaints.Pending')
                                        @endif
                                    </p>
                                </div>

                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('complaints.Complaint')</label>
                                    <p class="form-text">{{ $complaint['complain'] ?? '' }}</p>
                                </div>

                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('complaints.Comment')</label>
                                    <p class="form-text">{{ $complaint['comment'] ?? '' }}</p>
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
            <title>{{ app()->getLocale() == 'en' ? 'Feedbacks reports' : 'تقارير الاراء' }}</title>
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
            <h1 class="title">@lang('print.FeedbackDetails')</h1>
            <br><br>
            <p><strong>@lang('print.ClientName'):</strong> {{ $complaint['client_name'] ?? '' }}</p>
            <p><strong>@lang('print.Phone'):</strong> {{ $complaint['client_phone'] ?? '' }}</p>
            <p><strong>@lang('print.OrderNumber'):</strong> {{ $complaint['order_number'] ?? '' }}</p>
            <p><strong>@lang('print.InvoiceNumber'):</strong> {{ $complaint['invoice_number'] ?? '' }}</p>
            <p><strong>@lang('print.Branch'):</strong> 
                @if (app()->getLocale() == 'en')
                    {{ $complaint['branch']['name_en'] ?? '' }}
                @else
                    {{ $complaint['branch']['name_ar'] ?? '' }}
                @endif
            </p>
            <p><strong>@lang('print.Status'):</strong> 
                @if (($complaint['status'] ?? '') == 'solved')
                    @lang('complaints.Solved')
                @elseif (($complaint['status'] ?? '') == 'inprogress')
                    @lang('complaints.InProgress')
                @else
                    @lang('complaints.Pending')
                @endif
            </p>
            @php
                use Carbon\Carbon;
                $date = isset($complaint['updated_at']) ? Carbon::parse($complaint['updated_at']) : null;
            @endphp
            <p><strong>@lang('print.Date'):</strong> {{ $date ? $date->toDateString() : __('print.unknown') }}</p>
            <p><strong>@lang('print.Time'):</strong> {{ $date ? $date->format('h:i A') : __('print.unknown') }}</p>
            <p><strong>@lang('print.Feedback'):</strong> {{ $complaint['complain'] ? $complaint['complain'] : __('print.none') }}</p>
            <p><strong>@lang('print.Rate'):</strong> {{ $complaint['rate'] ?? 0 }}</p>
            <p><strong>@lang('print.Comment'):</strong> {{ $complaint['comment'] ? $complaint['comment'] : __('print.none') }}</p>
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
