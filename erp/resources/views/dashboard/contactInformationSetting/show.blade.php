@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('contactInformation.ShowCompanyProfileSetting')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('contact_information_setting.list') }}'">
                            @lang('contactInformation.ShowCompanyProfileSetting')
                        </a>
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
                            <div class="card-title">@lang('contactInformation.ShowCompanyProfileSetting')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('contactInformation.CompanyAddress')</label>
                                    <p class="form-text">{{ $contactInformationSetting->company_address ?? __('companyProfile.none') }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('contactInformation.MapLink')</label>
                                    <p class="form-text"><a href="{{ $contactInformationSetting->map_link ?? '#' }}" target="_blank">{{ $contactInformationSetting->map_link ?? __('companyProfile.none') }}</a></p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('contactInformation.PhoneNumber')</label>
                                    <p class="form-text">{{ $contactInformationSetting->phone_number ?? __('companyProfile.none') }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('contactInformation.Email')</label>
                                    <p class="form-text">{{ $contactInformationSetting->email ?? __('companyProfile.none') }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('contactInformation.WebsiteLink')</label>
                                    <p class="form-text"><a href="{{ $contactInformationSetting->website_link ?? '#' }}" target="_blank">{{ $contactInformationSetting->website_link ?? __('companyProfile.none') }}</a></p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('contactInformation.company_id')</label>
                                    <p class="form-text">
                                        {{ app()->getLocale() == 'ar' ? $contactInformationSetting->companyProfileSetting->name_ar : $contactInformationSetting->companyProfileSetting->name_en  ??  __('category.none')}}
                                    </p>
                                </div>
                            </div>
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
@endsection
