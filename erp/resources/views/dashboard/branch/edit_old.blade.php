@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            /* width: 500px; */
            /* Set a specific width */
            height: 300px;
            /* Set a specific height */
            /* You can adjust the values based on your needs */
        }

        .map-container {
            width: 100%;
            /* Or a specific width */
            /* You can set the width to 100% to fill the parent or any specific width like 500px */
        }
    </style>
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('branch.EditBranch')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('dashboard.home')}}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{route('branches.list')}}">@lang('branch.Branches')</a></li>                    
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch.EditBranch')</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid ">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('branch.EditBranch')
                            </div>
                        </div>
                        <div class="card-body">
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

                            <form method="POST" id="branch-form" action="{{ route('branch.update', $branch->id) }}" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT') <!-- Use PUT or PATCH for updates -->
                                <div class="row gy-4">
                                    <!-- Arabic Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.ArabicName')</label>
                                        <input type="text" class="form-control" name="name_ar" value="{{ old('name_ar', $branch->name_ar) }}"
                                               placeholder="@lang('branch.ArabicName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicName')
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.EnglishName')</label>
                                        <input type="text" class="form-control" name="name_en" value="{{ old('name_en', $branch->name_en) }}"
                                               placeholder="@lang('branch.EnglishName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishName')
                                        </div>
                                    </div>

                                    <!-- Address Arabic -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.ArabicAddress')</label>
                                        <textarea class="form-control" name="address_ar" rows="2" required>{{ old('address_ar', $branch->address_ar) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicAddress')
                                        </div>
                                    </div>

                                    <!-- Address English -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('branch.EnglishAddress')</label>
                                        <textarea class="form-control" name="address_en" rows="2" required>{{ old('address_en', $branch->address_en) }}</textarea>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishAddress')
                                        </div>
                                    </div>

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <!-- <div class="d-flex align-items-center justify-content-between">
                                            <a onclick="searchLocation()" class="btn btn-primary col-md-2">@lang('header.search')</a>
                                        </div> -->
                                        <div class="map position-relative my-3">
                                            <div class="map col-xl-12 col-lg-12 col-md-12 col-sm-12" id="map" width="auto" height="450" style="border:0;"></div>
                                        </div>

                                        <input type="hidden" id="latitude" name="latitute" value="{{ old('latitute', $branch->latitute) }}">
                                        <input type="hidden" id="longitude" name="longitute" value="{{ old('longitute', $branch->longitute) }}">
                                    </div>

                                    <div class="logout-modal modal fade" tabindex="-1" id="notfoundddressModal">
                                        <div class="modal-dialog  modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header border-0">
                                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <i class="fas fa-sign-out-alt main-color fs-1"></i>
                                                    <h4 class="mt-4"> @lang('auth.notfoundaddress')</h4>
                                                </div>
                                                <div class="modal-footer d-flex border-0 align-items-center justify-content-center">
                                                    <button type="button" class="btn reversed main-color w-25 mx-2"
                                                    data-bs-dismiss="modal"> @lang('auth.ok')</button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Country -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.Country')</label>
                                        <select class="form-control select2" name="country_id" id="country_id" required>
                                            <option value="" disabled>@lang('branch.SelectCountry')</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }}" {{ $country->id == old('country_id', $branch->country_id) ? 'selected' : '' }}>
                                                    {{ $country->name_ar ." | ". $country->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCountry')
                                        </div>
                                    </div>

                                    <!-- Latitude and Longitude -->
                                    <!-- <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.Latitude')</label>
                                        <input type="text" class="form-control" name="latitute" required value="{{ old('latitute', $branch->latitute) }}">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterLatitude')
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.Longitude')</label>
                                        <input type="text" class="form-control" name="longitute" required value="{{ old('longitute', $branch->longitute) }}">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterLongitude')
                                        </div>
                                    </div> -->

                                    <!-- Phone -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.Phone')</label>
                                        <input type="tel" pattern=".{10}" class="form-control phone-valid" name="phone" id="phone" onblur="isLengthPhone(this)" required value="{{ old('phone', $branch->phone) }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterPhone')
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                        <label class="form-label">@lang('branch.Email')</label>
                                        <input type="email" class="form-control" name="email" value="{{ old('email', $branch->email) }}">
                                    </div>

                                     <!-- Manager Name -->
                                     <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.ManagerName')</label>
                                        <select class="form-control select2" name="employee_id" id="employee_id" required>
                                            <option value="" disabled selected>@lang('branch.SelectEmployee')</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ $employee->id == old('employee_id', $branch->employee_id) ? 'selected' : '' }}>{{ $employee->first_name."  ". $employee->last_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEmployee')
                                        </div>
                                    </div>

                                    <!-- Opening and Closing Hours -->
                                    <!-- <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.OpeningHour')</label>
                                        <input type="time" class="form-control" name="opening_hour" required value="{{ old('opening_hour', $branch->opening_hour) }}">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterOpeningHour')
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.ClosingHour')</label>
                                        <input type="time" class="form-control" name="closing_hour" required value="{{ old('closing_hour', $branch->closing_hour) }}">
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterClosingHour')
                                        </div>
                                    </div> -->

                                    <!-- Has Kids Area -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.HasKidsArea')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_kids_area" value="1"
                                                {{ old('has_kids_area', $branch->has_kids_area) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_kids_area" value="0"
                                                {{ old('has_kids_area', $branch->has_kids_area) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <!-- Is Delivery -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsDelivery')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_delivery" value="1"
                                                {{ old('is_delivery', $branch->is_delivery) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_delivery" value="0"
                                                {{ old('is_delivery', $branch->is_delivery) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Is default -->
                                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                        <label class="form-label">@lang('branch.IsDefault')</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_default" value="1"
                                                {{ old('is_default', $branch->is_default) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_default" value="0"
                                                {{ old('is_default', $branch->is_default) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <div id="dish-sizes-section" class="col-xl-12">
                                        <h5 class="mb-3">@lang('branch.barchTime')</h5>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('branch.Day')</th>
                                                    <th>@lang('branch.OpeningHour')</th>
                                                    <th>@lang('branch.ClosingHour')</th>
                                                    <th>@lang('branch.CrossDay')</th>
                                                    <th>@lang('branch.Actions')</th>
                                                </tr>
                                            </thead>
                                            <tbody id="branch-time-table">
                                            
                                            @if (Cookie::get('branch_times') !== null)
                                                @php
                                                    $branch_times = json_decode(Cookie::get('branch_times'));
                                                    if ($branch_times === null) {
                                                        $branch_times = [];
                                                    }
                                                @endphp

                                                @foreach($branch_times as $k_branchTime => $branchTime)
                                                    <tr>
                                                        <td>
                                                            <input type="text" name="time[{{$k_branchTime}}][id]" id="time[{{$k_branchTime}}][id]" value="{{$branchTime->id}}">
                                                            <select name="time[{{$k_branchTime}}][day]" id="time[{{$k_branchTime}}][day]" class="form-control select2" required>
                                                                <option value="6" {{$branchTime->day == 6 ? "selected" : ""}}>@lang('branch.Saturday')</option>
                                                                <option value="0" {{$branchTime->day == 0 ? "selected" : ""}}>@lang('branch.Sunday')</option>
                                                                <option value="1" {{$branchTime->day == 1 ? "selected" : ""}}>@lang('branch.Monday')</option>
                                                                <option value="2" {{$branchTime->day == 2 ? "selected" : ""}}>@lang('branch.Tuesday')</option>
                                                                <option value="3" {{$branchTime->day == 3 ? "selected" : ""}}>@lang('branch.Wednesday')</option>
                                                                <option value="4" {{$branchTime->day == 4 ? "selected" : ""}}>@lang('branch.Thursday')</option>
                                                                <option value="5" {{$branchTime->day == 5 ? "selected" : ""}}>@lang('branch.Friday')</option>
                                                            </select>
                                                        </td>
                                                        <td><input type="time" name="time[{{$k_branchTime}}][opening_hour]" value="{{$branchTime->opening_hour}}" class="form-control" required></td>
                                                        <td><input type="time" name="time[{{$k_branchTime}}][closing_hour]" value="{{$branchTime->closing_hour}}" class="form-control" required></td>
                                                        <td>
                                                            @lang('branch.24Hours')<input type="radio" name="time[{{$k_branchTime}}][cross_day]" value="0" class="form-check-input" {{$branchTime->cross_day == 0 ? "checked" : ""}}><br>
                                                            @lang('branch.12Hours')<input type="radio" name="time[{{$k_branchTime}}][cross_day]" value="1" class="form-check-input" {{$branchTime->cross_day == 1 ? "checked" : ""}}>
                                                        </td>
                                                        <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('branch.remove')</button></td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                @if($branch->branchTimes)
                                                    @foreach($branch->branchTimes as $k_branchTime => $branchTime)
                                                        <tr>
                                                            <td>
                                                                <input type="text" name="time[{{$k_branchTime}}][id]" id="time[{{$k_branchTime}}][id]" value="{{$branchTime->id}}">
                                                                <select name="time[{{$k_branchTime}}][day]" id="time[{{$k_branchTime}}][day]" class="form-control select2" required>
                                                                    <option value="6" {{$branchTime->day == 6 ? "selected" : ""}}>@lang('branch.Saturday')</option>
                                                                    <option value="0" {{$branchTime->day == 0 ? "selected" : ""}}>@lang('branch.Sunday')</option>
                                                                    <option value="1" {{$branchTime->day == 1 ? "selected" : ""}}>@lang('branch.Monday')</option>
                                                                    <option value="2" {{$branchTime->day == 2 ? "selected" : ""}}>@lang('branch.Tuesday')</option>
                                                                    <option value="3" {{$branchTime->day == 3 ? "selected" : ""}}>@lang('branch.Wednesday')</option>
                                                                    <option value="4" {{$branchTime->day == 4 ? "selected" : ""}}>@lang('branch.Thursday')</option>
                                                                    <option value="5" {{$branchTime->day == 5 ? "selected" : ""}}>@lang('branch.Friday')</option>
                                                                </select>
                                                            </td>
                                                            <td><input type="time" name="time[{{$k_branchTime}}][opening_hour]" value="{{$branchTime->opening_hour}}" class="form-control" required></td>
                                                            <td><input type="time" name="time[{{$k_branchTime}}][closing_hour]" value="{{$branchTime->closing_hour}}" class="form-control" required></td>
                                                            <td>
                                                                @lang('branch.24Hours')<input type="radio" name="time[{{$k_branchTime}}][cross_day]" value="0" class="form-check-input" {{$branchTime->cross_day == 0 ? "checked" : ""}}><br>
                                                                @lang('branch.12Hours')<input type="radio" name="time[{{$k_branchTime}}][cross_day]" value="1" class="form-check-input" {{$branchTime->cross_day == 1 ? "checked" : ""}}>
                                                            </td>
                                                            <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('branch.remove')</button></td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endif
                                            </tbody>
                                        </table>
                                        <button type="button" id="add-branch-time" class="btn btn-success btn-sm">@lang('branch.AddTime')</button>
                                    </div>

                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary" id="input-submit"
                                                   value="@lang('category.save')">
                                        </div>
                                    </center>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
        $(document).ready(function () {
            $('.select2').select2();
        });

        let branchTimeIndex = {{$branch->branchTimes->count()}};
        $('#add-branch-time').on('click', function () {
            $('#branch-time-table').append(`
                <tr>
                    <td>
                    <input name="time[${branchTimeIndex}][id]" type="hidden" value="">
                    <select name="time[${branchTimeIndex}][day]" id="time[${branchTimeIndex}][day]" class="form-control select2" required>
                    <option value="6">@lang('branch.Saturday')</option>
                    <option value="0">@lang('branch.Sunday')</option>
                    <option value="1">@lang('branch.Monday')</option>
                    <option value="2">@lang('branch.Tuesday')</option>
                    <option value="3">@lang('branch.Wednesday')</option>
                    <option value="4">@lang('branch.Thursday')</option>
                    <option value="5">@lang('branch.Friday')</option>
                    </select></td>
                    <td><input type="time" name="time[${branchTimeIndex}][opening_hour]" class="form-control" required></td>
                    <td><input type="time" name="time[${branchTimeIndex}][closing_hour]" class="form-control" required></td>
                    <td>@lang('branch.24Hours')<input type="radio" name="time[${branchTimeIndex}][cross_day]" value="0" class="form-check-input" checked><br>
                    @lang('branch.12Hours')<input type="radio" name="time[${branchTimeIndex}][cross_day]" value="1" class="form-check-input"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('branch.remove')</button></td>
                </tr>
            `);
            branchTimeIndex++;
            $('.select2').select2();
        });

        // Remove Row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('change', '#country_id', function () {
            $('#phone').val("");
        }); 

        document.getElementById('branch-form').addEventListener('submit', function(event) {
            // Get all the elements related to the form inputs
            var time_id = document.querySelectorAll('input[name^="time"][name$="][id]"]');
            var time_day = document.querySelectorAll('select[name^="time"][name$="][day]"]');
            var time_opening_hour = document.querySelectorAll('input[name^="time"][name$="][opening_hour]"]');
            var time_closing_hour = document.querySelectorAll('input[name^="time"][name$="][closing_hour]"]');
            var time_cross_day = document.querySelectorAll('input[name^="time"][name$="][cross_day]"]');
            var time_count = time_day.length;
            var branch_times = [];

            for (let i = 0; i < time_count; i++) {
                var branch_time_item = {
                    id: time_id[i].value,
                    day: time_day[i].value,
                    opening_hour: time_opening_hour[i].value,
                    closing_hour: time_closing_hour[i].value,
                    cross_day: time_cross_day[i].value
                };
                branch_times.push(branch_time_item);
            }

            //Cookies.set('branch_times', JSON.stringify(branch_times), { expires: 2 }); // 2 minutes expiry
            setCookie('branch_times', JSON.stringify(branch_times), 2);
        });

        function setCookie(name, value, days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));  // Set expiration date
            var expires = "expires=" + date.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        }

        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i].trim();
                if (c.indexOf(nameEQ) === 0) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
            return null;
        }

        document.addEventListener('DOMContentLoaded', function() {
            var branchTimesCookie = getCookie('branch_times');
            if (branchTimesCookie) {
                var branchTimes = JSON.parse(branchTimesCookie);
                console.log(branchTimes); // Log to check the cookie data
            }
        });

        // Validate phone length and make AJAX call
        function isLengthPhone(phone, event) {
            var phoneValue = phone.value.trim(); // Ensure the phone input value is a string
            var countryId = $('#country_id').val();
            var get_url = "{{ route('country.shows', 'id') }}";
            get_url = get_url.replace('id', countryId);

            $.ajax({
                url: get_url, 
                type: 'GET',
                success: function(data) {
                    var country_phone_length = data['length']; // Length from the server
                    const phoneValidation = document.querySelector('.phone-valid');
                    const formValidation = document.querySelector('.needs-validation');

                    if (phoneValidation) {
                        // Check if the phone is numeric and has the correct length
                        if (/^\d+$/.test(phoneValue) && phoneValue.length === country_phone_length) {
                            phoneValidation.classList.add('is-valid');
                            phoneValidation.classList.remove('is-invalid');
                            formValidation.classList.remove('was-validated');
                        } else {
                            phoneValidation.classList.add('is-invalid');
                            phoneValidation.classList.remove('is-valid');
                            formValidation.classList.add('was-validated');
                        }
                    }

                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        }
    </script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Use old() values if validation failed; fallback to default values from $address
        var defaultLatitude = {{ old('latitute', $branch->latitute ?? 30.053802144287577) }};
        var defaultLongitude = {{ old('longitute', $branch->longitute ?? 31.23096827116421) }};
        var isEdit = false;
        var locationButton = document.getElementById('locationButton');

        if (isEdit) {
            locationButton.disabled = false;
            document.getElementById('latitude').value = defaultLatitude;
            document.getElementById('longitude').value = defaultLongitude;
        }

        // Initialize map with default latitude and longitude
        var map = L.map('map').setView([defaultLatitude, defaultLongitude], 13);

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Create draggable marker
        var marker = L.marker([defaultLatitude, defaultLongitude], {
            draggable: true
        }).addTo(map)
          .bindPopup('Drag or click to update location')
          .openPopup();

        // Update inputs and enable button on marker drag
        marker.on('dragend', function(event) {
            var position = marker.getLatLng();
            document.getElementById('latitude').value = position.lat;
            document.getElementById('longitude').value = position.lng;
            locationButton.disabled = false;
        });

        // Update inputs and marker position on map click
        map.on('click', function(event) {
            var lat = event.latlng.lat;
            var lng = event.latlng.lng;

            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            locationButton.disabled = false;

            marker.bindPopup('New location selected').openPopup();
        });

        // Search functionality
        window.searchLocation = function() {
            var query = document.getElementById('searchBox').value;
            if (!query) {
                var modal = new bootstrap.Modal(document.getElementById('notfoundddressModal'));
                modal.show();
                return;
            }

            var url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        var lat = data[0].lat;
                        var lon = data[0].lon;

                        map.setView([lat, lon], 13);
                        marker.setLatLng([lat, lon]);
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lon;
                        locationButton.disabled = false;
                        marker.bindPopup(data[0].display_name).openPopup();
                    } else {
                        var modal = new bootstrap.Modal(document.getElementById('notfoundddressModal'));
                        modal.show();
                    }
                })
                .catch(error => console.error('Error:', error));
        };
    });
</script>
@endsection
