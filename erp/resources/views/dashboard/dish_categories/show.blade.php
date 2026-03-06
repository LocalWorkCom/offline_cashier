@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('dishes.ShowCategory')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.dish-categories.index') }}">@lang('dishes.DishCategories')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('dishes.ShowCategory')</li>
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
                            <div class="card-title">@lang('dishes.ShowCategory')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('dishes.NameArabic')</label>
                                    <p class="form-text">{{ $category->name_ar }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('dishes.NameEnglish')</label>
                                    <p class="form-text">{{ $category->name_en }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('dishes.DescriptionArabic')</label>
                                    <p class="form-text">{{ $category->description_ar ?? __('dishes.None') }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('dishes.DescriptionEnglish')</label>
                                    <p class="form-text">{{ $category->description_en ?? __('dishes.None') }}</p>
                                </div>
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('dishes.Image')</label>
                                    @if ($category->image_path)
                                        <div class="mb-3">
                                            <img src="{{ asset($category->image_path) }}" alt="@lang('dishes.Image')" width="150" height="150">
                                        </div>
                                    @else
                                        <p class="form-text">@lang('dishes.None')</p>
                                    @endif
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('dishes.ParentCategory')</label>
                                    <p class="form-text">
                                        {{ $category->parent ? $category->parent->name_ar . ' | ' . $category->parent->name_en : __('dishes.None') }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('dishes.IsActive')</label>
                                    <p class="form-text">
                                        {{ $category->is_active ? __('dishes.Yes') : __('dishes.No') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('dashboard.dish-categories.index') }}" class="btn btn-secondary">
                                @lang('dishes.Back')
                            </a>
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
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
