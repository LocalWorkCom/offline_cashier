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
                    <li class="breadcrumb-item active" aria-current="page">@lang('order.cancelled_orders')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Branch Selection and Date Range Filters -->
            <div class="row mt-5 mb-4">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label for="date-from">@lang('order.date_from')</label>
                    <input type="date" id="date-from" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="date-to">@lang('order.date_to')</label>
                    <input type="date" id="date-to" class="form-control">
                </div>
                <div class="row mb-4 mt-3">
                    <div class="col-md-4">
                        <label for="source-filter">@lang('order.source')</label>
                        <select id="source-filter" class="form-select">
                            <option value="all">@lang('order.all_sources')</option>
                            <option value="waiter">@lang('order.waiter')</option>
                            <option value="cashier">@lang('order.cashier')</option>
                            <option value="online">@lang('order.online')</option>
                        </select>
                    </div>
                    <div id="source-count-display" class="col-md-2 alert alert-primary" style="display: none;">
                        <strong>@lang('order.orders_count'):</strong>
                        <span id="source-count">0</span>
                    </div>
                    <!-- Add this reset button -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button id="reset-filters" class="btn btn-danger">
                            <i class="ri-refresh-line"></i> @lang('order.reset_filters')
                        </button>
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
                            <table class="table table-bordered text-nowrap" style="width:100%">
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
                                        <th scope="col">@lang('order.reasons')</th>
                                        <th scope="col">@lang('order.type2')</th>
                                        <th scope="col">@lang('order.user_id')</th>
                                        <th scope="col">@lang('order.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr
                                            data-branch="{{ $order->branch_id }}" data-date="{{ $order->date }}"
                                            data-source="{{ strtolower($order->source) }}">
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->invoice_number }}</td>
                                            <td>{{ $order->date }}</td>
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
                                            <td>{{ $order->responsible_person }}</td>
                                            {{-- <td>{{ $order->client->name ?? __('order.client_deleted') }}</td> --}}
                                            <td>{{ $order->total_price_after_tax }}
                                                {{ $order->branch->country->currency_symbol }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ [
                                                        'pending' => 'bg-warning-transparent',
                                                        'in_progress' => 'bg-primary-transparent',
                                                        'packing' => 'bg-danger-transparent',
                                                        'completed' => 'bg-success-transparent',
                                                        'on_way' => 'bg-info-transparent',
                                                        'delivered' => 'bg-secondary-transparent',
                                                        'cancelled' => 'bg-danger-transparent',
                                                    ][strtolower($order->last_status)] ?? 'bg-info-transparent' }}">
                                                    @if ($order->last_status == 'packing')
                                                        @lang('order.readyForPickup')
                                                    @else
                                                        @lang('order.' . strtolower($order->last_status))
                                                    @endif
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
                                                @if ($order->status === 'cancelled' && $order->cancellationReasons->isNotEmpty())
                                                    <td>
                                                        @foreach ($order->cancellationReasons as $reason)
                                                            {{ $reason->localized_reason }}
                                                            @if (!$loop->last)
                                                                ,
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @foreach ($order->cancellationReasons as $reason)
                                                            {{ $reason->type ?? '-----' }}
                                                            @if (!$loop->last)
                                                                ,
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @foreach ($order->cancellationReasons as $reason)
                                                            {{ $reason->user->name ?? 'N/A' }}
                                                            @if (!$loop->last)
                                                                ,
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                @else
                                                    <td>-----</td>
                                                    <td>-----</td>
                                                    <td>-----</td>
                                                @endif
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('detail report_cancelled_orders', 'admin'))
                                                    <a href="{{ route('reports.cancelled_orders.show', $order->id) }}"
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
            // Custom DataTables filter
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const row = $('#file-export').DataTable().row(dataIndex).node();
                const rowStatus = $(row).data('status');
                const rowBranch = $(row).data('branch');
                const rowDate = $(row).data('date');
                const rowSource = $(row).data('source');

                const selectedStatus = $('#filter-tabs .nav-link.active').data('status');
                const selectedBranch = $('#branch-filter').val();
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();
                const selectedSource = $('#source-filter').val();

                const statusMatch = selectedStatus === 'all' || rowStatus === selectedStatus;
                const branchMatch = selectedBranch === 'all' || rowBranch == selectedBranch;
                const dateMatch = (!dateFrom || new Date(rowDate) >= new Date(dateFrom)) &&
                    (!dateTo || new Date(rowDate) <= new Date(dateTo));
                const sourceMatch = selectedSource === 'all' || rowSource === selectedSource;

                return statusMatch && branchMatch && dateMatch && sourceMatch;
            });

            const table = $('#file-export').DataTable({
                paging: true,
                ordering: true,
                responsive: true,
                dom: 'Bfrtip',
                // buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            });

            // Update source count display
            function updateSourceCount() {
                const selectedSource = $('#source-filter').val();
                if (selectedSource !== 'all') {
                    const visibleRows = table.rows({
                        search: 'applied'
                    }).nodes();
                    const count = $(visibleRows).filter(function() {
                        return $(this).data('source') === selectedSource;
                    }).length;
                    $('#source-count').text(count);
                    $('#source-count-display').show();
                } else {
                    $('#source-count-display').hide();
                }
            }

            // Filter tab click event
            $('#filter-tabs .nav-link').on('click', function() {
                $('#filter-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
                table.draw();
                updateSourceCount();
            });

            // Other filter change events
            $('#branch-filter, #date-from, #date-to, #source-filter').on('change', function() {
                table.draw();
                updateSourceCount();
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
                $('#source-count-display').hide();

                // Redraw the table to show all records
                table.draw();
            });

            // Initial count update
            updateSourceCount();
        });
    </script>
@endsection
