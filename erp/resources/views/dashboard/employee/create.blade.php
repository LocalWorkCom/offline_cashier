@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .permission-item {
            display: block;
            /* Ensure items are visible by default */
        }

        .permission-item[style*="display: none"] {
            display: none !important;
            /* Force hide items with display: none */
        }

        .wizard-content-left {
            background-blend-mode: darken;
            background-color: rgba(0, 0, 0, 0.45);
            background-image: url("https://i.ibb.co/X292hJF/form-wizard-bg-2.jpg");
            background-position: center center;
            background-size: cover;
            height: 100vh;
            padding: 30px;
        }

        .wizard-content-left h1 {
            color: #ffffff;
            font-size: 38px;
            font-weight: 600;
            padding: 12px 20px;
            text-align: center;
        }

        .form-wizard {
            color: #888888;
            padding: 30px;
        }

        .form-wizard .wizard-form-radio {
            display: inline-block;
            margin-left: 5px;
            position: relative;
        }

        .form-wizard .wizard-form-radio input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            -ms-appearance: none;
            -o-appearance: none;
            appearance: none;
            background-color: #dddddd;
            height: 25px;
            width: 25px;
            display: inline-block;
            vertical-align: middle;
            border-radius: 50%;
            position: relative;
            cursor: pointer;
        }

        .form-wizard .wizard-form-radio input[type="radio"]:focus {
            outline: 0;
        }

        .form-wizard .wizard-form-radio input[type="radio"]:checked {
            background-color: #fb1647;
        }

        .form-wizard .wizard-form-radio input[type="radio"]:checked::before {
            content: "";
            position: absolute;
            width: 10px;
            height: 10px;
            display: inline-block;
            background-color: #ffffff;
            border-radius: 50%;
            left: 1px;
            right: 0;
            margin: 0 auto;
            top: 8px;
        }

        .form-wizard .wizard-form-radio input[type="radio"]:checked::after {
            content: "";
            display: inline-block;
            webkit-animation: click-radio-wave 0.65s;
            -moz-animation: click-radio-wave 0.65s;
            animation: click-radio-wave 0.65s;
            background: #000000;
            content: '';
            display: block;
            position: relative;
            z-index: 100;
            border-radius: 50%;
        }

        .form-wizard .wizard-form-radio input[type="radio"]~label {
            padding-left: 10px;
            cursor: pointer;
        }

        .form-wizard .form-wizard-header {
            text-align: center;
        }

        .form-wizard .form-wizard-next-btn,
        .form-wizard .form-wizard-previous-btn,
        .form-wizard .form-wizard-submit {
            background-color: #4776e6;
            color: #ffffff;
            display: inline-block;
            min-width: 100px;
            min-width: 120px;
            padding: 10px;
            text-align: center;
        }

        .form-wizard .form-wizard-next-btn:hover,
        .form-wizard .form-wizard-next-btn:focus,
        .form-wizard .form-wizard-previous-btn:hover,
        .form-wizard .form-wizard-previous-btn:focus,
        .form-wizard .form-wizard-submit:hover,
        .form-wizard .form-wizard-submit:focus {
            color: #ffffff;
            opacity: 0.6;
            text-decoration: none;
        }

        .form-wizard .wizard-fieldset {
            display: none;
        }

        .form-wizard .wizard-fieldset.show {
            display: block;
        }

        .form-wizard .wizard-form-error {
            display: none;
            background-color: #d70b0b;
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2px;
            width: 100%;
        }

        .form-wizard .form-wizard-previous-btn {
            background-color: #fb1647;
        }

        .form-wizard .form-control {
            font-weight: 300;
            height: auto !important;
            padding: 15px;
            color: #888888;
            background-color: #f1f1f1;
            border: none;
        }

        .form-wizard .form-control:focus {
            box-shadow: none;
        }

        .form-wizard .form-group {
            position: relative;
            margin: 25px 0;
        }

        .form-wizard .wizard-form-text-label {
            position: absolute;
            left: 10px;
            top: 16px;
            transition: 0.2s linear all;
        }

        .form-wizard .focus-input .wizard-form-text-label {
            color: #4776e6;
            top: -18px;
            transition: 0.2s linear all;
            font-size: 12px;
        }

        .form-wizard .form-wizard-steps {
            margin: 30px 0;
        }

        .form-wizard .form-wizard-steps li {
            width: 11%;
            float: left;
            position: relative;
        }

        .form-wizard .form-wizard-steps li::after {
            background-color: #f3f3f3;
            content: "";
            height: 5px;
            left: 0;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            border-bottom: 1px solid #dddddd;
            border-top: 1px solid #dddddd;
        }

        .form-wizard .form-wizard-steps li span {
            background-color: #dddddd;
            border-radius: 50%;
            display: inline-block;
            height: 40px;
            line-height: 40px;
            position: relative;
            text-align: center;
            width: 40px;
            z-index: 1;
        }

        .form-wizard .form-wizard-steps li:last-child::after {
            width: 50%;
        }

        .form-wizard .form-wizard-steps li.active span,
        .form-wizard .form-wizard-steps li.activated span {
            background-color: #4776e6;
            color: #ffffff;
        }

        .form-wizard .form-wizard-steps li.active::after,
        .form-wizard .form-wizard-steps li.activated::after {
            background-color: #4776e6;
            left: 50%;
            width: 50%;
            border-color: #4776e6;
        }

        .form-wizard .form-wizard-steps li.activated::after {
            width: 100%;
            border-color: #4776e6;
        }

        .form-wizard .form-wizard-steps li:last-child::after {
            left: 0;
        }

        .form-wizard .wizard-password-eye {
            position: absolute;
            right: 32px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        @keyframes click-radio-wave {
            0% {
                width: 25px;
                height: 25px;
                opacity: 0.35;
                position: relative;
            }

            100% {
                width: 60px;
                height: 60px;
                margin-left: -15px;
                margin-top: -15px;
                opacity: 0.0;
            }
        }

        @media screen and (max-width: 767px) {
            .wizard-content-left {
                height: auto;
            }
        }
    </style>
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('employee.addEmployee')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('employees.list') }}">@lang('employee.employees')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('employee.addEmployee')</li>
                </ol>
            </nav>
        </div>
    </div>
    <form method="POST" action="{{ route('employee.store') }}" class="needs-validation" enctype="multipart/form-data"
        novalidate>
        @csrf
        <!-- APP-CONTENT START -->
        <div class="main-content app-content ">
            <div class="container-fluid ">

                <!-- Start:: row-1 -->
                <div class="row">
                    <div id="mainContent" class="col-md-12">

                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    @lang('employee.addEmployee')
                                </div>
                            </div>
                            <div class="card-body">
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
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @endif

                                <section class="wizard-section">
                                    <div class="row no-gutters">
                                        <div class="col-lg-12 col-md-12">
                                            <div class="form-wizard">
                                                <div class="form-wizard-header">
                                                    <p> @lang('employee.employeeSteps')</p>
                                                    <ul class="list-unstyled form-wizard-steps clearfix">
                                                        <li class="active"><span>1</span></li>
                                                        <li><span>2</span></li>
                                                        <li><span>3</span></li>
                                                        <li><span>4</span></li>
                                                        <li><span>5</span></li>
                                                        <li><span>6</span></li>
                                                        <li><span>7</span></li>
                                                        <li><span>8</span></li>
                                                        <li><span>9</span></li>
                                                    </ul>
                                                </div>

                                                <fieldset class="wizard-fieldset show">
                                                    <h5>@lang('employee.BasicInfo')</h5>
                                                    {{-- ID &  --}}


                                                    <div class="row gy-4">
                                                        <div class="form-group col-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.code')*</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="employee_code" name="employee_code"
                                                                value="{{ old('employee_code') }}"
                                                                placeholder="@lang('employee.code')" required>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEmployeeCode')
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- name ar-en --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.firstNameAr')*</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="first_name" name="first_name"
                                                                value="{{ old('first_name') }}"
                                                                placeholder="@lang('employee.firstNameAr')" required>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterFirstNameAr')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.lastNameAr')*</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="last_name" name="last_name"
                                                                value="{{ old('last_name') }}"
                                                                placeholder="@lang('employee.lastNameAr')" required>
                                                            {{-- <div class="wizard-form-radio">
                                                            <input name="radio-name" id="radio2" type="radio">
                                                            <label for="radio2">Female</label>
                                                        </div> --}}
                                                            <div class="wizard-form-error"></div>

                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterLastNameAr')
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.firstNameEn')</label>
                                                            <input type="text" class="form-control" id="first_name"
                                                                name="first_name" value="{{ old('first_name') }}"
                                                                placeholder="@lang('employee.firstNameEn')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.lastNameEn')</label>
                                                            <input type="text" class="form-control" id="last_name"
                                                                name="last_name" value="{{ old('last_name') }}"
                                                                placeholder="@lang('employee.lastNameEn')">
                                                        </div>
                                                    </div>
                                                    {{-- gender & birthdate --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="gender"
                                                                class="form-label">@lang('employee.gender')*</label>
                                                            <select class="select2 form-control  wizard-required"
                                                                id="gender" name="gender">
                                                                <option value="" selected disabled>@lang('employee.selectGender')
                                                                </option>
                                                                <option value="male">@lang('employee.male')</option>
                                                                <option value="female">@lang('employee.female')</option>
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.selectGender')
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="birth_date"
                                                                class="form-label">@lang('employee.dob')*</label>
                                                            <input type="date" class="form-control " id="birth_date"
                                                                name="birth_date" value="{{ old('birth_date') }}"
                                                                placeholder="@lang('employee.dob')">
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterDateOfBirth')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    {{-- nationality & image --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.nationality')*</p>
                                                            <select name="nationality_id" class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectNationality')
                                                                </option>
                                                                @foreach ($nationalities as $nationality)
                                                                    <option value="{{ $nationality->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $nationality->name_ar : $nationality->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterNationality') </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.image')</p>
                                                            <input type="file" class="form-control wizard-required"
                                                                name="image" id="image">
                                                        </div>
                                                    </div>
                                                    {{-- marital status & blood group --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="marital_status"
                                                                class="form-label">@lang('employee.maritalStatus')*</label>
                                                            <select class="select2 form-control" id="marital_status"
                                                                name="marital_status">
                                                                <option value="" selected disabled>@lang('employee.selectMaritalStatus')
                                                                </option>
                                                                <option value="Married">@lang('employee.married')</option>
                                                                <option value="Single">@lang('employee.single')</option>
                                                                <option value="Divorced">@lang('employee.divorced')</option>
                                                                <option value="Widowed">@lang('employee.widowed')</option>
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.selectMaritalStatus')
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="blood_group"
                                                                class="form-label">@lang('employee.bloodGroup')</label>
                                                            <select class="select2 form-control" id="blood_group"
                                                                name="blood_group">
                                                                <option value="" selected disabled>@lang('employee.selectBloodGroup')
                                                                </option>
                                                                <option value="A+">A+</option>
                                                                <option value="A-">A-</option>
                                                                <option value="B+">B+</option>
                                                                <option value="B-">B-</option>
                                                                <option value="AB+">AB+</option>
                                                                <option value="AB-">AB-</option>
                                                                <option value="O+">O+</option>
                                                                <option value="O-">O-</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.selectBloodGroup')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    {{-- social links --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.facebook_link')</label>
                                                            <input type="text" class="form-control" id="facebook_link"
                                                                name="facebook_link" value="{{ old('facebook_link') }}"
                                                                placeholder="@lang('employee.facebook_link')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.instagram_link')</label>
                                                            <input type="text" class="form-control"
                                                                id="instagram_link" name="instagram_link"
                                                                value="{{ old('instagram_link') }}"
                                                                placeholder="@lang('employee.instagram_link')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.twitter_link')</label>
                                                            <input type="text" class="form-control" id="twitter_link"
                                                                name="twitter_link" value="{{ old('twitter_link') }}"
                                                                placeholder="@lang('employee.twitter_link')">
                                                        </div>
                                                    </div>
                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;" class="form-wizard-next-btn float-right">
                                                            @lang('employee.Next')</a>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="wizard-fieldset">
                                                    <h5> @lang('employee.Documents')</h5>
                                                    {{-- status --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.status')*</p>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="status" id="Radio-md" value="active" checked>
                                                                <label class="form-check-label" for="Radio-md">
                                                                    @lang('employee.active')
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="status" id="Radio-md" value="not active">
                                                                <label class="form-check-label" for="Radio-md">
                                                                    @lang('employee.notActive')
                                                                </label>
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.CurrentStatus')*</p>
                                                            <select name="employee_status_id"
                                                                class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectCurrentStatus')
                                                                </option>
                                                                @foreach ($employeeStatuses as $status)
                                                                    <option value="{{ $status->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $status->name_ar : $status->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterCurrentStatus') </div>
                                                        </div>
                                                    </div>
                                                    {{-- passport number & national id --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.national_id')*</label>
                                                            <input type="text" class="form-control" id="national_id"
                                                                name="national_id" value="{{ old('national_id') }}"
                                                                placeholder="@lang('employee.national_id')">
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterValidPassportNumber')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.passport')</label>
                                                            <input type="text" class="form-control"
                                                                id="passport_number" name="passport_number"
                                                                value="{{ old('passport_number') }}"
                                                                placeholder="@lang('employee.passport')">
                                                        </div>
                                                    </div>
                                                    {{-- passport copy & expiry date --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="passport_expiry_date"
                                                                class="form-label">@lang('employee.PassportExpiryDate')</label>
                                                            <input type="date" class="form-control "
                                                                id="passport_expiry_date" name="passport_expiry_date"
                                                                value="{{ old('passport_expiry_date') }}"
                                                                placeholder="@lang('employee.PassportExpiryDate')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.PassportCopy')</p>
                                                            <input type="file" class="form-control"
                                                                name="passport_copy" id="passport_copy">
                                                        </div>
                                                    </div>
                                                    {{-- work permit copy & expiry date --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="work_permit_expiry_date"
                                                                class="form-label">@lang('employee.WorkPermitExpiryDate')</label>
                                                            <input type="date" class="form-control "
                                                                id="work_permit_expiry_date"
                                                                name="work_permit_expiry_date"
                                                                value="{{ old('work_permit_expiry_date') }}"
                                                                placeholder="@lang('employee.WorkPermitExpiryDate')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.WorkPermitCopy')</p>
                                                            <input type="file" class="form-control" name="work_permit"
                                                                id="work_permit">
                                                        </div>
                                                    </div>
                                                    {{-- residency expiry date & military service --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="residency_expiry_date"
                                                                class="form-label">@lang('employee.ResidencyExpiryDate')</label>
                                                            <input type="date" class="form-control "
                                                                id="residency_expiry_date" name="residency_expiry_date"
                                                                value="{{ old('residency_expiry_date') }}"
                                                                placeholder="@lang('employee.ResidencyExpiryDate')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.military_status')</p>
                                                            <select name="military_status_id"
                                                                class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectMilitaryStatus')
                                                                </option>
                                                                @foreach ($militarystatuses as $status)
                                                                    <option value="{{ $status->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $status->name_ar : $status->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterMilitaryStatus') </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;"
                                                            class="form-wizard-previous-btn float-left">
                                                            @lang('employee.Previous')</a>
                                                        <a href="javascript:;" class="form-wizard-next-btn float-right">
                                                            @lang('employee.Next')</a>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="wizard-fieldset ">
                                                    <h5> @lang('employee.ContactInfo')</h5>
                                                    {{-- phone $ countrycode --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.countryCode')*</p>
                                                            <select id="country_code" name="country_code"
                                                                class="select2 form-control wizard-required" required>
                                                                <option value="" disabled selected>
                                                                    @lang('employee.chooseCountryCode')
                                                                </option>
                                                                @foreach ($countries as $country)
                                                                    <option value="{{ $country->phone_code }}"
                                                                        {{ old('country_code') == $country->phone_code ? 'selected' : '' }}>
                                                                        {{ $country->phone_code }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">@lang('validation.EnterCountryCode')</div>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12 d-none"
                                                            id="city_div">
                                                            <p class="mb-2 text-muted">@lang('employee.city')*</p>
                                                            <select name="city_id" id="city_id"
                                                                class="select2 form-control wizard-required" required>
                                                                <option value="" disabled selected>
                                                                    @lang('employee.chooseCity')
                                                                </option>
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">@lang('validation.EnterCity')</div>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12 d-none">
                                                            <p class="mb-2 text-muted">@lang('employee.area')*</p>
                                                            <select name="area_id" id="area_id"
                                                                class="select2 form-control wizard-required" required>
                                                                <option value="" disabled selected>
                                                                    @lang('employee.choosearea')
                                                                </option>
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">@lang('validation.Enterarea')</div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.phone')*</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="phone" name="phone"
                                                                value="{{ old('phone') }}"
                                                                placeholder="@lang('employee.phone')" required>
                                                            <div class="wizard-form-error"></div>

                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterValidPhone')
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- whatsapp & email --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.whatsapp_number')</label>
                                                            <input type="text" class="form-control"
                                                                id="whatsapp_number" name="whatsapp_number"
                                                                value="{{ old('whatsapp_number') }}"
                                                                placeholder="@lang('employee.whatsapp_number')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.email')*</label>
                                                            <input type="email" class="form-control wizard-required"
                                                                id="email" name="email"
                                                                value="{{ old('email') }}"
                                                                placeholder="@lang('employee.email')" required>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterValidEmail')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    {{-- emergency contact 1 & 2 --}}
                                                    <div class="row gy-4">
                                                        {{-- emergency contact 1 --}}
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="emergency_contact_one_name"
                                                                class="form-label">@lang('employee.emergencyNameOne')</label>
                                                            <input type="text" class="form-control"
                                                                id="emergency_contact_one_name"
                                                                name="emergency_contact_one_name"
                                                                value="{{ old('emergency_contact_one_name') }}"
                                                                placeholder="@lang('employee.emergencyNameOne')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="emergency_contact_one_relation"
                                                                class="form-label">@lang('employee.emergencyRelOne')</label>
                                                            <input type="text" class="form-control"
                                                                id="emergency_contact_one_relation"
                                                                name="emergency_contact_one_relation"
                                                                value="{{ old('emergency_contact_one_relation') }}"
                                                                placeholder="@lang('employee.emergencyRelOne')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="emergency_contact_one_phone"
                                                                class="form-label">@lang('employee.emergencyPhoneOne')</label>
                                                            <input type="text" class="form-control"
                                                                id="emergency_contact_one_phone"
                                                                name="emergency_contact_one_phone"
                                                                value="{{ old('emergency_contact_one_phone') }}"
                                                                placeholder="@lang('employee.emergencyPhoneOne')">
                                                        </div>
                                                        {{-- emergency contact 2 --}}
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="emergency_contact_two_name"
                                                                class="form-label">@lang('employee.emergencyNameTwo')</label>
                                                            <input type="text" class="form-control"
                                                                id="emergency_contact_two_name"
                                                                name="emergency_contact_two_name"
                                                                value="{{ old('emergency_contact_two_name') }}"
                                                                placeholder="@lang('employee.emergencyNameTwo')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="emergency_contact_two_relation"
                                                                class="form-label">@lang('employee.emergencyRelTwo')</label>
                                                            <input type="text" class="form-control"
                                                                id="emergency_contact_two_relation"
                                                                name="emergency_contact_two_relation"
                                                                value="{{ old('emergency_contact_two_relation') }}"
                                                                placeholder="@lang('employee.emergencyRelTwo')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="emergency_contact_two_phone"
                                                                class="form-label">@lang('employee.emergencyPhoneTwo')</label>
                                                            <input type="text" class="form-control"
                                                                id="emergency_contact_two_phone"
                                                                name="emergency_contact_two_phone"
                                                                value="{{ old('emergency_contact_two_phone') }}"
                                                                placeholder="@lang('employee.emergencyPhoneTwo')">
                                                        </div>
                                                    </div>
                                                    {{-- curent & home address --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="current_address"
                                                                class="form-label">@lang('employee.current_address')*</label>
                                                            <textarea class="form-control" id="current_address" name="current_address" rows="4">{{ old('current_address') }}</textarea>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterCurrentAddress')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="home_country_address"
                                                                class="form-label">@lang('employee.home_country_address')</label>
                                                            <textarea class="form-control" id="home_country_address" name="home_country_address" rows="4">{{ old('home_country_address') }}</textarea>
                                                        </div>

                                                    </div>
                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;"
                                                            class="form-wizard-previous-btn float-left">
                                                            @lang('employee.Previous')</a>
                                                        <a href="javascript:;" class="form-wizard-next-btn float-right">
                                                            @lang('employee.Next')</a>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="wizard-fieldset">
                                                    <h5> @lang('employee.Assignment details')</h5>
                                                    {{-- department & job title(position) & job desc & vichele & cuisine --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.department')*</p>
                                                            <select name="department_id" class="select form-control"
                                                                onchange="fetchPositions(this.value)">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectDepartment')
                                                                </option>
                                                                @foreach ($departments as $department)
                                                                    <option value="{{ $department->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $department->name_ar : $department->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterDEpartment') </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.position')*</p>
                                                            <select name="position_id" id="position"
                                                                class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectPosition')
                                                                </option>
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">@lang('validation.EnterPosition')</div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.jobdesc')*</p>
                                                            <select name="flag" id="flag"
                                                                class="select2 form-control"
                                                                onchange="toggleVehicleSelect();toggleCuisineSelect();toggleBranchSelect();">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.jobdesc')
                                                                </option>
                                                                <option value="waiter">@lang('employee.waiter')</option>
                                                                <option value="chef">@lang('employee.chef')</option>
                                                                <option value="Head Chef">@lang('employee.Head Chef')</option>

                                                                <option value="cashier">@lang('employee.cashier')</option>
                                                                <option value="customer_service">@lang('employee.customer_service')
                                                                </option>
                                                                {{-- <option value="customer_service">@lang('employee.customer_service')
                                                                </option> --}}
                                                                <option value="driver">@lang('employee.driver')</option>
                                                                <option value="kitchen staff">@lang('employee.kitchen_staff')</option>
                                                                <option value="supervisor">@lang('employee.supervisor')</option>
                                                                @if (auth('admin')->user() &&
                                                                        (auth('admin')->user()->hasRole('superAdmin') || auth('admin')->user()->hasRole('LocalWork Admin')))
                                                                    <option value="officer">@lang('employee.officer')</option>
                                                                    <option value="Head Board">@lang('employee.Head Board')</option>
                                                                    {{-- <option value="hr">@lang('employee.hr_employee')
                                                                    </option>
                                                                    <option value="hr">@lang('employee.hr_manager')
                                                                    </option>
                                                                    <option value="finance">@lang('employee.finance_manager')
                                                                    </option><option value="finance">@lang('employee.finance_employee')
                                                                    </option> --}}
                                                                    <option value="kitchen manager">@lang('employee.kitchen_manager')
                                                                    </option>
                                                                    <option value="branch manager">@lang('employee.branch_manager')
                                                                    </option>
                                                                @endif
                                                                <option value="employee">@lang('employee.employee')</option>
                                                            </select>
                                                            <div class="invalid-feedback">@lang('validation.flag')</div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <div id="vehicleSelect"
                                                                style="display: none; margin-top: 15px;">
                                                                <label for="vehicle_type">@lang('employee.vehicle_type')</label>
                                                                <select name="vehicle_type" id="vehicle_type"
                                                                    class="select2 form-control">
                                                                    <option value="" selected disabled>
                                                                        @lang('employee.choose_vehicle_type')</option>
                                                                    @foreach (vehicles() as $vehicle)
                                                                        <option value="{{ $vehicle->id }}">
                                                                            {{ $vehicle->vehicle_type }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <input type="text" class="form-control"
                                                                    id="vehicle_number" name="vehicle_number"
                                                                    value="{{ old('vehicle_number') }}"
                                                                    placeholder="@lang('employee.vehicle_number')">
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <div id="cusinesSelect"
                                                                style="display: none; margin-top: 15px;">
                                                                <label for="cuisine_category">@lang('employee.cuisine_type')</label>
                                                                <!-- ✅ REMOVE the 'name' attribute so the category select doesn't send its own array -->
                                                                <select id="cuisine_category" class="form-control select2"
                                                                    multiple>
                                                                    <option disabled>@lang('Choose a category')</option>
                                                                    @foreach ($cuisineCategories as $category)
                                                                        <option value="{{ $category->id }}">
                                                                            {{ $category->dish_category->name . '-' . $category->cuisine->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>

                                                                <!-- ✅ Dynamic dish selects will be injected here -->
                                                                <div id="dish-selects-container"></div>
                                                            </div>




                                                            {{-- <div id="cusinesSelect"
                                                                style="display: none; margin-top: 15px;">
                                                                <label for="cuisine_type">@lang('employee.cuisine_type')</label>
                                                                <select name="cuisine_categories[]" id="cuisine_type"
                                                                    class="select2 form-control" multiple>
                                                                    <option value="" selected disabled>
                                                                        @lang('employee.choose_cuisine_type')</option>
                                                                    @foreach (cuisines() as $cuisine)
                                                                        <option value="{{ $cuisine->id }}">
                                                                            {{ $cuisine->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div> --}}
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="employment_type"
                                                                class="form-label">@lang('employee.employmentType')</label>
                                                            <select class="select2 form-control" id="employment_type"
                                                                name="employment_type">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectEmploymentType')</option>
                                                                <option value="Part-Time">@lang('employee.part-time')</option>
                                                                <option value="Full-Time">@lang('employee.full-time')</option>
                                                            </select>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.selectEmploymentType')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    {{-- branch & supervisor  --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.branch')*</p>
                                                            <div id="branch-container">
                                                                <select name="branch_id" id="branch"
                                                                    class="form-control" disabled>
                                                                    <option value="" selected disabled>
                                                                        @lang('employee.selectBranch')</option>
                                                                    @foreach ($branches as $branch)
                                                                        <option value="{{ $branch->id }}">
                                                                            {{ $branch->name }}</option>
                                                                    @endforeach
                                                                </select>

                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">@lang('validation.EnterNationality')</div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.supervisor')</p>
                                                            <select name="supervisor_id" class="select2 form-control">
                                                                <option value="" disabled selected>
                                                                    @lang('employee.selectSupervisor')
                                                                </option>
                                                                @foreach ($supervisors as $supervisor)
                                                                    <option value="{{ $supervisor->id }}">
                                                                        {{ $supervisor->first_name . ' ' . $supervisor->last_name . ' | ' . $supervisor->employee_code }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">@lang('validation.EnterDepartment')</div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    {{-- hire date & salary --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="hire_date"
                                                                class="form-label">@lang('employee.hireDate')*</label>
                                                            <input type="date" class="form-control" id="hire_date"
                                                                name="hire_date" value="{{ old('hire_date') }}"
                                                                placeholder="@lang('employee.hireDate')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="salary"
                                                                class="form-label">@lang('employee.salary')*</label>
                                                            <input type="text" class="form-control" id="salary"
                                                                name="salary" value="{{ old('salary') }}"
                                                                placeholder="@lang('employee.salary')">
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterSalary')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    {{-- assurance salary & number --}}
                                                    <div class="row gy-4">
                                                        <div class="col-lg-6 col-md-6 col-sm-6">
                                                            <div class="form-group">
                                                                <label for="assurance_salary"
                                                                    class="form-label">@lang('employee.assuranceSalary')</label>
                                                                <input type="text" class="form-control"
                                                                    id="assurance_salary" name="assurance_salary"
                                                                    value="{{ old('assurance_salary') }}"
                                                                    placeholder="@lang('employee.assuranceSalary')">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-6">
                                                            <div class="form-group">
                                                                <label for="assurance_number"
                                                                    class="form-label">@lang('employee.assuranceNumber')</label>
                                                                <input type="text" class="form-control"
                                                                    id="assurance_number" name="assurance_number"
                                                                    value="{{ old('assurance_number') }}"
                                                                    placeholder="@lang('employee.assuranceNumber')">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- payment type & frequency --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.PaymentType')*</p>
                                                            <select id="payment_type_id" name="payment_type_id"
                                                                class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectPaymentType')
                                                                </option>
                                                                @foreach ($payment_types as $type)
                                                                    <option value="{{ $type->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $type->name_ar : $type->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterPaymentType') </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.PaymentFrequency')*</p>
                                                            <select id="payment_frequency_id" name="payment_frequency_id"
                                                                class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectPaymentFrequency')
                                                                </option>
                                                                @foreach ($payment_frequencies as $frequency)
                                                                    <option value="{{ $frequency->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $frequency->name_ar : $frequency->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterPaymentFrequency') </div>
                                                        </div>
                                                    </div>
                                                    {{-- shift & work hours --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.schedule')*</p>
                                                            <select id="shift_id" name="shift_id"
                                                                class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectSchedule')
                                                                </option>
                                                                @foreach ($shifts as $shift)
                                                                    <option value="{{ $shift->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $shift->name_ar : $shift->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterSchedule') </div>
                                                        </div>
                                                        <div id="shiftDetailsContainer" class="col-12 mt-3"
                                                            style="display: none;"
                                                            data-direction="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h6>@lang('employee.ShiftDetails')</h6>
                                                                    <div id="shiftDetailsContent">
                                                                        <!-- Details will be loaded here -->
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="schedule_start_date"
                                                                class="form-label">@lang('employee.schedule_start_date')*</label>
                                                            <input type="date" class="form-control "
                                                                id="schedule_start_date" name="schedule_start_date"
                                                                value="{{ old('schedule_start_date') }}"
                                                                placeholder="@lang('employee.schedule_start_date')">
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterScheduleStartDate')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="schedule_end_date"
                                                                class="form-label">@lang('employee.schedule_end_date')*</label>
                                                            <input type="date" class="form-control "
                                                                id="schedule_end_date" name="schedule_end_date"
                                                                value="{{ old('schedule_end_date') }}"
                                                                placeholder="@lang('employee.schedule_end_date')">
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterScheduleEndDate')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    {{-- experience --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.PreviousPosition')</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="previous_position" name="previous_position"
                                                                value="{{ old('previous_position') }}"
                                                                placeholder="@lang('employee.PreviousPosition')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.PreviousSalary')</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="previous_salary" name="previous_salary"
                                                                value="{{ old('previous_salary') }}"
                                                                placeholder="@lang('employee.PreviousSalary')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.ExpectedSalary')</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="expected_salary" name="expected_salary"
                                                                value="{{ old('expected_salary') }}"
                                                                placeholder="@lang('employee.ExpectedSalary')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.YearsOfExperience')*</label>
                                                            <input type="number" class="form-control wizard-required"
                                                                id="num_experience_years" name="num_experience_years"
                                                                value="{{ old('num_experience_years') }}"
                                                                placeholder="@lang('employee.YearsOfExperience')" required>
                                                            <div class="wizard-form-error"></div>

                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterYearsOfExperience')
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.resume')*</p>
                                                            <input type="file" class="form-control wizard-required"
                                                                name="resume" id="resume" required>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.resume') </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;"
                                                            class="form-wizard-previous-btn float-left">
                                                            @lang('employee.Previous')</a>
                                                        <a href="javascript:;" class="form-wizard-next-btn float-right">
                                                            @lang('employee.Next')</a>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="wizard-fieldset">
                                                    <h5> @lang('employee.Education')</h5>
                                                    {{-- university & education level & field of study --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.University')*</p>
                                                            <select id="university_id" name="university_id"
                                                                class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectUniversity')
                                                                </option>
                                                                @foreach ($universities as $university)
                                                                    <option value="{{ $university->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $university->name_ar : $university->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterUniversity') </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.EducationLevel')*</p>
                                                            <select name="education_level_id"
                                                                class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectEducationLevel')
                                                                </option>
                                                                @foreach ($levels as $level)
                                                                    <option value="{{ $level->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $level->name_ar : $level->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEducationLevel') </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.FieldOfStudy')*</p>
                                                            <select name="filed_of_study_id" class="select2 form-control">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectFieldOfStudy')
                                                                </option>
                                                                @foreach ($fields as $field)
                                                                    <option value="{{ $field->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $field->name_ar : $field->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterFieldOfStudy') </div>
                                                        </div>
                                                    </div>
                                                    {{-- graduation year & degree certificate & additional certifications & certification documents --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="graduation_year"
                                                                class="form-label">@lang('employee.graduation_year')</label>
                                                            <input type="number" class="form-control"
                                                                id="graduation_year" name="graduation_year"
                                                                value="{{ old('graduation_year') }}" min="1900"
                                                                max="{{ date('Y') + 10 }}" step="1"
                                                                placeholder="@lang('employee.graduation_year')">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.DegreeCertificate')</p>
                                                            <input type="file" class="form-control"
                                                                name="degree_certificate" id="degree_certificate">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.CertificateDocument')</p>
                                                            <input type="file" class="form-control"
                                                                name="certification_documents"
                                                                id="certification_documents">
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.AdditionalCertificate')</p>
                                                            <input type="file" class="form-control"
                                                                name="additional_certifications"
                                                                id="additional_certifications">
                                                        </div>
                                                    </div>
                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;"
                                                            class="form-wizard-previous-btn float-left">
                                                            @lang('employee.Previous')</a>
                                                        <a href="javascript:;" class="form-wizard-next-btn float-right">
                                                            @lang('employee.Next')</a>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="wizard-fieldset ">
                                                    <h5> @lang('employee.BankingInfo')</h5>
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label>@lang('employee.selectBanks')</label>
                                                            <select name="banks[]" id="bankSelect"
                                                                class="form-control select2" multiple>
                                                                @foreach ($banks as $bank)
                                                                    <option value="{{ $bank->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? $bank->name_ar : $bank->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div id="bankDetailsContainer"></div>

                                                    </div>

                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;"
                                                            class="form-wizard-previous-btn float-left">
                                                            @lang('employee.Previous')</a>
                                                        <a href="javascript:;" class="form-wizard-next-btn float-right">
                                                            @lang('employee.Next')</a>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="wizard-fieldset">
                                                    <h5> @lang('employee.Additional_Information')</h5>
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="bank_account"
                                                                class="form-label">@lang('employee.How did you hear about us?')</label>
                                                            <input type="text" class="form-control" id="hear_about_us"
                                                                placeholder="@lang('employee.Friend, Ad, Employee, etc.')">

                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.hear_about_us')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="Visa_Information"
                                                                class="form-label">@lang('employee.Visa_Information')</label>
                                                            <input type="text" class="form-control"
                                                                id="Visa_Information" name="Visa_Information"
                                                                value="{{ old('Visa_Information') }}"
                                                                placeholder="@lang('employee.Visa_Information')">
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.Visa_Information')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.Is_the_residency_transferable')</p>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="residency_transferable" id="Radio-md"
                                                                    value="1" checked>
                                                                <label class="form-check-label" for="Radio-md">
                                                                    @lang('employee.yes')
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="residency_transferable" id="Radio-md"
                                                                    value="0">
                                                                <label class="form-check-label" for="Radio-md">
                                                                    @lang('employee.no')
                                                                </label>
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">

                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="Languages_Spoken"
                                                                class="form-label">@lang('employee.Languages_Spoken')</label>
                                                            <textarea class="form-control" id="Languages_Spoken" name="Languages_Spoken" rows="4">{{ old('Languages_Spoken') }}</textarea>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.Languages_Spoken')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="Tasks_instructions"
                                                                class="form-label">@lang('employee.Tasks_instructions')</label>
                                                            <textarea class="form-control" id="Tasks_instructions" name="Tasks_instructions" rows="4">{{ old('Tasks_instructions') }}</textarea>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.Tasks_instructions')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>

                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="Criminal_Record"
                                                                class="form-label">@lang('employee.Criminal_Record')</label>
                                                            <input type="file" name="Criminal_Record"
                                                                id="Criminal_Record">
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.Criminal_Record')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="Drug_Test_Report"
                                                                class="form-label">@lang('employee.Drug_Test_Report')</label>
                                                            <input type="file" name="Drug_Test_Report"
                                                                id="Drug_Test_Report">
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.Drug_Test_Report')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;"
                                                            class="form-wizard-previous-btn float-left">
                                                            @lang('employee.Previous')</a>
                                                        <a href="javascript:;" class="form-wizard-next-btn float-right">
                                                            @lang('employee.Next')</a>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="wizard-fieldset">
                                                    <h5> @lang('employee.Driving_License_Information')</h5>
                                                    {{-- Driving License --}}
                                                    <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <p class="mb-2 text-muted">@lang('employee.Does the employee have a driving license')</p>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="driving_license" value="1"
                                                                id="driving_license_yes" checked>
                                                            <label class="form-check-label"
                                                                for="driving_license_yes">@lang('employee.yes')</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="driving_license" value="0"
                                                                id="driving_license_no">
                                                            <label class="form-check-label"
                                                                for="driving_license_no">@lang('employee.no')</label>
                                                        </div>
                                                    </div>

                                                    <div id="license_fields" style="display: none;">
                                                        <div class="form-group">
                                                            <label for="license_country">@lang('employee.License Country')</label>
                                                            <input type="text" name="license_country"
                                                                class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="license_expiry">@lang('employee.License Expiry Date')</label>
                                                            <input type="date" name="license_expiry"
                                                                class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="license_copy">@lang('employee.Upload License Copy')</label>
                                                            <input type="file" name="license_copy"
                                                                class="form-control-file">
                                                        </div>
                                                    </div>

                                                    {{-- Kuwaiti License --}}
                                                    <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <p class="mb-2 text-muted">@lang('employee.Does the employee have a Kuwaiti License')</p>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="kuwait_license" value="1"
                                                                id="kuwait_license_yes">
                                                            <label class="form-check-label"
                                                                for="kuwait_license_yes">@lang('employee.yes')</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="kuwait_license" value="0"
                                                                id="kuwait_license_no" checked>
                                                            <label class="form-check-label"
                                                                for="kuwait_license_no">@lang('employee.no')</label>
                                                        </div>
                                                    </div>

                                                    <div id="kuwait_license_fields" style="display: none;">
                                                        <div class="form-group">
                                                            <label for="kuwait_license_expiry">@lang('employee.Kuwaiti License Expiry Date')</label>
                                                            <input type="date" name="kuwait_license_expiry"
                                                                class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="kuwait_license_copy">@lang('employee.Upload Kuwaiti License Copy')</label>
                                                            <input type="file" name="kuwait_license_copy"
                                                                class="form-control-file">
                                                        </div>
                                                    </div>

                                                    {{-- Egyptian License --}}
                                                    <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <p class="mb-2 text-muted">@lang('employee.Does the employee have an Egyptian License')</p>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="egypt_license" value="1"
                                                                id="egypt_license_yes">
                                                            <label class="form-check-label"
                                                                for="egypt_license_yes">@lang('employee.yes')</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="egypt_license" value="0" id="egypt_license_no"
                                                                checked>
                                                            <label class="form-check-label"
                                                                for="egypt_license_no">@lang('employee.no')</label>
                                                        </div>
                                                    </div>

                                                    <div id="egypt_license_fields" style="display: none;">
                                                        <div class="form-group">
                                                            <label for="egypt_license_expiry">@lang('employee.Egyptian License Expiry Date')</label>
                                                            <input type="date" name="egypt_license_expiry"
                                                                class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="egypt_license_copy">@lang('employee.Upload Egyptian License Copy')</label>
                                                            <input type="file" name="egypt_license_copy"
                                                                class="form-control-file">
                                                        </div>
                                                    </div>

                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;"
                                                            class="form-wizard-previous-btn float-left">
                                                            @lang('employee.Previous')</a>
                                                        <a href="javascript:;" class="form-wizard-next-btn float-right">
                                                            @lang('employee.Next')</a>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="wizard-fieldset">
                                                    <h5> @lang('employee.others')</h5>
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.isBiometric')</p>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="is_biometric" id="Radio-md" value="1"
                                                                    checked>
                                                                <label class="form-check-label" for="Radio-md">
                                                                    @lang('employee.yes')
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="is_biometric" id="Radio-md" value="0">
                                                                <label class="form-check-label" for="Radio-md">
                                                                    @lang('employee.no')
                                                                </label>
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="biometric_id"
                                                                class="form-label">@lang('employee.biometricId')</label>
                                                            <input type="text" class="form-control" id="biometric_id"
                                                                name="biometric_id" value="{{ old('biometric_id') }}"
                                                                placeholder="@lang('employee.biometricId')">
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterBiometricId')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="notes"
                                                                class="form-label">@lang('employee.notes')</label>
                                                            <textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterNotes')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;"
                                                            class="form-wizard-previous-btn float-left">
                                                            @lang('employee.Previous')</a>
                                                        <button type="submit" class="form-wizard-submit float-right">
                                                            @lang('employee.Submit')</button>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>

                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                    <div id="extraContent" class="col-md-3 d-none">
                        <div class="team-groups">
                            <div class="card custom-card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h6 class="fw-semibold mb-0">All Teams</h6>
                                    <div>
                                        <input type="text" id="searchPermissions" class="form-control"
                                            placeholder="Search permissions..." onkeyup="filterPermissions()">
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="teams-nav" id="teams-nav">
                                        <ul class="list-unstyled mb-0 mt-2">
                                            @foreach ($groupedPermissions as $group => $groupPermissions)
                                                <div class="col-md-6 mb-4">
                                                    <div class="permission-group">
                                                        <div class="d-flex flex-wrap">
                                                            @foreach ($groupPermissions as $permission)
                                                                <div class="permission-item">
                                                                    <div class="form-check me-3">
                                                                        <!-- Hidden input for unchecked state -->
                                                                        <input type="hidden"
                                                                            name="permissions_ids[{{ $permission->id }}]"
                                                                            value="0">
                                                                        <!-- Checkbox -->
                                                                        <input
                                                                            class="form-check-input form-checked-outline form-checked-success"
                                                                            type="checkbox"
                                                                            name="permissions_ids[{{ $permission->id }}]"
                                                                            id="permission_{{ $loop->parent->index }}_{{ $loop->index }}"
                                                                            value="1">
                                                                        <label class="form-check-label permission-label"
                                                                            for="permission_{{ $loop->parent->index }}_{{ $loop->index }}">
                                                                            {{ __("permissions.{$permission->name}") }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->
        </div>
    </form>

    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- INETRNAL SELECT2 JS -->
    @vite('resources/assets/js/select2.js')
    @vite('resources/assets/js/team.js')

    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    <script>
        function toggleVehicleSelect() {
            var flag = document.getElementById("flag").value;
            var vehicleSelect = document.getElementById("vehicleSelect");

            if (flag === "driver") {
                vehicleSelect.style.display = "block";
            } else {
                vehicleSelect.style.display = "none";
            }
        }

        function toggleCuisineSelect() {
            var flag = document.getElementById("flag").value;
            var cuisineSelect = document.getElementById("cusinesSelect");

            if (flag === "Head Chef") {
                cuisineSelect.style.display = "block";
            } else {
                cuisineSelect.style.display = "none";
            }
        }

        function toggleBranchSelect() {
            var flag = $('#flag').val();
            var branchEnabledFlags = [
                'waiter', 'chef', 'cashier', 'driver', 'kitchen staff', 'supervisor', 'branch manager', 'Head Chef',
                'customer_service'
            ];

            // Enable or disable branch selection
            if (branchEnabledFlags.includes(flag)) {
                $('#branch').prop('disabled', false);
            } else {
                $('#branch').prop('disabled', true).val('');
            }

            // Handle "Head Board" case (adjust column layout)
            var mainContent = $('#mainContent');
            var extraContent = $('#extraContent');

            if (flag === "Head Board") {
                mainContent.removeClass("col-md-12").addClass("col-md-9");
                extraContent.removeClass("d-none");
            } else {
                mainContent.removeClass("col-md-9").addClass("col-md-12");
                extraContent.addClass("d-none");
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            $('select[name="country_code"]').on('change', function() {
                var countryCode = $(this).val();
                console.log(countryCode);

                if (countryCode) {
                    $.ajax({
                        url: '/dashboard/city/show_all/' +
                            countryCode, // Adjust this to your correct route
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#city_div').removeClass('d-none');
                            $('#city_id').empty().append(
                                '<option disabled selected>@lang('employee.chooseCity')</option>');
                            $('#area_id').empty().append(
                                '<option disabled selected>@lang('employee.chooseregion')</option>');
                            $('select[name="city_id"]').parent().removeClass('d-none');

                            $.each(data, function(key, city) {
                                $('#city_id').append('<option value="' + city.id +
                                    '">' + city
                                    .name + '</option>');
                            });
                        }
                    });
                }
            });

            $('select[name="city_id"]').on('change', function() {
                var cityId = $(this).val();

                if (cityId) {
                    $.ajax({
                        url: '/dashboard/region/show_all/' +
                            cityId, // Adjust this to your correct route
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#region_div').removeClass('d-none');
                            $('#area_id').empty().append(
                                '<option disabled selected>@lang('employee.chooseRegion')</option>');

                            $('select[name="area_id"]').parent().removeClass('d-none');

                            $.each(data, function(key, region) {
                                $('#area_id').append('<option value="' + region.id +
                                    '">' + region
                                    .name + '</option>');
                            });
                        }
                    });
                }
            });

            function filterPermissions() {
                var input = document.getElementById("searchPermissions");
                var filter = input.value.toLowerCase();
                var items = document.querySelectorAll(".permission-item");

                items.forEach(function(item) {
                    var label = item.querySelector(".permission-label").textContent.toLowerCase();
                    if (label.includes(filter)) {
                        item.style.display = "block";
                    } else {
                        item.style.display = "none";
                    }
                });
            }

            document.querySelector('form').addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Collect IDs of checked permissions
                const permissions = [];
                document.querySelectorAll('input[name^="permissions_ids"]:checked').forEach(checkbox => {
                    permissions.push(checkbox.name.match(/\d+/)[
                        0]); // Extract the permission ID from the name attribute
                });

                // Add permissions to the form data
                const formData = new FormData(this);
                formData.set('permissions_ids', permissions.join(
                    ',')); // Store only the selected permission IDs

                // Submit the form using fetch
                fetch(this.action, {
                        method: this.method,
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                    })
                    .catch(error => {
                        console.error(error);
                    });
            });

            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });



            $('#branch').on('change', function() {
                var branchId = $(this).val();
                if (branchId) {
                    $.ajax({
                        url: '{{ route('fetch.supervisors') }}',
                        type: 'GET',
                        data: {
                            branch_id: branchId
                        },
                        success: function(data) {
                            var supervisorSelect = $('select[name="supervisor_id"]');
                            supervisorSelect.empty().append(
                                '<option value="" disabled selected>@lang('employee.selectSupervisor')</option>'
                            );
                            $.each(data, function(index, supervisor) {
                                supervisorSelect.append('<option value="' + supervisor
                                    .id + '">' +
                                    supervisor.first_name + ' ' + supervisor
                                    .last_name + ' | ' +
                                    supervisor.employee_code + '</option>');
                            });
                        },
                        error: function() {
                            console.error('Failed to fetch supervisors.');
                        }
                    });
                }
            });

            $('.select2').select2(); // initialize Select2

            $('#bankSelect').on('change', function() {
                let selectedBankIds = $(this).val();
                let banks = @json($banks);
                let container = $('#bankDetailsContainer');
                container.empty();

                selectedBankIds.forEach(function(id, index) {
                    let bank = banks.find(b => b.id == id);
                    let locale = '{{ app()->getLocale() }}';
                    console.log(bank);

                    let bankName = bank.name;

                    let html = `
                    <div class="border p-3 mb-3 rounded">
                        <h6>${bankName}</h6>
                        <input type="hidden" name="banks[${index}][bank_name_id]" value="${bank.id}" />

                        <div class="form-group">
                            <label>@lang('employee.bankAccount')</label>
                            <input type="text" name="banks[${index}][bank_account_number]" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>@lang('employee.bankIban')</label>
                            <input type="text" name="banks[${index}][bank_iban]" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>@lang('employee.IsPayroll')</label><br>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="banks[${index}][is_payroll_account]" value="1">
                                <label class="form-check-label">@lang('employee.yes')</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="banks[${index}][is_payroll_account]" value="0" checked>
                                <label class="form-check-label">@lang('employee.no')</label>
                            </div>
                        </div>
                    </div>
                `;

                    container.append(html);
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const branchSelect = document.getElementById('branch');
            const supervisorSelect = document.querySelector('select[name="supervisor_id"]');

            // Function to fetch supervisors by branch
            function fetchSupervisors(branchId) {
                if (branchId) {
                    fetch(`/fetch-supervisors?branch_id=${branchId}`)
                        .then(response => response.json())
                        .then(data => {
                            // Clear existing options
                            supervisorSelect.innerHTML =
                                '<option value="" disabled selected>@lang('employee.selectSupervisor')</option>';

                            // Add new options
                            data.forEach(supervisor => {
                                const option = document.createElement('option');
                                option.value = supervisor.id;
                                option.textContent =
                                    `${supervisor.first_name} ${supervisor.last_name} | ${supervisor.employee_code}`;
                                supervisorSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error fetching supervisors:', error));
                }
            }

            // Attach event listener to branch select
            branchSelect.addEventListener('change', function() {
                fetchSupervisors(this.value);
            });

            // Initialize supervisors on page load if a branch is already selected
            if (branchSelect.value) {
                fetchSupervisors(branchSelect.value);
            }
        });
    </script>
    <script>
        jQuery(document).ready(function() {
            // click on next button
            jQuery('.form-wizard-next-btn').click(function() {
                var parentFieldset = jQuery(this).parents('.wizard-fieldset');
                var currentActiveStep = jQuery(this).parents('.form-wizard').find(
                    '.form-wizard-steps .active');
                var next = jQuery(this);
                var nextWizardStep = true;
                parentFieldset.find('.wizard-required').each(function() {
                    var thisValue = jQuery(this).val();

                    if (thisValue == "") {
                        jQuery(this).siblings(".wizard-form-error").slideDown();
                        nextWizardStep = false;
                    } else {
                        jQuery(this).siblings(".wizard-form-error").slideUp();
                    }
                });
                if (nextWizardStep) {
                    next.parents('.wizard-fieldset').removeClass("show", "400");
                    currentActiveStep.removeClass('active').addClass('activated').next().addClass('active',
                        "400");
                    next.parents('.wizard-fieldset').next('.wizard-fieldset').addClass("show", "400");
                    jQuery(document).find('.wizard-fieldset').each(function() {
                        if (jQuery(this).hasClass('show')) {
                            var formAtrr = jQuery(this).attr('data-tab-content');
                            jQuery(document).find('.form-wizard-steps .form-wizard-step-item').each(
                                function() {
                                    if (jQuery(this).attr('data-attr') == formAtrr) {
                                        jQuery(this).addClass('active');
                                        var innerWidth = jQuery(this).innerWidth();
                                        var position = jQuery(this).position();
                                        jQuery(document).find('.form-wizard-step-move').css({
                                            "left": position.left,
                                            "width": innerWidth
                                        });
                                    } else {
                                        jQuery(this).removeClass('active');
                                    }
                                });
                        }
                    });
                }
            });
            //click on previous button
            jQuery('.form-wizard-previous-btn').click(function() {
                var counter = parseInt(jQuery(".wizard-counter").text());;
                var prev = jQuery(this);
                var currentActiveStep = jQuery(this).parents('.form-wizard').find(
                    '.form-wizard-steps .active');
                prev.parents('.wizard-fieldset').removeClass("show", "400");
                prev.parents('.wizard-fieldset').prev('.wizard-fieldset').addClass("show", "400");
                currentActiveStep.removeClass('active').prev().removeClass('activated').addClass('active',
                    "400");
                jQuery(document).find('.wizard-fieldset').each(function() {
                    if (jQuery(this).hasClass('show')) {
                        var formAtrr = jQuery(this).attr('data-tab-content');
                        jQuery(document).find('.form-wizard-steps .form-wizard-step-item').each(
                            function() {
                                if (jQuery(this).attr('data-attr') == formAtrr) {
                                    jQuery(this).addClass('active');
                                    var innerWidth = jQuery(this).innerWidth();
                                    var position = jQuery(this).position();
                                    jQuery(document).find('.form-wizard-step-move').css({
                                        "left": position.left,
                                        "width": innerWidth
                                    });
                                } else {
                                    jQuery(this).removeClass('active');
                                }
                            });
                    }
                });
            });
            //click on form submit button
            jQuery(document).on("click", ".form-wizard .form-wizard-submit", function() {
                var parentFieldset = jQuery(this).parents('.wizard-fieldset');
                var currentActiveStep = jQuery(this).parents('.form-wizard').find(
                    '.form-wizard-steps .active');
                parentFieldset.find('.wizard-required').each(function() {
                    var thisValue = jQuery(this).val();
                    if (thisValue == "") {
                        jQuery(this).siblings(".wizard-form-error").slideDown();
                    } else {
                        jQuery(this).siblings(".wizard-form-error").slideUp();
                    }
                });
            });
            // focus on input field check empty or not
            jQuery(".form-control").on('focus', function() {
                var tmpThis = jQuery(this).val();
                if (tmpThis == '') {
                    jQuery(this).parent().addClass("focus-input");
                } else if (tmpThis != '') {
                    jQuery(this).parent().addClass("focus-input");
                }
            }).on('blur', function() {
                var tmpThis = jQuery(this).val();
                if (tmpThis == '') {
                    jQuery(this).parent().removeClass("focus-input");
                    jQuery(this).siblings('.wizard-form-error').slideDown("3000");
                } else if (tmpThis != '') {
                    jQuery(this).parent().addClass("focus-input");
                    jQuery(this).siblings('.wizard-form-error').slideUp("3000");
                }
            });
        });
    </script>
    <script>
        // Generate the route URL with Blade
        const positionRoute = "{{ route('positions.byDepartment', ['department' => ':departmentId']) }}";

        function fetchPositions(departmentId) {
            const positionDropdown = document.getElementById('position');

            // Clear existing options
            positionDropdown.innerHTML = '<option value="" selected disabled>@lang('employee.selectPosition')</option>';

            if (!departmentId) return;

            // Replace the placeholder with the actual departmentId
            const url = positionRoute.replace(':departmentId', departmentId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        response.positions.forEach(position => {
                            const option = document.createElement('option');
                            option.value = position.id;
                            option.textContent = position.name;
                            positionDropdown.appendChild(option);
                        });
                    } else {
                        alert('@lang('employee.errorFetchingPositions')');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching positions:', error);
                    alert('@lang('employee.errorFetchingPositions')');
                }
            });
        }


        $(document).ready(function() {
            function toggleField(groupName, targetId) {
                let show = $(`input[name="${groupName}"]:checked`).val() == '1';
                $(`#${targetId}`)[show ? 'slideDown' : 'slideUp']();
            }

            // Initial load
            toggleField('driving_license', 'license_fields');
            toggleField('kuwait_license', 'kuwait_license_fields');
            toggleField('egypt_license', 'egypt_license_fields');

            // On change
            $('input[name="driving_license"]').change(() => toggleField('driving_license', 'license_fields'));
            $('input[name="kuwait_license"]').change(() => toggleField('kuwait_license', 'kuwait_license_fields'));
            $('input[name="egypt_license"]').change(() => toggleField('egypt_license', 'egypt_license_fields'));
        });
    </script>
    <script>
        const categoryRouteBase = "{{ route('categoriesemployee', ['id' => 'PLACEHOLDER']) }}";

        $(document).ready(function() {
            $('#cuisine_category').on('change', function() {
                const selectedCategories = $(this).val() || [];
                const container = $('#dish-selects-container');
                container.empty();

                selectedCategories.forEach((categoryId, index) => {
                    const ajaxUrl = categoryRouteBase.replace('PLACEHOLDER', categoryId);

                    $.ajax({
                        url: ajaxUrl,
                        type: "GET",
                        dataType: "json",
                        success: function(dishes) {
                            const selectId = `dish-select-${categoryId}`;
                            let selectHtml = `
                        <div class="form-group dish-group border p-2 rounded mb-2">
                            <input type="hidden" name="cuisine_categories[${index}][category_id]" value="${categoryId}">
                            <label>@lang('Select Dishes for category') ${categoryId}</label>
                            <select name="cuisine_categories[${index}][dishes][]" id="${selectId}" multiple class="form-control select2">
                                <option value="-1">@lang('All dishes')</option>
                    `;

                            if (dishes.length > 0) {
                                $.each(dishes, function(index, dish) {
                                    selectHtml +=
                                        `<option value="${dish.id}">${dish.name}</option>`;
                                });
                            } else {
                                selectHtml +=
                                    `<option disabled>@lang('No dishes available')</option>`;
                            }

                            selectHtml += `</select></div>`;

                            container.append(selectHtml);
                            $(`#${selectId}`).select2();
                        },
                        error: function() {
                            alert('@lang('Failed to fetch dishes.')');
                        }
                    });
                });
            });

            $('.select2').select2();
        });
    </script>

    <script>
        function formatTimeTo12Hour(timeStr) {
            if (!timeStr) return '';
            const [hour, minute] = timeStr.split(':');
            const date = new Date();
            date.setHours(parseInt(hour), parseInt(minute));
            return date.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit'
            });
        }

        $(document).ready(function() {
            $('#shift_id').change(function() {
                var shiftId = $(this).val();

                if (shiftId) {
                    $.ajax({
                        url: '/dashboard/shifts/' + shiftId +
                            '/details', // New route for shift details
                        type: 'GET',
                        dataType: 'json',
                        beforeSend: function() {
                            $('#shiftDetailsContent').html(
                                '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>'
                            );
                            $('#shiftDetailsContainer').slideDown();
                        },
                        success: function(response) {
                            if (response.success) {
                                var shift = response.shift;
                                var timetable = response.timetable;
                                var days = response.days;

                                // Create table header
                                var detailsHtml = `
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>@lang('employee.Day')</th>
                                            <th>@lang('employee.OnDutyTime')</th>
                                            <th>@lang('employee.OffDutyTime')</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                                // Map day indexes to names
                                var dayNames = {
                                    0: '@lang('employee.Sunday')',
                                    1: '@lang('employee.Monday')',
                                    2: '@lang('employee.Tuesday')',
                                    3: '@lang('employee.Wednesday')',
                                    4: '@lang('employee.Thursday')',
                                    5: '@lang('employee.Friday')',
                                    6: '@lang('employee.Saturday')'
                                };

                                // Add rows for each day
                                days.forEach(function(day) {
                                    detailsHtml += `
                                <tr>
                                    <td>${dayNames[day.day_index] || 'N/A'}</td>
                                    <td>${formatTimeTo12Hour(timetable.on_duty_time)}</td>
                                    <td>${formatTimeTo12Hour(timetable.off_duty_time)}</td>
                                </tr>`;
                                });

                                detailsHtml += `</tbody></table>
                            <div class="mt-3">
                                <strong>@lang('employee.LatenessGracePeriod'):</strong> ${timetable.lateness_grace_period} @lang('employee.minutes')<br>
                                <strong>@lang('employee.CrossDay'):</strong> ${timetable.cross_day ? '@lang('employee.yes')' : '@lang('employee.no')'}
                            </div>
                        </div>`;

                                $('#shiftDetailsContent').html(detailsHtml);
                            } else {
                                $('#shiftDetailsContent').html(
                                    '<div class="alert alert-danger">@lang('employee.ErrorLoadingShiftDetails')</div>'
                                );
                            }
                        },
                        error: function() {
                            $('#shiftDetailsContent').html(
                                '<div class="alert alert-danger">@lang('employee.ErrorLoadingShiftDetails')</div>');
                        }
                    });
                } else {
                    $('#shiftDetailsContainer').slideUp();
                }
            });
        });
    </script>
@endsection
