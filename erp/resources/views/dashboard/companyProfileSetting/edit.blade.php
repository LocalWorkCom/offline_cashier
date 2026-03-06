@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('companyProfile.CompanyProfileSettings')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);"
                            onclick="window.location.href='{{ route('company_profile_setting.list') }}'">@lang('companyProfile.CompanyProfileSettings')</a>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('companyProfile.EditCompanyProfileSetting')
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
                            <form method="POST"
                                action="{{ route('company_profile_setting.update', ['id' => $companyProfileSetting->id, 'checkToken' => 'true']) }}"
                                class="needs-validation" novalidate enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <!-- Arabic Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.ArabicName')</label>
                                        <input type="text" class="form-control" name="name_ar"
                                            value="{{ old('name_ar', $companyProfileSetting->name_ar) }}"
                                            placeholder="@lang('companyProfile.ArabicName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicName')
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.EnglishName')</label>
                                        <input type="text" class="form-control" name="name_en"
                                            value="{{ old('name_en', $companyProfileSetting->name_en) }}"
                                            placeholder="@lang('companyProfile.EnglishName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishName')
                                        </div>
                                    </div>

                                    <!-- Arabic Description -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.ArabicDesc')</label>
                                        <textarea class="form-control" name="description_ar" rows="2" required>{{ old('description_ar', $companyProfileSetting->description_ar) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicDesc')
                                        </div>
                                    </div>

                                    <!-- English Description -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.EnglishDesc')</label>
                                        <textarea class="form-control" name="description_en" rows="2" required>{{ old('description_en', $companyProfileSetting->description_en) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishDesc')
                                        </div>
                                    </div>

                                    <!-- business_activity  -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="business_activity" class="form-label">@lang('companyProfile.business_activity')</label>
                                        <select class="form-control" name="business_activity" id="choices-single-default"
                                            required>
                                            <option value="">{{ __('category.none') }}</option>
                                            @foreach ($companyProfileSettings as $activity)
                                                <option value="{{ $activity->id }}"
                                                    {{ old('business_activity', $companyProfileSetting->business_activity ?? '') == $activity->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() === 'ar' ? $activity->name_ar : $activity->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterBusinessActivity')</div>
                                    </div>

                                    <!-- trade_license  -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.trade_license')</label>
                                        <input type="text" class="form-control" name="trade_license"
                                            value="{{ old('trade_license', $companyProfileSetting->trade_license) }}"
                                            placeholder="@lang('companyProfile.trade_license')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterTradeLicense')
                                        </div>
                                    </div>

                                    <!-- license_expiry_date  -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.license_expiry_date')</label>
                                        <input type="date" class="form-control" name="license_expiry_date"
                                            value="{{ old('license_expiry_date', $companyProfileSetting->license_expiry_date) }}"
                                            placeholder="@lang('companyProfile.license_expiry_date')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterLicenseExpiryDate')
                                        </div>
                                    </div>

                                    <!-- tax_registration_number  -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.tax_registration_number')</label>
                                        <input type="text" class="form-control" name="tax_registration_number"
                                            value="{{ old('tax_registration_number', $companyProfileSetting->tax_registration_number) }}"
                                            placeholder="@lang('companyProfile.tax_registration_number')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterTaxRegistrationNumber')
                                        </div>
                                    </div>

                                    <!-- capital  -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.capital')</label>
                                        <input type="text" class="form-control" name="capital"
                                            value="{{ old('capital', $companyProfileSetting->capital) }}"
                                            placeholder="@lang('companyProfile.capital')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCapital')
                                        </div>
                                    </div>


                                    <!-- scanned_trade_license File Input -->
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label for="input-file" class="form-label">@lang('companyProfile.scanned_trade_license')</label>

                                        <!-- Show the current scanned_trade_license if it exists -->
                                        @if ($companyProfileSetting->scanned_trade_license)
                                            <div class="mb-3">
                                                <img src="{{ asset($companyProfileSetting->scanned_trade_license) }}"
                                                    alt="companyProfileSetting scanned_trade_license" width="150"
                                                    height="150">
                                            </div>
                                            <!-- Hidden input to send the current scanned_trade_license -->
                                            <input type="hidden" name="scanned_trade_license"
                                                value="{{ $companyProfileSetting->scanned_trade_license }}">
                                        @endif

                                        <input class="form-control" type="file" id="scanned_trade_license"
                                            name="scanned_trade_license">
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.scanned_trade_license')
                                        </div>
                                    </div>


                                    <!-- logo File Input -->

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label for="input-file" class="form-label">@lang('companyProfile.logo')</label>

                                        <!-- Show the current logo if it exists -->
                                        @if ($companyProfileSetting->logo)
                                            <div class="mb-3">
                                                <img src="{{ asset($companyProfileSetting->logo) }}"
                                                    alt="companyProfileSetting logo" width="150" height="150">
                                            </div>
                                            <!-- Hidden input to send the current logo -->
                                            <input type="hidden" name="logo"
                                                value="{{ $companyProfileSetting->logo }}">
                                        @endif

                                        <input class="form-control" type="file" id="logo" name="logo">
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterImage')
                                        </div>
                                    </div>

                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary" id="input-submit"
                                                value="@lang('companyProfile.Save')">
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
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- INTERNAL SELECT2 JS -->
    @vite('resources/assets/js/select2.js')

    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection
