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
        <h4 class="fw-medium mb-0">@lang('country.Countries')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('country.Countries')</li>
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
                        <div class="card-header"
                            style="
                        display: flex;
                        justify-content: space-between;">
                            <div class="card-title">
                                @lang('country.Countries')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create countries', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('country.Add')
                                </button>
                            @endif
                            <!-- Add country Modal -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('country.store') }}" id="country-form" method="POST"
                                            class="needs-validation" novalidate enctype="multipart/form-data">
                                            @csrf
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
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('country.Add')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <input type="text" class="form-control" name="type" value="0"
                                                        hidden>
                                                    <!-- Arabic Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('country.ArabicName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('country.ArabicName')" value="{{ old('name_ar') }}"
                                                            name="name_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>

                                                    <!-- English Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('country.EnglishName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('country.EnglishName')" name="name_en"
                                                            value="{{ old('name_en') }}" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="add-currency-ar"
                                                            class="form-label">@lang('country.ArabicCurrency')</label>
                                                        <input type="text" id="add-currency-ar"
                                                            placeholder="@lang('country.ArabicCurrency')" class="form-control"
                                                            name="currency_ar" value="{{ old('currency_ar') }}" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.ArabicCurrency')</div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="add-currency-en"
                                                            class="form-label">@lang('country.EnglishCurrency')</label>
                                                        <input type="text" id="add-currency-en" class="form-control"
                                                            name="currency_en" value="{{ old('currency_en') }}" required
                                                            placeholder="@lang('country.EnglishCurrency')">
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnglishCurrency')</div>
                                                    </div>
                                                    <!--  Code Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="add-code"
                                                            class="form-label">@lang('country.code')</label>
                                                        <input type="text" id="add-code" class="form-control"
                                                            name="code" value="{{ old('code') }}" required
                                                            placeholder="@lang('country.code')">
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterCode')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="add-currency-code"
                                                            class="form-label">@lang('country.CurrencyCode')</label>
                                                        <input type="text" id="add-currency-code" class="form-control"
                                                            name="currency_code" value="{{ old('currency_code') }}"
                                                            required placeholder="@lang('country.CurrencyCode')">
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.CurrencyCode')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="add-Currency_symbole"
                                                            class="form-label">@lang('country.CurrencySymbole')</label>
                                                        <input type="text" id="add-Currency_symbole"
                                                            class="form-control" name="CurrencySymbole"
                                                            value="{{ old('CurrencySymbole') }}" required
                                                            placeholder="@lang('country.CurrencySymbole')">
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.CurrencySymbole')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="add-phone-code"
                                                            class="form-label">@lang('country.phonecode')</label>
                                                        <input type="text" id="add-phone-code" class="form-control"
                                                            name="phone_code" required value="{{ old('phone_code') }}"
                                                            placeholder="@lang('country.phonecode')">
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.phonecode')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="add-length"
                                                            class="form-label">@lang('country.length')</label>
                                                        <input type="text" id="add-length" class="form-control"
                                                            name="length" required value="{{ old('length') }}"
                                                            placeholder="@lang('country.length')">
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.length')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="add-length"
                                                            class="form-label">@lang('country.order')</label>
                                                        <input type="text" id="add-order" class="form-control"
                                                            name="order" required value="{{ old('order') }}"
                                                            placeholder="@lang('country.order')">
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.order')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label class="form-label">@lang('country.shown')</label>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="active_show" id="add-shown-yes" value="1"
                                                                >
                                                            <label class="form-check-label" for="add-shown-yes">
                                                                @lang('validation.Shown')
                                                            </label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="active_show" id="add-shown-no" value="0">
                                                            <label class="form-check-label" for="add-shown-no">
                                                                @lang('validation.noShown')
                                                            </label>
                                                        </div>

                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.order')</div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="add-flag"
                                                            class="form-label">@lang('country.flag')</label>
                                                        <input type="file" id="add-flag" class="form-control"
                                                            name="flag" required placeholder="@lang('country.flag')">
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.flag')</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">@lang('modal.close')</button>
                                                <button type="submit"
                                                    class="btn btn-outline-primary">@lang('modal.save')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Edit country Modal -->
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-country-form" action="" method="POST"
                                            class="needs-validation" novalidate enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
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
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="editModalLabel">@lang('country.Edit')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <!-- Arabic Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-ar"
                                                            class="form-label">@lang('country.ArabicName')</label>
                                                        <input type="text" id="edit-name-ar" class="form-control"
                                                            name="name_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>

                                                    <!-- English Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-en"
                                                            class="form-label">@lang('country.EnglishName')</label>
                                                        <input type="text" id="edit-name-en" class="form-control"
                                                            name="name_en" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-currency-ar"
                                                            class="form-label">@lang('country.ArabicCurrency')</label>
                                                        <input type="text" id="edit-currency-ar" class="form-control"
                                                            name="currency_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.ArabicCurrency')</div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-currency-en"
                                                            class="form-label">@lang('country.EnglishCurrency')</label>
                                                        <input type="text" id="edit-currency-en" class="form-control"
                                                            name="currency_en" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnglishCurrency')</div>
                                                    </div>
                                                    <!--  Code Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-code"
                                                            class="form-label">@lang('country.code')</label>
                                                        <input type="text" id="edit-code" class="form-control"
                                                            name="code" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterCode')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-currency-code"
                                                            class="form-label">@lang('country.CurrencyCode')</label>
                                                        <input type="text" id="edit-currency-code"
                                                            class="form-control" name="currency_code" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.CurrencyCode')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-Currency-symbole"
                                                            class="form-label">@lang('country.CurrencySymbole')</label>
                                                        <input type="text" id="edit-Currency-symbole"
                                                            class="form-control" name="CurrencySymbole" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.CurrencySymbole')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-phone-code"
                                                            class="form-label">@lang('country.phonecode')</label>
                                                        <input type="text" id="edit-phone-code" class="form-control"
                                                            name="phone_code" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.phonecode')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-length"
                                                            class="form-label">@lang('country.length')</label>
                                                        <input type="text" id="edit-length" class="form-control"
                                                            name="length" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.length')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-length"
                                                            class="form-label">@lang('country.order')</label>
                                                        <input type="text" id="edit-order" class="form-control"
                                                            name="order" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.order')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label class="form-label">@lang('country.shown')</label>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="active_show" id="edit-shown-yes" value="1"
                                                                checked>
                                                            <label class="form-check-label" for="edit-shown-yes">
                                                                @lang('validation.Shown')
                                                            </label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="active_show" id="edit-shown-no" value="0">
                                                            <label class="form-check-label" for="edit-shown-no">
                                                                @lang('validation.noShown')
                                                            </label>
                                                        </div>

                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.order')</div>
                                                    </div>

                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="edit-flag"
                                                            class="form-label">@lang('country.flag')</label>
                                                        <input type="file" id="edit-flag" class="form-control"
                                                            name="flag" placeholder="@lang('country.flag')">
                                                        <img id="edit-flag-image" src="" alt="Current Flag"
                                                            width="50px" style="display: none;">
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.flag')</div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">@lang('modal.close')</button>
                                                <button type="submit"
                                                    class="btn btn-outline-primary">@lang('modal.save')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Show country Modal -->
                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('country.Show')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <!-- Arabic Name -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.ArabicName')</label>
                                                    <p id="show-name-ar" class="form-control-static"></p>
                                                </div>

                                                <!-- English Name -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.EnglishName')</label>
                                                    <p id="show-name-en" class="form-control-static"></p>
                                                </div>

                                                <!-- Arabic Currency -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.ArabicCurrency')</label>
                                                    <p id="show-currency-ar" class="form-control-static"></p>
                                                </div>

                                                <!-- English Currency -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.EnglishCurrency')</label>
                                                    <p id="show-currency-en" class="form-control-static"></p>
                                                </div>

                                                <!-- Code -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.code')</label>
                                                    <p id="show-code" class="form-control-static"></p>
                                                </div>

                                                <!-- Currency Code -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.CurrencyCode')</label>
                                                    <p id="show-currency-code" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.CurrencySymbole')</label>
                                                    <p id="show-Currency-symbole" class="form-control-static"></p>
                                                </div>
                                                <!-- Phone Code -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.phonecode')</label>
                                                    <p id="show-phone-code" class="form-control-static"></p>
                                                </div>

                                                <!-- Length -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.length')</label>
                                                    <p id="show-length" class="form-control-static"></p>
                                                </div>
                                                <!-- order -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.order')</label>
                                                    <p id="show-order" class="form-control-static"></p>
                                                </div>
                                                <!-- shown -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.shown')</label>
                                                    <p id="show-shown" class="form-control-static"></p>
                                                </div>
                                                <!-- Flag -->
                                                <div class="col-xl-6">
                                                    <label class="form-label">@lang('country.flag')</label>
                                                    <img id="show-flag" src="" alt="Country Flag"
                                                        class="img-fluid">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">@lang('modal.close')</button>
                                        </div>
                                    </div>
                                </div>
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
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('country.ArabicName')</th>
                                        <th scope="col">@lang('country.EnglishName')</th>
                                        <th scope="col">@lang('country.code')</th>
                                        <th scope="col">@lang('country.ArabicCurrency')</th>
                                        <th scope="col">@lang('country.EnglishCurrency')</th>
                                        <th scope="col">@lang('country.CurrencyCode')</th>
                                        <th scope="col">@lang('country.CurrencySymbole')</th>

                                        <th scope="col">@lang('country.flag')</th>
                                        <th scope="col">@lang('country.cities')</th>
                                        <th scope="col">@lang('country.order')</th>
                                        <th scope="col">@lang('country.viewed')</th>

                                        <th scope="col">@lang('country.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($countries as $country)
                                        {{-- {{ dd($country) }} --}}
                                        <tr>
                                            <td>{{ $country->name_ar }}</td>
                                            <td>{{ $country->name_en }}</td>
                                            <td>{{ $country->code }}</td>
                                            <td>{{ $country->currency_ar }}</td>
                                            <td>{{ $country->currency_en }}</td>
                                            <td>{{ $country->currency_code }}</td>
                                            <td>{{ $country->currency_symbol }}</td>

                                            <td> <img src="{{ $country->flag }}" width="50px"> </td>
                                            <td>{{ $country->cities->count() }}</td>
                                            <td>{{ $country->order == 0 ? __('country.withoutOrder') : $country->order }}
                                            </td>
                                            <td>
                                                @if ($country->active_show == 1)
                                                    <span class="badge bg-success">@lang('country.active')</span>
                                                @else
                                                    <span class="badge bg-danger">@lang('country.inactive')</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view countries', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-info-light btn-wave show-country-btn"
                                                        data-id="{{ $country->id }}"
                                                        data-name-ar="{{ $country->name_ar }}"
                                                        data-name-en="{{ $country->name_en }}"
                                                        data-code="{{ $country->code }}"
                                                        data-phone-code="{{ $country->phone_code }}"
                                                        data-length="{{ $country->length }}"
                                                        data-flag="{{ $country->flag }}"
                                                        data-currency-ar="{{ $country->currency_ar }}"
                                                        data-currency-en="{{ $country->currency_en }}"
                                                        data-currency-code="{{ $country->currency_code }}"
                                                        data-currency-symbol="{{ $country->currency_symbol }}"
                                                        data-order="{{ $country->order }}"
                                                        data-shown="{{ $country->active_show }}" data-bs-toggle="modal"
                                                        data-bs-target="#showModal">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update countries', 'admin'))
                                                    <!-- Edit Button -->
                                                    <button type="button"
                                                        class="btn btn-orange-light btn-wave edit-country-btn"
                                                        data-id="{{ $country->id }}"
                                                        data-name-ar="{{ $country->name_ar }}"
                                                        data-name-en="{{ $country->name_en }}"
                                                        data-code="{{ $country->code }}"
                                                        data-currency-ar="{{ $country->currency_ar }}"
                                                        data-currency-en="{{ $country->currency_en }}"
                                                        data-currency-code="{{ $country->currency_code }}"
                                                        data-currency-symbol="{{ $country->currency_symbol }}"
                                                        data-phone-code="{{ $country->phone_code }}"
                                                        data-shown="{{ $country->active_show }}"
                                                        data-length="{{ $country->length }}"
                                                        data-flag="{{ $country->flag }}"
                                                        data-order="{{ $country->order }}"
                                                        data-route="{{ route('country.update', ':id') }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('delete countries', 'admin'))
                                                    <!-- Delete Button -->
                                                    <button type="button" onclick="delete_item('{{ $country->id }}')"
                                                        class="btn btn-danger-light btn-wave">
                                                        @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('view cities', 'admin'))
                                                    <a href="{{ route('city.list', ['id' => $country->id]) }}"
                                                        class="btn btn-success-light">
                                                        @lang('country.cities') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')
    <script>
        @if ($errors->any())
            // If validation errors exist, open the modal automatically
            $(document).ready(function() {
                $('#exampleModal').modal('show');
            });
        @endif
    </script>
    <script>
        // Get the file input and image elements
        const flagInput = document.getElementById('edit-flag');
        const flagImage = document.getElementById('edit-flag-image');

        // Listen for changes on the file input
        flagInput.addEventListener('change', function(event) {
            const file = event.target.files[0];

            // If a file is selected
            if (file) {
                const reader = new FileReader();

                // When the file is read, update the image source
                reader.onload = function(e) {
                    flagImage.src = e.target.result; // Set the image src to the selected file
                    flagImage.style.display = 'block'; // Ensure the image is visible
                };

                // Read the selected file as a Data URL (base64 string)
                reader.readAsDataURL(file);
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-country-btn');
            const editForm = document.getElementById('edit-country-form');
            const nameArInput = document.getElementById('edit-name-ar');
            const nameEnInput = document.getElementById('edit-name-en');
            const codeInput = document.getElementById('edit-code');
            const currencyEnInput = document.getElementById('edit-currency-en');
            const currencyCodeInput = document.getElementById('edit-currency-code');
            const currencySymboleInput = document.getElementById('edit-Currency-symbole');

            const currencyArInput = document.getElementById('edit-currency-ar');
            const phoneCodeInput = document.getElementById('edit-phone-code');
            const lengthInput = document.getElementById('edit-length');
            const flagInput = document.getElementById('edit-flag');
            const orderInput = document.getElementById('edit-order');
            const shownYesInput = document.getElementById('edit-shown-yes');
            const shownNoInput = document.getElementById('edit-shown-no');

            const flagImage = document.getElementById('edit-flag-image');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get country details from data attributes
                    const countryId = this.getAttribute('data-id');
                    const nameAr = this.getAttribute('data-name-ar');
                    const nameEn = this.getAttribute('data-name-en');
                    const code = this.getAttribute('data-code');
                    const currencyEn = this.getAttribute('data-currency-en');
                    const currencyAr = this.getAttribute('data-currency-ar');
                    const currencyCode = this.getAttribute('data-currency-code');
                    const currencySymbole = this.getAttribute('data-currency-symbol');

                    const phoneCode = this.getAttribute('data-phone-code');
                    const length = this.getAttribute('data-length');
                    const order = this.getAttribute('data-order');
                    const shown = this.getAttribute('data-shown');

                    const flag = this.getAttribute('data-flag');
                    const routeTemplate = this.getAttribute('data-route');

                    // Set form action URL dynamically
                    const updateRoute = routeTemplate.replace(':id', countryId);
                    editForm.action = updateRoute;
                    orderInput.value = order;

                    if (shown === '1') {
                        shownYesInput.checked = true;
                    } else {
                        shownNoInput.checked = true;
                    }

                    // Populate the modal fields
                    nameArInput.value = nameAr;
                    nameEnInput.value = nameEn;
                    codeInput.value = code;
                    currencyArInput.value = currencyAr;
                    currencyEnInput.value = currencyEn;
                    currencyCodeInput.value = currencyCode;
                    currencySymboleInput.value = currencySymbole;
                    phoneCodeInput.value = phoneCode;
                    lengthInput.value = length;
                    flagInput.value = ''; // Clear the file input

                    // Display the current flag image if available
                    if (flag) {
                        flagImage.src = flag; // Set the image source to the flag URL
                        flagImage.style.display = 'block'; // Show the image
                    } else {
                        flagImage.style.display = 'none'; // Hide the image if no flag
                    }
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const showButtons = document.querySelectorAll('.show-country-btn');

            // Elements where we will display country details in the modal
            const nameArDisplay = document.getElementById('show-name-ar');
            const nameEnDisplay = document.getElementById('show-name-en');
            const currencyCodeDisplay = document.getElementById('show-currency-code');
            const currencyEnDisplay = document.getElementById('show-currency-en');
            const currencyArDisplay = document.getElementById('show-currency-ar');
            const phoneCodeDisplay = document.getElementById('show-phone-code');
            const lengthDisplay = document.getElementById('show-length');
            const flagDisplay = document.getElementById('show-flag');
            const CodeDisplay = document.getElementById('show-code');
            const OrderDisplay = document.getElementById('show-order');
            const ShownDisplay = document.getElementById('show-shown');

            const SymboleDisplay = document.getElementById('show-Currency-symbole');

            showButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const nameAr = this.getAttribute('data-name-ar');
                    const nameEn = this.getAttribute('data-name-en');
                    const Code = this.getAttribute('data-code');
                    const currencyCode = this.getAttribute('data-currency-code');
                    const currencySymbole = this.getAttribute('data-currency-symbol');

                    const currencyEn = this.getAttribute('data-currency-en');
                    const currencyAr = this.getAttribute('data-currency-ar');
                    const phoneCode = this.getAttribute('data-phone-code');
                    const Order = this.getAttribute('data-order');
                    const Shown = this.getAttribute('data-shown');

                    const length = this.getAttribute('data-length');
                    const flag = this.getAttribute('data-flag');

                    // Set the content for each display element
                    nameArDisplay.textContent = nameAr;
                    nameEnDisplay.textContent = nameEn;
                    CodeDisplay.textContent = Code;
                    currencyCodeDisplay.textContent = currencyCode;
                    SymboleDisplay.textContent = currencySymbole;
                    OrderDisplay.textContent = Order;
                    ShownDisplay.textContent = Shown;

                    currencyEnDisplay.textContent = currencyEn;
                    currencyArDisplay.textContent = currencyAr;
                    phoneCodeDisplay.textContent = phoneCode;
                    lengthDisplay.textContent = length;
                    if (flag && flag.trim() !== "") {
                        flagDisplay.style.display = "block";
                        flagDisplay.src = flag;
                    } else {
                        flagDisplay.style.display = "none";
                    }

                });
            });
        });


        function delete_item(id) {
            Swal.fire({
                title: '{{ __('country.warning_titleper') }}',
                text: '{{ __('country.delete_confirmationper') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('country.confirm_delete') }}',
                cancelButtonText: '{{ __('country.cancel') }}',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('country.delete', ':id') }}'.replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            if (response.status) {
                                // Success - Country deleted
                                Swal.fire({
                                    icon: 'success',
                                    title: '{{ __('country.delete_success') }}',
                                    showConfirmButton: false,
                                    timer: 1500
                                });

                                // Optionally refresh or update the UI
                                location.reload();
                            } else {
                                // Handle cases where the country cannot be deleted
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('country.delete_error') }}',
                                    text: response.message,
                                    showConfirmButton: true
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('country.delete_error') }}',
                                text: xhr.responseJSON?.error,
                                showConfirmButton: true
                            });
                        }
                    });
                }
            });
        }
    </script>
    <script>
        // Add event listener for the form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            // Get all input fields
            const inputs = document.querySelectorAll(
                'input[type="text"], input[type="file"], input[type="password"]');

            // Loop through each input field to trim spaces
            inputs.forEach(input => {
                if (input.value) {
                    input.value = input.value.trim(); // Trim spaces from the value
                }
            });
        });
        $('#country-form').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let order = $('#order').val();
            let token = form.find('input[name="_token"]').val();

            $.ajax({
                url: "{{ route('countries.check-order') }}",
                type: 'POST',
                data: {
                    _token: token,
                    order: order
                },
                success: function(res) {
                    if (res.exists) {
                        if (confirm("This order is already used by " + res.name +
                                ". Do you want to override it?")) {
                            // Proceed and submit with override
                            form.off('submit').submit();
                        }
                    } else {
                        // Safe to submit
                        form.off('submit').submit();
                    }
                }
            });
        });
    </script>
@endsection
