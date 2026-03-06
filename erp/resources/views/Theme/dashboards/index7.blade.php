@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
                        <h4 class="fw-medium mb-0">Projects-Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">Dashboards</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Projects</li>
                        </ol>
                    </div>
                    <!-- END PAGE HEADER -->
                    
                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start::row-1 -->
                            <div class="row">
                                <div class="col-xxl-3 col-sm-6">
                                    <div class="card custom-card">
                                        <div class="card-body">
                                            <div class="flex-1 border-bottom-dashed">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="mb-0 flex-1 text-muted fw-semibold">Total Projects</span>
                                                    <div class="ms-2">
                                                        <span class="avatar bg-info-transparent text-info"><i class="bx bx-briefcase fs-20"></i></span>
                                                    </div>
                                                </div>
                                                <h3 class="mb-0">676,90</h3>
                                                <div class="flex-between">
                                                    <span class="text-muted fs-12">6 min ago</span>
                                                    <span class="text-info fw-semibold"><i class="bi bi-arrow-down-right me-1 fs-13"></i>105.05%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <div class="card custom-card">
                                        <div class="card-body">
                                            <div class="flex-1 border-bottom-dashed">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="mb-0 flex-1 text-muted fw-semibold">Pending Projects</span>
                                                    <div class="ms-2">
                                                        <span class="avatar bg-pink-transparent text-pink"><i class="bx bx-book-open fs-20"></i></span>
                                                    </div>
                                                </div>
                                                <h3 class="mb-0">406,90</h3>
                                                <div class="flex-between">
                                                    <span class="text-muted fs-12">30 min ago</span>
                                                    <span class="text-pink fw-semibold"><i class="bi bi-arrow-up-right me-1 fs-13"></i>095.05%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <div class="card custom-card">
                                        <div class="card-body">
                                            <div class="flex-1 border-bottom-dashed">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="mb-0 flex-1 text-muted fw-semibold">New Projects</span>
                                                    <div class="ms-2">
                                                        <span class="avatar bg-warning-transparent text-warning"><i class="bx bx-folder-plus fs-20"></i></span>
                                                    </div>
                                                </div>
                                                <h3 class="mb-0">716,80</h3>
                                                <div class="flex-between">
                                                    <span class="text-muted fs-12">1 hr ago</span>
                                                    <span class="text-warning fw-semibold"><i class="bi bi-arrow-up-right me-1 fs-13"></i>006.43%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-sm-6">
                                    <div class="card custom-card">
                                        <div class="card-body">
                                            <div class="flex-1 border-bottom-dashed">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="mb-0 flex-1 text-muted fw-semibold">Completed Projects</span>
                                                    <div class="ms-2">
                                                        <span class="avatar bg-teal-transparent text-teal"><i class="bx bx-compass fs-20"></i></span>
                                                    </div>
                                                </div>
                                                <h3 class="mb-0">536,80</h3>
                                                <div class="flex-between">
                                                    <span class="text-muted fs-12">30 min ago</span>
                                                    <span class="text-teal fw-semibold"><i class="bi bi-arrow-down-right me-1 fs-13"></i>396.03%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xxl-6 col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div>
                                                <h5 class="card-title mb-0"> Project Analysis</h5>
                                            </div>
                                            <div class="ms-auto">
                                                <a aria-label="anchor" href="javascript:void(0)" class="btn  btn-sm text-muted border rounded-1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                                        <div class="card-body">
                                            <div id="projectAnalysis"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3  col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">Ongoing Tasks</div>
                                            <div class="dropdown">
                                                <a href="javascript:void(0);" class="btn  btn-sm text-muted border rounded-1" data-bs-toggle="dropdown" aria-expanded="false">
                                                    View All<i class="ri-arrow-down-s-line align-middle ms-1"></i>
                                                </a>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li class=""><a class="dropdown-item border-bottom" href="javascript:void(0);">Today</a></li>
                                                    <li class=""><a class="dropdown-item border-bottom" href="javascript:void(0);">This Week</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li class="list-item mb-3">
                                                    <div class="d-flex">
                                                        <div class="me-2">
                                                            <label class="ckbox mb-0 mt-1 me-2"><input type="checkbox" class="form-check-input"><span></span></label>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted  float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="tx-capitalize mb-1 fs-14 fw-semibiold">Designing a landing page</p>
                                                            <div class="d-flex">
                                                                <div class="avatar-list-stacked">
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/2.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/4.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/6.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/10.jpg')}}"></div>
                                                                </div>
                                                                <span class="text-muted fw-semibold fs-11 lh-lg ms-2"><i class="fe fe-clock me-1 fs-13 d-inline-block" ></i>10-04-22</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item mb-3">
                                                    <div class="d-flex">
                                                        <div class="me-2">
                                                            <label class="ckbox mb-0 mt-1 me-2"><input type="checkbox" class="form-check-input" checked><span></span></label>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted  float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="tx-capitalize mb-1 fs-14 fw-semibiold">Fixing Responsive Issues</p>
                                                            <div class="d-flex">
                                                                <div class="avatar-list-stacked">
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/8.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/10.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/14.jpg')}}"></div>
                                                                    <a href="javascript:void(0);" class="avatar avatar-xs bg-primary tx-white ">+3</a>
                                                                </div>
                                                                <span class="text-muted fw-semibold fs-11 lh-lg ms-2"><i class="fe fe-clock me-1 fs-13 d-inline-block" ></i>07-04-22</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item mb-3">
                                                    <div class="d-flex">
                                                        <div class="me-2">
                                                            <label class="ckbox mb-0 mt-1 me-2"><input type="checkbox" class="form-check-input"><span></span></label>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted  float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="tx-capitalize mb-1 fs-14 fw-semibiold">Fixing Bugs in project 2A2</p>
                                                            <div class="d-flex">
                                                                <div class="avatar-list-stacked">
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/11.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/13.jpg')}}"></div>
                                                                </div>
                                                                <span class="text-muted fw-semibold fs-11 lh-lg ms-2"><i class="fe fe-clock me-1 fs-13 d-inline-block" ></i>09-04-22</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item mb-3">
                                                    <div class="d-flex">
                                                        <div class="me-2">
                                                            <label class="ckbox mb-0 mt-1 me-2"><input type="checkbox" class="form-check-input"><span></span></label>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted  float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="tx-capitalize mb-1 fs-14 fw-semibiold">Designing a landing page</p>
                                                            <div class="d-flex">
                                                                <div class="avatar-list-stacked">
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/2.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/4.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/6.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/10.jpg')}}"></div>
                                                                </div>
                                                                <span class="text-muted fw-semibold fs-11 lh-lg ms-2"><i class="fe fe-clock me-1 fs-13 d-inline-block" ></i>10-04-22</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item mb-3">
                                                    <div class="d-flex">
                                                        <div class="me-2">
                                                            <label class="ckbox mb-0 mt-1 me-2"><input type="checkbox" class="form-check-input" checked><span></span></label>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted  float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="tx-capitalize mb-1 fs-14 fw-semibiold">Fixing Responsive Issues</p>
                                                            <div class="d-flex">
                                                                <div class="avatar-list-stacked">
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/8.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/10.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/14.jpg')}}"></div>
                                                                    <a href="javascript:void(0);" class="avatar avatar-xs bg-primary tx-white ">+3</a>
                                                                </div>
                                                                <span class="text-muted fw-semibold fs-11 lh-lg ms-2"><i class="fe fe-clock me-1 fs-13 d-inline-block" ></i>07-04-22</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-item">
                                                    <div class="d-flex">
                                                        <div class="me-2">
                                                            <label class="ckbox mb-0 mt-1 me-2"><input type="checkbox" class="form-check-input"><span></span></label>
                                                        </div>
                                                        <div class="flex-1">
                                                            <a aria-label="anchor" href="javascript:void(0)" class="text-muted  float-end" data-bs-toggle="dropdown" aria-expanded="false"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                            <p class="tx-capitalize mb-1 fs-14 fw-semibiold">Fixing Bugs in registration page</p>
                                                            <div class="d-flex">
                                                                <div class="avatar-list-stacked">
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/11.jpg')}}"></div>
                                                                    <div class="avatar avatar-xs"><img alt="avatar" class="rounded-circle" src="{{asset('build/assets/images/faces/13.jpg')}}"></div>
                                                                </div>
                                                                <span class="text-muted fw-semibold fs-11 lh-lg ms-2"><i class="fe fe-clock me-1 fs-13 d-inline-block" ></i>09-04-22</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Recent Activity</div>
                                        </div>
                                        <div class="card-body">
                                            <ul class="project-list mb-0">
                                                <li class="">
                                                    <div class="">
                                                        <i class="task-icon bg-primary"></i>
                                                        <div class="d-flex">
                                                            <h6 class="fw-semibold mb-0">Task Finished</h6>
                                                            <span class="fw-semibold text-muted fs-11 ms-auto my-auto">09 July 2021</span>
                                                        </div>
                                                        <p class="text-muted fs-12">Adam Berry finished task on
                                                            <a href="javascript:void(0)" class="fw-semibold text-primary"> AngularJS Template</a>
                                                        </p>
                                                    </div>
                                                </li>
                                                <li class="">
                                                    <div class="">
                                                        <i class="task-icon bg-secondary"></i>
                                                        <div class="d-flex">
                                                            <h6 class="fw-semibold mb-0">New Comment</h6>
                                                            <span class="fw-semibold text-muted fs-11 ms-auto my-auto">05 July 2021</span>
                                                        </div>
                                                        <p class="text-muted fs-12">Victoria commented on Project
                                                            <a href="javascript:void(0)" class="fw-semibold text-primary"> AngularJS Template</a>
                                                        </p>
                                                    </div>
                                                </li>
                                                <li class="">
                                                    <div class="">
                                                        <i class="task-icon bg-success"></i>
                                                        <div class="d-flex">
                                                            <h6 class="fw-semibold mb-0">Task Overdue</h6>
                                                            <span class="fw-semibold text-muted fs-11 ms-auto my-auto">29 June 2021</span>
                                                        </div>
                                                        <p class="text-muted fs-12">Petey Cruiser finished
                                                            <a href="javascript:void(0)" class="fw-semibold text-primary"> Integrated management</a>
                                                        </p>
                                                    </div>
                                                </li>
                                                <li class="">
                                                    <div class="">
                                                        <i class="task-icon bg-primary"></i>
                                                        <div class="d-flex">
                                                            <h6 class="fw-semibold mb-0">Task Finished</h6>
                                                            <span class="fw-semibold text-muted fs-11 ms-auto my-auto">09 July 2021</span>
                                                        </div>
                                                        <p class="text-muted fs-12">Adam Berry finished task on
                                                            <a href="javascript:void(0)" class="fw-semibold text-primary"> AngularJS Template</a>
                                                        </p>
                                                    </div>
                                                </li>
                                                <li class="">
                                                    <div class="">
                                                        <i class="task-icon bg-warning"></i>
                                                        <div class="d-flex">
                                                            <h6 class="fw-semibold mb-0">Task Finished</h6>
                                                            <span class="fw-semibold text-muted fs-11 ms-auto my-auto">09 July 2021</span>
                                                        </div>
                                                        <p class="text-muted fs-12">Adam Berry finished task on
                                                            <a href="javascript:void(0)" class="fw-semibold text-primary"> Project Management</a>
                                                        </p>
                                                    </div>
                                                </li>
                                                <li class="mb-0">
                                                    <div class="">
                                                        <i class="task-icon bg-info"></i>
                                                        <div class="d-flex">
                                                            <h6 class="fw-semibold mb-0">New Comment</h6>
                                                            <span class="fw-semibold text-muted fs-11 ms-auto my-auto">05 July 2021</span>
                                                        </div>
                                                        <p class="text-muted fs-12 mb-0">Victoria commented on Project
                                                            <a href="javascript:void(0)" class="fw-semibold text-primary"> AngularJS Template</a>
                                                        </p>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3  col-xl-6">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                            Project Status
                                            </div>
                                        </div>
                                        <div class="card-body p-0 overflow-auto">
                                            <table class="table mb-0 text-nowrap">
                                                <tbody>
                                                    <tr>
                                                        <td class="ps-4">Wordpress Template</td>
                                                        <td class="">
                                                            <span class="fw-semibiold fs-14">25%</span>
                                                        </td>
                                                        <td class="">
                                                            <span class="badge bg-info-transparent">Progress</span>
                                                        </td>
                                                    </tr>
                                                    <tr >
                                                        <td class="ps-4">Blog Page</td>
                                                        <td class="">
                                                            <span class="fw-semibiold fs-14">60%</span>
                                                        </td>
                                                        <td class="">
                                                            <span class="badge bg-warning-transparent">Pending</span>
                                                        </td>
                                                    </tr>
                                                    <tr >
                                                        <td class="ps-4">E commerce Blog</td>
                                                        <td class="">
                                                            <span class="fw-semibiold fs-14">75%</span>
                                                        </td>
                                                        <td class="">
                                                            <span class="badge bg-info-transparent">Progress</span>
                                                        </td>
                                                    </tr>
                                                    <tr >
                                                        <td class="ps-4">E-mail Templates</td>
                                                        <td class="">
                                                            <span class="fw-semibiold fs-14">100%</span>
                                                        </td>
                                                        <td class="">
                                                            <span class="badge bg-success-transparent">Finished</span>
                                                        </td>
                                                    </tr>
                                                    <tr >
                                                        <td class="ps-4">Business Website</td>
                                                        <td class="">
                                                            <span class="fw-semibiold fs-14">100%</span>
                                                        </td>
                                                        <td class="">
                                                            <span class="badge bg-success-transparent">Finished</span>
                                                        </td>
                                                    </tr>
                                                    <tr >
                                                        <td class="ps-4">Admin Templates</td>
                                                        <td class="">
                                                            <span class="fw-semibiold fs-14">90%</span>
                                                        </td>
                                                        <td class="">
                                                            <span class="badge bg-info-transparent">Progress</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="ps-4">Beauty Templates</td>
                                                        <td class="">
                                                            <span class="fw-semibiold fs-14">50%</span>
                                                        </td>
                                                        <td class="">
                                                            <span class="badge bg-warning-transparent">Pending</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="ps-4">Authentication Pages</td>
                                                        <td class="">
                                                            <span class="fw-semibiold fs-14">59%</span>
                                                        </td>
                                                        <td class="">
                                                            <span class="badge bg-info-transparent">Progress</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="ps-4">Landing Pages</td>
                                                        <td class="">
                                                            <span class="fw-semibiold fs-14">100%</span>
                                                        </td>
                                                        <td class="">
                                                            <span class="badge bg-success-transparent">Finished</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-4  col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between flex-wrap">
                                            <div class="card-title flex-between">
                                                Upcoming Shedule
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <select name="country" class="form-control form-select sh-calendar" data-bs-placeholder="Select Country">
                                                    <option value="jan">Jan</option>
                                                    <option value="feb">Feb</option>
                                                    <option value="mar">Mar</option>
                                                    <option value="apr">Apr</option>
                                                    <option value="may" selected>May</option>
                                                    <option value="jun">Jun</option>
                                                    <option value="jul">Jul</option>
                                                    <option value="aug">Aug</option>
                                                    <option value="sep">Sep</option>
                                                    <option value="oct">Oct</option>
                                                    <option value="nov">Nov</option>
                                                    <option value="dec">Dec</option>
                                                </select>
                                                <span class="ms-2">2023</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="upcoming-shedule">
                                                <ul class="nav nav-pills mb-3">
                                                    <li class="nav-item fw-semibold mb-3">
                                                        <a href="#sun_tab" class="nav-link sh-link active text-center" data-bs-toggle="tab">
                                                            <p class="sh-dt mb-0">09</p>
                                                            <span class="sh-day">Sun</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item fw-semibold mb-3">
                                                        <a href="#mon_tab" class="nav-link sh-link text-center" data-bs-toggle="tab">
                                                            <p class="sh-dt mb-0">10</p>
                                                            <span class="sh-day">Mon</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item fw-semibold mb-3">
                                                        <a href="#tue_tab" class="nav-link sh-link text-center" data-bs-toggle="tab">
                                                            <p class="sh-dt mb-0">11</p>
                                                            <span class="sh-day">Tue</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item fw-semibold mb-3">
                                                        <a href="#wed_tab" class="nav-link sh-link text-center" data-bs-toggle="tab">
                                                            <p class="sh-dt mb-0">12</p>
                                                            <span class="sh-day">Wed</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item fw-semibold mb-3">
                                                        <a href="#thu_tab" class="nav-link sh-link text-center" data-bs-toggle="tab">
                                                            <p class="sh-dt mb-0">13</p>
                                                            <span class="sh-day">Thu</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item fw-semibold mb-3">
                                                        <a href="#fri_tab" class="nav-link sh-link text-center" data-bs-toggle="tab">
                                                            <p class="sh-dt mb-0">14</p>
                                                            <span class="sh-day">Fri</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item fw-semibold">
                                                        <a href="#sat_tab" class="nav-link sh-link text-center" data-bs-toggle="tab">
                                                            <p class="sh-dt mb-0">15</p>
                                                            <span class="sh-day">Sat</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content">
                                                    <div class="tab-pane active" id="sun_tab">
                                                        <ul class="list-unstyled mb-0 sh-shedule-container">
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Meeting with client</p>
                                                                        <span class="text-muted">video conference</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>09:00</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>12:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Lunch with team members</p>
                                                                        <span class="text-muted">Dolores Ait Labore Sit</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>12:00</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>13:20</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Meeting With Client <a href="javascript:void(0);" class="tx-primary">Yuhan Sev</a></p>
                                                                        <span class="text-muted">Golden Park</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>16:00</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>17:20</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Create New Registration Page</p>
                                                                        <span class="text-muted">2UA Project</span>
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
                                                    <div class="tab-pane" id="mon_tab">
                                                        <ul class="list-unstyled mb-0 sh-shedule-container">
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Assigning Tasks To Employees</p>
                                                                        <span class="text-muted">Task Basic</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>09:35</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>10:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Lunch With <a href="javscript:void(0);" class="tx-primary">John Abu</a></p>
                                                                        <span class="text-muted">Silver Valley</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>12:20</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>13:20</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Checking Upcoming Projects &amp; Shedules</p>
                                                                        <span class="text-muted">Vlarhar Dhi Ionk Si</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>15:40</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>16:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Uploading New Projects In Terminal</p>
                                                                        <span class="text-muted">WPN Project</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>16:45</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>17:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="tue_tab">
                                                        <ul class="list-unstyled mb-0 sh-shedule-container">
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Meeting With <a href="javascript:void(0);" class="tx-primary">Abu Siddik</a></p>
                                                                        <span class="text-muted">Zoom Meeting</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>10:00</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>11:30</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Lunch with team members</p>
                                                                        <span class="text-muted">Amet Dolor Clita</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>13:45</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>14:45</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Uploading New Dashboard</p>
                                                                        <span class="text-muted">Iox Ui</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>16:10</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>16:30</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Meeting With Client <a href="javascript:void(0);" class="tx-primary">Ion Habbu</a></p>
                                                                        <span class="text-muted">Backend</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>16:55</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>18:55</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="wed_tab">
                                                        <ul class="list-unstyled mb-0 sh-shedule-container">
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Developing Backend For Login Page</p>
                                                                        <span class="text-muted">Backend</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>10:00</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>12:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Catch Lunch At <a href="javascript:void(0);" class="tx-primary">Sed Lorem</a></p>
                                                                        <span class="text-muted">Lorem Ipsum</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>13:15</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>14:10</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Join Client Meeting</p>
                                                                        <span class="text-muted">SpaceY</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>15:30</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>16:20</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Planning New Technology With Members</p>
                                                                        <span class="text-muted">Rio</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>17:30</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>18:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="thu_tab">
                                                        <ul class="list-unstyled mb-0 sh-shedule-container">
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Meeting with <a href="javascript:void(0);" class="tx-primary">Xion Shan</a></p>
                                                                        <span class="text-muted">G - Meets</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>09:00</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>11:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Updating UI Of Dashboard</p>
                                                                        <span class="text-muted">Dashboard Xin</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>12:50</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>13:20</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Assigning Tasks To Members</p>
                                                                        <span class="text-muted">Task MWU</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>15:45</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>16:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Dinner with team members</p>
                                                                        <span class="text-muted">Amet Dolor Clita</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>19:55</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>20:30</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-pane" id="fri_tab">
                                                        <ul class="list-unstyled mb-0 sh-shedule-container">
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Meeting with client</p>
                                                                        <span class="text-muted">video conference</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>09:00</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>12:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Lunch with team members</p>
                                                                        <span class="text-muted">Dolores Ait Labore Sit</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>12:00</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>13:20</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Meeting With Client <a href="javascript:void(0);" class="tx-primary">Yuhan Sev</a></p>
                                                                        <span class="text-muted">Golden Park</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>16:00</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>17:20</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Create New Registration Page</p>
                                                                        <span class="text-muted">2UA Project</span>
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
                                                    <div class="tab-pane" id="sat_tab">
                                                        <ul class="list-unstyled mb-0 sh-shedule-container">
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Assigning Tasks To Employees</p>
                                                                        <span class="text-muted">Task Basic</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>09:35</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>10:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Lunch With <a href="javscript:void(0);" class="tx-primary">John Abu</a></p>
                                                                        <span class="text-muted">Silver Valley</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>12:20</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>13:20</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Checking Upcoming Projects &amp; Shedules</p>
                                                                        <span class="text-muted">Vlarhar Dhi Ionk Si</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>15:40</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>16:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="sh-shedule d-sm-flex align-items-start justify-content-between font-weight-semibold">
                                                                    <div class="ms-sm-2 mb-1 mb-sm-0">
                                                                        <p class="mb-sm-1 mb-0 fs-14">Uploading New Projects In Terminal</p>
                                                                        <span class="text-muted">WPN Project</span>
                                                                    </div>
                                                                    <div class="min-w-fit-content d-flex align-items-center text-muted">
                                                                        <span><i class="fe fe-clock me-1 fs-13"></i></span>
                                                                        <span>16:45</span>
                                                                        <span class="mx-2 text-muted">-</span>
                                                                        <span>17:00</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-5  col-xl-6">
                                    <div class="col-xl-12">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-header justify-content-between">
                                                <div class="card-title">Team Members</div>
                                                <div class="dropdown">
                                                    <a aria-label="anchor" href="javascript:void(0);" class="btn  btn-sm text-muted border rounded-1" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fe fe-more-vertical"></i>
                                                    </a>
                                                    <ul class="dropdown-menu" role="menu">
                                                        <li class=""><a class="dropdown-item border-bottom" href="javascript:void(0);">Today</a></li>
                                                        <li class=""><a class="dropdown-item border-bottom" href="javascript:void(0);">This Week</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0);">Last Week</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table text-nowrap mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" class="text-center">S.no</th>
                                                                <th scope="col">Member</th>
                                                                <th scope="col">Role</th>
                                                                <th scope="col">Member Id</th>
                                                                <th scope="col">Tasks</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="top-selling">
                                                            <tr>
                                                                <td class="text-center lh-1">
                                                                    01
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <span class="avatar avatar-md">
                                                                            <img src="{{asset('build/assets/images/faces/1.jpg')}}" class="rounded-1" alt="">
                                                                        </span>
                                                                        <div class="flex-1 ms-2">
                                                                            <p class="mb-1 fs-14">Richard Dom</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">richarddom1116@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">Team Leader</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">#HTC098701</span>
                                                                </td>
                                                                <td><span class="">135</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center lh-1">
                                                                    02
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <span class="avatar avatar-md">
                                                                            <img src="{{asset('build/assets/images/faces/10.jpg')}}" class="rounded-1" alt="">
                                                                        </span>
                                                                        <div class="flex-1 ms-2">
                                                                            <p class="mb-1 fs-14">Jennifer Tab</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">jenny258@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">Project Manager</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">#HTC098702</span>
                                                                </td>
                                                                <td><span class="">105</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center lh-1">
                                                                    03
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <span class="avatar avatar-md">
                                                                            <img src="{{asset('build/assets/images/faces/2.jpg')}}" class="rounded-1" alt="">
                                                                        </span>
                                                                        <div class="flex-1 ms-2">
                                                                            <p class="mb-1 fs-14">Nikki Jey</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">nikkiJ141@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">UI Developer</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">#HTC098703</span>
                                                                </td>
                                                                <td><span class="">100</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center lh-1">
                                                                    04
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <span class="avatar avatar-md">
                                                                            <img src="{{asset('build/assets/images/faces/8.jpg')}}" class="rounded-1" alt="">
                                                                        </span>
                                                                        <div class="flex-1 ms-2">
                                                                            <p class="mb-1 fs-14">Arifa Zed</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">arifaZ@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">Web Developer</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">#HTC098704</span>
                                                                </td>
                                                                <td><span class="">055</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center lh-1">
                                                                    05
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <span class="avatar avatar-md">
                                                                            <img src="{{asset('build/assets/images/faces/7.jpg')}}" class="rounded-1" alt="">
                                                                        </span>
                                                                        <div class="flex-1 ms-2">
                                                                            <p class="mb-1 fs-14">Xiong Yu</p>
                                                                            <a href="javascript:void(0);" class="text-muted fs-12">xingzing444@demo.com</a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">Team Member</span>
                                                                </td>
                                                                <td>
                                                                    <span class="fw-semibold">#HTC098705</span>
                                                                </td>
                                                                <td><span class="">065</span></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--End::row-1 -->
                            <!--End::start-2 -->
                            <div class="row">
                                <div class="col-xxl-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Projects Summary
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
                                                        <li><a class="dropdown-item border-bottom" href="javascript:void(0);">New</a></li>
                                                        <li><a class="dropdown-item border-bottom" href="javascript:void(0);">Popular</a></li>
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
                                                            <th scope="col">Project Name</th>
                                                            <th scope="col">Start Date</th>
                                                            <th scope="col">Due Date</th>
                                                            <th scope="col">Team Members</th>
                                                            <th scope="col">Status</th>
                                                            <th scope="col">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">
                                                                1
                                                            </th>
                                                            <td>
                                                                Home Page
                                                            </td>
                                                            <td>
                                                                01 Apr 2023
                                                            </td>
                                                            <td>
                                                                14-04-2023
                                                            </td>
                                                            <td>
                                                                <div class="avatar-list-stacked">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                                    </span>
                                                                    <a class="avatar avatar-xs bg-primary avatar-rounded text-fixed-white" href="javascript:void(0);">
                                                                        +2
                                                                    </a>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-primary-transparent">In Progress</span>
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
                                                            <th scope="row">
                                                                2
                                                            </th>
                                                            <td>
                                                                Landing Design
                                                            </td>
                                                            <td>
                                                                02 Apr 2023
                                                            </td>
                                                            <td>
                                                                20-04-2023
                                                            </td>
                                                            <td>
                                                                <div class="avatar-list-stacked">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-primary-transparent">In Progress</span>
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
                                                            <th scope="row">
                                                                3
                                                            </th>
                                                            <td>
                                                                New Template Design
                                                            </td>
                                                            <td>
                                                                10 Apr 2023
                                                            </td>
                                                            <td>
                                                                29-05-2023
                                                            </td>
                                                            <td>
                                                                <div class="avatar-list-stacked">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-warning-transparent">Pending</span>
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
                                                            <th scope="row">
                                                                4
                                                            </th>
                                                            <td>
                                                                HR Management Template Design
                                                            </td>
                                                            <td>
                                                                01 May 2023
                                                            </td>
                                                            <td>
                                                                18-04-2023
                                                            </td>
                                                            <td>
                                                                <div class="avatar-list-stacked">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                                    </span>
                                                                    <a class="avatar avatar-xs bg-primary avatar-rounded text-fixed-white" href="javascript:void(0);">
                                                                        +5
                                                                    </a>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-primary-transparent">In Progress</span>
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
                                                            <th scope="row">
                                                                5
                                                            </th>
                                                            <td>
                                                                Designing New Template
                                                            </td>
                                                            <td>
                                                                20 May 2023
                                                            </td>
                                                            <td>
                                                                11-04-2023
                                                            </td>
                                                            <td>
                                                                <div class="avatar-list-stacked">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/16.jpg')}}" alt="img">
                                                                    </span>
                                                                    <a class="avatar avatar-xs bg-primary avatar-rounded text-fixed-white" href="javascript:void(0);">
                                                                        +3
                                                                    </a>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-transparent">Completed</span>
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
                                                            <th scope="row">
                                                                6
                                                            </th>
                                                            <td>
                                                                Documentation Project
                                                            </td>
                                                            <td>
                                                                21 May 2023
                                                            </td>
                                                            <td>
                                                                18-04-2023
                                                            </td>
                                                            <td>
                                                                <div class="avatar-list-stacked">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                                    </span>
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-primary-transparent">In Progress</span>
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
                            <!-- End::row-2 -->


                        </div>
                    </div>
                    <!-- END APP CONTENT -->
        
@endsection

@section('scripts')

        <!-- APEX CHARTS JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- USED FOR MULTIPLE COLORED TIMELINE CHART JS  -->
        <script src="{{asset('build/assets/libs/moment/moment.js')}}"></script>

        <!-- PROJECTS DASHBOARD JS -->
        @vite('resources/assets/js/projects-dashboard.js')


@endsection