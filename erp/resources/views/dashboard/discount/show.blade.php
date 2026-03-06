@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('discount.ShowDiscount')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('discounts.list') }}'">
                            @lang('discount.Discounts')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('discount.show', ['id' => $id]) }}">@lang('discount.ShowDiscount')</a>
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
                            <div class="card-title">@lang('discount.ShowDiscount')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <!-- Discount NameAr -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('discount.NameAr')</label>
                                    <p class="form-text">{{ $discount->name_ar }}</p>
                                </div>

                                <!-- Discount NameEn -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('discount.NameEn')</label>
                                    <p class="form-text">{{ $discount->name_en }}</p>
                                </div>

                                <!-- Discount Value -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('discount.Value')</label>
                                    <p class="form-text">{{ $discount->value }}</p>
                                </div>

                                <!-- Start Date -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('discount.StartDate')</label>
                                    <p class="form-text">{{ $discount->start_date ? \Carbon\Carbon::parse($discount->start_date)->format('Y-m-d H:i') : __('category.none') }}</p>
                                </div>

                                <!-- End Date -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('discount.EndDate')</label>
                                    <p class="form-text">{{ $discount->end_date ? \Carbon\Carbon::parse($discount->end_date)->format('Y-m-d H:i') : __('category.none') }}</p>
                                </div>

                                <!-- Discount Type -->
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('discount.Type')</label>
                                    <p class="form-text">
                                        {{ $discount->type == 'percentage' ? __('discount.Percentage') : __('discount.Fixed') }}
                                    </p>
                                </div>

                                <!-- Is Active -->
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('discount.IsActive')</label>
                                    <p class="form-text">{{ $discount->is_active ? __('discount.Active') : __('discount.Inactive') }}</p>
                                </div>

                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('discount.Branches')</label>
                                    <ul class="list-unstyled">
                                        @forelse ($discount->branches as $branch)
                                            <li>{{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}</li> <!-- Assuming the Branch model has a 'name' field -->
                                        @empty
                                            <li>@lang('discount.NoBranches')</li>
                                        @endforelse
                                    </ul>
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
