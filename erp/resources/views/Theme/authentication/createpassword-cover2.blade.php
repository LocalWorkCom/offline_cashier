@extends('layouts.custom-master')

@section('styles')

        <!-- SWIPER CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/swiper/swiper-bundle.min.css')}}">
        
@endsection

@section('content')

@section('errorbody')
<body class="bg-white">
@endsection
    
        <div class="row mx-0 authentication">
            <div class="col-xxl-6 col-xl-7 col-lg-12">
                <div class="row  mx-0 justify-content-center align-items-center h-100">
                    <div class="col-xxl-6 col-xl-7 col-lg-7 col-md-7 col-sm-8 col-12">
                        <div class="p-sm-5 p-0">
                            <p class="h5 fw-semibold mb-2">Create Password</p>
                            <p class="mb-3 text-muted op-7 fw-normal">Hello Jhon !</p>
                            <div class="row gy-3 mt-3">
                                <div class="col-xl-12 mt-0">
                                    <label for="create-password" class="form-label text-default">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="create-password" placeholder="Password">
                                        <button aria-label="button" type="button" onclick="createpassword('create-password',this)" class="btn btn-light bg-transparent"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-3">
                                    <label for="create-confirmpassword" class="form-label text-default">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="create-confirmpassword" placeholder="Password">
                                        <button aria-label="button" type="button" onclick="createpassword('create-confirmpassword',this)" class="btn btn-light bg-transparent"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                    <div class="mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                            <label class="form-check-label text-muted fw-normal" for="defaultCheck1">
                                                Remember password ?
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <button type="button" class="btn btn-lg btn-primary">Save Password</button>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="fs-12 text-muted mt-4">Back to home ? <a href="{{url('index')}}" class="text-primary">Click Here</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-5 col-lg-5 d-xl-block d-none px-0">
                <div class="authentication-cover">
                    <img src="{{asset('build/assets/images/authentication/2.png')}}" class="authentication1" alt="">
                    <img src="{{asset('build/assets/images/authentication/3.png')}}" class="authentication2" alt="">
                    <div class="">
                        <div class="row justify-content-center g-0">
                            <div class="col-xl-9">
                                <a href="{{url('index')}}"> <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="" class="authentication-brand cover-dark-logo op-9"></a> 
                                <div class="text-fixed-white text-start  d-flex align-items-center">
                                    <div>
                                        <h3 class="fw-semibold op-8 mb-3 text-fixed-white">Create Password</h3>
                                        <p class="mb-5 fw-normal fs-14 op-6"> Lorem ipsum dolor sit amet, consectetur adipisicing elit.  Porro rem voluptates possimus, ad, autem quae culpa architecto, quam labore blanditiis at ratione.</p>
                                    </div>
                                </div>
                                <div class="authentication-footer text-end">
                                    <span class="text-fixed-white op-8"> Copyright © <span id="year">2023</span> <a href="javascript:void(0);" class=" text-fixed-white fw-semibold">Velvet</a>.
                                        Designed with <span class="bi bi-heart-fill text-danger"></span> by <a href="javascript:void(0);">
                                            <span class="fw-semibold text-secondary text-decoration-underline">Spruko</span>
                                        </a> All
                                        rights
                                        reserved
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endsection

@section('scripts')

        <!-- SWIPER JS -->
        <script src="{{asset('build/assets/libs/swiper/swiper-bundle.min.js')}}"></script>

        <!-- SHOW PASSWORD JS -->
        <script src="{{asset('build/assets/show-password.js')}}"></script>
        
@endsection