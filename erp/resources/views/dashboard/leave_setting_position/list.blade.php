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
    <h4 class="fw-medium mb-0">@lang('leave_setting_position.LeaveSettingPosition')</h4>
    <div class="ms-sm-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                <li class="breadcrumb-item"><a href="#">@lang('leave_setting_position.Leaves')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('leave_setting_position.LeaveSettingPosition')</li>
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
                            @lang('leave_setting_position.LeaveSettingPosition')</div>
                        @if (auth('admin')->user()->hasPermissionTo('create leave_settings', 'admin'))
                        <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                            data-bs-target="#exampleModal">
                            <i class="fe fe-plus label-btn-icon me-2"></i>
                            @lang('leave_setting_position.AddLeaveSettingPosition')
                        </button>
                        @endif
                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content" style="width:750px !important">
                                    <form action="{{ route('leave-setting-positions.store') }}" enctype="multipart/form-data" method="POST"
                                        class="needs-validation" novalidate>
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
                                            <h6 class="modal-title" id="exampleModalLabel1">@lang('leave_setting_position.AddLeaveSettingPosition')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="country_id"
                                                        class="form-label">@lang('leave_setting_position.Country')</label>
                                                    <select class="form-select country_id" id="country_id" name="country_id"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.ChooseCountry')
                                                        </option>
                                                        @foreach ($countries as $country)
                                                        <option value="{{ $country->id }}">
                                                            {{ $country->name_site }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-8 col-md-8 col-sm-12">
                                                    <label for="branch"
                                                        class="form-label">@lang('leave_setting_position.choose_leave')</label>
                                                    <select class="form-select leave_setting_id" id="leave_setting_id"
                                                        name="leave_setting_id" required>
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterPartition')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="position_id"
                                                        class="form-label">@lang('leave_setting_position.position')</label>
                                                    <select class="form-select position_id" id="position_id" name="position_id"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.choose_position')
                                                        </option>
                                                        @foreach ($positions as $position)
                                                        <option value="{{ $position->id }}">
                                                            {{ $position->name_site }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-8 col-md-8 col-sm-12">
                                                    <label for="input-placeholder"
                                                        class="form-label">@lang('leave_setting_position.day_count')</label>
                                                    <input type="number" min="1" class="form-control day_count"
                                                        placeholder="@lang('leave_setting_position.Minmum')" name="day_count" id="day_count" required>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterMinmum')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="leave_pattern"
                                                        class="form-label">@lang('leave_setting_position.leave_pattern')</label>
                                                    <select class="form-select leave_pattern" id="leave_pattern" name="leave_pattern"
                                                        required>
                                                        <option value="consecutive">@lang('leave_setting_position.consecutive')</option>
                                                        <option value="split">@lang('leave_setting_position.split')</option>
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-8 col-md-8 col-sm-12 split_max_div" id="split_max_div" style="display: none;">
                                                    <label for="split_max"
                                                        class="form-label">@lang('leave_setting_position.split_max')</label>
                                                    <input type="number" min="0" class="form-control" value="0"
                                                        placeholder="@lang('leave_setting_position.EnglishName')" id="split_max" name="split_max">
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterMaxmum')
                                                    </div>
                                                </div>

                                                <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12"></div>

                                                <div class="col-xl-3 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('leave_setting_position.hr_approve')</label>
                                                    <div class="form-check" id="hr_approve_div">
                                                        <input class="form-check-input" type="radio" name="hr_approve" id="hr_approve_yes" value="yes" checked disabled required>
                                                        <label class="form-check-label" for="hr_approve_yes">
                                                            @lang('leave_setting_position.yes')
                                                        </label>
                                                    </div>
                                                    <div class="form-check" id="no_hr_approve_div">
                                                        <input class="form-check-input" type="radio" name="hr_approve" id="hr_approve_no" value="no" disabled>
                                                        <label class="form-check-label" for="hr_approve_no">
                                                            @lang('leave_setting_position.no')
                                                        </label>
                                                    </div>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterEnglishName')
                                                    </div>
                                                </div>

                                                <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="higher_position_approve"
                                                        class="form-label">@lang('leave_setting_position.higher_position_approve')</label>
                                                    <select class="form-select higher_position_approve" id="higher_position_approve" multiple name="higher_position_approve[]"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.higher_position_approve')
                                                        </option>
                                                        @foreach ($positions as $position)
                                                        <option value="{{ $position->id }}">
                                                            {{ $position->name_site }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('leave_setting_position.higher_position_setting')</label>
                                                    <div class="form-check" id="one_higher_position_setting_div">
                                                        <input class="form-check-input" type="radio" name="higher_position_setting" id="higher_position_setting_one" value="one" required>
                                                        <label class="form-check-label" for="higher_position_setting_one">
                                                            @lang('leave_setting_position.one')
                                                        </label>
                                                    </div>
                                                    <div class="form-check" id="all_higher_position_setting_div">
                                                        <input class="form-check-input" type="radio" name="higher_position_setting" id="higher_position_setting_all" value="all" checked>
                                                        <label class="form-check-label" for="higher_position_setting_all">
                                                            @lang('leave_setting_position.all')
                                                        </label>
                                                    </div>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterEnglishName')
                                                    </div>
                                                </div>

                                                <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="roles_assign"
                                                        class="form-label">@lang('leave_setting_position.roles_assign')</label>
                                                    <select class="form-select roles_assign" id="roles_assign" multiple name="roles_assign[]"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.roles_assign')
                                                        </option>
                                                        @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}">
                                                            {{ $role->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
                                                </div>

                                                <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="roles_view"
                                                        class="form-label">@lang('leave_setting_position.roles_view')</label>
                                                    <select class="form-select roles_view" id="roles_view" multiple name="roles_view[]"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.roles_view')
                                                        </option>
                                                        @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}">
                                                            {{ $role->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
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

                        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content" style="width:750px !important">
                                    <form id="edit-floor-form" action="" enctype="multipart/form-data" method="POST"
                                        class="needs-validation" novalidate>
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
                                            <h6 class="modal-title" id="editModalLabel">@lang('leave_setting_position.EditLeaveSettingPosition')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="country_id"
                                                        class="form-label">@lang('leave_setting_position.Country')</label>
                                                    <select class="form-select country_id" id="edit_country_id" name="country_id"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.ChooseCountry')
                                                        </option>
                                                        @foreach ($countries as $country)
                                                        <option value="{{ $country->id }}">
                                                            {{ $country->name_site }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-8 col-md-8 col-sm-12">
                                                    <label for="branch"
                                                        class="form-label">@lang('leave_setting_position.ChooseCountry')</label>
                                                    <select class="form-select leave_setting_id" id="edit_leave_setting_id"
                                                        name="leave_setting_id" required>
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterPartition')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="position_id"
                                                        class="form-label">@lang('leave_setting_position.position')</label>
                                                    <select class="form-select position_id" id="edit_position_id" name="position_id"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.choose_position')
                                                        </option>
                                                        @foreach ($positions as $position)
                                                        <option value="{{ $position->id }}">
                                                            {{ $position->name_site }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterPosition')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-8 col-md-8 col-sm-12">
                                                    <label for="input-placeholder"
                                                        class="form-label">@lang('leave_setting_position.day_count')</label>
                                                    <input type="number" min="1" class="form-control day_count"
                                                        placeholder="@lang('leave_setting_position.Minmum')" name="day_count" id="edit_day_count" required>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterMinmum')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="leave_pattern"
                                                        class="form-label">@lang('leave_setting_position.leave_pattern')</label>
                                                    <select class="form-select leave_pattern" id="edit_leave_pattern" name="leave_pattern"
                                                        required>
                                                        <option value="consecutive">@lang('leave_setting_position.consecutive')</option>
                                                        <option value="split">@lang('leave_setting_position.split')</option>
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
                                                </div>

                                                <div class="col-xl-4 col-lg-8 col-md-8 col-sm-12 split_max_div" id="split_max_div" style="display: none;">
                                                    <label for="split_max"
                                                        class="form-label">@lang('leave_setting_position.split_max')</label>
                                                    <input type="number" min="0" class="form-control" value="0"
                                                        placeholder="@lang('leave_setting_position.EnglishName')" id="edit_split_max" name="split_max">
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterMaxmum')
                                                    </div>
                                                </div>

                                                <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12"></div>

                                                <div class="col-xl-3 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('leave_setting_position.hr_approve')</label>
                                                    <div class="form-check" id="edit_hr_approve_div">
                                                        <input class="form-check-input" type="radio" name="hr_approve" id="edit_hr_approve_yes" value="yes" checked disabled required>
                                                        <label class="form-check-label" for="hr_approve_yes">
                                                            @lang('leave_setting_position.yes')
                                                        </label>
                                                    </div>
                                                    <div class="form-check" id="no_hr_approve_div">
                                                        <input class="form-check-input" type="radio" name="hr_approve" id="edit_hr_approve_no" value="no" disabled>
                                                        <label class="form-check-label" for="hr_approve_no">
                                                            @lang('leave_setting_position.no')
                                                        </label>
                                                    </div>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterEnglishName')
                                                    </div>
                                                </div>

                                                <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="higher_position_approve"
                                                        class="form-label">@lang('leave_setting_position.higher_position_approve')</label>
                                                    <select class="form-select higher_position_approve" id="edit_higher_position_approve" multiple name="higher_position_approve[]"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.higher_position_approve')
                                                        </option>
                                                        @foreach ($positions as $position)
                                                        <option value="{{ $position->id }}">
                                                            {{ $position->name_site }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
                                                </div>

                                                <div class="col-xl-3 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('leave_setting_position.higher_position_setting')</label>
                                                    <div class="form-check" id="edit_one_higher_position_setting_div">
                                                        <input class="form-check-input" type="radio" name="higher_position_setting" id="edit_higher_position_setting_one" value="one" required>
                                                        <label class="form-check-label" for="higher_position_setting_one">
                                                            @lang('leave_setting_position.one')
                                                        </label>
                                                    </div>
                                                    <div class="form-check" id="edit_all_higher_position_setting_div">
                                                        <input class="form-check-input" type="radio" name="higher_position_setting" id="edit_higher_position_setting_all" value="all" checked>
                                                        <label class="form-check-label" for="higher_position_setting_all">
                                                            @lang('leave_setting_position.all')
                                                        </label>
                                                    </div>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterEnglishName')
                                                    </div>
                                                </div>

                                                <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="roles_assign"
                                                        class="form-label">@lang('leave_setting_position.roles_assign')</label>
                                                    <select class="form-select roles_assign" id="edit_roles_assign" multiple name="roles_assign[]"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.roles_assign')
                                                        </option>
                                                        @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}">
                                                            {{ $role->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
                                                </div>

                                                <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                    <label for="roles_view"
                                                        class="form-label">@lang('leave_setting_position.roles_view')</label>
                                                    <select class="form-select roles_view" id="edit_roles_view" multiple name="roles_view[]"
                                                        required>
                                                        <option value="" disabled selected>@lang('leave_setting_position.roles_view')
                                                        </option>
                                                        @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}">
                                                            {{ $role->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">
                                                        @lang('validation.Correct')
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        @lang('validation.EnterCountry')
                                                    </div>
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

                        <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content" style="width:750px !important">
                                    <div class="modal-header">
                                        <h6 class="modal-title" id="showModalLabel">@lang('leave_setting_position.ShowFloor')</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row gy-4">
                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.Country')</label>
                                                <p id="show_country_id" class="form-control-static"></p>
                                            </div>
                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.leave')</label>
                                                <p id="show_leave_setting_id" class="form-control-static"></p>
                                            </div>
                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.choose_position')</label>
                                                <p id="show_position_id" class="form-control-static"></p>
                                            </div>
                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.day_count')</label>
                                                <p id="show_day_count" class="form-control-static"></p>
                                            </div>
                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.leave_pattern')</label>
                                                <p id="show_leave_pattern" class="form-control-static"></p>
                                            </div>
                                            <div class="col-xl-4 div_show_split_max" id="div_show_split_max" style="display:none">
                                                <label class="form-label">@lang('leave_setting_position.split_max')</label>
                                                <p id="show_split_max" class="form-control-static"></p>
                                            </div>
                                            <div class="col-xl-12"></div>
                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.hr_approve')</label>
                                                <p id="show_hr_approve" class="form-control-static"></p>
                                            </div>
                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.higher_position_approve')</label>
                                                <p id="show_higher_position_approve" class="form-control-static"></p>
                                            </div>
                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.higher_position_setting')</label>
                                                <p id="show_higher_position_setting" class="form-control-static"></p>
                                            </div>

                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.roles_assign')</label>
                                                <p id="show_roles_assign" class="form-control-static"></p>
                                            </div>
                                            <div class="col-xl-4">
                                                <label class="form-label">@lang('leave_setting_position.roles_view')</label>
                                                <p id="show_roles_view" class="form-control-static"></p>
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
                        <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">@lang('leave_setting_position.ID')</th>
                                    <th scope="col">@lang('leave_setting_position.CountryName')</th>
                                    <th scope="col">@lang('leave_setting_position.leave')</th>
                                    <th scope="col">@lang('leave_setting_position.position')</th>
                                    <th scope="col">@lang('category.Actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($LeaveSetting as $k_leave_setting => $leave_setting)
                                <tr>
                                    <td>{{ ++$k_leave_setting }}</td>
                                    <td>{{ $leave_setting->leaveSettings->leaveTypes ? $leave_setting->leaveSettings->countries->name_site : ""}}</td>
                                    <td>{{ $leave_setting->leaveSettings->leaveTypes ? $leave_setting->leaveSettings->leaveTypes->name_site : ""}}</td>
                                    <td>{{ $leave_setting->positions->name_site }}</td>
                                    <td>
                                        @if (auth('admin')->user()->hasPermissionTo('view leave_settings', 'admin'))

                                        <!-- Show Button -->
                                        <a href="javascript:void(0);"
                                            class="btn btn-info-light btn-wave show-floor-btn"
                                            data-id="{{ $leave_setting->id }}" data-bs-toggle="modal"
                                            data-bs-target="#showModal">
                                            @lang('category.show') <i class="ri-eye-line"></i>
                                        </a>
                                        @endif

                                        @if (auth('admin')->user()->hasPermissionTo('update leave_settings', 'admin'))

                                        <!-- Edit Button -->
                                        <button type="button"
                                            class="btn btn-orange-light btn-wave edit-floor-btn"
                                            data-id="{{ $leave_setting->id }}" data-bs-toggle="modal"
                                            data-bs-target="#editModal">
                                            @lang('category.edit') <i class="ri-edit-line"></i>
                                        </button>
                                        @endif
                                        @if (auth('admin')->user()->hasPermissionTo('delete leave_settings', 'admin'))

                                        <form class="d-inline" id="delete-form-{{ $leave_setting->id }}"
                                            action="{{ route('leave-setting-positions.delete', $leave_setting->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="delete_item('{{ $leave_setting->id }}')"
                                                class="btn btn-danger-light btn-wave">
                                                @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
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
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- INTERNAL DATADABLES JS -->
@vite('resources/assets/js/datatables.js')
@vite('resources/assets/js/validation.js')
@vite('resources/assets/js/choices.js')
@vite('resources/assets/js/modal.js')
@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.edit-floor-btn').on('click', function() {
            var LeaveSettingPositionId = this.getAttribute('data-id');
            var get_url = "{{ route('leave-setting-positions.show', 'id') }}";
            var edit_url = "{{ route('leave-setting-positions.update', 'id') }}";
            get_url = get_url.replace('id', LeaveSettingPositionId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    console.log(data);
                    
                    // Populate the modal with the data
                    $('#edit_day_count').val(data.day_count);
                    $('#edit_hr_approve').val(data.hr_approve);
                    $('#edit_country_id').val(data.leave_settings.country_id);
                    $('#edit_position_id').val(data.positions.id);

                    $('#edit_leave_pattern').val(data.leave_pattern);
                    if(data.leave_pattern == "split"){
                        $('.split_max_div').css('display', 'block');
                    }else{
                        $('.split_max_div').css('display', 'none');
                    }
                    $('#edit_split_max').val(data.split_max);

                    let higher_position_approve_ids = data.higher_position_approve;
                    if (typeof higher_position_approve_ids === 'string') {
                        try {
                            higher_position_approve_ids = JSON.parse(higher_position_approve_ids);
                        } catch (e) {
                            higher_position_approve_ids = [];
                        }
                    }
                    $('#edit_higher_position_approve').val(higher_position_approve_ids).trigger('change');

                    let roles_assign_ids = data.roles_assign;
                    if (typeof roles_assign_ids === 'string') {
                        try {
                            roles_assign_ids = JSON.parse(roles_assign_ids);
                        } catch (e) {
                            roles_assign_ids = [];
                        }
                    }
                    $('#edit_roles_assign').val(roles_assign_ids).trigger('change');

                    let roles_view_ids = data.roles_view;
                    if (typeof roles_view_ids === 'string') {
                        try {
                            roles_view_ids = JSON.parse(roles_view_ids);
                        } catch (e) {
                            roles_view_ids = [];
                        }
                    }
                    $('#edit_roles_view').val(roles_view_ids).trigger('change');


                    $('input[name="higher_position_setting"]').prop('checked', false);
                    if (data.higher_position_setting == "one") {
                        $('#edit_higher_position_setting_one').prop('checked', true);
                    } else {
                        $('#edit_higher_position_setting_all').prop('checked', true);
                    }


                    var countryId = data.leave_settings.countries.id;
                    var get_country_url = "{{ route('leave-setting.show_data',['country_id','col_id']) }}";
                    get_country_url = get_country_url.replace('col_id', countryId);
                    $.ajax({
                        url: get_country_url,
                        type: 'GET',
                        success: function(leave_type_data) {
                            $('.leave_setting_id').html(leave_type_data);
                            $('#edit_leave_setting_id').val(data.leave_setting_id);
                        },
                        error: function(xhr, status, error) {
                            console.log('Error: ' + error);
                        }
                    });

                    edit_url = edit_url.replace('id', LeaveSettingPositionId);

                    $('#edit-floor-form').attr('action', edit_url);

                    // Show the modal
                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('.show-floor-btn').on('click', function() {
            var LeaveSettingPositionId = this.getAttribute('data-id');
            var get_url = "{{ route('leave-setting-positions.show', 'id') }}";
            get_url = get_url.replace('id', LeaveSettingPositionId);
            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    console.log(data);
                    // Populate the modal with the data
                    $('#show_day_count').text(data.day_count);
                    $('#show_hr_approve').text(data.hr_approve);
                    $('#show_country_id').text(data.leave_settings.countries.name_site);
                    $('#show_leave_setting_id').text(data.leave_settings.leave_types.name_site);
                    $('#show_position_id').text(data.positions.name_site);

                    const leavePatternTranslations = {
                        split: "{{ __('leave_setting_position.split') }}",
                        consecutive: "{{ __('leave_setting_position.consecutive') }}"
                    };
                    if(data.leave_pattern == "split"){
                        $('#show_leave_pattern').text(leavePatternTranslations['split']);
                        $('.div_show_split_max').css('display', 'block');
                    }else{
                        $('#show_leave_pattern').text(leavePatternTranslations['consecutive']);
                        $('.div_show_split_max').css('display', 'none');
                    }

                    const hr_approve_translations = {
                        yes: "{{ __('leave_setting_position.yes') }}",
                        no: "{{ __('leave_setting_position.no') }}"
                    };
                    if(data.hr_approve == "no"){
                        $('#show_hr_approve').text(hr_approve_translations['no']);
                    }else{
                        $('#show_hr_approve').text(hr_approve_translations['yes']);
                    }

                    const higher_position_setting_translations = {
                        all: "{{ __('leave_setting_position.all') }}",
                        one: "{{ __('leave_setting_position.one') }}"
                    };
                    if(data.higher_position_setting == "all"){
                        $('#show_higher_position_setting').text(higher_position_setting_translations['all']);
                    }else{
                        $('#show_higher_position_setting').text(higher_position_setting_translations['one']);
                    }
                    $('#show_split_max').text(data.split_max);
                    $('#show_higher_position_approve').text(data.higher_position_approve_name);
                    //$('#show_higher_position_setting').text(data.higher_position_setting);
                    $('#show_roles_assign').text(data.roles_assign_name);
                    $('#show_roles_view').text(data.roles_view_name);

                    // Show the modal
                    $('#showModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('.country_id').on('change', function() {
            var countryId = this.value;
            var get_url = "{{ route('leave-setting.show_data',['country_id','col_id']) }}";
            get_url = get_url.replace('col_id', countryId);

            // Fetch floor partitions based on the selected floor
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    $('.leave_setting_id').html(data);
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('.leave_setting_id').on('change', function() {
            var daycount = $(this).find(':selected').data('daycount');
            if (!isNaN(daycount)) {
                $('.day_count').val(daycount);
            } else {
                $('.day_count').val(0);
            }
        });

        $('.leave_pattern').on('change', function() {
            var leavePattern = this.value;
            if (leavePattern == "split") {
                $('.split_max_div').css('display', 'block');
            } else {
                $('.split_max_div').css('display', 'none');
            }
        });
    });

    function confirmDelete() {
        return confirm("@lang('validation.DeleteConfirm')");
    }

    function delete_item(id) {
        Swal.fire({
            title: 'تنبيه',
            text: 'هل انت متاكد من انك تريد ان تحذف هذا الفرع',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم, احذف',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('delete-form-' + id);
                form.submit();
            }
        });
    }
</script>
