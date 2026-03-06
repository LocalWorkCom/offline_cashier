@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            /* width: 500px; */
            /* Set a specific width */
            height: 300px;
            /* Set a specific height */
            /* You can adjust the values based on your needs */
        }

        .map-container {
            width: 100%;
            /* Or a specific width */
            /* You can set the width to 100% to fill the parent or any specific width like 500px */
        }
    </style>
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('branch.EditBranch')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('branches.list') }}">@lang('branch.Branches')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch.EditBranch')</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid ">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('branch.EditBranch')
                            </div>
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

                            <form method="POST" id="branch-form" action="{{ route('branch.update', $branch->id) }}"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT') <!-- Use PUT or PATCH for updates -->
                                <div class="row gy-4">
                                    <!-- Arabic Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.ArabicName')</label>
                                        <input type="text" class="form-control" name="name_ar"
                                            value="{{ old('name_ar', $branch->name_ar) }}" placeholder="@lang('branch.ArabicName')"
                                            required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicName')
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.EnglishName')</label>
                                        <input type="text" class="form-control" name="name_en"
                                            value="{{ old('name_en', $branch->name_en) }}" placeholder="@lang('branch.EnglishName')"
                                            required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishName')
                                        </div>
                                    </div>

                                    <!-- Address Arabic -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.ArabicAddress')</label>
                                        <textarea class="form-control" name="address_ar" id="searchBox" onblur="searchLocation()" rows="2" required>{{ old('address_ar', $branch->address_ar) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicAddress')
                                        </div>
                                    </div>

                                    <!-- Address English -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.EnglishAddress')</label>
                                        <textarea class="form-control" name="address_en" rows="2" required>{{ old('address_en', $branch->address_en) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishAddress')
                                        </div>
                                    </div>

                                    <!-- Code -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.Code')</label>
                                        <textarea class="form-control" name="code" required rows="2">{{ old('code', $branch->code) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCode')
                                        </div>
                                    </div>

                                    <!-- Street -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.Street')</label>
                                        <textarea class="form-control" name="street" required rows="2">{{ old('street', $branch->street) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterStreet')
                                        </div>
                                    </div>

                                    <!-- BuildingNumber -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.BuildingNumber')</label>
                                        <input type="number" class="form-control" name="buildingNumber" id="buildingNumber"
                                            required value="{{ old('buildingNumber', $branch->buildingNumber) }}"
                                            maxlength="20">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterBuildingNumber')
                                        </div>
                                    </div>

                                    <!-- AutoCloseChat -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.AutoCloseChat')</label>
                                        <input type="number" class="form-control" name="auto_close_chat"
                                            id="auto_close_chat" required
                                            value="{{ old('auto_close_chat', $branch->auto_close_chat) }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterAutoCloseChat')
                                        </div>
                                    </div>

                                    <!-- Tax Apply -->
                                    <!-- <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                            <label class="form-label">@lang('branch.TaxApply')</label>
                                            <select class="form-control" name="tax_apply" required>
                                                <option value="0"
                                                    {{ old('tax_apply', $branch->tax_apply) == 0 ? 'selected' : '' }}>
                                                    @lang('branch.TaxNotApplied')
                                                </option>
                                                <option value="1"
                                                    {{ old('tax_apply', $branch->tax_apply) == 1 ? 'selected' : '' }}>
                                                    @lang('branch.TaxApplied')
                                                </option>
                                            </select>
                                        </div> -->

                                    <!-- Tax Application -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.TaxApplication')</label>
                                        <select class="form-control" name="tax_application" id="tax_application"
                                            required>
                                            <option value="0"
                                                {{ old('tax_application', $branch->tax_application) == 0 ? 'selected' : '' }}>
                                                @lang('branch.TaxNotIncluded')
                                            </option>
                                            <option value="1"
                                                {{ old('tax_application', $branch->tax_application) == 1 ? 'selected' : '' }}>
                                                @lang('branch.TaxIncluded')
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Tax Percentage -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.TaxPercentage')</label>
                                        <input type="number" class="form-control" name="tax_percentage"
                                            id="tax_percentage"
                                            value="{{ old('tax_percentage', $branch->tax_percentage) }}" min="0"
                                            max="100" step="0.01" required>
                                    </div>

                                    <!-- Coupon Application -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.CouponApplication')</label>
                                        <select class="form-control" name="coupon_application" required>
                                            <option value="0"
                                                {{ old('coupon_application', $branch->coupon_application) == 0 ? 'selected' : '' }}>
                                                @lang('branch.BeforeTax')
                                            </option>
                                            <option value="1"
                                                {{ old('coupon_application', $branch->coupon_application) == 1 ? 'selected' : '' }}>
                                                @lang('branch.AfterTax')
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.ServicesFeesType')</label>
                                        <select class="form-control" name="service_fees_type" id="service_fees_type"
                                            required>
                                            <option value="fixed"
                                                {{ old('service_fees_type', $branch->service_fees_type) === 'fixed' ? 'selected' : '' }}>
                                                @lang('branch.fixed')</option>
                                            <option value="percentage"
                                                {{ old('service_fees_type', $branch->service_fees_type) === 'percentage' ? 'selected' : '' }}>
                                                @lang('branch.percentage')</option>
                                        </select>
                                    </div>

                                    <!-- Services Fees -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.ServicesFees')</label>
                                        <input type="number" class="form-control" name="service_fees"
                                            value="{{ old('service_fees', $branch->service_fees) }}"
                                            step="0.01"min="0" required>
                                    </div>

                                    <!-- delivery_fees -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.delivery_fees')</label>
                                        <input type="number" class="form-control" name="delivery_fees"
                                            value="{{ old('delivery_fees', $branch->delivery_fees) }}"
                                            step="0.01" min="0" required>
                                    </div>

                                    <!-- Time Cancellation -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.TimeCancellation')</label>
                                        <input type="number" class="form-control" name="time_cancellation"
                                            value="{{ old('time_cancellation', $branch->time_cancellation) }}"
                                            min="1" required>
                                    </div>

                                    <!-- Delivery Time -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.DeliveryTime')</label>
                                        <input type="number" class="form-control" name="delivery_time"
                                            value="{{ old('delivery_time', $branch->delivery_time) }}" min="1"
                                            required>
                                    </div>

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <div class="map position-relative my-3">
                                            <div class="map col-xl-12 col-lg-12 col-md-12 col-sm-12" id="map"
                                                width="auto" height="450" style="border:0;"></div>
                                        </div>

                                        <input type="hidden" id="latitude" name="latitute"
                                            value="{{ old('latitute', $branch->latitute) }}">
                                        <input type="hidden" id="longitude" name="longitute"
                                            value="{{ old('longitute', $branch->longitute) }}">
                                    </div>

                                    <div class="logout-modal modal fade" tabindex="-1" id="notfoundddressModal">
                                        <div class="modal-dialog  modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header border-0">
                                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <i class="fas fa-sign-out-alt main-color fs-1"></i>
                                                    <h4 class="mt-4"> @lang('auth.notfoundaddress')</h4>
                                                </div>
                                                <div
                                                    class="modal-footer d-flex border-0 align-items-center justify-content-center">
                                                    <button type="button" class="btn reversed main-color w-25 mx-2"
                                                        data-bs-dismiss="modal"> @lang('auth.ok')</button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Country -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.Country')</label>
                                        <select class="form-control select2" name="country_id" id="country_id" required>
                                            <option value="" disabled>@lang('branch.SelectCountry')</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}"
                                                    {{ $country->id == old('country_id', $branch->country_id) ? 'selected' : '' }}>
                                                    {{ $country->name_ar . ' | ' . $country->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCountry')
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('country.ChooseCity')</label>
                                        <select class="form-control select2" name="city_id" id="city_id" required>
                                            <option value="" disabled selected>@lang('country.ChooseCity')</option>
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->id }}"
                                                    {{ $city->id == old('city_id', $branch->city_id) ? 'selected' : '' }}>
                                                    {{ $city->name_ar . ' | ' . $city->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCity')
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('country.ChooseRegion')</label>
                                        <select class="form-control select2" name="region_id" id="region_id" required>
                                            <option value="0" disabled selected>@lang('country.ChooseRegion')</option>
                                            @foreach ($regions as $region)
                                                <option value="{{ $region->id }}"
                                                    {{ $region->id == old('region_id', $branch->area_id) ? 'selected' : '' }}>
                                                    {{ $region->name_ar . ' | ' . $region->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterState')
                                        </div>
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.Phone')</label>
                                        <input type="tel" pattern=".{10}" class="form-control phone-valid"
                                            name="phone" id="phone" required
                                            value="{{ old('phone', $branch->phone) }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterPhone')
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.Email')</label>
                                        <input type="email" class="form-control" name="email"
                                            value="{{ old('email', $branch->email) }}">
                                    </div>

                                    <!-- Manager Name -->
                                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.ManagerName')</label>
                                        <select class="form-control select2" name="employee_id" id="employee_id"
                                            required>
                                            <option value="" disabled selected>@lang('branch.SelectEmployee')</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}"
                                                    {{ $employee->id == old('employee_id', $branch->employee_id) ? 'selected' : '' }}>
                                                    {{ $employee->first_name . '  ' . $employee->last_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEmployee')
                                        </div>
                                    </div>

                                    <!-- Manager Name -->
                                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.CompanyProfile')</label>
                                        <select class="form-control select2" name="company_profile_setting_id"
                                            id="company_profile_setting_id" required>
                                            <option value="" disabled selected>@lang('branch.SelectCompanyProfile')</option>
                                            @foreach ($company_profile_settings as $company_profile_setting)
                                                <option value="{{ $company_profile_setting->id }}"
                                                    {{ $company_profile_setting->id == old('company_profile_setting_id', $branch->company_profile_setting_id) ? 'selected' : '' }}>
                                                    {{ $company_profile_setting->name_site }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCompany')
                                        </div>
                                    </div>

                                    <!-- Has Kids Area -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.HasKidsArea')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_kids_area"
                                                value="1"
                                                {{ old('has_kids_area', $branch->has_kids_area) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_kids_area"
                                                value="0"
                                                {{ old('has_kids_area', $branch->has_kids_area) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- Is Delivery -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsDelivery')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_delivery"
                                                value="1"
                                                {{ old('is_delivery', $branch->is_delivery) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_delivery"
                                                value="0"
                                                {{ old('is_delivery', $branch->is_delivery) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- Is default -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsDefault')</label>
                                        <div class="form-check">
                                            <input class="form-check-input is_default_no" id="default_yes" type="radio"
                                                name="is_default" value="1"
                                                {{ old('is_default', $branch->is_default) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input is_default_no" id="default_no" type="radio"
                                                name="is_default" value="0"
                                                {{ old('is_default', $branch->is_default) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- Is default -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsActive')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_active"
                                                value="1"
                                                {{ old('is_active', $branch->is_active) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_active"
                                                value="0"
                                                {{ old('is_active', $branch->is_active) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- IsTableReservation -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsTableReservation')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_table_reservation"
                                                value="1"
                                                {{ old('is_table_reservation', $branch->is_table_reservation) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_table_reservation"
                                                value="0"
                                                {{ old('is_table_reservation', $branch->is_table_reservation) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- IsTakeaway -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsTakeaway')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_takeaway"
                                                value="1"
                                                {{ old('is_takeaway', $branch->is_takeaway) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_takeaway"
                                                value="0"
                                                {{ old('is_takeaway', $branch->is_takeaway) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    @if (auth('admin')->user()->hasPermissionTo('handle branch is_live', 'admin'))
                                    <!-- IsLive -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsLive')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_live" value="1"
                                                {{ old('is_live', $branch->is_live) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_live" value="0"
                                                {{ old('is_live', $branch->is_live) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>
                                    @endif

                                    <div id="dish-sizes-section" class="col-xl-12">
                                        <h5 class="mb-3">@lang('branch.barchTime')</h5>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('branch.Day')</th>
                                                    <th>@lang('branch.OpeningHour')</th>
                                                    <th>@lang('branch.ClosingHour')</th>
                                                    <th>@lang('branch.CrossDay')</th>
                                                    <th>@lang('branch.Actions')</th>
                                                </tr>
                                            </thead>
                                            <tbody id="branch-time-table">

                                                @if ($branch->branchTimes)
                                                    @foreach ($branch->branchTimes as $k_branchTime => $branchTime)
                                                        <tr>
                                                            <td>
                                                                <input type="hidden"
                                                                    name="time[{{ $k_branchTime }}][id]"
                                                                    id="time[{{ $k_branchTime }}][id]"
                                                                    value="{{ $branchTime->id }}">
                                                                <select name="time[{{ $k_branchTime }}][day]"
                                                                    id="time[{{ $k_branchTime }}][day]"
                                                                    class="form-control select2" required>
                                                                    <option value="6"
                                                                        {{ $branchTime->day == 6 ? 'selected' : '' }}>
                                                                        @lang('branch.Saturday')</option>
                                                                    <option value="0"
                                                                        {{ $branchTime->day == 0 ? 'selected' : '' }}>
                                                                        @lang('branch.Sunday')</option>
                                                                    <option value="1"
                                                                        {{ $branchTime->day == 1 ? 'selected' : '' }}>
                                                                        @lang('branch.Monday')</option>
                                                                    <option value="2"
                                                                        {{ $branchTime->day == 2 ? 'selected' : '' }}>
                                                                        @lang('branch.Tuesday')</option>
                                                                    <option value="3"
                                                                        {{ $branchTime->day == 3 ? 'selected' : '' }}>
                                                                        @lang('branch.Wednesday')</option>
                                                                    <option value="4"
                                                                        {{ $branchTime->day == 4 ? 'selected' : '' }}>
                                                                        @lang('branch.Thursday')</option>
                                                                    <option value="5"
                                                                        {{ $branchTime->day == 5 ? 'selected' : '' }}>
                                                                        @lang('branch.Friday')</option>
                                                                </select>
                                                            </td>
                                                            <td><input type="time"
                                                                    name="time[{{ $k_branchTime }}][opening_hour]"
                                                                    value="{{ $branchTime->opening_hour }}"
                                                                    class="form-control" required></td>
                                                            <td><input type="time"
                                                                    name="time[{{ $k_branchTime }}][closing_hour]"
                                                                    value="{{ $branchTime->closing_hour }}"
                                                                    class="form-control" required></td>
                                                            <td>
                                                                @lang('branch.24Hours')<input type="radio"
                                                                    name="time[{{ $k_branchTime }}][cross_day]"
                                                                    value="0" class="form-check-input"
                                                                    {{ $branchTime->cross_day == 0 ? 'checked' : '' }}><br>
                                                                @lang('branch.12Hours')<input type="radio"
                                                                    name="time[{{ $k_branchTime }}][cross_day]"
                                                                    value="1" class="form-check-input"
                                                                    {{ $branchTime->cross_day == 1 ? 'checked' : '' }}>
                                                            </td>
                                                            <td><button type="button"
                                                                    class="btn btn-danger btn-sm remove-row">@lang('branch.remove')</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <button type="button" id="add-branch-time"
                                            class="btn btn-success btn-sm">@lang('branch.AddTime')</button>
                                    </div>

                                    <div id="dish-sizes-section" class="col-xl-12">
                                        <h5 class="mb-3">@lang('branch.barchRegion')</h5>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('branch.region')</th>
                                                    <th>@lang('branch.price')</th>
                                                    <th>@lang('branch.Actions')</th>
                                                </tr>
                                            </thead>
                                            <tbody id="branch-region-table">

                                                @if ($branch->branchRegions)
                                                    @foreach ($branch->branchRegions as $k_branchRegion => $branchRegion)
                                                        <tr>
                                                            <td>
                                                                <input type="hidden"
                                                                    name="region[{{ $k_branchRegion }}][id]"
                                                                    id="region[{{ $k_branchRegion }}][id]"
                                                                    value="{{ $branchRegion->id }}">
                                                                <select name="region[{{ $k_branchRegion }}][region_id]"
                                                                    id="region[{{ $k_branchRegion }}][region_id]"
                                                                    class="form-control select2" required>
                                                                    <option value="{{ $branchRegion->region_id }}"
                                                                        selected>
                                                                        {{ $branchRegion->regions->name_site }}</option>
                                                                </select>
                                                            </td>
                                                            <td><input type="number"
                                                                    name="region[{{ $k_branchRegion }}][delivery_fees]"
                                                                    value="{{ $branchRegion->delivery_fees }}"
                                                                    min="0" class="form-control" required></td>
                                                            <td><button type="button"
                                                                    class="btn btn-danger btn-sm remove-row">@lang('branch.remove')</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <button type="button" id="add-branch-region"
                                            class="btn btn-success btn-sm">@lang('branch.AddRegion')</button>
                                    </div>

                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary" id="input-submit"
                                                value="@lang('category.save')">
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
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- INETRNAL SELECT2 JS -->
    @vite('resources/assets/js/select2.js')

    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')

    <script>
        $(document).ready(function() {
            $('.select2').select2();
            var phone = $('#phone');
            var countryLength = {{ $branch->country->length }};
            phone.attr('pattern', '\\d{' + countryLength + '}');
        });

        let branchTimeIndex = {{ $branch->branchTimes->count() }};
        $('#add-branch-time').on('click', function() {
            $('#branch-time-table').append(`
                <tr>
                    <td>
                    <input name="time[${branchTimeIndex}][id]" type="hidden" value="">
                    <select name="time[${branchTimeIndex}][day]" id="time[${branchTimeIndex}][day]" class="form-control select2" required>
                    <option value="6">@lang('branch.Saturday')</option>
                    <option value="0">@lang('branch.Sunday')</option>
                    <option value="1">@lang('branch.Monday')</option>
                    <option value="2">@lang('branch.Tuesday')</option>
                    <option value="3">@lang('branch.Wednesday')</option>
                    <option value="4">@lang('branch.Thursday')</option>
                    <option value="5">@lang('branch.Friday')</option>
                    </select></td>
                    <td><input type="time" name="time[${branchTimeIndex}][opening_hour]" class="form-control" required></td>
                    <td><input type="time" name="time[${branchTimeIndex}][closing_hour]" class="form-control" required></td>
                    <td>@lang('branch.24Hours')<input type="radio" name="time[${branchTimeIndex}][cross_day]" value="0" class="form-check-input" checked><br>
                    @lang('branch.12Hours')<input type="radio" name="time[${branchTimeIndex}][cross_day]" value="1" class="form-check-input"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('branch.remove')</button></td>
                </tr>
            `);
            branchTimeIndex++;
            $('.select2').select2();
        });

        // Remove Row
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        let branchRegionIndex = {{ $branch->branchRegions->count() }};
        console.log(branchRegionIndex);

        $('#add-branch-region').on('click', function() {
            var cityId = $('#city_id').val();
            console.log(cityId);

            if (cityId == null) {
                Swal.fire({
                    title: @json(__('validation.Alert')),
                    text: @json(__('branch.select_city_first_message')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: @json(__('validation.Ok')),
                    cancelButtonText: @json(__('validation.Cancel')),
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    return;
                });
                return;
            }

            var get_city_url = "{{ route('branch.showBranchRegion', ['id', 0]) }}";
            get_city_url = get_city_url.replace('id', cityId);

            $.get(get_city_url, function(regionDatas) {
                console.log("Full response:", regionDatas);

                let regions = regionDatas.branch_regions;

                if (!regions || regions.length === 0) {
                    Swal.fire({
                        title: @json(__('validation.Alert')),
                        text: @json(__('branch.no_regions_found_message')),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: @json(__('validation.Ok')),
                        cancelButtonText: @json(__('validation.Cancel')),
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                let options = "";
                $.each(regions, function(index, regionData) {
                    console.log("regionData:", regionData);
                    options += `<option value="${regionData.id}">${regionData.name_site}</option>`;
                });

                $('#branch-region-table').append(`
                        <tr>
                            <td>
                                <input name="region[${branchRegionIndex}][id]" type="hidden" value="">
                                <select name="region[${branchRegionIndex}][region_id]" class="form-control select2" required>
                                    ${options}
                                </select>
                            </td>
                            <td>
                                <input type="number" name="region[${branchRegionIndex}][delivery_fees]" min="0" class="form-control" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-region-row">@lang('branch.remove')</button>
                            </td>
                        </tr>
                    `);


            });



            branchRegionIndex++;
            $('.select2').select2();
        });

        // Remove Row
        $(document).on('click', '.remove-region-row', function() {
            $(this).closest('tr').remove();
        });

        $(document).on('change', '#country_id', function() {
            let phone = $('#phone');
            phone.val(""); // clear the phone input

            let countryId = $(this).val();
            let get_url = "{{ route('country.shows', 'id') }}".replace('id', countryId);
            let get_city_url = "{{ route('city.show_all', 'id') }}".replace('id', countryId);

            // Update phone pattern based on country
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    let country_phone_length = data['length'];
                    phone.attr('pattern', '\\d{' + country_phone_length + '}');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });

            // Fetch cities as JSON and populate <select>
            $.ajax({
                url: get_city_url,
                type: 'GET',
                dataType: 'json',
                success: function(cities) {
                    let $citySelect = $("#city_id");
                    let $regionSelect = $("#region_id");

                    $citySelect.empty().append(
                        '<option value="" disabled selected>@lang('country.ChooseCity')</option>');
                    $regionSelect.empty().append(
                        '<option value="" disabled selected>@lang('country.ChooseRegion')</option>');

                    $.each(cities, function(index, city) {
                        $citySelect.append(
                            `<option value="${city.id}">${city.name_ar} | ${city.name_en}</option>`
                            );
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error fetching cities: ' + error);
                }
            });
        });

        $(document).on('change', '#city_id', function() {
            let city_id = $(this).val();
            let get_region_url = "{{ route('region.show_all', 'id') }}".replace('id', city_id);

            $.ajax({
                url: get_region_url,
                type: 'GET',
                dataType: 'json',
                success: function(regions) {
                    let $regionSelect = $("#region_id");
                    $regionSelect.empty().append(
                        '<option value="" disabled selected>@lang('country.ChooseRegion')</option>');

                    $.each(regions, function(index, region) {
                        $regionSelect.append(
                            `<option value="${region.id}">${region.name_ar} | ${region.name_en}</option>`
                            );
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error fetching regions: ' + error);
                }
            });

            $('#branch-region-table tr').remove(); // optional table clearing
        });


        $(document).on('change', '#tax_application', function() {
            var tax_application = $('#tax_application').val();
            var tax_percentage = $('#tax_percentage');
            if (tax_application > 0) {
                tax_percentage.attr('min', '1');
            } else {
                tax_percentage.attr('min', '0');
                tax_percentage.val(0);
            }
        });

        $(document).on('change', '.is_default_no', function() {
            var is_default = $('input[name="is_default"]:checked').val();
            if (is_default == 0) {
                Swal.fire({
                    title: @json(__('validation.Alert')),
                    text: @json(__('branch.delete_default_message')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: @json(__('validation.Ok')),
                    cancelButtonText: @json(__('validation.Cancel')),
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var default_yes = $('#default_yes').prop('checked', true);
                    } else {
                        var default_yes = $('#default_yes').prop('checked', true);
                    }
                });
            }

        });
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDgewbk6uYuyvCImG5r5wl0wRuDVaQrFg8"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Use old() values if validation failed; fallback to default values from $address
            var defaultLatitude = {{ old('latitude', $branch->latitute ?? 30.064810617604635) }};
            var defaultLongitude = {{ old('longitude', $branch->longitute ?? 31.22420217605614) }};
            var isEdit = false;
            // var locationButton = document.getElementById('locationButton');

            if (isEdit) {
                locationButton.disabled = false;
                document.getElementById('latitude').value = defaultLatitude;
                document.getElementById('longitude').value = defaultLongitude;
            }

            // Initialize map with default latitude and longitude
            const map = new google.maps.Map(document.getElementById("map"), {
                center: {
                    lat: defaultLatitude,
                    lng: defaultLongitude
                },
                zoom: 13,
            });

            // Create draggable marker
            var marker = new google.maps.Marker({
                position: {
                    lat: defaultLatitude,
                    lng: defaultLongitude
                },
                map: map,
                draggable: true
            });

            // Update inputs and enable button on marker drag
            google.maps.event.addListener(marker, 'dragend', function(event) {
                var position = marker.getPosition();
                document.getElementById('latitude').value = position.lat();
                document.getElementById('longitude').value = position.lng();
                // locationButton.disabled = false;
            });

            // Update inputs and marker position on map click
            google.maps.event.addListener(map, 'click', function(event) {
                var lat = event.latLng.lat();
                var lng = event.latLng.lng();

                marker.setPosition(event.latLng);
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                //locationButton.disabled = false;
                console.log(lat);

            });

            // Search functionality using Google Geocoder
            window.searchLocation = function() {
                var query = document.getElementById('searchBox').value;
                if (!query) {
                    var modal = new bootstrap.Modal(document.getElementById('notfoundddressModal'));
                    modal.show();
                    return;
                }

                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    'address': query
                }, function(results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        var lat = results[0].geometry.location.lat();
                        var lng = results[0].geometry.location.lng();

                        map.setCenter(results[0].geometry.location);
                        marker.setPosition(results[0].geometry.location);
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                        // locationButton.disabled = false;

                        marker.setTitle(results[0].formatted_address);
                        marker.setMap(map);
                        marker.setIcon("https://maps.google.com/mapfiles/ms/icons/blue-dot.png");
                    } else {
                        var modal = new bootstrap.Modal(document.getElementById('notfoundddressModal'));
                        modal.show();
                        console.error('Geocode was not successful for the following reason: ' + status);
                    }
                });
            };
        });
    </script>
@endsection
