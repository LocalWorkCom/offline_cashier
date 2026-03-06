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
        <h4 class="fw-medium mb-0">@lang('sidebar.branches_reports')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('dashboard.home')}}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('sidebar.branches_reports')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div>
            <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-4" id="branch-filter-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'all' || is_null(request('status')) ? 'active' : '' }} bg-light text-dark" href="{{ url()->current() }}?status=all">
                            @lang('branch_report.All')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'active' ? 'active' : '' }} bg-success text-white" href="{{ url()->current() }}?status=active">
                            @lang('branch_report.Active')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'inactive' ? 'active' : '' }} bg-danger text-white" href="{{ url()->current() }}?status=inactive">
                            @lang('branch_report.Inactive')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'delivery' ? 'active' : '' }} bg-success text-white" href="{{ url()->current() }}?status=delivery">
                            @lang('branch_report.Delivery')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'no-delivery' ? 'active' : '' }} bg-danger text-white" href="{{ url()->current() }}?status=no-delivery">
                            @lang('branch_report.NoDelivery')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'noOrders' ? 'active' : '' }} bg-primary text-white" href="{{ url()->current() }}?status=noOrders">
                            @lang('branch_report.NoOrders')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'mostOrdered' ? 'active' : '' }} bg-info text-white" href="{{ url()->current() }}?status=mostOrdered">
                            @lang('branch_report.MostOrdered')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'mostProfit' ? 'active' : '' }} bg-yellow text-white" href="{{ url()->current() }}?status=mostProfit">
                            @lang('branch_report.MostProfit')
                        </a>
                    </li>

                    <li class="nav-item mx-5">
                        <strong>
                            <span class="nav-link" style="border: none;cursor: default;font-size: medium">@lang('branch_report.totalRevenueAllBranches'):
                                {{ $totalRevenueAllBranches && count($totalRevenueAllBranches) > 0
                                        ? collect($totalRevenueAllBranches)->map(fn($revenue, $currency) => number_format($revenue, 2) . ' ' . $currency)->implode(', ')
                                        : '0'
                                }}
                            </span>
                        </strong>
                    </li>

                </ul>
                <div class="d-flex">
                    <!-- Date Filter Form -->
                    <form class="d-flex p-1">
                        <div class="p-1">
                            <label for="from" class="form-label">@lang('branch_report.From')</label>
                            <input type="date" name="from" class="form-control" id="from" value="{{ request('from') }}">
                        </div>
                        <div class="p-1">
                            <label for="to" class="form-label">@lang('branch_report.To')</label>
                            <input type="date" name="to" class="form-control" id="to" value="{{ request('to') }}">
                        </div>
                        <div class="p-1">
                            <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                            <button type="submit" class="btn btn-outline-success btn-wave mt-4">@lang('branch_report.Search')</button>
                        </div>
                    </form>
                    <form method="GET" class="d-flex py-auto my-auto" action="{{ route('reports.branches.list') }}">
                        <input type="text" class="form-control mx-2" name="address" value="{{ request('address') }}" placeholder="@lang('branch_report.SearchByAddress')">
                        <button type="submit" class="btn btn-outline-success mt-4">@lang('branch_report.Search')</button>
                    </form>
                    <!-- <form method="GET" class="d-flex py-auto my-auto" action="{{ route('reports.branches.list') }}">
                        <input type="text" class="form-control mx-2" name="branchName" value="{{ request('branchName') }}" placeholder="@lang('branch_report.SearchBybranchName')">
                        <button type="submit" class="btn btn-outline-success mt-4">@lang('branch_report.Search')</button>
                    </form> -->
                    <form method="GET" class="d-flex py-auto my-auto" action="{{ route('reports.branches.list') }}">
                        <select class="form-control mx-2" name="branchName">
                            <option value="" selected disabled>@lang('branch_report.SearchBybranchName')</option>
                            @foreach($allbranches as $branch)
                                <option value="{{ $branch->name }}" {{ request('branchName') == $branch->name ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-outline-success mt-4">@lang('branch_report.Search')</button>
                    </form>

                </div>
            </div>

            <!-- Start:: row-4 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">@lang('sidebar.branches_reports')</div>
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
                                    <th>@lang('branch_report.ID')</th>
                                    <th>@lang('branch_report.Name')</th>
                                    <th>@lang('branch_report.Address')</th>
                                    <th>@lang('branch_report.Country')</th>
                                    <th>@lang('branch_report.Manager')</th>
                                    <th>@lang('branch_report.Phone')</th>
                                    <th>@lang('branch_report.Email')</th>
                                    <th>@lang('branch_report.Actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($branches as $branch)
                                    <tr data-status="{{ $branch->is_active ? 'active' : 'inactive' }} {{ $branch->is_delivery ? 'delivery' : '' }}">
                                        <td>{{ $branch->id }}</td>
                                        <td>{{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar}}</td>
                                        <td>{{ app()->getLocale() == 'en' ? $branch->address_en : $branch->address_ar }}</td>
                                        <td>{{ app()->getLocale() == 'en' ? $branch->country->name_en : $branch->country->name_ar }}</td>
                                        <td>{{ $branch->employess && $branch->employess->first_name ? ($branch->employess->first_name." ".$branch->employess->last_name) : null }}</td>
                                        <td>{{ $branch->phone }}</td>
                                        <td>{{ $branch->email }}</td>
                                        <td>
                                            @if (auth('admin')->user()->hasPermissionTo('detail report_branches', 'admin'))
                                            <a href="{{ route('reports.branches.show', ['id' => $branch->id]) }}"
                                               class="btn btn-info-light btn-wave">
                                                @lang('branch_report.Details') <i class="ri-eye-line"></i>
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
@endsection
