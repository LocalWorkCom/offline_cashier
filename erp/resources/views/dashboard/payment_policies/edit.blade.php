@extends('layouts.master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('payment_policies.EditPaymentPolicy')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('payment_policies.list') }}">@lang('payment_policies.PaymentPolicies')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('payment_policies.edit', ['id' => $id]) }}">@lang('payment_policies.EditPaymentPolicy')</a>
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
                        <div class="card-header">
                            <div class="card-title">@lang('payment_policies.EditPaymentPolicy')</div>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endforeach
                            @endif
                            <form method="POST" action="{{ route('payment_policies.update', $paymentPolicy->id) }}"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="row gy-4">
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('payment_policies.Branch')</label>
                                        <select class="form-control select2" name="branch_id" id="branchSelect" required>
                                            <option value="" disabled selected>@lang('payment_policies.SelectBranch')</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ old('branch_id', $paymentPolicy->branch_id) == $branch->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.RequiredBranch')</div>
                                    </div>

                                    <!-- Order Type -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('payment_policies.OrderType')</label>
                                        <select class="form-control select2" name="order_type" id="orderTypeSelect"
                                            required>
                                            <option value="" disabled>@lang('payment_policies.SelectOrderType')</option>
                                            <option value="takeaway"
                                                {{ old('order_type', $paymentPolicy->order_type) == 'takeaway' ? 'selected' : '' }}>
                                                @lang('payment_policies.takeaway')</option>
                                            <option value="delivery"
                                                {{ old('order_type', $paymentPolicy->order_type) == 'delivery' ? 'selected' : '' }}>
                                                @lang('payment_policies.delivery')</option>
                                            <option value="dine-in"
                                                {{ old('order_type', $paymentPolicy->order_type) == 'dine-in' ? 'selected' : '' }}>
                                                @lang('payment_policies.dine-in')</option>
                                            <option value="reservation_with_order"
                                                {{ old('order_type', $paymentPolicy->order_type) == 'reservation_with_order' ? 'selected' : '' }}>
                                                @lang('payment_policies.reservation_with_order')</option>
                                            <option value="reservation_without_order"
                                                {{ old('order_type', $paymentPolicy->order_type) == 'reservation_without_order' ? 'selected' : '' }}>
                                                @lang('payment_policies.reservation_without_order')</option>
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterOrderType')</div>
                                    </div>

                                    <!-- Payment Options -->
                                    <div class="col-xl-6">
                                        <p class="mb-2 text-muted">@lang('payment_policies.PaymentOptions')</p>

                                        <div class="form-check">
                                            <input type="checkbox" name="no_payment_required" value="1"
                                                class="form-check-input"
                                                {{ old('no_payment_required', $paymentPolicy->no_payment_required) ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('payment_policies.NoPaymentRequired')</label>
                                        </div>

                                        <div class="form-check">
                                            <input type="checkbox" name="deposit_required" value="1"
                                                class="form-check-input"
                                                {{ old('deposit_required', $paymentPolicy->deposit_required) ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('payment_policies.DepositRequired')</label>
                                        </div>

                                        <div class="form-check">
                                            <input type="checkbox" name="full_payment_required" value="1"
                                                class="form-check-input"
                                                {{ old('full_payment_required', $paymentPolicy->full_payment_required) ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('payment_policies.FullPaymentRequired')</label>
                                        </div>

                                        <div class="form-check">
                                            <input type="checkbox" name="table_cancelation_value_type" value="1"
                                                class="form-check-input"
                                                {{ old('table_cancelation_value_type', $paymentPolicy->table_cancelation_value_type) ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('payment_policies.TableCancelationValueType')</label>
                                        </div>
                                    </div>
                                    <!-- Add this to your edit.blade.php -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('payment_policies.InvoiceCount')</label>
                                        <input type="number" class="form-control" name="invoice_count"
                                            value="{{ old('invoice_count', $paymentPolicy->invoice_count) }}"
                                            min="0">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterValidInvoiceNumber')
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <button type="submit" class="btn btn-primary">@lang('payment_policies.Save')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @vite('resources/assets/js/select2.js')
    @vite('resources/assets/js/validation.js')

    <script>
        $(document).ready(function() {
            $('.select2').select2();

            let branchOrderTypes = @json($branchOrderTypes);

            function updateOrderTypeOptions(selectedBranch) {
                let orderTypeSelect = $('#orderTypeSelect');

                // Get the current selected order type
                let currentOrderType = orderTypeSelect.val();

                // Enable all options first before disabling
                orderTypeSelect.find('option').prop('disabled', false);

                let usedOrderTypes = branchOrderTypes[selectedBranch] ?
                    branchOrderTypes[selectedBranch].map(item => item.order_type) : [];

                orderTypeSelect.find('option').each(function() {
                    let optionValue = $(this).val();

                    if (optionValue && usedOrderTypes.includes(optionValue)) {
                        // Disable only if it's not the currently selected type
                        if (optionValue !== currentOrderType) {
                            $(this).prop('disabled', true);
                        }
                    }
                });
            }

            // Initial update when the page loads
            let initialBranch = $('#branchSelect').val();
            updateOrderTypeOptions(initialBranch);

            $('#branchSelect').on('change', function() {
                let selectedBranch = $(this).val();

                // Reset order type selection when branch changes
                $('#orderTypeSelect').val('').trigger('change');

                updateOrderTypeOptions(selectedBranch);
            });
        });

        // $(document).ready(function() {
        //     $('.select2').select2();

        //     let branchOrderTypes = @json($branchOrderTypes);
        //     let currentBranch = $('#branchSelect').val();
        //     let currentOrderType = $('#orderTypeSelect').val();

        //     function updateOrderTypeOptions(selectedBranch) {
        //         let usedOrderTypes = branchOrderTypes[selectedBranch] ? branchOrderTypes[selectedBranch]
        //             .map(item => item.order_type) : [];

        //         let orderTypeSelect = $('#orderTypeSelect');
        //         orderTypeSelect.find('option').each(function() {
        //             let optionValue = $(this).val();

        //             // Enable the current selected order type for editing
        //             if (optionValue && usedOrderTypes.includes(optionValue) && optionValue !==
        //                 currentOrderType) {
        //                 $(this).prop('disabled', true);
        //             } else {
        //                 $(this).prop('disabled', false);
        //             }
        //         });
        //     }

        //     // Initial update when the page loads
        //     updateOrderTypeOptions(currentBranch);

        //     $('#branchSelect').on('change', function() {
        //         let selectedBranch = $(this).val();
        //         updateOrderTypeOptions(selectedBranch);
        //         $('#orderTypeSelect').val('').trigger('change'); // Reset selection
        //     });
        // });
    </script>
@endsection
