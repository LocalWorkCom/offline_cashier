@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('Violation.AddViolation')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('violations.list') }}'">
                            @lang('Violation.Violations')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('violation.create') }}">@lang('Violation.AddViolation')</a>
                    </li>
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
                            <div class="card-title">@lang('violation.AddViolation')</div>
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
                            <form method="POST" action="{{ route('violation.store') }}" class="needs-validation" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <!-- Employee -->
                                    <div class="col-xl-6">
                                        <label for="employee_id" class="form-label">@lang('violation.Employee')</label>
                                        <select name="employee_id" id="employee_id" class="form-control select2" required>
                                            <option value="">@lang('general.Choose')</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}">
                                                    {{ $employee->first_name }}-
                                                    {{ $employee->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.SelectEmployee')</div>
                                    </div>
                            
                                    <!-- Violation Type -->
                                    <div class="col-xl-6">
                                        <label for="violation_type_id" class="form-label">@lang('violation.Type')</label>
                                        <select name="violation_type_id" id="violation_type_id" class="form-control select2" required>
                                            <option value="">@lang('general.Choose')</option>
                                            @foreach ($violationTypes as $type)
                                                <option value="{{ $type->id }}">
                                                    {{ app()->getLocale() == 'en' ? $type->name_en : $type->name_ar }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.SelectViolationType')</div>
                                    </div>
                            
                                    <!-- Violation Date -->
                                    <div class="col-xl-6">
                                        <label for="violation_date" class="form-label">@lang('violation.Date')</label>
                                        <input type="date" name="violation_date" id="violation_date" class="form-control" required>
                                        <div class="invalid-feedback">@lang('validation.EnterViolationDate')</div>
                                    </div>
                            
                                    <!-- Location -->
                                    <div class="col-xl-6">
                                        <label for="violation_location" class="form-label">@lang('violation.Location')</label>
                                        <input type="text" name="violation_location" id="violation_location" class="form-control" required>
                                        <div class="invalid-feedback">@lang('validation.EnterLocation')</div>
                                    </div>
                            
                                    <!-- Description -->
                                    <div class="col-xl-12">
                                        <label for="description" class="form-label">@lang('violation.Description')</label>
                                        <textarea name="description" id="description" rows="4" class="form-control" required></textarea>
                                        <div class="invalid-feedback">@lang('validation.EnterDescription')</div>
                                    </div>
                            
                                    <!-- Penalty -->
                                    <div class="col-xl-6">
                                        <label for="penalty" class="form-label">@lang('violation.Penalty')</label>
                                        <input type="number" step="0.01" name="penalty" id="penalty" class="form-control" required>
                                        <div class="invalid-feedback">@lang('validation.EnterPenalty')</div>
                                    </div>
                            
                                    <!-- Status -->
                       
                            
                                    <!-- Submit -->
                                    <div class="col-xl-4 mx-auto">
                                        <button type="submit" class="btn btn-primary form-control">
                                            @lang('violation.Save')
                                        </button>
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
