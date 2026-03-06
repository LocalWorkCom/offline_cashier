@extends('layouts.master')
@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
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
        .dataTables_filter {
            display: none; /* Hide default search box */
        }
    </style>
@endsection
@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('order.tables_orders_revenue')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('order.tables_orders_revenue')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="mt-4"></div>
            <!-- Filters Row -->
            <div class="row mt-5 mb-4">
                <div class="col-md-2">
                    <label for="branch-filter">@lang('order.branch')</label>
                    <select id="branch-filter" class="form-select">
                        <option value="">@lang('order.all_branches')</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date-from">@lang('order.date_from')</label>
                    <input type="date" id="date-from" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="date-to">@lang('order.date_to')</label>
                    <input type="date" id="date-to" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="order-number-filter">@lang('order.order_number')</label>
                    <input type="text" id="order-number-filter" class="form-control" placeholder="@lang('order.order_number')">
                </div>
                <div class="col-md-2">
                    <label for="invoice-number-filter">@lang('order.invoice_num')</label>
                    <input type="text" id="invoice-number-filter" class="form-control" placeholder="@lang('order.invoice_num')">
                </div>
                <div class="col-md-2">
                    <label for="table-filter">@lang('order.table')</label>
                    <input type="text" id="table-filter" class="form-control" placeholder="@lang('order.table')">
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">@lang('order.orders')</div>
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

                            <table id="orders-table" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                <tr>
                                    <th>@lang('order.ID')</th>
                                    <th>@lang('order.table')</th>
                                    <th>@lang('order.order_number')</th>
                                    <th>@lang('order.invoice_num')</th>
                                    <th>@lang('order.date')</th>
                                    <th>@lang('order.type')</th>
                                    <th>@lang('order.branch')</th>
                                    <th>@lang('order.client')</th>
                                    <th>@lang('order.total_price')</th>
                                    <th>@lang('order.status')</th>
                                    <th>@lang('order.status_paid')</th>
                                    <th>@lang('order.payment_method')</th>
                                    <th>@lang('order.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->Table ? (app()->getLocale() === 'ar' ? $order->Table->name_ar : $order->Table->name_en) : __('order.none') }}</td>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->invoice_number }}</td>
                                        <td>{{ $order->date }}</td>
                                        <!-- Order Type -->
                                        <td>
                                                <span class="badge {{ [
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
                                        <td>{{ $order->total_price_after_tax }} {{ $order->branch->country->currency_symbol }}</td>
                                        <!-- Order Status -->
                                        <td>
                                                <span class="badge {{ [
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
                                                <span class="badge {{ [
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
                                                <span class="badge {{ [
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
                                        <!-- Actions -->
                                        <td>
                                            @if (auth('admin')->user()->hasPermissionTo('detail report_booking_revenue', 'admin'))
                                                <a href="{{ route('reports.booking_revenue.show', $order->id) }}"
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

            const table = $('#orders-table').DataTable({
                initComplete: function() {
                    // Apply the search
                    this.api().columns().every(function() {
                        var column = this;
                    });
                }
            });

            // Status filter
            $('#filter-tabs .nav-link').on('click', function() {
                $('#filter-tabs .nav-link').removeClass('active');
                $(this).addClass('active');

                const status = $(this).data('status');
                if (status === 'all') {
                    table.columns(9).search('').draw();
                } else {
                    table.columns(9).search('^' + $(this).text().trim() + '$', true, false).draw();
                }
            });

            // Order number filter
            $('#order-number-filter').on('keyup', function() {
                table.columns(2).search(this.value).draw();
            });

            // Invoice number filter
            $('#invoice-number-filter').on('keyup', function() {
                table.columns(3).search(this.value).draw();
            });

            // Table filter
            $('#table-filter').on('keyup', function() {
                table.columns(1).search(this.value).draw();
            });

            // Branch filter
            $('#branch-filter').on('change', function() {
                const branchId = $(this).val();
                if (branchId === '') {
                    table.columns(6).search('').draw();
                } else {
                    table.columns(6).search($(this).find('option:selected').text().trim(), true, false).draw();
                }
            });

            // Date range filter
            $('#date-from, #date-to').on('change', function() {
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();

                // Custom filtering for date range
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        const orderDate = new Date(data[4]).getTime();
                        const fromDate = dateFrom ? new Date(dateFrom).getTime() : null;
                        const toDate = dateTo ? new Date(dateTo).getTime() : null;

                        if ((fromDate === null || orderDate >= fromDate) &&
                            (toDate === null || orderDate <= toDate)) {
                            return true;
                        }
                        return false;
                    }
                );

                table.draw();

                // Remove the custom filter function after drawing
                $.fn.dataTable.ext.search.pop();
            });
        });
    </script>
@endsection
