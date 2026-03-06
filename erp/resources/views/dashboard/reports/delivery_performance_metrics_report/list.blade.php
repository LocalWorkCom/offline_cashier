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
        <h4 class="fw-medium mb-0">@lang('report.delivery_performance_metrics_report')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href=""
                            onclick="window.location.href='{{ route('reports.delivery_performance_metrics_report.list') }}'">@lang('report.delivery_performance_metrics_report')</a>
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
                            <div class="card-title">@lang('report.delivery_performance_metrics_report')</div>

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

                            <form method="GET" action="{{ route('reports.delivery_performance_metrics_report.list') }}">
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
                                        <label>@lang('report.Area')</label>
                                        <select name="area_id" class="form-control">
                                            <option value="">@lang('report.All')</option>
                                            {{-- Loop through areas related to the selected branch --}}
                                            @foreach ($areas as $area)
                                                <option value="{{ $area->id }}"
                                                    {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'ar' ? $area->name_ar : $area->name_en }}
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
                                    <div class="col-md-3">
                                        <label>@lang('report.DeliveryName')</label>
                                        <select name="delivery_id" class="form-control">
                                            <option value="">@lang('report.All')</option>
                                            @foreach ($deliveryNames as $delivery)
                                                <option value="{{ $delivery['id'] }}"
                                                    {{ request('delivery_id') == $delivery['id'] ? 'selected' : '' }}>
                                                    {{ $delivery['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('report.DeliveryPhone')</label>
                                        <input type="text" name="delivery_phone" class="form-control"
                                            value="{{ request('delivery_phone') }}" placeholder="@lang('report.EnterDeliveryPhone')">
                                    </div>

                                    <div class="col-md-2">
                                        <label>@lang('report.OrderNumber')</label>
                                        <input type="text" name="order_number" class="form-control"
                                            value="{{ request('order_number') }}" placeholder="@lang('report.EnterOrderNumber')">
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
                                                'from_date',
                                                'to_date',
                                                'delivery_name',
                                                'delivery_phone',
                                                'order_number',
                                            ]))
                                            <a href="{{ route('reports.delivery_performance_metrics_report.list') }}"
                                                class="btn btn-secondary ml-2">@lang('report.Reset')</a>
                                        @endif
                                    </div>
                                </div>
                            </form>

                             <!-- Add this totals section -->
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="stats-box">
                                        <span class="badge bg-secondary">@lang('report.totalCompletedOrders'):
                                            {{ $totals['totalCompletedOrders'] }}</span>
                                        <span class="badge bg-success">@lang('report.TotalCancelledOrders'):
                                            {{ $totals['TotalCancelledOrders'] }}</span>
                                        <span class="badge bg-primary">@lang('report.TotalHoldOrders'):
                                            {{ $totals['TotalHoldOrders'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('category.ID')</th>
                                        <th>@lang('report.DeliveryName')</th>
                                        <th>@lang('report.totalCompletedOrders')</th>
                                        <th>@lang('report.TotalCancelledOrders')</th>
                                        <th>@lang('report.TotalHoldOrders')</th>
                                        <th>@lang('report.CancellationDetails')</th>
                                        <th>@lang('report.TimeOfDay')</th>
                                        <th>@lang('report.OnTimeDelivery')</th>
                                        <th>@lang('report.DelayedDeliveryPercentage')</th>
                                        <th>@lang('report.TotalDelayedDeliveries')</th>
                                        <th>@lang('report.Avg.TimeOfDay')</th>
                                        <th scope="col">@lang('category.Actions')</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($DeliveryReport as $delivery)
                                        <tr>
                                            {{-- Delivery Info --}}
                                            <td>{{ is_array($delivery) ? $delivery['delivery_id'] ?? 'N/A' : 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $firstName = is_array($delivery)
                                                        ? (is_array($delivery['first_name'] ?? null)
                                                            ? ''
                                                            : $delivery['first_name'] ?? '')
                                                        : '';
                                                    $lastName = is_array($delivery)
                                                        ? (is_array($delivery['last_name'] ?? null)
                                                            ? ''
                                                            : $delivery['last_name'] ?? '')
                                                        : '';
                                                @endphp
                                                {{ $firstName }} {{ $lastName }}
                                            </td>
                                            <td>{{ $delivery['total_completed_orders'] ?? 0 }}</td>
                                            <td>{{ $delivery['total_canceled_orders'] ?? 0 }}</td>
                                            <td>{{ $delivery['total_hold_orders'] ?? 0 }}</td>

                                            {{-- Cancellations --}}
                                            @if (($delivery['total_canceled_orders'] ?? 0) > 0)
                                                <td>
                                                    @foreach ($delivery['cancellations'] as $cancellation)
                                                        <div>
                                                            <strong>@lang('report.OrderNumber'):</strong>
                                                            {{ $cancellation['order_number'] }}<br>
                                                            <strong>@lang('report.BranchName'):</strong>
                                                            {{ $cancellation['branch_name'] }}<br>
                                                            <strong>@lang('report.CancelReason'):</strong>
                                                            {{ $cancellation['cancel_reason'] }}<br><br>
                                                        </div>
                                                        <hr>
                                                    @endforeach
                                                </td>
                                            @else
                                                <td>-</td>
                                            @endif
                                            <td>
                                                @if (!empty($delivery['time_of_day']))
                                                    @foreach ($delivery['time_of_day'] as $time)
                                                        <div>
                                                            <strong>@lang('report.OrderNumber'):</strong>
                                                            {{ $time['order_number'] }}<br>
                                                            <strong>@lang('report.EstimatedTime'):</strong>
                                                            {{ $time['estimated_time'] }}
                                                        </div>
                                                        <hr>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($delivery['on_time_deliveries']))
                                                    @foreach ($delivery['on_time_deliveries'] as $deliveryItem)
                                                        <div>
                                                            <strong>@lang('report.OrderNumber'):</strong>
                                                            {{ $deliveryItem['order_number'] }}<br>
                                                            <strong>@lang('report.DeliveredTime'):</strong>
                                                            {{ $deliveryItem['delivered_time'] }}
                                                        </div>
                                                        <hr>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td>
                                                <div>
                                                    <strong>@lang('report.Percentage'):</strong>
                                                    {{ $delivery['delayed_delivery_percentage'] ?? 0 }}%
                                                </div>
                                                @if (!empty($delivery['delayed_orders']))
                                                    <hr>
                                                    @foreach ($delivery['delayed_orders'] as $delayed)
                                                        <div>
                                                            <strong>@lang('report.OrderNumber'):</strong>
                                                            {{ $delayed['order_number'] }}<br>
                                                            <strong>@lang('report.EstimatedTime'):</strong>
                                                            {{ $delayed['estimated_time'] }}<br>
                                                            <strong>@lang('report.DeliveredTime'):</strong>
                                                            {{ $delayed['delivered_time'] }}<br>
                                                            <strong>@lang('report.DelayTime'):</strong>
                                                            {{ $delayed['delay_minutes'] ?? 0 }} @lang('report.Minutes')<br>
                                                            <strong>@lang('report.avg_delivery_time'):</strong>
                                                            {{ $delayed['avg_delivery_time'] ?? 0 }} @lang('report.Minutes')<br>

                                                        </div>
                                                        <hr>
                                                    @endforeach
                                                @else
                                                    <div>-</div>
                                                @endif
                                            </td>
                                            {{-- ➕ Total Delayed Deliveries --}}
                                            <td>{{ is_array($delivery['delayed_orders'] ?? null) ? count($delivery['delayed_orders']) : 0 }}
                                            </td>
                                            <td>
                                                {{ $delivery['avg_time_of_day'] ?? '-' }}
                                            </td>






                                            {{-- avg_delay_time for all orders --}}

                                            {{-- <td>
                                                {{ $delivery['avg_delay_time'] ?? 0 }} @lang('report.Minutes')
                                            </td> --}}

                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('detail delivery_performance_metrics_report', 'admin'))
                                                    <a href="{{ route('reports.delivery_performance_metrics_report.show', $delivery['delivery_id']) }}?{{ http_build_query(request()->query()) }}"
                                                        class="btn btn-info-light btn-wave">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">@lang('report.No data available')</td>
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
