@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('timeTable.editTable')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('timeTable.index') }}">@lang('timeTable.timeTables')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('timeTable.editTable')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('timeTable.editTable')
                            </div>
                        </div>
                        <div class="card-body">
                            @if (session('error'))
                                <div class="alert alert-solid-danger alert-dismissible fade show">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endif
                            @if (session('message'))
                                <div class="alert alert-solid-info alert-dismissible fade show">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endif
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <form method="POST" action="{{ route('timeTable.update', $timetable->id) }}"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <!-- Arabic Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_ar" class="form-label">@lang('timeTable.arabicName')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar"
                                            value="{{ old('name_ar', $timetable->name_ar) }}"
                                            placeholder="@lang('timeTable.arabicName')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicName')
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_en" class="form-label">@lang('timeTable.englishName')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en"
                                            value="{{ old('name_en', $timetable->name_en) }}"
                                            placeholder="@lang('timeTable.englishName')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishName')
                                        </div>
                                    </div>

                                    <!-- On Duty Time -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="on_duty_time" class="form-label">@lang('timeTable.onDuty')</label>
                                        <input type="time" class="form-control" id="on_duty_time" name="on_duty_time"
                                            value="{{ old('on_duty_time', \Carbon\Carbon::parse($timetable->on_duty_time)->format('H:i')) }}"
                                            required>
                                        <!-- ... -->
                                    </div>

                                    <!-- Off Duty Time -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="off_duty_time" class="form-label">@lang('timeTable.offDuty')</label>
                                        <input type="time" class="form-control" id="off_duty_time" name="off_duty_time"
                                            value="{{ old('off_duty_time', \Carbon\Carbon::parse($timetable->off_duty_time)->format('H:i')) }}"
                                            required>
                                        <!-- ... -->
                                    </div>

                                    <!-- Start Sign In Time -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="start_sign_in" class="form-label">@lang('timeTable.startSignIn')</label>
                                        <input type="time" class="form-control" id="start_sign_in" name="start_sign_in"
                                            value="{{ old('start_sign_in', \Carbon\Carbon::parse($timetable->start_sign_in)->format('H:i')) }}"
                                            required>
                                        <!-- ... -->
                                    </div>

                                    <!-- End Sign In Time -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="end_sign_in" class="form-label">@lang('timeTable.endSignIn')</label>
                                        <input type="time" class="form-control" id="end_sign_in" name="end_sign_in"
                                            value="{{ old('end_sign_in', \Carbon\Carbon::parse($timetable->end_sign_in)->format('H:i')) }}"
                                            required>
                                        <!-- ... -->
                                    </div>

                                    <!-- Start Sign Out Time -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="start_sign_out" class="form-label">@lang('timeTable.startSignOut')</label>
                                        <input type="time" class="form-control" id="start_sign_out"
                                            name="start_sign_out"
                                            value="{{ old('start_sign_out', \Carbon\Carbon::parse($timetable->start_sign_out)->format('H:i')) }}"
                                            required>
                                        <!-- ... -->
                                    </div>

                                    <!-- End Sign Out Time -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="end_sign_out" class="form-label">@lang('timeTable.endSignOut')</label>
                                        <input type="time" class="form-control" id="end_sign_out" name="end_sign_out"
                                            value="{{ old('end_sign_out', \Carbon\Carbon::parse($timetable->end_sign_out)->format('H:i')) }}"
                                            required>
                                        <!-- ... -->
                                    </div>

                                    <!-- Lateness Grace Period -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="lateness_grace_period" class="form-label">@lang('timeTable.lateGrace')</label>
                                        <input type="number" class="form-control" id="lateness_grace_period"
                                            name="lateness_grace_period"
                                            value="{{ old('lateness_grace_period', $timetable->lateness_grace_period) }}"
                                            placeholder="@lang('timeTable.lateGrace')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterLatenessGracePeriod')
                                        </div>
                                    </div>

                                    <!-- Start Late Time Option -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="start_late_time_option" class="form-label">@lang('timeTable.startLateOption')</label>
                                        <select class="form-control" id="start_late_time_option"
                                            name="start_late_time_option" required>
                                            <option value="after_duty_time_grace_period"
                                                {{ old('start_late_time_option', $timetable->start_late_time_option) == 'after_duty_time_grace_period' ? 'selected' : '' }}>
                                                @lang('timeTable.afterDutyTimeGracePeriod')
                                            </option>
                                            <option value="after_duty_time"
                                                {{ old('start_late_time_option', $timetable->start_late_time_option) == 'after_duty_time' ? 'selected' : '' }}>
                                                @lang('timeTable.afterDutyTime')
                                            </option>
                                            <option value="from_duty_time"
                                                {{ old('start_late_time_option', $timetable->start_late_time_option) == 'from_duty_time' ? 'selected' : '' }}>
                                                @lang('timeTable.fromDutyTime')
                                            </option>
                                        </select>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.SelectStartLateOption')
                                        </div>
                                    </div>

                                    <!-- Cross Day -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <p class="mb-2">@lang('timeTable.crossDay')</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="cross_day"
                                                id="cross_day_yes" value="1"
                                                {{ old('cross_day', $timetable->cross_day) == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cross_day_yes">@lang('timeTable.yes')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="cross_day"
                                                id="cross_day_no" value="0"
                                                {{ old('cross_day', $timetable->cross_day) == '0' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cross_day_no">@lang('timeTable.no')</label>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-xl-12 text-center mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            @lang('timeTable.update')
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->
        </div>
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
@endsection
