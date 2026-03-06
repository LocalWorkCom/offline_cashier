@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('businessActivity.EditBusinessActivity')</h4>
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
                            onclick="window.location.href='{{ route('business_activity.list') }}'">@lang('businessActivity.businessActivity')</a>
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
                                @lang('businessActivity.EditBusinessActivity')
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
                                action="{{ route('business_activity.update', ['id' => $businessActivity->id, 'checkToken' => 'true']) }}"
                                class="needs-validation" novalidate enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <!-- Arabic Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('businessActivity.ArabicName')</label>
                                        <input type="text" class="form-control" name="name_ar"
                                            value="{{ old('name_ar', $businessActivity->name_ar) }}"
                                            placeholder="@lang('companyProfile.ArabicName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicName')
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('businessActivity.EnglishName')</label>
                                        <input type="text" class="form-control" name="name_en"
                                            value="{{ old('name_en', $businessActivity->name_en) }}"
                                            placeholder="@lang('businessActivity.EnglishName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishName')
                                        </div>
                                    </div>

                                    <!-- Arabic Description -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('businessActivity.ArabicDesc')</label>
                                        <textarea class="form-control" name="description_ar" rows="2" required>{{ old('description_ar', $businessActivity->description_ar) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicDesc')
                                        </div>
                                    </div>

                                    <!-- English Description -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('businessActivity.EnglishDesc')</label>
                                        <textarea class="form-control" name="description_en" rows="2" required>{{ old('description_en', $businessActivity->description_en) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishDesc')
                                        </div>
                                    </div>

                                  
                                    <!-- Is Active -->
                                    <div class="col-xl-6">
                                        <p class="mb-2 text-muted">@lang('businessActivity.IsActive')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="is_active" value="1" class="form-check-input" {{ old('is_active', $businessActivity->is_active) ? 'checked' : '' }} required>
                                            <label class="form-check-label">@lang('businessActivity.Active')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="is_active" value="0" class="form-check-input" {{ !old('is_active', $businessActivity->is_active) ? 'checked' : '' }} required>
                                            <label class="form-check-label">@lang('businessActivity.Inactive')</label>
                                        </div>
                                    </div> 

                                    <!-- logo File Input -->

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label for="input-file" class="form-label">@lang('businessActivity.logo')</label>

                                        <!-- Show the current logo if it exists -->
                                        @if ($businessActivity->logo)
                                            <div class="mb-3">
                                                <img src="{{ asset($businessActivity->logo) }}"
                                                    alt="companyProfileSetting logo" width="150" height="150">
                                            </div>
                                            <!-- Hidden input to send the current logo -->
                                            <input type="hidden" name="logo"
                                                value="{{ $businessActivity->logo }}">
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
                                                value="@lang('businessActivity.Save')">
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
