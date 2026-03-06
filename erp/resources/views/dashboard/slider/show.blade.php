@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('slider.ShowSlider')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('sliders.list') }}">@lang('slider.Sliders')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('slider.ShowSlider')</li>
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
                            <div class="card-title">@lang('slider.ShowSlider')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('slider.ArabicName')</label>
                                    <p class="form-text">{{ $slider->name_ar }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('slider.EnglishName')</label>
                                    <p class="form-text">{{ $slider->name_en }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('slider.ArabicDescription')</label>
                                    <p class="form-text">{{ $slider->description_ar }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('slider.EnglishDescription')</label>
                                    <p class="form-text">{{ $slider->description_en }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('slider.Dish')</label>
                                    <p class="form-text">
                                        {{ $slider->dish ? $slider->dish->name_ar . ' | ' . $slider->dish->name_en : __('slider.none') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('slider.Offer')</label>
                                    <p class="form-text">
                                        {{ $slider->offer ? $slider->offer->name_ar . ' | ' . $slider->offer->name_en : __('slider.none') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('slider.Discount')</label>
                                    <p class="form-text">
                                        {{ $slider->discount ? $slider->discount->dish->name_ar . " | " . $slider->discount->dish->name_en
                                        . " | ". number_format($slider->discount->discount->value, 0). ($slider->discount->discount->type == "percentage" ? "%" : "EGP")
                                        : __('slider.none') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('slider.Image')</label>
                                    <p class="form-text">
                                        <img src="{{ url($slider->image) }}" alt=""
                                             width="150" height="150">
                                    </p>
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
