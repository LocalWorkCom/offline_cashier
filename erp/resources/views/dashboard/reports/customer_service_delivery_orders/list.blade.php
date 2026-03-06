@extends('layouts.master')
@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endsection
@section('content')
    <style>
        .nav-tabs .nav-link {
            margin-top: 50px;
            margin-right: 10px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }

        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }
    </style>
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('order.customer_service_delivery_orders')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('order.customer_service_delivery_orders')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="mb-5">
            </div>
            <!-- Add these search inputs with your other filters -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="branch-filter">@lang('order.branch')</label>
                    <select id="branch-filter" class="form-select">
                        <option value="all">@lang('order.all_branches')</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date-from">@lang('order.date_from')</label>
                    <input type="date" id="date-from" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="date-to">@lang('order.date_to')</label>
                    <input type="date" id="date-to" class="form-control">
                </div>
            </div>

            <!-- Add these search inputs below your date filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="order-search">@lang('order.order_number')</label>
                    <input type="text" id="order-search" class="form-control" placeholder="@lang('order.search_order_number')">
                </div>
                <div class="col-md-3">
                    <label for="invoice-search">@lang('order.invoice_num')</label>
                    <input type="text" id="invoice-search" class="form-control" placeholder="@lang('order.search_invoice_number')">
                </div>
                <div class="col-md-3">
                    <label for="callcenter-search">@lang('order.call_center_name')</label>
                    <input type="text" id="callcenter-search" class="form-control" placeholder="@lang('order.search_call_center_name')">
                </div>
                <div class="col-md-3">
                    <label for="delivery-search">@lang('order.delivery_name')</label>
                    <input type="text" id="delivery-search" class="form-control" placeholder="@lang('order.search_delivery_name')">
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">@lang('order.customer_service_delivery_orders')</div>
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

                            <table class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('order.ID')</th>
                                        <th scope="col">@lang('order.call_center_name')</th>
                                        <th scope="col">@lang('order.delivery_name')</th>
                                        <th scope="col">@lang('order.order_number')</th>
                                        <th scope="col">@lang('order.invoice_num')</th>
                                        <th scope="col">@lang('order.date')</th>
                                        <th scope="col">@lang('order.type')</th>
                                        <th scope="col">@lang('order.branch')</th>
                                        <th scope="col">@lang('order.client')</th>
                                        <th scope="col">@lang('order.total_price')</th>
                                        <th scope="col">@lang('order.status')</th>
                                        <th scope="col">@lang('order.status_paid')</th>
                                        <th scope="col">@lang('order.payment_method')</th>
                                        <th scope="col">@lang('order.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr data-status="{{ strtolower($order->last_status) }}"
                                            data-branch="{{ $order->branch_id }}" data-date="{{ $order->date }}">
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->customerService->first_name . ' ' . $order->customerService->last_name }}</td>
                                            <td>{{ $order->delivery->first_name . ' ' . $order->delivery->last_name }}</td>
                                            <td>{{ $order->order_number }}</td>
                                            <td>{{ $order->invoice_number }}</td>
                                            <td>{{ $order->date }}</td>
                                            <!-- Order Type -->
                                            <td>
                                                <span
                                                    class="badge {{ [
                                                        'delivery' => 'bg-primary-transparent',
                                                        'callcenter' => 'bg-info-transparent',
                                                        'takeaway' => 'bg-secondary-transparent',
                                                        'online' => 'bg-success-transparent',
                                                        'dine-in' => 'bg-warning-transparent',
                                                    ][strtolower($order->type)] ?? 'bg-light-transparent' }}">
                                                    @lang('order.' . strtolower($order->type))
                                                </span>
                                            </td>
                                            <td>
                                                {{ $order->branch ? (app()->getLocale() === 'ar' ? $order->branch->name_ar : $order->branch->name_en) : __('order.none') }}
                                            </td>
                                            <td>{{ $order->client->name ?? __('order.client_deleted') }}</td>
                                            <td>{{ $order->total_price_after_tax }}
                                                {{ $order->branch->country->currency_symbol }}</td>
                                            <!-- Order Status -->
                                            <td>
                                                <span
                                                    class="badge {{ [
                                                        'pending' => 'bg-warning-transparent',
                                                        'in_progress' => 'bg-primary-transparent',
                                                        'completed' => 'bg-success-transparent',
                                                        'on_way' => 'bg-info-transparent',
                                                        'delivered' => 'bg-secondary-transparent',
                                                        'cancelled' => 'bg-danger-transparent',
                                                    ][strtolower($order->last_status)] ?? 'bg-light-transparent' }}">
                                                    @lang('order.' . strtolower($order->last_status))
                                                </span>
                                            </td>
                                            <!-- Payment Status -->
                                            <td>
                                                @if ($order->transaction)
                                                    <span
                                                        class="badge {{ [
                                                            'paid' => 'bg-success-transparent',
                                                            'unpaid' => 'bg-danger-transparent',
                                                        ][strtolower($order['transaction']['payment_status'])] ?? 'bg-light-transparent' }}">
                                                        @lang('order.' . strtolower($order['transaction']['payment_status']))
                                                    </span>
                                                @else
                                                    <span>-----</span>
                                                @endif
                                            </td>
                                            <!-- Payment Method -->
                                            <td>
                                                @if ($order->transaction)
                                                    <span
                                                        class="badge {{ [
                                                            'cash' => 'bg-primary-transparent',
                                                            'credit_card' => 'bg-info-transparent',
                                                            'online' => 'bg-success-transparent',
                                                        ][strtolower($order['transaction']['payment_method'])] ?? 'bg-light-transparent' }}">
                                                        @lang('order.' . strtolower($order['transaction']['payment_method']))
                                                    </span>
                                                @else
                                                    <span>-----</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('detail report_customer_service_delivery_orders', 'admin'))
                                                    <a href="{{ route('reports.customer_service_delivery_orders.show', $order->id) }}"
                                                        class="btn btn-info-light btn-wave show-order">
                                                        @lang('order.show') <i class="ri-eye-line"></i>
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
        </div>
    </div>
