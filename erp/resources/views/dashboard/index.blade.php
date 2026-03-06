@extends('layouts.master')

@section('styles')
<!-- JSVECTORMAP CSS -->
<link rel="stylesheet" href="{{ asset('build/assets/libs/jsvectormap/css/jsvectormap.min.css') }}">
@endsection

@section('content')
<!-- PAGE HEADER -->
<div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
    <h4 class="fw-medium mb-0">{{ auth('admin')->user()->name }} {{ __('home.Hello') }}</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">@lang('home.Dashboards')</a>
        </li>
        <!-- <li class="breadcrumb-item active" aria-current="page">Ecommerce</li> -->
    </ol>
</div>
<!-- END PAGE HEADER -->

<!-- APP CONTENT -->
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Start::row-1 -->
        <div class="row">
            <div class="col-xxl-12">
                <div class="card custom-card">
                    <div class="card-body p-0">
                        <div class="row row-cols-sm-2 row-cols-xxl-5 g-0 ecommerce-cards">
                            <div class="col d-flex p-4 tx-white pos-relative">
                                <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                <div class="me-3 my-auto">
                                    <div class="avatar avatar-lg bg-secondary-transparent radius-5"><i
                                            class="ti ti-package fs-20"></i></div>
                                </div>
                                <div class="flex-1">

                                    <p class="mb-0 text-muted">@lang('home.Total Products')</p>
                                    <div class="">

                                        <span class="text-xl fw-semibold">{{ \App\Models\Product::count() }}</span>
                                        <!-- <span class="ms-2 fs-13 text-secondary"><i
                                                    class="fe fe-arrow-up-right me-1 d-inline-block fs-12"></i>1.31%</span> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col d-flex p-4 tx-white pos-relative">
                                <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                <div class="me-3 my-auto">
                                    <div class="avatar avatar-lg bg-pink-transparent radius-5"><i
                                            class="ti ti-packge-import fs-20"></i></div>
                                </div>
                                <div class="flex-1">
                                    <p class="mb-0 text-muted">@lang('home.Total Orders')</p>
                                    <div class="">
                                        <span class="text-xl fw-semibold">{{ \App\Models\Order::count() }}</span>
                                        <!-- <span class="ms-2 fs-13 text-pink"><i
                                                    class="fe fe-arrow-up-right me-1 d-inline-block fs-12"></i>12.05%</span> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col d-flex p-4 tx-white pos-relative">
                                <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                <div class="me-3 my-auto">
                                    <div class="avatar avatar-lg bg-info-transparent radius-5"><i class="ti ti-tools-kitchen fs-20"></i></div>
                                </div>
                                <div class="flex-1">
                                    <p class="mb-0 text-muted">@lang('home.Total Dishes')</p>
                                    <div class="">
                                        <span class="text-xl fw-semibold">{{ \App\Models\Dish::count() }}</span>
                                        <!-- <span class="ms-2 fs-13 text-info"><i
                                                    class="fe fe-arrow-down-right fs-12 me-1 d-inlineblock"></i>1.14%</span> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col d-flex p-4 tx-white pos-relative">
                                <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                <div class="me-3 my-auto">
                                    <div class="avatar avatar-lg bg-orange-transparent radius-5"><i class="ti ti-grid-dots fs-20"></i></div>
                                </div>
                                <div class="flex-1">
                                    <p class="mb-0 text-muted">@lang('home.Total Category')</p>
                                    <div class="">
                                        <span class="text-xl fw-semibold">{{ \App\Models\Category::count() }}</span>
                                        <!-- <span class="ms-2 fs-13 text-orange"><i
                                                    class="fe fe-arrow-up-right me-1 d-inline-block fs-12 "></i>2.58%</span> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col d-flex p-4 tx-white pos-relative">
                                <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                <div class="me-3 my-auto">
                                    <div class="avatar avatar-lg bg-success-transparent radius-5"><i class="ti ti-building-store fs-20"></i></div>
                                </div>
                                <div class="flex-1">
                                    <p class="mb-0 text-muted">@lang('home.All Branches')</p>
                                    <div class="">
                                        <span class="text-xl fw-semibold">{{ \App\Models\Branch::count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-12">
                <div class="row">
                    <!-- <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">Earnings</div>
                                    <div class="dropdown">
                                        <a aria-label="anchor" href="javascript:void(0);"
                                            class="btn btn-outline-light btn-icons btn-sm text-muted"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>
                                        <ul class="dropdown-menu" role="menu">
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">Today</a></li>
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">This Week</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row justify-content-center">
                                        <div class="col-xl-4 col-md-4  m-1 m-md-0">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <span
                                                    class="avatar avatar-md br-5 bg-primary-transparent text-primary me-2"><i
                                                        class="fe fe-arrow-up"></i></span>
                                                <div class="">
                                                    <h5 class="mb-0">180.32k</h5>
                                                    <p class="mb-0 tx-muted">Total Orders </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-md-4  m-1 m-md-0">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <span
                                                    class="avatar avatar-md br-5 bg-secondary-transparent text-secondary me-2"><i
                                                        class="fe fe-arrow-down"></i></span>
                                                <div class="">
                                                    <h5 class="mb-0">743.32k</h5>
                                                    <p class="mb-0 tx-muted">Total Sales </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="projectstatistics"></div>
                                </div>
                            </div>
                        </div> -->
                    <div class="col-xl-12">
                        <div class="card custom-card overflow-hidden">
                            <div class="card-header justify-content-between">
                                <div class="card-title">
                                    @lang('home.Top Branches By Orders')
                                </div>
                                <!-- <div class="dropdown">
                                        <a href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted"
                                            data-bs-toggle="dropdown" aria-expanded="true">
                                            View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                        </a>
                                        <ul class="dropdown-menu" role="menu">
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">Today</a></li>
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">This Week</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                        </ul>
                                    </div> -->
                                <?php
                                $topbranches = \App\Models\Order::select('branch_id', Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                                    ->with(['branch', 'branch.country'])
                                    ->groupBy('branch_id')
                                    ->orderByDesc('total')
                                    ->take(10)
                                    ->get();
                                $totalOrders = \App\Models\Order::count();
                                ?>
                            </div>
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card-body">
                                        <div id="country-sales"></div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card-body">
                                        @foreach ($topbranches as $topbranch)
                                        @php
                                        $percentage = $totalOrders > 0 ? round(($topbranch->total / $totalOrders) * 100, 2) : 0;
                                        @endphp
                                        <div class="">
                                            <div class="d-flex mb-1">
                                                <p class="mb-0">{{ $topbranch->branch->name }} - {{ $topbranch->branch->country->name }}</p>
                                                <span class="ms-auto">{{ $percentage }}%</span>
                                            </div>
                                            <div class="progress progress-xs mb-3" role="progressbar"
                                                aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <!-- <div class="mt-4">
                                                <div class="d-flex mb-1">
                                                    <p class="mb-0">India</p>
                                                    <span class="ms-auto">55%</span>
                                                </div>
                                                <div class="progress progress-xs mb-3" role="progressbar"
                                                    aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-secondary" style="width: 55%">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <div class="d-flex mb-1">
                                                    <p class="mb-0">Vatican City</p>
                                                    <span class="ms-auto">60%</span>
                                                </div>
                                                <div class="progress progress-xs mb-3" role="progressbar"
                                                    aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-info" style="width: 60%">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <div class="d-flex mb-1">
                                                    <p class="mb-0">Palau</p>
                                                    <span class="ms-auto">80%</span>
                                                </div>
                                                <div class="progress progress-xs mb-3" role="progressbar"
                                                    aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-warning" style="width: 80%">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <div class="d-flex mb-1">
                                                    <p class="mb-0">Sao Tome and Principe</p>
                                                    <span class="ms-auto">45%</span>
                                                </div>
                                                <div class="progress progress-xs" role="progressbar" aria-valuenow="25"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar bg-success" style="width: 45%">
                                                    </div>
                                                </div>
                                            </div> -->
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-12">
                <div class="row">
                    <!-- <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">
                                        Invoice
                                    </div>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted"
                                            data-bs-toggle="dropdown" aria-expanded="true">
                                            View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                        </a>
                                        <ul class="dropdown-menu" role="menu">
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">Today</a></li>
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">This Week</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-3">
                                            <a href="{{ url('invoice-details') }}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-top justify-content-center">
                                                        <div class="me-2">
                                                            <span class="avatar bg-primary-transparent rounded-circle"><i
                                                                    class="ti ti-building-skyscraper fs-18"></i></span>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fw-semibold">Hedges Corp Ed</p>
                                                            <p class="mb-0 text-muted fs-12">30 Invoices</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="badge bg-success-transparent badge-sm rounded-pill min-w-fit-content ms-1">Paid</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ url('invoice-details') }}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-top justify-content-center">
                                                        <div class="me-2">
                                                            <span class="avatar"><img
                                                                    src="{{ asset('build/assets/images/faces/4.jpg') }}"
                                                                    alt="img" class="rounded-circle"></span>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fw-semibold">Charlie Beth</p>
                                                            <p class="mb-0 text-muted fs-12">11 Invoices</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="badge bg-warning-transparent badge-sm rounded-pill min-w-fit-content ms-1">Unpaid</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ url('invoice-details') }}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-top justify-content-center">
                                                        <div class="me-2">
                                                            <span
                                                                class="avatar bg-secondary-transparent  rounded-circle"><i
                                                                    class="ti ti-user fs-18 "></i></span>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fw-semibold">Nikson Bey</p>
                                                            <p class="mb-0 text-muted fs-12">21 Invoices</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="badge bg-success-transparent badge-sm rounded-pill min-w-fit-content ms-1">Paid</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ url('invoice-details') }}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-top justify-content-center">
                                                        <div class="me-2">
                                                            <span class="avatar"><img
                                                                    src="{{ asset('build/assets/images/faces/2.jpg') }}"
                                                                    alt="img" class="rounded-circle"></span>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fw-semibold">Malbi Inf Et.</p>
                                                            <p class="mb-0 text-muted fs-12">07 Invoices</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="badge bg-success-transparent badge-sm rounded-pill min-w-fit-content ms-1">Paid</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="mb-3">
                                            <a href="{{ url('invoice-details') }}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-top justify-content-center">
                                                        <div class="me-2">
                                                            <span class="avatar"><img
                                                                    src="{{ asset('build/assets/images/faces/8.jpg') }}"
                                                                    alt="img" class="rounded-circle"></span>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fw-semibold">Hedges Corp Ed.</p>
                                                            <p class="mb-0 text-muted fs-12">37 Invoices</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="badge bg-danger-transparent badge-sm rounded-pill min-w-fit-content ms-1">Overdue</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="{{ url('invoice-details') }}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-top justify-content-center">
                                                        <div class="me-2">
                                                            <span class="avatar"><img
                                                                    src="{{ asset('build/assets/images/faces/10.jpg') }}"
                                                                    alt="img" class="rounded-circle"></span>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fw-semibold">Bickle Bob</p>
                                                            <p class="mb-0 text-muted fs-12">313 Invoices</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="badge bg-secondary-transparent badge-sm rounded-pill min-w-fit-content ms-1">Unpaid</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div> -->
                    <!-- <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6">
                            <div class="card custom-card overflow-hidden">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">
                                        Sales By Category
                                    </div>
                                    <div class="dropdown">
                                        <a aria-label="anchor" href="javascript:void(0);"
                                            class="btn btn-outline-light btn-icons btn-sm text-muted"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>
                                        <ul class="dropdown-menu" role="menu">
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">Today</a></li>
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">This Week</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="salesDonut"></div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="row row-cols-12 border-top border-block-start-dashed">
                                        <div class="col p-0">
                                            <div class="ps-4 py-3 pe-3 text-center border-end border-inline-end-dashed">
                                                <span
                                                    class="text-muted fs-12 mb-1 crm-lead-legend mobile d-inline-block">Electronics
                                                </span>
                                                <div><span class="text-md fw-semibold">7,724</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col p-0">
                                            <div class="p-3 text-center border-end border-inline-end-dashed">
                                                <span
                                                    class="text-muted fs-12 mb-1 crm-lead-legend desktop d-inline-block">Women's
                                                </span>
                                                <div><span class="text-md fw-semibold">4,987</span></div>
                                            </div>
                                        </div>
                                        <div class="col p-0">
                                            <div class="p-3 text-center border-end border-inline-end-dashed">
                                                <span
                                                    class="text-muted fs-12 mb-1 crm-lead-legend laptop d-inline-block">Men's
                                                </span>
                                                <div><span class="text-md fw-semibold">8,093</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col p-0">
                                            <div class="p-3 text-center">
                                                <span
                                                    class="text-muted fs-12 mb-1 crm-lead-legend tablet d-inline-block">Kid's
                                                </span>
                                                <div><span class="text-md fw-semibold">979</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    <div class="col-xl-12">
                        <div class="card custom-card overflow-hidden">
                            <div class="card-header justify-content-between">
                                <div class="card-title">@lang('home.Top 5 Selling Dishes')</div>
                                <!-- <div class="dropdown">
                                        <a aria-label="anchor" href="javascript:void(0);"
                                            class="btn btn-outline-light btn-icons btn-sm text-muted"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>
                                        <ul class="dropdown-menu" role="menu">
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">Today</a></li>
                                            <li class="border-bottom"><a class="dropdown-item"
                                                    href="javascript:void(0);">This Week</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                        </ul>
                                    </div> -->
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-center">@lang('home.S.no')</th>
                                                <th scope="col">@lang('home.Dish')</th>
                                                <th scope="col">@lang('home.Total Orders')</th>
                                            </tr>
                                        </thead>
                                        <?php
                                        $count = 1;
                                        $topDishes = \App\Models\OrderDetail::select('dish_id', Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
                                            ->with('dish')
                                            ->groupBy('dish_id')
                                            ->orderByDesc('total')
                                            ->take(5)
                                            ->get();
                                        ?>
                                        <tbody class="top-selling">
                                            @foreach ($topDishes as $dish)
                                            <tr>
                                                <td class="text-center lh-1">
                                                    {{$count++}}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <span class="avatar avatar-md">
                                                            <img src="{{ asset($dish->dish->image ) }}"
                                                                class="rounded-1" alt="">
                                                        </span>
                                                        <div class="flex-1 ms-2">
                                                            <p class="mb-0">{{ $dish->dish->name }}</p>
                                                            <a href="javascript:void(0);"
                                                                class="text-primary ">{{ $dish->dish->code }}</a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold">{{ $dish->total }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End::row-1 -->

        <!-- Start:: row-2 -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            @lang('home.Order Summary')
                        </div>
                        <!-- <div class="d-sm-flex">
                                <div class="me-3 mb-2 mb-sm-0">
                                    <input class="form-control form-control-sm" type="text" placeholder="Search Here"
                                        aria-label=" example">
                                </div>
                                <div class="dropdown">
                                    <a href="javascript:void(0);"
                                        class="btn btn-sm btn-primary btn-wave waves-effect waves-light"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Sort By<i class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                    </a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a class="dropdown-item" href="javascript:void(0);">New</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);">Popular</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);">Relevant</a></li>
                                    </ul>
                                </div>
                            </div> -->
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="orderTable" class="table text-nowrap table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('home.Order Number')</th>
                                        <th scope="col">@lang('home.Transaction Id')</th>
                                        <th scope="col">@lang('home.Total Price after Tax')</th>
                                        <th scope="col">@lang('home.Date')</th>
                                        <th scope="col">@lang('home.Type')</th>
                                        <th scope="col">@lang('home.Status')</th>
                                    </tr>
                                </thead>
                                <?php
                                $count = 1;
                                $orders = \App\Models\Order::with(['orderDetails.dish', 'orderTransactions'])->get();
                                ?>
                                <tbody id="orderTableBody">
                                    @foreach ($orders as $order)
                                    <tr class="order-row">
                                        <!-- <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2 lh-1">
                                                            <span class="avatar avatar-sm">
                                                                <img src="{{ asset('build/assets/images/ecommerce/jpg/2.jpg') }}"
                                                                    alt="" class="rounded-1">
                                                            </span>
                                                        </div>
                                                        <div class="fs-14">Black colored Headset</div>
                                                    </div>
                                                </td> -->
                                        <td>
                                            <span class="fw-semibold">{{ $order->order_number }}</span>
                                        </td>
                                        <td>
                                            {{ $order->orderTransactions->first()->transaction_id ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $order->total_price_after_tax }}
                                        </td>
                                        <td>
                                            <span class="">{{ $order->date }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-transparent">{{ $order->type }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-transparent">{{ $order->status }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex align-items-center">
                            <div>
                                @lang('home.Showing 5 Entries') <i class="bi bi-arrow-right ms-2 fw-semibold"></i>
                            </div>
                            <div class="ms-auto">
                                <nav aria-label="Page navigation" class="pagination-style-4">
                                    <ul class="pagination mb-0">
                                        <li class="page-item">
                                            <a class="page-link" href="javascript:void(0);" id="prevBtn">Prev</a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link text-primary" href="javascript:void(0);" id="nextBtn">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End:: row-2 -->

    </div>
</div>
<!-- END APP CONTENT -->
@endsection

@section('scripts')
<!-- JSVECTOR MAPS JS -->
<script src="{{ asset('build/assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
<script src="{{ asset('build/assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

<!-- APEX CHARTS JS -->
<script src="{{ asset('build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

<!-- ECOMMERCE DASHBOARD JS -->
@vite('resources/assets/js/ecommerce-dashboard.js')
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rows = document.querySelectorAll(".order-row");
        const rowsPerPage = 5;
        let currentPage = 1;
        const totalPages = Math.ceil(rows.length / rowsPerPage);

        function showPage(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? "" : "none";
            });

            // Disable prev/next based on current page
            document.getElementById("prevBtn").parentElement.classList.toggle("disabled", page === 1);
            document.getElementById("nextBtn").parentElement.classList.toggle("disabled", page === totalPages);
        }

        document.getElementById("prevBtn").addEventListener("click", function() {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });

        document.getElementById("nextBtn").addEventListener("click", function() {
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });

        // Initial page load
        showPage(currentPage);


    });
</script>
