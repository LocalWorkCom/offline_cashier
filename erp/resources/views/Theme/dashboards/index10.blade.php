@extends('layouts.master')

@section('styles')
 

@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
                        <h4 class="fw-medium mb-0">Personal-Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">Dashboards</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Personal</li>
                        </ol>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start::row-1 -->
                                <div class="row">
                                    <div class="col-xxl-9">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="row">
                                                    <div class="col-xl-3 col-lg-6 col-sm-6 ">
                                                        <div class="card custom-card">
                                                            <div class="card-body">
                                                                <div class="flex-1">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <span class="mb-0 flex-1 fs-14">Instagram</span>
                                                                        <div class="ms-2">
                                                                            <span class="avatar bg-warning-transparent text-warning"><i class="bi bi-instagram fs-16"></i></span>
                                                                        </div>
                                                                    </div>
                                                                    <h3 class="mb-0 fs-24">121K</h3>
                                                                    <div class="flex-between">
                                                                        <span class="text-muted fs-12">6 min ago</span>
                                                                        <span class="text-warning"><i class="bi bi-arrow-down-right mg-e-3 fs-10 me-1  d-inline-block"></i>1.5%</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-3 col-lg-6 col-sm-6">
                                                        <div class="card custom-card">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-center justify-content-between">
                                                                            <span class="mb-0 flex-1 fs-14">Facebook</span>
                                                                            <div class="ms-2">
                                                                                <span class="avatar bg-success-transparent text-success"><i class="bi bi-facebook fs-16"></i></span>
                                                                            </div>
                                                                        </div>
                                                                        <h3 class="mb-0 fs-24">2.1K</h3>
                                                                        <div class="flex-between">
                                                                            <span class="text-muted fs-12">1 hr ago</span>
                                                                            <span class="text-success"><i class="bi bi-arrow-down-right mg-e-3 fs-10 me-1  d-inline-block"></i>1.9%</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-3 col-lg-6 col-sm-6">
                                                        <div class="card custom-card">
                                                            <div class="card-body">
                                                                <div class=" flex-1">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <span class="mb-0 flex-1 fs-14">Twitter</span>
                                                                        <div class="ms-2">
                                                                            <span class="avatar bg-info-transparent text-info"><i class="bi bi-twitter fs-16"></i></span>
                                                                        </div>
                                                                    </div>
                                                                    <h3 class="mb-0 fs-24">2.1K</h3>
                                                                    <div class="flex-between">
                                                                        <span class="text-muted fs-12">1 week ago</span>
                                                                        <span class="text-info"><i class="bi bi-arrow-up-right mg-e-3 fs-10 me-1  d-inline-block"></i>1.9%</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-3 col-lg-6 col-sm-6 ">
                                                        <div class="card custom-card">
                                                            <div class="card-body">
                                                                <div class=" flex-1">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <span class="mb-0 flex-1 fs-14">Youtube</span>
                                                                        <div class="ms-2">
                                                                            <span class="avatar bg-danger-transparent text-danger"><i class="bi bi-youtube fs-16"></i></span>
                                                                        </div>
                                                                    </div>
                                                                    <h3 class="mb-0 fs-24">1.1M</h3>
                                                                    <div class="flex-between">
                                                                        <span class="text-muted fs-12">1 day ago</span>
                                                                        <span class="text-danger"><i class="bi bi-arrow-up-right mg-e-3 fs-10 me-1  d-inline-block"></i>1.9%</span>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4">
                                                <div class="card custom-card">
                                                    <div class="card-header d-flex ajustify-content-between">
                                                        <div class="flex-1">
                                                            <div class="card-title">Upcoming Events</div>
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
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="list-item mb-3">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <span class="avatar bg-primary-transparent text-primary"><i class="bi bi-calendar2-heart"></i></span>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="flex-between">
                                                                            <span class="badge bg-primary-transparent">11-06-2022</span>
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="tx-inverse" data-bs-toggle="dropdown"><i class="bi bi-three-dots "></i></a>
                                                                            <ul class="dropdown-menu">
                                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                                            </ul>
                                                                        </div>
                                                                        <span>Meeting with<a href="{{url('contacts')}}" class="text-primary font-weight-semibold ms-1 op-8">Umar Xai</a></span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item  mb-3">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <span class="avatar  text-secondary">
                                                                            <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img" class="rounded-circle">
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="flex-between">
                                                                            <span class="badge bg-secondary-transparent">15-06-2022</span>
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="tx-inverse" data-bs-toggle="dropdown"><i class="bi bi-three-dots "></i></a>
                                                                            <ul class="dropdown-menu">
                                                                                <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                                                <li><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                                                <li><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                                            </ul>
                                                                        </div>
                                                                        <span><a href="{{url('contacts')}}" class="text-primary font-weight-semibold me-1 op-8">Sheh Map</a> Birthday Party</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item  mb-3">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <span class="avatar bg-secondary-transparent text-secondary"><i class="bi bi-compass"></i></span>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="flex-between">
                                                                            <span class="badge bg-orange-transparent">20-06-2022</span>
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="tx-inverse" data-bs-toggle="dropdown"><i class="bi bi-three-dots "></i></a>
                                                                            <ul class="dropdown-menu">
                                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                                            </ul>
                                                                        </div>
                                                                        <span>Holiday Trip To Italy</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item  mb-3">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <span class="avatar bg-info-transparent text-info"><i class="bi bi-balloon-heart"></i></span>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="flex-between">
                                                                            <span class="badge bg-info-transparent">24-07-2022</span>
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="tx-inverse" data-bs-toggle="dropdown"><i class="bi bi-three-dots "></i></a>
                                                                            <ul class="dropdown-menu">
                                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                                            </ul>
                                                                        </div>
                                                                        <span>Wedding Anniversary</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <span class="avatar  text-warning">
                                                                            <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img" class="rounded-circle">
                                                                        </span>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="flex-between">
                                                                            <span class="badge bg-warning-transparent">11-11-2022</span>
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="tx-inverse" data-bs-toggle="dropdown"><i class="bi bi-three-dots "></i></a>
                                                                            <ul class="dropdown-menu">
                                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Action</a></li>
                                                                                <li><a href="javascript:void(0)" class="dropdown-item border-bottom">Another Action</a></li>
                                                                                <li><a href="javascript:void(0)" class="dropdown-item">Something Else Here</a></li>
                                                                            </ul>
                                                                        </div>
                                                                        <span>Meeting With <a href="{{url('contacts')}}" class="text-primary font-weight-semibold op-8  ms-1">Irav Ert</a></span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-8">
                                                <div class="card custom-card">
                                                    <div class="card-header">
                                                        <div class="card-title">Statistics</div>
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
                                                    <div class="card-body">
                                                        <div class="">
                                                            <div id="balance"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4">
                                                <div class="card custom-card">
                                                    <div class="card-header d-flex  justify-content-between">
                                                        <div class="flex-1">
                                                            <div class="card-title">Recent Messages</div>
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
                                                        <div class="row d-lg-flex d-none mb-2">
                                                            <div class="col-sm-2">
                                                                <a href="{{url('chat')}}" class="avatar-sm avatar-status rounded-circle">
                                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img" class="avatar avatar-md rounded-circle">
                                                                </a>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <a href="{{url('chat')}}" class="avatar-sm avatar-status rounded-circle">
                                                                    <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img" class="avatar avatar-md rounded-circle">
                                                                </a>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <a href="{{url('chat')}}" class="avatar-sm avatar-status rounded-circle">
                                                                    <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img" class="avatar avatar-md rounded-circle">
                                                                </a>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <a href="{{url('chat')}}" class="avatar-sm avatar-status offline rounded-circle">
                                                                    <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img" class="avatar avatar-md rounded-circle">
                                                                </a>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <a aria-label="anchor" href="{{url('contacts')}}" class="avatar avatar-md rounded-circle bg-light text-primary">
                                                                    <i class="fe fe-chevron-right"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="flex-between mb-3">
                                                            <span class="text-muted fw-semibiold">Messages</span>
                                                            <a href="javascript:void(0);" class="tx-inverse text-muted  fw-semibiold"><i class="bi bi-check2-all  v-middle me-1"></i>Mark as read</a>
                                                        </div>
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="list-item mb-3">
                                                                <div class="d-flex align-items-start">
                                                                    <a href="{{url('chat')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img" class="avatar rounded-circle">
                                                                    </a>
                                                                    <div class="flex-1 flex-between pos-relative">
                                                                        <a aria-label="anchor" href="{{url('chat')}}" class="masked-link"></a>
                                                                        <div class="flex-1">
                                                                            <p class="mb-0 fw-semibold fs-14">Elon Isk</p>
                                                                            <span class="fs-12 text-muted">Hello there! How are you doing? Call me when...</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-2 text-end">
                                                                            <p class="mb-0 text-muted fs-12">2 min</p>
                                                                            <span class="badge bg-primary radius-20">2</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="d-flex align-items-start">
                                                                    <a aria-label="anchor" href="{{url('chat')}}" class="me-2">
                                                                        <span class="avatar bg-success-transparent text-success"><i class="bi bi-person fs-18"></i></span>
                                                                    </a>
                                                                    <div class="flex-1 flex-between pos-relative">
                                                                        <a aria-label="anchor" href="{{url('chat')}}" class="masked-link"></a>
                                                                        <div class="flex-1">
                                                                            <p class="mb-0 fw-semibold fs-14">Sebastian</p>
                                                                            <span class="fs-12 text-muted">Shall we go to the cafe at downtown...</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-2 text-end">
                                                                            <p class="mb-0 text-muted fs-12">yesterday</p>
                                                                            <span class="badge bg-primary radius-20">2</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="d-flex align-items-start">
                                                                    <a href="{{url('chat')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img" class="avatar rounded-circle">
                                                                    </a>
                                                                    <div class="flex-1 flex-between pos-relative">
                                                                        <a aria-label="anchor" href="{{url('chat')}}" class="masked-link"></a>
                                                                        <div class="flex-1">
                                                                            <p class="mb-0 fw-semibold fs-14">Shakira Sen</p>
                                                                            <span class="fs-12 text-muted">I would like to discuss about that assets...</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-2 text-end">
                                                                            <p class="mb-0 text-muted fs-12">09:43</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="d-flex align-items-start">
                                                                    <a href="{{url('chat')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img" class="avatar rounded-circle">
                                                                    </a>
                                                                    <div class="flex-1 flex-between pos-relative">
                                                                        <a aria-label="anchor" href="{{url('chat')}}" class="masked-link"></a>
                                                                        <div class="flex-1">
                                                                            <p class="mb-0 fw-semibold fs-14">Elon Isk</p>
                                                                            <span class="fs-12 text-muted">Hello there! How are you doing? Call me when...</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-2 text-end">
                                                                            <p class="mb-0 text-muted fs-12">2 min</p>
                                                                            <span class="badge bg-primary radius-20">2</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-3">
                                                                <div class="d-flex align-items-start">
                                                                    <a href="{{url('chat')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img" class="avatar rounded-circle">
                                                                    </a>
                                                                    <div class="flex-1 flex-between pos-relative">
                                                                        <a aria-label="anchor" href="{{url('chat')}}" class="masked-link"></a>
                                                                        <div class="flex-1">
                                                                            <p class="mb-0 fw-semibold fs-14">Shakira Sen</p>
                                                                            <span class="fs-12 text-muted">I would like to discuss about that assets...</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-2 text-end">
                                                                            <p class="mb-0 text-muted fs-12">09:43</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="d-flex align-items-start">
                                                                    <a aria-label="anchor" href="{{url('chat')}}" class="me-2">
                                                                        <span class="avatar bg-primary-transparent text-primary"><i class="bi bi-person fs-18"></i></span>
                                                                    </a>
                                                                    <div class="flex-1 flex-between pos-relative">
                                                                        <a aria-label="anchor" href="{{url('chat')}}" class="masked-link"></a>
                                                                        <div class="flex-1">
                                                                            <p class="mb-0 fw-semibold fs-14">Sebastian</p>
                                                                            <span class="fs-12 text-muted">Shall we go to the cafe at downtown...</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-2 text-end">
                                                                            <p class="mb-0 text-muted fs-12">yesterday</p>
                                                                            <span class="badge bg-primary radius-20">2</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-8">
                                                <div class="card custom-card">
                                                    <div class="card-header justify-content-between">
                                                        <div class="card-title">
                                                            Recent Trasaction history
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
                                                                        <th scope="col" class="text-center">S.No</th>
                                                                        <th scope="col">Recipient</th>
                                                                        <th scope="col">Transaction Id</th>
                                                                        <th scope="col">Amount</th>
                                                                        <th scope="col">Type</th>
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
                                                                                <a href="{{url('contacts')}}" class="me-2">
                                                                                    <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                                </a>
                                                                                <div class="flex-1 flex-between">
                                                                                    <div class="flex-1 pos-relative">
                                                                                        <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                                        <p class="mb-0 fw-semibold fs-14">Arzil Merob</p>
                                                                                        <span class="fs-12 text-muted">Merob0198@.com</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="text-primary">#abc0012311</span>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                            $12,098
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                Debited
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge bg-success-transparent">Success</span>
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
                                                                                <a href="{{url('contacts')}}" class="me-2">
                                                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                                </a>
                                                                                <div class="flex-1 flex-between">
                                                                                    <div class="flex-1 pos-relative">
                                                                                        <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                                        <p class="mb-0 fw-semibold fs-14">Vrsiore Merob</p>
                                                                                        <span class="fs-12 text-muted">Vrsiore987@.com</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="text-primary">#abc0012312</span>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                            $62,098
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                Debited
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge bg-success-transparent">Success</span>
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
                                                                                <a href="{{url('contacts')}}" class="me-2">
                                                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                                </a>
                                                                                <div class="flex-1 flex-between">
                                                                                    <div class="flex-1 pos-relative">
                                                                                        <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                                        <p class="mb-0 fw-semibold fs-14">Johber Akber</p>
                                                                                        <span class="fs-12 text-muted">Johber1123@.com</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="text-primary">#abc0012313</span>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                            $122,098
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                Credited
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge bg-info-transparent">Pending</span>
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
                                                                                <a href="{{url('contacts')}}" class="me-2">
                                                                                    <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                                </a>
                                                                                <div class="flex-1 flex-between">
                                                                                    <div class="flex-1 pos-relative">
                                                                                        <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                                        <p class="mb-0 fw-semibold fs-14">Bobber John</p>
                                                                                        <span class="fs-12 text-muted">Bobber123@.com</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="text-primary">#abc0012314</span>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                            $502,098
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                Credited
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge bg-danger-transparent">cancel</span>
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
                                                                                <a href="{{url('contacts')}}" class="me-2">
                                                                                    <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                                </a>
                                                                                <div class="flex-1 flex-between">
                                                                                    <div class="flex-1 pos-relative">
                                                                                        <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                                        <p class="mb-0 fw-semibold fs-14">Allian Aksa</p>
                                                                                        <span class="fs-12 text-muted">Allian123@.com</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="text-primary">#abc0012315</span>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                            $95,098
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                Debited
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge bg-success-transparent">success</span>
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
                                    </div>
                                    <div class="col-xxl-3">
                                        <div class="row">
                                            <div class="col-xxl-12 col-md-6">
                                                <div class="card custom-card">
                                                    <div class="card-body ">
                                                        <div class="d-flex">
                                                            <div class="me-4">
                                                                <h6 class="mb-3">Your Balance</h6>
                                                                <h3 class="mb-0">$56,470</h3>
                                                            </div>
                                                            <div class="">
                                                                <div id="personal-balance"></div>
                                                            </div>
                                                        </div>
                                                        <p class="mb-3">
                                                            <i class="fe fe-trending-up me-1 text-success"></i>
                                                            <span class="fs-11 text-muted">12% in last 7 days</span>
                                                        </p>
                                                        <div class="d-flex mt-4 flex-wrap">
                                                        <div class="d-flex">
                                                            <a aria-label="anchor" href="javascript:void(0)"><span class="avatar avatar-sm br-5 bg-primary-transparent text-primary me-2"><i class="fe fe-arrow-up"></i></span></a>
                                                            <div class="w-100">
                                                                <a href="javascript:void(0)">
                                                                    <h5 class="mb-0  me-3">$73.002</h5>
                                                                </a>
                                                                <p class="fs-12 text-muted me-3 mb-0">Income</p>
                                                            </div>
                                                            </div>
                                                        <div class="d-flex">
                                                            <a aria-label="anchor" href="javascript:void(0)"><span class="avatar avatar-sm br-5 bg-light  text-dark me-2"><i class="fe fe-arrow-down"></i></span></a>
                                                            <div class="w-100">
                                                                <a href="javascript:void(0)">
                                                                    <h5 class="mb-0  me-3">$43.987</h5>
                                                                </a>
                                                                <p class="fs-12 text-muted me-3 mb-0">Outcome</p>
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card custom-card">
                                                    <div class="card-header d-flex  justify-content-between">
                                                        <div class="flex-1">
                                                            <div class="card-title">My Goals</div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="list-item mb-4">
                                                                <div class="d-flex align-items-start pos-relative">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link z-1"></a>
                                                                    <div class="me-2">
                                                                        <span class="avatar bg-info-transparent text-info"><i class="bi bi-globe2"></i></span>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                                                            <span class="flex-1 fw-semibold">Maldives Trip</span>
                                                                            <span class="min-w-fit-content ms-1 text-info">80%</span>
                                                                        </div>
                                                                        <div class="progress progress-xs progress-custom progress-animate" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                                            <div class="progress-bar bg-info" style="width: 80%">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-4">
                                                                <div class="d-flex align-items-start pos-relative">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link z-1"></a>
                                                                    <div class="me-2">
                                                                        <span class="avatar bg-warning-transparent text-warning"><i class="bi bi-balloon-heart"></i></span>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                                                            <span class="flex-1  fw-semibold">Savings For Birthday</span>
                                                                            <span class="min-w-fit-content ms-1 text-warning">70%</span>
                                                                        </div>
                                                                        <div class="progress progress-xs progress-custom progress-animate" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                                            <div class="progress-bar bg-warning" style="width: 70%">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-4">
                                                                <div class="d-flex align-items-start pos-relative">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link z-1"></a>
                                                                    <div class="me-2">
                                                                        <span class="avatar bg-secondary-transparent text-secondary"><i class="bi bi-lightning"></i></span>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                                                            <span class="flex-1 fw-semibold">Learn Guitar</span>
                                                                            <span class="min-w-fit-content ms-1 text-secondary">40%</span>
                                                                        </div>
                                                                        <div class="progress progress-xs progress-custom progress-animate" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                                            <div class="progress-bar bg-secondary" style="width: 40%">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="d-flex align-items-start pos-relative">
                                                                    <a aria-label="anchor" href="javascript:void(0);" class="masked-link z-1"></a>
                                                                    <div class="me-2">
                                                                        <span class="avatar bg-success-transparent text-success"><i class="bi bi-shop-window"></i></span>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                                                            <span class="flex-1 fw-semibold">Learn Danceing</span>
                                                                            <span class="min-w-fit-content ms-1 text-success">55%</span>
                                                                        </div>
                                                                        <div class="progress progress-xs progress-custom progress-animate" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                                                            <div class="progress-bar bg-success" style="width: 55%">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-xxl-12 col-md-6">
                                                <div class="card custom-card">
                                                    <div class="card-header d-flex  justify-content-between">
                                                        <div class="flex-1">
                                                            <div class="card-title">Favourite Contacts</div>
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
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="list-item mb-4">
                                                                <div class="d-flex align-items-center">
                                                                    <a href="{{url('contacts')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                    </a>
                                                                    <div class="flex-1 flex-between">
                                                                        <div class="flex-1 pos-relative">
                                                                            <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                            <p class="mb-0 fw-semibold fs-14">Gold</p>
                                                                            <span class="fs-12 text-muted">+(72)-86976972</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-1">
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="text-danger me-2"><i class="bi bi-heart-fill"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-4">
                                                                <div class="d-flex align-items-center">
                                                                    <a href="{{url('contacts')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                    </a>
                                                                    <div class="flex-1 flex-between">
                                                                        <div class="flex-1 pos-relative">
                                                                            <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                            <p class="mb-0 fw-semibold fs-14">Prax Bhav</p>
                                                                            <span class="fs-12 text-muted">+(12)-05147532</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-1">
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="text-danger me-2"><i class="bi bi-heart-fill"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-4">
                                                                <div class="d-flex align-items-center">
                                                                    <a href="{{url('contacts')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                    </a>
                                                                    <div class="flex-1 flex-between">
                                                                        <div class="flex-1 pos-relative">
                                                                            <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                            <p class="mb-0 fw-semibold fs-14">Rox avaren</p>
                                                                            <span class="fs-12 text-muted">+(12)-05147582</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-1">
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="text-danger me-2"><i class="bi bi-heart-fill"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-4">
                                                                <div class="d-flex align-items-center">
                                                                    <a href="{{url('contacts')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                    </a>
                                                                    <div class="flex-1 flex-between">
                                                                        <div class="flex-1 pos-relative">
                                                                            <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                            <p class="mb-0 fw-semibold fs-14">Zebor varin</p>
                                                                            <span class="fs-12 text-muted">+(12)-05147532</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-1">
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="text-danger me-2"><i class="bi bi-heart-fill"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-4">
                                                                <div class="d-flex align-items-center">
                                                                    <a href="{{url('contacts')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                    </a>
                                                                    <div class="flex-1 flex-between">
                                                                        <div class="flex-1 pos-relative">
                                                                            <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                            <p class="mb-0 fw-semibold fs-14">Arhohs Vlli</p>
                                                                            <span class="fs-12 text-muted">+(21)-16773116</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-1">
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="text-danger me-2"><i class="bi bi-heart-fill"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item mb-4">
                                                                <div class="d-flex align-items-center">
                                                                    <a href="{{url('contacts')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                    </a>
                                                                    <div class="flex-1 flex-between">
                                                                        <div class="flex-1 pos-relative">
                                                                            <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                            <p class="mb-0 fw-semibold fs-14">Arif Azedh</p>
                                                                            <span class="fs-12 text-muted">+(22)-19873116</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-1">
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="text-danger me-2"><i class="bi bi-heart-fill"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-item">
                                                                <div class="d-flex align-items-center">
                                                                    <a href="{{url('contacts')}}" class="me-2">
                                                                        <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img" class="avatar avatar-md radius-5">
                                                                    </a>
                                                                    <div class="flex-1 flex-between">
                                                                        <div class="flex-1 pos-relative">
                                                                            <a aria-label="anchor" href="{{url('contacts')}}" class="masked-link"></a>
                                                                            <p class="mb-0 fw-semibold fs-14">Aliz Varne</p>
                                                                            <span class="fs-12 text-muted">+(99)-19873116</span>
                                                                        </div>
                                                                        <div class="min-w-fit-content ms-1">
                                                                            <a aria-label="anchor" href="javascript:void(0);" class="text-danger me-2"><i class="bi bi-heart-fill"></i></a>
                                                                        </div>
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
                            <!-- End::row-1-->

                        </div>
                    </div>
                    <!-- END APP CONTENT -->

@endsection

@section('scripts')

        <!-- CHARTJS CHART JS -->
        <script src="{{asset('build/assets/libs/chart.js/chart.min.js')}}"></script>

        <!-- APEX CHARTS JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- PERSONAL-Dashboard -->
        @vite('resources/assets/js/personal-dashboard.js')


@endsection