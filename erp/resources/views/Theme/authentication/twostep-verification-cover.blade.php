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
                            <div class="col-xl-8 col-md-10 col-sm-10 ">
                                <div class="card custom-card  rectangle2">
                                    <div class="card-body p-0 ">
                                        <div class="row justify-content-center">
                                            <div class="col-md-6  pe-0">
                                                <div class="">
                                                    <div class="card-body p-5">
                                                        <p class="h5 fw-semibold mb-1">Verify Your Account</p>
                                                        <p class="mb-4 text-muted op-7 fw-normal">Enter the 4 digit code sent to the registered email Id.</p>
                                                        <div class="row">
                                                            <div class="col-xl-12 mb-4">
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
                                                            <div class="col-xl-12 d-grid">
                                                                <a href="{{url('index')}}" class="btn btn-lg btn-primary">Verify</a>
                                                            </div>
                                                        </div>
                                                        <div class="text-center">
                                                            <p class="fs-12 text-danger mt-3"><sup><i class="ri-asterisk"></i></sup>Don't share the verification code with anyone !</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-md-6 ps-0 text-fixed-white rounded-0 d-none d-md-block  ">
                                                <div class="card custom-card mb-0 cover-background overflow-hidden rounded-end rounded-0">
                                                    <div class="card-img-overlay d-flex  align-items-center p-0 rounded-">
                                                        <div class="card-body p-5 rectangle3">
                                                            <div>
                                                                <a href="{{url('index')}}"> <img src="{{asset('build/assets/images/brand-logos/desktop-dark.png')}}" alt="logo" class="desktop-dark"></a>
                                                            </div>
                                                            <h6 class="mt-4 fs-15 op-9  text-fixed-white">Verify Your Account</h6>
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

        <!-- INTERNAL TWO STEP VERIFICATION JS -->
        <script src="{{asset('build/assets/two-step-verification.js')}}"></script>

@endsection