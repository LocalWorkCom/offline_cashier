@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('socialMediaInformationSetting.ShowSocialMediaInformationSetting')</h4>
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
                            onclick="window.location.href='{{ route('social_media_information_setting.list') }}'">
                            @lang('socialMediaInformationSetting.ShowSocialMediaInformationSetting')
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
                            <div class="card-title">@lang('socialMediaInformationSetting.ShowSocialMediaInformationSetting')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('socialMediaInformationSetting.android_app_link')</label>
                                    <p class="form-text"><a href="{{ $socialMediaInformationSetting->android_app_link ?? '#' }}"
                                            target="_blank">{{ $socialMediaInformationSetting->android_app_link ?? __('companyProfile.none') }}</a>
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('socialMediaInformationSetting.ios_app_link')</label>
                                    <p class="form-text"><a href="{{ $socialMediaInformationSetting->ios_app_link ?? '#' }}"
                                            target="_blank">{{ $socialMediaInformationSetting->ios_app_link ?? __('companyProfile.none') }}</a>
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('socialMediaInformationSetting.facebook')</label>
                                    <p class="form-text"><a href="{{ $socialMediaInformationSetting->facebook ?? '#' }}"
                                            target="_blank">{{ $socialMediaInformationSetting->facebook ?? __('companyProfile.none') }}</a>
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('socialMediaInformationSetting.instagram')</label>
                                    <p class="form-text"><a href="{{ $socialMediaInformationSetting->instagram ?? '#' }}"
                                            target="_blank">{{ $socialMediaInformationSetting->instagram ?? __('companyProfile.none') }}</a>
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('socialMediaInformationSetting.snapchat')</label>
                                    <p class="form-text"><a href="{{ $socialMediaInformationSetting->snapchat ?? '#' }}"
                                            target="_blank">{{ $socialMediaInformationSetting->snapchat ?? __('companyProfile.none') }}</a>
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('socialMediaInformationSetting.twitter')</label>
                                    <p class="form-text"><a href="{{ $socialMediaInformationSetting->twitter ?? '#' }}"
                                            target="_blank">{{ $socialMediaInformationSetting->twitter ?? __('companyProfile.none') }}</a>
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('socialMediaInformationSetting.tiktok')</label>
                                    <p class="form-text"><a href="{{ $socialMediaInformationSetting->tiktok ?? '#' }}"
                                            target="_blank">{{ $socialMediaInformationSetting->tiktok ?? __('companyProfile.none') }}</a>
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('socialMediaInformationSetting.company')</label>
                                    <p class="form-text">
                                        {{ app()->getLocale() == 'ar' ? $socialMediaInformationSetting->companyProfileSetting->name_ar : $socialMediaInformationSetting->companyProfileSetting->name_en  ??  __('category.none')}}
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
