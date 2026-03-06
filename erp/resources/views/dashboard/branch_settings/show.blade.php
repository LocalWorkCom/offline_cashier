@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('branch_settings.ShowBranchSetting')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);"
                            onclick="window.location.href='{{ route('branch_settings.list') }}'">@lang('branch_settings.BranchSettings')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('branch_settings.show', ['id' => $id]) }}">@lang('branch_settings.ShowBranchSetting')</a>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('branch_settings.ShowBranchSetting')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.Branch')</label>
                                    <p class="form-text">{{ $branchSetting->branch->name ?? __('branch_settings.none') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.DepositWithoutOrderDeductionPolicy')</label>
                                    <p class="form-text">
                                        {{ __('branch_settings.' . $branchSetting->deposit_without_order_deduction_policy) }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.DepositWithoutOrderDeductionPercentage')</label>
                                    <p class="form-text">
                                        {{ $branchSetting->deposit_without_order_deduction_percentage ?? 0 }}%</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.DepositWithOrderDeductionPolicy')</label>
                                    <p class="form-text">
                                        {{ __('branch_settings.' . $branchSetting->deposit_with_order_deduction_policy) }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.DepositWithOrderDeductionPercentage')</label>
                                    <p class="form-text">{{ $branchSetting->deposit_with_order_deduction_percentage ?? 0 }}%
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.FullPaidOrderDeductionPolicy')</label>
                                    <p class="form-text">
                                        {{ __('branch_settings.' . $branchSetting->full_paid_order_deduction_policy) }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.FullPaidOrderDeductionPercentage')</label>
                                    <p class="form-text">{{ $branchSetting->full_paid_order_deduction_percentage ?? 0 }}%
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.AlertBeforeArrivalMinutes')</label>
                                    <p class="form-text">{{ $branchSetting->alert_before_arrival_minutes }}
                                        @lang('branch_settings.minutes')</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.AlertAfterArrivalMinutes')</label>
                                    <p class="form-text">{{ $branchSetting->alert_after_arrival_minutes }}
                                        @lang('branch_settings.minutes')</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.TableSession')</label>
                                    <p class="form-text">{{ $branchSetting->table_session_minutes }} @lang('branch_settings.minutes')</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.TakeawaySessionMinutes')</label>
                                    <p class="form-text">{{ $branchSetting->takeaway_session_minutes }} @lang('branch_settings.minutes')
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.TakeawayDepositValueIf1')</label>
                                    <p class="form-text">
                                        {{ $branchSetting->takeaway_deposit_value_if_1 ??  0  }}%</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.TableCancelationTimeAllowed')</label>
                                    <p class="form-text">
                                        {{ $branchSetting->table_cancelation_time_allowed ?: __('branch_settings.none') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.CapacityTakeaway')</label>
                                    <p class="form-text">{{ $branchSetting->capacity_takeaway }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.table_reservation_deposit')</label>
                                    <p class="form-text">{{ $branchSetting->table_reservation_deposit }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.order_reservation_deposit')</label>
                                    <p class="form-text">{{ $branchSetting->order_reservation_deposit }}%</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.ApplyOrderPreparationSchedulingFullPayment')</label>
                                    <p class="form-text">
                                        {{ $branchSetting->full_payment_preparation_setting ? __('branch_settings.Yes') : __('branch_settings.No') }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.ApplyOrderPreparationSchedulingNoPayment')</label>
                                    <p class="form-text">
                                        {{ $branchSetting->no_payment_preparation_setting ? __('branch_settings.Yes') : __('branch_settings.No') }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch_settings.ApplyOrderPreparationSchedulingDeposit')</label>
                                    <p class="form-text">
                                        {{ $branchSetting->deposit_preparation_setting ? __('branch_settings.Yes') : __('branch_settings.No') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->
        </div>
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
