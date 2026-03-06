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
        <h4 class="fw-medium mb-0">@lang('report.delivery_earnings_payments_report')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href=""
                            onclick="window.location.href='{{ route('reports.delivery_order_report.list') }}'">@lang('report.delivery_earnings_payments_report')</a>
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
                            <div class="card-title">@lang('report.delivery_earnings_payments_report')</div>

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

                            <form method="GET" action="{{ route('reports.delivery_earnings_payments_report.list') }}">
                                <!-- Add these new filter fields to your form -->
                                <div class="row">
                                    <div class="col-md-3">
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
                                    <div class="col-md-3">
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
                                    <div class="col-md-3">
                                        <label>@lang('report.DeliveryPhone')</label>
                                        <input type="text" name="delivery_phone" class="form-control"
                                            value="{{ request('delivery_phone') }}" placeholder="@lang('report.EnterDeliveryPhone')">
                                    </div>
                                    <!-- New filter fields -->
                                    <div class="col-md-2">
                                        <label>@lang('report.OrderNumber')</label>
                                        <input type="text" name="order_number" class="form-control"
                                            value="{{ request('order_number') }}" placeholder="@lang('report.EnterOrderNumber')">
                                    </div>

                                    <div class="col-md-2">
                                        <label>@lang('report.PaymentMethod')</label>
                                        <select name="payment_method" class="form-control">
                                            <option value="">@lang('report.All')</option>
                                            @foreach (__('report.payment_methods') as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ request('payment_method') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="col-md-2">
                                        <label>@lang('report.MinAvgPrice')</label>
                                        <input type="number" step="0.01" name="min_total_price" class="form-control"
                                            value="{{ request('min_total_price') }}" placeholder="@lang('report.MinAvgPrice')"
                                            min="0">
                                    </div>

                                    <div class="col-md-2">
                                        <label>@lang('report.MaxAvgPrice')</label>
                                        <input type="number" step="0.01" name="max_total_price" class="form-control"
                                            value="{{ request('max_total_price') }}" placeholder="@lang('report.MaxAvgPrice')"
                                            min="0">
                                    </div>


                                    <!-- Existing date filters -->
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
                                                'order_number',
                                                'payment_method',
                                                'min_total_price',
                                                'max_total_price',
                                                'delivery_name',
                                                'delivery_phone',
                                            ]))
                                            <a href="{{ route('reports.delivery_earnings_payments_report.list') }}"
                                                class="btn btn-secondary ml-2">@lang('report.Reset')</a>
                                        @endif
                                    </div>
                                </div>
                            </form>

                            <!-- Add this totals section -->
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="stats-box">
                                        <span class="badge bg-secondary">@lang('report.TotalCompletedOrders'):
                                            {{ $totals['totalCompletedOrders'] }}</span>
                                        <span class="badge bg-success">@lang('report.TotalEarnings'):
                                            {{ $totals['TotalEarnings'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('category.ID')</th>
                                        <th>@lang('report.DeliveryName')</th>
                                        <th>@lang('report.TotalCompletedOrders')</th>
                                        <th>@lang('report.TotalEarnings')</th>
                                        <th>@lang('report.OrderDetails')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($DeliveryReport as $delivery)
                                        <tr>
                                            <td>{{ $delivery['delivery_id'] ?? 'N/A' }}</td>
                                            <td>
                                                {{ $delivery['first_name'] ?? '' }} {{ $delivery['last_name'] ?? '' }}
                                            </td>
                                            <td>{{ $delivery['total_orders'] }}</td>
                                            <td>{{ $delivery['total_earnings'] }}</td>
                                            <td>
                                                @foreach ($delivery['completed_orders'] as $order)
                                                    <div class="mb-2">
                                                        <strong>@lang('report.OrderNumber'):</strong>
                                                        {{ $order['order_number'] }}<br>
                                                        <strong>@lang('report.Date'):</strong> {{ $order['date'] }}<br>
                                                        <strong>@lang('report.BranchName'):</strong>
                                                        {{ $order['branch_name'] }}<br>
                                                        <strong>@lang('report.total_price'):</strong> {{ $order['total_price'] }}
                                                        <br>
                                                        <strong>@lang('report.PaymentMethod'):</strong>
                                                        {{ $order['payment_method'] }}
                                                    </div>
                                                    @if (!$loop->last)
                                                        <hr>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('detail delivery_earnings_payments_report', 'admin'))
                                                    <a href="{{ route('reports.delivery_earnings_payments_report.show', $delivery['delivery_id']) }}?{{ http_build_query(request()->query()) }}"
                                                        class="btn btn-info-light btn-wave">@lang('category.show') <i
                                                            class="ri-eye-line"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6">@lang('report.No data available')</td>
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
