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
        <h4 class="fw-medium mb-0">@lang('sidebar.cashier_balances_reports')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('dashboard.home')}}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('sidebar.cashier_balances_reports')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div>
            <!-- Filter Tabs -->
{{--                <ul class="nav nav-tabs mb-4" id="branch-filter-tabs" role="tablist">--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ request('status') === 'all' || is_null(request('status')) ? 'active' : '' }} bg-light text-dark" href="{{ url()->current() }}?status=all">--}}
{{--                            @lang('cashier_balances_reports.All')--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ request('status') === 'active' ? 'active' : '' }} bg-success text-white" href="{{ url()->current() }}?status=active">--}}
{{--                            @lang('cashier_balances_reports.Active')--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ request('status') === 'inactive' ? 'active' : '' }} bg-danger text-white" href="{{ url()->current() }}?status=inactive">--}}
{{--                            @lang('cashier_balances_reports.Inactive')--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ request('status') === 'delivery' ? 'active' : '' }} bg-success text-white" href="{{ url()->current() }}?status=delivery">--}}
{{--                            @lang('cashier_balances_reports.Delivery')--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ request('status') === 'no-delivery' ? 'active' : '' }} bg-danger text-white" href="{{ url()->current() }}?status=no-delivery">--}}
{{--                            @lang('cashier_balances_reports.NoDelivery')--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ request('status') === 'noOrders' ? 'active' : '' }} bg-primary text-white" href="{{ url()->current() }}?status=noOrders">--}}
{{--                            @lang('cashier_balances_reports.NoOrders')--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ request('status') === 'mostOrdered' ? 'active' : '' }} bg-info text-white" href="{{ url()->current() }}?status=mostOrdered">--}}
{{--                            @lang('cashier_balances_reports.MostOrdered')--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link {{ request('status') === 'mostProfit' ? 'active' : '' }} bg-yellow text-white" href="{{ url()->current() }}?status=mostProfit">--}}
{{--                            @lang('cashier_balances_reports.MostProfit')--}}
{{--                        </a>--}}
{{--                    </li>--}}

{{--                    <li class="nav-item mx-5">--}}
{{--                        <strong>--}}
{{--                            <span class="nav-link" style="border: none;cursor: default;font-size: medium">@lang('cashier_balances_reports.totalRevenueAllBranches'):--}}
{{--                                {{ $totalRevenueAllBranches && count($totalRevenueAllBranches) > 0--}}
{{--                                        ? collect($totalRevenueAllBranches)->map(fn($revenue, $currency) => number_format($revenue, 2) . ' ' . $currency)->implode(', ')--}}
{{--                                        : '0'--}}
{{--                                }}--}}
{{--                            </span>--}}
{{--                        </strong>--}}
{{--                    </li>--}}

{{--                </ul>--}}
                <div class="d-flex mt-5">
                    <!-- Date Filter Form -->
                    <form class="d-flex p-1">
                        <div class="p-1">
                            <label for="from" class="form-label">@lang('cashier_balances_reports.From')</label>
                            <input type="date" name="from" class="form-control" id="from" value="{{ request('from') }}">
                        </div>
                        <div class="p-1">
                            <label for="to" class="form-label">@lang('cashier_balances_reports.To')</label>
                            <input type="date" name="to" class="form-control" id="to" value="{{ request('to') }}">
                        </div>
                        <div class="p-1">
                            <label for="shift_id" class="form-label">@lang('cashier_balances_reports.Shift')</label>
                            <select name="shift_id" id="shift_id" class="form-select">
                                <option value="">@lang('cashier_balances_reports.AllShifts')</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ $selectedShiftId == $shift->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'en' ? $shift->name_en : $shift->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="p-1">
                            <label for="cashier_id" class="form-label">@lang('cashier_balances_reports.Cashier')</label>
                            <select name="cashier_id" id="cashier_id" class="form-select">
                                <option value="">@lang('cashier_balances_reports.AllCashiers')</option>
                                @foreach($cashiers as $cashier)
                                    <option value="{{ $cashier->id }}" {{ $selectedCashierId == $cashier->id ? 'selected' : '' }}>
                                        {{ $cashier->first_name }} {{ $cashier->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="p-1">
                            <label for="branch_id" class="form-label">@lang('cashier_balances_reports.Branch')</label>
                            <select name="branch_id" id="branch_id" class="form-select">
                                <option value="">@lang('cashier_balances_reports.AllBranches')</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="p-1">
                            <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                            <button type="submit" class="btn btn-outline-success btn-wave mt-4">@lang('cashier_balances_reports.Search')</button>
                        </div>
                        <div class="p-1">
                            <a href="{{ url()->current() }}" class="btn btn-outline-danger btn-wave mt-4">
                                @lang('cashier_balances_reports.Reset')
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Start:: row-4 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">@lang('sidebar.cashier_balances_reports')</div>
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
                                    <th>@lang('cashier_balances_reports.ID')</th>
                                    <th>@lang('cashier_balances_reports.Machine')</th>
                                    <th>@lang('cashier_balances_reports.Cashier')</th>
                                    <th>@lang('cashier_balances_reports.OpenCash')</th>
                                    <th>@lang('cashier_balances_reports.OpenVisa')</th>
                                    <th>@lang('cashier_balances_reports.CloseCash')</th>
                                    <th>@lang('cashier_balances_reports.CloseVisa')</th>
                                    <th>@lang('cashier_balances_reports.RealCash')</th>
                                    <th>@lang('cashier_balances_reports.RealVisa')</th>
                                    <th>@lang('cashier_balances_reports.DeficitCash')</th>
                                    <th>@lang('cashier_balances_reports.DeficitVisa')</th>
                                    <th>@lang('cashier_balances_reports.Time')</th>
                                    <th>@lang('cashier_balances_reports.Date')</th>
                                    <th>@lang('cashier_balances_reports.Type')</th>
                                    <th>@lang('cashier_balances_reports.Actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($balances as $balance)
                                    <tr>
                                        <td>{{ $balance->id }}</td>
                                        <td>{{ app()->getLocale() == 'en' ? $balance->cashierMachines->name_en : $balance->cashierMachines->name_ar}}</td>
{{--                                        <td>{{ app()->getLocale() == 'en' ? $balance->address_en : $balance->address_ar }}</td>--}}
{{--                                        <td>{{ app()->getLocale() == 'en' ? $balance->country->name_en : $balance->country->name_ar }}</td>--}}
                                        <td>{{ $balance->employees && $balance->employees->first_name ? ($balance->employees->first_name." ".$balance->employees->last_name) : null }}</td>
{{--                                        <td>{{ $balance->phone }}</td>--}}
{{--                                        <td>{{ $balance->email }}</td>--}}
                                        <td>{{ $balance->open_cash . " " . $balance?->cashierMachines?->branches?->country?->currency_symbol }}</td>
                                        <td>{{ $balance->open_visa . " " . $balance?->cashierMachines?->branches?->country?->currency_symbol}}</td>
                                        <td>{{ $balance->close_cash . " " . $balance?->cashierMachines?->branches?->country?->currency_symbol}}</td>
                                        <td>{{ $balance->close_visa . " " . $balance?->cashierMachines?->branches?->country?->currency_symbol}}</td>
                                        <td>{{ $balance->real_cash . " " . $balance?->cashierMachines?->branches?->country?->currency_symbol}}</td>
                                        <td>{{ $balance->real_visa . " " . $balance?->cashierMachines?->branches?->country?->currency_symbol}}</td>
                                        <td>{{ $balance->deficit_cash . " " . $balance?->cashierMachines?->branches?->country?->currency_symbol}}</td>
                                        <td>{{ $balance->deficit_visa . " " . $balance?->cashierMachines?->branches?->country?->currency_symbol}}</td>
                                        <td>{{ $balance->time }}</td>
                                        <td>{{ $balance->date }}</td>
                                        <td>{{ $balance->type == 1 ? __('cashier_balances_reports.Open') : __('cashier_balances_reports.Close')}}</td>


                                        <td>
                                            @if (auth('admin')->user()->hasPermissionTo('detail report_cashier_balances', 'admin'))
                                                <a href="{{ route('reports.cashier_balances.show', ['id' => $balance->id]) }}"
                                               class="btn btn-info-light btn-wave">
                                                @lang('cashier_balances_reports.Details') <i class="ri-eye-line"></i>
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
            <!-- End:: row-4 -->
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- DATA-TABLES CDN -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')
@endsection
