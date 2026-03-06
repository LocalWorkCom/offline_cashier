@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('vehicle_setting.EditVehicleSetting')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('vehicle_settings.index') }}'">
                            @lang('vehicle_setting.VehicleSettings')
                        </a>

                    </li>
                    <a href="javascript:void(0);"
                        onclick="window.location.href='{{ route('vehicle_setting.edit', ['id' => $vehicleSetting->id]) }}'">
                        @lang('vehicle_setting.VehicleSettings')
                    </a>

                </ol>

            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('vehicle_setting.EditVehicleSetting')</div>
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

                            <form method="POST" action="{{ route('vehicle_setting.update', $vehicleSetting->id) }}"
                                class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <!-- Vehicle Type -->
                                    <div class="col-xl-6">
                                        <label for="vehicle_type" class="form-label">@lang('vehicle_setting.vehicle_type')</label>
                                        <select name="vehicle_type" id="vehicle_type" class="form-control" required>
                                            @foreach (\App\Models\VehicleSetting::getVehicleTypes() as $type)
                                                <option value="{{ $type }}"
                                                    {{ old('vehicle_type', $vehicleSetting->vehicle_type) == $type ? 'selected' : '' }}>
                                                    @lang('vehicle_setting.' . strtolower($type)) <!-- Translate car and motorcycle types -->
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.vehicle_type')</div>
                                    </div>
                                    

                                    <div class="col-xl-6">
                                        <label for="vehicle_max" class="form-label">@lang('vehicle_setting.vehicle_max')</label>
                                        <input type="number" name="vehicle_max" id="vehicle_max" class="form-control"
                                            value="{{ old('vehicle_max', $vehicleSetting->vehicle_max) }}" min="1"
                                            required>
                                        <div class="invalid-feedback">@lang('validation.vehicle_max')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="vehicle_min" class="form-label">@lang('vehicle_setting.vehicle_min')</label>
                                        <input type="number" name="vehicle_min" id="vehicle_min" class="form-control"
                                            value="{{ old('vehicle_min', $vehicleSetting->vehicle_min) }}" min="1"
                                            required>
                                        <div class="invalid-feedback">@lang('validation.EnterVehicleMin')</div>
                                    </div>

                                    <div class="col-xl-12 text-center">
                                        <button type="submit" class="btn btn-primary">@lang('vehicle_setting.EditVehicleSetting')</button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Custom JS -->
    @vite('resources/assets/js/validation.js')
@endsection
