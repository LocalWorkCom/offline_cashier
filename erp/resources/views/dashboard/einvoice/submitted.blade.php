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
            <!-- Filter Buttons for Cashier Machines -->
            <div class="d-flex flex-wrap mb-3">
                @foreach ($cashierMachines as $machine)
                    <a href="{{ route(
                        'dashboard.einvoices.submitted',
                        array_filter([
                            'cashier_machine_id' => $machine->id,
                            'from' => $fromDate,
                            'to' => $toDate,
                        ]),
                    ) }}"
                        class="btn btn-wave text-white me-2 mb-2
                    {{ $cashierMachineId == $machine->id ? 'btn-primary' : 'btn-success' }}">
                        {{ $machine->name_ar }} / {{ $machine->name_en }}
                    </a>
                @endforeach
            </div>
        </div>
        <div class="container-fluid">
            <!-- Filter Buttons for Cashier Machines -->

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">

                            @if ($cashierMachineId)
                                @php
                                    $selectedMachine = $cashierMachines->where('id', $cashierMachineId)->first();
                                @endphp
                                <h5 class="mb-3">
                                    @lang('einvoice.showing_orders_for'): {{ $selectedMachine->name_ar }} / {{ $selectedMachine->name_en }}
                                </h5>
                            @else
                                <h5 class="mb-3">@lang('einvoice.showing_all_orders')</h5>
                            @endif

                            <table class="table table-bordered" id="invoicesTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('einvoice.invoice_number')</th>
                                        <th>@lang('einvoice.total_after_tax')</th>
                                        <th>@lang('einvoice.order_number')</th>
                                        <th>@lang('einvoice.date')</th>
                                        <th>@lang('einvoice.time')</th>
                                        <th>@lang('einvoice.type')</th>
                                        <th>@lang('einvoice.has_return_receipts')</th>
                                        <th>@lang('einvoice.public_url')</th>
                                        <th>@lang('einvoice.status')</th>
                                        <th>@lang('einvoice.actions')</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($filteredOrders))
                                        @foreach ($filteredOrders as $item)
                                            <tr>

                                                <td>{{ $item->invoice->invoice_num }}</td>
                                                <td>{{ $item->invoice->total_after_tax }}</td>
                                                <td>{{ optional($item->invoice->orders)->order_number ?? 'N/A' }}</td>
                                                <td>{{ $item->invoice->orders->date }}</td>
                                                <td>{{ $item->invoice->orders->time }}</td>
                                                <td>{{ $item->invoice_type == 'i' ? __('einvoice.invoice') : __('einvoice.return') }}
                                                </td>
                                                <td>{{ $item->has_return_receipts ? 'yes' : 'no' }}</td>
                                                <td>{{ $item->status }}</td>
                                                <td><a href="{{ $item->public_urls }}" target="_blank"
                                                        class="btn btn-primary">
                                                        @lang('einvoice.view_eta')
                                                    </a>
                                                </td>
                                                <td>{{ $item->status }}</td>
                                                <td>
                                                    <button class="btn btn-primary"
                                                        onclick="handleInvoiceAction({{ $item->invoice->orders->id }})">
                                                        @lang('einvoice.view_details')
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @foreach ($einvoices as $item)
                                            <tr>


                                                <td>{{ $item->invoice->invoice_num }}</td>
                                                <td>{{ $item->invoice->total_after_tax }}</td>
                                                <td>{{ optional($item->invoice->orders)->order_number ?? 'N/A' }}</td>
                                                <td>{{ $item->invoice->orders->date }}</td>
                                                <td>{{ $item->invoice->orders->time }}</td>
                                                <td>{{ $item->invoice_type == 'i' ? __('einvoice.Invoice') : __('einvoice.return') }}

                                                <td>{{ $item->has_return_receipts ? 'yes' : 'no' }}</td>
                                                <td><a href="{{ $item->public_urls }}" target="_blank"
                                                        class="btn btn-primary">
                                                        @lang('einvoice.view_eta')
                                                    </a>
                                                </td>
                                                <td>{{ $item->status }}</td>
                                                <td>
                                                    <button class="btn btn-primary"
                                                        onclick="handleInvoiceAction({{ $item->id }})">
                                                        @lang('einvoice.view_details')
                                                    </button>
                                                    @if ($item->status == 'Invalid' && \Carbon\Carbon::parse($item->invoice->orders->created_at)->diffInHours(now()) < 24)
                                                        <form action="{{ route('dashboard.einvoices.ereceipt.send') }}"
                                                            method="POST" id="uploadForm">
                                                            @csrf
                                                            <input type="hidden" name="test" id="selectedInvoicesUpload"
                                                                value="{{ $item->id }}">
                                                            <button type="submit"
                                                                class="btn btn-info-light btn-wave">@lang('einvoice.uploadAgain')</button>
                                                        </form>
                                                    @endif
                                                </td>

                                            </tr>
                                        @endforeach
                                    @endif
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
    <script>
        function handleInvoiceAction(invoiceId) {
            // Example of handling the action (like redirecting to the invoice details page)
            window.location.href = "/dashboard/einvoices/ereceipt/details/" +
                invoiceId; // Redirect to the invoice details page

            // Or if you prefer to use AJAX to handle the invoice:
            // $.ajax({
            //     url: '/your-endpoint/' + invoiceId, // Specify your route
            //     method: 'GET',
            //     success: function(response) {
            //         // Handle response (e.g., show modal with details)
            //     },
            //     error: function(err) {
            //         console.error('Error:', err);
            //     }
            // });
        }
    </script>
@endsection
