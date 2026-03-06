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
                                            <div class="col-xl-6 col-md-6 pe-0">
                                                <div class="p-5">
                                                    <p class="h5 fw-semibold mb-2 text-center">Create Password</p>
                                                    <p class="mb-4 text-muted op-7 fw-normal text-center">Hello Jhon !</p>
                                                    <div class="row gy-3">
                                                        <div class="col-xl-12">
                                                            <label for="create-password" class="form-label text-default">Password</label>
                                                            <div class="input-group">
                                                                <input type="password" class="form-control form-control-lg" id="create-password" placeholder="Password">
                                                                <button aria-label="button" type="button" class="btn btn-light bg-transparent" onclick="createpassword('create-password',this)"><i class="ri-eye-off-line align-middle"></i></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12 mb-2">
                                                            <label for="create-confirmpassword" class="form-label text-default">Confirm Password</label>
                                                            <div class="input-group">
                                                                <input type="password" class="form-control form-control-lg" id="create-confirmpassword" placeholder="Password">
                                                                <button aria-label="button" class="btn btn-light bg-transparent" onclick="createpassword('create-confirmpassword',this)" type="button"><i class="ri-eye-off-line align-middle"></i></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12 d-grid mt-4">
                                                            <button aria-label="button" type="button" class="btn btn-primary btn-lg">Save Password</button>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="fs-12 text-muted mt-3 mb-0">Back to home ? <a href="{{url('index')}}" class="text-primary">Click Here</a></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-md-6 ps-0 text-fixed-white d-none d-md-block  ">
                                                <div class="card custom-card mb-0 cover-background  overflow-hidden rounded-end rounded-0">
                                                    <div class="card-img-overlay d-flex  align-items-center p-0 rounded-0">
                                                        <div class="card-body p-5 rectangle3">
                                                            <div>
                                                                <a href="{{url('index')}}"> <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark"></a>
                                                            </div>
                                                            <h6 class="mt-4 fs-15 op-9  text-fixed-white">Create Password</h6>
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