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
        <h4 class="fw-medium mb-0">@lang('order_setting.OrderSettings')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);"
                           onclick="window.location.href='{{ route('order_settings.list') }}'">@lang('order_setting.OrderSettings')</a>
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
                            <div class="card-title">@lang('order_setting.OrderSettings')</div>
                            @if(!$recordExists)

                            @if (auth('admin')->user()->hasPermissionTo('create order_settings', 'admin'))

                                <button type="button" class="btn btn-primary label-btn"
                                        onclick="window.location.href='{{ route('order_setting.create') }}'">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('order_setting.AddOrderSetting')
                                </button>
                                @endif
                            @endif
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
                                    <th scope="col">@lang('category.ID')</th>
                                    <th scope="col">@lang('order_setting.TaxApplication')</th>
                                    <th scope="col">@lang('order_setting.CouponApplication')</th>
                                    <th scope="col">@lang('order_setting.TaxPercentage')</th>
                                    <th scope="col">@lang('order_setting.TimeCancellation')</th>
                                    <th scope="col">@lang('order_setting.DeliveryTime')</th>
                                    <th scope="col">@lang('order_setting.DeliveryDifference')</th>
                                    <th scope="col">@lang('category.Actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $rowNumber = 1; @endphp
                                @foreach ($order_settings as $order_setting)
                                    <tr>
                                        <td>{{ $rowNumber++  }}</td>
                                        <td>{{ $order_setting->tax_application == 0 ? __('category.no') : __('category.yes')}}</td>
                                        <td>{{ $order_setting->coupon_application == 0 ? __('category.no') : __('category.yes')}}</td>
                                        <td>{{ $order_setting->tax_percentage }}</td>
                                        <td>{{ $order_setting->time_cancellation }}</td>
                                        <td>{{ $order_setting->delivery_time }}</td>
                                        <td>{{ $order_setting->delivery_difference }}</td>
                                        <td>
                                            @if (auth('admin')->user()->hasPermissionTo('view order_settings', 'admin'))

                                            <a href="{{ route('order_setting.show', $order_setting->id) }}" class="btn btn-info-light btn-wave">@lang('category.show') <i class="ri-eye-line"></i></a>
                                            @endif

                                            @if (auth('admin')->user()->hasPermissionTo('update order_settings', 'admin'))
                                            <a href="{{ route('order_setting.edit', $order_setting->id) }}" class="btn btn-orange-light btn-wave">@lang('category.edit') <i class="ri-edit-line"></i></a>
                                           @endif
                                            @if (auth('admin')->user()->hasPermissionTo('delete order_settings', 'admin'))
                                            <form class="d-inline" id="delete-form-{{ $order_setting->id }}"
                                                  action="{{ route('order_setting.delete', $order_setting->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="delete_item('{{ $order_setting->id }}')"
                                                        class="btn btn-danger-light btn-wave">
                                                    @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
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
@endsection
<script>
    function confirmDelete() {
        return confirm("@lang('validation.DeleteConfirm')");
    }
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
