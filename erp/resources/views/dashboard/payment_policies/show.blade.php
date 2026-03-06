@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('payment_policies.ShowPaymentPolicy')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);"
                            onclick="window.location.href='{{ route('payment_policies.list') }}'">@lang('payment_policies.PaymentPolicies')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('payment_policies.show', ['id' => $id]) }}">@lang('payment_policies.ShowPaymentPolicy')</a>
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
                            <div class="card-title">@lang('payment_policies.ShowPaymentPolicy')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('payment_policies.Branch')</label>
                                    <p class="form-text">
                                        @if($paymentPolicy->branch)
                                            {{ app()->getLocale() == 'en' ? $paymentPolicy->branch->name_en : $paymentPolicy->branch->name_ar }}
                                        @else
                                            {{ __('payment_policies.none') }}
                                        @endif
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('payment_policies.OrderType')</label>
                                    <p class="form-text">{{ __('payment_policies.' . $paymentPolicy->order_type) }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('payment_policies.NoPaymentRequired')</label>
                                    <p class="form-text">
                                        {{ $paymentPolicy->no_payment_required ? __('payment_policies.yes') : __('payment_policies.no') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('payment_policies.DepositRequired')</label>
                                    <p class="form-text">
                                        {{ $paymentPolicy->deposit_required ? __('payment_policies.yes') : __('payment_policies.no') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('payment_policies.FullPaymentRequired')</label>
                                    <p class="form-text">
                                        {{ $paymentPolicy->full_payment_required ? __('payment_policies.yes') : __('payment_policies.no') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('payment_policies.TableCancelationValueType')</label>
                                    <p class="form-text">
                                        {{ $paymentPolicy->table_cancelation_value_type ? __('payment_policies.yes') : __('payment_policies.no') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('payment_policies.InvoiceCount')</label>
                                    <p class="form-text">
                                        {{ $paymentPolicy->invoice_count ?? 0 }}
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