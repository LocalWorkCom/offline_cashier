@extends('layouts.custom-master')

@section('styles')
 
      
@endsection

@section('content')

@section('errorbody')
<body class="bg-white">
@endsection 

        <div class="error-bg error-basic-background"  id="particles-js">
            <!-- Start::error-page -->
            <div class="error-page constract constract2 ">
                <div class="container">
                    <!-- Start::row-1 -->
                    <div class="row justify-content-center align-items-center">
                        <div class="col-xxl-7 col-xl-8 col-lg-9 rectangle error-authentication ">
                            <div class="text-center authentication-style rectangle1">
                                <h1 class="construction-heading">Under <span class="text-primary">Construction</span></h1>
                                <p class="mt-4 mb-5 text-muted">The Page you're looking for is currently under maintenance and will be back soon. Subscribe to get notified</p>
                                <div class="main d-flex justify-content-center flex-wrap">
                                    <div class="counter-item count  bg-info-transparent m-2 br-3">
                                        <h3 class="count_down">0</h3>
                                        <h6 class="">Days</h6>
                                    </div>
                                    <div class="counter-item count  bg-success-transparent m-2 br-3">
                                        <h3 class="count_down">0</h3>
                                        <h6 class="">Hours</h6>
                                    </div>
                                    <div class="counter-item count bg-warning-transparent m-2 br-3">
                                        <h3 class="count_down">0</h3>
                                        <h6 class="">Minutes</h6>
                                    </div>
                                    <div class="counter-item count bg-pink-transparent m-2 br-3">
                                        <h3 class="count_down">0</h3>
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

        <!-- INTERNAL MAINTENANCE JS -->
        <script src="{{asset('build/assets/under-maintenance.js')}}"></script>

@endsection