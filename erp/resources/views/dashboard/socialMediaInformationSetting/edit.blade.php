@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('socialMediaInformationSetting.social_media_information_setting')</h4>
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
                            onclick="window.location.href='{{ route('social_media_information_setting.list') }}'">@lang('socialMediaInformationSetting.social_media_information_setting')</a>
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
                                @lang('socialMediaInformationSetting.EditSocialMediaInformationSetting')
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
                                action="{{ route('social_media_information_setting.update', ['id' => $socialMediaInformationSetting->id, 'checkToken' => 'true']) }}"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="row gy-4">
                                    <!-- Website Link -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('socialMediaInformationSetting.android_app_link')</label>
                                        <input type="text" class="form-control" name="android_app_link"
                                            value="{{ old('android_app_link', $socialMediaInformationSetting->android_app_link) }}"
                                            placeholder="@lang('socialMediaInformationSetting.android_app_link')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterAndroidAppLink')
                                        </div>
                                    </div>

                                    <!-- Map Link -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('socialMediaInformationSetting.ios_app_link')</label>
                                        <input type="text" class="form-control" name="ios_app_link"
                                            value="{{ old('ios_app_link', $socialMediaInformationSetting->ios_app_link) }}"
                                            placeholder="@lang('socialMediaInformationSetting.ios_app_link')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.IosAppLink')
                                        </div>
                                    </div>

                                    <!-- Map Link -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('socialMediaInformationSetting.facebook')</label>
                                        <input type="text" class="form-control" name="facebook"
                                            value="{{ old('facebook', $socialMediaInformationSetting->facebook) }}"
                                            placeholder="@lang('socialMediaInformationSetting.facebook')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterFacebook')
                                        </div>
                                    </div>
                                    <!-- Map Link -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('socialMediaInformationSetting.instagram')</label>
                                        <input type="text" class="form-control" name="instagram"
                                            value="{{ old('instagram', $socialMediaInformationSetting->instagram) }}"
                                            placeholder="@lang('socialMediaInformationSetting.instagram')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterInstagram')
                                        </div>
                                    </div>
                                    <!-- Map Link -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('socialMediaInformationSetting.snapchat')</label>
                                        <input type="text" class="form-control" name="snapchat"
                                            value="{{ old('snapchat', $socialMediaInformationSetting->snapchat) }}"
                                            placeholder="@lang('socialMediaInformationSetting.snapchat')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterSnapchat')
                                        </div>
                                    </div>
                                    <!-- Map Link -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('socialMediaInformationSetting.twitter')</label>
                                        <input type="text" class="form-control" name="twitter"
                                            value="{{ old('twitter', $socialMediaInformationSetting->twitter) }}"
                                            placeholder="@lang('socialMediaInformationSetting.twitter')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterTwitter')
                                        </div>
                                    </div>
                                    <!-- Map Link -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('socialMediaInformationSetting.tiktok')</label>
                                        <input type="text" class="form-control" name="tiktok"
                                            value="{{ old('tiktok', $socialMediaInformationSetting->tiktok) }}"
                                            placeholder="@lang('socialMediaInformationSetting.tiktok')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterTiktok')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="company_id" class="form-label">@lang('socialMediaInformationSetting.company')</label>
                                        <select class="form-control" name="company_id" id="choices-single-default" required>
                                            <option value="">{{ __('socialMediaInformationSetting.none') }}</option>
                                            @foreach ($companyProfileSetting as $profile)
                                                <option value="{{ $profile->id }}"
                                                    {{ old('company_id', $socialMediaInformationSetting->company_id ?? '') == $profile->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'ar' ? $profile->name_ar : $profile->name_en }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="invalid-feedback">@lang('validation.SelectCompany')</div>
                                    </div>

                                    <!-- Company ID (Hidden) -->
                                    {{-- <input type="hidden" name="company_id" value="{{ $companyProfileSetting->id }}"> --}}

                                    <!-- Submit Button -->
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
