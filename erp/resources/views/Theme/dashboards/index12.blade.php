@extends('layouts.master')

@section('styles')

        <!-- SWIPER CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/swiper/swiper-bundle.min.css')}}">
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
                        <h4 class="fw-medium mb-0">Course-Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">Dashboards</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Course</li>
                        </ol>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start::row-1 -->
                                <div class="row">
                                    <div class="col-xxl-5">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="card">
                                                    <div class="card-body pos-relative">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                        <div class="d-flex align-items-center">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="pe-3 d-block">
                                                                <span class="avatar radius-5 bg-primary-transparent text-primary"><i class="ti ti-wallet fs-18"></i></span>
                                                            </a>
                                                            <div class="flex-1 font-weight-semibold">
                                                                <h4 class="mb-0">$98,450</h4>
                                                                <div class="flex-between">
                                                                    <span class="text-muted fs-14">YTD Earning</span>
                                                                    <span class="text-success fs-12"><i class="ti ti-trending-up fs-16 me-1"></i>+2.02%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="card">
                                                    <div class="card-body pos-relative">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                        <div class="d-flex align-items-center">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="me-3 d-block">
                                                                <span class="avatar radius-5 bg-secondary-transparent tx-secondary"><i class="ti ti-users fs-18"></i></span>
                                                            </a>
                                                            <div class="flex-1 font-weight-semibold">
                                                                <h4 class="mb-0">15,379</h4>
                                                                <div class="flex-between">
                                                                    <span class="text-muted fs-14">Total Students</span>
                                                                    <span class="text-danger fs-12"><i class="ti ti-trending-down fs-16 me-1"></i>+2.02%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="card">
                                                    <div class="card-body pos-relative">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                        <div class="d-flex align-items-center">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="me-3 d-block">
                                                                <span class="avatar radius-5 bg-warning-transparent tx-warning"><i class="ti ti-id fs-18"></i></span>
                                                            </a>
                                                            <div class="flex-1 font-weight-semibold">
                                                                <h4 class="mb-0">377</h4>
                                                                <div class="flex-between">
                                                                    <span class="text-muted fs-14">Total Instructors</span>
                                                                    <span class="text-danger fs-12"><i class="ti ti-trending-down fs-16 me-1"></i>+2.02%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="card">
                                                    <div class="card-body pos-relative">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                        <div class="d-flex align-items-center">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="me-3 d-block">
                                                                <span class="avatar radius-5 bg-pink-transparent text-pink"><i class="ti ti-gift fs-18"></i></span>
                                                            </a>
                                                            <div class="flex-1 font-weight-semibold">
                                                                <h4 class="mb-0">11,016</h4>
                                                                <div class="flex-between">
                                                                    <span class="text-muted fs-14">Total Courses</span>
                                                                    <span class="text-success fs-12"><i class="ti ti-trending-up fs-16 me-1"></i>+2.02%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="card custom-card">
                                                    <div class="card-header justify-content-between">
                                                        <div class="card-title">
                                                            New Students
                                                        </div>
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                                View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" role="menu">
                                                                <li><a class="dropdown-item border-bottom" href="javascript:void(0);">Today</a></li>
                                                                <li><a class="dropdown-item border-bottom" href="javascript:void(0);">This Week</a></li>
                                                                <li><a class="dropdown-item border-bottom" href="javascript:void(0);">Last Week</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover text-nowrap table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">Name</th>
                                                                        <th scope="col">Courses</th>
                                                                        <th scope="col">Completed</th>
                                                                        <th scope="col">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td >
                                                                            <div class="d-flex align-items-center">
                                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                                </a>
                                                                                <div class="flex-1 pos-relative">
                                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                                    <p class="mb-0 fs-14">Richard Dom</p>
                                                                                    <span class="text-muted fs-12">richarddom1116@demo.com</span>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-center"> 08</td>
                                                                        <td class="text-center"><span class="badge badge-sm rounded-pill bg-warning">02</span></td>
                                                                        <td class="">
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
                                                                        <td >
                                                                            <div class="d-flex align-items-center">
                                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                                    <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                                </a>
                                                                                <div class="flex-1 pos-relative">
                                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                                    <p class="mb-0 fs-14">Aliz Varna</p>
                                                                                    <span class="text-muted fs-12">AlizVarn876a@demo.com</span>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-center"> 10</td>
                                                                        <td class="text-center"><span class="badge badge-sm rounded-pill bg-success">05</span></td>
                                                                        <td class="">
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
                                                                        <td >
                                                                            <div class="d-flex align-items-center">
                                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                                    <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                                </a>
                                                                                <div class="flex-1 pos-relative">
                                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                                    <p class="mb-0 fs-14">Joberon Varith</p>
                                                                                    <span class="text-muted fs-12">Varith908@demo.com</span>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-center"> 07</td>
                                                                        <td class="text-center"><span class="badge badge-sm rounded-pill bg-info">05</span></td>
                                                                        <td class="">
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
                                                                        <td >
                                                                            <div class="d-flex align-items-center">
                                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                                    <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                                </a>
                                                                                <div class="flex-1 pos-relative">
                                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                                    <p class="mb-0 fs-14">Alizbeth John</p>
                                                                                    <span class="text-muted fs-12">Alizbeth123@demo.com</span>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-center"> 10</td>
                                                                        <td class="text-center"><span class="badge badge-sm rounded-pill bg-danger">03</span></td>
                                                                        <td class="">
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
                                                                        <td >
                                                                            <div class="d-flex align-items-center">
                                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                                    <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                                </a>
                                                                                <div class="flex-1 pos-relative">
                                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                                    <p class="mb-0 fs-14">Varjon Aryem</p>
                                                                                    <span class="text-muted fs-12">VarjonAry123m@demo.com</span>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-center"> 10</td>
                                                                        <td class="text-center"><span class="badge badge-sm rounded-pill bg-warning">06</span></td>
                                                                        <td class="">
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-7">
                                        <div class="row">
                                            <div class="col-xxl-12">
                                                <div class="card custom-card overflow-hidden">
                                                    <div class="card-header flex-between">
                                                        <div class="card-title">Statistics</div>
                                                        <div class="btn-group">
                                                            <a href="javascript:void(0);" class="btn btn-sm btn-primary-light">1H</a>
                                                            <a href="javascript:void(0);" class="btn btn-sm btn-primary-light">6H</a>
                                                            <a href="javascript:void(0);" class="btn btn-sm btn-primary-light">12H</a>
                                                            <a href="javascript:void(0);" class="btn btn-sm btn-primary">1D</a>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="CourseStatistics" class=""></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xxl-12 ">
                                                <div class="card custom-card overflow-hidden">
                                                    <div class="card-header flex-between justify-content-between">
                                                        <div class="card-title">Top Category</div>
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                                View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" role="menu">
                                                                <li><a class="dropdown-item border-bottom" href="javascript:void(0);">Today</a></li>
                                                                <li><a class="dropdown-item border-bottom" href="javascript:void(0);">This Week</a></li>
                                                                <li><a class="dropdown-item border-bottom" href="javascript:void(0);">Last Week</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-xl-3 col-sm-6 my-1">
                                                                <a href="javascript:void(0);" class="category-link font-weight-semibold">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="category-svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 5h4v6H5zm10 8h4v6h-4zM5 17h4v2H5zM15 5h4v2h-4z" opacity=".3"/><path d="M3 13h8V3H3v10zm2-8h4v6H5V5zm8 16h8V11h-8v10zm2-8h4v6h-4v-6zM13 3v6h8V3h-8zm6 4h-4V5h4v2zM3 21h8v-6H3v6zm2-4h4v2H5v-2z"/></svg>
                                                                    <p class="fs-14 mb-0 text-center">UI / UX Design</p>
                                                                    <span class="fs-11 text-center d-block">1000+ Courses</span>
                                                                </a>
                                                            </div>
                                                            <div class="col-xl-3 col-sm-6 my-1">
                                                                <a href="javascript:void(0);" class="category-link font-weight-semibold">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="category-svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><g><rect fill="none" height="24" width="24"/></g><g><g opacity=".3"><path d="M6.71,18.71c-0.28,0.28-2.17,0.76-2.17,0.76s0.47-1.88,0.76-2.17C5.47,17.11,5.72,17,6,17c0.55,0,1,0.45,1,1 C7,18.28,6.89,18.53,6.71,18.71z M7.41,10.83L5.5,10.01l1.97-1.97l1.44,0.29C8.34,9.16,7.83,10.03,7.41,10.83z M13.99,18.5 l-0.82-1.91c0.8-0.42,1.67-0.93,2.49-1.5l0.29,1.44L13.99,18.5z M19.99,4.01c0,0-3.55-0.69-8.23,3.99 c-1.32,1.32-2.4,3.38-2.73,4.04l2.93,2.93c0.65-0.32,2.71-1.4,4.04-2.73C20.68,7.56,19.99,4.01,19.99,4.01z M15,11 c-1.1,0-2-0.9-2-2c0-1.1,0.9-2,2-2s2,0.9,2,2C17,10.1,16.1,11,15,11z"/></g><g><path d="M6,15c-0.83,0-1.58,0.34-2.12,0.88C2.7,17.06,2,22,2,22s4.94-0.7,6.12-1.88C8.66,19.58,9,18.83,9,18C9,16.34,7.66,15,6,15 z M6.71,18.71c-0.28,0.28-2.17,0.76-2.17,0.76s0.47-1.88,0.76-2.17C5.47,17.11,5.72,17,6,17c0.55,0,1,0.45,1,1 C7,18.28,6.89,18.53,6.71,18.71z M17.42,13.65L17.42,13.65c6.36-6.36,4.24-11.31,4.24-11.31s-4.95-2.12-11.31,4.24l-2.49-0.5 C7.21,5.95,6.53,6.16,6.05,6.63L2,10.69l5,2.14L11.17,17l2.14,5l4.05-4.05c0.47-0.47,0.68-1.15,0.55-1.81L17.42,13.65z M7.41,10.83L5.5,10.01l1.97-1.97l1.44,0.29C8.34,9.16,7.83,10.03,7.41,10.83z M13.99,18.5l-0.82-1.91 c0.8-0.42,1.67-0.93,2.49-1.5l0.29,1.44L13.99,18.5z M16,12.24c-1.32,1.32-3.38,2.4-4.04,2.73l-2.93-2.93 c0.32-0.65,1.4-2.71,2.73-4.04c4.68-4.68,8.23-3.99,8.23-3.99S20.68,7.56,16,12.24z M15,11c1.1,0,2-0.9,2-2s-0.9-2-2-2s-2,0.9-2,2 S13.9,11,15,11z"/></g></g></svg>
                                                                    <p class="fs-14 mb-0 text-center">Digital Marketing</p>
                                                                    <span class="fs-11 text-center  d-block">90+ Courses</span>
                                                                </a>
                                                            </div>
                                                            <div class="col-xl-3 col-sm-6 my-1">
                                                                <a href="javascript:void(0);" class="category-link font-weight-semibolda active">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="category-svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><g><rect fill="none" height="24" width="24"/></g><g><g><path d="M4,18h16V8H4V18z M12,15h6v2h-6V15z M6.09,10.41L7.5,9l4,4l-4,4l-1.41-1.41L8.67,13L6.09,10.41z" opacity=".3"/><rect height="2" width="6" x="12" y="15"/><path d="M20,4H4C2.89,4,2,4.9,2,6v12c0,1.1,0.89,2,2,2h16c1.1,0,2-0.9,2-2V6C22,4.9,21.11,4,20,4z M20,18H4V8h16V18z"/><polygon points="7.5,17 11.5,13 7.5,9 6.09,10.41 8.67,13 6.09,15.59"/></g></g></svg>
                                                                    <p class="fs-14 mb-0 text-center">Web Development</p>
                                                                    <span class="fs-11 text-center  d-block">250+ Courses</span>
                                                                </a>
                                                            </div>
                                                            <div class="col-xl-3 col-sm-6 my-1">
                                                                <a href="javascript:void(0);" class="category-link font-weight-semibold">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="category-svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><rect fill="none" height="24" width="24"/><g opacity=".3"><path d="M10,5h4v14h-4V5z M4,11h4v8H4V11z M20,19h-4v-6h4V19z"/></g><g><path d="M16,11V3H8v6H2v12h20V11H16z M10,5h4v14h-4V5z M4,11h4v8H4V11z M20,19h-4v-6h4V19z"/></g></svg>
                                                                    <p class="fs-14 mb-0 text-center ">Stocks &amp; Trading</p>
                                                                    <span class="fs-11 text-center  d-block">100+ Courses</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <!-- End::row-1 -->

                            <!-- Start::row-2 -->
                            <div class="row">
                                <div class="col-xxl-3 col-lg-6">
                                    <div class="card custom-card">
                                        <div class="card-header flex-between justify-content-between">
                                            <div class="card-title">Ongoing Courses</div>
                                            <div class="dropdown">
                                                <a href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                    View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                                </a>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Today</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">This Week</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <ul class="mb-2">
                                                <li>
                                                    <div class="d-flex mb-4 align-items-center">
                                                        <div class="me-2">
                                                            <span class="avatar avatar-md br-5 bg-success-transparent text-success me-3"><i class="bx bx-store"></i></span>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <div class="d-flex justify-content-between">
                                                                <h6 class="fw-semibold">
                                                                    Marketing
                                                                </h6>
                                                                <div>
                                                                    <p class="mb-0 fs-13 text-muted">
                                                                        <i class="fe fe-arrow-up-right me-1 text-success brround d-inline-block"></i>93.0%
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 93%;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex mb-4 align-items-center">
                                                        <div class="me-2">
                                                            <span class="avatar avatar-md br-5 bg-purple-transparent text-purple me-3"><i class="bx bx-layout"></i></span>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <div class="d-flex justify-content-between">
                                                                <h6 class="fw-semibold">
                                                                    UI/UX Design
                                                                </h6>
                                                                <div>
                                                                    <p class="mb-0 fs-13 text-muted">
                                                                        <i class="fe fe-arrow-up-right  me-1 text-success brround d-inline-block"></i>75.0%
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-purple" style="width: 70%;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex mb-4 align-items-center">
                                                        <div class="me-2">
                                                            <span class="avatar avatar-md br-5 bg-warning-transparent text-warning me-3"><i class="bx bx-code"></i></span>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <div class="d-flex justify-content-between">
                                                                <h6 class="fw-semibold">
                                                                    Java Programming
                                                                </h6>
                                                                <div>
                                                                    <p class="mb-0 fs-13 text-muted">
                                                                        <i class="fe fe-arrow-up-right me-1 text-success brround d-inline-block"></i>85.0%
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 85%;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex mb-4 align-items-center">
                                                        <div class="me-2">
                                                            <span class="avatar avatar-md br-5 bg-pink-transparent text-pink me-3"><i class="bx bx-layer "></i></span>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <div class="d-flex justify-content-between">
                                                                <h6 class="fw-semibold">
                                                                    Ui Development
                                                                </h6>
                                                                <div>
                                                                    <p class="mb-0 fs-13 text-muted">
                                                                        <i class="fe fe-arrow-up-right me-1 text-success brround d-inline-block"></i>73.0%
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-pink" style="width: 73%;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <span class="avatar avatar-md br-5 bg-info-transparent text-info me-3"><i class="bx bx-recycle"></i></span>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <div class="d-flex justify-content-between">
                                                                <h6 class="fw-semibold">
                                                                React Js
                                                                </h6>
                                                                <div>
                                                                    <p class="mb-0 fs-13 text-muted">
                                                                        <i class="fe fe-arrow-up-right me-1 text-success brround d-inline-block"></i>60.0%
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: 60%;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-6">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-header flex-between justify-content-between">
                                            <div class="card-title">Top Instructors</div>
                                            <div class="dropdown">
                                                <a href="javascript:void(0);" class="btn-outline-light btn btn-sm text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                    View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                                </a>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a class="dropdown-item border-bottom" href="javascript:void(0);">Today</a></li>
                                                    <li><a class="dropdown-item border-bottom" href="javascript:void(0);">This Week</a></li>
                                                    <li><a class="dropdown-item border-bottom" href="javascript:void(0);">Last Week</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-body">

                                    <ul class="list-unstyled mb-0 font-weight-semibold">
                                        <li class="list-item mb-4">
                                            <div class="flex-between">
                                                <div class="flex-1 d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="pe-2 d-block">
                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img" class="avatar avatar-md">
                                                    </a>
                                                    <div class="flex-1 pos-relative">
                                                        <a aria-label="anchor" href="javscript:void(0);" class="masked-link"></a>
                                                        <h6 class="fs-14 mb-0">John Hny</h6>
                                                        <span class="fs-12 text-muted">M.tech</span>
                                                    </div>
                                                </div>
                                                <div class="min-w-fit-content text-end">
                                                    <a href="javascript:void(0);" class="d-block text-primary op-9">321 Courses</a>
                                                    <span class="fs-12 tx-muted">Digital Marketing</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-item mb-4">
                                            <div class="flex-between">
                                                <div class="flex-1 d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="pe-2 d-block">
                                                        <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img" class="avatar avatar-md">
                                                    </a>
                                                    <div class="flex-1 pos-relative">
                                                        <a aria-label="anchor" href="javscript:void(0);" class="masked-link"></a>
                                                        <h6 class="fs-14 mb-0">Mortal Yun</h6>
                                                        <span class="fs-12 text-muted">Phd</span>
                                                    </div>
                                                </div>
                                                <div class="min-w-fit-content text-end">
                                                    <a href="javascript:void(0);" class="d-block text-primary op-9">25 Courses</a>
                                                    <span class="fs-12 tx-muted">Stocks &amp; Trading</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-item mb-4">
                                            <div class="flex-between">
                                                <div class="flex-1 d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="pe-2 d-block">
                                                        <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img" class="avatar avatar-md">
                                                    </a>
                                                    <div class="flex-1 pos-relative">
                                                        <a aria-label="anchor" href="javscript:void(0);" class="masked-link"></a>
                                                        <h6 class="fs-14 mb-0">Trex Con</h6>
                                                        <span class="fs-12 text-muted">MBBS</span>
                                                    </div>
                                                </div>
                                                <div class="min-w-fit-content text-end">
                                                    <a href="javascript:void(0);" class="d-block text-primary op-9">39 Courses</a>
                                                    <span class="fs-12 tx-muted">Science</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-item mb-4">
                                            <div class="flex-between">
                                                <div class="flex-1 d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="pe-2 d-block">
                                                        <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="img" class="avatar avatar-md">
                                                    </a>
                                                    <div class="flex-1 pos-relative">
                                                        <a aria-label="anchor" href="javscript:void(0);" class="masked-link"></a>
                                                        <h6 class="fs-14 mb-0">Saiu Sarah</h6>
                                                        <span class="fs-12 text-muted">Phd</span>
                                                    </div>
                                                </div>
                                                <div class="min-w-fit-content text-end">
                                                    <a href="javascript:void(0);" class="d-block text-primary op-9">11 Courses</a>
                                                    <span class="fs-12 tx-muted">Science</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-item">
                                            <div class="flex-between">
                                                <div class="flex-1 d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="pe-2 d-block">
                                                        <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img" class="avatar avatar-md">
                                                    </a>
                                                    <div class="flex-1 pos-relative">
                                                        <a aria-label="anchor" href="javscript:void(0);" class="masked-link"></a>
                                                        <h6 class="fs-14 mb-0">Ion Hao</h6>
                                                        <span class="fs-12 text-muted">M.tech</span>
                                                    </div>
                                                </div>
                                                <div class="min-w-fit-content text-end">
                                                    <a href="javascript:void(0);" class="d-block text-primary op-9">124 Courses</a>
                                                    <span class="fs-12 tx-muted">Web Development</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-6">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Payouts
                                            </div>
                                            <div class="d-flex flex-wrap">
                                                <div class="btn-group btn-group-sm" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" name="payoutsRadio" id="btnradio5">
                                                    <label class="btn btn-outline-primary mb-0" for="btnradio5">1M</label>

                                                    <input type="radio" class="btn-check" name="payoutsRadio" id="btnradio6">
                                                    <label class="btn btn-outline-primary mb-0" for="btnradio6">6M</label>

                                                    <input type="radio" class="btn-check" name="payoutsRadio" id="btnradio7" checked>
                                                    <label class="btn btn-outline-primary mb-0" for="btnradio7">1Y</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="px-4 mt-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="avatar radius-5 bg-primary-transparent text-primary"><i class="ti ti-cash fs-18"></i></span>
                                                </div>
                                                <div class="flex-1 font-weight-semibold">
                                                    <p class="mb-1 text-muted fs-12">Total Payouts</p>
                                                    <h5 class="text-primary">$89,700</h5>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="avatar radius-5 bg-secondary-transparent text-secondary"><i class="ti ti-x fs-18"></i></span>
                                                </div>
                                                <div class="flex-1 font-weight-semibold">
                                                    <p class="mb-1 text-muted fs-12">Unpaid</p>
                                                    <h5 class=" text-secondary">$21,300</h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="payoutsReport" class="sparkline-chart"></div>
                                        </div>
                                </div>
                                <div class="col-xxl-3 col-lg-6">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Upcoming schedule
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0 Upcoming-courses-schedule">
                                                <li class="list-item mb-4">
                                                    <div class=" d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                        <div class="ms-2 mb-1 mb-sm-0 course-schedule">
                                                            <h6 class="mb-sm-1 mb-0">Web Design</h6>
                                                            <span class="text-muted">10-06-2023</span>
                                                        </div>
                                                        <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                            <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                            <span>09:00</span>
                                                            <span class="mx-2 text-muted">-</span>
                                                            <span>12:00</span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item mb-4">
                                                    <div class=" d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                        <div class="ms-2 mb-1 mb-sm-0 course-schedule">
                                                            <h6 class="mb-sm-1 mb-0">Java Programming</h6>
                                                            <span class="text-muted">15-06-2023</span>
                                                        </div>
                                                        <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                            <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                            <span>12:00</span>
                                                            <span class="mx-2 text-muted">-</span>
                                                            <span>13:20</span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item mb-4">
                                                    <div class=" d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                        <div class="ms-2 mb-1 mb-sm-0 course-schedule">
                                                            <h6 class="mb-sm-1 mb-0">Meeting <a href="javascript:void(0);" class="tx-primary">Yuhan Sev</a></h6>
                                                            <span class="text-muted">15-06-2023</span>
                                                        </div>
                                                        <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                            <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                            <span>16:00</span>
                                                            <span class="mx-2 text-muted">-</span>
                                                            <span>17:20</span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item  mb-4">
                                                    <div class=" d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                        <div class="ms-2 mb-1 mb-sm-0 course-schedule">
                                                            <h6 class="mb-sm-1 mb-0">UX/UI</h6>
                                                            <span class="text-muted">20-06-2023</span>
                                                        </div>
                                                        <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                            <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                            <span>18:15</span>
                                                            <span class="mx-2 text-muted">-</span>
                                                            <span>19:00</span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item">
                                                    <div class=" d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                        <div class="ms-2 mb-1 mb-sm-0 course-schedule">
                                                            <h6 class="mb-sm-1 mb-0">React js</h6>
                                                            <span class="text-muted">20-06-2023</span>
                                                        </div>
                                                        <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                            <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                            <span>18:15</span>
                                                            <span class="mx-2 text-muted">-</span>
                                                            <span>19:00</span>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End::row-2 -->


                            <div class="col-xxl-12">
                                <div class="card custom-card">
                                    <div class="card-header justify-content-between">
                                        <div class="card-title">
                                            Course List
                                        </div>
                                        <div class="d-flex flex-wrap gap-1">
                                            <div class="me-3">
                                                <input class="form-control form-control-sm" type="text" placeholder="Search Here" aria-label=" example">
                                            </div>
                                            <div class="dropdown">
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
                                                        <th scope="col" class="text-center">S.No</th>
                                                        <th scope="col">Course</th>
                                                        <th scope="col">Category</th>
                                                        <th scope="col" class="text-center">Classes</th>
                                                        <th scope="col">Last Update</th>
                                                        <th scope="col">Instructur</th>
                                                        <th scope="col" class="text-center">Sudents</th>
                                                        <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-center">01 </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                    <img src="{{asset('build/assets/images/media/media-15.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                </a>
                                                                <div class="flex-1 pos-relative">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                    <p class="mb-0 fs-14">Digital Marketing Course From Scratch</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class=""> <span class="badge rounded-pill bg-danger-transparent">Marketing</span> </td>
                                                        <td class="text-center">115</td>
                                                        <td class=""><span class="">21-06-2022</span></td>
                                                        <td class=""><span class="">Stuart Little</span></td>
                                                        <td class=" text-center"><span class="">1,189</span></td>
                                                        <td class=" ">
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
                                                        <td class="text-center">02 </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                    <img src="{{asset('build/assets/images/media/media-13.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                </a>
                                                                <div class="flex-1 pos-relative">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                    <p class="mb-0 fs-14">Master Linear Algebra Medium Level</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class=""> <span class="badge rounded-pill bg-success-transparent">Mathematics</span> </td>
                                                        <td class="text-center">809</td>
                                                        <td class=""><span class="">11-06-2022</span></td>
                                                        <td class=""><span class="">Arya Neo</span></td>
                                                        <td class=" text-center"><span class="">773</span></td>
                                                        <td class=" ">
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
                                                        <td class="text-center">03 </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                    <img src="{{asset('build/assets/images/media/media-14.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                </a>
                                                                <div class="flex-1 pos-relative">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                    <p class="mb-0 fs-14">Data Structures &amp; Algorithms For Beginners</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class=""> <span class="badge rounded-pill bg-info-transparent">Programming</span> </td>
                                                        <td class="text-center">679</td>
                                                        <td class=""><span class="">14-06-2022</span></td>
                                                        <td class=""><span class="">Boran Ray</span></td>
                                                        <td class=" text-center"><span class="">973</span></td>
                                                        <td class=" ">
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
                                                        <td class="text-center">04 </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                    <img src="{{asset('build/assets/images/media/media-18.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                </a>
                                                                <div class="flex-1 pos-relative">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                    <p class="mb-0 fs-14">Ray Optics &amp; Optical Fibre Master Class </p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class=""> <span class="badge rounded-pill bg-teal-transparent">Science</span> </td>
                                                        <td class="text-center">20</td>
                                                        <td class=""><span class="">14-07-2022</span></td>
                                                        <td class=""><span class="">Chin Op</span></td>
                                                        <td class=" text-center"><span class="">05</span></td>
                                                        <td class=" ">
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
                                                        <td class="text-center">05 </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <a href="javascript:void(0);" class="pe-2 d-block">
                                                                    <img src="{{asset('build/assets/images/media/media-22.jpg')}}" alt="img" class="avatar avatar-sm rounded-circle ">
                                                                </a>
                                                                <div class="flex-1 pos-relative">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link"></a>
                                                                    <p class="mb-0 fs-14">CSS Zero to Hero Master Class </p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class=""> <span class="badge rounded-pill bg-pink-transparent">UI/UX</span> </td>
                                                        <td class="text-center">70</td>
                                                        <td class=""><span class="">14-08-2022</span></td>
                                                        <td class=""><span class="">Burak Oin</span></td>
                                                        <td class=" text-center"><span class="">55</span></td>
                                                        <td class=" ">
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
                    </div>
                    <!-- END APP CONTENT -->

@endsection

@section('scripts')

        <!-- CHARTJS CHART JS -->
        <script src="{{asset('build/assets/libs/chart.js/chart.min.js')}}"></script>

        <!-- APEX CHARTS JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- COURSES DASHBOARD JS -->
        @vite('resources/assets/js/Course-dashboard.js')


@endsection