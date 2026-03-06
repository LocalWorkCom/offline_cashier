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
        <h4 class="fw-medium mb-0">@lang('reservations.table_reservations')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href=""
                            onclick="window.location.href='{{ route('reports.table_reservations.list') }}'">@lang('reservations.table_reservations')</a>
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
                            <div class="card-title">@lang('reservations.table_reservations')</div>

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

                            <form method="GET" action="{{ route('reports.table_reservations.list') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>@lang('reservations.Branch')</label>
                                        <select name="branch_id" class="form-control">
                                            <option value="">@lang('reservations.All')</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>@lang('reservations.Client')</label>
                                        <select name="client_id" class="form-control select2">
                                            <option value="">@lang('reservations.All')</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}"
                                                    {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                                    {{ $client->name }}
                                                    @if ($client->phone)
                                                        - {{ $client->phone }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('reservations.Client Phone')</label>
                                        <input type="text" name="phone" class="form-control"
                                            value="{{ request('phone') }}" placeholder="@lang('reservations.Phone')">
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('reservations.TableID')</label>
                                        <input type="number" name="table_id" class="form-control"
                                            value="{{ request('table_id') }}" placeholder="@lang('reservations.TableID')"
                                            min="1" oninput="this.value = Math.abs(this.value)">
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('reservations.Status')</label>
                                        <select name="status" class="form-control">
                                            <option value="">@lang('reservations.All')</option>
                                            <option value="confirm" {{ request('status') == 'confirm' ? 'selected' : '' }}>
                                                @lang('reservations.Confirmed')
                                            </option>
                                            <option value="cancel" {{ request('status') == 'cancel' ? 'selected' : '' }}>
                                                @lang('reservations.cancelled')
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('reservations.FromDate')</label>
                                        <input type="date" name="from_date" class="form-control"
                                            value="{{ request('from_date') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label>@lang('reservations.ToDate')</label>
                                        <input type="date" name="to_date" class="form-control"
                                            value="{{ request('to_date') }}">
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">@lang('reservations.Filter')</button>
                                        <a href="{{ route('reports.table_reservations.list') }}" class="btn btn-secondary">
                                            @lang('reports.reset')
                                        </a>
                                    </div>
                                </div>
                            </form>
                            <!-- Add this totals section -->
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="stats-box">
                                        <span class="badge bg-secondary">@lang('reservations.TotalReservations'):
                                            {{ $totals['total_reservations'] }}</span>
                                        <span class="badge bg-success">@lang('reservations.TotalPaid'):
                                            {{ number_format($totals['total_paid'], 2) }}</span>
                                        <span class="badge bg-primary">@lang('reservations.TotalRefund'):
                                            {{ number_format($totals['total_refund'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('category.ID')</th>
                                        <th scope="col">@lang('reservations.TableID')</th>
                                        <th scope="col">@lang('reservations.Client')</th>
                                        <th scope="col">@lang('reservations.Branch')</th>
                                        <th scope="col">@lang('reservations.ConfirmedDate')</th>
                                        <th scope="col">@lang('reservations.ConfirmedTime')</th>
                                        <th scope="col">@lang('reservations.ReservationType')</th>
                                        <th scope="col">@lang('reservations.Status')</th>
                                        <th scope="col">@lang('reservations.Confirmed')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($reservations) && count($reservations))
                                        @foreach ($reservations as $reservation)
                                            <tr>
                                                <td>{{ $reservation['id'] ?? '-' }}</td>
                                                <td>{{ $reservation['table_id'] ?? '-' }}</td>
                                                <td>{{ $reservation->client->name ?? 'N/A' }}</td>
                                                <td>{{ !empty($reservation['branch']['name_ar']) ? (app()->getLocale() === 'ar' ? $reservation['branch']['name_ar'] : $reservation['branch']['name_en']) : __('order.none') }}
                                                </td>
                                                <td>{{ $reservation['confirmed_date'] ?? '-' }}</td>
                                                <td>{{ isset($reservation['confirmed_time']) ? \Carbon\Carbon::parse($reservation['confirmed_time'])->format('h:i:s A') : __('reservations.NotAvailable') }}
                                                </td>

                                                <td>{{ $reservation['reservation_type'] == 'with' ? __('reservations.WithMeal') : __('reservations.WithoutMeal') }}
                                                </td>
                                                <td>
                                                    @if ($reservation['status'] == 'confirm')
                                                        @lang('reservations.Confirmed')
                                                    @else
                                                        @lang('reservations.cancelled')
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($reservation['confirmed'] == 1)
                                                        @lang('reservations.Pending')
                                                    @elseif($reservation['confirmed'] == 2)
                                                        @lang('reservations.Confirmed')
                                                    @else
                                                        @lang('reservations.Rejected')
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (auth('admin')->user()->hasPermissionTo('detail report_table_reservations', 'admin'))
                                                        <a href="{{ route('reports.table_reservations.show', $reservation['id']) }}"
                                                            class="btn btn-info-light btn-wave">@lang('category.show') <i
                                                                class="ri-eye-line"></i></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif


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
