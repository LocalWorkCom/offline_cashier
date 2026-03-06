@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('branch.ShowBranch')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('branches.list') }}">@lang('branch.Branches')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch.ShowBranch')</li>
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
                            <div class="card-title">@lang('branch.ShowBranch')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.ArabicName')</label>
                                    <p class="form-text">{{ $branch->name_ar }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.EnglishName')</label>
                                    <p class="form-text">{{ $branch->name_en }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.ArabicAddress')</label>
                                    <p class="form-text">
                                        {{ $branch->address_ar == null ? __('branch.NoData') : $branch->address_ar }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.EnglishAddress')</label>
                                    <p class="form-text">
                                        {{ $branch->address_en == null ? __('branch.NoData') : $branch->address_en }}</p>
                                </div>
                                <div class="row gy-4">

                                    <!-- Tax Apply -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.TaxApply')</label>
                                        <p class="form-text">
                                            {{ $branch->tax_apply == 0 ? __('branch.TaxNotApplied') : __('branch.TaxApplied') }}
                                        </p>
                                    </div>

                                    <!-- Tax Application -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.TaxApplication')</label>
                                        <p class="form-text">
                                            {{ $branch->tax_application == 0 ? __('branch.TaxNotIncluded') : __('branch.TaxIncluded') }}
                                        </p>
                                    </div>

                                    <!-- Tax Percentage -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.TaxPercentage')</label>
                                        <p class="form-text">
                                            {{ $branch->tax_percentage == null ? __('branch.NoData') : $branch->tax_percentage }}
                                        </p>
                                    </div>

                                    <!-- Coupon Application -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.CouponApplication')</label>
                                        <p class="form-text">
                                            {{ is_null($branch->coupon_application) ? __('branch.NoData') : ($branch->coupon_application == 0 || $branch->coupon_application == '0' ? __('branch.BeforeTax') : __('branch.AfterTax')) }}
                                        </p>
                                    </div>

                                    <!-- Services Fees -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.ServicesFees')</label>
                                        <p class="form-text">
                                            {{ $branch->service_fees == null ? __('branch.NoData') : $branch->service_fees }}
                                        </p>
                                    </div>

                                    <!-- delivery_fees -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.delivery_fees')</label>
                                        <p class="form-text">
                                            {{ $branch->delivery_fees == null ? __('branch.NoData') : $branch->delivery_fees }}
                                        </p>
                                    </div>

                                    <!-- Time Cancellation -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.TimeCancellation')</label>
                                        <p class="form-text">
                                            {{ $branch->time_cancellation == null ? __('branch.NoData') : $branch->time_cancellation }}
                                        </p>
                                    </div>

                                    <!-- Delivery Time -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.DeliveryTime')</label>
                                        <p class="form-text">
                                            {{ $branch->delivery_time == null ? __('branch.NoData') : $branch->delivery_time }}
                                        </p>
                                    </div>

                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.Latitude')</label>
                                    <p class="form-text">
                                        {{ $branch->latitute == null ? __('branch.NoData') : $branch->latitute }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.Longitude')</label>
                                    <p class="form-text">
                                        {{ $branch->longitute == null ? __('branch.NoData') : $branch->longitute }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.Country')</label>
                                    <p class="form-text">
                                        {{ $branch->country ? $branch->country->name_site : __('branch.NoData') }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.Phone')</label>
                                    <p class="form-text">
                                        {{ $branch->phone == null ? __('branch.NoData') : $branch->phone }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.Email')</label>
                                    <p class="form-text">
                                        {{ $branch->email == null ? __('branch.NoData') : $branch->email }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('branch.ManagerName')</label>
                                    <p class="form-text">
                                        {{ $branch->employess ? $branch->employess->first_name . ' ' . $branch->employess->last_name : __('branch.NoData') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.HasKidsArea')</label>
                                    <p class="form-text">
                                        {{ $branch->has_kids_area ? __('category.yes') : __('category.no') }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.IsDelivery')</label>
                                    <p class="form-text">
                                        {{ $branch->is_delivery ? __('category.yes') : __('category.no') }}
                                    </p>
                                </div>


                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.Code')</label>
                                    <p class="form-text">
                                        {{ $branch->code == null ? __('branch.NoData') : $branch->code }}
                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.Street')</label>
                                    <p class="form-text">
                                        {{ $branch->street == null ? __('branch.NoData') : $branch->street }}
                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.BuildingNumber')</label>
                                    <p class="form-text">
                                        {{ $branch->buildingNumber == null ? __('branch.NoData') : $branch->buildingNumber }}
                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.AutoCloseChat')</label>
                                    <p class="form-text">
                                        {{ $branch->auto_close_chat ?? __('branch.NoData') }}
                                        {{ $branch->auto_close_chat !== null ? __('branch.minutes') : '' }}
                                        {{-- minutes --}}
                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.ServicesFeesType')</label>
                                    <p class="form-text">
                                        {{ $branch->service_fees_type === 'fixed' ? __('branch.fixed') : __('branch.percentage') }}
                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.City')</label>
                                    <p class="form-text">
                                        {{ $branch->city ? $branch->city->name_ar . ' | ' . $branch->city->name_en : __('branch.NoData') }}

                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.Area')</label>
                                    <p class="form-text">
                                        {{ $branch->area ? $branch->area->name_ar . ' | ' . $branch->area->name_en : __('branch.NoData') }}

                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.IsDefault')</label>
                                    <p class="form-text">
                                        {{ $branch->is_default ? __('category.yes') : __('category.no') }}
                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.IsActive')</label>
                                    <p class="form-text">
                                        {{ $branch->is_active ? __('category.yes') : __('category.no') }}
                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.IsTakeaway')</label>
                                    <p class="form-text">
                                        {{ $branch->is_takeaway ? __('category.yes') : __('category.no') }}
                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.IsTableReservation')</label>
                                    <p class="form-text">
                                        {{ $branch->is_table_reservation ? __('category.yes') : __('category.no') }}
                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.CompanyProfile')</label>
                                    <p class="form-text">
                                        {{ $branch->company ? $branch->company->name_ar . ' | ' . $branch->company->name_en : __('branch.NoData') }}

                                    </p>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                    <label class="form-label">@lang('branch.Branch delivery area')</label>
                                    <p class="form-text">
                                        @forelse($branch->branchRegions as $branchRegion)
                                            {{ $branchRegion->regions->name_ar . ' | ' . $branchRegion->regions->name_en }}
                                            @if (!$loop->last)
                                                ,
                                            @endif
                                        @empty
                                            {{ __('branch.NoData') }}
                                        @endforelse
                                    </p>
                                </div>

                                @if ($branch->branchTimes)
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.Day')</label>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.OpeningHour')</label>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.ClosingHour')</label>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.CrossDay')</label>
                                    </div>

                                    @foreach ($branch->branchTimes as $branch_time)
                                        <p class="form-text col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                            {{ weekDay($branch_time->day, app()->getLocale()) }}
                                        </p>

                                        <p class="form-text col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                            {{ $branch_time->opening_hour }}
                                        </p>

                                        <p class="form-text col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                            {{ $branch_time->closing_hour }}
                                        </p>

                                        <p class="form-text col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                            {{ $branch_time->cross_day == 0 ? __('branch.24Hours') : __('branch.12Hours') }}
                                        </p>
                                    @endforeach
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
