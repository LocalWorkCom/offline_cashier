@extends('layouts.master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('branch_settings.EditSetting')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('branch_settings.list') }}">@lang('branch_settings.BranchSettings')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('branch_settings.edit', ['id' => $id]) }}">@lang('branch_settings.EditSetting')</a>
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
                            <div class="card-title">@lang('branch_settings.EditSetting')</div>
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
                            <form method="POST" action="{{ route('branch_settings.update', $branchSetting->id) }}"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="row gy-4">
                                    <!-- Branch Selection -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.Branch')</label>
                                        <select class="form-control select2 @error('branch_id') is-invalid @enderror"
                                            name="branch_id" required>
                                            <option value="" disabled selected>@lang('branch_settings.SelectBranch')</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ old('branch_id', $branchSetting->branch_id) == $branch->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- 
                                    <!-- Order Type Selection -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.OrderType')</label>
                                        <select class="form-control @error('order_type') is-invalid @enderror"
                                            name="order_type" id="order_type">
                                            <option value="" disabled selected>@lang('branch_settings.SelectOrderType')</option>
                                        </select>
                                        @error('order_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div> --}}

                                    <!-- Deposit Without Order Deduction Policy -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.DepositWithoutOrderDeductionPolicy')</label>
                                        <select
                                            class="form-control @error('deposit_without_order_deduction_policy') is-invalid @enderror"
                                            name="deposit_without_order_deduction_policy">
                                            <option value="" disabled selected>@lang('branch_settings.SelectDeductionPolicy')</option>
                                            <option value="full"
                                                {{ old('deposit_without_order_deduction_policy', $branchSetting->deposit_without_order_deduction_policy) == 'full' ? 'selected' : '' }}>
                                                @lang('branch_settings.full')</option>
                                            <option value="part"
                                                {{ old('deposit_without_order_deduction_policy', $branchSetting->deposit_without_order_deduction_policy) == 'part' ? 'selected' : '' }}>
                                                @lang('branch_settings.partial')</option>
                                            <option value="none"
                                                {{ old('deposit_without_order_deduction_policy', $branchSetting->deposit_without_order_deduction_policy) == 'none' ? 'selected' : '' }}>
                                                @lang('branch_settings.none')</option>
                                        </select>
                                        @error('deposit_without_order_deduction_policy')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Deposit Without Order Deduction Percentage -->
                                    <div class="col-xl-6" id="deposit_without_order_percentage_div">
                                        <label class="form-label">@lang('branch_settings.DepositWithoutOrderDeductionPercentage')</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('deposit_without_order_deduction_percentage') is-invalid @enderror"
                                            name="deposit_without_order_deduction_percentage"
                                            value="{{ old('deposit_without_order_deduction_percentage', $branchSetting->deposit_without_order_deduction_percentage) }}"
                                            min="0" max="100">
                                        @error('deposit_without_order_deduction_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Deposit With Order Deduction Policy -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.DepositWithOrderDeductionPolicy')</label>
                                        <select
                                            class="form-control @error('deposit_with_order_deduction_policy') is-invalid @enderror"
                                            name="deposit_with_order_deduction_policy">
                                            <option value="" disabled selected>@lang('branch_settings.SelectDeductionPolicy')</option>
                                            <option value="full"
                                                {{ old('deposit_with_order_deduction_policy', $branchSetting->deposit_with_order_deduction_policy) == 'full' ? 'selected' : '' }}>
                                                @lang('branch_settings.full')</option>
                                            <option value="part"
                                                {{ old('deposit_with_order_deduction_policy', $branchSetting->deposit_with_order_deduction_policy) == 'part' ? 'selected' : '' }}>
                                                @lang('branch_settings.partial')</option>
                                            <option value="none"
                                                {{ old('deposit_with_order_deduction_policy', $branchSetting->deposit_with_order_deduction_policy) == 'none' ? 'selected' : '' }}>
                                                @lang('branch_settings.none')</option>
                                        </select>
                                        @error('deposit_with_order_deduction_policy')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Deposit With Order Deduction Percentage -->
                                    <div class="col-xl-6" id="deposit_with_order_percentage_div">
                                        <label class="form-label">@lang('branch_settings.DepositWithOrderDeductionPercentage')</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('deposit_with_order_deduction_percentage') is-invalid @enderror"
                                            name="deposit_with_order_deduction_percentage"
                                            value="{{ old('deposit_with_order_deduction_percentage', $branchSetting->deposit_with_order_deduction_percentage) }}"
                                            min="0" max="100">
                                        @error('deposit_with_order_deduction_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Full Paid Order Deduction Policy -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.FullPaidOrderDeductionPolicy')</label>
                                        <select
                                            class="form-control @error('full_paid_order_deduction_policy') is-invalid @enderror"
                                            name="full_paid_order_deduction_policy">
                                            <option value="" disabled selected>@lang('branch_settings.SelectDeductionPolicy')</option>
                                            <option value="full"
                                                {{ old('full_paid_order_deduction_policy', $branchSetting->full_paid_order_deduction_policy) == 'full' ? 'selected' : '' }}>
                                                @lang('branch_settings.full')</option>
                                            <option value="part"
                                                {{ old('full_paid_order_deduction_policy', $branchSetting->full_paid_order_deduction_policy) == 'part' ? 'selected' : '' }}>
                                                @lang('branch_settings.partial')</option>
                                            <option value="none"
                                                {{ old('full_paid_order_deduction_policy', $branchSetting->full_paid_order_deduction_policy) == 'none' ? 'selected' : '' }}>
                                                @lang('branch_settings.none')</option>
                                        </select>
                                        @error('full_paid_order_deduction_policy')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Full Paid Order Deduction Percentage -->
                                    <div class="col-xl-6" id="full_paid_order_percentage_div">
                                        <label class="form-label">@lang('branch_settings.FullPaidOrderDeductionPercentage')</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('full_paid_order_deduction_percentage') is-invalid @enderror"
                                            name="full_paid_order_deduction_percentage"
                                            value="{{ old('full_paid_order_deduction_percentage', $branchSetting->full_paid_order_deduction_percentage) }}"
                                            min="0" max="100">
                                        @error('full_paid_order_deduction_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Alert Before Arrival -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.AlertBeforeArrivalMinutes')</label>
                                        <input type="number"
                                            class="form-control @error('alert_before_arrival_minutes') is-invalid @enderror"
                                            name="alert_before_arrival_minutes"
                                            value="{{ old('alert_before_arrival_minutes', $branchSetting->alert_before_arrival_minutes) }}"
                                            min="0">
                                        @error('alert_before_arrival_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Alert After Arrival -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.AlertAfterArrivalMinutes')</label>
                                        <input type="number"
                                            class="form-control @error('alert_after_arrival_minutes') is-invalid @enderror"
                                            name="alert_after_arrival_minutes"
                                            value="{{ old('alert_after_arrival_minutes', $branchSetting->alert_after_arrival_minutes) }}"
                                            min="0">
                                        @error('alert_after_arrival_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Table Session -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.TableSession')</label>
                                        <input type="number"
                                            class="form-control @error('table_session_minutes') is-invalid @enderror"
                                            name="table_session_minutes"
                                            value="{{ old('table_session_minutes', $branchSetting->table_session_minutes) }}"
                                            min="0">
                                        @error('table_session_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Takeaway Session -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.TakeawaySessionMinutes')</label>
                                        <input type="number"
                                            class="form-control @error('takeaway_session_minutes') is-invalid @enderror"
                                            name="takeaway_session_minutes"
                                            value="{{ old('takeaway_session_minutes', $branchSetting->takeaway_session_minutes) }}"
                                            min="0">
                                        @error('takeaway_session_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Takeaway Deposit Value -->
                                    <div class="col-xl-6" id="takeaway_deposit_div">
                                        <label class="form-label">@lang('branch_settings.TakeawayDepositValueIf1')</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('takeaway_deposit_value_if_1') is-invalid @enderror"
                                            name="takeaway_deposit_value_if_1"
                                            value="{{ old('takeaway_deposit_value_if_1', $branchSetting->takeaway_deposit_value_if_1) }}"
                                            min="0"  max="100">
                                        @error('takeaway_deposit_value_if_1')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Table Cancellation Time Allowed -->
                                    <div class="col-xl-6" id="table_cancelation_time_div">
                                        <label class="form-label">@lang('branch_settings.TableCancelationTimeAllowed')</label>
                                        <input type="number"
                                            class="form-control @error('table_cancelation_time_allowed') is-invalid @enderror"
                                            name="table_cancelation_time_allowed"
                                            value="{{ old('table_cancelation_time_allowed', $branchSetting->table_cancelation_time_allowed) }}"
                                            min="0">
                                        @error('table_cancelation_time_allowed')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Capacity Takeaway -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.CapacityTakeaway')</label>
                                        <input type="number"
                                            class="form-control @error('capacity_takeaway') is-invalid @enderror"
                                            name="capacity_takeaway"
                                            value="{{ old('capacity_takeaway', $branchSetting->capacity_takeaway) }}"
                                            min="0">
                                        @error('capacity_takeaway')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- table_reservation_deposit -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.table_reservation_deposit')</label>
                                        <input type="number"
                                            class="form-control @error('table_reservation_deposit') is-invalid @enderror"
                                            name="table_reservation_deposit"
                                            value="{{ old('table_reservation_deposit', $branchSetting->table_reservation_deposit) }}"
                                            min="0">
                                        @error('table_reservation_deposit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- order_reservation_deposit -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.order_reservation_deposit')</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('order_reservation_deposit') is-invalid @enderror"
                                            name="order_reservation_deposit"
                                            value="{{ old('order_reservation_deposit', $branchSetting->order_reservation_deposit) }}"
                                            min="0" max="100">
                                        @error('order_reservation_deposit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- Full Payment Preparation Setting -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.ApplyOrderPreparationSchedulingFullPayment')</label>
                                        <div class="form-check">
                                            <input type="radio"
                                                class="form-check-input @error('full_payment_preparation_setting') is-invalid @enderror"
                                                name="full_payment_preparation_setting" id="full_payment_yes"
                                                value="1"
                                                {{ old('full_payment_preparation_setting', $branchSetting->full_payment_preparation_setting) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="full_payment_yes">@lang('branch_settings.Yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio"
                                                class="form-check-input @error('full_payment_preparation_setting') is-invalid @enderror"
                                                name="full_payment_preparation_setting" id="full_payment_no"
                                                value="0"
                                                {{ old('full_payment_preparation_setting', $branchSetting->full_payment_preparation_setting) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="full_payment_no">@lang('branch_settings.No')</label>
                                        </div>
                                        @error('full_payment_preparation_setting')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- Full Payment Preparation Setting -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.ApplyOrderPreparationSchedulingNoPayment')</label>
                                        <div class="form-check">
                                            <input type="radio"
                                                class="form-check-input @error('no_payment_preparation_setting') is-invalid @enderror"
                                                name="no_payment_preparation_setting" id="full_payment_yes"
                                                value="1"
                                                {{ old('no_payment_preparation_setting', $branchSetting->no_payment_preparation_setting) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="full_payment_yes">@lang('branch_settings.Yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio"
                                                class="form-check-input @error('no_payment_preparation_setting') is-invalid @enderror"
                                                name="no_payment_preparation_setting" id="full_payment_no"
                                                value="0"
                                                {{ old('no_payment_preparation_setting', $branchSetting->no_payment_preparation_setting) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="full_payment_no">@lang('branch_settings.No')</label>
                                        </div>
                                        @error('no_payment_preparation_setting')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- Deposit Preparation Setting -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('branch_settings.ApplyOrderPreparationSchedulingDeposit')</label>
                                        <div class="form-check">
                                            <input type="radio"
                                                class="form-check-input @error('deposit_preparation_setting') is-invalid @enderror"
                                                name="deposit_preparation_setting" id="deposit_yes" value="1"
                                                {{ old('deposit_preparation_setting', $branchSetting->deposit_preparation_setting) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="deposit_yes">@lang('branch_settings.Yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio"
                                                class="form-check-input @error('deposit_preparation_setting') is-invalid @enderror"
                                                name="deposit_preparation_setting" id="deposit_no" value="0"
                                                {{ old('deposit_preparation_setting', $branchSetting->deposit_preparation_setting) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="deposit_no">@lang('branch_settings.No')</label>
                                        </div>
                                        @error('deposit_preparation_setting')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- Submit Button -->
                                    <div class="col-xl-12 text-center">
                                        <button type="submit" class="btn btn-primary">@lang('branch_settings.Save')</button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            // Prepare the order types data from PHP to JavaScript
            const allOrderTypes = @json($allOrderTypes);
            let paymentPoliciesData = {};

            // Order type translations mapping
            const orderTypeTranslations = {
                'takeaway': '@lang('branch_settings.order_types.takeaway')',
                'delivery': '@lang('branch_settings.order_types.delivery')',
                'dine-in': '@lang('branch_settings.order_types.dine-in')',
                'reservation_with_order': '@lang('branch_settings.order_types.reservation_with_order')',
                'reservation_without_order': '@lang('branch_settings.order_types.reservation_without_order')'
            };

            function updateOrderTypes(branchId) {
                const orderTypeSelect = $('#order_type');
                orderTypeSelect.empty(); // Clear existing options
                orderTypeSelect.append('<option value="" disabled selected>@lang('branch_settings.SelectOrderType')</option>');

                if (branchId && allOrderTypes[branchId]) {
                    // Add the order types for the selected branch with translations
                    allOrderTypes[branchId].forEach(function(orderType) {
                        const translatedText = orderTypeTranslations[orderType] || orderType;
                        orderTypeSelect.append(
                            `<option value="${orderType}" ${orderType === '{{ old('order_type', $branchSetting->order_type) }}' ? 'selected' : ''}>${translatedText}</option>`
                        );
                    });
                }
            }

            function getPaymentPolicyDetails(branchId, orderType) {
                if (!branchId || !orderType) return;

                $.ajax({
                    url: "{{ route('branch_settings.getPaymentPolicies') }}",
                    type: "GET",
                    data: {
                        branch_id: branchId,
                        order_type: orderType
                    },
                    success: function(data) {
                        paymentPoliciesData = data;
                        updateFieldVisibility();
                    },
                    error: function(xhr) {
                        console.error('Error fetching payment policies:', xhr.responseText);
                    }
                });
            }

            function updateFieldVisibility() {
                // Show/hide takeaway deposit based on deposit_required
                if (paymentPoliciesData.deposit_required == 1) {
                    $('#takeaway_deposit_div').show();
                    $('[name="takeaway_deposit_value_if_1"]').prop('required', true);
                } else {
                    $('#takeaway_deposit_div').hide();
                    $('[name="takeaway_deposit_value_if_1"]').prop('required', false);
                }

                // Show/hide table cancellation time based on table_cancelation_value_type
                if (paymentPoliciesData.table_cancelation_value_type == 1) {
                    $('#table_cancelation_time_div').show();
                    $('[name="table_cancelation_time_allowed"]').prop('required', true);
                } else {
                    $('#table_cancelation_time_div').hide();
                    $('[name="table_cancelation_time_allowed"]').prop('required', false);
                }
            }

            // Toggle percentage fields based on policy selection
            function togglePercentageFields() {
                const withoutOrderPolicy = $('[name="deposit_without_order_deduction_policy"]').val();
                const withOrderPolicy = $('[name="deposit_with_order_deduction_policy"]').val();
                const fullPaidPolicy = $('[name="full_paid_order_deduction_policy"]').val();

                $('#deposit_without_order_percentage_div').toggle(withoutOrderPolicy !== 'none');
                $('#deposit_with_order_percentage_div').toggle(withOrderPolicy !== 'none');
                $('#full_paid_order_percentage_div').toggle(fullPaidPolicy !== 'none');
            }

            // Initialize on page load
            togglePercentageFields();

            // Listen for changes
            $('[name="deposit_without_order_deduction_policy"], [name="deposit_with_order_deduction_policy"], [name="full_paid_order_deduction_policy"]')
                .change(function() {
                    togglePercentageFields();
                });

            $('select[name="branch_id"]').change(function() {
                let branchId = $(this).val();
                updateOrderTypes(branchId);
                // Clear order type selection when branch changes
                $('#order_type').val('').trigger('change');
            });

            $('#order_type').change(function() {
                const branchId = $('select[name="branch_id"]').val();
                const orderType = $(this).val();
                getPaymentPolicyDetails(branchId, orderType);
            });

            // Initialize on page load if branch is pre-selected
            let initialBranchId = $('select[name="branch_id"]').val();
            if (initialBranchId) {
                updateOrderTypes(initialBranchId);
                // If there's a pre-selected order type, get its payment policy details
                const initialOrderType = $('#order_type').val();
                if (initialOrderType) {
                    getPaymentPolicyDetails(initialBranchId, initialOrderType);
                } else if ('{{ $branchSetting->order_type }}') {
                    $('#order_type').val('{{ $branchSetting->order_type }}').trigger('change');
                }
            }
        });
    </script>
@endsection
