@extends('layouts.custom-master')

@section('styles')
 
      
@endsection

@section('content')

@section('errorbody')
<body class="bg-white">
@endsection

        <div class="row mx-0 authentication">

            <div class="col-xxl-6 col-xl-7 col-lg-12">
                <div class="row  mx-0 justify-content-center align-items-center h-100">
                    <div class="col-xxl-6 col-xl-7 col-lg-7 col-md-7 col-sm-8 col-12">
                        <div class="p-sm-5 p-3">
                            <p class="h5 fw-semibold mb-1">Verify Your Account</p>
                            <p class="mb-4 text-muted op-7 fw-normal">Enter the 4 digit code sent to the registered email Id.</p>
                            <div class="row mx-0">
                                <div class="col-xl-12 mb-4 ps-0">
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
                                    <div class="form-check mt-2 mb-0">
                                        <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                                        <label class="form-check-label" for="defaultCheck1">
                                            Did not recieve a code ?<a href="javascript:void(0);" class="text-primary ms-2 d-inline-block">Resend</a>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xl-12 d-grid ps-0">
                                    <a href="{{url('index')}}" class="btn btn-lg btn-primary">Verify</a>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="fs-12 text-danger mt-3"><sup><i class="ri-asterisk"></i></sup>Don't share the verification code with anyone !</p>
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
                                        <h3 class="fw-semibold op-8 mb-3  text-fixed-white">Verify Your Account</h3>
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

        <!-- INTERNAL TWO STEP VERIFICATION JS -->
        <script src="{{asset('build/assets/two-step-verification.js')}}"></script>

@endsection