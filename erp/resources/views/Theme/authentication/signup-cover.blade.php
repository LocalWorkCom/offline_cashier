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
                    <div class="row justify-content-center ">
                        <div class="col-xl-8 col-md-12 col-sm-10 ">
                            <div class="card custom-card  rectangle2">
                                <div class="card-body p-0 ">
                                    <div class="row">
                                        <div class="col-xl-6 col-md-6 pe-sm-0 d-flex">
                                            <div class="p-5">
                                                <p class="h4 fw-semibold mb-2">Sign Up</p>
                                                <p class="mb-3 text-muted op-7 fw-normal">Welcome & Join us by creating a free account !</p>
                                                <div class="row gy-3 mt-3">
                                                    <div class="col-xl-12 mt-0">
                                                        <label for="signin-username" class="form-label text-default">Full Name</label>
                                                        <input type="text" class="form-control form-control-lg" id="signin-username" placeholder="user name">
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
                                                    <button class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri-facebook-line fw-bold "></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri-google-line fw-bold "></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-light btn-wave waves-effect waves-light">
                                                        <i class="ri-twitter-line fw-bold "></i>
                                                    </button>
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
                                                        <h6 class="mt-4 fs-15 op-9  text-fixed-white">Sign Up</h6>
                                                        <div class="d-flex mt-3  text-fixed-white">
                                                            <p class="mb-0 fw-normal fs-14 op-7"> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ipsa eligendi expedita aliquam quaerat nulla voluptas facilis.
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