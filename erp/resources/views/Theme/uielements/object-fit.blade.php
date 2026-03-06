@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                        <h4 class="fw-medium mb-0">Object Fit</h4>
                        <div class="ms-sm-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Ui Elements</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Object Fit</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start::row-1 -->
                            <div class="row">   
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit Contain</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-contain border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit Cover</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-cover border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit Fill</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-fill border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit Scale Down</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-scale border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit None</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-none border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit Contain (SM - responsive)</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-sm-contain border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit Contain (MD - responsive)</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-md-contain border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit Contain (LG - responsive)</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-lg-contain border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit Contain (XL - responsive)</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-xl-contain border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Object Fit Contain (XXL - responsive)</div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="object-fit-xxl-contain border rounded" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Object Fit Contain Video
                                            </div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <video src="{{asset('build/assets/video/1.mp4')}}" class="object-fit-contain rounded border" autoplay></video>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Object Fit Cover Video
                                            </div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <video src="{{asset('build/assets/video/1.mp4')}}" class="object-fit-cover rounded border" autoplay></video>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Object Fit Fill Video
                                            </div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <video src="{{asset('build/assets/video/1.mp4')}}" class="object-fit-fill rounded border" autoplay></video>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Object Fit Scale Video
                                            </div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <video src="{{asset('build/assets/video/1.mp4')}}" class="object-fit-scale rounded border" autoplay></video>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Object Fit None Video
                                            </div>
                                        </div>
                                        <div class="card-body object-fit-container">
                                            <video src="{{asset('build/assets/video/1.mp4')}}" class="object-fit-none rounded border" autoplay></video>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End::row-1 -->

                        </div>
                    </div>
                    <!-- END APP CONTENT -->
        
@endsection

@section('scripts')
  

@endsection
