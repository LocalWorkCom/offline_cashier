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
        <h4 class="fw-medium mb-0">@lang('floor.Tables')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('floor.Tables')</li>
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
                                @lang('floor.Tables')</div>

                            @if (auth('admin')->user()->hasPermissionTo('create tables', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('floor.AddTable')
                                </button>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('create tables', 'admin'))
                                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('table.store') }}" method="POST"
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
                                                    <h6 class="modal-title" id="exampleModalLabel1">@lang('floor.AddTable')</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row gy-4">
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="branch"
                                                                class="form-label">@lang('floor.Floor')</label>
                                                            <select class="form-select floor" id="floor" name="floor_id"
                                                                required>
                                                                <option value="" disabled selected>@lang('floor.ChooseFloor')
                                                                </option>
                                                                @foreach ($floors as $floor)
                                                                    <option value="{{ $floor->id }}">
                                                                        {{ $floor->name_ar . ' | ' . $floor->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterFloor')
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="branch"
                                                                class="form-label">@lang('floor.Partition')</label>
                                                            <select class="form-select floor_partition" id="floor_partition"
                                                                name="floor_partition_id" required>
                                                            </select>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterPartition')
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('floor.ArabicName')</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="@lang('floor.ArabicName')" name="name_ar" required>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterArabicName')
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('floor.EnglishName')</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="@lang('floor.EnglishName')" name="name_en" required>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEnglishName')
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('floor.TableNumber')</label>
                                                            <input type="number" class="form-control"
                                                                placeholder="@lang('floor.TableNumber')" min="0"
                                                                name="table_number" required>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterTableNumber')
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('floor.Capacity')</label>
                                                            <input type="number" class="form-control"
                                                                placeholder="@lang('floor.Capacity')" min="1"
                                                                name="capacity" required>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterCapacity')
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                            <label class="form-label">@lang('floor.Type')</label>
                                                            <div class="form-check typeIndoor-div" id="typeIndoor-div"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="type" id="typeIndoor" value="1"
                                                                    required>
                                                                <label class="form-check-label" for="typeIndoor">
                                                                    @lang('floor.Indoor')
                                                                </label>
                                                            </div>
                                                            <div class="form-check typeOutdoor-div" id="typeOutdoor-div"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="type" id="typeOutdoor" value="2">
                                                                <label class="form-check-label" for="typeOutdoor">
                                                                    @lang('floor.Outdoor')
                                                                </label>
                                                            </div>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEnglishName')
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                            <label class="form-label">@lang('floor.Smoking')</label>
                                                            <div class="form-check smoking-div" id="smoking-div"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="smoking" id="typeIndoor" value="1"
                                                                    required>
                                                                <label class="form-check-label" for="typeIndoor">
                                                                    @lang('floor.Smokin')
                                                                </label>
                                                            </div>
                                                            <div class="form-check notSmoking-div" id="notSmoking-div"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="smoking" id="typeOutdoor" value="2">
                                                                <label class="form-check-label" for="typeOutdoor">
                                                                    @lang('floor.NoSmokin')
                                                                </label>
                                                            </div>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEnglishName')
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                            <label class="form-label">@lang('floor.Status')</label>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="status" id="statusAvailable" value="1"
                                                                    required>
                                                                <label class="form-check-label" for="statusAvailable">
                                                                    @lang('floor.Available')
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="status" id="statusOccupied" value="2">
                                                                <label class="form-check-label" for="statusOccupied">
                                                                    @lang('floor.Occupied')
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="status" id="statusReserved" value="3">
                                                                <label class="form-check-label" for="statusReserved">
                                                                    @lang('floor.Reserved')
                                                                </label>
                                                            </div>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEnglishName')
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                            <label class="form-label">@lang('floor.Online')</label>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="online" id="onlineYes" value="1"
                                                                    required>
                                                                <label class="form-check-label" for="onlineYes">
                                                                    @lang('floor.Yes')
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="online" id="onlineNo" value="0">
                                                                <label class="form-check-label" for="onlineNo">
                                                                    @lang('floor.No')
                                                                </label>
                                                            </div>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterOnlineStatus')
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
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('update tables', 'admin'))
                                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form id="edit-table-form" action="" method="POST"
                                                class="needs-validation" novalidate>
                                                @csrf
                                                @method('PUT')
                                                @if ($errors->any())
                                                    @foreach ($errors->all() as $error)
                                                        <div class="alert alert-solid-danger alert-dismissible fade show">
                                                            {{ $error }}
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="alert" aria-label="Close">
                                                                <i class="bi bi-x"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="editModalLabel">@lang('floor.EditTable')</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row gy-4">
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="edit-floor"
                                                                class="form-label">@lang('floor.Floor')</label>
                                                            <select id="floor_edit" class="form-select floor"
                                                                name="floor_id" required>
                                                                @foreach ($floors as $floor)
                                                                    <option value="{{ $floor->id }}">
                                                                        {{ $floor->name_ar . ' | ' . $floor->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="edit-partition"
                                                                class="form-label">@lang('floor.Partition')</label>
                                                            <select id="floor_partition_edit"
                                                                class="form-select floor_partition"
                                                                name="floor_partition_id" required>
                                                            </select>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="edit-name-ar"
                                                                class="form-label">@lang('floor.ArabicName')</label>
                                                            <input type="text" id="edit-name-ar" class="form-control"
                                                                name="name_ar" required>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="edit-name-en"
                                                                class="form-label">@lang('floor.EnglishName')</label>
                                                            <input type="text" id="edit-name-en" class="form-control"
                                                                name="name_en" required>
                                                        </div>
                                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                            <label for="edit-table-number"
                                                                class="form-label">@lang('floor.TableNumber')</label>
                                                            <input type="number" id="edit-table-number"
                                                                class="form-control" name="table_number" required>
                                                        </div>
                                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                            <label for="edit-capacity"
                                                                class="form-label">@lang('floor.Capacity')</label>
                                                            <input type="number" id="edit-capacity" min="1"
                                                                class="form-control" name="capacity" required>
                                                        </div>
                                                        <!-- Type Radio Buttons -->
                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                                            <label class="form-label">@lang('floor.Type')</label>
                                                            <div>
                                                                <div class="form-check typeIndoor-div"
                                                                    id="typeIndoor-divEdit" style="display:none">
                                                                    <input type="radio" class="form-check-input"
                                                                        id="type-indoor" name="type" value="1"
                                                                        required>
                                                                    <label class="form-check-label"
                                                                        for="type-indoor">@lang('floor.Indoor')</label>
                                                                </div>
                                                                <div class="form-check typeOutdoor-div"
                                                                    id="typeOutdoor-divEdit" style="display:none">
                                                                    <input type="radio" class="form-check-input"
                                                                        id="type-outdoor" name="type" value="2">
                                                                    <label class="form-check-label"
                                                                        for="type-outdoor">@lang('floor.Outdoor')</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Smoking Radio Buttons -->
                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                                            <label class="form-label">@lang('floor.Smoking')</label>
                                                            <div>
                                                                <div class="form-check smoking-div" id="smoking-divEdit"
                                                                    style="display:none">
                                                                    <input type="radio" class="form-check-input"
                                                                        id="smoking-yes" name="smoking" value="1"
                                                                        required>
                                                                    <label class="form-check-label"
                                                                        for="smoking-yes">@lang('floor.Smokin')</label>
                                                                </div>
                                                                <div class="form-check notSmoking-div"
                                                                    id="notSmoking-divEdit" style="display:none">
                                                                    <input type="radio" class="form-check-input"
                                                                        id="smoking-no" name="smoking" value="2">
                                                                    <label class="form-check-label"
                                                                        for="smoking-no">@lang('floor.NoSmokin')</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Status Radio Buttons -->
                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                                            <label class="form-label">@lang('floor.Status')</label>
                                                            <div>
                                                                <div class="form-check">
                                                                    <input type="radio" class="form-check-input"
                                                                        id="status-available" name="status"
                                                                        value="1" required>
                                                                    <label class="form-check-label"
                                                                        for="status-available">@lang('floor.Available')</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input type="radio" class="form-check-input"
                                                                        id="status-occupied" name="status"
                                                                        value="2">
                                                                    <label class="form-check-label"
                                                                        for="status-occupied">@lang('floor.Occupied')</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input type="radio" class="form-check-input"
                                                                        id="status-reserved" name="status"
                                                                        value="3">
                                                                    <label class="form-check-label"
                                                                        for="status-reserved">@lang('floor.Reserved')</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                            <label class="form-label">@lang('floor.Online')</label>
                                                            <div>
                                                                <div class="form-check">
                                                                    <input type="radio" class="form-check-input"
                                                                        id="edit-online-yes" name="online"
                                                                        value="1" required>
                                                                    <label class="form-check-label"
                                                                        for="edit-online-yes">@lang('floor.Yes')</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input type="radio" class="form-check-input"
                                                                        id="edit-online-no" name="online"
                                                                        value="0">
                                                                    <label class="form-check-label"
                                                                        for="edit-online-no">@lang('floor.No')</label>
                                                                </div>
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
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view tables', 'admin'))
                                <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="showModalLabel">@lang('floor.ShowTable')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.Floor')</label>
                                                        <p id="show-floor-id" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.Partition')</label>
                                                        <p id="show-partition-id" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.ArabicName')</label>
                                                        <p id="show-name-ar" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.EnglishName')</label>
                                                        <p id="show-name-en" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.TableNumber')</label>
                                                        <p id="show-number" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.Capacity')</label>
                                                        <p id="show-capacity" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.Type')</label>
                                                        <p id="show-type" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.Status')</label>
                                                        <p id="show-status" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.Smoking')</label>
                                                        <p id="show-smoking" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.Online')</label>
                                                        <p id="show-online" class="form-control-static"></p>
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
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view tables', 'admin'))
                                <div class="card-body">
                                    @if (session('message'))
                                        <div class="alert alert-solid-info alert-dismissible fade show">
                                            {{ session('message') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close">
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
                                    <!-- Add this filter section -->
                                    <div class="row mb-3">
                                        @unless (auth('admin')->user()->hasRole('Branch Manager'))
                                            <div class="col-md-3">
                                                <label for="branch-filter" class="form-label">@lang('floor.FilterByBranch')</label>
                                                <select class="form-select" id="branch-filter">
                                                    <option value="">@lang('floor.AllBranches')</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->id }}"
                                                            {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                            {{ $branch->name_ar . ' | ' . $branch->name_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endunless
                                    </div>
                                    <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th scope="col">@lang('floor.ID')</th>
                                                <th scope="col">@lang('floor.ArabicName')</th>
                                                <th scope="col">@lang('floor.EnglishName')</th>
                                                <th scope="col">@lang('floor.TableNumber')</th>
                                                <th scope="col">@lang('branch.Branch')</th>
                                                <th scope="col">@lang('floor.Capacity')</th>
                                                <th scope="col">@lang('floor.Type')</th>
                                                <th scope="col">@lang('floor.Status')</th>
                                                <th scope="col">@lang('floor.Smoking')</th>
                                                <th scope="col">@lang('floor.Floor')</th>
                                                <th scope="col">@lang('floor.Partition')</th>
                                                <th scope="col">@lang('floor.Online')</th>
                                                <th scope="col">@lang('category.Actions')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($Tables as $tables)
                                                <tr>
                                                    <td>{{ $tables->id }}</td>
                                                    <td>{{ $tables->name_ar }}</td>
                                                    <td>{{ $tables->name_en }}</td>
                                                    <td>{{ $tables->table_number }}</td>
                                                    <td>{{ $tables->floors->branches->name_ar . ' | ' . $tables->floors->branches->name_en }}
                                                    <td>{{ $tables->capacity }}</td>
                                                    <td>{{ $tables->type == 1 ? __('floor.Indoor') : ($tables->type == 2 ? __('floor.Outdoor') : __('floor.Both')) }}
                                                    </td>
                                                    <td>{{ $tables->status == 1 ? __('floor.Available') : ($tables->status == 2 ? __('floor.Occupied') : __('floor.Reserved')) }}
                                                    </td>
                                                    <td>{{ $tables->smoking == 1 ? __('floor.Smokin') : ($tables->smoking == 2 ? __('floor.NoSmokin') : __('floor.Both')) }}
                                                    </td>
                                                    <td>{{ $tables->floors->name_ar . ' | ' . $tables->floors->name_en }}
                                                    </td>
                                                    <td>{{ ($tables->floorPartitions->name_ar ?? '') . ' | ' . ($tables->floorPartitions->name_en ?? '') }}</td>
                                                    <td>{{ $tables->online == 1 ? __('floor.Yes') : __('floor.No') }}</td>
                                                    <td>

                                                        @if (auth('admin')->user()->hasPermissionTo('view tables', 'admin'))
                                                            <!-- Show Button -->
                                                            <a href="javascript:void(0);"
                                                                class="btn btn-info-light btn-wave show-table-btn"
                                                                data-id="{{ $tables->id }}" data-bs-toggle="modal"
                                                                data-bs-target="#showModal">
                                                                @lang('category.show') <i class="ri-eye-line"></i>
                                                            </a>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('update tables', 'admin'))
                                                            <!-- Edit Button -->
                                                            <button type="button"
                                                                class="btn btn-orange-light btn-wave edit-table-btn"
                                                                data-id="{{ $tables->id }}" data-bs-toggle="modal"
                                                                data-bs-target="#editModal">
                                                                @lang('category.edit') <i class="ri-edit-line"></i>
                                                            </button>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('delete tables', 'admin'))
                                                            <!-- Delete Button -->
                                                            <form class="d-inline" id="delete-form-{{ $tables->id }}"
                                                                action="{{ route('table.delete', $tables->id) }}"
                                                                method="POST" onsubmit="return confirmDelete()">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button"
                                                                    onclick="delete_item({{ $tables->id }})"
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
                            @endif
                        </div>
                    </div>
                </div>
                <!-- End:: row-4 -->
            </div>
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
        // Branch filter change handler
        $('#branch-filter').on('change', function() {
            var branchId = $(this).val();
            var url = "{{ route('tables.list') }}";

            if (branchId) {
                url += "?branch_id=" + branchId;
            }

            window.location.href = url;
        });
        $('.edit-table-btn').on('click', function() {
            var tableId = this.getAttribute('data-id');
            var get_url = "{{ route('table.show', 'id') }}";
            var edit_url = "{{ route('table.update', 'id') }}";
            get_url = get_url.replace('id', tableId);

            // AJAX request to fetch table details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    var floorId = data.floor_id;
                    var get_floor_partition_url =
                        "{{ route('floorPartition.show_data', 'id') }}";
                    get_floor_partition_url = get_floor_partition_url.replace('id',
                        floorId);

                    // Fetch floor partitions based on the selected floor
                    $.ajax({
                        url: get_floor_partition_url,
                        type: 'GET',
                        success: function(floor_data) {
                            $('.floor_partition').html(floor_data);
                            $('#floor_partition_edit').val(data
                                .floor_partition_id);
                        },
                        error: function(xhr, status, error) {
                            console.log('Error: ' + error);
                        }
                    });

                    // Populate the modal with the data
                    console.log(data.floor_id);

                    $('#floor_edit').val(data.floor_id);
                    $('#edit-name-ar').val(data.name_ar);
                    $('#edit-name-en').val(data.name_en);
                    $('#edit-table-number').val(data.table_number);
                    $('#edit-capacity').val(data.capacity);

                    // Handle type radio buttons
                    $('input[name="type"]').prop('checked', false);
                    if (data.type == 1) {
                        $('#type-indoor').prop('checked', true);
                        $('#typeIndoor-divEdit').css('display', 'block');
                    } else {
                        $('#type-outdoor').prop('checked', true);
                        $('#typeOutdoor-divEdit').css('display', 'block');
                    }

                    // Handle smoking radio buttons
                    $('input[name="smoking"]').prop('checked', false);
                    if (data.smoking == 1) {
                        $('#smoking-yes').prop('checked', true);
                        $('#smoking-divEdit').css('display', 'block');
                    } else {
                        $('#smoking-no').prop('checked', true);
                        $('#notSmoking-divEdit').css('display', 'block');
                    }

                    // Handle status radio buttons
                    $('input[name="status"]').prop('checked', false);
                    if (data.status == 1) {
                        $('#status-available').prop('checked', true);
                    } else if (data.status == 2) {
                        $('#status-occupied').prop('checked', true);
                    } else {
                        $('#status-reserved').prop('checked', true);
                    }

                    // Handle online radio buttons
                    $('input[name="online"]').prop('checked', false);
                    if (data.online == 1) {
                        $('#edit-online-yes').prop('checked', true);
                    } else {
                        $('#edit-online-no').prop('checked', true);
                    }

                    // Set the form action URL
                    edit_url = edit_url.replace('id', tableId);
                    $('#edit-table-form').attr('action', edit_url);

                    // Show the modal
                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('.show-table-btn').on('click', function() {
            var tableId = this.getAttribute('data-id');
            var get_url = "{{ route('table.show', 'id') }}";
            get_url = get_url.replace('id', tableId);

            // AJAX request to fetch table details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data
                    $('#show-floor-id').text(data.floors.name_site);
                    $('#show-partition-id').text(data.floor_partitions.name_site);
                    $('#show-name-ar').text(data.name_ar);
                    $('#show-name-en').text(data.name_en);
                    $('#show-number').text(data.table_number);
                    $('#show-capacity').text(data.capacity);

                    // Handle online status
                    let onlineText = data.online == 1 ? '{{ __('floor.Yes') }}' :
                        '{{ __('floor.No') }}';
                    $('#show-online').text(onlineText);

                    // Handle status
                    let statusText = data.status == 1 ? '{{ __('floor.Available') }}' :
                        data.status == 2 ? '{{ __('floor.Occupied') }}' :
                        '{{ __('floor.Reserved') }}';
                    $('#show-status').text(statusText);

                    // Handle type
                    if (data.type == 1) {
                        $('#show-type').text('{{ __('floor.Indoor') }}');
                    } else {
                        $('#show-type').text('{{ __('floor.Outdoor') }}');
                    }

                    // Handle smoking
                    if (data.smoking == 1) {
                        $('#show-smoking').text('{{ __('floor.Smokin') }}');
                    } else {
                        $('#show-smoking').text('{{ __('floor.NoSmokin') }}');
                    }

                    // Show the modal
                    $('#showModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('.floor').on('change', function() {
            var floorId = this.value;
            var get_url = "{{ route('floorPartition.show_data', 'id') }}";
            get_url = get_url.replace('id', floorId);

            // Fetch floor partitions based on the selected floor
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    $('.floor_partition').html(data);
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('#floor_partition').on('change', function() {
            var floorPartitionId = this.value;
            var get_url = "{{ route('floorPartition.show', 'id') }}";
            get_url = get_url.replace('id', floorPartitionId);

            // Fetch floor partition details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Handle type and smoking options
                    $('input[name="type"]').prop('checked', false);
                    $('input[name="smoking"]').prop('checked', false);
                    $('#typeIndoor-div, #typeOutdoor-div, #smoking-div, #notSmoking-div')
                        .hide();

                    if (data.type == 1) {
                        $('#typeIndoor-div').css('display', 'block');
                    } else {
                        $('#typeOutdoor-div').css('display', 'block');
                    }

                    if (data.smoking == 1 || data.smoking == 3) {
                        $('#smoking-div').css('display', 'block');
                    } else {
                        $('#notSmoking-div').css('display', 'block');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('#floor_partition_edit').on('change', function() {
            var floorPartitionId = this.value;
            var get_url = "{{ route('floorPartition.show', 'id') }}";
            get_url = get_url.replace('id', floorPartitionId);

            // Fetch floor partition details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Handle type and smoking options
                    $('input[name="type"]').prop('checked', false);
                    $('input[name="smoking"]').prop('checked', false);
                    $('#typeIndoor-divEdit, #typeOutdoor-divEdit, #smoking-divEdit, #notSmoking-divEdit')
                        .hide();

                    if (data.type == 1) {
                        $('#typeIndoor-divEdit').css('display', 'block');
                    } else {
                        $('#typeOutdoor-divEdit').css('display', 'block');
                    }

                    if (data.smoking == 1 || data.smoking == 3) {
                        $('#smoking-divEdit').css('display', 'block');
                    } else {
                        $('#notSmoking-divEdit').css('display', 'block');
                    }
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
            title: @json(__('validation.Alert')),
            text: @json(__('validation.DeleteConfirm')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: @json(__('validation.Delete')),
            cancelButtonText: @json(__('validation.Cancel')),
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('delete-form-' + id);
                form.submit();
            }
        });
    }
</script>

<?php /*{{-- // old js 
<script>
    $(document).ready(function() {
        $('.edit-table-btn').on('click', function() {
            var tableId = this.getAttribute('data-id');
            var get_url = "{{ route('table.show', 'id') }}";
            var edit_url = "{{ route('table.update', 'id') }}";
            get_url = get_url.replace('id', tableId);

            // AJAX request to fetch table details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    var floorId = data.floor_id;
                    var get_floor_partition_url = "{{ route('floorPartition.show_data', 'id') }}";
                    get_floor_partition_url = get_floor_partition_url.replace('id', floorId);

                    // Fetch floor partitions based on the selected floor
                    $.ajax({
                        url: get_floor_partition_url,
                        type: 'GET',
                        success: function(floor_data) {
                            $('.floor_partition').html(floor_data);
                            $('#floor_partition_edit').val(data.floor_partition_id);
                        },
                        error: function(xhr, status, error) {
                            console.log('Error: ' + error);
                        }
                    });

                    console.log(data.floor_id);
                    

                    // Populate the modal with the data
                    $('#floor').val(data.floor_id);
                    $('#edit-name-ar').val(data.name_ar);
                    $('#edit-name-en').val(data.name_en);
                    $('#edit-table-number').val(data.table_number);
                    $('#edit-capacity').val(data.capacity);

                    // Handle type radio buttons
                    $('input[name="type"]').prop('checked', false);
                    if (data.type == 1) {
                        $('#type-indoor').prop('checked', true);
                        $('#typeIndoor-divEdit').css('display', 'block');
                    } else {
                        $('#type-outdoor').prop('checked', true);
                        $('#typeOutdoor-divEdit').css('display', 'block');
                    }

                    // Handle smoking radio buttons
                    $('input[name="smoking"]').prop('checked', false);
                    if (data.smoking == 1) {
                        $('#smoking-yes').prop('checked', true);
                        $('#smoking-divEdit').css('display', 'block');
                    } else {
                        $('#smoking-no').prop('checked', true);
                        $('#notSmoking-divEdit').css('display', 'block');
                    }

                    // Handle status radio buttons
                    $('input[name="status"]').prop('checked', false);
                    if (data.status == 1) {
                        $('#status-available').prop('checked', true);
                    } else if (data.status == 2) {
                        $('#status-occupied').prop('checked', true);
                    } else {
                        $('#status-reserved').prop('checked', true);
                    }

                    // Handle online radio buttons
                    $('input[name="online"]').prop('checked', false);
                    if (data.online == 1) {
                        $('#edit-online-yes').prop('checked', true);
                    } else {
                        $('#edit-online-no').prop('checked', true);
                    }

                    // Set the form action URL
                    edit_url = edit_url.replace('id', tableId);
                    $('#edit-table-form').attr('action', edit_url);

                    // Show the modal
                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('.show-table-btn').on('click', function() {
            var tableId = this.getAttribute('data-id');
            var get_url = "{{ route('table.show', 'id') }}";
            get_url = get_url.replace('id', tableId);

            // AJAX request to fetch table details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data
                    $('#show-floor-id').text(data.floors.name_site);
                    $('#show-partition-id').text(data.floor_partitions.name_site);
                    $('#show-name-ar').text(data.name_ar);
                    $('#show-name-en').text(data.name_en);
                    $('#show-number').text(data.table_number);
                    $('#show-capacity').text(data.capacity);

                    // Handle online status
                    let onlineText = data.online == 1 ? '{{ __('floor.Yes') }}' : '{{ __('floor.No') }}';
                    $('#show-online').text(onlineText);

                    // Handle status
                    let statusText = data.status == 1 ? '{{ __('floor.Available') }}' :
                        data.status == 2 ? '{{ __('floor.Occupied') }}' :
                        '{{ __('floor.Reserved') }}';
                    $('#show-status').text(statusText);

                    // Handle type
                    if (data.type == 1) {
                        $('#show-type').text('{{ __('floor.Indoor') }}');
                    } else {
                        $('#show-type').text('{{ __('floor.Outdoor') }}');
                    }

                    // Handle smoking
                    if (data.smoking == 1) {
                        $('#show-smoking').text('{{ __('floor.Smokin') }}');
                    } else {
                        $('#show-smoking').text('{{ __('floor.NoSmokin') }}');
                    }

                    // Show the modal
                    $('#showModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('.floor').on('change', function() {
            var floorId = this.value;
            var get_url = "{{ route('floorPartition.show_data', 'id') }}";
            get_url = get_url.replace('id', floorId);

            // Fetch floor partitions based on the selected floor
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    $('.floor_partition').html(data);
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('#floor_partition').on('change', function() {
            var floorPartitionId = this.value;
            var get_url = "{{ route('floorPartition.show', 'id') }}";
            get_url = get_url.replace('id', floorPartitionId);

            // Fetch floor partition details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Handle type and smoking options
                    $('input[name="type"]').prop('checked', false);
                    $('input[name="smoking"]').prop('checked', false);
                    $('#typeIndoor-div, #typeOutdoor-div, #smoking-div, #notSmoking-div').hide();

                    if (data.type == 1) {
                        $('#typeIndoor-div').css('display', 'block');
                    } else {
                        $('#typeOutdoor-div').css('display', 'block');
                    }

                    if (data.smoking == 1 || data.smoking == 3) {
                        $('#smoking-div').css('display', 'block');
                    } else {
                        $('#notSmoking-div').css('display', 'block');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('#floor_partition_edit').on('change', function() {
            var floorPartitionId = this.value;
            var get_url = "{{ route('floorPartition.show', 'id') }}";
            get_url = get_url.replace('id', floorPartitionId);

            // Fetch floor partition details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Handle type and smoking options
                    $('input[name="type"]').prop('checked', false);
                    $('input[name="smoking"]').prop('checked', false);
                    $('#typeIndoor-divEdit, #typeOutdoor-divEdit, #smoking-divEdit, #notSmoking-divEdit').hide();

                    if (data.type == 1) {
                        $('#typeIndoor-divEdit').css('display', 'block');
                    } else {
                        $('#typeOutdoor-divEdit').css('display', 'block');
                    }

                    if (data.smoking == 1 || data.smoking == 3) {
                        $('#smoking-divEdit').css('display', 'block');
                    } else {
                        $('#notSmoking-divEdit').css('display', 'block');
                    }
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
            title: @json(__('validation.Alert')),
            text: @json(__('validation.DeleteConfirm')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: @json(__('validation.Delete')),
            cancelButtonText: @json(__('validation.Cancel')),
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('delete-form-' + id);
                form.submit();
            }
        });
    }
</script> --}} */
?>