@endsection
@section('scripts')
    <!-- REQUIRED DATA-TABLES SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script>
        $(document).ready(function() {
            // Add data attributes for searching
            $('table tbody tr').each(function() {
                const $row = $(this);
                $row.attr('data-order-number', $row.find('td:eq(3)').text().trim().toLowerCase());
                $row.attr('data-invoice-number', $row.find('td:eq(4)').text().trim().toLowerCase());
                $row.attr('data-callcenter-name', $row.find('td:eq(1)').text().trim().toLowerCase());
                $row.attr('data-delivery-name', $row.find('td:eq(2)').text().trim().toLowerCase());
            });

            function filterTable() {
                const selectedBranch = $('#branch-filter').val();
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();
                const orderSearch = $('#order-search').val().toLowerCase();
                const invoiceSearch = $('#invoice-search').val().toLowerCase();
                const callcenterSearch = $('#callcenter-search').val().toLowerCase();
                const deliverySearch = $('#delivery-search').val().toLowerCase();

                $('table tbody tr').each(function() {
                    const $row = $(this);
                    const rowBranch = $row.data('branch');
                    const rowDate = $row.data('date');
                    const rowOrderNumber = $row.data('order-number');
                    const rowInvoiceNumber = $row.data('invoice-number');
                    const rowCallcenterName = $row.data('callcenter-name');
                    const rowDeliveryName = $row.data('delivery-name');

                    // Apply all filters
                    const branchMatch = selectedBranch === 'all' || rowBranch == selectedBranch;
                    const dateMatch = (!dateFrom || rowDate >= dateFrom) && (!dateTo || rowDate <= dateTo);
                    const orderMatch = !orderSearch || rowOrderNumber.includes(orderSearch);
                    const invoiceMatch = !invoiceSearch || rowInvoiceNumber.includes(invoiceSearch);
                    const callcenterMatch = !callcenterSearch || rowCallcenterName.includes(callcenterSearch);
                    const deliveryMatch = !deliverySearch || rowDeliveryName.includes(deliverySearch);

                    if (branchMatch && dateMatch && orderMatch && invoiceMatch && callcenterMatch && deliveryMatch) {
                        $row.show();
                    } else {
                        $row.hide();
                    }
                });
            }

            // Add event listeners for all filters
            $('#branch-filter, #date-from, #date-to').on('change', filterTable);
            $('#order-search, #invoice-search, #callcenter-search, #delivery-search').on('keyup', filterTable);

            // Reset all filters (add this button to your HTML if needed)
            $('#reset-filters').on('click', function() {
                $('#branch-filter').val('all');
                $('#date-from, #date-to, #order-search, #invoice-search, #callcenter-search, #delivery-search').val('');
                filterTable();
            });

            // Initial filter
            filterTable();
        });
    </script>
@endsection
