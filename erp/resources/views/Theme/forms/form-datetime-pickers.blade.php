@extends('layouts.master')

@section('styles')

        <!-- SIMONWEP CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/@simonwep/pickr/themes/classic.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/@simonwep/pickr/themes/monolith.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/@simonwep/pickr/themes/nano.min.css')}}">

@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                        <h4 class="fw-medium mb-0">Date & Time Pickers</h4>
                        <div class="ms-sm-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Form Elements</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Date & Time Pickers</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- end PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start:: row-1 -->
                            <div class="row">
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Basic Date picker
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="date" placeholder="Choose date">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Date picker With Time
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="datetime" placeholder="Choose date with time">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Human Friendly dates
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="humanfrienndlydate" placeholder="Human friendly dates">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Date range picker
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="daterange" placeholder="Date range picker">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-1 -->

                            <!-- Start:: row-2 -->
                            <div class="row">
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Basic Time picker
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                                                    <input type="text" class="form-control" id="timepikcr" placeholder="Choose time">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Time Pickr with 24hr Format
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                                                    <input type="text" class="form-control" id="timepickr1" placeholder="Choose time in 24hr format">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Time Picker with Limits
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                                                    <input type="text" class="form-control" id="limittime" placeholder="choose time min 16:00 to max 22:30">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                DateTimePicker with Limited Time Range
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                                                    <input type="text" class="form-control" id="limitdatetime" placeholder="date with time limit from 16:00 to 22:00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-2 -->

                            <!-- Start:: row-3 -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        Date Picker with week numbers
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group mb-0">
                                                        <div class="input-group">
                                                            <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                            <input type="text" class="form-control" id="weeknum" placeholder="Choose date">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        Inline Time Picker
                                                    </div>
                                                </div>
                                                <div class="card-body overflow-auto">
                                                    <div class="form-group mb-0">
                                                        <input type="text" class="form-control" id="inlinetime" placeholder="Choose time">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        Preloading time
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group mb-0">
                                                        <div class="input-group">
                                                            <div class="input-group-text text-muted"> <i class="ri-time-line"></i> </div>
                                                            <input type="text" class="form-control" id="pretime" placeholder="Preloading time">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Inline Calendar
                                            </div>
                                        </div>
                                        <div class="card-body overflow-auto">
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="inlinecalendar2" placeholder="Choose date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-3 -->

                            <!-- Start::row-4 -->
                            <div class="row">
                                <div class="col-lg-3 col-md-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Bootstrap Color Picker
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input type="color" class="form-control form-control-color border-0"
                                                    id="exampleColorInput" value="#136ad0" title="Choose your color">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Classic-01
                                            </div>
                                        </div>
                                        <div class="card-body d-flex justify-content-center">
                                            <div>
                                                <div class="theme-container"></div>
                                                <div class="pickr-container example-picker"></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Classic-02
                                            </div>
                                        </div>
                                        <div class="card-body d-flex justify-content-center">
                                            <div>
                                                <div class="theme-container1"></div>
                                                <div class="pickr-container1 example-picker"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Classic-03
                                            </div>
                                        </div>
                                        <div class="card-body d-flex justify-content-center">
                                            <div>
                                                <div class="theme-container2"></div>
                                                <div class="pickr-container2 example-picker"></div>
                                            </div>   
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--End::row-4 -->

                        </div>
                    </div>
                    <!-- END APP CONTENT -->

@endsection

@section('scripts')
        
        <!-- COLOR PICKER JS -->
        @vite('resources/assets/js/color-picker.js')
        

@endsection