@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                        <h4 class="fw-medium mb-0">Input Masks</h4>
                        <div class="ms-sm-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Form Elements</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Input Masks</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start:: row-1 -->
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Date Format-1
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control date-format" placeholder="DD-MM-YYYY">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Date Format-2
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control date-format-1" placeholder="MM-DD-YYYY">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Date Format-3
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control date-format-2" placeholder="MM-YY">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-1 -->

                            <!-- Start:: row-2 -->
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Number Formatting
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control number-format" placeholder="Number Here">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Time Format-1
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control time-format-1" placeholder="hh:mm:ss">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Time Format-2
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control time-format-2" placeholder="hh:mm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-2 -->

                            <!-- Start:: row-3 -->
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Formatting Into Blocks
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control formatting-blocks" placeholder="ABCD EFG HIJ KLMN">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Delimiter
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control delimiter" placeholder="ABC.DEF.GHi">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Delimiters
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control delimiters" placeholder="ABC/DEF/GHi-JK">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-3 -->

                            <!-- Start:: row-4 -->
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Prefix
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control prefix-element" type="text">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Phone Number Formatting
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <input class="form-control phone-number"  placeholder="US(+1)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-4 -->

                        </div>
                    </div>
                    <!-- END APP CONTENT -->
        
@endsection

@section('scripts')

        <!-- CLEAVE JS -->
        <script src="{{asset('build/assets/libs/cleave.js/cleave.min.js')}}"></script>

        <!-- INTERNAL FROM-INPUT-MASK JS  -->
        @vite('resources/assets/js/form-input-mask.js')
        

@endsection