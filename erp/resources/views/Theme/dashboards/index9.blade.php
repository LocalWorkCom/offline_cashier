@extends('layouts.master')

@section('styles')


@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
                        <h4 class="fw-medium mb-0">HRM-Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">Dashboards</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">HRM</li>
                        </ol>
                    </div>
                    <!-- PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start::row-1 -->
                            <div class="row">
                                <div class="col-xxl-6">
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <div class="avatar avatar-lg bg-info-transparent text-info">
                                                                <i class="fe fe-users"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="">
                                                                <p class="mb-0  text-muted">Total Employees</p>
                                                            </div>
                                                            <div class="flex-between">
                                                                <h3 class="fs-20 mb-0 font-weight-normal">1,116</h3>
                                                                <span class="text-info"><i class="ti ti-arrow-up-right me-1 fs-14"></i>+12.86%</span>
                                                            </div>
                                                            <span class="badge bg-info-transparent">This Month</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <div class="avatar avatar-lg bg-warning-transparent text-warning">
                                                                <i class="fe fe-mail"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="">
                                                                <p class="mb-0  text-muted">Employees In Leave</p>
                                                            </div>
                                                            <div class="flex-between">
                                                                <h3 class="fs-20 mb-0 font-weight-normal">0567</h3>
                                                                <span class="text-warning"><i class="ti ti-arrow-up-right me-1 fs-14"></i>+54.86%</span>
                                                            </div>
                                                            <span class="badge bg-warning-transparent">This Month</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <div class="avatar avatar-lg bg-teal-transparent text-teal">
                                                                <i class="fe fe-user-plus"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="">
                                                                <p class="mb-0  text-muted">Total Clients</p>
                                                            </div>
                                                            <div class="flex-between">
                                                                <h3 class="fs-20 mb-0 font-weight-normal">0567</h3>
                                                                <span class="text-teal"><i class="ti ti-arrow-up-right me-1 fs-14"></i>+54.86%</span>
                                                            </div>
                                                            <span class="badge bg-teal-transparent">This Month</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="card custom-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <div class="avatar avatar-lg bg-pink-transparent text-pink">
                                                                <i class="fe fe-map"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="">
                                                                <p class="mb-0  text-muted">New Leads</p>
                                                            </div>
                                                            <div class="flex-between">
                                                                <h3 class="fs-20 mb-0 font-weight-normal">457</h3>
                                                                <span class="text-pink"><i class="ti ti-arrow-up-right me-1 fs-14"></i>+64.86%</span>
                                                            </div>
                                                            <span class="badge bg-pink-transparent">This Month</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="card custom-card">
                                            <div class="card-header">
                                                <div>
                                                    <h5 class="card-title mb-0"> Total Employees</h5>
                                                </div>
                                                <div class="ms-auto">
                                                    <a aria-label="anchor" href="javascript:void(0)" class="btn btn-outline-light btn-icons btn-sm text-muted" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fe fe-more-vertical"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-start">
                                                        <a class="d-flex dropdown-item" href="javascript:void(0)">
                                                            <i class="ri-share-forward-line me-2"></i>Share
                                                        </a>
                                                        <a class="d-flex dropdown-item" href="javascript:void(0)">
                                                            <i class="ri-download-2-line me-2"></i>Download
                                                        </a>
                                                        <a class="d-flex dropdown-item" href="javascript:void(0)">
                                                            <i class="ri-delete-bin-7-line me-2"></i>Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <div class="card-body">
                                                            <div class="d-sm-flex mb-3 ">
                                                            <div class="me-sm-2 my-auto">
                                                                <div class="avatar bg-primary-transparent text-primary">
                                                                    <i class="fe fe-user-plus"></i>
                                                                </div>
                                                            </div>
                                                            <div class="">
                                                                <p class="mb-0 text-muted">Total Employees</p>
                                                                <h3 class="mb-0 numberfont">50,200</h3>
                                                            </div>
                                                        </div>
                                                        <div class="progress progress-xl my-4">
                                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 65%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">65%</div>
                                                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 35%" aria-valuenow="12" aria-valuemin="0" aria-valuemax="100">35%</div>
                                                        </div>
                                                        <div class="d-flex justify-content-evenly text-center ">
                                                            <div class="me-5">
                                                                <span class="fs-12 text-muted fw-semibold d-block d-sm-inline-block"><i class="bx bxs-circle text-primary fs-12 me-1"></i>Male</span>
                                                                <h5 class="mb-0">30,000</h5>
                                                            </div>
                                                            <div class="">
                                                                <span class="fs-12 text-muted fw-semibold d-block d-sm-inline-block"><i class="bx bxs-circle text-secondary fs-12 me-1"></i>Female</span>
                                                                <h5 class="mb-0">20,200</h5>
                                                            </div>
                                                        </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="card custom-card">
                                                <div class="card-header justify-content-between">
                                                    <div class="card-title">
                                                        Birthday Today
                                                    </div>
                                                    <div class="ms-auto">
                                                        <a aria-label="anchor" href="javascript:void(0)" class="btn btn-outline-light btn-icons btn-sm text-muted" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fe fe-more-vertical"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-start">
                                                            <a class="d-flex dropdown-item" href="javascript:void(0)">
                                                                <i class="ri-share-forward-line me-2"></i>Share
                                                            </a>
                                                            <a class="d-flex dropdown-item" href="javascript:void(0)">
                                                                <i class="ri-download-2-line me-2"></i>Download
                                                            </a>
                                                            <a class="d-flex dropdown-item" href="javascript:void(0)">
                                                                <i class="ri-delete-bin-7-line me-2"></i>Delete
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="">
                                                    <ul class="list-group list-group-flush rounded-3">
                                                        <li class="list-group-item d-flex">
                                                            <a href="javascript:void(0);">
                                                                <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-2 lh-1">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="">
                                                                            </span>
                                                                        </div>
                                                                        <div>
                                                                            <p class="fw-semibold mb-0 fs-15">Kakasha Si</p>
                                                                            <span class="text-muted fs-12">@sensei011</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                            <div class="ms-auto">
                                                                <button type="button" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Wish Him</button>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item d-flex">
                                                            <a href="javascript:void(0);">
                                                                <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-2 lh-1">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="">
                                                                            </span>
                                                                        </div>
                                                                        <div>
                                                                            <p class="fw-semibold mb-0 fs-15">SakuraYM</p>
                                                                            <span class="text-muted fs-12">@sakura903</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                            <div class="ms-auto">
                                                                <button type="button" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Wish Him</button>
                                                            </div>
                                                        </li>
                                                        <li class=" list-group-item d-flex">
                                                            <a href="javascript:void(0);">
                                                                <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-2 lh-1">
                                                                            <span class="avatar avatar-md">
                                                                                <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="">
                                                                            </span>
                                                                        </div>
                                                                        <div>
                                                                            <p class="fw-semibold mb-0 fs-15">Sasuke Uchiha</p>
                                                                            <span class="text-muted fs-12">@sasuke777</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                            <div class="ms-auto">
                                                                <button type="button" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Wish Her</button>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-6">
                                    <div class="col-xxl-12 col-xl-12">
                                        <div class="card custom-card">
                                            <div class="card-header  justify-content-between">
                                                <div class="card-title">Payrol Summary</div>
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
                                                <div id="hrmstatistics"></div>
                                                <div class="row mt-4 justify-content-center">
                                                    <div class="col-xl-4 col-md-4  m-1 m-md-0 d-block text-center">
                                                        <div class="px-4 py-2 border rounded-1">
                                                            <h4 class="mb-1">$73,970</h4>
                                                            <p class="mb-0 text-muted"><i class="bx bxs-circle text-primary fs-13  me-1"></i>Gross Salary </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-4 col-md-4  m-1 m-md-0  d-block text-center">
                                                        <div class="px-4 py-2  border rounded-1">
                                                            <h4 class="mb-1">45,389</h4>
                                                            <p class="mb-0 text-muted"><i class="bx bxs-circle text-secondary fs-13 me-1"></i>Net Salary </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-4  col-md-4  m-1  m-md-0 d-block text-center">
                                                        <div class="px-4 py-2  border rounded-1">
                                                            <h4 class="mb-1">19,685</h4>
                                                            <p class="mb-0 text-muted"> <i class="bx bxs-circle text-light fs-13 me-1"></i>Taxes</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End::row-1-->
                            
                            <!--Start::row-2-->
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between flex-wrap">
                                            <div class="card-title flex-between">
                                                Upcoming Events
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
                                            <ul class="list-unstyled mb-0 up-event-container">
                                                <li class="list-item mb-4 up-event">
                                                    <div class="d-flex align-items-start">
                                                        <div class="me-5 text-center fw-semibold">
                                                            <p class="text-primary fs-12 mb-0">5 min ago</p>
                                                        </div>
                                                        <div class="flex-1 fw-semibold">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="mb-2">You Have An Announcement - Ipsum Est Diam Eirmod</p>
                                                            <div class="d-flex flex-wrap align-items-center">
                                                                <span class="text-muted fw-normal"><i class="fe fe-clock me-1 d-inline-flex"></i>10:00AM</span>
                                                                <span class="badge bg-success-transparent rounded-pill badge-sm fw-semibold ms-2">Announcement</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item mb-4 up-event">
                                                    <div class="d-flex align-items-start">
                                                        <div class="me-5 text-center fw-semibold">
                                                            <p class="text-primary fs-12 mb-0">2 hrs ago</p>
                                                        </div>
                                                        <div class="flex-1 fw-semibold">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="mb-2">National Holiday - Vero Jayanti</p>
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge bg-pink-transparent rounded-pill badge-sm fw-semibold">Holiday</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item mb-4 up-event">
                                                    <div class="d-flex align-items-start">
                                                        <div class="me-5 text-center fw-semibold">
                                                            <p class="text-primary fs-12 mb-0">12 hrs ago</p>
                                                        </div>
                                                        <div class="flex-1 fw-semibold">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="mb-2"><a href="javascript:void(0);" class="text-primary me-1">John Pup</a>Birthday - Team Member</p>
                                                            <div class="d-flex align-items-center">
                                                                <span class="text-muted fw-normal"><i class="fe fe-clock me-1 d-inline-flex"></i>09:00AM</span>
                                                                <span class="badge bg-secondary-transparent rounded-pill badge-sm fw-semibold ms-2">Birthday</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item  mb-4  up-event">
                                                    <div class="d-flex align-items-start">
                                                        <div class="me-5 text-center fw-semibold">
                                                            <p class="text-primary fs-12 mb-0">yesterday</p>
                                                        </div>
                                                        <div class="flex-1 fw-semibold">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="mb-2">National Holiday - Dolore Ipsum</p>
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge bg-pink-transparent rounded-pill badge-sm fw-semibold">Holiday</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item up-event">
                                                    <div class="d-flex align-items-start">
                                                        <div class="me-5 text-center fw-semibold">
                                                            <p class="text-primary fs-12 mb-0">yesterday</p>
                                                        </div>
                                                        <div class="flex-1 fw-semibold">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="mb-2">Amet Sed No Dolor Kasd - Et Dolores Tempor Erat</p>
                                                            <div class="d-flex align-items-center">
                                                                <span class="text-muted fw-normal"><i class="fe fe-clock me-1 d-inline-flex"></i>06:00AM</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-5">
                                    <div class="col-xl-12">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">Absent Members</div>
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
                                                                <th scope="col">Role</th>
                                                                <th scope="col">Cause</th>
                                                                <th scope="col">From</th>
                                                                <th scope="col">Up-To</th>
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
                                                                            <p class="mb-0 fs-14">Richard Dom</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">richard116@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">Team Leader</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold badge bg-primary-transparent">	Sick Leave</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	13-05-23</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	15-05-23</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="ps-4">
                                                                    <div class="d-flex">
                                                                        <span class="avatar avatar-md">
                                                                            <img src="{{asset('build/assets/images/faces/10.jpg')}}" class="rounded-1" alt="">
                                                                        </span>
                                                                        <div class="flex-1 ms-2">
                                                                            <p class="mb-0 fs-14">Jennifer Tab</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">jenny258@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">Project Manager</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold badge bg-success-transparent">Travel</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	15-05-23</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	20-05-23</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="ps-4">
                                                                    <div class="d-flex">
                                                                        <span class="avatar avatar-md">
                                                                            <img src="{{asset('build/assets/images/faces/2.jpg')}}" class="rounded-1" alt="">
                                                                        </span>
                                                                        <div class="flex-1 ms-2">
                                                                            <p class="mb-0 fs-14">Nikki Jey</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">nikki11@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">UI Developer</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold badge bg-secondary-transparent">Maternity Leave</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	10-05-23</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	15-12-23</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="ps-4">
                                                                    <div class="d-flex">
                                                                        <span class="avatar avatar-md">
                                                                            <img src="{{asset('build/assets/images/faces/8.jpg')}}" class="rounded-1" alt="">
                                                                        </span>
                                                                        <div class="flex-1 ms-2">
                                                                            <p class="mb-0 fs-14">Arifa Zed</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">arifaZ@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">Web Developer</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold badge bg-info-transparent">Other</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	13-06-23</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	10-07-23</span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="ps-4">
                                                                    <div class="d-flex">
                                                                        <span class="avatar avatar-md">
                                                                            <img src="{{asset('build/assets/images/faces/7.jpg')}}" class="rounded-1" alt="">
                                                                        </span>
                                                                        <div class="flex-1 ms-2">
                                                                            <p class="mb-0 fs-14">Xiong Yu</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">xingzing444@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold ">Team Member</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold badge bg-info-transparent">Other</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	13-03-23</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">	15-04-23</span>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Recruitment Pipeline
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <ul class="mb-0">
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-sm rounded-circle bg-primary-transparent text-primary"><i class="fe fe-file-text "></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold"> Total Applications</a>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="text-primary fs-17">1,982</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-sm rounded-circle bg-secondary-transparent text-secondary"><i class="fe fe-user-plus "></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold"> Recruited</a>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="text-secondary fs-17">982</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-sm rounded-circle bg-success-transparent text-success"><i class="fe fe-file-plus "></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">  Short Listed</a>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="text-success fs-17">582</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-sm rounded-circle bg-warning-transparent text-warning"><i class="fe fe-zap-off "></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Rejected</a>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="text-warning fs-17">395</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-sm rounded-circle bg-danger-transparent text-danger"><i class="fe fe-slash "></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Blocked</a>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="text-danger fs-17">905</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-sm rounded-circle bg-success-transparent text-success"><i class="fe fe-file-plus "></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">  Short Listed</a>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="text-success fs-17">582</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-sm rounded-circle bg-info-transparent text-info"><i class="fe fe-monitor "></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Interviewed</a>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="text-info fs-17">145</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--End::row-2-->

                            <!--End::start-3 -->
                            <div class="row">
                                <div class="col-xxl-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Employees
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
                                                            <th scope="col"  class="text-center">S.No</th>
                                                            <th scope="col">Employee Name</th>
                                                            <th scope="col">Employee Id</th>
                                                            <th scope="col">Designation</th>
                                                            <th scope="col">Email</th>
                                                            <th scope="col">Contact</th>
                                                            <th scope="col">Status</th>
                                                            <th scope="col">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-center">
                                                                01
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/1.jpg')}}" class="rounded-1" alt="">
                                                                    </span>
                                                                    <div class="flex-1 flex-between pos-relative ms-2">
                                                                        <div class="">
                                                                            <a href="javascript:void(0);" class="fs-13 fw-semibold">Richard Dom</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-primary fs-14">#HTC012350</span>
                                                            </td>
                                                            <td>
                                                                <span class="">Team Leader</span>
                                                            </td>
                                                            <td>
                                                                <a href="javascript:void(0);" class="text-muted fs-12">richard116@demo.com</a>
                                                            </td>
                                                            <td>
                                                                <span class="">+0987654321</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-transparent">Active</span>
                                                            </td>
                                                            <td>
                                                                <div class="g-2">
                                                                    <a aria-label="anchor" class="btn  btn-primary-light btn-sm" data-bs-toggle="tooltip" data-bs-original-title="Edit">
                                                                        <span class="ri-pencil-line fs-14"></span>
                                                                    </a>
                                                                    <a aria-label="anchor" class="btn btn-danger-light btn-sm ms-2" data-bs-toggle="tooltip" data-bs-original-title="Delete">
                                                                        <span class="ri-delete-bin-7-line fs-14"></span>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">
                                                                02
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/2.jpg')}}" class="rounded-1" alt="">
                                                                    </span>
                                                                    <div class="flex-1 flex-between pos-relative ms-2">
                                                                        <div class="">
                                                                            <a href="javascript:void(0);" class="fs-13 fw-semibold">Kakashra Sri</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-primary fs-14">#HTC012351</span>
                                                            </td>
                                                            <td>
                                                                <span class="">Web Developer</span>
                                                            </td>
                                                            <td>
                                                                <a href="javascript:void(0);" class="text-muted fs-12">Kakashra987@demo.com</a>
                                                            </td>
                                                            <td>
                                                                <span class="">+0986548761</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-transparent">Active</span>
                                                            </td>
                                                            <td>
                                                                <div class="g-2">
                                                                    <a aria-label="anchor" class="btn  btn-primary-light btn-sm" data-bs-toggle="tooltip" data-bs-original-title="Edit">
                                                                        <span class="ri-pencil-line fs-14"></span>
                                                                    </a>
                                                                    <a aria-label="anchor" class="btn btn-danger-light btn-sm ms-2" data-bs-toggle="tooltip" data-bs-original-title="Delete">
                                                                        <span class="ri-delete-bin-7-line fs-14"></span>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">
                                                                03
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/3.jpg')}}" class="rounded-1" alt="">
                                                                    </span>
                                                                    <div class="flex-1 flex-between pos-relative ms-2">
                                                                        <div class="">
                                                                            <a href="javascript:void(0);" class="fs-13 fw-semibold">Nikki Jey</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-primary fs-14">#HTC012352</span>
                                                            </td>
                                                            <td>
                                                                <span class="">Project Manager</span>
                                                            </td>
                                                            <td>
                                                                <a href="javascript:void(0);" class="text-muted fs-12">Nikki654@demo.com</a>
                                                            </td>
                                                            <td>
                                                                <span class="">+0986548787</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-transparent">Active</span>
                                                            </td>
                                                            <td>
                                                                <div class="g-2">
                                                                    <a aria-label="anchor" class="btn  btn-primary-light btn-sm" data-bs-toggle="tooltip" data-bs-original-title="Edit">
                                                                        <span class="ri-pencil-line fs-14"></span>
                                                                    </a>
                                                                    <a aria-label="anchor" class="btn btn-danger-light btn-sm ms-2" data-bs-toggle="tooltip" data-bs-original-title="Delete">
                                                                        <span class="ri-delete-bin-7-line fs-14"></span>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">
                                                                04
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" class="rounded-1" alt="">
                                                                    </span>
                                                                    <div class="flex-1 flex-between pos-relative ms-2">
                                                                        <div class="">
                                                                            <a href="javascript:void(0);" class="fs-13 fw-semibold">Sasukey Ahuhi</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-primary fs-14">#HTC012353</span>
                                                            </td>
                                                            <td>
                                                                <span class="">Project Manager</span>
                                                            </td>
                                                            <td>
                                                                <a href="javascript:void(0);" class="text-muted fs-12">Sasukey986@demo.com</a>
                                                            </td>
                                                            <td>
                                                                <span class="">+0986548788</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-transparent">Active</span>
                                                            </td>
                                                            <td>
                                                                <div class="g-2">
                                                                    <a aria-label="anchor" class="btn  btn-primary-light btn-sm" data-bs-toggle="tooltip" data-bs-original-title="Edit">
                                                                        <span class="ri-pencil-line fs-14"></span>
                                                                    </a>
                                                                    <a aria-label="anchor" class="btn btn-danger-light btn-sm ms-2" data-bs-toggle="tooltip" data-bs-original-title="Delete">
                                                                        <span class="ri-delete-bin-7-line fs-14"></span>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">
                                                                05
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/5.jpg')}}" class="rounded-1" alt="">
                                                                    </span>
                                                                    <div class="flex-1 flex-between pos-relative ms-2">
                                                                        <div class="">
                                                                            <a href="javascript:void(0);" class="fs-13 fw-semibold">Xiong Yu</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-primary fs-14">#HTC012354</span>
                                                            </td>
                                                            <td>
                                                                <span class="">UI Developer</span>
                                                            </td>
                                                            <td>
                                                                <a href="javascript:void(0);" class="text-muted fs-12">Xiongu987@demo.com</a>
                                                            </td>
                                                            <td>
                                                                <span class="">+0986548988</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-transparent">Active</span>
                                                            </td>
                                                            <td>
                                                                <div class="g-2">
                                                                    <a aria-label="anchor" class="btn  btn-primary-light btn-sm" data-bs-toggle="tooltip" data-bs-original-title="Edit">
                                                                        <span class="ri-pencil-line fs-14"></span>
                                                                    </a>
                                                                    <a aria-label="anchor" class="btn btn-danger-light btn-sm ms-2" data-bs-toggle="tooltip" data-bs-original-title="Delete">
                                                                        <span class="ri-delete-bin-7-line fs-14"></span>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">
                                                                06
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/6.jpg')}}" class="rounded-1" alt="">
                                                                    </span>
                                                                    <div class="flex-1 flex-between pos-relative ms-2">
                                                                        <div class="">
                                                                            <a href="javascript:void(0);" class="fs-13 fw-semibold">Arifa Zed</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-primary fs-14">#HTC012355</span>
                                                            </td>
                                                            <td>
                                                                <span class="">Team Member</span>
                                                            </td>
                                                            <td>
                                                                <a href="javascript:void(0);" class="text-muted fs-12">Arifa432@demo.com</a>
                                                            </td>
                                                            <td>
                                                                <span class="">+0986548985</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-transparent">Active</span>
                                                            </td>
                                                            <td>
                                                                <div class="g-2">
                                                                    <a aria-label="anchor" class="btn  btn-primary-light btn-sm" data-bs-toggle="tooltip" data-bs-original-title="Edit">
                                                                        <span class="ri-pencil-line fs-14"></span>
                                                                    </a>
                                                                    <a aria-label="anchor" class="btn btn-danger-light btn-sm ms-2" data-bs-toggle="tooltip" data-bs-original-title="Delete">
                                                                        <span class="ri-delete-bin-7-line fs-14"></span>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">
                                                                07
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/7.jpg')}}" class="rounded-1" alt="">
                                                                    </span>
                                                                    <div class="flex-1 flex-between pos-relative ms-2">
                                                                        <div class="">
                                                                            <a href="javascript:void(0);" class="fs-13 fw-semibold">Jennifer Tab</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-primary fs-14">#HTC012356</span>
                                                            </td>
                                                            <td>
                                                                <span class="">Project Manager</span>
                                                            </td>
                                                            <td>
                                                                <a href="javascript:void(0);" class="text-muted fs-12">Jennifer543@demo.com</a>
                                                            </td>
                                                            <td>
                                                                <span class="">+09865489987</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-transparent">Active</span>
                                                            </td>
                                                            <td>
                                                                <div class="g-2">
                                                                    <a aria-label="anchor" class="btn  btn-primary-light btn-sm" data-bs-toggle="tooltip" data-bs-original-title="Edit">
                                                                        <span class="ri-pencil-line fs-14"></span>
                                                                    </a>
                                                                    <a aria-label="anchor" class="btn btn-danger-light btn-sm ms-2" data-bs-toggle="tooltip" data-bs-original-title="Delete">
                                                                        <span class="ri-delete-bin-7-line fs-14"></span>
                                                                    </a>
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
                            <!-- End::row-3 -->

                        </div>
                    </div>
                    <!-- END APP CONTENT -->

@endsection

@section('scripts')

        <!-- CHARTJS CHART JS -->
        <script src="{{asset('build/assets/libs/chart.js/chart.min.js')}}"></script>

        <!-- APEX CHARTS JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- CRM-DASHBOARD JS -->
        @vite('resources/assets/js/hrm-dashboard.js')


@endsection