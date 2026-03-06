@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('contactInformation.contact_information_setting')</h4>
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
                            onclick="window.location.href='{{ route('contact_information_setting.list') }}'">@lang('contactInformation.contact_information_setting')</a>
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
                                @lang('contactInformation.AddContactInformationSetting')
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
                            <form method="POST" action="{{ route('contact_information_setting.store', ['checkToken' => true]) }}" class="needs-validation" novalidate enctype="multipart/form-data">
                                @csrf
                                <div class="row gy-4">
                                    <!-- Company Address (Arabic) -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('contactInformation.CompanyAddressArabic')</label>
                                        <input type="text" class="form-control" name="company_address_ar" value="{{ old('company_address_ar') }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterCompanyAddressArabic')</div>
                                    </div>
                            
                                    <!-- Company Address (English) -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('contactInformation.CompanyAddressEnglish')</label>
                                        <input type="text" class="form-control" name="company_address_en" value="{{ old('company_address_en') }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterCompanyAddressEnglish')</div>
                                    </div>
                            
                                    <!-- Map Link (Optional) -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('contactInformation.MapLink')</label>
                                        <input type="url" class="form-control" name="map_link" value="{{ old('map_link') }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterMapLink')</div>

                                    </div>
                            
                                    <!-- Phone Number -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('contactInformation.PhoneNumber')</label>
                                        <input type="text" class="form-control" name="phone_number" value="{{ old('phone_number') }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterPhoneNumber')</div>
                                    </div>
                            
                                    <!-- Email -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('contactInformation.Email')</label>
                                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterEmail')</div>
                                    </div>
                            
                                    <!-- Website Link (Optional) -->
                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('contactInformation.WebsiteLink')</label>
                                        <input type="url" class="form-control" name="website_link" value="{{ old('website_link') }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterWebsiteLink')</div>
                                    </div>
                            
                                    <div class="col-xl-6">
                                        <label for="company_id" class="form-label">@lang('contactInformation.company_id')</label>
                                        <select name="company_id" id="company_id" class="form-control" required>
                                            <option value="" selected disabled>@lang('contactInformation.ChooseBrand')</option>
                                            @foreach ($ContactInformationSetting as $ContactInformationSetting)
                                                <option value="{{ $ContactInformationSetting->id }}">
                                                    {{ app()->getLocale() == 'ar' ? $ContactInformationSetting->name_ar : $ContactInformationSetting->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.SelectCompany')</div>
                                    </div>
                                    

                                    <!-- Company ID (Hidden) -->
                                    {{-- <input type="hidden" name="company_id" value="{{ $companyProfileSetting->id ?? old('company_id') }}"> --}}
                            
                                    <center>
                                        <div class="col-xl-4">
                                            <input type="submit" class="form-control btn btn-primary" value="@lang('companyProfile.Save')">
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
        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>
@endsection
