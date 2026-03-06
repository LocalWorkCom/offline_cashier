@extends('layouts.custom-master')

@section('styles')
 
      
@endsection

@section('content')

@section('errorbody')
<body class="bg-white">
@endsection

        <div class="error-bg error-basic-background" id="particles-js">
            <!-- Start::error-page -->
            <div class="error-page constract constract3">
                <div class="container">
                    <!-- Start::row-1 -->
                    <div class="row  mx-0 align-items-center justify-content-center">
                        <div class="col-xxl-7 col-xl-8 col-lg-9 rectangle error-authentication ">
                            <div class="text-center authentication-style rectangle1">
                                <h1 class="construction-heading">Coming Soon</h1>
                                <p class="mt-4 mb-4 text-muted">Our website is almost Done, mean while enter your email id to get latest updates and notifications about the website.</p>
                                <div class="input-group mb-4">
                                    <input type="email" class="form-control form-control-lg" placeholder="info@gmail.com" aria-label="info@gmail.com" aria-describedby="button-addon2">
                                    <button class="btn btn-primary" type="button" id="button-addon2">Subscribe</button>
                                </div>
                                <div class="main d-flex justify-content-center flex-wrap">
                                    <div class="counter-item count  bg-info-transparent m-2 br-3">
                                        <h3 class="count_down">169</h3>
                                        <h6 class="">Days</h6>
                                    </div>
                                    <div class="counter-item count  bg-success-transparent m-2 br-3">
                                        <h3 class="count_down">13</h3>
                                        <h6 class="">Hours</h6>
                                    </div>
                                    <div class="counter-item count bg-warning-transparent m-2 br-3">
                                        <h3 class="count_down">16</h3>
                                        <h6 class="">Minutes</h6>
                                    </div>
                                    <div class="counter-item count bg-pink-transparent m-2 br-3">
                                        <h3 class="count_down">49</h3>
                                        <h6 class="">Seconds</h6>
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

        <!-- INTERNAL UNDER MAINTENANCE JS -->
        <script src="{{asset('build/assets/under-maintenance.js')}}"></script>
        
@endsection