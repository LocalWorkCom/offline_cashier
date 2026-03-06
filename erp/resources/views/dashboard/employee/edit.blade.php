@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
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
            width: 25%;
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
        <h4 class="fw-medium mb-0">@lang('employee.editEmployee')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('employees.list') }}">@lang('employee.employees')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('employee.editEmployee')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid ">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('employee.editEmployee')
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
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <form method="POST" action="{{ route('employee.update', $employee->id) }}"
                                class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                @method('PUT')
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
                                                    </ul>
                                                </div>

                                                <fieldset class="wizard-fieldset show">
                                                    <h5>@lang('employee.Personal')</h5>
                                                    {{-- name --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.firstName')*</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="first_name" name="first_name"
                                                                value="{{ old('first_name', $employee->first_name) }}"
                                                                placeholder="@lang('employee.firstName')" required>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterFirstName')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.lastName')*</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="last_name" name="last_name"
                                                                value="{{ old('last_name', $employee->last_name) }}"
                                                                placeholder="@lang('employee.lastName')" required>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterLastName')
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- code & email --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.code')*</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="employee_code" name="employee_code"
                                                                value="{{ old('employee_code', $employee->employee_code) }}"
                                                                placeholder="@lang('employee.code')" required>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEmployeeCode')
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.email')*</label>
                                                            <input type="email" class="form-control wizard-required"
                                                                id="email" name="email"
                                                                value="{{ old('email', $employee->email) }}"
                                                                placeholder="@lang('employee.email')" required>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterValidEmail')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    {{-- phone $ countrycode --}}
                                                    <div class="row gy-4">
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.countryCode')*</p>
                                                            <select name="country_code"
                                                                class="select2 form-control wizard-required" required>
                                                                <option value="" disabled selected>@lang('employee.chooseCountryCode')
                                                                </option>
                                                                @foreach ($countries as $country)
                                                                    <option value="{{ $country->phone_code }}"
                                                                        {{ old('country_code', $employee->country_code) == $country->phone_code ? 'selected' : '' }}>
                                                                        {{ $country->phone_code }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">@lang('validation.EnterCountryCode')</div>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.phone')*</label>
                                                            <input type="text" class="form-control wizard-required"
                                                                id="phone" name="phone"
                                                                value="{{ old('phone', $employee->phone_number) }}"
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
                                                    {{-- gender & birthdate --}}
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="gender"
                                                                class="form-label">@lang('employee.gender')</label>
                                                            <select class="select2 form-control  wizard-required"
                                                                id="gender" name="gender">
                                                                <option value="" selected disabled>@lang('employee.selectGender')
                                                                </option>
                                                                <option value="male"
                                                                    {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>
                                                                    @lang('employee.male')</option>
                                                                <option value="female"
                                                                    {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>
                                                                    @lang('employee.female')</option>
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.selectGender')
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="birth_date"
                                                                class="form-label">@lang('employee.dob')</label>
                                                            <input type="date" class="form-control " id="birth_date"
                                                                name="birth_date"
                                                                value="{{ old('birth_date', $employee->birth_date) }}"
                                                                placeholder="@lang('employee.dob')">
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterDateOfBirth')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group clearfix">
                                                        <a href="javascript:;" class="form-wizard-next-btn float-right">
                                                            @lang('employee.Next')</a>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="wizard-fieldset">
                                                    <h5> @lang('employee.Information')</h5>
                                                    <div class="row gy-4">

                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.passport')</label>
                                                            <input type="text" class="form-control"
                                                                id="passport_number" name="passport_number"
                                                                value="{{ old('passport_number', $employee->passport_number) }}"
                                                                placeholder="@lang('employee.passport')">
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterValidnationalId')
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('employee.national_id')</label>
                                                            <input type="text" class="form-control" id="national_id"
                                                                name="national_id"
                                                                value="{{ old('national_id', $employee->national_id) }}"
                                                                placeholder="@lang('employee.national_id')">
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterValidPassportNumber')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">

                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="marital_status"
                                                                class="form-label">@lang('employee.maritalStatus')</label>
                                                            <select class="select2 form-control" id="marital_status"
                                                                name="marital_status">
                                                                <option value="" selected disabled>@lang('employee.selectMaritalStatus')
                                                                </option>
                                                                <option value="Married"
                                                                    @if (old('marital_status', $employee->marital_status) == 'Married') selected @endif>
                                                                    @lang('employee.married')</option>
                                                                <option value="Single"
                                                                    @if (old('marital_status', $employee->marital_status) == 'Single') selected @endif>
                                                                    @lang('employee.single')</option>
                                                                <option value="Divorced"
                                                                    @if (old('marital_status', $employee->marital_status) == 'Divorced') selected @endif>
                                                                    @lang('employee.divorced')</option>
                                                                <option value="Widowed"
                                                                    @if (old('marital_status', $employee->marital_status) == 'Widowed') selected @endif>
                                                                    @lang('employee.widowed')</option>
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
                                                                <option value="A+"
                                                                    @if (old('blood_group', $employee->blood_group) == 'A+') selected @endif>A+
                                                                </option>
                                                                <option
                                                                    value="A-"@if (old('blood_group', $employee->blood_group) == 'A-') selected @endif>
                                                                    A-</option>
                                                                <option
                                                                    value="B+"@if (old('blood_group', $employee->blood_group) == 'B+') selected @endif>
                                                                    B+</option>
                                                                <option
                                                                    value="B-"@if (old('blood_group', $employee->blood_group) == 'B-') selected @endif>
                                                                    B-</option>
                                                                <option
                                                                    value="AB+"@if (old('blood_group', $employee->blood_group) == 'AB+') selected @endif>
                                                                    AB+</option>
                                                                <option
                                                                    value="AB-"@if (old('blood_group', $employee->blood_group) == 'AB-') selected @endif>
                                                                    AB-</option>
                                                                <option
                                                                    value="O+"@if (old('blood_group', $employee->blood_group) == 'O+') selected @endif>
                                                                    O+</option>
                                                                <option
                                                                    value="O-"@if (old('blood_group', $employee->blood_group) == 'O-') selected @endif>
                                                                    O-</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.selectBloodGroup')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">

                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="emergency_contact_name"
                                                                class="form-label">@lang('employee.emergencyName')</label>
                                                            <input type="text" class="form-control"
                                                                id="emergency_contact_name" name="emergency_contact_name"
                                                                value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}"
                                                                placeholder="@lang('employee.emergencyName')">
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEmergencyContactName')
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="emergency_contact_relationship"
                                                                class="form-label">@lang('employee.emergencyRel')</label>
                                                            <input type="text" class="form-control"
                                                                id="emergency_contact_relationship"
                                                                name="emergency_contact_relationship"
                                                                value="{{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) }}"
                                                                placeholder="@lang('employee.emergencyRel')">
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEmergencyContactRelationship')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="emergency_contact_phone"
                                                            class="form-label">@lang('employee.emergencyPhone')</label>
                                                        <input type="text" class="form-control"
                                                            id="emergency_contact_phone" name="emergency_contact_phone"
                                                            value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}"
                                                            placeholder="@lang('employee.emergencyPhone')">
                                                        <div class="wizard-form-error"></div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEmergencyContactPhone')
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
                                                    <h5> @lang('employee.Employee placement')</h5>
                                                    <div class="row gy-4">

                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.department')</p>
                                                            <select name="department_id" class="select2 form-control"
                                                                onchange="fetchPositions(this.value)">
                                                                <option value="" selected disabled>@lang('employee.selectDepartment')
                                                                </option>
                                                                @foreach ($departments as $department)
                                                                    <option value="{{ $department->id }}"
                                                                        @if (old('department_id', $employee->department_id) == $department->id) selected @endif>
                                                                        {{ app()->getLocale() === 'ar' ? $department->name_ar : $department->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterDEpartment') </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.position')</p>
                                                            <select name="position_id" id="position"
                                                                class="select2 form-control">
                                                                <option value="" selected disabled>@lang('employee.selectPosition')
                                                                </option>
                                                            </select>
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">@lang('validation.EnterPosition')</div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.jobdesc')</p>
                                                            <select name="flag" id="flag"
                                                                class="select2 form-control"
                                                                onchange="toggleVehicleSelect();toggleBranchSelect()">
                                                                <option value="" selected disabled>@lang('employee.jobdesc')
                                                                </option>
                                                                <option value="waiter"
                                                                    @if (old('flag', $employee->flag) == 'waiter') selected @endif>
                                                                    @lang('employee.waiter')</option>
                                                                <option value="chef"
                                                                    @if (old('flag', $employee->flag) == 'chef') selected @endif>
                                                                    @lang('employee.chef')</option>
                                                                <option value="cashier"
                                                                    @if (old('flag', $employee->flag) == 'cashier') selected @endif>
                                                                    @lang('employee.cashier')</option>
                                                                <option
                                                                    value="customer_service"@if (old('flag', $employee->flag) == 'customer_service') selected @endif>
                                                                    @lang('employee.customer_service')</option>
                                                                <option value="customer_service"
                                                                    @if (old('flag', $employee->flag) == 'customer_service') selected @endif>
                                                                    @lang('employee.customer_service')
                                                                </option>
                                                                <option value="driver"
                                                                    @if (old('flag', $employee->flag) == 'driver') selected @endif>
                                                                    @lang('employee.driver')</option>
                                                                <option value="kitchen staff"
                                                                    @if (old('flag', $employee->flag) == 'kitchen staff') selected @endif>
                                                                    @lang('employee.kitchen_staff')</option>
                                                                <option value="supervisor"
                                                                    @if (old('flag', $employee->flag) == 'supervisor') selected @endif>
                                                                    @lang('employee.supervisor')</option>
                                                                @if (Auth::user() && (Auth::user()->hasRole('superAdmin') || Auth::user()->hasRole('LocalWork Admin')))
                                                                    <option value="officer"
                                                                        @if (old('flag', $employee->flag) == 'officer') selected @endif>
                                                                        @lang('employee.officer')</option>
                                                                    <option value="kitchen manager"
                                                                        @if (old('flag', $employee->flag) == 'kitchen manager') selected @endif>
                                                                        @lang('employee.kitchen_manager')
                                                                    </option>
                                                                    <option
                                                                        value="branch manager"@if (old('flag', $employee->flag) == 'branch manager') selected @endif>
                                                                        @lang('employee.branch_manager')
                                                                    </option>
                                                                @endif
                                                                <option
                                                                    value="employee"@if (old('flag', $employee->flag) == 'employee') selected @endif>
                                                                    @lang('employee.employee')</option>
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
                                                                        <option value="{{ $vehicle->id }}"
                                                                            @if (old('vehicle_type', $employee->vehicle_id) == $vehicle->id) selected @endif>
                                                                            {{ $vehicle->vehicle_type }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.branch')</p>
                                                            <div id="branch-container">
                                                                <select name="branch_id" id="branch"
                                                                    class="form-control" >
                                                                    <option value="" selected disabled>
                                                                        @lang('employee.selectBranch')</option>
                                                                    @foreach ($branches as $branch)
                                                                        <option value="{{ $branch->id }}"
                                                                            @if (old('branch_id', $employee->vehicle_id) == $branch->id) selected @endif>
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
                                                                <option value="" disabled selected>@lang('employee.selectSupervisor')
                                                                </option>
                                                                @foreach ($supervisors as $supervisor)
                                                                    <option value="{{ $supervisor->id }}"
                                                                        @if (old('supervisor_id', $employee->supervisor_id) == $supervisor->id) selected @endif>
                                                                        {{ $supervisor->first_name . ' ' . $supervisor->last_name . ' | ' . $supervisor->employee_code }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">@lang('validation.EnterDEpartment')</div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">

                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.nationality')</p>
                                                            <select name="nationality_id" class="select2 form-control">
                                                                <option value="" selected disabled>@lang('employee.selectNationality')
                                                                </option>
                                                                @foreach ($nationalities as $nationality)
                                                                    <option value="{{ $nationality->id }}"
                                                                        @if (old('nationality_id', $employee->nationality_id) == $nationality->id) selected @endif>
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
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.image') </div>
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
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="hire_date"
                                                                class="form-label">@lang('employee.hireDate')</label>
                                                            <input type="date" class="form-control" id="hire_date"
                                                                name="hire_date"
                                                                value="{{ old('hire_date', $employee->hire_date) }}"
                                                                placeholder="@lang('employee.hireDate')">
                                                            <div class="wizard-form-error"></div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterHireDate')
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="salary"
                                                                class="form-label">@lang('employee.salary')*</label>
                                                            <input type="text" class="form-control" id="salary"
                                                                name="salary"
                                                                value="{{ old('salary', $employee->salary) }}"
                                                                placeholder="@lang('employee.salary')">
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterSalary')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">
                                                        <div class="col-lg-6 col-md-6 col-sm-6">
                                                            <div class="form-group">
                                                                <label for="assurance_salary"
                                                                    class="form-label">@lang('employee.assuranceSalary')*</label>
                                                                <input type="text" class="form-control"
                                                                    id="assurance_salary" name="assurance_salary"
                                                                    value="{{ old('assurance_salary', $employee->assurance_salary) }}"
                                                                    placeholder="@lang('employee.assuranceSalary')">
                                                                <div class="wizard-form-error"></div>
                                                                <div class="invalid-feedback">
                                                                    @lang('validation.EnterAssuranceSalary')
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-6">
                                                            <div class="form-group">
                                                                <label for="assurance_number"
                                                                    class="form-label">@lang('employee.assuranceNumber')</label>
                                                                <input type="text" class="form-control"
                                                                    id="assurance_number" name="assurance_number"
                                                                    value="{{ old('assurance_number', $employee->assurance_number) }}"
                                                                    placeholder="@lang('employee.assuranceNumber')">

                                                                <div class="invalid-feedback">
                                                                    @lang('validation.EnterAssuranceNumber')
                                                                </div>
                                                                <div class="wizard-form-error"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="bank_account"
                                                                class="form-label">@lang('employee.bankAccount')</label>
                                                            <input type="text" class="form-control" id="bank_account"
                                                                name="bank_account"
                                                                value="{{ old('bank_account', $employee->bank_account) }}"
                                                                placeholder="@lang('employee.bankAccount')">
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterBankAccount')
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="employment_type"
                                                                class="form-label">@lang('employee.employmentType')</label>
                                                            <select class="select2 form-control" id="employment_type"
                                                                name="employment_type">
                                                                <option value="" selected disabled>
                                                                    @lang('employee.selectEmploymentType')</option>
                                                                <option value="Part-Time"
                                                                    @if (old('employment_type', $employee->employment_type) == 'Part-Time') selected @endif>
                                                                    @lang('employee.part-time')</option>
                                                                <option value="Full-Time"
                                                                    @if (old('employment_type', $employee->employment_type) == 'Full-Time') selected @endif>
                                                                    @lang('employee.full-time')</option>
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
                                                    <div class="row gy-4">
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.status')*</p>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="status" id="Radio-md" value="active"
                                                                    @if (old('status', $employee->status) == 'active') checked @endif>
                                                                <label class="form-check-label" for="Radio-md">
                                                                    @lang('employee.active')
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="status" id="Radio-md" value="not-active"
                                                                    @if (old('status', $employee->status) == 'not-active') checked @endif>
                                                                <label class="form-check-label" for="Radio-md">
                                                                    @lang('employee.notActive')
                                                                </label>
                                                            </div>
                                                            <div class="wizard-form-error"></div>
                                                        </div>
                                                        <div class="form-group col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <p class="mb-2 text-muted">@lang('employee.isBiometric')</p>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="is_biometric" id="Radio-md" value="1"
                                                                    @if (old('is_biometric', $employee->is_biometric) == 1) checked @endif>
                                                                <label class="form-check-label" for="Radio-md">
                                                                    @lang('employee.yes')
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    name="is_biometric" id="Radio-md" value="0"
                                                                    @if (old('is_biometric', $employee->is_biometric) == 0) checked @endif>
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
                                                                name="biometric_id"
                                                                value="{{ old('biometric_id', $employee->biometric_id) }}"
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
                                                            <textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes', $employee->notes) }}</textarea>
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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End:: row-1 -->
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- INETRNAL SELECT2 JS -->
    @vite('resources/assets/js/select2.js')

    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    <script>
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

        function toggleVehicleSelect() {
            var flag = document.getElementById("flag").value;
            var vehicleSelect = document.getElementById("vehicleSelect");

            if (flag === "driver") {
                vehicleSelect.style.display = "block";
            } else {
                vehicleSelect.style.display = "none";
            }
        }

        function toggleBranchSelect() {
            var flag = $('#flag').val();
            var branchEnabledFlags = ['waiter', 'chef', 'cashier', 'driver', 'kitchen staff', 'supervisor',
                'branch manager'
            ];
            if (branchEnabledFlags.includes(flag)) {
                $('#branch').prop('disabled', false);
            } else {
                $('#branch').prop('disabled', true).val('');
            }
        }

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
                            '<option value="" disabled selected>@lang('employee.selectSupervisor')</option>');
                        $.each(data, function(index, supervisor) {
                            supervisorSelect.append('<option value="' + supervisor.id + '">' +
                                supervisor.first_name + ' ' + supervisor.last_name + ' | ' +
                                supervisor.employee_code + '</option>');
                        });
                    },
                    error: function() {
                        console.error('Failed to fetch supervisors.');
                    }
                });
            }
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
    </script>
@endsection
