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
        <h4 class="fw-medium mb-0">@lang('order.cashier_performance_reports')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('order.cashier_performance_reports')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="mb-5">
            </div>
            <!-- Branch Selection and Date Range Filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="branch-filter">@lang('order.branch')</label>
                    <select id="branch-filter" class="form-select">
                        <option value="all">@lang('order.all_branches')</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cashier-filter">@lang('order.cashier')</label>
                    <select id="cashier-filter" class="form-select">
                        <option value="all">@lang('order.all_cashiers')</option>
                        @foreach ($allCashiers as $cashierOption)
                            <option value="{{ $cashierOption->id }}"
                                {{ request('cashier_id') == $cashierOption->id ? 'selected' : '' }}>
                                {{ $cashierOption->first_name . " " . $cashierOption->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="phone-filter">@lang('order.cashier_phone')</label>
                    <input type="text" id="phone-filter" class="form-control" placeholder="@lang('order.cashier_phone')">
                </div>
                <div class="col-md-3">
                    <label for="date-from">@lang('order.date_from')</label>
                    <input type="date" id="date-from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date-to">@lang('order.date_to')</label>
                    <input type="date" id="date-to" class="form-control" value="{{ request('to') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">@lang('order.cashiers')</div>
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
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                <tr>
                                    <th scope="col">@lang('order.ID')</th>
                                    <th scope="col">@lang('order.cashier_name')</th>
                                    <th scope="col">@lang('order.cashier_phone')</th>
                                    <th scope="col">@lang('order.branch')</th>
                                    <th scope="col">@lang('order.orders_count')</th>
                                    <th scope="col">@lang('order.orders_total')</th>
                                    <th scope="col">@lang('order.actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($cashiers as $cashier)
                                    <tr
                                        data-branch="{{ $cashier->branch_id }}">
                                        <td>{{ $cashier->id }}</td>
                                        <td>{{ $cashier->first_name . " " .$cashier->last_name }}</td>
                                        <td>{{ $cashier->phone_number }}</td>
                                        <td>
                                            {{ $cashier->branch ? (app()->getLocale() === 'ar' ? $cashier->branch->name_ar : $cashier->branch->name_en) : __('order.none') }}
                                        </td>
                                        <td>{{$cashier->order_count}}</td>
                                        <td>{{( $cashier->total_order_price ? $cashier?->total_order_price : 0 )  . " " . $cashier->branch->country->currency_symbol}}</td>
                                        <!-- Actions -->
                                        <td>
                                            @if (auth('admin')->user()->hasPermissionTo('detail report_cashier_performance', 'admin'))
                                                <a href="{{ route('reports.cashier_performance.show', $cashier->id) }}"
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
            const table = $('#file-export').DataTable({
                initComplete: function() {
                    // Apply the search
                    this.api().columns().every(function() {
                        var column = this;
                    });
                }
            });

            function filterTable() {
                const selectedBranch = $('#branch-filter').val();
                const phoneNum = $('#phone-filter').val().toLowerCase();

                $('#file-export tbody tr').each(function() {
                    const rowBranch = $(this).data('branch');
                    const rowPhone = $(this).find('td:eq(2)').text().toLowerCase(); // Phone is in 3rd column (index 2)

                    // Branch filter
                    const branchMatch = selectedBranch === 'all' || rowBranch == selectedBranch;
                    // Phone filter
                    const phoneMatch = !phoneNum || rowPhone.includes(phoneNum);

                    if (branchMatch && phoneMatch) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            // Event listeners
            $('#filter-tabs .nav-link').on('click', function() {
                $('#filter-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
                filterTable();
            });

            $('#branch-filter, #phone-filter').on('change keyup', function() {
                filterTable();
            });

            // Initialize
            filterTable();
        });
    </script>
    <script>
        function triggerFilterIfReady() {
            const from = $('#date-from').val();
            const to = $('#date-to').val();
            const cashierId = $('#cashier-filter').val();

            const queryParams = new URLSearchParams();

            if (from) queryParams.append('from', from);
            if (to) queryParams.append('to', to);
            if (cashierId && cashierId !== 'all') queryParams.append('cashier_id', cashierId);

            if (from && to) {
                window.location.href = `?${queryParams.toString()}`;
            }
        }
        function triggerCashierFilterIfReady() {
            const cashierId = $('#cashier-filter').val();

            const queryParams = new URLSearchParams();

            if (cashierId && cashierId !== 'all') queryParams.append('cashier_id', cashierId);

                window.location.href = `?${queryParams.toString()}`;
        }

        $('#date-from, #date-to').on('change', triggerFilterIfReady);
        $('#cashier-filter').on('change', triggerCashierFilterIfReady);
    </script>

@endsection
