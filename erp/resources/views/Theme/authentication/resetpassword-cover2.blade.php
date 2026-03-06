@extends('layouts.custom-master')

@section('styles')
 
      
@endsection

@section('content')

@section('errorbody')
<body class="bg-white">
@endsection

        <div class="row mx-0 authentication">

            <div class="col-xxl-6 col-xl-7 col-lg-12">
                <div class="row mx-0  justify-content-center align-items-center h-100">
                    <div class="col-xxl-6 col-xl-7 col-lg-7 col-md-7 col-sm-8 col-12">
                        <div class="p-sm-5 p-0">
                            <p class="h5 fw-semibold mb-2">Reset Password</p>
                            <p class="mb-3 text-muted op-7 fw-normal">Hello Jhon !</p>
                            <div class="row gy-3 mt-3">
                                <div class="col-xl-12 mt-0">
                                    <label for="reset-password" class="form-label text-default">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="reset-password" placeholder="current password">
                                        <button aria-label="button" class="btn btn-light bg-transparent" onclick="createpassword('reset-password',this)" type="button" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label for="reset-newpassword" class="form-label text-default">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="reset-newpassword" placeholder="new password">
                                        <button aria-label="button" class="btn btn-light bg-transparent" onclick="createpassword('reset-newpassword',this)" type="button" id="button-addon21"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-3">
                                    <label for="reset-confirmpassword" class="form-label text-default">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="reset-confirmpassword" placeholder="confirm password">
                                        <button aria-label="button" class="btn btn-light bg-transparent" onclick="createpassword('reset-confirmpassword',this)" type="button" id="button-addon22"><i class="ri-eye-off-line align-middle"></i></button>
                                    </div>
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <a href="{{url('signin-basic')}}" class="btn btn-lg btn-primary">Create</a>
                                    <label class="mt-1">
                                        Remembered your password ?<a href="{{url('signin-basic')}}" class="text-primary ms-2 d-inline-block">Login</a>
                                    </label>
                                </div>
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
                                <div class="text-fixed-white text-start d-flex align-items-center">
                                    <div>
                                        <h3 class="fw-semibold op-8 mb-3  text-fixed-white">Reset Password</h3>
                                        <p class="mb-5 fw-normal fs-14 op-6"> Lorem ipsum dolor sit amet, consectetur adipisicing elit.  Porro rem voluptates possimus, ad, autem quae culpa architecto, quam labore blanditiis at ratione.</p>
                                    </div>
                                </div>
                                <div class="authentication-footer text-end">
                                    <span class="text-fixed-white op-8"> Copyright © <span id="year">2023</span> <a href="javascript:void(0);" class=" text-fixed-white fw-semibold">Velvet</a>.
                                        Designed with <span class="bi bi-heart-fill text-danger"></span> by <a href="javascript:void(0);">
                                            <span class="fw-semibold text-primary text-decoration-underline">Spruko</span>
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

        <!-- SHOW PASSWORD JS -->
        <script src="{{asset('build/assets/show-password.js')}}"></script>

@endsection