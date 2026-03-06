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
    <h4 class="fw-medium mb-0">@lang('sidebar.returnInvoiceRequest')</h4>
    <div class="ms-sm-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('sidebar.returnInvoiceRequest')</li>
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
                        <div class="card-title">@lang('sidebar.returnInvoiceRequest')</div>

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
                        <table id="orders-table" class="table table-bordered text-nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">@lang('einvoice.ID')</th>
                                    <th scope="col">@lang('einvoice.invoice_num')</th>
                                    <th scope="col">@lang('einvoice.invoice_details_ids')</th>
                                    <th scope="col">@lang('einvoice.order_num')</th>
                                    <th scope="col">@lang('einvoice.reject_resone')</th>
                                    <th scope="col">@lang('einvoice.request_type')</th>
                                    <th scope="col">@lang('einvoice.status')</th>
                                    <th scope="col">@lang('einvoice.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($returnInvoices as $index => $returnInvoice)
                                <tr>
                                    <td>{{ $returnInvoice->id }}</td>
                                    <td>{{ $returnInvoice->invoice ? $returnInvoice->invoice['invoice_num'] : "" }}</td>
                                    <td>{{ implode(' - ', $namesDishAndAddon[$index] ?? []) }}</td>
                                    {{-- <td>{{ $returnInvoice->invoice ? $returnInvoice->invoice['orders']['order_number'] : ""   }}</td>
                                    --}}
                                    <td>{{ $returnInvoice->invoice['orders']['order_number'] ?? '' }}</td>
                                    <td>
                                        {{ $returnInvoice->reject_resone ?? (app()->getLocale() == 'en' 
        ? 'no reason, order not rejected' 
        : 'لا يوجد سبب، الطلب غير مرفوض') }}
                                    </td>
                                    <td>@lang('einvoice.' . $returnInvoice->request_type)</td>
                                    <td>@lang('einvoice.' . $returnInvoice->status)</td>
                                    <td>
                                        @if (auth('admin')->user()->hasPermissionTo('show returnInvoiceRequest', 'admin'))
                                        <!-- Show Button -->
                                        <a href="{{ route('return-invoice-request.show', $returnInvoice->id) }}"
                                            class="btn btn-info-light btn-wave show-order">
                                            @lang('einvoice.show_details') <i class="ri-eye-line"></i>
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

    </div>
</div>

@endsection

@section('scripts')
<!-- Existing CDN scripts -->
<script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
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



<script>
$(document).ready(function () {
    $('#orders-table').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        language: {
            url: "{{ app()->getLocale() == 'ar' 
                ? '//cdn.datatables.net/plug-ins/1.12.1/i18n/ar.json' 
                : '//cdn.datatables.net/plug-ins/1.12.1/i18n/en-GB.json' }}"
        }
    });
});
</script>

@endsection