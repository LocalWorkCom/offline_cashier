@extends('layouts.custom-master')

@section('styles')
 
      
@endsection

@section('content')

@section('errorbody')
<body>
@endsection

        <div class="page error-bg" id="particles-js">
            <canvas class="error-basic-background"></canvas>
            <!-- Start::error-page -->
            <div class="error-page  ">
                <div class="container">
                    <!-- Start::row-1 -->
                    <div class="row align-items-center justify-content-center">
                        <div class="col-xxl-7 col-xl-8 col-lg-9 rectangle error-authentication ">
                            <div class="text-center authentication-style rectangle1">
                                <div class="lh-1 mb-3  d-inline-table">
                                    <span class="text-info text-large fw-bold">4</span>
                                    <span class="text-success text-large fw-bold">0</span>
                                    <span class="text-warning text-large fw-bold">1</span>
                                </div>
                                <span class="d-block fs-15 mb-3">Oops &#128557;,The page you are looking for is not available.</span>
                                <p class="text-muted fw-normal">
                                    We are sorry for the inconvenience,The page you are trying to access has been removed or never been existed.
                                </p>

                                <a href="{{url('index')}}" class="btn btn-primary"><i class="ri-arrow-left-line align-middle me-1 d-inline-block"></i>BACK TO HOME</a>
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

        <!-- PARTICLES JS -->
        <script src="{{asset('build/assets/libs/particlesjs/particles.min.js')}}"></script>

        <!-- ERROR JS -->
        @vite('resources/assets/js/error.js')
        

@endsection