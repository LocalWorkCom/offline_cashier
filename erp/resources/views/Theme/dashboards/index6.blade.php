@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
                        <h4 class="fw-medium mb-0">Analytics-Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">Dashboards</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Analytics</li>
                        </ol>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start::row-1 -->
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xxl-3  col-sm-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                        <div>
                                                            <h6 class="fw-semibold mb-1">Total Users</h6>
                                                            <h3 class="fw-semibold">9,789</h3>
                                                            <span class="d-block text-success fs-12">+0.892 <i class="ti ti-trending-up ms-1"></i></span>
                                                        </div>
                                                        <div id="analytics-users"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3  col-sm-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                        <div>
                                                            <h6 class="fw-semibold mb-1">Total visitors</h6>
                                                            <h3 class="fw-semibold">17,789</h3>
                                                            <span class="d-block text-success fs-12">+0.892 <i class="ti ti-trending-up ms-1"></i></span>
                                                        </div>
                                                        <div id="analytics-visitors"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3  col-sm-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                        <div>
                                                            <h6 class="fw-semibold mb-1">Bounce Rate</h6>
                                                            <h3 class="fw-semibold">70.09%</h3>
                                                            <span class="d-block text-success fs-12">+0.892 <i class="ti ti-trending-up ms-1"></i></span>
                                                        </div>
                                                        <div id="Bounce-Rate"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3  col-sm-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                        <div>
                                                            <h6 class="fw-semibold mb-1">Page Views</h6>
                                                            <h3 class="fw-semibold">19,789</h3>
                                                            <span class="d-block text-success fs-12">+0.892 <i class="ti ti-trending-up ms-1"></i></span>
                                                        </div>
                                                        <div id="Page-Views"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3  col-md-6 col-sm-12">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between flex-wrap">
                                                    <div class="card-title">
                                                        Sessions By Device
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div id="sessionsByDevice"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3  col-md-6 col-sm-12">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between flex-wrap">
                                                    <div class="card-title">
                                                        Activity
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="analytics-timeline">
                                                        <ul class="timeline-1 mb-0">
                                                            <li class="mt-0">
                                                                <i class="bx bx-bullseye analytics-icon bg-primary-transparent text-primary"></i>
                                                                <span class="fw-semibold mb-4 fs-14 ">Total Visits</span>
                                                                <a href="javascript:void(0);" class="float-end fs-12 text-muted">1 hour ago</a>
                                                                <p class="text-muted fs">15% increased</p>
                                                            </li>
                                                            <li class="mt-0">
                                                                <i class="bx bx-cube-alt analytics-icon bg-warning-transparent text-warning"></i>
                                                                <span class="fw-semibold mb-4 fs-14 ">Total Products</span>
                                                                <a href="javascript:void(0);" class="float-end fs-12 text-muted">1 day ago</a>
                                                                <p class="mb-0 text-muted fs">1.3k New Products</p>
                                                            </li>
                                                            <li class="mt-0">
                                                                <i class="bx bx-wallet-alt analytics-icon bg-secondary-transparent text-secondary"></i>
                                                                <span class="fw-semibold mb-4 fs-14 ">Total Sales</span>
                                                                <a href="javascript:void(0);" class="float-end fs-12 text-muted">3 days ago</a>
                                                                <p class="mb-0 text-muted fs">23,89k  New Sales</p>
                                                            </li>
                                                            <li class="mt-0">
                                                                <i class="bx bx-money-withdraw analytics-icon bg-success-transparent text-success"></i>
                                                                <span class="fw-semibold mb-4 fs-14 ">Total Revenue</span>
                                                                <a href="javascript:void(0);" class="float-end fs-12 text-muted">5 days ago</a>
                                                                <p class="mb-0 text-muted fs">187k  New Revenue</p>
                                                            </li>
                                                            <li class="mt-0">
                                                                <i class="bx bx-download analytics-icon bg-info-transparent text-info"></i>
                                                                <span class="fw-semibold mb-4 fs-14 ">Total profit</span>
                                                                <a href="javascript:void(0);" class="float-end fs-12 text-muted">1 week ago</a>
                                                                <p class="mb-0 text-muted fs">13k  New profit</p>
                                                            </li>
                                                            <li class="my-0">
                                                                <i class="bx bx-money analytics-icon bg-pink-transparent text-pink"></i>
                                                                <span class="fw-semibold mb-4 fs-14 ">Total Income</span>
                                                                <a href="javascript:void(0);" class="float-end fs-12 text-muted">2 week ago</a>
                                                                <p class="mb-0 text-muted fs">983k  New Income</p>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Audience Report
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-primary-light btn-wave"><i class="ri-share-forward-line me-1 align-middle d-inline-block"></i>Export</button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div id="audienceReport"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6">
                                            <div class="card custom-card overflow-hidden">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Top Countries By visitors
                                                    </div>
                                                    <div class="dropdown">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="btn btn-light btn-sm border" data-bs-toggle="dropdown">
                                                            <i class="fe fe-more-vertical"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li class=""><a class="dropdown-item" href="javascript:void(0);">Action</a></li>
                                                            <li class=""><a class="dropdown-item" href="javascript:void(0);">Another action</a></li>
                                                            <li><a class="dropdown-item" href="javascript:void(0);">Something else here</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table text-nowrap mb-0">
                                                            <tbody class="top-selling">
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-inline-flex top-countries">
                                                                            <span class="avatar avatar-xs  text-default">
                                                                                <img src="{{asset('build/assets/images/flags/us_flag.jpg')}}" alt="">
                                                                            </span>
                                                                            <span class="ms-2 fs-14 fw-semibold mb-0">USA</span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="wd-100">
                                                                        <div class="progress progress-xs">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 90%;"></div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="">
                                                                        <span class="fw-semibold fs-14">98.75k</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                            <div class="d-inline-flex top-countries">
                                                                                <span class="avatar avatar-xs  text-default">
                                                                                    <img src="{{asset('build/assets/images/flags/india_flag.jpg')}}" alt="">
                                                                                </span>
                                                                                <span class="ms-2 fs-14 fw-semibold mb-0">India</span>
                                                                            </div>
                                                                    </td>
                                                                    <td class="wd-100">
                                                                        <div class="progress progress-xs">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-secondary" style="width: 80%;"></div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="">
                                                                        <span class="fw-semibold fs-14">14.82k</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                            <div class="d-inline-flex top-countries">
                                                                                <span class="avatar avatar-xs  text-default">
                                                                                    <img src="{{asset('build/assets/images/flags/argentina_flag.jpg')}}" alt="">
                                                                                </span>
                                                                                <span class="ms-2 fs-14 fw-semibold mb-0">Argentina</span>
                                                                            </div>
                                                                    </td>
                                                                    <td class="wd-100">
                                                                        <div class="progress progress-xs">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: 60%;"></div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="">
                                                                        <span class="fw-semibold fs-14">14.82k</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                            <div class="d-inline-flex top-countries">
                                                                                <span class="avatar avatar-xs  text-default">
                                                                                    <img src="{{asset('build/assets/images/flags/canada_flag.jpg')}}" alt="">
                                                                                </span>
                                                                                <span class="ms-2 fs-14 fw-semibold mb-0">Canada</span>
                                                                            </div>
                                                                    </td>
                                                                    <td class="wd-100">
                                                                        <div class="progress progress-xs">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 75%;"></div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="">
                                                                        <span class="fw-semibold fs-14">91.60k</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                            <div class="d-inline-flex top-countries">
                                                                                <span class="avatar avatar-xs  text-default">
                                                                                    <img src="{{asset('build/assets/images/flags/russia_flag.jpg')}}" alt="">
                                                                                </span>
                                                                                <span class="ms-2 fs-14 fw-semibold mb-0">Russia</span>
                                                                            </div>
                                                                    </td>
                                                                    <td class="wd-100">
                                                                        <div class="progress progress-xs">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 60%;"></div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="">
                                                                        <span class="fw-semibold fs-14">15.60k</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="">
                                                                            <div class="d-inline-flex top-countries">
                                                                                <span class="avatar avatar-xs  text-default">
                                                                                    <img src="{{asset('build/assets/images/flags/spain_flag.jpg')}}" alt="">
                                                                                </span>
                                                                                <span class="ms-2 fs-14 fw-semibold mb-0">Spain</span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="wd-100">
                                                                        <div class="progress progress-xs">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-pink" style="width: 70%;"></div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="">
                                                                        <span class="fw-semibold fs-14">14.60k</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="">
                                                                            <div class="d-inline-flex top-countries">
                                                                                <span class="avatar avatar-xs  text-default">
                                                                                    <img src="{{asset('build/assets/images/flags/mexico_flag.jpg')}}" alt="">
                                                                                </span>
                                                                                <span class="ms-2 fs-14 fw-semibold mb-0">Mexico</span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="wd-100">
                                                                        <div class="progress progress-xs">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-teal" style="width: 50%;"></div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="">
                                                                        <span class="fw-semibold fs-14">14.60k</span>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-5 col-lg-6">
                                            <div class="card custom-card  overflow-hidden">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">Top Visitors</div>
                                                    <div class="dropdown">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fe fe-more-vertical"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" role="menu">
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Today</a></li>
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">This Week</a></li>
                                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table text-nowrap mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col" class="ps-4">Member</th>
                                                                    <th scope="col">Time</th>
                                                                    <th scope="col">Location</th>
                                                                    <th scope="col">Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="top-selling">
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="{{asset('build/assets/images/faces/1.jpg')}}" class="rounded-1" alt="">
                                                                            </span>
                                                                            <div class="flex-1 ms-2">
                                                                                <p class="mb-0 fs-14">Diam Ipsum</p>
                                                                                <a href="javascript:void(0);" class="text-muted fs-12">12 min ago</a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">Today, 10:03</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">Germany</span>
                                                                    </td>
                                                                    <td><span class="badge badge-sm bg-success-transparent text-success">online</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="{{asset('build/assets/images/faces/2.jpg')}}" class="rounded-1" alt="">
                                                                            </span>
                                                                            <div class="flex-1 ms-2">
                                                                                <p class="mb-0 fs-14">Magna Elitr</p>
                                                                                <a href="javascript:void(0);" class="text-muted fs-12">15 min ago</a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">Today, 10:03</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">England</span>
                                                                    </td>
                                                                    <td><span class="badge badge-sm bg-success-transparent text-success">online</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="{{asset('build/assets/images/faces/3.jpg')}}" class="rounded-1" alt="">
                                                                            </span>
                                                                            <div class="flex-1 ms-2">
                                                                                <p class="mb-0 fs-14">No Eirmod</p>
                                                                                <a href="javascript:void(0);" class="text-muted fs-12">30 min ago</a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">Today, 11:03</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">parise</span>
                                                                    </td>
                                                                    <td><span class="badge badge-sm bg-danger-transparent text-danger">offline</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="{{asset('build/assets/images/faces/4.jpg')}}" class="rounded-1" alt="">
                                                                            </span>
                                                                            <div class="flex-1 ms-2">
                                                                                <p class="mb-0 fs-14">Eirmod Vonsetetur</p>
                                                                                <a href="javascript:void(0);" class="text-muted fs-12">50 min ago</a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">Today, 12:03</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">Usa</span>
                                                                    </td>
                                                                    <td><span class="badge badge-sm bg-success-transparent text-success">online</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="{{asset('build/assets/images/faces/7.jpg')}}" class="rounded-1" alt="">
                                                                            </span>
                                                                            <div class="flex-1 ms-2">
                                                                                <p class="mb-0 fs-14">Morrie ali</p>
                                                                                <a href="javascript:void(0);" class="text-muted fs-12">60 min ago</a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">Today, 01:03</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="fw-semibold">England</span>
                                                                    </td>
                                                                    <td><span class="badge badge-sm bg-danger-transparent text-danger">offline</span></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-4 col-lg-6">
                                            <div class="card custom-card  overflow-hidden">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Browser Usuage
                                                    </div>
                                                    <div class="dropdown">
                                                        <a href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                            View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" role="menu">
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Today</a></li>
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">This Week</a></li>
                                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover text-nowrap">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col" class="ps-4">Browser</th>
                                                                    <th scope="col">Sessions</th>
                                                                    <th scope="col"  class="text-center">Bounce Rate</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-rounded avatar-sm p-2 bg-primary-transparent me-2">
                                                                                <i class="ri-google-fill fs-18 text-primary"></i>
                                                                            </span>
                                                                            <div class="fw-semibold">Google</div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>23,379</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="d-block text-success fs-14 text-center">+0.892 <i class="ti ti-trending-up ms-1"></i></span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-rounded avatar-sm p-2 bg-secondary-transparent me-2">
                                                                                <i class="ri-safari-line fs-18 text-secondary"></i>
                                                                            </span>
                                                                            <div class="fw-semibold">Safari</div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>78,973</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="d-block text-success fs-14 text-center">+4.892 <i class="ti ti-trending-up ms-1"></i></span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-rounded avatar-sm p-2 bg-success-transparent me-2">
                                                                                <i class="ri-opera-fill fs-18 text-success"></i>
                                                                            </span>
                                                                            <div class="fw-semibold">Opera</div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>12,457</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="d-block text-danger fs-14 text-center">-0.142 <i class="ti ti-trending-down ms-1"></i></span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-rounded avatar-sm p-2 bg-info-transparent me-2">
                                                                                <i class="ri-edge-fill fs-18 text-info"></i>
                                                                            </span>
                                                                            <div class="fw-semibold">Edge</div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>8,570</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="d-block text-success fs-14 text-center">+21.092 <i class="ti ti-trending-up ms-1"></i></span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-rounded avatar-sm p-2 bg-warning-transparent me-2">
                                                                                <i class="ri-firefox-fill fs-18 text-warning"></i>
                                                                            </span>
                                                                            <div class="fw-semibold">Firefox</div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span>6,135</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="d-block text-danger fs-14 text-center">-2.098 <i class="ti ti-trending-down ms-1"></i></span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="border-bottom-0 ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-rounded avatar-sm p-2 bg-danger-transparent me-2">
                                                                                <i class="ri-ubuntu-fill fs-18 text-danger"></i>
                                                                            </span>
                                                                            <div class="fw-semibold">Ubuntu</div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="border-bottom-0">
                                                                        <span>4,789</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="d-block text-success fs-14 text-center">+31.765 <i class="ti ti-trending-up ms-1"></i></span>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-xl-4 col-lg-6">
                                            <div class="card custom-card ">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Visitors
                                                    </div>
                                                    <div class="dropdown">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fe fe-more-vertical"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" role="menu">
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Today</a></li>
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">This Week</a></li>
                                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div id="popTrades" class="mx-auto"></div>
                                                        <div class="d-flex justify-content-center">
                                                            <div class="d-flex p-4  pb-0  ps-0">
                                                                <div class="text-center">
                                                                    <p class="mb-1 text-muted"> <i class="bx bxs-circle text-primary fs-13  me-1"></i>Online visitors</p>
                                                                    <h5 class="mb-0">186,75.00 </h5>
                                                                    <span class="text-success font-semibold fs-12"><i class="ri-arrow-up-s-fill align-middle"></i>0.23%</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex p-4  pb-0 ">
                                                                <div class="text-center">
                                                                    <p class="mb-1 text-muted"> <i class="bx bxs-circle text-light fs-13  me-1"></i>Offline visitors</p>
                                                                    <h5 class=" mb-0">$122,39 </h5>
                                                                    <span class="text-danger font-semibold fs-12"><i class="ri-arrow-down-s-fill align-middle"></i>0.23%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                            </div>
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="text-sm fw-semibold mb-0">Active Users</h6>
                                                    <div class="dropdown">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fe fe-more-vertical"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" role="menu">
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Today</a></li>
                                                            <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">This Week</a></li>
                                                            <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                                        </ul>
                                                    </div>
                                                    </div>
                                                    <div class="d-flex align-items-end">
                                                        <div class="flex-1">
                                                            <p class="text-lg fw-semibold mb-1">12,890<span class="text-success fs-12 ms-2"><i class="fe fe-arrow-up-right me-2 fs-12"></i>10.5%</span></p>
                                                            <p class="mb-0 fs-12 text-muted">Currently active now</p>
                                                        </div>
                                                        <div class="ms-2">
                                                            <span class="avatar radius-5 bg-primary"><i class="fe fe-activity"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-9 col-xl-8 col-lg-12">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Visitors By Channel Report
                                                    </div>
                                                    <div class="d-flex flex-wrap">
                                                        <div class="me-3 my-1">
                                                            <input class="form-control form-control-sm" type="text" placeholder="Search Here" aria-label=" example">
                                                        </div>
                                                        <div class="dropdown my-1">
                                                            <a href="javascript:void(0);" class="btn btn-sm btn-primary btn-wave waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Sort By<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" role="menu">
                                                                <li><a class="dropdown-item" href="javascript:void(0);">New</a></li>
                                                                <li><a class="dropdown-item" href="javascript:void(0);">Popular</a></li>
                                                                <li><a class="dropdown-item" href="javascript:void(0);">Relevant</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover text-nowrap table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">S.No</th>
                                                                    <th scope="col">Channel</th>
                                                                    <th scope="col">Sessions</th>
                                                                    <th scope="col">Bounce Rate</th>
                                                                    <th scope="col">Avg Session Duration</th>
                                                                    <th scope="col">Goal Completed</th>
                                                                    <th scope="col">Pages Per Session</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row" class="text-center">
                                                                        1
                                                                    </th>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-sm bg-primary-transparent avatar-rounded">
                                                                                <i class="ri-search-2-line fs-15 fw-semibiold text-primary"></i>
                                                                            </span>
                                                                            <span class="ms-2">
                                                                                Organic Search
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td>782</td>
                                                                    <td>32.09%</td>
                                                                    <td>
                                                                        0 hrs : 0 mins : 32 secs
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="">278</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                    <span class="badge bg-primary-transparent">2.9</span>
                                                                </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row" class="text-center">
                                                                        2
                                                                    </th>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-sm bg-secondary-transparent avatar-rounded">
                                                                                <i class="ri-globe-line fs-15 fw-semibiold text-secondary"></i>
                                                                            </span>
                                                                            <span class="ms-2">
                                                                                Direct
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td>882</td>
                                                                    <td>39.38%</td>
                                                                    <td>
                                                                        0 hrs : 2 mins : 45 secs
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="">782</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                    <span class="badge bg-secondary-transparent">1.5</span>
                                                                </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row" class="text-center">
                                                                        3
                                                                    </th>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-sm bg-success-transparent avatar-rounded">
                                                                                <i class="ri-share-forward-line fs-15 fw-semibiold text-success"></i>
                                                                            </span>
                                                                            <span class="ms-2">
                                                                                Referral
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td>322</td>
                                                                    <td>22.67%</td>
                                                                    <td>
                                                                        0 hrs : 38 mins : 28 secs
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span >622</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                    <span class="badge bg-success-transparent"> 3.2</span>
                                                                </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row" class="text-center">
                                                                        4
                                                                    </th>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-sm bg-info-transparent avatar-rounded">
                                                                                <i class="ri-reactjs-line fs-15 fw-semibiold text-info"></i>
                                                                            </span>
                                                                            <span class="ms-2">
                                                                                Social
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td>389</td>
                                                                    <td>25.11%</td>
                                                                    <td>
                                                                        0 hrs : 12 mins : 89 secs
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span>142</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                    <span class="badge bg-info-transparent">  5.5</span>
                                                                </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row" class="text-center">
                                                                        5
                                                                    </th>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="avatar avatar-sm bg-warning-transparent avatar-rounded">
                                                                                <i class="ri-mail-line fs-15 fw-semibiold text-warning"></i>
                                                                            </span>
                                                                            <span class="ms-2">
                                                                                Email
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td>378</td>
                                                                    <td>23.79%</td>
                                                                    <td>
                                                                        0 hrs : 14 mins : 27 secs
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span >178</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                    <span class="badge bg-warning-transparent">  2.5</span>
                                                                </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            Showing 6 Entries <i class="bi bi-arrow-right ms-2 fw-semibold"></i>
                                                        </div>
                                                        <div class="ms-auto">
                                                            <nav aria-label="Page navigation" class="pagination-style-4">
                                                                <ul class="pagination mb-0">
                                                                    <li class="page-item disabled">
                                                                        <a class="page-link" href="javascript:void(0);">
                                                                            Prev
                                                                        </a>
                                                                    </li>
                                                                    <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                                    <li class="page-item">
                                                                        <a class="page-link text-primary" href="javascript:void(0);">
                                                                            next
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </nav>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--End::row-1 -->

                        </div>
                    </div>
                    <!-- END APP CONTENT -->

@endsection

@section('scripts')

        <!-- APEX CHARTS JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- USED FOR SESSIONS BY DEVICE CHART -->
        <script src="{{asset('build/assets/libs/moment/moment.js')}}"></script>

        <!-- ANALYTICS DASHBOARD JS -->
        @vite('resources/assets/js/analytics-dashboard.js')


@endsection