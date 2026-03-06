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
                <div class="container-lg">
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
                                    <p class="h4 fw-semibold mb-2 text-center">Sign Up</p>
                                    <p class="mb-4 text-muted op-7 fw-normal text-center">Welcome &amp; Join us by creating a free account !</p>
                                    <div class="row gy-3">
                                        <div class="col-xl-12">
                                            <label for="signup-firstname" class="form-label text-default">Full Name</label>
                                            <input type="text" class="form-control form-control-lg" id="signup-firstname" placeholder="first name">
                                        </div>
                                        <div class="col-xl-12">
                                            <label for="signup-lastname" class="form-label text-default">Email</label>
                                            <input type="email" class="form-control form-control-lg" id="signup-lastname" placeholder="emali">
                                        </div>
                                        <div class="col-xl-12 d-grid mt-2">
                                            <a href="{{url('signin-basic')}}" class="btn btn-lg btn-primary mt-4">Create Account</a>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <p class="fs-12 text-muted mt-3">Already have an account? <a href="{{url('signin-basic')}}" class="text-primary">Sign In</a></p>
                                    </div>
                                    <div class="text-center my-3 authentication-barrier">
                                        <span>OR</span>
                                    </div>
                                    <div class="btn-list text-center">
                                        <button aria-label="button" type="button" class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                            <i class="ri-facebook-line fw-bold "></i>
                                        </button>
                                        <button aria-label="button" type="button" class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                            <i class="ri-google-line fw-bold "></i>
                                        </button>
                                        <button aria-label="button" type="button" class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                            <i class="ri-twitter-line fw-bold "></i>
                                        </button>
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