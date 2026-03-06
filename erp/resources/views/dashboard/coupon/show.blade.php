@extends('layouts.master')

@section('styles')
<!-- SELECT2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
    <h4 class="fw-medium mb-0">@lang('coupon.ShowCoupon')</h4>
    <div class="ms-sm-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="javascript:void(0);" onclick="window.location.href='{{ route('coupons.list') }}'">
                        @lang('coupon.Coupons')
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <a href="{{ route('coupon.show', ['id' => $id]) }}">@lang('coupon.ShowCoupon')</a>
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
                        <div class="card-title">@lang('coupon.ShowCoupon')</div>
                    </div>
                    <div class="card-body">

                        <div class="row gy-4">
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label class="form-label">@lang('coupon.title_ar')</label>
                                <p class="form-text">{{ $coupon->title_ar }}</p>
                            </div>
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label class="form-label">@lang('coupon.title_en')</label>
                                <p class="form-text">{{ $coupon->title_en }}</p>
                            </div>
                            <!-- Coupon Code -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label class="form-label">@lang('coupon.Code')</label>
                                <p class="form-text">{{ $coupon->code }}</p>
                            </div>

                            <!-- Discount Value -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label class="form-label">@lang('coupon.Value')</label>
                                <p class="form-text">{{ $coupon->value }}</p>
                            </div>

                            <!-- Minimum Spend -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label class="form-label">@lang('coupon.MinimumSpend')</label>
                                <p class="form-text">{{ $coupon->minimum_spend ?? __('category.none') }}</p>
                            </div>

                            <!-- Usage Limit -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label class="form-label">@lang('coupon.UsageLimit')</label>
                                <p class="form-text">{{ $coupon->usage_limit ?? __('category.none') }}</p>
                            </div>

                            <!-- Start Date -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label class="form-label">@lang('coupon.StartDate')</label>
                                <p class="form-text">{{ $coupon->start_date ? \Carbon\Carbon::parse($coupon->start_date)->format('Y-m-d H:i') : __('category.none') }}</p>
                            </div>

                            <!-- End Date -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label class="form-label">@lang('coupon.EndDate')</label>
                                <p class="form-text">{{ $coupon->end_date ? \Carbon\Carbon::parse($coupon->end_date)->format('Y-m-d H:i') : __('category.none') }}</p>
                            </div>

                            <!-- Discount Type -->
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                <label class="form-label">@lang('coupon.Type')</label>
                                <p class="form-text">
                                    {{ $coupon->type == 'percentage' ? __('coupon.Percentage') : __('coupon.Fixed') }}
                                </p>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                <label class="form-label">@lang('coupon.apply_type')</label>
                                <p class="form-text">
                                    {{ $coupon->apply_type == 'dish' ? __('coupon.Dish') : __('coupon.Order') }}
                                </p>
                            </div>
                            <!-- Is Active -->
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                <label class="form-label">@lang('coupon.IsActive')</label>
                                <p class="form-text">{{ $coupon->is_active ? __('coupon.Active') : __('coupon.Inactive') }}</p>
                            </div>

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <label class="form-label">@lang('coupon.Branches')</label>
                                <ul class="list-unstyled">
                                    @forelse ($coupon->branches as $branch)
                                    <li>{{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}</li> <!-- Assuming the Branch model has a 'name' field -->
                                    @empty
                                    <li>@lang('coupon.NoBranches')</li>
                                    @endforelse
                                </ul>
                            </div>
                            @if($coupon->apply_type == 'dish' && $coupon->branches->isNotEmpty())
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <label class="form-label">@lang('coupon.Dish')</label>
                                <ul class="list-unstyled">
                                    @forelse ($coupon->branches as $branch)
                                    @php
                                    $dishIds = json_decode($branch->pivot->dish_ids, true) ?? [];
                                    @endphp

                                    @if (count($dishIds))
                                    @foreach ($dishIds as $dishId)
                                    @php
                                    $dish = \App\Models\Dish::find($dishId);
                                    @endphp
                                    @if ($dish)
                                    <li>{{ app()->getLocale() == 'en' ? $dish->name_en : $dish->name_ar }}</li>
                                    @endif
                                    @endforeach
                                    @else
                                    <li>@lang('coupon.NoDishes')</li>
                                    @endif
                                    @empty
                                    <li>@lang('coupon.NoBranches')</li>
                                    @endforelse

                                </ul>
                            </div>
                            @endif
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