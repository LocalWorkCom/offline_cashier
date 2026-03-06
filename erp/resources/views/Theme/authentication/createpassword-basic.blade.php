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
                    <div class="row align-items-center justify-content-center authentication authentication-basic">
                        <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                            <div class="my-5 d-flex justify-content-center ">
                                <a href="{{url('index')}}">
                                    <img src="{{asset('build/assets/images/brand-logos/desktop-logo.png')}}" alt="logo" class="desktop-logo">
                                    <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark">
                                </a>
                            </div>
                            <div class="card custom-card rectangle2 ">
                                <div class="card-body p-sm-5 p-3 rectangle3">
                                    <p class="h5 fw-semibold mb-2 text-center">Create Password</p>
                                    <p class="mb-4 text-muted op-7 fw-normal text-center">Hello Jhon !</p>
                                    <div class="row gy-3">
                                        <div class="col-xl-12">
                                            <label for="create-password" class="form-label text-default">Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control form-control-lg" id="create-password" placeholder="Password">
                                                <button aria-label="button" type="button" class="btn btn-light bg-transparent" onclick="createpassword('create-password',this)"><i class="ri-eye-off-line align-middle text-muted"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 mb-2">
                                            <label for="create-confirmpassword" class="form-label text-default">Confirm Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control form-control-lg" id="create-confirmpassword" placeholder="Password">
                                                <button aria-label="button" type="button" class="btn btn-light bg-transparent" onclick="createpassword('create-confirmpassword',this)"><i class="ri-eye-off-line align-middle text-muted"></i></button>
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