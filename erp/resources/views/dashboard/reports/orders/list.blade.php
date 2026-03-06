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
        <h4 class="fw-medium mb-0">@lang('order.orders')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('order.orders')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <div>
                <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-4" id="filter-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active bg-light text-dark" data-status="all" href="javascript:void(0);">
                            @lang('order.all')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link bg-warning text-dark" data-status="pending" href="javascript:void(0);">
                            @lang('order.pending')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link bg-primary text-white" data-status="in_progress" href="javascript:void(0);">
                            @lang('order.inprogress')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link bg-danger text-white" data-status="readyforpickup" href="javascript:void(0);">
                            @lang('order.readyForPickup')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link bg-success text-white" data-status="completed" href="javascript:void(0);">
                            @lang('order.completed')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link bg-danger text-white" data-status="cancelled" href="javascript:void(0);">
                            @lang('order.cancelled')
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Filter Rows -->
            <div class="row mb-2">
                <div class="col-md-6">
                    <label for="type-filter">@lang('order.type')</label>
                    <select id="type-filter" class="form-select">
                        <option value="all">@lang('order.all')</option>
                        <option value="delivery">@lang('order.delivery')</option>
                        <option value="callcenter">@lang('order.callcenter')</option>
                        <option value="takeaway">@lang('order.takeaway')</option>
                        <option value="online">@lang('order.online')</option>
                        <option value="dine-in">@lang('order.dine-in')</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="branch-filter">@lang('order.branch')</label>
                    <select id="branch-filter" class="form-select">
                        <option value="all">@lang('order.all_branches')</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6">
                    <label for="date-from">@lang('order.date_from')</label>
                    <input type="date" id="date-from" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="date-to">@lang('order.date_to')</label>
                    <input type="date" id="date-to" class="form-control">
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="source-filter">@lang('order.source')</label>
                    <select id="source-filter" class="form-select">
                        <option value="all">@lang('order.all_sources')</option>
                        <option value="waiter">@lang('order.waiter')</option>
                        <option value="cashier">@lang('order.cashier')</option>
                        <option value="online">@lang('order.online')</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex justify-content-end align-items-end">
                    <div id="source-count-display" class="alert alert-primary mb-0 me-2" style="min-width: 180px;">
                        <strong>@lang('order.orders_count'):</strong>
                        <span id="source-count">0</span>
                    </div>
                    <button id="reset-filters" class="btn btn-danger">
                        <i class="ri-refresh-line"></i> @lang('order.reset_filters')
                    </button>
                </div>
            </div>
            <!-- Totals Row -->
            <div class="row mb-3" id="order-totals-row">
                <div class="col-md-2">
                    <div class="alert alert-info text-center mb-2" id="total-before-tax-box">
                        <strong>@lang('order.total_price_befor_tax')</strong><br>
                        <span id="total-before-tax">0</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="alert alert-success text-center mb-2" id="total-after-tax-box">
                        <strong>@lang('order.total_price_after_tax')</strong><br>
                        <span id="total-after-tax">0</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="alert alert-primary text-center mb-2" id="total-cash-box">
                        <strong>@lang('order.total_cash_paid')</strong><br>
                        <span id="total-cash">0</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="alert alert-warning text-center mb-2" id="total-credit-box">
                        <strong>@lang('order.total_credit_paid')</strong><br>
                        <span id="total-credit">0</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="alert alert-secondary text-center mb-2" id="total-service-fees-box">
                        <strong>@lang('order.total_service_fees')</strong><br>
                        <span id="total-service-fees">0</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="alert alert-danger text-center mb-2" id="total-coupon-box">
                        <strong>@lang('order.total_coupon_value')</strong><br>
                        <span id="total-coupon">0</span>
                    </div>
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
                            @php
                                $hasCancelledOrders = $orders->contains('last_status', 'cancelled');
                            @endphp
                            <div style="overflow-x: auto; width: 100%;">
                                <table id="file-export" class="table table-bordered text-nowrap"
                                    style="min-width: 1500px; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th scope="col">@lang('order.ID')</th>
                                            <th scope="col">@lang('order.invoice_num')</th>
                                            <th scope="col">@lang('order.order_number')</th>
                                            <th scope="col">@lang('order.date')</th>
                                            <th scope="col">@lang('order.time')</th>
                                            <th scope="col">@lang('order.type')</th>
                                            <th scope="col">@lang('order.table_number')</th>
                                            <th scope="col">@lang('order.client_name')</th>
                                            <th scope="col">@lang('order.client_phone')</th>
                                            <th scope="col">@lang('order.subtotal_price')</th>
                                            <th scope="col">@lang('order.tax_value')</th>
                                            <th scope="col">@lang('order.total_price')</th>
                                            <th scope="col">@lang('order.order_items_count')</th>
                                            <th scope="col">@lang('order.coupon_value')</th>
                                            <th scope="col">@lang('order.status')</th>
                                            <th scope="col">@lang('order.status_paid')</th>
                                            <th scope="col">@lang('order.payment_method')</th>
                                            <th scope="col">@lang('order.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders as $order)
                                            @php
                                                $subtotal = $order->total_price_befor_tax;
                                                if (isset($order->tax_application) && $order->tax_application == 1) {
                                                    $subtotal += $order->tax_value;
                                                }
                                                if ($order->status === 'cancelled') {
                                                    $order_items_count = $order->orderDetails->sum('quantity');
                                                } else {
                                                    $order_items_count = $order->orderDetailsWithoutCancel->sum(
                                                        'quantity',
                                                    );
                                                }
                                            @endphp
                                            <tr data-status="{{ strtolower($order->last_status) }}"
                                                data-branch="{{ $order->branch_id }}" data-date="{{ $order->date }}"
                                                data-source="{{ strtolower($order->source) }}"
                                                data-type="{{ strtolower($order->type) }}"
                                                data-order="{!! htmlspecialchars(
                                                    json_encode([
                                                        'total_price_befor_tax' => $order->total_price_befor_tax,
                                                        'total_price_after_tax' => $order->total_price_after_tax,
                                                        'service_fees' => $order->service_fees,
                                                        'coupon_value' => $order->coupon_value,
                                                        'transaction' => $order->transaction
                                                            ? [
                                                                'payment_status' => $order->transaction['payment_status'] ?? '',
                                                                'payment_method' => $order->transaction['payment_method'] ?? '',
                                                                'paid' => $order->transaction['paid'] ?? 0,
                                                            ]
                                                            : null,
                                                    ]),
                                                    ENT_QUOTES,
                                                    'UTF-8',
                                                ) !!}">
                                                <td>{{ $order->id }}</td>
                                                <td>{{ $order->invoice_number }}</td>
                                                <td>{{ $order->order_number }}</td>
                                                <td>{{ $order->date }}</td>
                                                <td>{{ $order->created_at->format('H:i') }}</td>
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
                                                @if ($order->Table)
                                                    <td>{{ $order->Table->table_number }}</td>
                                                @else
                                                    <td>-----</td>
                                                @endif
                                                <td>
                                                    @if ($order->Client && $order->Client->flag != 'unknown')
                                                        {{ $order->Client->name }}
                                                    @elseif ($order->address)
                                                        {{ $order->address->address_name }}
                                                    @else
                                                        {{ $order->client_name ?? '-----' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($order->Client && $order->Client->flag != 'unknown')
                                                        {{ $order->Client->phone }}
                                                    @elseif ($order->address)
                                                        {{ $order->address->address_phone }}
                                                    @else
                                                        {{ $order->client_phone ?? '-----' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ isset($subtotal) ? number_format($subtotal, 2) : '-----' }}
                                                    {{ $order->branch->country->currency_symbol ?? '' }}
                                                </td>
                                                <td>
                                                    {{ isset($order->tax_value) ? number_format($order->tax_value, 2) : '-----' }}
                                                    {{ $order->branch->country->currency_symbol ?? '' }}
                                                </td>
                                                <td>
                                                    {{ isset($order->total_price_after_tax) ? number_format($order->total_price_after_tax, 2) : '-----' }}
                                                    {{ $order->branch->country->currency_symbol ?? '' }}
                                                </td>
                                                <td>
                                                    {{ $order_items_count ?? '-----' }}
                                                </td>
                                                <td>
                                                    {{ isset($order->coupon_value) && $order->coupon_value != 0 ? number_format($order->coupon_value, 2) : '-----' }}
                                                    {{ $order->branch->country->currency_symbol ?? '' }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ [
                                                            'pending' => 'bg-warning-transparent',
                                                            'in_progress' => 'bg-primary-transparent',
                                                            'completed' => 'bg-success-transparent',
                                                            'on_way' => 'bg-info-transparent',
                                                            'readyForPickup' => 'bg-danger-transparent',
                                                            'delivered' => 'bg-secondary-transparent',
                                                            'cancelled' => 'bg-danger-transparent',
                                                        ][strtolower($order->last_status)] ?? 'bg-info-transparent' }}">
                                                        @lang('order.' . strtolower($order->last_status))
                                                    </span>
                                                </td>
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
                                                <td>
                                                    @if ($order->transaction)
                                                        <span
                                                            class="badge {{ [
                                                                'cash' => 'bg-primary-transparent',
                                                                'credit_card' => 'bg-primary-transparent',
                                                                'online' => 'bg-primary-transparent',
                                                            ][strtolower($order['transaction']['payment_method'])] ?? 'bg-primary-transparent' }}">
                                                            @lang('order.' . strtolower($order['transaction']['payment_method']))
                                                        </span>
                                                    @else
                                                        <span>-----</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (auth('admin')->user()->hasPermissionTo('detail report_orders', 'admin'))
                                                        <a href="{{ route('reports.orders.show', $order->id) }}"
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
            // Custom DataTables filter
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const row = $('#file-export').DataTable().row(dataIndex).node();
                const rowStatus = $(row).data('status');
                const rowBranch = $(row).data('branch');
                const rowDate = $(row).data('date');
                const rowSource = $(row).data('source');
                const rowType = $(row).data('type');

                const selectedStatus = $('#filter-tabs .nav-link.active').data('status');
                const selectedBranch = $('#branch-filter').val();
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();
                const selectedSource = $('#source-filter').val();
                const selectedType = $('#type-filter').val();

                const statusMatch = selectedStatus === 'all' || rowStatus === selectedStatus;
                const branchMatch = selectedBranch === 'all' || rowBranch == selectedBranch;
                const dateMatch = (!dateFrom || new Date(rowDate) >= new Date(dateFrom)) &&
                    (!dateTo || new Date(rowDate) <= new Date(dateTo));
                const sourceMatch = selectedSource === 'all' || rowSource === selectedSource;
                const typeMatch = selectedType === 'all' || rowType === selectedType;

                return statusMatch && branchMatch && dateMatch && sourceMatch && typeMatch;
            });

            const table = $('#file-export').DataTable({
                paging: true,
                ordering: true,
                responsive: true,
                scrollX: true,
                dom: 'Bfrtip',
                // buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            });

            // Update source count display
            function updateSourceCount() {
                const visibleRows = table.rows({
                    search: 'applied'
                }).nodes();
                const count = $(visibleRows).length;
                $('#source-count').text(count);
                $('#source-count-display').show();
            }

            // Update order totals display
            function updateOrderTotals() {
                let totalBeforeTax = 0;
                let totalAfterTax = 0;
                let totalCash = 0;
                let totalCredit = 0;
                let totalServiceFees = 0;
                let totalCoupon = 0;

                $('#file-export').DataTable().rows({
                    search: 'applied'
                }).every(function() {
                    const $row = $(this.node());
                    const order = $row.data('order');
                    if (order) {
                        totalBeforeTax += parseFloat(order.total_price_befor_tax) || 0;
                        totalAfterTax += parseFloat(order.total_price_after_tax) || 0;
                        totalServiceFees += parseFloat(order.service_fees) || 0;
                        totalCoupon += parseFloat(order.coupon_value) || 0;
                        if (order.transaction) {
                            if (order.transaction.payment_status && order.transaction.payment_status
                                .toLowerCase() === 'paid') {
                                if (order.transaction.payment_method === 'cash') {
                                    totalCash += parseFloat(order.transaction.paid) || 0;
                                }
                                if (order.transaction.payment_method === 'credit_card' || order.transaction
                                    .payment_method === 'credit') {
                                    totalCredit += parseFloat(order.transaction.paid) || 0;
                                }
                            }
                        }
                    }
                });
                $('#total-before-tax').text(totalBeforeTax.toFixed(2));
                $('#total-after-tax').text(totalAfterTax.toFixed(2));
                $('#total-cash').text(totalCash.toFixed(2));
                $('#total-credit').text(totalCredit.toFixed(2));
                $('#total-service-fees').text(totalServiceFees.toFixed(2));
                $('#total-coupon').text(totalCoupon.toFixed(2));
            }

            // Filter tab click event
            $('#filter-tabs .nav-link').on('click', function() {
                $('#filter-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
                table.draw();
                updateSourceCount();
                updateOrderTotals();
            });

            // Other filter change events
            $('#branch-filter, #date-from, #date-to, #source-filter, #type-filter').on('change', function() {
                table.draw();
                updateSourceCount();
                updateOrderTotals();
            });

            // Reset all filters
            $('#reset-filters').on('click', function() {
                // Reset status filter to "all"
                $('#filter-tabs .nav-link').removeClass('active');
                $('#filter-tabs .nav-link[data-status="all"]').addClass('active');

                // Reset branch filter
                $('#branch-filter').val('all');

                // Reset date filters
                $('#date-from').val('');
                $('#date-to').val('');

                // Reset source filter
                $('#source-filter').val('all');
                $('#type-filter').val('all'); // Reset type filter
                updateSourceCount();
                updateOrderTotals();

                // Redraw the table to show all records
                table.draw();
            });

            // Initial count update
            updateSourceCount();
            updateOrderTotals();
        });
    </script>
@endsection
