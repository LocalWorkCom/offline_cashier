@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('reports.reports')</h4>
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
            <!-- Start:: Filters and DataTable -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">@lang('report.best_seller_dish_report')</div>
                        </div>
                        <!-- Filter Form -->
                        <form method="GET" class="d-flex m-auto p-1" action="{{ route('reports.best_seller_dish.list') }}"
                            id="filterForm">
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
                                <label for="branch" class="form-label">@lang('reports.branch')</label>
                                <select name="branch" id="branch" class="form-control">
                                    <option value="">@lang('reports.all_branches')</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ request('branch') == $branch->id ? 'selected' : '' }}>
                                            {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="p-1">
                                <label for="dish_name" class="form-label">@lang('report.Dish Name')</label>
                                <input type="text" name="dish_name" class="form-control" id="dish_name"
                                    value="{{ request('dish_name') }}" placeholder="@lang('report.Search by dish name')">
                            </div>
                            <div class="p-1">
                                <label for="min_price" class="form-label">@lang('report.Min Price')</label>
                                <input type="number" name="min_price" class="form-control" id="min_price"
                                    value="{{ request('min_price') }}" placeholder="@lang('report.Minimum price')" min="0"
                                    step="0.01">
                            </div>
                            <div class="p-1">
                                <label for="max_price" class="form-label">@lang('report.Max Price')</label>
                                <input type="number" name="max_price" class="form-control" id="max_price"
                                    value="{{ request('max_price') }}" placeholder="@lang('report.Maximum price')" min="0"
                                    step="0.01">
                            </div>
                            <div class="p-1 d-flex align-items-end">
                                <div>
                                    <button type="submit" class="btn btn-outline-success btn-wave">
                                        @lang('reports.search') <i class="bi bi-search"></i>
                                    </button>
                                    <button type="button" id="resetFilters" class="btn btn-outline-danger btn-wave ms-2">
                                        @lang('reports.reset') <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- End Filter Form -->

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
                                        <th scope="col">@lang('report.ID')</th>
                                        <th scope="col">@lang('report.Dish Name')</th>
                                        <th scope="col">@lang('report.Total Quantity')</th>
                                        <th scope="col">@lang('report.Price')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tbody>
                                    @foreach ($dishes as $index => $dish)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ app()->getLocale() == 'en' ? $dish->name_en : $dish->name_ar }}</td>
                                            <td>{{ $dish->total_quantity }}</td>
                                            <td>


                                                {{ $dish->total_price_after_tax }}
                                                {{-- {{ $dish->currency_symbol }} --}}



                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('detail report_best_seller_dishes', 'admin'))
                                                    <a href="{{ route('reports.best_seller_dish.show', ['id' => $dish->id]) }}"
                                                        class="btn btn-info-light btn-wave">
                                                        @lang('reports.details') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: Filters and DataTable -->
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

    <!-- INTERNAL DATATABLES JS -->
    @vite('resources/assets/js/datatables.js')
    <script>
        $(document).ready(function() {
            // Reset filters button
            $('#resetFilters').click(function() {
                // Reset all form fields
                $('#filterForm').find('input[type="text"], input[type="number"], input[type="date"]').val(
                    '');
                $('#filterForm').find('select').val('');

                // Submit the form immediately after resetting
                $('#filterForm').submit();
            });
        });
    </script>
@endsection
