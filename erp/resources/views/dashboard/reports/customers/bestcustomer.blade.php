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
        <h4 class="fw-medium mb-0">@lang('report.reports')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('report.reports')</li>
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
                        <a class="nav-link {{ request('status') === 'all' || is_null(request('status')) ? 'active' : '' }} bg-light text-dark" href="{{ url()->current() }}?status=all">
                            @lang('branch_report.All')
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Start:: row-4 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header" style="display: flex;">
                            <div class="card-title">@lang('sidebar.report_customers') </div>

                        </div>
                        <form method="GET" class="d-flex m-auto p-1" action="{{ route('reports.most.customers.list') }}">
                            <div class="p-1">
                                <label for="from" class="form-label">@lang('reports.from')</label>
                                <input type="date" name="from" class="form-control" id="from"
                                    value="{{ request('from') }}">
                            </div>
                            <div class="p-1">
                                <label for="to" class="form-label">@lang('reports.to')</label>
                                <input type="date" name="to" class="form-control" id="to"
                                    value="{{ request('to') }}">
                            </div>

                            <div class="p-1">
                                <label for="to" class="form-label">@lang('reports.order_by')</label>

                                <select class="form-select" name="order_by">
                                    <option selected>@lang('reports.selectfrom')
                                    </option>
                                    <option value="1">@lang('reports.mostcustomerorder')</option>
                                    <option value="2">@lang('reports.mostcustomerprofit')</option>
                                </select>
                            </div>
                            <div class="p-1">
                                <label class="form-label"></label>
                                <button type="submit"
                                    class="btn btn-outline-success btn-wave mt-1">@lang('reports.search')</button>
                            </div>

                        </form>
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
                                        <th scope="col">@lang('reports.customer')</th>
                                        <th scope="col">@lang('reports.total_num')</th>
                                        <th scope="col">@lang('reports.total_price')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topClients as $item)
                                        <tr>
                                            <td>{{ $item->Client->name }}
                                                @if ($item->Client->deleted_at)
                                                    <span class="text-danger">( @lang('category.deleted'))</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->total_orders }}</td>
                                            <td>{{ $item->total_price }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view report_most_customer_place_order', 'admin'))
                                                    <a href="{{ route('reports.most.customers.detail', ['id' => $item->client_id, 'from' => request('from'), 'to' => request('to')]) }}"
                                                        class="btn btn-info-light btn-wave show-category">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
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
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
@endsection
