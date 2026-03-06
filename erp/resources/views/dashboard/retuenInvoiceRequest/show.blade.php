@extends('layouts.master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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

        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>

@section('content')

    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('einvoice.show')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('return-invoice-request.index') }}'">
                            @lang('sidebar.returnInvoiceRequest')
                        </a>
                    </li>
                </ol>

            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                <h4> @lang('einvoice.invoice_details')</h4>

                                @if ($returnInvoices->status === 'pending')
                                    <ul class="nav nav-tabs mb-4" id="filter-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a id="acceptBtn" class="nav-link active bg-light text-dark"
                                                data-status="accept" href="javascript:void(0);">
                                                @lang('einvoice.accept')
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a id="rejectBtn" class="nav-link bg-warning text-dark" data-status="reject"
                                                href="javascript:void(0);">
                                                @lang('einvoice.reject')
                                            </a>
                                        </li>
                                    </ul>
                                @endif

                                @if ($returnInvoices->invoice->id)
                                    <div>
                                        <a target="_blank" href="{{ route('invoice.show', $returnInvoices->invoice->id) }}"
                                            class="btn btn-primary-light btn-wave">
                                            <i class="ri-file-text-line"></i> @lang('einvoice.view_original_invoice')
                                        </a>
                                    </div>
                                @endif

                            </div>
                        </div>

                        <div class="card-body">
                            <div id="select-error-msg" class="alert alert-danger d-none"></div>
                            {{-- Handle API errors --}}
                            @if (session('errors'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    @if (is_array(session('errors')))
                                        @foreach (session('errors') as $error)
                                            {{ $error }}
                                        @endforeach
                                    @else
                                        {{ session('errors') }}
                                    @endif
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- Handle direct errors passed to view --}}
                            @if (isset($errors) && is_array($errors))
                                @foreach ($errors as $error)
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        @if (is_array($error))
                                            {{ implode(' ', $error) }}
                                        @else
                                            {{ $error }}
                                        @endif
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endforeach
                            @endif



                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('einvoice.invoice_num')</label>
                                    <p class="form-text">{{ $returnInvoices->invoice->invoice_num }}</p>
                                </div>
                                @if($returnInvoices->status === 'accept')
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('einvoice.invoicerefund')</label>
                                    <p class="form-text">{{ $invoicerefund }}</p>
                                </div>
                                @endif
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.status')</label>
                                    <p class="form-text">@lang('einvoice.' . $returnInvoices->status)</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.reject_resone')</label>
                                    <p class="form-text">{{ $returnInvoices->reject_resone }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.reason')</label>
                                    <p class="form-text">{{ $returnInvoices->reason }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.date')</label>
                                    <p class="form-text">
                                        {{ \Carbon\Carbon::parse($returnInvoices->date)->format('Y-m-d') }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.time')</label>
                                    <p class="form-text">{{ $returnInvoices->time }}</p>
                                </div>
                                {{-- <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.request_num')</label>
                                    <p class="form-text">{{ $returnInvoices->request_num }}</p>
                                    </div> --}}
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.request_type')</label>
                                    <p class="form-text">@lang('einvoice.' . $returnInvoices->request_type)</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.created_by')</label>
                                    <p class="form-text">
                                        {{ $returnInvoices->employee->first_name . ' ' . $returnInvoices->employee->last_name }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.invoice_order')</label>
                                    <p class="form-text">@lang('einvoice.' . $returnInvoices->invoice->status)</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.type')</label>
                                    <p class="form-text">@lang('einvoice.' . $returnInvoices->invoice->order_type)</p>
                                </div>

                                <h4>@lang('einvoice.detailsOrder')</h4>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.order_number')</label>
                                    <p class="form-text">{{ $returnInvoices->invoice->orders->order_number }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.status')</label>
                                    <p class="form-text">@lang('einvoice.' . $returnInvoices->invoice->orders->status)</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.type')</label>
                                    <p class="form-text">@lang('einvoice.' . $returnInvoices->invoice->orders->type)</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.branch')</label>
                                    <p class="form-text">{{ $returnInvoices->invoice->orders->branch->name }}</p>
                                </div>
                                <!-- Admin Code -->
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.admin_code')</label>
                                    <p class="form-text">{{ $orderDetails2->admin_code ?? 'N/A' }}</p>
                                </div>

                                <!-- Timestamp -->
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('einvoice.timestamp')</label>
                                    <p class="form-text">{{ $orderDetails2->order_timestamp ?? 'N/A' }}
                                    </p>
                                </div>


                                <h4>@lang('einvoice.dish_details')</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>@lang('einvoice.dish')</th>
                                            <th>@lang('einvoice.size')</th>
                                            <th>@lang('einvoice.quantity')</th>
                                            <th>@lang('einvoice.price')</th>
                                            <th>@lang('einvoice.tax')</th>
                                            <th>@lang('einvoice.service_fees')</th>
                                            <th>@lang('einvoice.total')</th>
                                            <th>@lang('einvoice.status')</th>
                                            <th>@lang('einvoice.waste')</th>
                                            <th>@lang('einvoice.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dataOfDishes as $detail)
                                            <tr data-id="{{ $detail->invoiceId }}" data-type="dish"
                                                data-waste="{{ $detail->waste }}"></tr>
                                            <tr>
                                                <td>{{ $detail->name }}</td>
                                                <td>{{ $detail->size }}</td>
                                                <td>{{ $detail->quantity }}</td>
                                                <td>{{ $detail->price }}</td>
                                                <td>{{ $detail->tax }}</td>
                                                <td>{{ $detail->service_fees }}</td>
                                                <td>{{ $detail->total_after_tax }}</td>
                                                <td>{{ __('einvoice.' . $detail->status) }}</td>
                                                <td>{{ __('einvoice.' . $detail->waste) }}</td>
                                                <td>
                                                    @if ($orders === 'pending' || ($returnInvoices->status === 'accept' || $returnInvoices->status === 'reject'))
                                                        <span id="dataItems" class="text-muted">@lang('einvoice.cant_update')</span>
                                                    @else
                                                        <select class="form-select d-inline" style="width: auto;"
                                                            data-id="{{ $detail->invoiceId }}" data-type="dish">
                                                            <option>@lang('einvoice.ChooseBranchDetails')</option>
                                                            <option value="waste">@lang('einvoice.waste')</option>
                                                            <option value="not_waste">@lang('einvoice.not_waste')</option>
                                                            <option value="temp">@lang('einvoice.temporary_waste')</option>
                                                        </select>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <h4>@lang('einvoice.addon_details')</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>@lang('einvoice.addon')</th>
                                            <th>@lang('einvoice.dish')</th>
                                            <th>@lang('einvoice.quantity')</th>
                                            <th>@lang('einvoice.price')</th>
                                            <th>@lang('einvoice.tax')</th>
                                            <th>@lang('einvoice.service_fees')</th>
                                            <th>@lang('einvoice.total')</th>

                                            <th>@lang('einvoice.status')</th>
                                            <th>@lang('einvoice.waste')</th>
                                            <th>@lang('einvoice.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dataOfAddons as $detail)
                                            <tr data-id="{{ $detail->invoiceId }}" data-type="addon"
                                                data-waste="{{ $detail->waste }}"></tr>
                                            <tr>

                                                <td>{{ $detail->name }}</td>
                                                <td>{{ $detail->forDish }}</td>
                                                <td>{{ $detail->quantity }}</td>
                                                <td>{{ $detail->price }}</td>
                                                <td>{{ $detail->tax }}</td>
                                                <td>{{ $detail->service_fees }}</td>
                                                <td>{{ $detail->total_after_tax }}</td>

                                                <td>{{ __('einvoice.' . $detail->status) }}</td>
                                                <td>{{ __('einvoice.' . $detail->waste) }}</td>
                                                <td>

                                                    @if ($orders === 'pending' || ($returnInvoices->status === 'accept' || $returnInvoices->status === 'reject'))
                                                        <span id="dataItems" class="text-muted">@lang('einvoice.cant_update')</span>
                                                    @else
                                                        <select class="form-select d-inline" style="width: auto;"
                                                            data-id="{{ $detail->invoiceId }}" data-type="addon">
                                                            <option>@lang('einvoice.ChooseBranchDetails')</option>
                                                            <option value="waste">@lang('einvoice.waste')</option>
                                                            <option value="not_waste">@lang('einvoice.not_waste')</option>
                                                            <option value="temp">@lang('einvoice.temporary_waste')</option>
                                                        </select>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>


                                <h4>@lang('einvoice.total_details')</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>@lang('einvoice.quantity')</th>
                                            <th>@lang('einvoice.price')</th>
                                            <th>@lang('einvoice.tax')</th>
                                            <th>@lang('einvoice.service_fees')</th>
                                            <th>@lang('einvoice.coupon')</th>
                                            <th>@lang('einvoice.total')</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                            <tr>
                                                <td></td>
                                                <td>{{array_sum(array_column($dataOfDishes, 'quantity'))}}</td>
                                                <td>{{array_sum(array_column($dataOfDishes, 'price')) + array_sum(array_column($dataOfAddons, 'price'))}}</td>
                                                <td>{{array_sum(array_column($dataOfDishes, 'tax')) + array_sum(array_column($dataOfAddons, 'tax'))}}</td>
                                                <td>{{array_sum(array_column($dataOfDishes, 'service_fees')) + array_sum(array_column($dataOfAddons, 'service_fees'))}}</td>
                                                <td>{{array_sum(array_column($dataOfDishes, 'coupon_value')) + array_sum(array_column($dataOfAddons, 'coupon_value'))}}</td>
                                                <td>{{array_sum(array_column($dataOfDishes, 'total_after_tax')) + array_sum(array_column($dataOfAddons, 'total_after_tax'))}}</td>
                                            </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- CANCEL MODAL -->
                            <div class="modal fade" id="cancelOrderModal" tabindex="-1"
                                aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="cancelOrderModalLabel">@lang('einvoice.cancel_order')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="cancelOrderForm">
                                                @csrf
                                                <input type="hidden" id="cancelOrderDetailId" name="order_detail_id">
                                                <input type="hidden" id="cancelOrderStatus" name="status">

                                                <div class="mb-3">
                                                    <label for="cancelReason"
                                                        class="form-label">@lang('einvoice.rejecrReason')</label>
                                                    <textarea class="form-control" id="cancelReason" name="reject_resone" rows="3"></textarea>
                                                </div>
                                                <!-- <div class="mb-3">
                                                                <label class="form-label">@lang('einvoice.reject_type')</label>
                                                                <div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio" name="reject_type" id="rejectTypeFull" value="full" checked>
                                                                        <label class="form-check-label" for="rejectTypeFull">@lang('einvoice.full')</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio" name="reject_type" id="rejectTypePartial" value="partial">
                                                                        <label class="form-check-label" for="rejectTypePartial">@lang('einvoice.partial')</label>
                                                                    </div>
                                                                </div>
                                                            </div> -->

                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                @lang('einvoice.close')
                                            </button>
                                            <button type="button" class="btn btn-primary" onclick="sendCancelRequest()">
                                                @lang('einvoice.submit')
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endsection

   @section('scripts')
<script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    $('.nav-link').on('click', function () {
        var status = $(this).data('status');
        var invoiceId = {{ $returnInvoices->id }};
        var statusText = status === 'accept' ? '@lang('einvoice.accept')' : '@lang('einvoice.reject')';
        Swal.fire({
            title: '@lang("einvoice.confirm_title")',
            text: "@lang('einvoice.confirm_change_status_to') " + statusText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '@lang("einvoice.yes")',
            cancelButtonText: '@lang("einvoice.no")'
        }).then((result) => {
            if (result.isConfirmed) {
                if (status === 'reject') {
                    $('#cancelOrderDetailId').val(invoiceId);
                    $('#cancelOrderStatus').val('reject');
                    $('#cancelReason').val('');
                    $('#cancelOrderModal').modal('show');
                } else if (status === 'accept') {
                    $('#select-error-msg').addClass('d-none').text('');
                    let isValid = true;
                    let items = [];
                    $('select[data-type]').each(function () {
                        let value = $(this).val();
                        let itemId = $(this).data('id');
                        let type = $(this).data('type');
                        let quantity = $(this).closest('tr').prev('tr[data-id]').next('tr').find('td:nth-child(3)').text();
                        if (!value || value === '@lang("einvoice.ChooseBranchDetails")') {
                            isValid = false;
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                            items.push({
                                invoice_detail_id: itemId,
                                type: value,
                                data_type: type,
                                quantity: parseInt(quantity)
                            });
                        }
                    });
                    if (!isValid) {
                        Swal.fire({
                            icon: 'warning',
                            title: '@lang("einvoice.alert")',
                            text: '@lang("einvoice.select_all_waste_values")'
                        });
                        $('#select-error-msg')
                        .removeClass('d-none')
                        .text('@lang("einvoice.select_all_waste_values")');
                        return;
                    }
                    // إرسال الطلب إذا جميع الحقول صحيحة
                    $.ajax({
                        url: '/dashboard/return-invoice-request/update/' + invoiceId,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: 'accept',
                            items: items
                        },
                        success: function () {
                            Swal.fire('@lang("einvoice.done")', '@lang("einvoice.status_updated_successfully")', 'success').then(() => {
                                $('#acceptBtn, #rejectBtn').hide();
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            let response = xhr.responseJSON;
                            let errorMsg = '';
                            if (response.errors) {
                                // Handle array of errors or single error
                                if (Array.isArray(response.errors)) {
                                    errorMsg = response.errors.join('<br>');
                                } else if (typeof response.errors === 'object') {
                                    errorMsg = Object.values(response.errors).flat().join('<br>');
                                } else {
                                    errorMsg = response.errors;
                                }
                            } else {
                                errorMsg = '@lang("einvoice.update_error")';
                            }
                            $('#select-error-msg').removeClass('d-none').html(`
                                <div class="alert alert-danger alert-dismissible fade show">
                                    ${errorMsg}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `);
                        }
                    });
                }
            }
        });
    });
});
function sendCancelRequest() {
    var invoiceId = $('#cancelOrderDetailId').val();
    var reason = $('#cancelReason').val();
    if (!reason.trim()) {
        Swal.fire('@lang("einvoice.alert")', '@lang("einvoice.enter_reject_reason")', 'warning');
        return;
    }
    // جمع البيانات من الصفوف (dishes و addons)
    let items = [];
    $('tr[data-id]').each(function () {
        let row = $(this);
        let itemId = row.data('id');
        let type = row.data('type');
        let wasteValue = row.data('waste');
        let select = row.find('select');
        let value = select.length > 0 ? select.val() : wasteValue;
        let quantity = row.next('tr').find('td:nth-child(3)').text();
        items.push({
            invoice_detail_id: itemId,
            type: value,
            quantity: parseInt(quantity)
        });
    });
    // إرسال البيانات
    $.ajax({
        url: '/dashboard/return-invoice-request/update/' + invoiceId,
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: 'reject',
            reject_resone: reason,
            items: items
        },
        success: function () {
            Swal.fire('@lang("einvoice.done")', '@lang("einvoice.order_rejected")', 'success').then(() => {
                $('#cancelOrderModal').modal('hide');
                $('#acceptBtn, #rejectBtn').hide();
                location.reload();
            });
        },
        error: function (xhr) {
            let response = xhr.responseJSON;
            let errorMsg = '';
            if (response.errors) {
                // Handle array of errors or single error
                if (Array.isArray(response.errors)) {
                    errorMsg = response.errors.join('<br>');
                } else if (typeof response.errors === 'object') {
                    errorMsg = Object.values(response.errors).flat().join('<br>');
                } else {
                    errorMsg = response.errors;
                }
            } else {
                errorMsg = '@lang("einvoice.update_error")';
            }
            $('#select-error-msg').removeClass('d-none').html(`
                <div class="alert alert-danger alert-dismissible fade show">
                    ${errorMsg}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
        }
    });
}
</script>
@endsection