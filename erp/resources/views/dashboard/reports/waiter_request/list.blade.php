@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .badge-container {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .request-item {
            margin-bottom: 8px;
            padding: 8px;
            border-left: 3px solid #5e72e4;
            background-color: #f8f9fa;
        }

        .request-details {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('sidebar.report_waiter_requests')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('sidebar.report_waiter_requests')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <form action="{{ route('reports.waiter_requests.search') }}" method="post">
                @csrf
                @method('POST')

                <!-- Filters Row -->
                <div class="row mt-5 mb-3">
                    <div class="col-md-3">
                        <label for="branch-filter">@lang('complaints.Branch')</label>
                        <select id="branch-filter" name="branch_id" class="form-select"
                            {{ auth('admin')->user()->hasRole('Branch Manager') ? 'disabled' : '' }}>
                            <option value="all">@lang('complaints.AllBranches')</option>
                            @foreach (branches() as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ auth('admin')->user()->hasRole('Branch Manager') && getBranchManagerID() === $branch->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date-from">@lang('complaints.DateFrom')</label>
                        <input type="date" id="date-from" name="date_from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="date-to">@lang('complaints.DateTo')</label>
                        <input type="date" id="date-to" name="date_to" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <div class="stats-box">
                            <span class="badge bg-warning">@lang('reports.pending'): {{ $requests['total_pending'] ?? 0 }}</span>
                            <span class="badge bg-success">@lang('reports.accepted'):
                                {{ $requests['total_accepted'] ?? 0 }}</span>
                            <span class="badge bg-danger">@lang('reports.rejected'): {{ $requests['total_rejected'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Second Filters Row -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="status-filter">@lang('reports.RequestStatus')</label>
                        <select id="status-filter" name="status" class="form-select">
                            <option value="all">@lang('reports.AllStatuses')</option>
                            <option value="0">@lang('reports.pending')</option>
                            <option value="1">@lang('reports.accepted')</option>
                            <option value="2">@lang('reports.rejected')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="type-filter">@lang('complaints.Type')</label>
                        <select id="type-filter" name="type" class="form-select">
                            <option value="all">@lang('complaints.AllTypes')</option>
                            <option value="0">@lang('reports.split')</option>
                            <option value="1">@lang('reports.merge')</option>
                            <option value="2">@lang('reports.print')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="waiter-filter">@lang('reports.waiter')</label>
                        <select id="waiter-filter" name="waiter_id" class="form-select">
                            <option value="all">@lang('reports.AllWaiters')</option>
                            @foreach (getWaiters() ?? [] as $waiter)
                                <option value="{{ $waiter->id }}">{{ $waiter->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-box">
                            <span class="badge bg-info">@lang('reports.merge'): {{ $requests['total_merge'] ?? 0 }}</span>
                            <span class="badge bg-primary">@lang('reports.split'): {{ $requests['total_split'] ?? 0 }}</span>
                            <span class="badge bg-secondary">@lang('reports.print'):
                                {{ $requests['total_reprint'] ?? 0 }}</span>
                        </div>
                    </div>
                    <!-- Add this to your form, preferably in the first filters row -->
                    <div class="col-md-3">
                        <label for="order-number-filter">@lang('report.OrderNumber')</label>
                        <input type="text" id="order-number-filter" name="order_number" class="form-control"
                            placeholder="@lang('report.OrderNumber')">
                    </div>
                    <div class="col-md-3">
                        <label for="invoice-number-filter">@lang('report.invoice_num')</label>
                        <input type="text" id="invoice-number-filter" name="invoice_number" class="form-control"
                            placeholder="@lang('report.invoice_num')">
                    </div>
                </div>
                <!-- Search Button -->
                <div class="row mb-3">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary">@lang('reports.search')</button>
                        <a href="{{ route('reports.waiter_requests.list') }}"
                            class="btn btn-secondary">@lang('reports.reset')</a>
                    </div>
                </div>
            </form>
            <!-- Data Table -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">@lang('sidebar.report_waiter_requests')</div>
                            <div class="export-buttons">
                               
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

                            <table id="waiter-requests-table" class="table table-bordered text-nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>@lang('order.ID')</th>
                                        <th>@lang('report.OrderNumber')</th>
                                        <th>@lang('order.date')</th>
                                        <th>@lang('order.type')</th>
                                        <th>@lang('order.branch')</th>
                                        <th>@lang('reports.waiter')</th>
                                        <th>@lang('order.total_price')</th>
                                        {{-- <th>@lang('order.request_types')</th>
                                        <th>@lang('order.request_statuses')</th>
                                        <th>@lang('order.request_dates')</th> --}}
                                        <th>@lang('order.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests['orders'] as $order)
                                        <tr>
                                            <td>{{ $order['id'] }}</td>
                                            <td>{{ $order['order_number'] }}</td>
                                            <td>{{ $order['order_date'] ? \Carbon\Carbon::parse($order['order_date'])->format('Y-m-d H:i') : '' }}
                                            </td>
                                            <td>{{ $order['order_type'] }}</td>
                                            <td>{{ $order['branch_name'] ?? '-----' }}</td>
                                            <td>{{ $order['waiter_name'] ?? '-----' }}</td>
                                            <td>{{ number_format($order['total_amount'], 2) }}</td>

                                            <!-- Request Types -->
                                            {{-- <td>
                                                @if (!empty($order['requests']))
                                                    <div class="badge-container">
                                                        @foreach ($order['requests'] as $request)
                                                            <span class="badge bg-info-transparent">
                                                                {{ $request['type_name'] }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    -----
                                                @endif
                                            </td>

                                            <!-- Request Statuses -->
                                            <td>
                                                @if (!empty($order['requests']))
                                                    <div class="badge-container">
                                                        @foreach ($order['requests'] as $request)
                                                            @php
                                                                $statusClass = [
                                                                    'Pending' => 'bg-warning-transparent',
                                                                    'Approved' => 'bg-success-transparent',
                                                                    'Rejected' => 'bg-danger-transparent',
                                                                ][$request['status_name']] ?? 'bg-light-transparent';
                                                            @endphp
                                                            <span class="badge {{ $statusClass }}">
                                                                {{ $request['status_name'] }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    -----
                                                @endif
                                            </td>

                                            <!-- Request Dates -->
                                            <td>
                                                @if (!empty($order['requests']))
                                                    @foreach ($order['requests'] as $request)
                                                        <div class="request-dates">
                                                            {{ \Carbon\Carbon::parse($request['created_at'])->format('Y-m-d H:i') }}
                                                        </div>
                                                    @endforeach
                                                @else
                                                    -----
                                                @endif
                                            </td> --}}

                                            <!-- Actions -->
                                            <td>
                                                <div class="d-flex flex-column gap-2">
                                                    @if (auth('admin')->user()->can('detail report_booking_cancellation'))
                                                        <a href="{{ route('reports.booking_cancellation.show', $order['id']) }}"
                                                            class="btn btn-sm btn-info-light">
                                                            @lang('order.show') <i class="ri-eye-line"></i>
                                                        </a>
                                                    @endif
                                                    @if (!empty($order['requests']))
                                                        <button class="btn btn-sm btn-primary-light view-requests"
                                                            data-order-id="{{ $order['id'] }}"
                                                            data-requests='@json($order['requests'])'>
                                                            @lang('reports.view_requests') <i class="ri-list-check"></i>
                                                        </button>
                                                    @endif

                                                </div>
                                            </td>
                                        </tr>
                                        {{-- @empty
                                        <tr>
                                            <td colspan="11" class="text-center">@lang('order.no_requests_found')</td>
                                        </tr> --}}
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Details Modal -->
    <div class="modal fade" id="requestsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('order.request_details')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="requestsModalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('order.close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>

    <!-- DATA-TABLES CDN -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with print button
            $('#waiter-requests-table').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> @lang('order.print')',
                    className: 'btn btn-primary',
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function(win) {
                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table').addClass('compact').css('font-size',
                            'inherit');

                        // Add a title to the printout
                        $(win.document.body).prepend(
                            '<h2>@lang('sidebar.report_waiter_requests')</h2>' +
                            '<div class="mb-3">' +
                            '<p>@lang('common.printed_at'): ' + new Date().toLocaleString() +
                            '</p>' +
                            '</div>'
                        );
                    }
                }],
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.12.1/i18n/' + $('html').attr('lang') + '.json'
                }
            });

            // Print button handler
            $(document).on('click', '.print-btn', function() {
                $('#waiter-requests-table').DataTable().button('.buttons-print').trigger();
            });

            // Translation mapping objects
            const statusTranslations = {
                0: "@lang('reports.pending')",
                1: "@lang('reports.accepted')",
                2: "@lang('reports.rejected')"
            };

            const typeTranslations = {
                0: "@lang('reports.split')",
                1: "@lang('reports.merge')",
                2: "@lang('reports.print')"
            };

            // View requests modal handler
            $(document).on('click', '.view-requests', function() {
                try {
                    const requests = $(this).data('requests');
                    const orderId = $(this).data('order-id');

                    let html = `
                        <div class="mb-4">
                            <h5>@lang('order.order_id'): ${orderId}</h5>
                            <p class="text-muted">@lang('order.total_amount'): ${$(this).closest('tr').find('td:eq(6)').text()}</p>
                        </div>
                        <hr>
                        <div class="requests-list">
                    `;

                    if (requests.length === 0) {
                        html += `<div class="alert alert-info">@lang('order.no_requests_found')</div>`;
                    } else {
                        requests.forEach(request => {
                            const statusClass = {
                                0: 'warning', // pending
                                1: 'success', // accepted
                                2: 'danger' // rejected
                            } [request.status] || 'secondary';

                            const typeClass = {
                                0: 'primary', // split
                                1: 'info', // merge
                                2: 'secondary' // print
                            } [request.type] || 'light';

                            // Get translated labels
                            const statusLabel = statusTranslations[request.status] || 'Unknown';
                            const typeLabel = typeTranslations[request.type] || 'Unknown';

                            html += `
                                <div class="request-item">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-${typeClass}">${typeLabel}</span>
                                        <span class="badge bg-${statusClass}">${statusLabel}</span>
                                    </div>
                                    <div class="request-details">
                                        <p><strong>@lang('order.request_id'):</strong> ${request.request_id}</p>
                                        <p><strong>@lang('order.created_at'):</strong> ${new Date(request.created_at).toLocaleString()}</p>
                                        <p><strong>@lang('order.related_orders'):</strong> ${request.order_ids.join(', ')}</p>
                                        ${request.reason ? `<p><strong>@lang('order.reason'):</strong> ${request.reason}</p>` : ''}
                                    </div>
                                </div>
                            `;
                        });
                    }

                    html += `</div>`;
                    $('#requestsModalBody').html(html);
                    $('#requestsModal').modal('show');
                } catch (error) {
                    console.error('Error loading requests:', error);
                    $('#requestsModalBody').html(`
                        <div class="alert alert-danger">
                            @lang('order.error_loading_requests'): ${error.message}
                        </div>
                    `);
                    $('#requestsModal').modal('show');
                }
            });
        });
    </script>
@endsection
