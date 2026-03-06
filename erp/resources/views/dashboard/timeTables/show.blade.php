@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('timeTable.show')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('timeTable.index') }}">@lang('timeTable.timeTables')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('timeTable.show')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-4 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('timeTable.timeTableDetails')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.ID')</label>
                                    <p class="form-text">{{ $timeTable->id }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.name')</label>
                                    <p class="form-text">
                                        {{ app()->getLocale() === 'ar' ? $timeTable->name_ar : $timeTable->name_en }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.onDuty')</label>
                                    <p class="form-text">{{ $timeTable->on_duty_time }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.offDuty')</label>
                                    <p class="form-text">{{ $timeTable->off_duty_time }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.startSignIn')</label>
                                    <p class="form-text">{{ $timeTable->start_sign_in }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.endSignIn')</label>
                                    <p class="form-text">{{ $timeTable->end_sign_in }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.startSignOut')</label>
                                    <p class="form-text">{{ $timeTable->start_sign_out }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.endSignOut')</label>
                                    <p class="form-text">{{ $timeTable->end_sign_out }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.lateGrace')</label>
                                    <p class="form-text">{{ $timeTable->lateness_grace_period }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.startLateOption')</label>
                                    <p class="form-text">{{ $timeTable->start_late_time_option }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('timeTable.crossDay')</label>
                                    <p class="form-text">
                                        {{ $timeTable->cross_day ? __('timeTable.yes') : __('timeTable.no') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-4 -->
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- DATA-TABLES CDN -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
@endsection
