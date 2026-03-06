@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('branch.AddBranch')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('branches.list') }}">@lang('branch.Branches')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch.AddBranch')</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid ">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('branch.AddBranch')
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

                            <form method="POST" action="{{ route('branch.store') }}" class="needs-validation" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <!-- Arabic Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.ArabicName')</label>
                                        <input type="text" class="form-control" name="name_ar"
                                            value="{{ old('name_ar') }}" placeholder="@lang('branch.ArabicName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicName')
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.EnglishName')</label>
                                        <input type="text" class="form-control" name="name_en"
                                            value="{{ old('name_en') }}" placeholder="@lang('branch.EnglishName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishName')
                                        </div>
                                    </div>

                                    <!-- Address Arabic -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.ArabicAddress')</label>
                                        <textarea class="form-control" id="searchBox" onblur="searchLocation()" name="address_ar" required rows="2">{{ old('address_ar') }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicAddress')
                                        </div>
                                    </div>

                                    <!-- Address English -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.EnglishAddress')</label>
                                        <textarea class="form-control" name="address_en" required rows="2">{{ old('address_en') }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishAddress')
                                        </div>
                                    </div>

                                    <!-- Code -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.Code')</label>
                                        <textarea class="form-control" name="code" required rows="2">{{ old('code') }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterBranchCode')
                                        </div>
                                    </div>

                                    <!-- Street -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.Street')</label>
                                        <textarea class="form-control" name="street" required rows="2">{{ old('street') }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterStreet')
                                        </div>
                                    </div>

                                    <!-- BuildingNumber -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.BuildingNumber')</label>
                                        <input type="number" class="form-control" name="buildingNumber" id="buildingNumber"
                                            required value="{{ old('buildingNumber') }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterBuildingNumber')
                                        </div>
                                    </div>

                                    <!-- AutoCloseChat -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.AutoCloseChat')</label>
                                        <input type="number" class="form-control" name="auto_close_chat"
                                            id="auto_close_chat" required value="{{ old('auto_close_chat') }}"
                                            maxlength="20">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterAutoCloseChat')
                                        </div>
                                    </div>

                                    <!-- Tax Apply -->
                                    <!-- <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                                        <label class="form-label">@lang('branch.TaxApply')</label>
                                                        <select class="form-control" name="tax_apply" required>
                                                            <option value="0" {{ old('tax_apply') == 0 ? 'selected' : '' }}>
                                                                @lang('branch.TaxNotApplied')</option>
                                                            <option value="1" {{ old('tax_apply') == 1 ? 'selected' : '' }}>
                                                                @lang('branch.TaxApplied')</option>
                                                        </select>
                                                    </div> -->

                                    <!-- Tax Application -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.TaxApplication')</label>
                                        <select class="form-control" name="tax_application" id="tax_application" required>
                                            <option value="0" {{ old('tax_application') == 0 ? 'selected' : '' }}>
                                                @lang('branch.TaxNotIncluded')</option>
                                            <option value="1" {{ old('tax_application') == 1 ? 'selected' : '' }}>
                                                @lang('branch.TaxIncluded')</option>
                                        </select>
                                    </div>

                                    <!-- Tax Percentage -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.TaxPercentage')</label>
                                        <input type="number" class="form-control" name="tax_percentage"
                                            id="tax_percentage" value="{{ old('tax_percentage') }}" min="0"
                                            step="0.01" required>
                                    </div>

                                    <!-- Coupon Application -->
                                    <input type="hidden" name="coupon_application" value="0">
                                    <!-- <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.CouponApplication')</label>
                                        <select class="form-control" name="coupon_application" required>
                                            <option value="0" {{ old('coupon_application') == 0 ? 'selected' : '' }}>
                                                @lang('branch.BeforeTax')</option>
                                            <option value="1" {{ old('coupon_application') == 1 ? 'selected' : '' }}>
                                                @lang('branch.AfterTax')</option>
                                        </select>
                                    </div> -->

                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.ServicesFeesType')</label>
                                        <select class="form-control" name="service_fees_type" id="service_fees_type"
                                            required>
                                            <option value="fixed"
                                                {{ old('ServicesFeesType') === 'fixed' ? 'selected' : '' }}>
                                                @lang('branch.fixed')</option>
                                            <option value="percentage"
                                                {{ old('ServicesFeesType') === 'percentage' ? 'selected' : '' }}>
                                                @lang('branch.percentage')</option>
                                        </select>
                                    </div>

                                    <!-- Services Fees -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.ServicesFees')</label>
                                        <input type="number" class="form-control" name="service_fees"
                                            value="{{ old('service_fees') }}" step="0.01"min="0" required>
                                    </div>


                                    <!-- delivery_fees -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.delivery_fees')</label>
                                        <input type="number" class="form-control" name="delivery_fees"
                                            value="{{ old('delivery_fees') }}" step="0.01" min="0" required>
                                    </div>

                                    <!-- Time Cancellation -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.TimeCancellation')</label>
                                        <input type="number" class="form-control" name="time_cancellation"
                                            value="{{ old('time_cancellation') }}" min="1" required>
                                    </div>

                                    <!-- Delivery Time -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.DeliveryTime')</label>
                                        <input type="number" class="form-control" name="delivery_time"
                                            value="{{ old('delivery_time') }}" min="1" required>
                                    </div>


                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <div class="map position-relative my-3">
                                            <div class="map col-xl-12 col-lg-12 col-md-12 col-sm-12" id="map"
                                                width="auto" height="450" style="border:0;"></div>
                                        </div>

                                        <input type="hidden" id="latitude" name="latitute" value="30.053802144287577">
                                        <input type="hidden" id="longitude" name="longitute" value="31.23096827116421">
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
                                            <option value="0" disabled selected>@lang('branch.SelectCountry')</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}">
                                                    {{ $country->name_ar . ' | ' . $country->name_en }}</option>
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
                                        </select>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCity')
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('country.ChooseRegion')</label>
                                        <select class="form-control select2" name="region_id" id="region_id" required>
                                            <option value="" disabled selected>@lang('country.ChooseRegion')</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterState')
                                        </div>
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.Phone')</label>
                                        <input type="tel" class="form-control phone-valid" name="phone"
                                            id="phone" required value="{{ old('phone') }}" minlength="5"
                                            maxlength="20" title="@lang('validation.EnterPhoneDigits')">

                                        <div class="invalid-feedback">
                                            @lang('validation.EnterPhone')
                                        </div>
                                        <small class="form-text text-muted phone-length-hint"></small>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.Email')</label>
                                        <input type="email" class="form-control" name="email"
                                            value="{{ old('email') }}">
                                    </div>

                                    <!-- Manager Name -->
                                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.ManagerName')</label>
                                        <select class="form-control select2" name="employee_id" id="employee_id"
                                            required>
                                            <option value="" disabled selected>@lang('branch.SelectEmployee')</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}">
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
                                                <option value="{{ $company_profile_setting->id }}">
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
                                                value="1" checked>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_kids_area"
                                                value="0">
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- Is Delivery -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsDelivery')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_delivery"
                                                value="1" checked>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_delivery"
                                                value="0">
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- Is default -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsDefault')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_default"
                                                value="1" checked>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_default"
                                                value="0">
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- Is default -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsActive')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_active"
                                                value="1" checked>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_active"
                                                value="0">
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- IsTableReservation -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsTableReservation')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_table_reservation"
                                                value="1" checked>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_table_reservation"
                                                value="0">
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- IsTakeaway -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsTakeaway')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_takeaway"
                                                value="1" checked>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_takeaway"
                                                value="0">
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    @if (auth('admin')->user()->hasPermissionTo('handle branch is_live', 'admin'))
                                    <!-- IsLive -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsLive')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_live" value="1"
                                                checked>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_live"
                                                value="0">
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
                                            <tbody id="branch-time-table"></tbody>
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
                                            <tbody id="branch-region-table"></tbody>
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
            <!-- End:: row-1 -->
        </div>
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
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

            // Repopulate branch times if validation failed
            @if (session('old_times'))
                @foreach (session('old_times') as $index => $time)
                    $('#branch-time-table').append(`
                    <tr>
                        <td><select name="time[{{ $index }}][day]" class="form-control select2" required>
                            <option value="6" {{ $time['day'] == 6 ? 'selected' : '' }}>@lang('branch.Saturday')</option>
                            <option value="0" {{ $time['day'] == 0 ? 'selected' : '' }}>@lang('branch.Sunday')</option>
                            <option value="1" {{ $time['day'] == 1 ? 'selected' : '' }}>@lang('branch.Monday')</option>
                            <option value="2" {{ $time['day'] == 2 ? 'selected' : '' }}>@lang('branch.Tuesday')</option>
                            <option value="3" {{ $time['day'] == 3 ? 'selected' : '' }}>@lang('branch.Wednesday')</option>
                            <option value="4" {{ $time['day'] == 4 ? 'selected' : '' }}>@lang('branch.Thursday')</option>
                            <option value="5" {{ $time['day'] == 5 ? 'selected' : '' }}>@lang('branch.Friday')</option>
                        </select></td>
                        <td><input type="time" name="time[{{ $index }}][opening_hour]" class="form-control" value="{{ $time['opening_hour'] }}" required></td>
                        <td><input type="time" name="time[{{ $index }}][closing_hour]" class="form-control" value="{{ $time['closing_hour'] }}" required></td>
                        <td>
                            @lang('branch.24Hours')<input type="radio" name="time[{{ $index }}][cross_day]" value="0" class="form-check-input" {{ ($time['cross_day'] ?? 0) == 0 ? 'checked' : '' }}><br>
                            @lang('branch.12Hours')<input type="radio" name="time[{{ $index }}][cross_day]" value="1" class="form-check-input" {{ ($time['cross_day'] ?? 0) == 1 ? 'checked' : '' }}>
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('branch.remove')</button></td>
                    </tr>
                `);
                    branchTimeIndex = {{ $index }} + 1;
                @endforeach
            @endif

            // Repopulate branch regions if validation failed
            @if (session('old_regions'))
                @foreach (session('old_regions') as $index => $region)
                    var cityId = '{{ old('city_id') }}';
                    if (cityId) {
                        var get_city_url = "{{ route('branch.showBranchRegion', ['id', 0]) }}".replace('id',
                            cityId);

                        $.get(get_city_url, function(regionDatas) {
                            if (regionDatas && Object.keys(regionDatas).length > 0) {
                                let options = "";
                                $.each(regionDatas, function(index, regionData) {
                                    var selected = '{{ $region['region_id'] }}' == regionData.id ?
                                        'selected' : '';
                                    options +=
                                        `<option value="${regionData.id}" ${selected}>${regionData.name_site}</option>`;
                                });

                                $('#branch-region-table').append(`
                                <tr>
                                    <td>
                                        <select name="region[{{ $index }}][region_id]" class="form-control select2" required>
                                            ${options}
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="region[{{ $index }}][delivery_fees]" min="0" class="form-control" value="{{ $region['delivery_fees'] }}" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-region-row">@lang('branch.remove')</button>
                                    </td>
                                </tr>
                            `);
                                $('.select2').select2();
                                branchRegionIndex = {{ $index }} + 1;
                            }
                        });
                    }
                @endforeach
            @endif
        });

        let branchTimeIndex = 0;

        $('#add-branch-time').on('click', function() {
            let selectedDays = [];
            $('.day-select').each(function() {
                let selectedValue = $(this).val();
                if (selectedValue !== '' && selectedValue !== null) {
                    selectedDays.push(selectedValue);
                }
            });

            const days = [
                { value: "6", label: "@lang('branch.Saturday')" },
                { value: "0", label: "@lang('branch.Sunday')" },
                { value: "1", label: "@lang('branch.Monday')" },
                { value: "2", label: "@lang('branch.Tuesday')" },
                { value: "3", label: "@lang('branch.Wednesday')" },
                { value: "4", label: "@lang('branch.Thursday')" },
                { value: "5", label: "@lang('branch.Friday')" }
            ];

            let dayOptions = '';
            days.forEach(day => {
                let isDisabled = selectedDays.includes(day.value) ? 'disabled' : '';
                dayOptions += `<option value="${day.value}" ${isDisabled}>${day.label}</option>`;
            });

            $('#branch-time-table').append(`
                <tr>
                    <td><select name="time[${branchTimeIndex}][day]" class="form-control select2 day-select" required>
                        ${dayOptions}
                    </select></td>
                    <td><input type="time" name="time[${branchTimeIndex}][opening_hour]" class="form-control" required></td>
                    <td><input type="time" name="time[${branchTimeIndex}][closing_hour]" class="form-control" required></td>
                    <td>
                        @lang('branch.24Hours')<input type="radio" name="time[${branchTimeIndex}][cross_day]" value="0" class="form-check-input" checked><br>
                        @lang('branch.12Hours')<input type="radio" name="time[${branchTimeIndex}][cross_day]" value="1" class="form-check-input">
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('branch.remove')</button></td>
                </tr>
            `);

            branchTimeIndex++;
            $('.select2').select2();

            updateDayOptions();
        });

        function updateDayOptions() {
            let selectedDays = [];
            $('.day-select').each(function() {
                let selectedValue = $(this).val();
                if (selectedValue !== '' && selectedValue !== null) {
                    selectedDays.push(selectedValue);
                }
            });

            $('.day-select').each(function() {
                let currentSelect = $(this);
                let currentValue = currentSelect.val();
                currentSelect.find('option').prop('disabled', false);
                selectedDays.forEach(function(day) {
                    if (currentValue !== day) {
                        currentSelect.find(`option[value="${day}"]`).prop('disabled', true);
                    }
                });
                currentSelect.select2();
            });
        }

        $(document).on('change', '.day-select', function() {
            updateDayOptions();
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            updateDayOptions();
        });

        let branchRegionIndex = 0;
        $('#add-branch-region').on('click', function() {
            var cityId = $('#city_id').val();
            if (!cityId) {
                Swal.fire({
                    title: @json(__('validation.Alert')),
                    text: @json(__('branch.select_city_first_message')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: @json(__('validation.Ok')),
                    cancelButtonText: @json(__('validation.Cancel')),
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            var get_city_url = "{{ route('branch.showBranchRegion', ['id', 0]) }}".replace('id', cityId);

            $.get(get_city_url, function(regionDatas) {
                if (!regionDatas || Object.keys(regionDatas).length === 0) {
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

                // Extract branch_regions and branch_region_ids
                let branchRegions = Array.isArray(regionDatas) ? regionDatas : Object.values(regionDatas['branch_regions']);
                let branchRegionIds = regionDatas['branch_region_ids'] ? Object.values(regionDatas['branch_region_ids']) : [];

                let options = '<option value="" disabled selected>@lang("branch.SelectRegion")</option>';
                $.each(branchRegions, function(index, regionData) {
                    options += `<option value="${regionData.id}">${regionData.name_site}</option>`;
                });

                let rowHtml = `
                    <tr>
                        <td>
                            <select name="region[${branchRegionIndex}][region_id]" class="form-control select2" required>
                                ${options}
                            </select>
                            <small class="text-danger warning-message" style="display: none;">هذه المنطقه مستخدمه من فرع اخر وسوف يتم مسحها من الفرع عند الحفظ</small>
                        </td>
                        <td>
                            <input type="number" name="region[${branchRegionIndex}][delivery_fees]" min="0" class="form-control" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-region-row">@lang('branch.remove')</button>
                        </td>
                    </tr>
                `;

                // Append the row to the table
                let $row = $(rowHtml);
                $('#branch-region-table').append($row);

                // Initialize select2 on the select element
                $row.find('.select2').select2();

                // Add change event listener to the select element
                $row.find('select[name="region[' + branchRegionIndex + '][region_id]"]').on('change', function() {
                    let selectedId = parseInt($(this).val());
                    let $warningMessage = $(this).siblings('.warning-message');

                    // Show or hide the warning message based on whether the selected ID is in branchRegionIds
                    if (branchRegionIds.includes(selectedId)) {
                        $warningMessage.show();
                    } else {
                        $warningMessage.hide();
                    }
                });

                $('.select2').select2();
                branchRegionIndex++;
            });
        });


        // Initialize select2 for the region_id select
        $('#region_id').select2();

        // Handle change event for region_id select
        $('#region_id').on('change', function() {
            let selectedRegionId = $(this).val();
            if (selectedRegionId == 0) {
                Swal.fire({
                    title: @json(__('validation.Alert')),
                    text: @json(__('branch.select_region_first_message')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: @json(__('validation.Ok')),
                    cancelButtonText: @json(__('validation.Cancel')),
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Get the selected region's text (name_site) and city_id from the select option
            let selectedRegionName = $('#region_id option:selected').text();
            let cityId = $('#region_id').data('city-id') || $('#city_id').val(); // Assume city_id is available

            if (!cityId) {
                Swal.fire({
                    title: @json(__('validation.Alert')),
                    text: @json(__('branch.select_city_first_message')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: @json(__('validation.Ok')),
                    cancelButtonText: @json(__('validation.Cancel')),
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Construct the URL for fetching regions
            var get_city_url = "{{ route('branch.showBranchRegion', ['id', 0]) }}".replace('id', cityId);

            $.get(get_city_url, function(regionDatas) {
                if (!regionDatas || Object.keys(regionDatas).length === 0) {
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

                // Extract branch_regions and branch_region_ids
                let branchRegions = Array.isArray(regionDatas) ? regionDatas : Object.values(regionDatas['branch_regions']);
                let branchRegionIds = regionDatas['branch_region_ids'] ? Object.values(regionDatas['branch_region_ids']) : [];

                // Create options for the select, with the selected region pre-selected
                let options = '<option value="" disabled>@lang("branch.SelectRegion")</option>';
                $.each(branchRegions, function(index, regionData) {
                    let selected = regionData.id == selectedRegionId ? 'selected' : '';
                    options += `<option value="${regionData.id}" ${selected}>${regionData.name_site}</option>`;
                });

                // Create the table row
                let rowHtml = `
                    <tr>
                        <td>
                            <select name="region[${branchRegionIndex}][region_id]" class="form-control select2" required>
                                ${options}
                            </select>
                            <small class="text-danger warning-message" style="display: none;">هذه المنطقه مستخدمه من فرع اخر وسوف يتم مسحها من الفرع عند الحفظ</small>
                        </td>
                        <td>
                            <input type="number" name="region[${branchRegionIndex}][delivery_fees]" min="0" class="form-control" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-region-row">@lang('branch.remove')</button>
                        </td>
                    </tr>
                `;

                // Append the row to the table
                let $row = $(rowHtml);
                $('#branch-region-table').append($row);

                // Initialize select2 on the new select element
                $row.find('.select2').select2();

                // Add change event listener to the new select element for warning message
                $row.find('select[name="region[' + branchRegionIndex + '][region_id]"]').on('change', function() {
                    let selectedId = parseInt($(this).val());
                    let $warningMessage = $(this).siblings('.warning-message');

                    // Show or hide the warning message based on whether the selected ID is in branchRegionIds
                    if (branchRegionIds.includes(selectedId)) {
                        $warningMessage.show();
                    } else {
                        $warningMessage.hide();
                    }
                });

                // Trigger the change event to show warning message if the pre-selected region is in branch_region_ids
                $row.find('select[name="region[' + branchRegionIndex + '][region_id]"]').trigger('change');

                // Increment branchRegionIndex for the next row
                branchRegionIndex++;
            });
        });

        // Remove Region Row
        $(document).on('click', '.remove-region-row', function() {
            $(this).closest('tr').remove();
        });

        // Updated Country Change Handler
        $(document).on('change', '#country_id', function() {
            var countryId = $(this).val();
            var $phoneInput = $('#phone');
            var $citySelect = $('#city_id');
            var $regionSelect = $('#region_id');

            // Reset dependent fields
            $citySelect.html('<option value="" disabled selected>@lang('country.ChooseCity')</option>');
            $regionSelect.html('<option value="0" disabled selected>@lang('country.ChooseRegion')</option>');
            $phoneInput.val('');
            $('.phone-length-hint').text('');

            if (!countryId) return;

            // Get phone length requirements
            var get_country_url = "{{ route('country.shows', ':id') }}".replace(':id', countryId);
            $.ajax({
                url: get_country_url,
                type: 'GET',
                success: function(countryData) {
                    if (countryData && countryData.length) {
                        $phoneInput.attr('pattern', '\\d{' + countryData.length + '}');
                        $phoneInput.attr('minlength', countryData.length);
                        $phoneInput.attr('maxlength', countryData.length);
                        $phoneInput.attr('title', '@lang('validation.PhoneMustBe') ' + countryData.length +
                            ' @lang('validation.digits')');
                        $('.phone-length-hint').text(
                            '@lang('validation.PhoneMustBe')'
                            .replace(':attribute', 'phone')
                            .replace(':digits', countryData.length)
                        );

                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching country data:', error);
                }
            });

            // Load Cities
            var get_city_url = "{{ route('city.show_all', ':id') }}".replace(':id', countryId);
            $.ajax({
                url: get_city_url,
                type: 'GET',
                success: function(response) {
                    if (response && response.length > 0) {
                        var options = '<option value="" disabled selected>@lang('country.ChooseCity')</option>';
                        $.each(response, function(index, city) {
                            options +=
                                `<option value="${city.id}">${city.name_ar} | ${city.name_en}</option>`;
                        });
                        $citySelect.html(options);
                    } else {
                        $citySelect.html('<option value="" disabled>@lang('branch.NoCitiesAvailable')</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching cities:', error);
                    $citySelect.html('<option value="" disabled>@lang('branch.ErrorLoadingCities')</option>');
                }
            });
        });

        // Load Regions on City Change
        $(document).on('change', '#city_id', function() {
            var cityId = $(this).val();
            var $regionSelect = $('#region_id');

            $regionSelect.html('<option value="" disabled>@lang('branch.Loading')...</option>');
            $regionSelect.prop('disabled', true);

            if (!cityId) return;

            var get_region_url = "{{ route('region.show_all', ':id') }}".replace(':id', cityId);
            $.ajax({
                url: get_region_url,
                type: 'GET',
                success: function(response) {
                    if (response && response.length > 0) {
                        var options =
                            '<option value="0" disabled selected>@lang('country.ChooseRegion')</option>';
                        $.each(response, function(index, region) {
                            var name_ar = region.name_ar || region.name_site || '';
                            var name_en = region.name_en || region.name_site || '';
                            options +=
                                `<option value="${region.id}">${name_ar} | ${name_en}</option>`;
                        });
                        $regionSelect.html(options).prop('disabled', false);
                    } else {
                        $regionSelect.html('<option value="" disabled>@lang('branch.NoRegionsAvailable')</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching regions:', error);
                    $regionSelect.html('<option value="" disabled>@lang('branch.ErrorLoadingRegions')</option>');
                }
            });
        });

        // Tax input logic
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
    </script>


        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDgewbk6uYuyvCImG5r5wl0wRuDVaQrFg8"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Use old() values if validation failed; fallback to default values from $address
            var defaultLatitude = {{ old('latitude', $address->latitude ?? 30.064810617604635) }};
            var defaultLongitude = {{ old('longitude', $address->longtitude ?? 31.22420217605614) }};
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
