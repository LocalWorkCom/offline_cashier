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

                    <!-- Start::row-1 -->
                    <div class="row  justify-content-center ">
                        <div class="col-xl-8 col-md-12 col-sm-10 ">
                            <div class="card custom-card  rectangle2">
                                <div class="card-body p-0 ">
                                    <div class="row">
                                        <div class="col-xl-6 col-md-6 pe-0">
                                            <div class="p-5">
                                                <p class="h5 fw-semibold mb-2">Reset Password</p>
                                                <p class="mb-3 text-muted op-7 fw-normal">Hello Jhon !</p>
                                                <div class="row gy-3 mt-3">
                                                    <div class="col-xl-12 mt-0">
                                                        <label for="reset-password" class="form-label text-default">Current Password</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control form-control-lg" id="reset-password" placeholder="current password">
                                                            <button class="btn btn-light bg-transparent" onclick="createpassword('reset-password',this)" type="button" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <label for="reset-newpassword" class="form-label text-default">New Password</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control form-control-lg" id="reset-newpassword" placeholder="new password">
                                                            <button class="btn btn-light bg-transparent" onclick="createpassword('reset-newpassword',this)" type="button" id="button-addon21"><i class="ri-eye-off-line align-middle"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12 mb-3">
                                                        <label for="reset-confirmpassword" class="form-label text-default">Confirm Password</label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control form-control-lg" id="reset-confirmpassword" placeholder="confirm password">
                                                            <button class="btn btn-light bg-transparent" onclick="createpassword('reset-confirmpassword',this)" type="button" id="button-addon22"><i class="ri-eye-off-line align-middle"></i></button>
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
                                        <div class="col-xl-6 col-md-6 ps-0 text-fixed-white d-none d-md-block  ">
                                            <div class="card custom-card mb-0 cover-background overflow-hidden rounded-end rounded-0">
                                                <div class="card-img-overlay d-flex  align-items-center p-0 rounded-0">
                                                    <div class="card-body p-5 rectangle3">
                                                        <div>
                                                            <a href="{{url('index')}}"> <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark"></a>
                                                        </div>
                                                        <h6 class="mt-4 fs-15 op-9  text-fixed-white">Reset Password</h6>
                                                        <div class="d-flex mt-3">
                                                            <p class="mb-0 fw-normal fs-14 op-7  text-fixed-white"> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa eligendi expedita aliquam quaerat nulla voluptas facilis.
                                                                Porro rem voluptates possimus, ad, autem quae culpa architecto, quam labore blanditiis at ratione.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->
                </div>
            </div>
            <!-- End::error-page -->
        </div>

@endsection

@section('scripts')

        <!-- SHOW PASSWORD JS -->
        <script src="{{asset('build/assets/show-password.js')}}"></script>

@endsection