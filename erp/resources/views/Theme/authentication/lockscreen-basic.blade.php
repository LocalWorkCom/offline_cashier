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
                            <div class="my-5 d-flex justify-content-center">
                                <a href="{{url('index')}}">
                                    <img src="{{asset('build/assets/images/brand-logos/desktop-logo.png')}}" alt="logo" class="desktop-logo">
                                    <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark">
                                </a>
                            </div>
                            <div class="card custom-card rectangle2">
                                <div class="card-body p-5 rectangle3">
                                    <p class="h5 fw-semibold mb-2 text-center">Lock Screen</p>
                                    <p class="mb-3 text-muted op-7 fw-normal text-center">Hello Jhon !</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="lh-1">
                                            <span class="avatar avatar-md avatar-rounded">
                                                <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="ms-3">
                                            <p class="mb-0 fw-semibold text-dark">jhonslicer21@gmail.com</p>
                                        </div>
                                    </div>
                                    <div class="row gy-3">
                                        <div class="col-xl-12 mb-2">
                                            <label for="lockscreen-password" class="form-label text-default">Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control form-control-lg" id="lockscreen-password" placeholder="password">
                                                <button class="btn btn-light bg-transparent" onclick="createpassword('lockscreen-password',this)" type="button" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                            </div>
                                            <div class="mt-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                                    <label class="form-check-label text-muted fw-normal" for="defaultCheck1">
                                                        Remember password ?
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 d-grid mt-2">
                                            <a href="{{url('index')}}" class="btn btn-primary">Unlock</a>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <p class="fs-12 text-muted mt-3 mb-0">Try unlock with different methods <a class="text-success" href="javascript:void(0);"><u>Finger print</u></a> / <a class="text-success" href="javascript:void(0);"><u>Face Id</u></a></p>
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