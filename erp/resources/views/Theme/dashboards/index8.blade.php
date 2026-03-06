@extends('layouts.master')

@section('styles')

        <!-- SWIPER CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/swiper/swiper-bundle.min.css')}}">

@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="page-header-breadcrumb d-md-flex d-block align-items-center justify-content-between ">
                        <h4 class="fw-medium mb-0">NFT-Dashboard</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);" class="text-white-50">Dashboards</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">NFT</li>
                        </ol>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start::row-1 -->
                            <div class="row">
                                <div class="col-xxl-6  col-lg-12">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 col-auto">
                                                    <div class="featured-nft d-md-block d-none">
                                                        <img src="{{asset('build/assets/images/nft-images/1.png')}}" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-md-8 col-12">
                                                    <div class="p-2">
                                                        <h4 class="fw-semibold mb-2">Discover, collect and sell your <span class="text-primary">NFTs</span> at one place</h4>
                                                        <p class="mb-4 text-muted fs-14 op-7">
                                                            NFT means non-fungible tokens (NFTs), which are generally created using the same type of programming used for cryptocurrencies.It is is a unique digital identifier that cannot be copied.
                                                        </p>
                                                        <div class="btn-list pt-1">
                                                            <button type="button" class="btn btn-primary btn-wave m-1">Discover Now</button>
                                                            <button type="button" class="btn btn-outline-primary btn-wave m-1">Create Yours</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3  col-lg-6">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body mb-3">
                                            <div class="d-flex flex-wrap align-items-center lh-1 mb-2">
                                                <span class="avatar avatar-md me-1">
                                                    <img src="{{asset('build/assets/images/crypto-currencies/square-color/Bitcoin.svg')}}" alt="">
                                                </span>
                                                <h6 class="ms-2 fs-16 mt-2">Bitcoin</h6>
                                            </div>
                                            <div class="d-flex align-items-center flex-wrap">
                                                <h3 class="mb- fw-semibold">$124.00USD</h3>
                                                <span class="fw-semibold text-muted fs-14 ms-auto min-w-fit-content">
                                                <i class="ti ti-trending-up me-2 text-success fs-18 align-middle d-inline-block"></i>122.03%</span>
                                            </div>
                                        </div>
                                        <div id="nft-balance-chart" class=""></div>
                                    </div>
                                </div>
                                <div class="col-xxl-3  col-lg-6 ">
                                    <div class="card custom-card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start justify-content-between mb-3">
                                                <div class="flex-grow-1">
                                                    <p class="fs-14 mb-2 text-muted">Your Balance</p>
                                                    <h3 class="mb-3">$23,773.09</h3>
                                                </div>
                                                <span class="bg-primary-transparent text-primary avatar avatar-lg br-10"><i class="bi bi-wallet-fill"></i></span>
                                            </div>
                                            <p class="text-muted mb-1 fs-14">Transactions</p>
                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <h6 class="fs-18 mb-0">1,107</h6>
                                                <span class="fs-13 text-muted">Last activity 07-08-22</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <a href="javascript:void(0);" class="flex-grow-1 btn btn-primary me-2">Withdraw Money</a>
                                                <a href="javascript:void(0);" class="min-w-fit-content btn btn-outline-primary" data-bs-toggle="tooltip" aria-label="Topup Wallet"><i class="ti ti-wallet "></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--End::row-1 -->

                            <!-- Start::row-2 -->
                            <div class="row">
                                <div class="col-xxl-9 col-xl-12 col-lg-12">
                                    <div class="d-xl-flex flex-wrap align-items-center justify-content-between gap-2 ">
                                        <div class="nft-tag nft-tag-primary active">
                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                            <span class="nft-tag-icon bg-primary-transparent"><i class="ti ti-world fs-18 "></i></span>
                                            <span class="nft-tag-text">All</span>
                                        </div>
                                        <div class="nft-tag nft-tag-secondary">
                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                            <span class="nft-tag-icon bg-secondary-transparent"><i class="ti ti-flame fs-18 "></i></span>
                                            <span class="nft-tag-text">New Trends</span>
                                        </div>
                                        <div class="nft-tag nft-tag-info">
                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                            <span class="nft-tag-icon bg-info-transparent"><i class="ti ti-palette fs-18 "></i></span>
                                            <span class="nft-tag-text">Art Work</span>
                                        </div>
                                        <div class="nft-tag nft-tag-success">
                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                            <span class="nft-tag-icon bg-success-transparent"><i class="ti ti-device-gamepad-2 fs-18 "></i></span>
                                            <span class="nft-tag-text">Games</span>
                                        </div>
                                        <div class="nft-tag nft-tag-warning">
                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                            <span class="nft-tag-icon bg-warning-transparent"><i class="ti ti-tie fs-18 "></i></span>
                                            <span class="nft-tag-text">Fashion</span>
                                        </div>
                                        <div class="nft-tag nft-tag-pink">
                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                            <span class="nft-tag-icon bg-pink-transparent"><i class="ti ti-music fs-18 "></i></span>
                                            <span class="nft-tag-text">Music</span>
                                        </div>
                                        <div class="nft-tag nft-tag-danger">
                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                            <span class="nft-tag-icon bg-danger-transparent"><i class="ti ti-home fs-18 "></i></span>
                                            <span class="nft-tag-text">AI</span>
                                        </div>
                                    </div>
                                    <div class="mb-4 d-flex align-items-center justify-content-between">
                                        <h5 class="fw-bold mb-0">Trending Auctions :</h5>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-primary-light btn-wave">View All</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xxl-3 col-lg-6  col-sm-6 col-12">
                                            <div class="card custom-card">
                                                <img src="{{asset('build/assets/images/nft-images/2.png')}}" class="card-img-top" alt="...">
                                                <div class="d-flex align-items-center justify-content-between nft-like-section w-100 px-3">
                                                    <div class="flex-fill">
                                                        <button aria-label="button" type="button" class="btn btn-sm btn-icon btn-success rounded-pill btn-wave">
                                                            <i class="ri-heart-fill"></i>
                                                        </button>
                                                    </div>
                                                    <div>
                                                        <span class="badge nft-like-badge text-fixed-white"><i class="ri-heart-fill me-1 text-danger align-middle d-inline-block"></i>1.32k</span>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-0 text-fixed-white nft-auction-time">
                                                        04hrs : 24m : 38s
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-start mb-2  flex-wrap">
                                                        <div class="d-inline-flex align-items-start position-relative">
                                                            <a aria-label="anchor" href="{{url('profile')}}" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img" class="avatar avatar-md rounded-circle bg-primary-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">SpaceY NFT</h6>
                                                                <span class="fs-13 text-muted">@sp1116 <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <span class="fs-13 text-muted">1.2K Likes</span>
                                                    </div>
                                                    <h6 class="fs-16 mb-2"><a href="javascript:void(0);">NFT Lorem Shapes</a></h6>
                                                    <div class="d-flex align-items-end  flex-wrap">
                                                        <div class="flex-grow-1">
                                                            <p class="fs-14 mb-1 text-muted">Highest Bid</p>
                                                            <h6 class="fs-18 mb-0">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="svg-primary" height="20" fill="var(--default-color)" data-name="Layer 1" viewBox="0 0 128 128"><path d="m64 34.84 35.58 23.2L64 7 29.24 58.07 64 34.84z"/><path d="M99.91 63.98 64 41.43 28.09 64 64 86.57l35.91-22.59z"/><path d="M28.42 69.93 64 121l34.76-51.08L64 93.16 28.42 69.93z"/></svg>
                                                                1.16ETH
                                                            </h6>
                                                        </div>
                                                        <a href="javascript:void(0);" class="min-w-fit-content btn btn-primary mt-0 mt-xxl-2">Place Bid</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6  col-sm-6 col-12">
                                            <div class="card custom-card">
                                                <img src="{{asset('build/assets/images/nft-images/4.png')}}" class="card-img-top" alt="...">
                                                <div class="d-flex align-items-center justify-content-between nft-like-section w-100 px-3 ">
                                                    <div class="flex-fill">
                                                        <button type="button" aria-label="button" class="btn btn-sm btn-icon btn-success rounded-pill btn-wave">
                                                            <i class="ri-heart-fill"></i>
                                                        </button>
                                                    </div>
                                                    <div>
                                                        <span class="badge nft-like-badge text-fixed-white"><i class="ri-heart-fill me-1 text-danger align-middle d-inline-block"></i>1.32k</span>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-0 text-fixed-white nft-auction-time">
                                                        04hrs : 24m : 38s
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-start mb-2  flex-wrap">
                                                        <div class="d-inline-flex align-items-start position-relative">
                                                            <a aria-label="anchor" href="{{url('profile')}}" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img" class="avatar avatar-md rounded-circle bg-primary-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Applx YUN</h6>
                                                                <span class="fs-13 text-muted">@appi1x76 <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <span class="fs-13 text-muted">1.7K Likes</span>
                                                    </div>
                                                    <h6 class="fs-16 mb-2"><a href="javascript:void(0);">Ivan Yang Hun</a></h6>
                                                    <div class="d-flex align-items-end  flex-wrap">
                                                        <div class="flex-grow-1">
                                                            <p class="fs-14 mb-1 text-muted">Highest Bid</p>
                                                            <h6 class="fs-18 mb-0">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="svg-secondary" height="20" fill="var(--default-color)" data-name="Layer 1" viewBox="0 0 128 128"><path d="m64 34.84 35.58 23.2L64 7 29.24 58.07 64 34.84z"/><path d="M99.91 63.98 64 41.43 28.09 64 64 86.57l35.91-22.59z"/><path d="M28.42 69.93 64 121l34.76-51.08L64 93.16 28.42 69.93z"/></svg>
                                                                2.05ETH
                                                            </h6>
                                                        </div>
                                                        <a href="javascript:void(0);" class="min-w-fit-content btn btn-primary mt-0 mt-xxl-2">Place Bid</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6  col-sm-6 col-12">
                                            <div class="card custom-card">
                                                <img src="{{asset('build/assets/images/nft-images/3.png')}}" class="card-img-top" alt="...">
                                                <div class="d-flex align-items-center justify-content-between nft-like-section w-100 px-3 ">
                                                    <div class="flex-fill">
                                                        <button type="button" aria-label="button" class="btn btn-sm btn-icon btn-success rounded-pill btn-wave">
                                                            <i class="ri-heart-fill"></i>
                                                        </button>
                                                    </div>
                                                    <div>
                                                        <span class="badge nft-like-badge text-fixed-white"><i class="ri-heart-fill me-1 text-danger align-middle d-inline-block"></i>1.32k</span>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-0 text-fixed-white nft-auction-time">
                                                        04hrs : 24m : 38s
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-start mb-2  flex-wrap">
                                                        <div class="d-inline-flex align-items-start position-relative">
                                                            <a aria-label="anchor" href="{{url('profile')}}" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img" class="avatar avatar-md rounded-circle bg-primary-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Notingu</h6>
                                                                <span class="fs-13 text-muted">@oneplsk <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <span class="fs-13 text-muted">1.4K Likes</span>
                                                    </div>
                                                    <h6 class="fs-16 mb-2"><a href="javascript:void(0);">Nature Atom Ion</a></h6>
                                                    <div class="d-flex align-items-end  flex-wrap">
                                                        <div class="flex-grow-1">
                                                            <p class="fs-14 mb-1 text-muted">Highest Bid</p>
                                                            <h6 class="fs-18 mb-0">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="svg-success" height="20" fill="var(--default-color)" data-name="Layer 1" viewBox="0 0 128 128"><path d="m64 34.84 35.58 23.2L64 7 29.24 58.07 64 34.84z"/><path d="M99.91 63.98 64 41.43 28.09 64 64 86.57l35.91-22.59z"/><path d="M28.42 69.93 64 121l34.76-51.08L64 93.16 28.42 69.93z"/></svg>
                                                                1.76ETH
                                                            </h6>
                                                        </div>
                                                        <a href="javascript:void(0);" class="min-w-fit-content btn btn-primary mt-0 mt-xxl-2">Place Bid</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-6  col-sm-6 col-12">
                                            <div class="card custom-card">
                                                <img src="{{asset('build/assets/images/nft-images/6.png')}}" class="card-img-top" alt="...">
                                                <div class="d-flex align-items-center justify-content-between nft-like-section w-100 px-3">
                                                    <div class="flex-fill">
                                                        <button type="button" aria-label="button" class="btn btn-sm btn-icon btn-success rounded-pill btn-wave">
                                                            <i class="ri-heart-fill"></i>
                                                        </button>
                                                    </div>
                                                    <div>
                                                        <span class="badge nft-like-badge text-fixed-white"><i class="ri-heart-fill me-1 text-danger align-middle d-inline-block"></i>1.32k</span>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-0 text-fixed-white nft-auction-time">
                                                        07hrs : 04m : 38s
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-start mb-2  flex-wrap">
                                                        <div class="d-inline-flex align-items-start position-relative">
                                                            <a aria-label="anchor" href="{{url('profile')}}" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img" class="avatar avatar-md rounded-circle bg-primary-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">TesliX NFT</h6>
                                                                <span class="fs-13 text-muted">@spcss3 <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <span class="fs-13 text-muted">2.34K Likes</span>
                                                    </div>
                                                    <h6 class="fs-16 mb-2"><a href="javascript:void(0);">Nortin Waving</a></h6>
                                                    <div class="d-flex align-items-end  flex-wrap">
                                                        <div class="flex-grow-1">
                                                            <p class="fs-14 mb-1 text-muted">Highest Bid</p>
                                                            <h6 class="fs-18 mb-0">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="svg-pink" height="20" fill="var(--default-color)" data-name="Layer 1" viewBox="0 0 128 128"><path d="m64 34.84 35.58 23.2L64 7 29.24 58.07 64 34.84z"/><path d="M99.91 63.98 64 41.43 28.09 64 64 86.57l35.91-22.59z"/><path d="M28.42 69.93 64 121l34.76-51.08L64 93.16 28.42 69.93z"/></svg>
                                                                3.36ETH
                                                            </h6>
                                                        </div>
                                                        <a href="javascript:void(0);" class="min-w-fit-content btn btn-primary mt-0 mt-xxl-2">Place Bid</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-12 col-lg-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Top Sellers
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
                                                <li class="list-unstyled mb-4">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <div class="d-flex align-items-center position-relative">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img" class="avatar rounded-circle bg-warning-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Kakashi Si</h6>
                                                                <span class=" tx-muted">@sensei011 <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-fit-content">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-secondary-transparent rounded-pill btn-wave waves-effect waves-light" data-bs-toggle="tooltip" title="Follow"><i class="fe fe-user-plus"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-light rounded-pill  btn-wave waves-effect waves-light" data-bs-toggle="dropdown"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-unstyled mb-4">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <div class="d-flex align-items-center position-relative">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img" class="avatar rounded-circle bg-warning-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Gervon Hir</h6>
                                                                <span class=" tx-muted">@Gervon0012 <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-fit-content">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-secondary-transparent rounded-pill btn-wave waves-effect waves-light" data-bs-toggle="tooltip" title="Follow"><i class="fe fe-user-plus"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-light rounded-pill  btn-wave waves-effect waves-light" data-bs-toggle="dropdown"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-unstyled mb-4">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <div class="d-flex align-items-center position-relative">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img" class="avatar rounded-circle bg-primary-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Oorichimaru Io</h6>
                                                                <span class=" tx-muted">@ooro000 <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-fit-content">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-secondary-transparent rounded-pill btn-wave waves-effect waves-light" data-bs-toggle="tooltip" title="Following"><i class="fe fe-user-check"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-light rounded-pill  btn-wave waves-effect waves-light" data-bs-toggle="dropdown"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-unstyled mb-4">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <div class="d-flex align-items-center position-relative">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img" class="avatar rounded-circle bg-secondary-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Sakura Yt</h6>
                                                                <span class=" tx-muted">@sak111 <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-fit-content">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-secondary-transparent rounded-pill btn-wave waves-effect waves-light" data-bs-toggle="tooltip" title="Follow"><i class="fe fe-user-plus"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-light rounded-pill  btn-wave waves-effect waves-light" data-bs-toggle="dropdown"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-unstyled mb-4">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <div class="d-flex align-items-center position-relative">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img" class="avatar rounded-circle bg-warning-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Sasuke Uchiha</h6>
                                                                <span class=" tx-muted">@sasuke777 <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-fit-content">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-secondary-transparent rounded-pill btn-wave waves-effect waves-light" data-bs-toggle="tooltip" title="Follow"><i class="fe fe-user-plus"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-light rounded-pill  btn-wave waves-effect waves-light" data-bs-toggle="dropdown"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-unstyled">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <div class="d-flex align-items-center position-relative">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                            <div class="me-2">
                                                                <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img" class="avatar rounded-circle bg-primary-transparent">
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Naruto Uzumaki</h6>
                                                                <span class=" tx-muted">@naruto111 <i class="bi bi-patch-check-fill text-primary ms-1 fs-15"></i></span>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-fit-content">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-secondary-transparent rounded-pill btn-wave waves-effect waves-light" data-bs-toggle="tooltip" title="Following"><i class="fe fe-user-check"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-light rounded-pill  btn-wave waves-effect waves-light" data-bs-toggle="dropdown"><i class="fe fe-more-vertical"></i></a>
                                                            <ul class="dropdown-menu">
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item border-bottom">Another Action</a></li>
                                                                <li class="mb-0"><a href="javascript:void(0);" class="dropdown-item">Something Else Here</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                        </div>
                            <!-- End::row-2 -->

                            <!-- Start::row-3 -->
                            <div class="row">
                                <div class="col-xxl-6 col-xl-8 col-lg-7 col-md-12 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between flex-wrap">
                                            <div class="card-title flex-between">
                                                Upcoming Events
                                            </div>
                                            <div class="d-flex align-items-center nft-choices">
                                                <select name="country" class="form-control nft-choices" data-trigger>
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
                                        <div class="card-body p-0">
                                            <div class="d-flex flex-wrap p-3 border-bottom border-block-end-dashed">
                                                <div class="me-3">
                                                    <span class="avatar avatar-md avatar-rounded p-2 bg-light">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <div class="d-sm-flex flex-wrap justify-content-evenly flex-fill">
                                                    <div class="m-1">
                                                        <span>Symbol</span>
                                                        <p class="fw-semibold mb-0">ETH</p>
                                                    </div>
                                                    <div class="m-1">
                                                        <span>Price Benchmark</span>
                                                        <p class="fw-semibold mb-0">-0.39%</p>
                                                    </div>
                                                    <div class="m-1">
                                                        <span>Price (USD)</span>
                                                        <p class="text-success fe-semibold mb-0">$1,212.67</p>
                                                    </div>
                                                    <div class="m-1">
                                                        <span>Change (24H)</span>
                                                        <p class="text-danger fw-semibold mb-0">-0.14%</p>
                                                    </div>
                                                    <div class="m-1">
                                                        <span>Market Cap</span>
                                                        <p class="fw-semibold mb-0">$148.20B</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="nft-statistics" class="p-3"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-4 col-lg-5 col-md-12 col-sm-12">
                                    <div class="mb-3 d-flex align-items-center justify-content-between">
                                        <h5 class="fw-semibold mb-0">Featured Collections :</h5>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-primary-light btn-wave waves-effect waves-light">View All</button>
                                        </div>
                                    </div>
                                    <div class="swiper pagination-dynamic text-start">
                                        <div class="swiper-wrapper">
                                            <div class="swiper-slide">
                                                <div class="card custom-card">
                                                    <div class="card-body">
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/6.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/7.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/8.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/9.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <div class="text-muted fs-12 d-flex align-items-center">Collection Rank :
                                                                    <span class="fw-semibold text-success fs-15 mb-0 ms-1">#1</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-rounded bg-light p-1 avatar-sm">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <span class="fw-semibold fs-15">1.0979ETH</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="d-flex align-items-center">
                                                            <div class="d-flex align-items-center flex-fill">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="">
                                                                        <a aria-label="anchor" href="javascript:void(0);" class="badge rounded-pill bg-primary avatar-badge"><i class="ri-check-line align-mmiddle"></i></a>
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="mb-0 fw-semibold"><a href="javascript:void(0);">Simon Cowell</a></p>
                                                                    <p class="text-muted fs-12 mb-0">@simon</p>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown">
                                                                <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-primary-light" data-bs-toggle="dropdown">
                                                                    <i class="fe fe-more-vertical"></i>
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Action</a></li>
                                                                    <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Another action</a></li>
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Something else here</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="swiper-slide">
                                                <div class="card custom-card">
                                                    <div class="card-body">
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/11.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/12.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/13.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/14.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <div class="text-muted fs-12 d-flex align-items-center">Collection Rank :
                                                                    <span class="fw-semibold text-success fs-15 mb-0 ms-1">#2</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-rounded bg-light p-1 avatar-sm">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <span class="fw-semibold fs-15">1.0466ETH</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="d-flex align-items-center">
                                                            <div class="d-flex align-items-center flex-fill">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="">
                                                                        <a aria-label="anchor" href="javascript:void(0);" class="badge rounded-pill bg-primary avatar-badge"><i class="ri-check-line align-mmiddle"></i></a>
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="mb-0 fw-semibold"><a href="javascript:void(0);">Melissa Smith</a></p>
                                                                    <p class="text-muted fs-12 mb-0">@melissa</p>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown">
                                                                <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-primary-light" data-bs-toggle="dropdown">
                                                                    <i class="fe fe-more-vertical"></i>
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Action</a></li>
                                                                    <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Another action</a></li>
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Something else here</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="swiper-slide">
                                                <div class="card custom-card">
                                                    <div class="card-body">
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/10.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/15.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/17.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                            <div class="col-6">
                                                                <img src="{{asset('build/assets/images/nft-images/16.png')}}" alt="" class="nft-featuredcollect-image">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <div class="text-muted fs-12 d-flex align-items-center">Collection Rank :
                                                                    <span class="fw-semibold text-success fs-15 mb-0 ms-1">#3</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-rounded bg-light p-1 avatar-sm">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <span class="fw-semibold fs-15">1.0355ETH</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="d-flex align-items-center">
                                                            <div class="d-flex align-items-center flex-fill">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="">
                                                                        <a aria-label="anchor" href="javascript:void(0);" class="badge rounded-pill bg-primary avatar-badge"><i class="ri-check-line align-mmiddle"></i></a>
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="mb-0 fw-semibold"><a href="javascript:void(0);">Json Talor</a></p>
                                                                    <p class="text-muted fs-12 mb-0">@taylor</p>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown">
                                                                <a aria-label="anchor" href="javascript:void(0);" class="btn btn-icon btn-sm btn-primary-light" data-bs-toggle="dropdown">
                                                                    <i class="fe fe-more-vertical"></i>
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Action</a></li>
                                                                    <li class="border-bottom"><a class="dropdown-item" href="javascript:void(0);">Another action</a></li>
                                                                    <li><a class="dropdown-item" href="javascript:void(0);">Something else here</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-6">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                History
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
                                            <ul class="list-group list-group-flush mb-0">
                                                <li class="list-group-item">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/nft-images/10.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Kakasha Si</p>
                                                                    <span class="text-muted fs-12">@sensei011</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <p class="mb-0 fw-semibold d-flex align-items-center fs-16">0.234<span class="text-muted fs-12 mt-1 ms-1">BTC</span></p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li class="list-group-item">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/nft-images/1.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Oorichimaru lo</p>
                                                                    <span class="text-muted fs-12">@ooro001</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <p class="mb-0 fw-semibold d-flex align-items-center fs-16">0.334<span class="text-muted fs-12 mt-1 ms-1">BTC</span></p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li class="list-group-item">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/nft-images/12.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">SakuraYM</p>
                                                                    <span class="text-muted fs-12">@sakura903</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <p class="mb-0 fw-semibold d-flex align-items-center fs-16">0.2534<span class="text-muted fs-12 mt-1 ms-1">BTC</span></p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li class="list-group-item">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/nft-images/4.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Sasuke Uchiha</p>
                                                                    <span class="text-muted fs-12">@sasuke777</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <p class="mb-0 fw-semibold d-flex align-items-center fs-16">0.3504<span class="text-muted fs-12 mt-1 ms-1">BTC</span></p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li class="list-group-item">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/nft-images/15.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Naruto Uzumaki</p>
                                                                    <span class="text-muted fs-12">@naruto111</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <p class="mb-0 fw-semibold d-flex align-items-center fs-16">0.2504<span class="text-muted fs-12 mt-1 ms-1">BTC</span></p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li class="list-group-item">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/nft-images/17.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Uchiha Uzumaki</p>
                                                                    <span class="text-muted fs-12">@uzumaki111</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <p class="mb-0 fw-semibold d-flex align-items-center fs-16">0.2504<span class="text-muted fs-12 mt-1 ms-1">BTC</span></p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li class="list-group-item">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/nft-images/6.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Nagiro Ohinavo</p>
                                                                    <span class="text-muted fs-12">@nagiro096</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <p class="mb-0 fw-semibold d-flex align-items-center fs-16">0.3564<span class="text-muted fs-12 mt-1 ms-1">BTC</span></p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-6">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Top Creators NFTs
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
                                            <ul class="list-group list-group-flush mb-0">
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
                                                                    <p class="fw-semibold mb-0 fs-14">Kakasha Si</p>
                                                                    <span class="text-muted fs-12">@sensei011</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <div class="ms-auto my-auto">
                                                        <a href="javascript:void(0);" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Follow</a>
                                                    </div>
                                                </li>
                                                <li class="list-group-item d-flex">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Oorichimaru lo</p>
                                                                    <span class="text-muted fs-12">@ooro001</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <div class="ms-auto my-auto">
                                                        <a href="javascript:void(0);" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Follow</a>
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
                                                                    <p class="fw-semibold mb-0 fs-14">SakuraYM</p>
                                                                    <span class="text-muted fs-12">@sakura903</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <div class="ms-auto my-auto">
                                                        <a href="javascript:void(0);" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Follow</a>
                                                    </div>
                                                </li>
                                                <li class="list-group-item d-flex">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Sasuke Uchiha</p>
                                                                    <span class="text-muted fs-12">@sasuke777</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <div class="ms-auto my-auto">
                                                        <a href="javascript:void(0);" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Follow</a>
                                                    </div>
                                                </li>
                                                <li class="list-group-item d-flex">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Naruto Uzumaki</p>
                                                                    <span class="text-muted fs-12">@naruto111</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <div class="ms-auto my-auto">
                                                        <a href="javascript:void(0);" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Follow</a>
                                                    </div>
                                                </li>
                                                <li class="list-group-item d-flex">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Uchiha Uzumaki</p>
                                                                    <span class="text-muted fs-12">@uzumaki111</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <div class="ms-auto my-auto">
                                                        <a href="javascript:void(0);" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Follow</a>
                                                    </div>
                                                </li>
                                                <li class="list-group-item d-flex">
                                                    <a href="javascript:void(0);">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div class="me-2 lh-1">
                                                                    <span class="avatar avatar-md">
                                                                        <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <p class="fw-semibold mb-0 fs-14">Nagiro Ohinavo</p>
                                                                    <span class="text-muted fs-12">@nagiro096</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <div class="ms-auto my-auto">
                                                        <a href="javascript:void(0);" class="btn btn-primary-light btn-sm  btn-wave waves-effect waves-light">Follow</a>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-9 col-lg-12">
                                    <div class="card custom-card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">
                                                Top NFTs
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
                                                <table class="table table-hover table-bordered text-nowrap">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Collection</th>
                                                            <th scope="col">Owners</th>
                                                            <th scope="col">Volume</th>
                                                            <th scope="col">24h %</th>
                                                            <th scope="col">7d %</th>
                                                            <th scope="col">Floor Price</th>
                                                            <th scope="col">Items</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-md avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/nft-images/1.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">Starter Sense NFT</a></p>
                                                                        <a href="javscript:void(0);" class="fs-12 text-muted fw-normal" title="creator_name">@irukasensei229</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span class="fw-semibold text-muted">5.55k</span></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">2.56BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-success"><i class="ti ti-trending-up me-1 align-middle"></i>15.2%</span>
                                                            </td>
                                                            <td><span class="text-success"><i class="ti ti-trending-up me-1 align-middle"></i>3.1%</span></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">2.31BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>12.4K</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-md avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/nft-images/2.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">Lorem Kekkei</a></p>
                                                                        <a href="javscript:void(0);" class="fs-12 text-muted fw-normal" title="creator_name">@clansound209</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span class="fw-semibold text-muted">6.13k</span></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">1.25BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-success"><i class="ti ti-trending-up me-1 align-middle"></i>3.7%</span>
                                                            </td>
                                                            <td><span class="text-danger"><i class="ti ti-trending-down me-1 align-middle"></i>0.5%</span></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">0.25BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>10.1K</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-md avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/nft-images/10.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">NFT Uchiha</a></p>
                                                                        <a href="javscript:void(0);" class="fs-12 text-muted fw-normal" title="creator_name">@sasukeuhi990</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span class="fw-semibold text-muted">7.23k</span></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">2,092BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span class="text-danger"><i class="ti ti-trending-down me-1 align-middle"></i>23.1%</span></td>
                                                            <td>
                                                                <span class="text-success"><i class="ti ti-trending-up me-1 align-middle"></i>9.12%</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name"> 2,000BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>52.7K</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-md avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/nft-images/12.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">
                                                                            Lorem Ipsum Uch</a></p>
                                                                        <a href="javscript:void(0);" class="fs-12 text-muted fw-normal" title="creator_name">@kakashi092</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span class="fw-semibold text-muted">1.07k</span></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name"> 36.25BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span class="text-danger"><i class="ti ti-trending-down me-1 align-middle"></i>5.2%</span></td>
                                                            <td>
                                                                <span class="text-danger"><i class="ti ti-trending-down me-1 align-middle"></i>4.1%</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">30.12BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>31.4K</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-md avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/nft-images/15.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">
                                                                            Ivan Shomer Har</a></p>
                                                                        <a href="javscript:void(0);" class="fs-12 text-muted fw-normal" title="creator_name">@narutouze025</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span class="fw-semibold text-muted">3.02k</span></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">12.52BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><span class="text-success"><i class="ti ti-trending-up me-1 align-middle"></i>7.0%</span></td>
                                                            <td>
                                                                <span class="text-success"><i class="ti ti-trending-up me-1 align-middle"></i>12.5%</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2 lh-1">
                                                                        <span class="avatar avatar-xs avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0 fw-semibold"><a href="javascript:void(0);" title="nft_name">12.50BTC</a></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>121.5K</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    Showing 5 Entries <i class="bi bi-arrow-right ms-2 fw-semibold"></i>
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

        <!-- SWIPER JS -->
        <script src="{{asset('build/assets/libs/swiper/swiper-bundle.min.js')}}"></script>

        <!-- NFT-DASHBOARD JS -->
        @vite('resources/assets/js/nft-dashboard.js')


@endsection