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
            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('companyProfile.AddCompanyProfileSetting')
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
                                action="{{ route('company_profile_setting.store', ['checkToken' => true]) }}"
                                class="needs-validation" novalidate enctype="multipart/form-data">
                                @csrf
                                <div class="row gy-4">
                                    <!-- Arabic Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.ArabicName')</label>
                                        <input type="text" class="form-control" name="name_ar"
                                            value="{{ old('name_ar') }}" placeholder="@lang('companyProfile.ArabicName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicName')
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.EnglishName')</label>
                                        <input type="text" class="form-control" name="name_en"
                                            value="{{ old('name_en') }}" placeholder="@lang('companyProfile.EnglishName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishName')
                                        </div>
                                    </div>
                                    <!-- description_ar -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.ArabicDesc')</label>
                                        <input type="text" class="form-control" name="description_ar"
                                            value="{{ old('description_ar') }}" placeholder="@lang('companyProfile.ArabicDesc')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicDesc')
                                        </div>
                                    </div>

                                    <!-- description_en -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.EnglishDesc')</label>
                                        <input type="text" class="form-control" name="description_en"
                                            value="{{ old('description_en') }}" placeholder="@lang('companyProfile.EnglishDesc')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishDesc')
                                        </div>
                                    </div>

                                    <!-- Business Activity -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="business_activity" class="form-label">@lang('companyProfile.business_activity')</label>
                                        <select name="business_activity" id="business_activity" class="form-control"
                                            required>
                                            <option value="" selected disabled>@lang('companyProfile.business_activity')</option>
                                            @foreach ($companyProfileSetting as $profile)
                                            <option value="{{ $profile->id }}">
                                                {{ app()->getLocale() === 'ar' ? $profile->name_ar : $profile->name_en }}
                                            </option>
                                        @endforeach
                                        
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterBusinessActivity')</div>
                                    </div>

                                    <!-- Trade License -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.trade_license')</label>
                                        <input type="text" class="form-control" name="trade_license"
                                            value="{{ old('trade_license') }}" placeholder="@lang('companyProfile.trade_license')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterTradeLicense')
                                        </div>
                                    </div>

                                    <!-- License Expiry Date -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.license_expiry_date')</label>
                                        <input type="date" class="form-control" name="license_expiry_date"
                                            value="{{ old('license_expiry_date') }}" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterLicenseExpiryDate')
                                        </div>
                                    </div>

                                    <!-- Tax Registration Number -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.tax_registration_number')</label>
                                        <input type="text" class="form-control" name="tax_registration_number"
                                            value="{{ old('tax_registration_number') }}" placeholder="@lang('companyProfile.tax_registration_number')"
                                            required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterTaxRegistrationNumber')
                                        </div>
                                    </div>

                                    <!-- Capital -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('companyProfile.capital')</label>
                                        <input type="text" class="form-control" name="capital"
                                            value="{{ old('capital') }}" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCapital')
                                        </div>
                                    </div>

                                    <!-- Scanned Trade License -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="scanned_trade_license" class="form-label">@lang('companyProfile.scanned_trade_license')</label>
                                        <input type="file" name="scanned_trade_license" class="form-control"
                                            accept=".pdf,.jpg,.png"/>
                                        {{-- <div class="invalid-feedback">@lang('validation.scanned_trade_license')</div> --}}

                                    </div>

                                    <!-- Logo -->
                                    <div class="col-xl-4">
                                        <label for="logo" class="form-label">@lang('companyProfile.logo')</label>
                                        <input type="file" name="logo" id="logo" class="form-control"
                                            required>
                                        <div class="invalid-feedback">@lang('companyProfile.EnterImage')</div>
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
