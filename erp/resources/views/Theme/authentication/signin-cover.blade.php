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
                    <div class="row justify-content-center">
                        <div class="col-xl-8 col-md-12 col-sm-10 ">
                            <div class="card custom-card  rectangle2">
                                <div class="card-body p-0 ">
                                    <div class="row">
                                        <div class="col-xl-6 col-md-6 pe-sm-0">
                                            <div class="p-sm-5 p-3">
                                                <p class="h4 fw-semibold mb-2">Sign In</p>
                                                <p class="mb-3 text-muted op-7 fw-normal">Welcome back Jhon !</p>
                                                <div class="row gy-3 mt-3">
                                                    <div class="col-xl-12 mt-0">
                                                        <label for="signin-username" class="form-label text-default">User Name</label>
                                                        <input type="text" class="form-control form-control-lg" id="signin-username" placeholder="user name">
                                                    </div>
                                                    <div class="col-xl-12 mb-3">
                                                        <label for="signin-password" class="form-label text-default d-block">Password<a href="{{url('resetpassword-cover')}}" class="float-end text-primary">Forget password ?</a></label>
                                                        <div class="input-group">
                                                            <input type="password" class="form-control form-control-lg" id="signin-password" placeholder="password">
                                                            <button class="btn btn-light bg-transparent" type="button" onclick="createpassword('signin-password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
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
                                                    <div class="col-xl-12 d-grid mt-3">
                                                        <a href="{{url('index')}}" class="btn btn-lg btn-primary">Sign In</a>
                                                    </div>
                                                </div>
                                                <div class="text-center my-4 authentication-barrier">
                                                    <span>OR</span>
                                                </div>
                                                <div class="btn-list text-center">
                                                    <button class="btn btn-light "><svg class="google-svg" xmlns="http://www.w3.org/2000/svg" width="2443" height="2500" preserveAspectRatio="xMidYMid" viewBox="0 0 256 262"><path fill="#4285F4" d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027"/><path fill="#34A853" d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1"/><path fill="#FBBC05" d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782"/><path fill="#EB4335" d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251"/></svg>Sign In with google</button>
                                                    <button class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri-facebook-line fw-bold "></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri-twitter-line fw-bold "></i>
                                                    </button>
                                                </div>
                                                <div class="text-center ">
                                                    <p class="fs-12 text-muted mt-4 mb-0">Dont have an account? <a href="{{url('signup-cover')}}" class="text-primary">Sign Up</a></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-md-6 ps-0 text-fixed-white rounded-0 d-none d-md-block  ">
                                            <div class="card custom-card mb-0 cover-background overflow-hidden rounded-end rounded-0">
                                                <div class="card-img-overlay d-flex  align-items-center p-0 rounded-0">
                                                    <div class="card-body p-5 rectangle3">
                                                        <div>
                                                            <a href="{{url('index')}}"> <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark"></a>
                                                        </div>
                                                        <h6 class="mt-4 fs-15 op-9  text-fixed-white">Sign In</h6>
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
                </div>
                <!--End::row-1 -->
            </div>
            <!-- End::error-page -->
        </div>

@endsection

@section('scripts')

        <!-- SHOW PASSWORD JS -->
        <script src="{{asset('build/assets/show-password.js')}}"></script>

@endsection