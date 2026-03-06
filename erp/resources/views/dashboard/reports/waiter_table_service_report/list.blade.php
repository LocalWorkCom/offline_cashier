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
        <h4 class="fw-medium mb-0">@lang('report.waiter_table_service_report')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href=""
                            onclick="window.location.href='{{ route('reports.waiter_table_service_report.list') }}'">@lang('report.waiter_table_service_report')</a>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-4 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header"
                            style="
                        display: flex;
                        justify-content: space-between;">
                            <div class="card-title">@lang('report.waiter_table_service_report')</div>

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

                            <form method="GET" action="{{ route('reports.waiter_table_service_report.list') }}">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>@lang('report.Branch')</label>
                                        <select name="branch_id" class="form-control">
                                            <option value="">@lang('report.All')</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="timetable_id" class="form-label">@lang('report.Timetable')</label>
                                        <select name="timetable_id" id="timetable_id" class="form-select">
                                            <option value="">@lang('report.All')</option>
                                            @foreach ($timetables as $timetable)
                                                <option value="{{ $timetable->id }}"
                                                    {{ (request('timetable_id') ?? $selectedTimetableId) == $timetable->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'en' ? $timetable->name_en : $timetable->name_ar }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('report.WaiterName')</label>
                                        <select name="waiter_id" class="form-control select2">
                                            <option value="">@lang('report.All')</option>
                                            @foreach ($waiters as $waiter)
                                                <option value="{{ $waiter->id }}"
                                                    {{ request('waiter_id') == $waiter->id ? 'selected' : '' }}>
                                                    {{ $waiter->first_name }} {{ $waiter->last_name ?? '' }}
                                                    @if ($waiter->phone_number)
                                                        - {{ $waiter->phone_number }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('report.PhoneNumber')</label>
                                        <input type="text" name="phone_number" class="form-control"
                                            value="{{ request('phone_number') }}" placeholder="@lang('report.PhoneNumber')">
                                    </div>

                                    <div class="col-md-2">
                                        <label>@lang('report.TableID')</label>
                                        <input type="number" name="table_id" class="form-control"
                                            value="{{ request('table_id') }}" placeholder="@lang('report.TableID')"
                                            min="1" oninput="this.value = Math.abs(this.value)">
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('report.MinAvgPrice')</label>
                                        <input type="number" name="min_avg_price" class="form-control"
                                            value="{{ request('min_avg_price') }}" placeholder="@lang('report.MinAvgPrice')"
                                            min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('report.MaxAvgPrice')</label>
                                        <input type="number" name="max_avg_price" class="form-control"
                                            value="{{ request('max_avg_price') }}" placeholder="@lang('report.MaxAvgPrice')"
                                            min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('report.FromDate')</label>
                                        <input type="date" name="from_date" class="form-control"
                                            value="{{ request('from_date') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('report.ToDate')</label>
                                        <input type="date" name="to_date" class="form-control"
                                            value="{{ request('to_date') }}">
                                    </div>
                                    <div class="col-md-2 mt-4">
                                        <button type="submit" class="btn btn-primary">@lang('report.Filter')</button>
                                        @if (request()->hasAny([
                                                'branch_id',
                                                'timetable_id',
                                                'waiter_id',
                                                'table_id',
                                                'min_avg_price',
                                                'max_avg_price',
                                                'from_date',
                                                'to_date',
                                            ]))
                                            <a href="{{ route('reports.waiter_table_service_report.list') }}"
                                                class="btn btn-secondary ml-2">@lang('report.Reset')</a>
                                        @endif
                                    </div>
                                </div>
                            </form>

                            <!-- Add this totals section -->
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="stats-box">
                                        <span class="badge bg-secondary">@lang('report.TotalOrders'):
                                            {{ $totals['total_orders'] }}</span>
                                        <span class="badge bg-primary">@lang('report.TotalTable'):
                                            {{ $totals['total_table'] }}</span>
                                        <span class="badge bg-success">@lang('report.TotalPrice'):
                                            {{ number_format($totals['total_price'], 2) }}</span>

                                    </div>
                                </div>
                            </div>

                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('category.ID')</th>
                                        <th scope="col">@lang('report.WaiterName')</th>
                                        <th scope="col">@lang('report.TotalOrders')</th>
                                        <th scope="col">@lang('report.TableName')</th>
                                        <th scope="col">@lang('report.Modifications')</th>
                                        <th scope="col">@lang('report.Cancellations')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($WaiterTableService as $waiter)
                                        <tr>
                                            <td>{{ $waiter['waiter_id'] }}</td>
                                            <td>{{ $waiter['first_name'] }} {{ $waiter['last_name'] ?? '' }}</td>
                                            <td>{{ $waiter['total_orders'] }}</td>
                                            <td>
                                                @foreach ($waiter['tables'] as $table)
                                                    <div>
                                                        <strong>{{ app()->getLocale() === 'ar' ? $table['name_ar'] : $table['name_en'] }}</strong><br>
                                                        @lang('report.TotalOrders'): {{ $table['order_count'] }}<br>
                                                        @lang('report.AvgPrice'):
                                                        {{ number_format($table['avg_total_price'], 2) }}
                                                    </div>
                                                    <hr>
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach ($waiter['tables'] as $table)
                                                    <div>
                                                        <strong>{{ app()->getLocale() === 'ar' ? $table['name_ar'] : $table['name_en'] }}</strong><br>
                                                        @lang('report.TotalModifications'):
                                                        {{ array_sum(array_column($table['modifications'], 'count')) }}<br>
                                                        @foreach ($table['modifications'] as $mod)
                                                            - {{ $mod['dish_name'] }} ({{ $mod['count'] }})<br>
                                                        @endforeach
                                                        @if (empty($table['modifications']))
                                                            @lang('report.NoModifications')
                                                        @endif
                                                    </div>
                                                    <hr>
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach ($waiter['tables'] as $table)
                                                    <div>
                                                        <strong>{{ app()->getLocale() === 'ar' ? $table['name_ar'] : $table['name_en'] }}</strong><br>
                                                        @lang('report.TotalCancellations'):
                                                        {{ array_sum(array_column($table['cancellations'], 'count')) }}<br>
                                                        @foreach ($table['cancellations'] as $cancel)
                                                            - {{ $cancel['dish_name'] }} ({{ $cancel['count'] }})<br>
                                                        @endforeach
                                                        @if (empty($table['cancellations']))
                                                            @lang('report.NoCancellations')
                                                        @endif
                                                    </div>
                                                    <hr>
                                                @endforeach
                                            </td>
                                            @if (auth('admin')->user()->hasPermissionTo('detail waiter_table_service_report', 'admin'))
                                                <td>
                                                    <a href="{{ route('reports.waiter_table_service_report.show', $waiter['waiter_id']) }}?{{ http_build_query(request()->query()) }}"
                                                        class="btn btn-info-light btn-wave">@lang('category.show') <i
                                                            class="ri-eye-line"></i></a>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">@lang('report.NoDataFound')</td>
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
@endsection

@section('scripts')
@endsection
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "@lang('report.SelectWaiter')",
            allowClear: true
        });
    });

    function delete_item(id) {
        Swal.fire({
            title: @json(__('validation.Alert')),
            text: @json(__('validation.DeleteConfirm')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: @json(__('validation.Delete')),
            cancelButtonText: @json(__('validation.Cancel')),
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('delete-form-' + id);
                form.submit();
            }
        });
    }
</script>
