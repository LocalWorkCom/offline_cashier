@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('order_setting.EditOrderSetting')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('order_settings.list') }}">@lang('order_setting.OrderSettings')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('order_setting.EditOrderSetting')</li>
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
                            <div class="card-title">@lang('order_setting.EditOrderSetting')</div>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <form method="POST" action="{{ route('order_setting.update', $order_setting->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('order_setting.TaxApplication')</label>
                                        <div class="d-block">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="tax_application" id="tax_yes" value="1"
                                                       {{ old('tax_application', $order_setting->tax_application) == '1' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="tax_yes">@lang('category.yes')</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="tax_application" id="tax_no" value="0"
                                                       {{ old('tax_application', $order_setting->tax_application) == '0' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="tax_no">@lang('category.no')</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('order_setting.CouponApplication')</label>
                                        <div class="d-block">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="coupon_application" id="coupon_yes" value="1"
                                                       {{ old('coupon_application', $order_setting->coupon_application) == '1' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="coupon_yes">@lang('category.yes')</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="coupon_application" id="coupon_no" value="0"
                                                       {{ old('coupon_application', $order_setting->coupon_application) == '0' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="coupon_no">@lang('category.no')</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Input Fields -->
                                    <div class="col-xl-6">
                                        <label for="tax_percentage" class="form-label">@lang('order_setting.TaxPercentage')</label>
                                        <input type="number" name="tax_percentage" id="tax_percentage" class="form-control"
                                               placeholder="@lang('order_setting.TaxPercentage')"
                                               value="{{ old('tax_percentage', $order_setting->tax_percentage) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterTaxPercentage')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="time_cancellation" class="form-label">@lang('order_setting.TimeCancellation')</label>
                                        <input type="number" name="time_cancellation" id="time_cancellation" class="form-control"
                                               placeholder="@lang('order_setting.TimeCancellation')"
                                               value="{{ old('time_cancellation', $order_setting->time_cancellation) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterTimeCancellation')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="delivery_time" class="form-label">@lang('order_setting.DeliveryTime')</label>
                                        <input type="number" name="delivery_time" id="delivery_time" class="form-control"
                                               placeholder="@lang('order_setting.DeliveryTime')"
                                               value="{{ old('delivery_time', $order_setting->delivery_time) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterDeliveryTime')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="delivery_difference" class="form-label">@lang('order_setting.DeliveryDifference')</label>
                                        <input type="number" name="delivery_difference" id="delivery_difference" class="form-control"
                                               placeholder="@lang('order_setting.DeliveryDifference')"
                                               value="{{ old('delivery_difference', $order_setting->delivery_difference) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterDeliveryDifference')</div>
                                    </div>

                                    <!-- Submit Button -->
                                    <center>
                                        <div class="col-xl-4">
                                            <button type="submit" class="btn btn-primary form-control">@lang('category.save')</button>
                                        </div>
                                    </center>
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
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Custom JS -->
    @vite('resources/assets/js/validation.js')
@endsection
