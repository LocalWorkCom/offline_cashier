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
            <!-- Branch Selection and Date Range Filters -->
            <div class="row mb-4">
                <div class="col-md-3 mt-3">
                    <label for="branch-filter">@lang('order.branch')</label>
                    <select id="branch-filter" class="form-select">
                        <option value="all">@lang('order.all_branches')</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mt-3">
                    <label for="delivery-filter">@lang('order.delivery')</label>
                    <select id="delivery-filter" class="form-select">
                        <option value="all">@lang('order.all_deliveries')</option>
                        @foreach ($deliveries as $delivery)
                            <option value="{{ $delivery->id }}">
                                {{ $delivery->first_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mt-3">
                    <label for="date-from">@lang('order.date_from')</label>
                    <input type="date" id="date-from" class="form-control">
                </div>
                <div class="col-md-3 mt-3">
                    <label for="date-to">@lang('order.date_to')</label>
                    <input type="date" id="date-to" class="form-control">
                </div>
                <!-- Payment Status Filter -->
                <div class="col-md-3 mt-3">
                    <label for="payment-status-filter">@lang('order.status_paid')</label>
                    <select id="payment-status-filter" class="form-select">
                        <option value="all">@lang('order.all_payment_statuses')</option>
                        @foreach ($paymentStatuses as $status)
                            <option value="{{ $status }}">@lang('order.' . $status)</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="col-md-3 mt-3">
                    <label for="payment-method-filter">@lang('order.payment_method')</label>
                    <select id="payment-method-filter" class="form-select">
                        <option value="all">@lang('order.all_payment_methods')</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method }}">@lang('order.' . $method)</option>
                        @endforeach
                    </select>
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
                                // Check if any order has the status 'cancelled'
                                $hasCancelledOrders = $orders->contains('last_status', 'cancelled');
                            @endphp

                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('order.ID')</th>
                                        <th scope="col">@lang('order.invoice_num')</th>
                                        <th scope="col">@lang('order.date')</th>
                                        <th scope="col">@lang('order.type')</th>
                                        <th scope="col">@lang('order.branch')</th>
                                        <th scope="col">@lang('order.client')</th>
                                        <th scope="col">@lang('order.total_price')</th>
                                        <th scope="col">@lang('order.status')</th>
                                        <th scope="col">@lang('order.status_paid')</th>
                                        <th scope="col">@lang('order.payment_method')</th>
                                        <!-- Conditionally show these columns if there are cancelled orders -->
                                        @if ($hasCancelledOrders)
                                            <th scope="col">@lang('order.reason_for_cancellation')</th>
                                            <th scope="col">@lang('order.type2')</th>
                                            <th scope="col">@lang('order.user_id')</th>
                                        @endif
                                        <th scope="col">@lang('order.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr data-status="{{ strtolower($order->last_status) }}"
                                            data-branch="{{ $order->branch_id }}"
                                            data-delivery="{{ $order->delivery_id }}" data-date="{{ $order->date }}">
                                            <td>{{ $order->id }}</td>
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
                                            <!-- Cancellation Reason, Type, and User ID -->
                                            @if ($hasCancelledOrders)
                                                @if ($order->last_status === 'cancelled' && $order->cancellation_reason)
                                                    <td>{{ $order->cancellation_reason->reason }}</td>
                                                    <td>{{ $order->cancellation_reason->type }}</td>
                                                    <td>{{ $order->cancellation_reason->user->name ?? 'N/A' }}</td>
                                                @else
                                                    <td>-----</td>
                                                    <td>-----</td>
                                                    <td>-----</td>
                                                @endif
                                            @endif
                                            <!-- Actions -->
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('detail report_delivery_orders', 'admin'))
                                                    <a href="{{ route('reports.delivery.orders.show', $order->id) }}"
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
            // Initialize DataTable with proper configuration
            const table = $('#file-export').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                searching: false, // Disable DataTable's built-in search
                paging: true,
                ordering: true,
                responsive: true,
                info: false,
                lengthChange: false,
                // Disable DataTable's filtering to prevent interference
                initComplete: function() {
                    this.api().columns().every(function() {
                        var column = this;
                        $(column.header()).removeClass('sorting sorting_asc sorting_desc');
                    });
                }
            });

            // Enhanced function to set data attributes
            function setRowDataAttributes() {
                $('#file-export tbody tr').each(function() {
                    const $row = $(this);

                    // Set payment status from the badge text or cell content
                    const paymentStatusBadge = $row.find('td:eq(8) span.badge');
                    const paymentStatus = paymentStatusBadge.length ?
                        paymentStatusBadge.text().trim().toLowerCase() :
                        $row.find('td:eq(8)').text().trim().toLowerCase();

                    // Set payment method from the badge text or cell content
                    const paymentMethodBadge = $row.find('td:eq(9) span.badge');
                    const paymentMethod = paymentMethodBadge.length ?
                        paymentMethodBadge.text().trim().toLowerCase() :
                        $row.find('td:eq(9)').text().trim().toLowerCase();

                    $row.attr('data-payment-status', paymentStatus === '-----' ? 'none' : paymentStatus);
                    $row.attr('data-payment-method', paymentMethod === '-----' ? 'none' : paymentMethod);

                    // Ensure other data attributes are properly set
                    $row.attr('data-status', $row.data('status')?.toLowerCase() || '');
                    $row.attr('data-branch', $row.data('branch')?.toString() || '');
                    $row.attr('data-delivery', $row.data('delivery')?.toString() || '');
                    $row.attr('data-date', $row.data('date') || '');
                });
            }

            // Call this function to initialize data attributes
            setRowDataAttributes();

            // Main filtering function
            function filterTable() {
                const selectedBranch = $('#branch-filter').val();
                const selectedDelivery = $('#delivery-filter').val();
                const selectedStatus = $('#filter-tabs .nav-link.active').data('status');
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();
                const paymentStatus = $('#payment-status-filter').val().toLowerCase();
                const paymentMethod = $('#payment-method-filter').val().toLowerCase();

                $('#file-export tbody tr').each(function() {
                    const $row = $(this);
                    const rowStatus = $row.data('status')?.toLowerCase() || '';
                    const rowBranch = $row.data('branch')?.toString() || '';
                    const rowDelivery = $row.data('delivery')?.toString() || '';
                    const rowDate = $row.data('date') || '';

                    // Get payment status/method from data attributes
                    const rowPaymentStatus = $row.data('payment-status')?.toLowerCase() || 'none';
                    const rowPaymentMethod = $row.data('payment-method')?.toLowerCase() || 'none';

                    // All filter conditions
                    const statusMatch = selectedStatus === 'all' || rowStatus === selectedStatus
                        .toLowerCase();
                    const branchMatch = selectedBranch === 'all' || rowBranch === selectedBranch;
                    const deliveryMatch = selectedDelivery === 'all' || rowDelivery === selectedDelivery;
                    const dateMatch = (!dateFrom || rowDate >= dateFrom) && (!dateTo || rowDate <= dateTo);
                    const paymentStatusMatch = paymentStatus === 'all' ||
                        rowPaymentStatus === paymentStatus.toLowerCase();
                    const paymentMethodMatch = paymentMethod === 'all' ||
                        rowPaymentMethod === paymentMethod.toLowerCase();

                    // Combine all conditions
                    const shouldShow = statusMatch && branchMatch && deliveryMatch && dateMatch &&
                        paymentStatusMatch && paymentMethodMatch;

                    // Toggle visibility
                    $row.toggle(shouldShow);
                });

                // Redraw DataTable to account for filtered rows
                table.draw(false);
            }

            // Event listeners
            $('#filter-tabs .nav-link').on('click', function() {
                $('#filter-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
                filterTable();
            });

            $('#branch-filter, #delivery-filter, #date-from, #date-to, #payment-status-filter, #payment-method-filter')
                .on('change', filterTable);

            // Initial filter
            filterTable();
        });
    </script>
@endsection
