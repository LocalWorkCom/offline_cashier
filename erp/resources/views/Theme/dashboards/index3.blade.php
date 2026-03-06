@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
                        <h4 class="fw-medium mb-0">Jobs-Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">Dashboards</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Jobs</li>
                        </ol>
                    </div>
                    <!-- PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start::row-1 -->
                                <div class="row row-cols-12">
                                    <div class="col-xxl-2 col-xl-4 col-md-4 col-sm-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="flex-between mb-3">
                                                    <span class="text-muted">Total Applicants</span>
                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted ms-2" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                    </ul>
                                                </div>
                                                <div class="d-flex align-items-end">
                                                    <div class="flex-1">
                                                        <h3 class="mb-2">1,123</h3>
                                                        <span class="badge bg-primary-transparent rounded-1">+1.26%</span>
                                                    </div>
                                                    <span class="ms-2 avatar bg-primary-transparent">
                                                        <i class="bx bx-user-circle fs-22"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-xl-4 col-md-4 col-sm-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="flex-between mb-3">
                                                    <span class="text-muted">Total Shortlisted</span>
                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted ms-2" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                    </ul>
                                                </div>
                                                <div class="d-flex align-items-end">
                                                    <div class="flex-1">
                                                        <h3 class="mb-2">7,123</h3>
                                                        <span class="badge bg-pink-transparent rounded-1">+4.26%</span>
                                                    </div>
                                                    <span class="ms-2 avatar bg-pink-transparent">
                                                        <i class="bx bx-user-minus  fs-22"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-xl-4 col-md-4 col-sm-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="flex-between mb-3">
                                                    <span class="text-muted">Hired Candidates</span>
                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted ms-2" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                    </ul>
                                                </div>
                                                <div class="d-flex align-items-end">
                                                    <div class="flex-1">
                                                        <h3 class="mb-2">8,123</h3>
                                                        <span class="badge bg-info-transparent rounded-1">+6.26%</span>
                                                    </div>
                                                    <span class="ms-2 avatar bg-info-transparent">
                                                        <i class="bx bx-user-plus fs-22"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-xl-4 col-md-4 col-sm-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="flex-between mb-3">
                                                    <span class="text-muted">Rejected Applicants</span>
                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted ms-2" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                    </ul>
                                                </div>
                                                <div class="d-flex align-items-end">
                                                    <div class="flex-1">
                                                        <h3 class="mb-2">8,123</h3>
                                                        <span class="badge bg-danger-transparent rounded-1">9.26%</span>
                                                    </div>
                                                    <span class="ms-2 avatar bg-danger-transparent">
                                                        <i class="bx bx-user-x  fs-22"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-xl-4 col-md-4 col-sm-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="flex-between mb-3">
                                                    <span class="text-muted">Total Locations</span>
                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted ms-2" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                    </ul>
                                                </div>
                                                <div class="d-flex align-items-end">
                                                    <div class="flex-1">
                                                        <h3 class="mb-2">5,123</h3>
                                                        <span class="badge bg-warning-transparent rounded-1">886</span>
                                                    </div>
                                                    <span class="ms-2 avatar bg-warning-transparent">
                                                        <i class="bx bx-location-plus fs-22"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-xl-4 col-md-4 col-sm-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="flex-between mb-3">
                                                    <span class="text-muted">Resume  Upload</span>
                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted ms-2" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                        <li><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                    </ul>
                                                </div>
                                                <div class="d-flex align-items-end">
                                                    <div class="flex-1">
                                                        <h3 class="mb-2">5,123</h3>
                                                        <span class="badge bg-teal-transparent rounded-1">9,686</span>
                                                    </div>
                                                    <span class="ms-2 avatar bg-teal-transparent">
                                                        <i class="bx bx-file  fs-22"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <!--End::row-1 -->

                            <!-- Start::row-2 -->
                            <div class="row">
                                <div class="col-xxl-3 col-xl-5 col-md-5">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-header justify-content-between">
                                            <h6 class="card-title">Open Jobs</h6>
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
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="pe-3">
                                                            <span class="avatar avatar-md radius-5 bg-primary-transparent text-primary"><i class="bi bi-phone-fill fs-16"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Sr. Mobile App Developer</a>
                                                                <p  class="tx-inverse  text-muted mb-0">16 Applicants</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fs-12 text-muted">1 day ago</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="pe-3">
                                                            <span class="avatar avatar-md radius-5 bg-secondary-transparent text-secondary"><i class="bi bi-briefcase-fill fs-16"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Data Scientist Trainee</a>
                                                                <p  class="tx-inverse  text-muted mb-0">773 Applicants</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fs-12 text-muted">3 day ago</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="pe-3">
                                                            <span class="avatar avatar-md radius-5 bg-info-transparent text-info"><i class="bi bi-capslock-fill fs-16"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="flex-1">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Sr. Backend Developer</a>
                                                                <p  class="tx-inverse  text-muted mb-0">16 Applicants</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fs-12 text-muted">1 week ago</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="pe-3">
                                                            <span class="avatar avatar-md radius-5 bg-success-transparent text-success"><i class="bi bi-bar-chart-fill fs-16"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="flex-1">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Data Visualization Engineer</a>
                                                                <p  class="tx-inverse  text-muted mb-0">11 Applicants</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fs-12 text-muted">1 month ago</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="pe-3">
                                                            <span class="avatar avatar-md radius-5 bg-orange-transparent text-orange"><i class="bi bi-capslock-fill fs-16"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="flex-1">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Sr. Backend Developer</a>
                                                                <p  class="tx-inverse  text-muted mb-0">16 Applicants</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fs-12 text-muted">1 month ago</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="pe-3">
                                                            <span class="avatar avatar-md radius-5 bg-secondary-transparent text-secondary"><i class="bi bi-calendar3-range-fill fs-16"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="flex-1">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Dashboard Trainee</a>
                                                                <p  class="tx-inverse  text-muted mb-0">16 Applicants</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fs-12 text-muted">14month ago</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-6  col-xl-7 col-md-7">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">Project Analysis</div>
                                            <div class="dropdown">
                                                <a aria-label="anchor" href="javascript:void(0);" class="btn btn-outline-light btn-icons btn-sm text-muted" data-bs-toggle="dropdown">
                                                    <i class="fe fe-more-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Action</a></li>
                                                    <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Another action</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Something else here</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div id="applicantStats"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-5 col-md-5">
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
                                                            <span class="avatar avatar-md rounded-circle bg-primary-transparent text-primary"><i class="fe fe-file-text fs-14"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold"> Total Applications</a>
                                                                <p class="text-muted mb-0 fw-normal">This month</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fw-semibiold fs-17">1,982</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-md rounded-circle bg-secondary-transparent text-secondary"><i class="fe fe-user-plus fs-14"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold"> Recruited</a>
                                                                <p class="text-muted mb-0 fw-normal">This month</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fw-semibiold fs-17">982</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-md rounded-circle bg-success-transparent text-success"><i class="fe fe-file-plus fs-14"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">  Short Listed</a>
                                                                <p class="text-muted mb-0 fw-normal">This month</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fw-semibiold fs-17">582</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-md rounded-circle bg-warning-transparent text-warning"><i class="fe fe-zap-off fs-14"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Rejected</a>
                                                                <p class="text-muted mb-0 fw-normal">This month</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fw-semibiold fs-17">395</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-md rounded-circle bg-danger-transparent text-danger"><i class="fe fe-slash fs-14"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Blocked</a>
                                                                <p class="text-muted mb-0 fw-normal">This month</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fw-semibiold fs-17">905</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="">
                                                    <div class="d-flex align-items-center">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="me-2">
                                                            <span class="avatar avatar-md rounded-circle bg-info-transparent text-info"><i class="fe fe-monitor fs-14"></i></span>
                                                        </a>
                                                        <div class="flex-1 flex-between pos-relative">
                                                            <div class="">
                                                                <a href="javascript:void(0);" class="fs-13 fw-semibold">Interviewed</a>
                                                                <p class="text-muted mb-0 fw-normal">This month</p>
                                                            </div>
                                                            <div class="min-w-fit-content text-end">
                                                                <span class="fw-semibiold fs-17">145</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-xl-7 col-md-7">
                                    <div class="card custom-card overflow-hidden justify-content-between">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Top Referrers
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
                                                <table class="table text-nowrap">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col" class="ps-4">Browser</th>
                                                            <th scope="col">Page Views</th>
                                                            <th scope="col">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded bg-primary">
                                                                            <i class="ri-chrome-line"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            Chrome
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>37,098<i class="bi bi-caret-up-fill ms-2 fs-11 text-success"></i></td>
                                                            <td class="border-top-0">
                                                                <div class="progress progress-sm" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                                    <div class="progress-bar bg-primary" style="width: 65%">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded bg-secondary">
                                                                            <i class="ri-safari-line"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            safari
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>37,098<i class="bi bi-caret-up-fill ms-2 fs-11 text-success"></i></td>
                                                            <td class="border-top-0">
                                                                <div class="progress progress-sm" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                                    <div class="progress-bar bg-primary" style="width: 60%">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded bg-info">
                                                                            <i class="ri-firefox-line"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            Firefox
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>54,085<i class="bi bi-caret-down-fill ms-2 fs-11 text-danger"></i></td>
                                                            <td class="border-top-0">
                                                                <div class="progress progress-sm" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                                    <div class="progress-bar bg-primary" style="width: 40%">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded bg-warning">
                                                                            <i class="ri-opera-line"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            Opera
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>74,085<i class="bi bi-caret-up-fill ms-2 fs-11 text-success"></i></td>
                                                            <td class="border-top-0">
                                                                <div class="progress progress-sm" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                                    <div class="progress-bar bg-primary" style="width: 50%">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded bg-success">
                                                                            <i class="ri-edge-line"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            Edge
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>54,085<i class="bi bi-caret-down-fill ms-2 fs-11 text-danger"></i></td>
                                                            <td class="border-top-0">
                                                                <div class="progress progress-sm" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                                                    <div class="progress-bar bg-primary" style="width: 45%">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-5 col-xl-6 col-md-6">
                                    <div class="card custom-card overflow-hidden justify-content-between">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Recent Applicants
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
                                                <table class="table text-nowrap">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col" class="ps-4">Applicant</th>
                                                            <th scope="col">Mail</th>
                                                            <th scope="col">Role</th>
                                                            <th scope="col">Date</th>
                                                            <th scope="col">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            Nasiha
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>nasiha293@demo</td>
                                                            <td>Data Analyst</td>
                                                            <td>13-06-22</td>
                                                            <td>
                                                                <span class="badge bg-success-transparent rounded-1">Hired</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            Hasi Nah
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>hasihas888@demo</td>
                                                            <td>Executive Officer</td>
                                                            <td>03-05-22</td>
                                                            <td>
                                                                <span class="badge bg-danger-transparent rounded-1">Rejected</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            David
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>david@demo</td>
                                                            <td>Data Analyst</td>
                                                            <td>13-01-23</td>
                                                            <td>
                                                                <span class="badge bg-warning-transparent rounded-1">Screened</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            Jack Bruce
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>Jack5236@demo</td>
                                                            <td>Developer</td>
                                                            <td>16-01-23</td>
                                                            <td>
                                                                <span class="badge bg-info-transparent rounded-1">Interviewed</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-sm avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="fw-semibold">
                                                                            Sarah Hny
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>Sarah123@demo</td>
                                                            <td>Data Scientist</td>
                                                            <td>07-02-23</td>
                                                            <td>
                                                                <span class="badge bg-success-transparent rounded-1">Applied</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-md-6">
                                    <div class="card custom-card overflow-hidden justify-content-between">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Acceptance Ratio
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
                                        <div class="card-body py-0">
                                            <div id="careerPageStats"></div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex justify-content-evenly">
                                                    <div class="d-flex">
                                                        <div class="me-2">
                                                            <span class="avatar avatar-md rounded-circle bg-primary  text-white"><i class="fe fe-bar-chart fs-14"></i></span>
                                                        </div>
                                                        <div class="">
                                                            <p class="mb-0 font-weight-semibold">Accepted</p>
                                                            <span class="tx-primary ">+2.6%</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <div class="me-2">
                                                            <span class="avatar avatar-md rounded-circle bg-primary-transparent text-primary"><i class="fe fe-corner-down-left fs-14"></i></span>
                                                        </div>
                                                        <div class="">
                                                            <p class="mb-0 font-weight-semibold">Rejected</p>
                                                            <span class="tx-danger ">-1.6%</span>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End::row-2 -->

                            <!-- Start::row-3 -->
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Interview Schedule
                                            </div>
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <input class="form-control form-control-sm" type="text" placeholder="Search Here" aria-label=" example">
                                                </div>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-primary btn-wave waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Sort By<i class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
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
                                                <table class="table text-nowrap table-hover border table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">S.NO</th>
                                                            <th scope="col">Candidate</th>
                                                            <th scope="col">Designation</th>
                                                            <th scope="col">Mail</th>
                                                            <th scope="col">Time</th>
                                                            <th scope="col">Date</th>
                                                            <th scope="col">Status</th>
                                                            <th scope="col">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>01</td>
                                                            <td>
                                                                <div class="d-flex align-items-center fw-semibold">
                                                                    <span class="avatar avatar-sm me-2 avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                                    </span>Mayor Kelly
                                                                </div>
                                                            </td>
                                                            <td><span class="badge bg-primary-transparent">Team Lead</span></td>
                                                            <td>mayorkelly@gmail.com</td>
                                                            <td>
                                                                <span class="ms-1">10.00am</span>
                                                            </td>
                                                            <td>Sep 15 - Oct 12, 2023</td>
                                                            <td>
                                                                <span class="badge badge-sm bg-warning-transparent rounded-pill">Processing</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-15">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-info-light"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-wave waves-effect waves-light btn-sm btn-primary-light"><i class="ri-edit-line"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>02</td>
                                                            <td>
                                                                <div class="d-flex align-items-center fw-semibold">
                                                                    <span class="avatar avatar-sm me-2 avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                                                    </span>Andrew Garfield
                                                                </div>
                                                            </td>
                                                            <td><span class="badge bg-secondary-transparent">Director</span></td>
                                                            <td>andrewgarfield@gmail.com</td>
                                                            <td><span class="ms-1">	14:00am</span>
                                                            </td>
                                                            <td>Apr 10 - Dec 12, 2023</td>
                                                            <td>
                                                                <span class="badge badge-sm bg-secondary-transparent rounded-pill">Not Started</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-15">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon waves-effect waves-light btn-sm btn-info-light"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon waves-effect waves-light btn-sm btn-primary-light"><i class="ri-edit-line"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>03</td>
                                                            <td>
                                                                <div class="d-flex align-items-center fw-semibold">
                                                                    <span class="avatar avatar-sm me-2 avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                                    </span>Simon Cowel
                                                                </div>
                                                            </td>
                                                            <td><span class="badge bg-success-transparent">Manager</span></td>
                                                            <td>simoncowel234@gmail.com</td>
                                                            <td><span class="ms-1">	12:00am</span>
                                                            </td>
                                                            <td>Sep 15 - Oct 12, 2023</td>
                                                            <td>
                                                                <span class="badge badge-sm bg-success-transparent rounded-pill">Comleted</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-15">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon waves-effect waves-light btn-sm btn-info-light"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon waves-effect waves-light btn-sm btn-primary-light"><i class="ri-edit-line"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>04</td>
                                                            <td>
                                                                <div class="d-flex align-items-center fw-semibold">
                                                                    <span class="avatar avatar-sm me-2 avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                                    </span>Mirinda Hers
                                                                </div>
                                                            </td>
                                                            <td><span class="badge bg-danger-transparent">Employee</span></td>
                                                            <td>mirindahers@gmail.com</td>
                                                            <td><span class="ms-1">09:00am</span>
                                                            </td>
                                                            <td>Apr 10 - Dec 12, 2023</td>
                                                            <td>
                                                                <span class="badge badge-sm bg-warning-transparent rounded-pill">Processing</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-15">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon waves-effect waves-light btn-sm btn-info-light"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon waves-effect waves-light btn-sm btn-primary-light"><i class="ri-edit-line"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>05</td>
                                                            <td>
                                                                <div class="d-flex align-items-center fw-semibold">
                                                                    <span class="avatar avatar-sm me-2 avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/16.jpg')}}" alt="img">
                                                                    </span>Andrew Garfield
                                                                </div>
                                                            </td>
                                                            <td><span class="badge bg-warning-transparent">Director</span></td>
                                                            <td>andrewgarfield@gmail.com</td>
                                                            <td><span class="ms-1">	10:00am</span></td>
                                                            <td>Jun 10 - Dec 12, 2022</td>
                                                            <td>
                                                                <span class="badge badge-sm bg-success-transparent rounded-pill">Completed</span>
                                                            </td>
                                                            <td>
                                                                <div class="hstack gap-2 fs-15">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon waves-effect waves-light btn-sm btn-info-light"><i class="ri-download-2-line"></i></a>
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon waves-effect waves-light btn-sm btn-primary-light"><i class="ri-edit-line"></i></a>
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


        <!-- APEX CHARTS JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- JOBS-DASHBOARD JS -->
        @vite('resources/assets/js/jobs-dashboard.js')


@endsection