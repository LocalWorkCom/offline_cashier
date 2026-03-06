@extends('layouts.custom-master')

@section('styles')
 
      
@endsection

@section('content')

@section('errorbody')
<body>
@endsection

        <div class="page error-bg" id="particles-js">
            <!-- Start::error-page -->
            <div class="error-page  ">
                <div class="container">
                    <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
                        <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                            <div class="my-5 d-flex justify-content-center">
                                <a href="{{url('index')}}">
                                    <img src="{{asset('build/assets/images/brand-logos/desktop-logo.png')}}" alt="logo" class="desktop-logo">
                                    <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark">
                                </a>
                            </div>
                            <div class="card custom-card  rectangle2">
                                <div class="card-body p-sm-5 p-3  rectangle3">
                                    <p class="h5 fw-semibold mb-2 text-center">Reset Password</p>
                                    <p class="mb-4 text-muted op-7 fw-normal text-center">Hello Jhon !</p>
                                    <div class="row gy-3">
                                        <div class="col-xl-12">
                                            <label for="reset-password" class="form-label text-default">Current Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control form-control-lg" id="reset-password" placeholder="current password">
                                                <button class="btn btn-light bg-transparent" type="button" onclick="createpassword('reset-password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <label for="reset-newpassword" class="form-label text-default">New Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control form-control-lg" id="reset-newpassword" placeholder="new password">
                                                <button class="btn btn-light bg-transparent" type="button" onclick="createpassword('reset-newpassword',this)" id="button-addon21"><i class="ri-eye-off-line align-middle"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 mb-2">
                                            <label for="reset-confirmpassword" class="form-label text-default">Confirm Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control form-control-lg" id="reset-confirmpassword" placeholder="confirm password">
                                                <button class="btn btn-light bg-transparent" type="button" onclick="createpassword('reset-confirmpassword',this)" id="button-addon22"><i class="ri-eye-off-line align-middle"></i></button>
                                            </div>
                                            <div class="col-xl-12 d-grid mt-4">
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
                    </div>
                </div>
            </div>
            <!-- End::error-page -->
        </div>
       
@endsection

@section('scripts')

        <!-- SHOW PASSWORD JS -->
        <script src="{{asset('build/assets/show-password.js')}}"></script>

@endsection