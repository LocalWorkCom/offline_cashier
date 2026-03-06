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
        <h4 class="fw-medium mb-0">@lang('leave_setting.LeaveSetting')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="#">@lang('leave_setting.Leaves')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('leave_setting.LeaveSetting')</li>
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
                                @lang('leave_setting.LeaveSetting')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create leave_settings', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('leave_setting.AddLeaveSetting')
                                </button>
                            @endif
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content" style="width:750px !important">
                                        <form action="{{ route('leave-setting.store') }}" enctype="multipart/form-data" method="POST"
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
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('leave_setting.AddLeaveSetting')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="leave_type_id"
                                                            class="form-label">@lang('leave_setting.LeaveType')</label>
                                                        <select class="form-select" id="leave_type_id" name="leave_type_id"
                                                            required>
                                                            <option value="" disabled selected>@lang('leave_setting.ChooseLeaveType')
                                                            </option>
                                                            @foreach ($leaveTypes as $leaveType)
                                                                <option value="{{ $leaveType->id }}">
                                                                    {{ $leaveType->name_site }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterLeaveType')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="country_id"
                                                            class="form-label">@lang('leave_setting.Country')</label>
                                                        <select class="form-select" id="country_id" name="country_id"
                                                            required>
                                                            <option value="" disabled selected>@lang('leave_setting.ChooseCountry')
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

                                                    <div class="col-xl-2 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('leave_setting.عدد الايام')</label>
                                                        <input type="number" min="1" class="form-control"
                                                            placeholder="@lang('leave_setting.Minmum')" name="day_count" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMinmum')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-2 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('leave_setting.الايام المدفوعه')</label>
                                                        <input type="number" min="0" class="form-control"
                                                            placeholder="@lang('leave_setting.EnglishName')" name="day_paid">
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMaxmum')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-2 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('leave_setting.الايام الغير مدفوعه')</label>
                                                        <input type="number" min="0" value="0" class="form-control"
                                                            placeholder="@lang('leave_setting.EnglishName')" name="day_unpaid">
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMaxmum')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="leave_type"
                                                            class="form-label">@lang('leave_setting.نوع الاجازة')</label>
                                                        <select class="form-select" id="leave_type" name="leave_type"
                                                            required>
                                                            <option value="day">@lang('leave_setting.يوم')</option>
                                                            <option value="hour">@lang('leave_setting.ساعة')</option>
                                                        </select>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterCountry')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-2 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('leave_setting.الاجازة خلال مدة - بالشهر')</label>
                                                        <input type="number" min="1" class="form-control" value="12"
                                                            placeholder="@lang('leave_setting.EnglishName')" name="within_month">
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMaxmum')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label class="form-label">@lang('leave_setting.يجب على الموظف ان يرفع ملف')</label>
                                                        <div class="form-check" id="upload_certificate_div">
                                                            <input class="form-check-input" type="radio" name="upload_certificate" id="upload_certificate_yes" value="yes" required>
                                                            <label class="form-check-label" for="upload_certificate_yes">
                                                                @lang('leave_setting.yes')
                                                            </label>
                                                        </div>
                                                        <div class="form-check" id="no_upload_certificate_div">
                                                            <input class="form-check-input" type="radio" name="upload_certificate" id="upload_certificate_no" value="no" checked>
                                                            <label class="form-check-label" for="upload_certificate_no">
                                                                @lang('leave_setting.no')
                                                            </label>
                                                        </div>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label class="form-label">@lang('leave_setting.حالة الاجازات فى نهاية المدة')</label>
                                                        <div class="form-check" id="money_transfer_leave_div">
                                                            <input class="form-check-input" type="radio" name="transfer_leave" id="transfer_leave_money" value="money" required>
                                                            <label class="form-check-label" for="upload_certificate_money">
                                                                @lang('leave_setting.تتحول اللى فلوس')
                                                            </label>
                                                        </div>
                                                        <div class="form-check" id="next_year_transfer_leave_div">
                                                            <input class="form-check-input" type="radio" name="transfer_leave" id="transfer_leave_next_year" value="next_year">
                                                            <label class="form-check-label" for="upload_certificate_next_year">
                                                                @lang('leave_setting.تتحول الى المدة القادمة')
                                                            </label>
                                                        </div>
                                                        <div class="form-check" id="nothing_transfer_leave_div">
                                                            <input class="form-check-input" type="radio" name="transfer_leave" id="transfer_leave_nothing" value="nothing" checked>
                                                            <label class="form-check-label" for="upload_certificate_nothing">
                                                                @lang('leave_setting.لا يحدث شئ')
                                                            </label>
                                                        </div>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('leave_setting.ملف تعريفى بالاجازة و شروطها')</label>
                                                        <input type="file" class="form-control" name="file">
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMaxmum')
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
                                                <h6 class="modal-title" id="editModalLabel">@lang('leave_setting.EditLeaveSetting')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="edit_leave_type_id"
                                                            class="form-label">@lang('leave_setting.LeaveType')</label>
                                                        <select class="form-select" id="edit_leave_type_id" name="leave_type_id"
                                                            required>
                                                            <option value="" disabled selected>@lang('leave_setting.ChooseLeaveType')
                                                            </option>
                                                            @foreach ($leaveTypes as $leaveType)
                                                                <option value="{{ $leaveType->id }}">
                                                                    {{ $leaveType->name_site }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterLeaveType')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="edit_country_id"
                                                            class="form-label">@lang('leave_setting.Country')</label>
                                                        <select class="form-select" id="edit_country_id" name="country_id"
                                                            required>
                                                            <option value="" disabled selected>@lang('leave_setting.ChooseCountry')
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

                                                    <div class="col-xl-2 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit_day_count"
                                                            class="form-label">@lang('leave_setting.عدد الايام')</label>
                                                        <input type="number" min="1" class="form-control"
                                                            placeholder="@lang('leave_setting.Minmum')" id="edit_day_count" name="day_count" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMinmum')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-2 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit_day_paid"
                                                            class="form-label">@lang('leave_setting.الايام المدفوعه')</label>
                                                        <input type="number" min="0" class="form-control"
                                                            placeholder="@lang('leave_setting.EnglishName')" id="edit_day_paid" name="day_paid">
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMaxmum')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-2 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit_day_unpaid"
                                                            class="form-label">@lang('leave_setting.الايام الغير مدفوعه')</label>
                                                        <input type="number" min="0" value="0" class="form-control"
                                                            placeholder="@lang('leave_setting.EnglishName')" id="edit_day_unpaid" name="day_unpaid">
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMaxmum')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="edit_leave_type"
                                                            class="form-label">@lang('leave_setting.نوع الاجازة')</label>
                                                        <select class="form-select" id="edit_leave_type" name="leave_type"
                                                            required>
                                                            <option value="day">@lang('leave_setting.يوم')</option>
                                                            <option value="hour">@lang('leave_setting.ساعة')</option>
                                                        </select>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterCountry')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-2 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit_within_month"
                                                            class="form-label">@lang('leave_setting.الاجازة خلال مدة - بالشهر')</label>
                                                        <input type="number" min="1" class="form-control" value="12"
                                                            placeholder="@lang('leave_setting.EnglishName')" id="edit_within_month" name="within_month">
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMaxmum')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label class="edit_upload_certificate_yes">@lang('leave_setting.يجب على الموظف ان يرفع ملف')</label>
                                                        <div class="form-check" id="upload_certificate_div">
                                                            <input class="form-check-input" type="radio" name="upload_certificate" id="edit_upload_certificate_yes" value="yes" required>
                                                            <label class="form-check-label" for="upload_certificate_yes">
                                                                @lang('leave_setting.yes')
                                                            </label>
                                                        </div>
                                                        <div class="edit_upload_certificate_no" id="no_upload_certificate_div">
                                                            <input class="form-check-input" type="radio" name="upload_certificate" id="edit_upload_certificate_no" value="no" checked>
                                                            <label class="form-check-label" for="upload_certificate_no">
                                                                @lang('leave_setting.no')
                                                            </label>
                                                        </div>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label class="form-label">@lang('leave_setting.حالة الاجازات فى نهاية المدة')</label>
                                                        <div class="form-check" id="money_transfer_leave_div">
                                                            <input class="form-check-input" type="radio" name="transfer_leave" id="edit_transfer_leave_money" value="money" required>
                                                            <label class="form-check-label" for="upload_certificate_money">
                                                                @lang('leave_setting.تتحول اللى فلوس')
                                                            </label>
                                                        </div>
                                                        <div class="form-check" id="next_year_transfer_leave_div">
                                                            <input class="form-check-input" type="radio" name="transfer_leave" id="edit_transfer_leave_next_year" value="next_year">
                                                            <label class="form-check-label" for="upload_certificate_next_year">
                                                                @lang('leave_setting.تتحول الى المدة القادمة')
                                                            </label>
                                                        </div>
                                                        <div class="form-check" id="nothing_transfer_leave_div">
                                                            <input class="form-check-input" type="radio" name="transfer_leave" id="edit_transfer_leave_nothing" value="nothing" checked>
                                                            <label class="form-check-label" for="upload_certificate_nothing">
                                                                @lang('leave_setting.لا يحدث شئ')
                                                            </label>
                                                        </div>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit_file"
                                                            class="form-label">@lang('leave_setting.ملف تعريفى بالاجازة و شروطها')</label><br>
                                                            <img src="" id="edit_file" width="100" height="100"><br>
                                                        <input type="file" class="form-control" name="file">
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterMaxmum')
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
                                            <h6 class="modal-title" id="showModalLabel">@lang('leave_setting.ShowFloor')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('leave_setting.LeaveType')</label>
                                                    <p id="show_leave_type_id" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('leave_setting.CountryName')</label>
                                                    <p id="show_country_id" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('leave_setting.Minmum')</label>
                                                    <p id="show_day_paid" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('leave_setting.Maxmum')</label>
                                                    <p id="show_day_count" class="form-control-static"></p>
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
                                        <th scope="col">@lang('leave_setting.ID')</th>
                                        <th scope="col">@lang('leave_setting.CountryName')</th>
                                        <th scope="col">@lang('leave_setting.LeaveTypeName')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($LeaveSetting as $k_leave_setting => $leave_setting)
                                        <tr>
                                            <td>{{ ++$k_leave_setting }}</td>
                                            <td>{{ $leave_setting->countries->name_site }}</td>
                                            <td>{{ $leave_setting->leaveTypes->name_site }}</td>
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
                                                    action="{{ route('leave-setting.delete', $leave_setting->id) }}"
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
            var leaveSettingId = this.getAttribute('data-id');
            var get_url = "{{ route('leave-setting.show', 'id') }}";
            var edit_url = "{{ route('leave-setting.update', 'id') }}";
            get_url = get_url.replace('id', leaveSettingId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data
                    $('#edit_leave_type_id').val(data.leave_type_id);
                    $('#edit_country_id').val(data.country_id);
                    $('#edit_day_count').val(data.day_count);
                    $('#edit_day_paid').val(data.day_paid);
                    $('#edit_day_unpaid').val(data.day_unpaid);
                    $('#edit_leave_type').val(data.leave_type);
                    $('#edit_within_month').val(data.within_month);

                    $('input[name="upload_certificate"]').prop('checked', false);
                    if(data.upload_certificate == "yes"){
                        $('#edit_upload_certificate_yes').prop('checked', true);
                    }else{
                        $('#edit_upload_certificate_no').prop('checked', true);
                    }

                    $('input[name="transfer_leave"]').prop('checked', false);
                    if(data.transfer_leave == "money"){
                        $('#edit_transfer_leave_money').prop('checked', true);
                    }else if(data.transfer_leave == "next_year"){
                        $('#edit_transfer_leave_next_year').prop('checked', true);
                    }else{
                        $('#edit_transfer_leave_nothing').prop('checked', true);
                    }

                    if (data.file == null) {
                        $('#edit_file').css('display', 'none');
                    } else {
                        $('#edit_file').attr('src', data.file).css('display', 'block');
                    }

                    edit_url = edit_url.replace('id', leaveSettingId);

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
            var leaveSettingId = this.getAttribute('data-id');
            var get_url = "{{ route('leave-setting.show', 'id') }}";
            get_url = get_url.replace('id', leaveSettingId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data
                    $('#show_leave_type_id').text(data.leave_types.name_site);
                    $('#show_country_id').text(data.countries.name_site);
                    $('#show_day_count').text(data.day_count);
                    $('#show_day_paid').text(data.day_paid);
                    $('#show_day_unpaid').text(data.day_unpaid);

                    // Show the modal
                    $('#showModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
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
