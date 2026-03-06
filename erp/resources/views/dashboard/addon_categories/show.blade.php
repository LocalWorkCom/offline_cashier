@extends('layouts.master')

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('addon_categories.AddonCategoryDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.addon_categories.index') }}">@lang('addon_categories.AddonCategories')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('addon_categories.AddonCategoryDetails')</li>
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
                            <div class="card-title">@lang('addon_categories.AddonCategoryDetails')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6">
                                    <label class="form-label">@lang('addon_categories.NameArabic')</label>
                                    <p class="form-text">{{ $addonCategory->name_ar }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('addon_categories.NameEnglish')</label>
                                    <p class="form-text">{{ $addonCategory->name_en }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('addon_categories.DescriptionArabic')</label>
                                    <p class="form-text">{{ $addonCategory->description_ar ?? __('addon_categories.NoDescription') }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('addon_categories.DescriptionEnglish')</label>
                                    <p class="form-text">{{ $addonCategory->description_en ?? __('addon_categories.NoDescription') }}</p>
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