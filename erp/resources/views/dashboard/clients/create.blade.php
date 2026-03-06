@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('client.addClient')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('client.index') }}">@lang('client.clients')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('client.addClient')</li>
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
                                @lang('client.addClient')
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
                            <form method="POST" action="{{ route('client.store') }}" class="needs-validation"
                                enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="input-placeholder" class="form-label">@lang('client.name')</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="{{ old('name') }}" placeholder="@lang('client.name')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterName')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="input-placeholder" class="form-label">@lang('client.email')</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ old('email') }}" placeholder="@lang('client.email')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterValidEmail')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="input-placeholder" class="form-label">@lang('client.countryCode')</label>
                                        <select name="country_code" id="country_code" class="select2 form-control" required>
                                            <option value="" disabled>@lang('client.chooseCountry')</option>
                                            @foreach (GetCountries() as $country)
                                                <option value="{{ $country->phone_code }}">
                                                    {{ $country->phone_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCountryCode')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="input-placeholder" class="form-label">@lang('client.phone')</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            value="{{ old('phone') }}" placeholder="@lang('client.phone')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterValidPhone')
                                        </div>
                                    </div>
                                    {{-- <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="input-file" class="form-label">@lang('client.img')</label>
                                        <input class="form-control" type="file" id="image" name="image">
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterImage')
                                        </div>
                                    </div> --}}
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="birth_date" class="form-label">@lang('client.dob')</label>
                                        <input type="date" class="form-control" id="birth_date" name="birth_date"
                                            value="{{ old('birth_date') }}" placeholder="@lang('client.dob')">
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterDateOfBirth')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <p class="mb-2 text-muted">@lang('client.country')</p>
                                        <select name="country_id" class="select2 form-control" required>
                                            <option value="" disabled>@lang('client.chooseCountry')</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}">
                                                    {{ $country->name_ar . ' | ' . $country->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterBrand')</div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="address" class="form-label">@lang('client.address')</label>
                                        <input type="text" class="form-control" id="address" name="address"
                                            value="{{ old('address') }}" placeholder="@lang('client.address')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterAddress')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="city" class="form-label">@lang('client.city')</label>
                                        <input type="text" class="form-control" id="city" name="city"
                                            value="{{ old('city') }}" placeholder="@lang('client.city')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCity')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="state" class="form-label">@lang('client.state')</label>
                                        <input type="text" class="form-control" id="state" name="state"
                                            value="{{ old('state') }}" placeholder="@lang('client.state')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterState')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="postal_code" class="form-label">@lang('client.postalCode')</label>
                                        <input type="text" class="form-control" id="postal_code" name="[postal_code"
                                            value="{{ old('postal_code') }}" placeholder="@lang('client.postalCode')">
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterPostalCode')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="input-placeholder" class="form-label">@lang('client.addressPhone')</label>
                                        <input type="text" class="form-control" id="address_phone"
                                            name="address_phone" value="{{ old('address_phone') }}"
                                            placeholder="@lang('client.addressPhone')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterValidAddressPhone')
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                                        <p class="mb-2 text-muted">@lang('client.is_active')</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_active"
                                                id="Radio-md" value="1" checked>
                                            <label class="form-check-label" for="Radio-md">
                                                @lang('client.yes')
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_active"
                                                id="Radio-md" value="0">
                                            <label class="form-check-label" for="Radio-md">
                                                @lang('client.no')
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                                        <p class="mb-2 text-muted">@lang('client.isDefault')</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_defult"
                                                id="Radio-md" value="1" checked>
                                            <label class="form-check-label" for="Radio-md">
                                                @lang('client.yes')
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_defult"
                                                id="Radio-md" value="0">
                                            <label class="form-check-label" for="Radio-md">
                                                @lang('client.no')
                                            </label>
                                        </div>
                                    </div>
                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary " id="input-submit"
                                                value="@lang('client.save')">
                                        </div>
                                    </center>
                                </div>
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
@endsection
