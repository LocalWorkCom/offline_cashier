@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('offer.ShowOffer')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('offers.list')}}">@lang('offer.Offers')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('offer.ShowOffer')</li>
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
                            <div class="card-title">@lang('offer.ShowOffer')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('offer.ArabicName')</label>
                                    <p class="form-text">{{ $offer->is_active == 0 ? __('category.no') : __('category.yes')  }}</p>
                                </div>
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('offer.Branches')</label>
                                    @if ($offer->branch_id == -1)
                                        <p class="form-text">@lang('offer.AllBranches')</p>
                                    @else
                                        @if ($branches && $branches->isNotEmpty())
                                            <ul class="form-text">
                                                @foreach ($branches as $branch)
                                                    <li>{{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="form-text">@lang('category.none')</p>
                                        @endif
                                    @endif
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.DiscountType')</label>
                                    <p class="form-text">
                                        {{ __('offer.' . $offer->discount_type) }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.DiscountValue')</label>
                                    <p class="form-text">{{ $offer->discount_value }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.ArabicName')</label>
                                    <p class="form-text">{{ $offer->name_ar }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.EnglishName')</label>
                                    <p class="form-text">{{ $offer->name_en }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.ArabicDescription')</label>
                                    <p class="form-text">{{ $offer->description_ar == null ? __('category.none') :  $offer->description_ar}}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.EnglishDescription')</label>
                                    <p class="form-text">{{ $offer->description_en == null ? __('category.none') :  $offer->description_en}}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.StartDate')</label>
                                    <p class="form-text">{{ $offer->start_date == null ? __('category.none') :  $offer->start_date}}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.EndDate')</label>
                                    <p class="form-text">{{ $offer->end_date == null ? __('category.none') :  $offer->end_date}}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.ImageAr')</label>
                                    @if($offer->image_ar)
                                        <div class="mb-2">
                                            <img src="{{ asset($offer->image_ar) }}" alt="Current Image" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('offer.ImageEn')</label>
                                    @if($offer->image_en)
                                        <div class="mb-2">
                                            <img src="{{ asset($offer->image_en) }}" alt="Current Image" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
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
