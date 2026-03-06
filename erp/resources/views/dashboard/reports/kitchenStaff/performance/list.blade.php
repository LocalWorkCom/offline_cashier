@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .performance-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 500;
        }

        .faster-badge {
            background-color: #d1fae5;
            color: #065f46;
        }

        .slower-badge {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .on-time-badge {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .shift-info {
            background-color: #f3f4f6;
            padding: 8px;
            border-radius: 4px;
            margin-top: 5px;
        }
    </style>
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('sidebar.report_kitchen_performance')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('sidebar.report_kitchen_performance')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <form action="{{ route('reports.kitchen_performance.list') }}" method="get">
                <!-- Filters Row -->
                <div class="row mt-5 mb-3">
                    <div class="col-md-3">
                        <label for="branch-filter">@lang('reports.Branch')</label>
                        <select id="branch-filter" name="branch_id" class="form-select"
                            {{ auth('admin')->user()->hasRole('Branch Manager') ? 'disabled' : '' }}>
                            <option value="all">@lang('reports.AllBranches')</option>
                            @foreach (branches() as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ (auth('admin')->user()->hasRole('Branch Manager') && getBranchManagerID() === $branch->id) ||
                                    request('branch_id') == $branch->id
                                        ? 'selected'
                                        : '' }}>
                                    {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date-from">@lang('reports.DateFrom')</label>
                        <input type="date" id="date-from" name="date_from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="date-to">@lang('reports.DateTo')</label>
                        <input type="date" id="date-to" name="date_to" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <div class="stats-box">
                            <span class="badge bg-primary">@lang('reports.total_orders'):
                                {{ $performanceData['totals']['totalOrders'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Second Filters Row -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="status-filter">@lang('reports.PerformanceStatus')</label>
                        <select id="status-filter" name="status" class="form-select">
                            <option value="all">@lang('reports.AllStatuses')</option>
                            <option value="Faster">@lang('reports.Faster')</option>
                            <option value="Slower">@lang('reports.Slower')</option>
                            <option value="On Time">@lang('reports.OnTime')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="employee-filter">@lang('reports.employee')</label>
                        <select id="employee-filter" name="employee_id" class="form-select">
                            <option value="all">@lang('reports.AllEmployees')</option>
                            @foreach (chefs() as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="shift-filter">@lang('reports.Shift')</label>
                        <select id="shift-filter" name="shift_id" class="form-select">
                            <option value="all">@lang('reports.AllShifts')</option>
                            @foreach (shifts() as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-box">
                            <span class="badge bg-success">@lang('reports.totalOnTime'):
                                {{ $performanceData['totals']['totalOnTime'] ?? 0 }} </span>
                            <span class="badge bg-info">@lang('reports.totalFaster'):
                                {{ $performanceData['totals']['totalFaster'] ?? 0 }} </span>
                            <span class="badge bg-danger">@lang('reports.totalDelayed'):
                                {{ $performanceData['totals']['totalDelayed'] ?? 0 }} </span>

                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="dish-filter">@lang('reports.dish')</label>
                        <select id="dish-filter" name="dish_id" class="form-select">
                            <option value="all">@lang('reports.AllDishes')</option>
                            @foreach (getDishes() as $dish)
                                <option value="{{ $dish->id }}">{{ $dish->getNameSiteAttribute() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="category-filter">@lang('reports.Categories')</label>
                        <select id="category-filter" name="category_id" class="form-select">
                            <option value="all">@lang('reports.Allcategories')</option>
                            @foreach (getCategories() as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="order-number">@lang('reports.order_number')</label>
                        <input type="text" id="order-number" name="order_number" class="form-control"
                               placeholder="@lang('reports.search_by_order_number')" value="{{ request('order_number') }}">
                    </div>

                </div>
                <!-- Search Button -->
                <div class="row mb-3">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary">@lang('reports.search')</button>
                        <a href="{{ route('reports.kitchen_performance.list') }}"
                            class="btn btn-secondary">@lang('reports.reset')</a>
                    </div>
                </div>
            </form>

            <!-- Data Table -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">@lang('sidebar.report_kitchen_performance')</div>
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

                            <table id="kitchen-performance-table" class="table table-bordered text-nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>@lang('reports.order_number')</th>
                                        <th>@lang('reports.branch')</th>
                                        <th>@lang('reports.dish_name')</th>
                                        <th>@lang('reports.expected_time') (@lang('reports.minutes'))</th>
                                        <th>@lang('reports.actual_time') (@lang('reports.minutes'))</th>
                                        <th>@lang('reports.performance')</th>
                                        <th>@lang('reports.employee')</th>
                                        <th>@lang('reports.shift')</th>
                                        <th class="noExport">@lang('reports.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($performanceData['data'] as $performance)
                                        <tr>
                                            <td>{{ $performance['order_number'] }}</td>
                                            <td>{{ $performance['branch_name'] }}</td>
                                            <td>{{ $performance['dish_name'] }}</td>
                                            <td>{{ $performance['expected_time'] }}</td>
                                            <td>{{ $performance['actual_time'] }}</td>
                                            <td>
                                                @php
                                                    $badgeClass =
                                                        [
                                                            'Faster' => 'faster-badge',
                                                            'Slower' => 'danger-badge',
                                                            'OnTime' => 'on-time-badge',
                                                        ][$performance['status']] ?? '';
                                                @endphp
                                                <span class="performance-badge {{ $badgeClass }}">
                                                    @lang('reports.' . $performance['status'])
                                                    {{-- {{ $performance['status'] }} --}}
                                                </span>
                                            </td>
                                            <td>{{ $performance['employee_name'] }}</td>
                                            <td>
                                                {{ $performance['shift_time']['shift_name'] ?? 'N/A' }}
                                                <div class="shift-info">
                                                    {{ $performance['shift_time']['start_time'] ?? '' }} -
                                                    {{ $performance['shift_time']['end_time'] ?? '' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-info view-details"
                                                        data-performance='@json($performance)'>
                                                        @lang('reports.details') <i class="ri-eye-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">@lang('reports.no_performance_data_found')</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Details Modal -->
    <div class="modal fade" id="performanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('reports.performance_details')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="performanceModalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="printPerformanceDetails">
                        <i class="bi bi-printer-fill"></i> @lang('reports.print')
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('reports.close')</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden div for printing -->
    <div id="printableArea" style="display:none;"></div>
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
            // Initialize DataTable
            $('#kitchen-performance-table').DataTable({
                responsive: true,
                order: [
                    [3, 'asc']
                ] // Default sort by expected time
            });


            // View performance details modal handler
            $(document).on('click', '.view-details', function() {
                try {
                    const performance = $(this).data('performance');

                    let html = `
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>@lang('reports.order_number'): ${performance.order_number}</h5>
                                <p class="text-muted">@lang('reports.branch'): ${performance.branch_name}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="${getBadgeClass(performance.status)} performance-badge">
                                    ${performance.status}
                                </span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6>@lang('reports.time_metrics')</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>@lang('reports.expected_time'):</strong> ${performance.expected_time} @lang('reports.minutes')</p>
                                        <p><strong>@lang('reports.actual_time'):</strong> ${performance.actual_time} @lang('reports.minutes')</p>
                                        <p><strong>@lang('reports.difference'):</strong> ${Math.abs(performance.expected_time - performance.actual_time)} @lang('reports.minutes') ${performance.expected_time > performance.actual_time ? '@lang('order.faster')' : '@lang('order.slower')'}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6>@lang('reports.employee_info')</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>@lang('reports.employee'):</strong> ${performance.employee_name}</p>
                                        <p><strong>@lang('reports.shift'):</strong> ${performance.shift_time.shift_name}</p>
                                        <p><strong>@lang('reports.shift_time'):</strong> ${performance.shift_time.start_time} - ${performance.shift_time.end_time}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-light">
                                <h6>@lang('reports.dish_info')</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>@lang('reports.dish_name'):</strong> ${performance.dish_name}</p>
                                <!-- Add more dish details here if available -->
                            </div>
                        </div>
                    `;

                    $('#performanceModalBody').html(html);
                    $('#performanceModal').modal('show');
                } catch (error) {
                    console.error('Error loading performance details:', error);
                    $('#performanceModalBody').html(`
                        <div class="alert alert-danger">
                            @lang('reports.error_loading_details'): ${error.message}
                        </div>
                    `);
                    $('#performanceModal').modal('show');
                }
            });

            function getBadgeClass(status) {
                switch (status) {
                    case 'Faster':
                        return 'faster-badge';
                    case 'Slower':
                        return 'slower-badge';
                    case 'On Time':
                        return 'on-time-badge';
                    default:
                        return '';
                }
            }
        });

        // Print performance details handler
        $(document).on('click', '#printPerformanceDetails', function() {
            // Clone the modal content
            const printContent = $('#performanceModalBody').clone();

            // Create a printable version with better styling for print
            const printable = $('<div>').append(printContent);

            // Add a title
            printable.prepend('<h2 class="text-center mb-4">@lang("reports.performance_details")</h2>');

            // Add print-specific styles
            printable.find('.card').css({
                'border': '1px solid #ddd',
                'page-break-inside': 'avoid',
                'margin-bottom': '15px'
            });

            // Add date/time of printing
            printable.append('<div class="text-end mt-4">' +
                '<small>' + new Date().toLocaleString() + '</small>' +
                '</div>');

            // Put content in printable area
            $('#printableArea').html(printable.html());

            // Open print dialog
            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write(`
            <html>
                <head>
                    <title>@lang("reports.performance_details")</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h2 { color: #333; }
                        .card { border: 1px solid #ddd; margin-bottom: 15px; page-break-inside: avoid; }
                        .card-header { background-color: #f8f9fa; padding: 10px; font-weight: bold; }
                        .card-body { padding: 15px; }
                        .performance-badge { padding: 3px 8px; border-radius: 3px; font-weight: 500; }
                        .shift-info { background-color: #f3f4f6; padding: 5px; border-radius: 3px; margin-top: 3px; }
                        @media print {
                            .no-print { display: none !important; }
                            body { padding: 0; }
                        }
                    </style>
                </head>
                <body>
                    ${$('#printableArea').html()}
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(function() {
                                window.close();
                            }, 100);
                        };
                    <\/script>
                </body>
            </html>
        `);
            printWindow.document.close();
        });
    </script>
@endsection
