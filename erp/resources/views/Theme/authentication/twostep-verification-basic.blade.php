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
                    <div class="row justify-content-center align-items-center  authentication authentication-basic h-100">
                        <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                            <div class="my-5 d-flex justify-content-center">
                                <a href="{{url('index')}}">
                                    <img src="{{asset('build/assets/images/brand-logos/desktop-logo.png')}}" alt="logo" class="desktop-logo">
                                    <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark">
                                </a>
                            </div>
                            <div class="card custom-card rectangle2">
                                <div class="card-body p-5 rectangle3">
                                    <p class="h5 fw-semibold mb-2 text-center">Verify Your Account</p>
                                    <p class="mb-4 text-muted op-7 fw-normal text-center">Enter the 4 digit code sent to the registered email Id.</p>
                                    <div class="row gy-3">
                                        <div class="col-xl-12 mb-2">
                                            <div class="row">
                                                <div class="col-3">
                                                    <input type="text" class="form-control form-control-lg text-center" id="one" maxlength="1" onkeyup="clickEvent(this,'two')">
                                                </div>
                                                <div class="col-3">
                                                    <input type="text" class="form-control form-control-lg text-center" id="two" maxlength="1" onkeyup="clickEvent(this,'three')">
                                                </div>
                                                <div class="col-3">
                                                    <input type="text" class="form-control form-control-lg text-center" id="three" maxlength="1" onkeyup="clickEvent(this,'four')">
                                                </div>
                                                <div class="col-3">
                                                    <input type="text" class="form-control form-control-lg text-center" id="four" maxlength="1">
                                                </div>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                                <label class="form-check-label" for="defaultCheck1">
                                                    Did not recieve a code ?<a href="javascript:void(0);" class="text-primary ms-2 d-inline-block">Resend</a>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 d-grid mt-2">
                                            <a href="{{url('index')}}" class="btn btn-lg btn-primary">Verify</a>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <p class="fs-12 text-danger mt-3 mb-0"><sup><i class="ri-asterisk"></i></sup>Don't share the verification code with anyone !</p>
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

        <!-- INTERNAL TWO STEP VERIFICATION JS -->
        <script src="{{asset('build/assets/two-step-verification.js')}}"></script>

@endsection