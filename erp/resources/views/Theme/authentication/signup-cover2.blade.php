@extends('layouts.custom-master')

@section('styles')
 
      
@endsection

@section('content')

@section('errorbody')
<body class="bg-white">
@endsection

        <div class="row mx-0 authentication">
            <div class="col-xxl-6 col-xl-7 col-lg-12">
                <div class="row mx-0 justify-content-center align-items-center h-100">
                    <div class="col-xxl-6 col-xl-7 col-lg-7 col-md-7 col-sm-8 col-12">
                        <div class="p-sm-5 p-0">
                            <p class="h5 fw-semibold mb-2">Sign Up</p>
                            <p class="mb-3 text-muted op-7 fw-normal">Welcome & Join us by creating a free account !</p>
                            <div class="row gy-3">
                                <div class="row gy-3 mt-3">
                                    <div class="col-xl-12">
                                        <label for="signup-firstname" class="form-label text-default">Full Name</label>
                                        <input type="text" class="form-control form-control-lg" id="signup-firstname" placeholder="first name">
                                    </div>
                                    <div class="col-xl-12">
                                        <label for="signup-lastname" class="form-label text-default">Email</label>
                                        <input type="email" class="form-control form-control-lg" id="signup-lastname" placeholder="emali">
                                    </div>
                                    <div class="col-xl-12 d-grid mt-4">
                                        <a href="{{url('signin-basic')}}" class="btn btn-lg btn-primary">Create Account</a>
                                    </div>
                                </div>
                                <div class="text-center my-4 authentication-barrier">
                                    <span>OR</span>
                                </div>
                                <div class="btn-list text-center">
                                    <button class="btn btn-light"><svg class="google-svg" xmlns="http://www.w3.org/2000/svg" width="2443" height="2500" preserveAspectRatio="xMidYMid" viewBox="0 0 256 262"><path fill="#4285F4" d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027"/><path fill="#34A853" d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1"/><path fill="#FBBC05" d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782"/><path fill="#EB4335" d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251"/></svg>Sign up with google</button>
                                    <button class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                        <i class="ri-facebook-line fw-bold "></i>
                                    </button>
                                    <button class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                        <i class="ri-twitter-line fw-bold "></i>
                                    </button>
                                </div>
                                <div class="text-center">
                                    <p class="fs-12 text-muted mt-4">Already have an account? <a href="{{url('signin-cover')}}" class="text-primary  d-inline-block">Sign In</a></p>
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
                                        <h3 class="fw-semibold op-8 mb-3  text-fixed-white">Sign Up</h3>
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