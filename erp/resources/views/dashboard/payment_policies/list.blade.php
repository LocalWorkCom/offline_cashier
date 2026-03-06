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
        <h4 class="fw-medium mb-0">@lang('payment_policies.PaymentPolicies')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('payment_policies.list') }}'">
                            @lang('payment_policies.PaymentPolicies')
                        </a>
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
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">@lang('payment_policies.PaymentPolicies')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create payment_policies', 'admin'))
                                <a href="{{ route('payment_policies.create') }}" class="btn btn-primary label-btn">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('payment_policies.AddPolicy')
                                </a>
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
                                        <th scope="col">@lang('payment_policies.ID')</th>
                                        <th scope="col">@lang('payment_policies.Branch')</th>
                                        <th scope="col">@lang('payment_policies.OrderType')</th>
                                        <th scope="col">@lang('payment_policies.NoPaymentRequired')</th>
                                        <th scope="col">@lang('payment_policies.DepositRequired')</th>
                                        <th scope="col">@lang('payment_policies.FullPaymentRequired')</th>
                                        <th scope="col">@lang('payment_policies.TableCancelationValueType')</th>
                                        <th scope="col">@lang('payment_policies.InvoiceCount')</th>
                                        <th scope="col">@lang('payment_policies.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($paymentPolicies as $policy)
                                        <tr>
                                            <td>{{ $policy->id }}</td>
                                            <td>{{ app()->getLocale() == 'en' ? $policy->branch->name_en : $policy->branch->name_ar }}
                                            </td>
                                            <td>{{ __('payment_policies.' . $policy->order_type) }}</td>
                                            <td>{{ $policy->no_payment_required ? __('payment_policies.yes') : __('payment_policies.no') }}
                                            </td>
                                            <td>{{ $policy->deposit_required ? __('payment_policies.yes') : __('payment_policies.no') }}
                                            </td>
                                            <td>{{ $policy->full_payment_required ? __('payment_policies.yes') : __('payment_policies.no') }}
                                            </td>
                                            <td>{{ $policy->table_cancelation_value_type ? __('payment_policies.yes') : __('payment_policies.no') }}
                                            </td>
                                            <td>{{ $policy->invoice_count }}</td>

                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view payment_policies', 'admin'))
                                                    <a href="{{ route('payment_policies.show', $policy->id) }}"
                                                        class="btn btn-info-light btn-wave">
                                                        @lang('payment_policies.Show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update payment_policies', 'admin'))
                                                    <a href="{{ route('payment_policies.edit', $policy->id) }}"
                                                        class="btn btn-orange-light btn-wave">
                                                        @lang('payment_policies.Edit') <i class="ri-edit-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete payment_policies', 'admin'))
                                                    <form class="d-inline" id="delete-form-{{ $policy->id }}"
                                                        action="{{ route('payment_policies.delete', $policy->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item('{{ $policy->id }}')"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('payment_policies.Delete') <i class="ri-delete-bin-line"></i>
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

    <!-- INTERNAL DATATABLES JS -->
    @vite('resources/assets/js/datatables.js')
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
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
