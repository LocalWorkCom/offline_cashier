@extends('layouts.master')

@section('styles')


@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
                        <h4 class="fw-medium mb-0">CRM-Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">Dashboards</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">CRM</li>
                        </ol>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start::row-1 -->
                            <div class="row">
                                <div class="col-xxl-6 col-xl-12">
                                    <div class="row">
                                        <div class="col-xxl-6 col-lg-6 col-md-6 col-sm-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <div class="avatar avatar-md bg-primary-transparent text-primary">
                                                                <i class="fe fe-users"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="tx-inverse float-end text-muted" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots tx-14"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <div class="mg-b-6">
                                                                <p class="mb-0 tx-13 text-muted">Total Customers</p>
                                                            </div>
                                                            <div class="flex-between">
                                                                <h3 class="tx-20 mb-0 font-weight-normal">1,116</h3>
                                                                <span class="text-primary ms-1"><i class="ti ti-arrow-up-right me-1"></i>+12.86%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-6 col-lg-6 col-md-6 col-sm-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <div class="avatar avatar-md bg-success-transparent">
                                                                <i class="fe fe-edit"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="tx-inverse float-end text-muted" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots tx-14"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <div class="mg-b-6">
                                                                <p class="mb-0 tx-13 text-muted">Total Deals</p>
                                                            </div>
                                                            <div class="flex-between">
                                                                <h3 class="tx-20 mb-0 font-weight-normal">7,030</h3>
                                                                <span class="text-success  ms-1"><i class="ti ti-arrow-up-right me-1"></i>+54.86%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-6 col-lg-6 col-md-6 col-sm-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <div class="avatar avatar-md bg-secondary-transparent">
                                                                <i class="fe fe-dollar-sign"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="tx-inverse float-end text-muted" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots tx-14"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <div class="mg-b-6">
                                                                <p class="mb-0 tx-13 text-muted"> Total Revenue</p>
                                                            </div>
                                                            <div class="flex-between">
                                                                <h3 class="tx-20 mb-0 font-weight-normal">$87.030</h3>
                                                                <span class="text-secondary  ms-1"><i class="ti ti-arrow-down-right me-1"></i>+94.86%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-6 col-lg-6 col-md-6 col-sm-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <div class="avatar avatar-md bg-warning-transparent">
                                                                <i class="fe fe-repeat"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="tx-inverse float-end text-muted" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots tx-14"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <div class="mg-b-6">
                                                                <p class="mb-0 tx-13 text-muted">Conversion Rate</p>
                                                            </div>
                                                            <div class="flex-between">
                                                                <h3 class="tx-20 mb-0 font-weight-normal">12.08%</h3>
                                                                <span class="text-warning ms-1"><i class="ti ti-arrow-up-right me-1"></i>+03.86%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Leads Response
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
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="d-sm-flex align-items-center">
                                                                <span class="text-lg fw-semibold">6.70</span>
                                                                <p class="mb-0 mt-0 fs-13 ms-sm-2 text-muted">Average Lead Response Time</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <ul class="list-unstyled mb-0">
                                                        <li class="mb-3">
                                                            <div class="d-flex">
                                                                <span class="avatar avatar-md avatar-rounded bg-secondary-transparent fw-semibold me-2">
                                                                    PO
                                                                </span>
                                                                <div class="flex-1">
                                                                    <div class="flex-between mb-1">
                                                                        <p class="mb-0">Patty O'Furniture.</p>
                                                                        <span>2.7</span>
                                                                    </div>
                                                                    <div class="progress progress-xs mb-3" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                                        <div class="progress-bar bg-secondary progress-bar-striped" style="width: 45%">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="d-flex">
                                                                <span class="avatar avatar-md avatar-rounded bg-success-transparent fw-semibold me-2">
                                                                    MB
                                                                </span>
                                                                <div class="flex-1">
                                                                    <div class="flex-between mb-1">
                                                                        <p class="mb-0">Maureen Biologist.</p>
                                                                        <span>4.7</span>
                                                                    </div>
                                                                    <div class="progress progress-xs mb-3" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                                        <div class="progress-bar bg-success progress-bar-striped" style="width: 65%">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="d-flex">
                                                                <span class="avatar avatar-md avatar-rounded bg-warning-transparent fw-semibold me-2">
                                                                    P0
                                                                </span>
                                                                <div class="flex-1">
                                                                    <div class="flex-between mb-1">
                                                                        <p class="mb-0">Paddy O'Furniture.</p>
                                                                        <span>3.5</span>
                                                                    </div>
                                                                    <div class="progress progress-xs mb-3" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                                        <div class="progress-bar bg-warning progress-bar-striped" style="width: 50%">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="">
                                                            <div class="d-flex">
                                                                <span class="avatar avatar-md avatar-rounded bg-info-transparent fw-semibold me-2">
                                                                    BS
                                                                </span>
                                                                <div class="flex-1">
                                                                    <div class="flex-between mb-1">
                                                                        <p class="mb-0">Borry Sananesh.</p>
                                                                        <span>4.6</span>
                                                                    </div>
                                                                    <div class="progress progress-xs mb-3" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                                        <div class="progress-bar bg-info progress-bar-striped" style="width: 70%">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6">
                                            <div class="card custom-card">
                                                <div class="card-header  justify-content-between">
                                                    <div class="card-title">
                                                        Top Deals
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
                                                <div class="card-body">
                                                    <ul class="list-unstyled crm-top-deals mb-0">
                                                        <li class="mb-3">
                                                            <div class="d-flex align-items-top flex-wrap">
                                                                <div class="me-2">
                                                                    <div class="me-2">
                                                                        <div class="avatar avatar-md"><img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img" class="radius-5"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-fill">
                                                                    <p class="fw-semibold mb-0">Michael Jordan</p>
                                                                    <span class="text-muted fs-12">michael@example.com</span>
                                                                </div>
                                                                <div class="fw-semibold fs-15">$2,893</div>
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="d-flex align-items-top flex-wrap">
                                                                <div class="me-2">
                                                                    <div class="me-2">
                                                                        <div class="avatar avatar-md"><img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img" class="radius-5"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-fill">
                                                                    <p class="fw-semibold mb-0">Emigo Kiaren</p>
                                                                    <span class="text-muted fs-12">emigo@gmail.com</span>
                                                                </div>
                                                                <div class="fw-semibold fs-15">$4,289</div>
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="d-flex align-items-top flex-wrap">
                                                                <div class="me-2">
                                                                    <div class="me-2">
                                                                        <div class="avatar avatar-md"><img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img" class="radius-5"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-fill">
                                                                    <p class="fw-semibold mb-0">Randy Origon</p>
                                                                    <span class="text-muted fs-12">randyn@gmail.com</span>
                                                                </div>
                                                                <div class="fw-semibold fs-15">$6,347</div>
                                                            </div>
                                                        </li>
                                                        <li class="mb-3">
                                                            <div class="d-flex align-items-top flex-wrap">
                                                                <div class="me-2">
                                                                    <div class="me-2">
                                                                        <div class="avatar avatar-md"><img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img" class="radius-5"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-fill">
                                                                    <p class="fw-semibold mb-0">George Pieterson</p>
                                                                    <span class="text-muted fs-12">george@gmail.com</span>
                                                                </div>
                                                                <div class="fw-semibold fs-15">$3,894</div>
                                                            </div>
                                                        </li>
                                                        <li class="">
                                                            <div class="d-flex align-items-top flex-wrap">
                                                                <div class="me-2">
                                                                    <div class="me-2">
                                                                        <div class="avatar avatar-md"><img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img" class="radius-5"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-fill">
                                                                    <p class="fw-semibold mb-0">Shara Pieterson</p>
                                                                    <span class="text-muted fs-12">Pieterson@gmail.com</span>
                                                                </div>
                                                                <div class="fw-semibold fs-15">$4,699</div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="card custom-card overflow-hidden">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Leads Overview
                                                    </div>
                                                    <div class="dropdown">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
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
                                                        <table class="table text-nowrap">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col" class="ps-4">Lead Name</th>
                                                                    <th scope="col">Lead Source</th>
                                                                    <th scope="col">Contact</th>
                                                                    <th scope="col">Lead Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="me-2 lh-1">
                                                                                <div class="avatar avatar-sm"><img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="img" class="radius-5"></div>
                                                                            </div>
                                                                            <div>
                                                                                <span class="fs-14">
                                                                                    Benson and John's
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-success ">online store</td>
                                                                    <td>
                                                                        <div>
                                                                            <!-- <p class="mb-0">benson123@abs.com</p> -->
                                                                            <p class="mb-0">+123-9876530</p>
                                                                        </div>
                                                                    </td>
                                                                    <td class="border-top-0">
                                                                        <span class="badge rounded-pill bg-danger-transparent">Unqualified</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="me-2 lh-1">
                                                                                <div class="avatar avatar-sm"><img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img" class="radius-5"></div>
                                                                            </div>
                                                                            <div>
                                                                                <span class="fs-14">
                                                                                    Ragnor Rock
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-info">External Link</td>
                                                                    <td>
                                                                        <div>
                                                                            <!-- <p class="mb-0">Ragnor643@abs.com</p> -->
                                                                            <p class="mb-0">+123-9876760</p>
                                                                        </div>
                                                                    </td>
                                                                    <td class="border-top-0">
                                                                        <span class="badge rounded-pill bg-success-transparent">Attempt To Contact</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="me-2 lh-1">
                                                                                <div class="avatar avatar-sm"><img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img" class="radius-5"></div>
                                                                            </div>
                                                                            <div>
                                                                                <span class="fs-14">
                                                                                    Beth Industries
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-success">Online Store</td>
                                                                    <td>
                                                                        <div>
                                                                            <!-- <p class="mb-0">Industries443@abs.com</p> -->
                                                                            <p class="mb-0">+123-48986760</p>
                                                                        </div>
                                                                    </td>
                                                                    <td class="border-top-0">
                                                                        <span class="badge rounded-pill bg-primary-transparent">New</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="me-2 lh-1">
                                                                                <div class="avatar avatar-sm"><img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img" class="radius-5"></div>
                                                                            </div>
                                                                            <div>
                                                                                <span class="fs-14">
                                                                                    Opera
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-info">External Link</td>
                                                                    <td>
                                                                        <div>
                                                                            <!-- <p class="mb-0">Opera3443@abs.com</p> -->
                                                                            <p class="mb-0">+123-98986760</p>
                                                                        </div>
                                                                    </td>
                                                                    <td class="border-top-0">
                                                                        <span class="badge rounded-pill bg-warning-transparent">Bad Timming</span>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-6 col-xl-12">
                                    <div class="row">
                                        <div class="col-xxl-12 col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between flex-wrap">
                                                    <div class="card-title">
                                                        Total Revenue
                                                    </div>
                                                    <div class="btn-group" role="group" aria-label="Basic example">
                                                        <button type="button" class="btn btn-primary btn-sm btn-wave">1D</button>
                                                        <button type="button" class="btn btn-primary-light btn-sm btn-wave">1W</button>
                                                        <button type="button" class="btn btn-primary-light btn-sm btn-wave">1M</button>
                                                        <button type="button" class="btn btn-primary-light btn-sm btn-wave">3M</button>
                                                        <button type="button" class="btn btn-primary-light btn-sm btn-wave">6M</button>
                                                        <button type="button" class="btn btn-primary-light btn-sm btn-wave">1Y</button>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div id="crm-statistics" class="p-3"></div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-xxl-6 col-xl-6 col-md-6">
                                            <div class="card custom-card ">
                                                <div class="card-header ">
                                                    <div class="card-title">Tasks</div>
                                                </div>
                                                <div class="card-body todo-tab">
                                                    <div class="panel panel-primary">
                                                        <ul class="nav nav-tabs tab-style-1 d-sm-flex d-block mb-0 justify-content-between" role="tablist">
                                                            <li class="nav-item mt-0">
                                                                <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#orders"
                                                                    aria-current="page" href="#orders">Today</a>
                                                            </li>
                                                            <li class="nav-item mt-0">
                                                                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#accepted"
                                                                    href="#accepted">Upcoming</a>
                                                            </li>
                                                            <li class="nav-item mt-0">
                                                                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#declined"
                                                                    href="#declined">Completed</a>
                                                            </li>
                                                        </ul>
                                                        <div class="tab-content">
                                                            <div class="tab-pane border-0 active" id="orders" role="tabpanel">
                                                                <div class="todolist mb-3 d-flex">
                                                                    <div class="mb-3 form-check  me-2">
                                                                        <input type="checkbox" class="form-check-input">
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between">
                                                                            <p class="mb-0 fs-14">Added New Customers</p>
                                                                            <div class="">
                                                                                <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-muted mb-2 fs-13">Invi sadip takimata</p>
                                                                        <div class="flex-between">
                                                                            <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>18-05-22</span>
                                                                            <span class="badge badge-sm rounded-pill bg-primary-transparent">Progress</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="todolist mb-3 d-flex">
                                                                    <div class="mb-3 form-check  me-2">
                                                                        <input type="checkbox" class="form-check-input" checked>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between">
                                                                            <p class="mb-0 fs-14">Sales Accounting</p>
                                                                            <div class="">
                                                                                <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-muted mb-2 fs-13">Eos dolor ea</p>
                                                                        <div class="flex-between">
                                                                            <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>05-10-22</span>
                                                                            <span class="badge badge-sm rounded-pill bg-success-transparent">Completed</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="todolist mb-3 d-flex">
                                                                    <div class="mb-3 form-check  me-2">
                                                                        <input type="checkbox" class="form-check-input">
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between">
                                                                            <p class="mb-0 fs-14">Meeting with Sales Team</p>
                                                                            <div class="">
                                                                                <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-muted mb-2 fs-13">Nonumy erat ipsum ut ipsum</p>
                                                                        <div class="flex-between">
                                                                            <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>15-03-22</span>
                                                                            <span class="badge badge-sm rounded-pill bg-danger-transparent">not Started</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="todolist d-flex">
                                                                    <div class="mb-3 form-check me-2">
                                                                        <input type="checkbox" class="form-check-input" checked>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between">
                                                                            <p class="mb-0 fs-14">Customers Meeting</p>
                                                                            <div class="">
                                                                                <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-muted mb-2 fs-13">Sed labore ut sed</p>
                                                                        <div class="flex-between">
                                                                            <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>15-03-22</span>
                                                                            <span class="badge badge-sm rounded-pill bg-success-transparent">completed</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane  border-0" id="accepted" role="tabpanel">
                                                                <div class="todolist">
                                                                    <div class="todolist mb-3 d-flex">
                                                                        <div class="mb-3 form-check  me-2">
                                                                            <input type="checkbox" class="form-check-input">
                                                                        </div>
                                                                        <div class="flex-1">
                                                                            <div class="d-flex align-items-start justify-content-between">
                                                                                <p class="mb-0 fs-14">Build a New Team</p>
                                                                                <div class="">
                                                                                    <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                                </div>
                                                                            </div>
                                                                            <p class="text-muted mb-2 fs-13">Nonumy erat ipsum ut ipsum</p>
                                                                            <div class="flex-between">
                                                                                <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>15-03-22</span>
                                                                                <span class="badge badge-sm rounded-pill bg-danger-transparent">not Started</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="todolist mb-3 d-flex">
                                                                        <div class="mb-3 form-check  me-2">
                                                                            <input type="checkbox" class="form-check-input">
                                                                        </div>
                                                                        <div class="flex-1">
                                                                            <div class="d-flex align-items-start justify-content-between">
                                                                                <p class="mb-0 fs-14">Meeting with Sales Team</p>
                                                                                <div class="">
                                                                                    <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                                </div>
                                                                            </div>
                                                                            <p class="text-muted mb-2 fs-13">Consetetur et amet dolor</p>
                                                                            <div class="flex-between">
                                                                                <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>15-03-22</span>
                                                                                <span class="badge badge-sm rounded-pill bg-danger-transparent">not Started</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="todolist mb-3 d-flex">
                                                                        <div class="mb-3 form-check  me-2">
                                                                            <input type="checkbox" class="form-check-input">
                                                                        </div>
                                                                        <div class="flex-1">
                                                                            <div class="d-flex align-items-start justify-content-between">
                                                                                <p class="mb-0 fs-14">Created a New Task today</p>
                                                                                <div class="">
                                                                                    <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                                </div>
                                                                            </div>
                                                                            <p class="text-muted mb-2 fs-13">Nonumy erat ipsum ut ipsum</p>
                                                                            <div class="flex-between">
                                                                                <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>15-03-22</span>
                                                                                <span class="badge badge-sm rounded-pill bg-danger-transparent">not Started</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    </div>
                                                                    <div class="todolist  d-flex">
                                                                        <div class="mb-3 form-check  me-2">
                                                                            <input type="checkbox" class="form-check-input">
                                                                        </div>
                                                                        <div class="flex-1">
                                                                            <div class="d-flex align-items-start justify-content-between">
                                                                                <p class="mb-0 fs-14">27 New people joined summit</p>
                                                                                <div class="">
                                                                                    <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                                </div>
                                                                            </div>
                                                                            <p class="text-muted mb-2 fs-13">Accusam aliquyam ea sea</p>
                                                                            <div class="flex-between">
                                                                                <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>15-03-22</span>
                                                                                <span class="badge badge-sm rounded-pill bg-danger-transparent">not Started</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                            </div>
                                                            <div class="tab-pane  border-0" id="declined" role="tabpanel">
                                                                <div class="todolist mb-3 d-flex">
                                                                    <div class="mb-3 form-check  me-2">
                                                                        <input type="checkbox" class="form-check-input" checked>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between">
                                                                            <p class="mb-0 fs-14">Meeting with Sales Team</p>
                                                                            <div class="">
                                                                                <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-muted mb-2 fs-13">Eos dolor ea</p>
                                                                        <div class="flex-between">
                                                                            <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>05-10-22</span>
                                                                            <span class="badge badge-sm rounded-pill bg-success-transparent">Completed</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="todolist mb-3 d-flex">
                                                                    <div class="mb-3 form-check  me-2">
                                                                        <input type="checkbox" class="form-check-input" checked>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between">
                                                                            <p class="mb-0 fs-14">Replied to new support request</p>
                                                                            <div class="">
                                                                                <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-muted mb-2 fs-13">Eos clita dolor elitr et</p>
                                                                        <div class="flex-between">
                                                                            <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>05-10-22</span>
                                                                            <span class="badge badge-sm rounded-pill bg-success-transparent">Completed</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="todolist mb-3 d-flex">
                                                                    <div class="mb-3 form-check  me-2">
                                                                        <input type="checkbox" class="form-check-input" checked>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between">
                                                                            <p class="mb-0 fs-14">Update of calendar events</p>
                                                                            <div class="">
                                                                                <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-muted mb-2 fs-13">Sadi tempor guberg rebum</p>
                                                                        <div class="flex-between">
                                                                            <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>05-10-22</span>
                                                                            <span class="badge badge-sm rounded-pill bg-success-transparent">Completed</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="todolist  d-flex">
                                                                    <div class="mb-3 form-check  me-2">
                                                                        <input type="checkbox" class="form-check-input" checked>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between">
                                                                            <p class="mb-0 fs-14">Added New Customers</p>
                                                                            <div class="">
                                                                                <a href="javascript:void(0);" class="text-danger op-7 mg-e-6" data-bs-toggle="tooltip" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="fe fe-trash fs-14"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-muted mb-2 fs-13">Sadi tempor guberg rebum</p>
                                                                        <div class="flex-between">
                                                                            <span class="text-muted fs-12"><i class="bi bi-calendar4-event me-2 fs-11"></i>05-10-22</span>
                                                                            <span class="badge badge-sm rounded-pill bg-success-transparent">Completed</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                        </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6 col-md-6">
                                            <div class="card custom-card ">
                                                <div class="card-header">
                                                    <div>
                                                        <h5 class="card-title mb-0">Leads By Source</h5>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <a aria-label="anchor" href="javascript:void(0)" class="btn btn-outline-light btn-icons btn-sm text-muted" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fe fe-more-vertical"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-start">
                                                            <a class="dropdown-item border-bottom" href="javascript:void(0)">
                                                                <i class="ri-share-forward-line me-2 d-inline-block"></i>Share
                                                            </a>
                                                            <a class="dropdown-item border-bottom" href="javascript:void(0)">
                                                                <i class="ri-download-2-line me-2 d-inline-block"></i>Download
                                                            </a>
                                                            <a class="dropdown-item" href="javascript:void(0)">
                                                                <i class="ri-delete-bin-7-line me-2 d-inline-block"></i>Delete
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body pb-2">
                                                    <div id="dealsSource"></div>
                                                </div>
                                                <div class="card-footer p-0">
                                                    <div class="d-flex">
                                                        <div class="flex-1 px-4 py-3 text-center border-end-dotted">
                                                            <p class="fs-13 mb-1  text-muted">Total Leads</p>
                                                            <span class="text-md fw-semibold">1,289</span>
                                                            <span class="text-success fs-12 ms-2"><i class="ti ti-arrow-up-right"></i>5.08%</span>
                                                        </div>
                                                        <div class="flex-1 px-4 py-3 text-center">
                                                            <p class="fs-13 mb-1 text-muted">Leads to Deals Rate</p>
                                                            <span class="text-md fw-semibold">+12.08%</span>
                                                            <span class="text-success fs-12 ms-2"><i class="ti ti-arrow-up-right"></i>3.64%</span>
                                                        </div>
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
                                                Top Deals
                                            </div>
                                            <div class="d-flex flex-wrap">
                                                <div class="me-3 my-1">
                                                    <input class="form-control  form-control-sm" type="text" placeholder="Search Here" aria-label=" example">
                                                </div>
                                                <div class="dropdown m-1">
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
                                                <table class="table text-nowrap table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">S.No </th>
                                                            <th scope="col">Deal </th>
                                                            <th scope="col">Sales Rep</th>
                                                            <th scope="col">Mail</th>
                                                            <th scope="col">Amount</th>
                                                            <th scope="col">Location</th>
                                                            <th scope="col">Sales Cycle length</th>
                                                            <th scope="col">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <span>
                                                                    01
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold">	Xenon Tech. Ed.</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div class="fs-14">Simon Cowall</div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                mayorkelly@gmail.com
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold fs-14">$4320.29</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-inline-flex align-items-center">
                                                                    <i class="ri-map-pin-fill text-muted fs-10"></i>
                                                                    <span class="ms-1">Germany</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">43 Days</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-1">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-light btn-wave"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-info-light btn-wave"><i class="ri-edit-line"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span>
                                                                    02
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold">Ideal IT Sol.</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div class="fs-14">Meisha Kerr</div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                andrewgarfield@gmail.com
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold fs-14">$6745.99</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-inline-flex align-items-center">
                                                                    <i class="ri-map-pin-fill text-muted fs-10"></i>
                                                                    <span class="ms-1">Canada</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">45 Days</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-1">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-light btn-wave"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-info-light btn-wave"><i class="ri-edit-line"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span>
                                                                    03
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold">Inferno Xo</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div class="fs-14">Jessica</div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                simoncowel234@gmail.com
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold fs-14">$1176.89</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-inline-flex align-items-center">
                                                                    <i class="ri-map-pin-fill text-muted fs-10"></i>
                                                                    <span class="ms-1">Singapore</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">50 Days</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-1">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-light btn-wave"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-info-light btn-wave"><i class="ri-edit-line"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span>
                                                                    04
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold">Myami Group &amp; Tech.</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div class="fs-14">Amanda B</div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                mirindahers@gmail.com
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold fs-14">$1899.99</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-inline-flex align-items-center">
                                                                    <i class="ri-map-pin-fill text-muted fs-10"></i>
                                                                    <span class="ms-1">USA</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">55 Days</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-1">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-light btn-wave"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-info-light btn-wave"><i class="ri-edit-line"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span>
                                                                    05
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold">Mevengo Tech Et Sed</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div class="fs-14">Jason Stathman</div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                jacobsmith@gmail.com
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold fs-14">$1867.29</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-inline-flex align-items-center">
                                                                    <i class="ri-map-pin-fill text-muted fs-10"></i>
                                                                    <span class="ms-1">Europe</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">30 Days</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-1">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-light btn-wave"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-info-light btn-wave"><i class="ri-edit-line"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span>
                                                                    06
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold">Lackme Info Et.</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div class="fs-14">Khabib Hussain</div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Hussain@gmail.com
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold fs-14">$2439.99</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-inline-flex align-items-center">
                                                                    <i class="ri-map-pin-fill text-muted fs-10"></i>
                                                                    <span class="ms-1">Canada</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">35 Days</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-1">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-success-light btn-wave"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-info-light btn-wave"><i class="ri-edit-line"></i></a>
                                                                </div>
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
                            <!-- End:: row-2 -->

                        </div>
                    </div>
                    <!-- END APP CONTENT -->

@endsection

@section('scripts')

        <!-- APEX CHARTS JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- SALES DASHBOARD JS -->
        @vite('resources/assets/js/crm-dashboard.js')


@endsection
