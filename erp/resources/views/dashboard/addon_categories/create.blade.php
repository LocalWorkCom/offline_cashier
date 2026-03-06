@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('addon_categories.AddAddonCategory')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.addon_categories.index') }}">@lang('addon_categories.AddonCategories')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('addon_categories.AddAddonCategory')</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('addon_categories.AddAddonCategory')</div>
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

                            <form method="POST" action="{{ route('dashboard.addon_categories.store') }}" class="needs-validation" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_ar" class="form-label">@lang('addon_categories.NameArabic')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ old('name_ar') }}" placeholder="@lang('addon_categories.NameArabic')" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('addon_categories.EnterNameArabic')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_en" class="form-label">@lang('addon_categories.NameEnglish')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en') }}" placeholder="@lang('addon_categories.NameEnglish')" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('addon_categories.EnterNameEnglish')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_ar" class="form-label">@lang('addon_categories.DescriptionArabic')</label>
                                        <textarea class="form-control" id="description_ar" name="description_ar" rows="4">{{ old('description_ar') }}</textarea>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_en" class="form-label">@lang('addon_categories.DescriptionEnglish')</label>
                                        <textarea class="form-control" id="description_en" name="description_en" rows="4">{{ old('description_en') }}</textarea>
                                    </div>

                                    <div class="col-xl-4">
                                        <input type="submit" class="form-control btn btn-primary" value="@lang('addon_categories.Save')">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row -->
        </div>
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- SELECT2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>
@endsection
