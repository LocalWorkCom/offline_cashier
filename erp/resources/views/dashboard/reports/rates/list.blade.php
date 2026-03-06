@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('rate.Rates')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);"
                            onclick="window.location.href='{{ route('reports.rates.list') }}'">@lang('rate.Rates')</a>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-4 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header"
                            style="
                        display: flex;
                        justify-content: space-between;">
                            <div class="card-title">
                                @lang('rate.Rates')</div>
                            <!-- Modal for Showing a Rate -->
                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('rate.ShowRate')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('rate.Client')</label>
                                                    <p id="show-client" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('rate.Value')</label>
                                                    <p id="show-value" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('rate.Note')</label>
                                                    <p id="show-note" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('rate.active')</label>
                                                    <p id="show-active" class="form-control-static"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-success label-btn" onclick="printFormattedReport()"
                                                data-bs-dismiss="modal"><i class="fe fe-printer label-btn-icon me-2"></i>@lang('rate.Print')</button>
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">@lang('modal.close')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if (session('message'))
                                <div class="alert alert-solid-info alert-dismissible fade show">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endif
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('category.ID')</th>
                                        <th scope="col">@lang('rate.Client')</th>
                                        <th scope="col">@lang('rate.Value')</th>
                                        <th scope="col">@lang('rate.Note')</th>
                                        <th scope="col">@lang('rate.active')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php $rowNumber = 1; @endphp
                                @foreach ($rates as $rate)
                                        <tr>
                                            <td>{{ $rowNumber++  }}</td>
                                            <td>{{ $rate->user->name ?? "" }}</td>
                                            <td>{{ $rate->value }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($rate->note, 30) }}</td>
                                            <td>
                                            <span class="badge {{ $rate->active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $rate->active ? __('rate.Active') : __('rate.Inactive') }}
                                            </span>
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view rates', 'admin'))

                                                <!-- Show Button -->
                                                <a href="javascript:void(0);"
                                                    class="btn btn-info-light btn-wave show-rate-btn"
                                                    data-id="{{ $rate->id }}" data-value="{{ $rate->value }}" data-client="{{ $rate->user->name ?? "" }}"
                                                    data-note="{{ $rate->note }}" data-active="{{ $rate->active }}"
                                                    data-bs-toggle="modal" data-bs-target="#showModal">
                                                    @lang('category.show') <i class="ri-eye-line"></i>
                                                </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-4 -->

        </div>
    </div>
@endsection

@section('scripts')
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {


        const showButtons = document.querySelectorAll('.show-rate-btn');
        const showValue = document.getElementById('show-value');
        const showClient = document.getElementById('show-client');
        const showNote = document.getElementById('show-note');
        const showActive = document.getElementById('show-active');

        showButtons.forEach(button => {
            button.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const client = this.getAttribute('data-client');
                const note = this.getAttribute('data-note');
                const active = this.getAttribute('data-active'); // 1 or 0

                showValue.textContent = value;
                showClient.textContent = client;
                showNote.textContent = note;
                showActive.textContent = active == 1 ? '@lang("rate.Active")' : '@lang("rate.Inactive")';
            });
        });

    });

        function printFormattedRatesReport() {
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
            <title>{{ app()->getLocale() == 'en' ? 'Rates Report' : 'تقرير تقييم' }}</title>
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
            <h1 class="title">@lang('print.RatesReport')</h1>
            <br><br>
            <div class="sub-title"><strong>@lang('print.RateDetails')</strong></div>
                <p><strong>@lang('print.ClientName'):</strong> {{ $rate->user->name ?? __('print.unknown') }}</p>
                <p><strong>@lang('print.RateValue'):</strong> {{ $rate->value ?? __('print.unknown') }}</p>
                @php
                    use Carbon\Carbon;
                    $date = $rate->updated_at ? Carbon::parse($rate->updated_at) : null;
                @endphp

                <p><strong>@lang('print.Date'):</strong> {{ $date ? $date->toDateString() : __('print.unknown') }}</p>
                <p><strong>@lang('print.Time'):</strong> {{ $date ? $date->format('h:i A') : __('print.unknown') }}</p>

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

