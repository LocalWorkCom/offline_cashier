@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('brand.ShowBrand')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('brands.list') }}'">@lang('brand.Brands')</a>

                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('brand.show', ['id' => $id]) }}">@lang('brand.ShowBrand')</a>
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
                            <div class="card-title">@lang('brand.ShowBrand')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('brand.ArabicName')</label>
                                    <p class="form-text">{{ $brand->name_ar }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('brand.EnglishName')</label>
                                    <p class="form-text">{{ $brand->name_en }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('brand.ArabicDesc')</label>
                                    <p class="form-text">{{ $brand->description_ar == null ? __('brand.none') :  $brand->description_ar}}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('brand.EnglishDesc')</label>
                                    <p class="form-text">{{ $brand->description_en == null ? __('brand.none') :  $brand->description_en}}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('brand.Remind')</label>
                                    <p class="form-text">
                                        {{ $brand->is_active ? __('brand.yes') : __('brand.no') }}
                                    </p>
                                </div>
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('brand.Image')</label>
                                    @if ($brand->logo_path)
                                        <div class="mb-3">
                                            <img src="{{ asset($brand->logo_path) }}" alt="Brand Image" width="150" height="150">
                                        </div>
                                    @else
                                        <p class="form-text">@lang('brand.none')</p>
                                    @endif
                                </div>
                            </div>
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
@endsection
