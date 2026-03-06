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
        <h4 class="fw-medium mb-0">@lang('permissions.einvoices')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('permissions.einvoices')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">
                                @lang('permissions.einvoices')

                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('view officer_assign_setting', 'admin'))

                            <a href="{{ route('dashboard.einvoices.setting.list') }}" class="btn btn-primary label-btn">
                                <i class="fe fe-pepole label-btn-icon me-2"></i>
                                @lang('einvoice.addbalance')
                            </a>
                            @endif
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
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <form method="GET" class="d-flex m-auto p-1" action="{{ route('dashboard.einvoices.list') }}">
                                <div class="p-1">
                                    <label for="date" class="form-label">@lang('reports.date')</label>
                                    <input type="date" name="date" class="form-control" id="date" value="{{ request('date') }}">
                                </div>
                                <div class="p-1">
                                    <label for="amount" class="form-label">@lang('einvoice.total')</label>
                                    <input type="number" name="amount" class="form-control" id="amount" value="{{ request('amount') }}">
                                </div>
                                <div class="p-1">
                                    <label for="posname" class="form-label">@lang('einvoice.posname')</label>
                                    <select class="form-select" name="posname">
                                        <option value="">@lang('einvoice.selectposname')</option>
                                        @foreach (getPOS() as $item)
                                            <option value="{{ $item->id }}" {{ request('posname') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="p-1">
                                    <label class="form-label"></label>
                                    <button type="submit" class="btn btn-outline-success btn-wave mt-1">@lang('reports.search')</button>
                                </div>
                                <div class="p-1">
                                    <label class="form-label"></label>
                                    <a href="{{ route('dashboard.einvoices.list') }}" class="btn btn-outline-danger btn-wave mt-1">
                                        @lang('report.reset')
                                    </a>
                                </div>
                            </form>
                            {{-- uuid has value it sent to portal and view it

                                    pint has qr url
                                    sent action disable after 24h

                                    status invalid validation_ar error_msg --}}
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('einvoice.invoice_number')</th>
                                        <th>@lang('einvoice.username')</th>
                                        <th>@lang('einvoice.ordertype')</th>
                                        <th>@lang('einvoice.branchname')</th>
                                        <th>@lang('einvoice.posname')</th>

                                        <th>@lang('einvoice.total')</th>
                                        <th>@lang('einvoice.invoice_type')</th>
                                        <th>@lang('einvoice.submission_date')</th>
                                        <th>@lang('einvoice.uuid')</th>
                                        <th>@lang('einvoice.status')</th>
                                        {{-- <th>@lang('einvoice.Actions')</th> --}}
                                    </tr>

                                </thead>
                                <tbody>
                                    @foreach ($Data as $item)
                                        {{-- {{ dd($item) }} --}}
                                        <tr>
                                            <td>{{ $item['invoice_number'] }}</td>
                                            <td>{{ $item['client_name'] }}</td>
                                            <td>{{ $item['order_type'] }}</td>
                                            <td>{{ $item['branch_name'] }}</td>
                                            <td>{{ $item['pos'] }}</td>

                                            <td>{{ $item['total'] }}</td>

                                            <td>
                                                @if ($item['invoice']->invoice_type === 'i')
                                               {{ __('einvoice.Invoice')}}

                                                @elseif($item['invoice']->invoice_type === 'c')
                                                {{ __('einvoice.Credit')}}
                                                @else
                                                    {{ $item['invoice']->invoice_type }}
                                                @endif
                                            </td>
                                            <td>{{ $item['invoice']->submission_date ?? __('einvoice.notuploaded') }}</td>
                                            <td>{{ $item['invoice']->uuid ?? __('einvoice.nothave')}}</td>
                                            <td>{{ $item['invoice']->status ?? __('einvoice.nothave')}}</td>
                                            {{-- <td>
                                                @if ($item['invoice']->uuid)
                                                @if (auth('admin')->user()->hasPermissionTo('view einvoices', 'admin')
                                                    <a href="{{ route('dashboard.einvoices.show',['id'=>$item['invoice']->id]) }}" class="btn btn-info-light btn-wave show-order">
                                                        @lang('order.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('print einvoices', 'admin'))
                                                    <a class="btn btn-info-light btn-wave print-order"
                                                        data-invoice-number="{{ $item['invoice_number'] }}"
                                                        data-client-name="{{ $item['client_name'] }}"
                                                        data-order-type="{{ $item['order_type'] }}"
                                                        data-branch-name="{{ $item['branch_name'] }}"
                                                        data-invoice-type="{{ $item['invoice']->invoice_type }}"
                                                        data-submission-date="{{ $item['invoice']->submission_date }}"
                                                        data-uuid="{{ $item['invoice']->uuid }}"
                                                        data-status-order="{{ $item['invoice']->status }}"
                                                        data-total-price="{{ $item['invoice']->order->total_price_after_tax }}"
                                                        data-status-einvoice="{{ $item['invoice']->status }}">
                                                        @lang('order.print') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                                @endif
                                                @if (!$item['invoice']->uuid)
                                                    @if (auth('admin')->user()->hasPermissionTo('allowPort einvoices', 'admin'))
                                                        <a href="javascript:void(0)"
                                                            class="btn btn-info-light btn-wave show-order"
                                                            @if ($item['is_older_than_24h']) disabled
                                                       onclick="showForbiddenAlert()" @endif>
                                                            @lang('einvoice.send') <i class="ri-eye-line"></i>
                                                        </a>
                                                    @endif
                                                @endif


                                            </td> --}}
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
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showForbiddenAlert() {
            Swal.fire({
                icon: 'warning',
                title: '@lang('einvoice.action_forbidden')',
                text: '@lang('einvoice.action_forbidden')',
                confirmButtonText: '@lang('einvoice.ok_button')',
            });
        }
    </script>
    <script>
        document.querySelectorAll('.print-order').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                // Get the data from the clicked row
                const invoiceNumber = event.target.getAttribute('data-invoice-number');
                const clientName = event.target.getAttribute('data-client-name');
                const orderType = event.target.getAttribute('data-order-type');
                const branchName = event.target.getAttribute('data-branch-name');
                const invoiceType = event.target.getAttribute('data-invoice-type');
                const submissionDate = event.target.getAttribute('data-submission-date');
                const uuid = event.target.getAttribute('data-uuid');
                const status = event.target.getAttribute('data-status');
                const totalPrice = event.target.getAttribute('data-total-price');
                const statusOrder = event.target.getAttribute('data-status-order');

                console.log(invoiceNumber);

                // Call the print function with the relevant data
                printFormattedReport(invoiceNumber, clientName, orderType, branchName, invoiceType,
                    submissionDate, uuid, status, statusOrder, totalPrice);
            });
        });

        // Print formatted report
        function printFormattedReport(invoiceNumber, clientName, orderType, branchName, invoiceType, submissionDate, uuid,
            status, statusOrder, totalPrice) {
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
            <title>{{ app()->getLocale() == 'en' ? 'Invoice Report' : 'تقرير الفاتورة' }}</title>
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
            <h1 class="title">@lang('print.InvoiceNumber') - #${invoiceNumber}</h1>

            <div class="sub-title"><strong>@lang('print.ClientDetails')</strong></div>
            <ul>
                <li><strong>@lang('print.Name'):</strong> ${clientName}</li>
                <li><strong>@lang('print.OrderType'):</strong> ${orderType}</li>
                <li><strong>@lang('print.BranchName'):</strong> ${branchName}</li>
                <li><strong>@lang('print.TotalPrice'):</strong> ${branchName}</li>
                <li><strong>@lang('print.Status'):</strong> ${branchName}</li>

            </ul>

            <div class="sub-title"><strong>@lang('print.InvoiceDetails')</strong></div>
            <ul>
                <li><strong>@lang('print.InvoiceType'):</strong> ${invoiceType}</li>
                <li><strong>@lang('print.SubmissionDate'):</strong> ${submissionDate}</li>
                <li><strong>@lang('print.UUID'):</strong> ${uuid}</li>
                <li><strong>@lang('print.Status'):</strong> ${status}</li>
            </ul>
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
