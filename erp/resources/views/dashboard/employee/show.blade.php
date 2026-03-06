@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .section-title {
            background-color: #6571ff;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-left: 4px solid #6571ff;
        }

        .info-card {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
    </style>
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('employee.show')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('employees.list') }}">@lang('employee.employees')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('employee.show')</li>
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
                            <div class="card-title">
                                {{ $employee->first_name . ' ' . $employee->last_name }}
                                <small class="text-muted">({{ $employee->employee_code }})</small>
                            </div>
                            {{-- <div class="card-options">
                                @if (auth('admin')->user()->hasPermissionTo('update employees', 'admin'))
                                    <a href="{{ route('employee.edit', $employee->id) }}" class="btn btn-sm btn-primary">
                                        <i class="ri-edit-line"></i> @lang('employee.edit')
                                    </a>
                                @endif
                            </div> --}}
                        </div>
                        <div class="card-body">
                            <!-- Basic Information Section DONE -->
                            <div class="mb-5">
                                <h5 class="section-title">@lang('employee.basicInformation')</h5>
                                <div class="row">
                                    <div class="col-md-3 text-center mb-4">
                                        @if ($employee->image)
                                            <img src="{{ asset($employee->image) }}" class="img-thumbnail rounded-circle"
                                                style="width: 150px; height: 150px; object-fit: cover;">
                                        @else
                                            <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 150px; height: 150px; background-color: #f0f0f0;">
                                                <i class="ri-user-line" style="font-size: 60px; color: #999;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">@lang('employee.employeeID')</label>
                                                <p class="form-control-static">
                                                    {{ $employee->employee_code ?? __('employee.nothing') }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">@lang('employee.fullName') (AR)</label>
                                                <p class="form-control-static">
                                                    {{ $employee->first_name . ' ' . $employee->last_name ?? __('employee.nothing') }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">@lang('employee.fullName') (EN)</label>
                                                <p class="form-control-static">
                                                    {{ ($employee->first_name_en ? $employee->first_name_en . ' ' : '') . ($employee->last_name_en ?? '') ?: __('employee.nothing') }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">@lang('employee.gender')</label>
                                                <p class="form-control-static">
                                                    {{ $employee->gender ? __('employee.' . strtolower($employee->gender)) : __('employee.nothing') }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">@lang('employee.dob')</label>
                                                <p class="form-control-static">
                                                    {{ $employee->birth_date ? date('d/m/Y', strtotime($employee->birth_date)) : __('employee.nothing') }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">@lang('employee.nationality')</label>
                                                <p class="form-control-static">
                                                    @if ($employee->nationality_id)
                                                        {{ app()->getLocale() === 'ar' ? $employee->nationality->name_ar : $employee->nationality->name_en }}
                                                    @else
                                                        {{ __('employee.nothing') }}
                                                    @endif
                                                </p>

                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">@lang('employee.maritalStatus')</label>
                                                <p class="form-control-static">
                                                    @if ($employee->marital_status_id)
                                                        {{ app()->getLocale() === 'ar' ? $employee->maritalStatus->name_ar : $employee->maritalStatus->name_en }}
                                                    @else
                                                        {{ __('employee.nothing') }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">@lang('employee.bloodGroup')</label>
                                                <p class="form-control-static">
                                                    {{ $employee->blood_group ?? __('employee.nothing') }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">@lang('employee.ethnicBackground')</label>
                                                <p class="form-control-static">
                                                    @if ($employee->ethnic_background_id)
                                                        {{ app()->getLocale() === 'ar' ? $employee->ethnicBackground->name_ar : $employee->ethnicBackground->name_en }}
                                                    @else
                                                        {{ __('employee.nothing') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Section DONE-->
                            <div class="mb-5">
                                <h5 class="section-title">@lang('employee.contactInformation')</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.countryCode')</label>
                                        <p class="form-control-static">
                                            {{ $employee->country_code ?? __('employee.nothing') }}</p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.phone')</label>
                                        <p class="form-control-static">
                                            {{ $employee->phone_number ?? __('employee.nothing') }}</p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.whatsapp_number')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->employeeContactInfo->first())->whatsapp_number ?? __('employee.nothing') }}
                                        </p>

                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.email')</label>
                                        <p class="form-control-static">{{ $employee->email ?? __('employee.nothing') }}
                                        </p>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">@lang('employee.current_address')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->employeeContactInfo->first())->current_address ?? __('employee.nothing') }}
                                        </p>

                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">@lang('employee.home_country_address')</label>

                                        <p class="form-control-static">
                                            {{ optional($employee->employeeContactInfo->first())->home_country_address ?? __('employee.nothing') }}
                                        </p>

                                    </div>
                                </div>
                            </div>

                            <!-- Employment Details Section DONE-->
                            <div class="mb-5">
                                <h5 class="section-title">@lang('employee.employmentDetails')</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.branch')</label>
                                        <p class="form-control-static">
                                            @if ($employee->branch_id)
                                                {{ $employee->branch->name }}
                                            @else
                                                {{ __('employee.nothing') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.department')</label>
                                        <p class="form-control-static">
                                            @if ($employee->department_id)
                                                {{ app()->getLocale() === 'ar' ? $employee->department->name_ar : $employee->department->name_en }}
                                            @else
                                                {{ __('employee.nothing') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.position')</label>
                                        <p class="form-control-static">
                                            @if ($employee->position_id)
                                                {{ app()->getLocale() === 'ar' ? $employee->position->name_ar : $employee->position->name_en }}
                                            @else
                                                {{ __('employee.nothing') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.employmentType')</label>
                                        <p class="form-control-static">
                                            {{ $employee->employment_type ? __('employee.' . strtolower($employee->employment_type)) : __('employee.nothing') }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.hireDate')</label>
                                        <p class="form-control-static">
                                            {{ $employee->hire_date ? date('d/m/Y', strtotime($employee->hire_date)) : __('employee.nothing') }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.schedule')</label>
                                        <p class="form-control-static">
                                            @if ($employee->employeeSchedules->first() && $employee->employeeSchedules->first()->shift_id)
                                                {{ app()->getLocale() === 'ar'
                                                    ? optional($employee->employeeSchedules->first()->shift)->name_ar
                                                    : optional($employee->employeeSchedules->first()->shift)->name_en }}
                                                <button type="button" class="btn btn-info-light btn-wave show-btn"
                                                    data-id="{{ $employee->employeeSchedules->first()->id }}"
                                                    data-employee-name="{{ $employee->first_name . ' ' . $employee->last_name }}"
                                                    data-shift-id="{{ $employee->employeeSchedules->first()->shift_id }}"
                                                    data-shift-name="{{ app()->getLocale() == 'ar' ? $employee->employeeSchedules->first()->shift->name_ar : $employee->employeeSchedules->first()->shift->name_en }}"
                                                    data-start-date="{{ $employee->employeeSchedules->first()->start_date }}"
                                                    data-end-date="{{ $employee->employeeSchedules->first()->end_date }}"
                                                    data-shift-details="{{ json_encode(
                                                        $employee->employeeSchedules->first()->shift->details->map(function ($detail) {
                                                                return [
                                                                    'day_index' => $detail->day_index,
                                                                    'timetable' => $detail->timetable
                                                                        ? [
                                                                            'name' =>
                                                                                app()->getLocale() == 'ar' ? $detail->timetable->name_ar : $detail->timetable->name_en,
                                                                            'on_duty_time' => $detail->timetable->on_duty_time,
                                                                            'off_duty_time' => $detail->timetable->off_duty_time,
                                                                            'start_sign_in' => $detail->timetable->start_sign_in,
                                                                            'end_sign_in' => $detail->timetable->end_sign_in,
                                                                            'start_sign_out' => $detail->timetable->start_sign_out,
                                                                            'end_sign_out' => $detail->timetable->end_sign_out,
                                                                            'lateness_grace_period' => $detail->timetable->lateness_grace_period,
                                                                            'cross_day' => $detail->timetable->cross_day,
                                                                        ]
                                                                        : null,
                                                                ];
                                                            })->toArray(),
                                                    ) }}"
                                                    data-bs-toggle="modal" data-bs-target="#showModal">
                                                    @lang('schedule.show') <i class="ri-eye-line"></i>
                                                </button>
                                            @else
                                                {{ __('employee.nothing') }}
                                            @endif

                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.schedule_start_date')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->employeeSchedules->first())->start_date
                                                ? date('d/m/Y', strtotime($employee->employeeSchedules->first()->start_date))
                                                : __('employee.nothing') }}
                                        </p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.schedule_end_date')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->employeeSchedules->first())->end_date
                                                ? date('d/m/Y', strtotime($employee->employeeSchedules->first()->end_date))
                                                : __('employee.nothing') }}
                                        </p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.supervisor')</label>
                                        <p class="form-control-static">
                                            @if ($employee->supervisor_id)
                                                {{ $employee->supervisor->first_name . ' ' . $employee->supervisor->last_name . ' | ' . $employee->supervisor->employee_code }}
                                            @else
                                                {{ __('employee.nothing') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.flag')</label>
                                        <p class="form-control-static">
                                            {{ $employee->flag ? __('employee.' . strtolower(str_replace(' ', '_', $employee->flag))) : __('employee.nothing') }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.role')</label>
                                        <p class="form-control-static">
                                            @if ($employee->roles->isNotEmpty())
                                                {{ $employee->roles->pluck('name')->join(', ') }}
                                            @else
                                                {{ __('employee.nothing') }}
                                            @endif
                                        </p>

                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.activation')</label>
                                        <p class="form-control-static">
                                            {{ $employee->status == 'active' ? __('employee.active') : __('employee.notActive') }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.status')</label>
                                        <p class="form-control-static">
                                            @if ($employee->employee_status_id)
                                                {{ app()->getLocale() === 'ar' ? $employee->employeeStatus->name_ar : $employee->employeeStatus->name_en }}
                                            @else
                                                {{ __('employee.nothing') }}
                                            @endif
                                        </p>
                                    </div>

                                </div>
                            </div>

                            @if ($employee->flag === 'hr')
                                <!-- Compensation & Payroll Section DONE -->
                                <div class="mb-5">
                                    <h5 class="section-title">@lang('employee.compensationPayroll')</h5>
                                    <div class="row">

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.base_salary')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->base_salary ?? __('employee.nothing') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.salary_work_permit')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->salary_work_permit ?? __('employee.nothing') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.annual_salary')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->annual_salary ?? __('employee.nothing') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.payment_type')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->payment_type
                                                    ? __(
                                                        'employee.' .
                                                            strtolower(str_replace(' ', '_', optional($employee->employeeSalaryDetail->first())->payment_type)),
                                                    )
                                                    : __('employee.nothing') }}


                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.payment_frequency')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->payment_frequency
                                                    ? __(
                                                        'employee.' .
                                                            strtolower(str_replace(' ', '_', optional($employee->employeeSalaryDetail->first())->payment_frequency)),
                                                    )
                                                    : __('employee.nothing') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.insurance_subscription_amount')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->insurance_subscription_amount ?? __('employee.nothing') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.commission_amount')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->commission_amount ?? __('employee.nothing') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.commission_type')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->commission_type
                                                    ? __(
                                                        'employee.' .
                                                            strtolower(str_replace(' ', '_', optional($employee->employeeSalaryDetail->first())->commission_type)),
                                                    )
                                                    : __('employee.nothing') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.currency')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->currency ?? __('employee.nothing') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.currency_code')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->currency_code ?? __('employee.nothing') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.insurance_registered')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->insurance_registered ?? '--------' }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.is_bounce_allowance')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->is_bounce_allowance == 1
                                                    ? __('employee.yes')
                                                    : __('employee.no') }}
                                            </p>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.assuranceSalary')</label>
                                            <p class="form-control-static">
                                                {{ $employee->assurance_salary ? number_format($employee->assurance_salary, 2) : __('employee.nothing') }}
                                            </p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.assuranceNumber')</label>
                                            <p class="form-control-static">
                                                {{ $employee->assurance_number ?? __('employee.nothing') }}
                                            </p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.bankname')</label>
                                            <p class="form-control-static">
                                                {{ app()->getLocale() == 'ar'
                                                    ? optional(optional($employee->EmployeeBankingInfo->first())->bank_names)->name_ar ?? __('employee.nothing')
                                                    : optional(optional($employee->EmployeeBankingInfo->first())->bank_names)->name_en ?? __('employee.nothing') }}
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.bankAccountnumber')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->EmployeeBankingInfo->first())->bank_account_number ?? __('employee.nothing') }}
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.bank_iban')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->EmployeeBankingInfo->first())->bank_iban ?? __('employee.nothing') }}
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.is_payroll_account')</label>
                                            <p class="form-control-static">
                                                {{ optional($employee->employeeSalaryDetail->first())->is_payroll_account == 1
                                                    ? __('employee.yes')
                                                    : __('employee.no') }}
                                            </p>
                                        </div>

                                    </div>
                                </div>
                            @endif

                            <!-- Legal Documents Section DONE -->
                            <div class="mb-5">
                                <h5 class="section-title">@lang('employee.legalDocuments')</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.national_id')</label>
                                        <p class="form-control-static">
                                            {{ $employee->national_id ?? __('employee.nothing') }}</p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.passport')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->legalDocument->first())->passport_number ?? __('employee.nothing') }}

                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.PassportExpiryDate')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->legalDocument->first())->passport_expiry_date ?? __('employee.nothing') }}

                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.WorkPermitExpiryDate')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->legalDocument->first())->work_permit_expiry_date ?? __('employee.nothing') }}

                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.ResidencyExpiryDate')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->legalDocument->first())->residency_expiry_date ?? __('employee.nothing') }}

                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.military_status')</label>
                                        <p class="form-control-static">
                                            @if ($employee->military_service_status_id)
                                                {{ app()->getLocale() === 'ar'
                                                    ? optional($employee->militaryStatus)->name_ar
                                                    : optional($employee->militaryStatus)->name_en }}
                                            @else
                                                {{ __('employee.nothing') }}
                                            @endif
                                        </p>
                                    </div>
                                    @if ($employee->flag === 'driver')
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.vehicle_type')</label>
                                            <p class="form-control-static">
                                                {{ optional(optional($employee->vehicle)->type)->vehicle_type ?? __('employee.nothing') }}
                                            </p>

                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">@lang('employee.vehicle_number')</label>


                                            <p class="form-control-static">
                                                {{ optional(optional($employee->vehicle))->license ?? __('employee.nothing') }}

                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Documents List -->
                                <div class="info-card">
                                    <h6>@lang('employee.uploadedDocuments')</h6>
                                    <div class="document-item">
                                        <span>@lang('employee.profileImage')</span>
                                        @if ($employee->image)
                                            <a href="{{ $employee->image }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="ri-download-line"></i> @lang('employee.download')
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('employee.notUploaded')</span>
                                        @endif
                                    </div>
                                    <div class="document-item">
                                        <span>@lang('employee.PassportCopy')</span>

                                        @if ($employee->legalDocument->first()?->passport_copy)
                                            <a href="{{ asset($employee->legalDocument->first()->passport_copy) }}"
                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-download-line"></i> @lang('employee.download')
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('employee.notUploaded')</span>
                                        @endif
                                    </div>
                                    <div class="document-item">
                                        <span>@lang('employee.WorkPermitCopy')</span>


                                        @if ($employee->legalDocument->first()?->work_permit)
                                            <a href="{{ asset($employee->legalDocument->first()->work_permit) }}"
                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-download-line"></i> @lang('employee.download')
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('employee.notUploaded')</span>
                                        @endif
                                    </div>
                                    <div class="document-item">
                                        <span>@lang('employee.resume')</span>
                                        @if ($employee->experience->first()?->resume)
                                            <a href="{{ asset($employee->experience->first()->resume) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="ri-download-line"></i> @lang('employee.download')
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('employee.notUploaded')</span>
                                        @endif

                                    </div>
                                    <div class="document-item">
                                        <span>@lang('employee.certification_documents')</span>
                                        @if ($employee->educations->first()?->certification_documents)
                                            <a href="{{ asset($employee->educations->first()->certification_documents) }}"
                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-download-line"></i> @lang('employee.download')
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('employee.notUploaded')</span>
                                        @endif

                                    </div>
                                    <div class="document-item">
                                        <span>@lang('employee.additional_certifications')</span>
                                        @if ($employee->educations->first()?->additional_certifications)
                                            <a href="{{ asset($employee->educations->first()->additional_certifications) }}"
                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-download-line"></i> @lang('employee.download')
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('employee.notUploaded')</span>
                                        @endif

                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact Information Section DONE-->
                            <div class="mb-5">
                                <h5 class="section-title">@lang('employee.emergencyContactInfo')</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.emergencyNameOne')</label>

                                        <p class="form-control-static">
                                            {{ optional($employee->employeeContactInfo->first())->emergency_contact_one_name ?? __('employee.nothing') }}

                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.emergencyRelOne')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->employeeContactInfo->first())->emergency_contact_one_relation ?? __('employee.nothing') }}

                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.emergencyPhoneOne')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->employeeContactInfo->first())->emergency_contact_one_phone ?? __('employee.nothing') }}

                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.emergencyNameTwo')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->employeeContactInfo->first())->emergency_contact_two_name ?? __('employee.nothing') }}

                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.emergencyRelTwo')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->employeeContactInfo->first())->emergency_contact_two_relation ?? __('employee.nothing') }}

                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.emergencyPhoneTwo')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->employeeContactInfo->first())->emergency_contact_two_phone ?? __('employee.nothing') }}

                                    </div>
                                </div>
                            </div>

                            <!-- Work History & Experience Section DONE -->
                            <div class="mb-5">
                                <h5 class="section-title">@lang('employee.workHistoryExperience')</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.previousPosition')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->experience->first())->previous_position ?? __('employee.nothing') }}
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.previousSalary')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->experience->first())->previous_salary ?? __('employee.nothing') }}

                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.expectedSalary')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->experience->first())->expected_salary ?? __('employee.nothing') }}
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.yearsOfExperience')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->experience->first())->num_experience_years ?? __('employee.nothing') }}
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.degree_certificate')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->educations->first())->degree_certificate ?? __('employee.nothing') }}
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.graduation_year')</label>
                                        <p class="form-control-static">
                                            {{ optional($employee->educations->first())->graduation_year ?? __('employee.nothing') }}
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.filed_of_study')</label>
                                        <p class="form-control-static">
                                            {{ app()->getLocale() == 'ar'
                                                ? optional(optional($employee->educations->first())->filed_of_study)->name_ar ?? __('employee.nothing')
                                                : optional(optional($employee->educations->first())->filed_of_study)->name_en ?? __('employee.nothing') }}
                                        </p>
                                    </div>


                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.education_level')</label>
                                        <p class="form-control-static">
                                            {{ app()->getLocale() == 'ar'
                                                ? optional(optional($employee->educations->first())->education_level)->name_ar ?? __('employee.nothing')
                                                : optional(optional($employee->educations->first())->education_level)->name_en ?? __('employee.nothing') }}
                                        </p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.university')</label>
                                        <p class="form-control-static">
                                            {{ app()->getLocale() == 'ar'
                                                ? optional(optional($employee->educations->first())->university)->name_ar ?? __('employee.nothing')
                                                : optional(optional($employee->educations->first())->university)->name_en ?? __('employee.nothing') }}
                                        </p>
                                    </div>


                                </div>
                            </div>

                            <!-- Other Details Section DONE -->
                            <div class="mb-5">
                                <h5 class="section-title">@lang('employee.otherDetails')</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.isBiometric')</label>
                                        <p class="form-control-static">
                                            {{ $employee->is_biometric == '1' ? __('employee.yes') : __('employee.no') }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">@lang('employee.biometricId')</label>
                                        <p class="form-control-static">
                                            {{ $employee->biometric_id ?? __('employee.nothing') }}</p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">@lang('employee.notes')</label>
                                        <p class="form-control-static">{{ $employee->notes ?? __('employee.nothing') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Show Modal -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="showModalLabel">@lang('schedule.showSchedule')</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h5 id="show-employee-name"></h5>
                        <p class="text-muted">
                            <strong>@lang('schedule.shift'):</strong> <span id="show-shift-name"></span> |
                            <strong>@lang('schedule.period'):</strong> <span id="show-period"></span>
                        </p>
                    </div>
                    <table class="table table-bordered schedule-table">
                        <thead>
                            <tr>
                                <th>@lang('schedule.day')</th>
                                <th>@lang('schedule.timetable')</th>
                                <th>@lang('schedule.dutyTime')</th>
                                <th>@lang('schedule.signInWindow')</th>
                                <th>@lang('schedule.signOutWindow')</th>
                                <th>@lang('schedule.latenessGrace')</th>
                            </tr>
                        </thead>
                        <tbody id="show-schedule-body">
                            <!-- Populated dynamically by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('modal.close')</button>
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
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <!-- SELECT2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
    <!-- SWEETALERT2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- FLATPICKR JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Reinitialize Select2 when modals are shown
            $('#createModal, #editModal, #defaultScheduleModal').on('shown.bs.modal', function() {
                $('.select2-employee, .select2-shift, .select2-multiple').select2({
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $(this),
                    closeOnSelect: $(this).hasClass('select2-multiple') ? false : true
                });
            });

            // Initialize Flatpickr with icon
            flatpickr('.datepicker', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                theme: 'airbnb',
                position: 'auto',
                onOpen: function(selectedDates, dateStr, instance) {
                    instance.calendarContainer.style.zIndex = '9999';
                }
            });

            // Show Modal
            const showButtons = document.querySelectorAll('.show-btn');
            const showEmployeeName = document.getElementById('show-employee-name');
            const showShiftName = document.getElementById('show-shift-name');
            const showPeriod = document.getElementById('show-period'); // Fixed ID
            const showScheduleBody = document.getElementById('show-schedule-body');

            showButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Populate basic info
                    showEmployeeName.textContent = this.getAttribute('data-employee-name') || 'N/A';
                    showShiftName.textContent = this.getAttribute('data-shift-name') || 'N/A';
                    showPeriod.textContent =
                        `${this.getAttribute('data-start-date')} - ${this.getAttribute('data-end-date')}`;

                    // Parse shift details
                    let shiftDetails;
                    try {
                        shiftDetails = JSON.parse(this.getAttribute('data-shift-details'));
                        console.log('Shift Details:', shiftDetails); // Debug: Log shift details
                    } catch (error) {
                        console.error('Error parsing shift details:', error);
                        shiftDetails = [];
                    }

                    // Define days of the week
                    const days = [
                        "@lang('schedule.sunday')",
                        "@lang('schedule.monday')",
                        "@lang('schedule.tuesday')",
                        "@lang('schedule.wednesday')",
                        "@lang('schedule.thursday')",
                        "@lang('schedule.friday')",
                        "@lang('schedule.saturday')"
                    ];

                    // Clear previous schedule
                    showScheduleBody.innerHTML = '';

                    // Populate schedule for each day
                    days.forEach((day, index) => {
                        const detail = shiftDetails.find(d => d.day_index == index);
                        let row = '<tr>';

                        // Day
                        row += `<td>${day}</td>`;

                        if (detail && detail.timetable) {
                            const timetable = detail.timetable;
                            // Timetable Name
                            row +=
                                `<td>${timetable.name || 'N/A'}${timetable.cross_day ? ' <small>(Cross Day)</small>' : ''}</td>`;
                            // Duty Time
                            row +=
                                `<td>${timetable.on_duty_time || 'N/A'} - ${timetable.off_duty_time || 'N/A'}</td>`;
                            // Sign In Window
                            row +=
                                `<td>${timetable.start_sign_in || 'N/A'} - ${timetable.end_sign_in || 'N/A'}</td>`;
                            // Sign Out Window
                            row +=
                                `<td>${timetable.start_sign_out || 'N/A'} - ${timetable.end_sign_out || 'N/A'}</td>`;
                            // Lateness Grace Period
                            row +=
                                `<td>${timetable.lateness_grace_period !== null ? timetable.lateness_grace_period + ' min' : 'N/A'}</td>`;
                        } else {
                            // No schedule for this day
                            row +=
                                '<td colspan="5" class="no-schedule">@lang('schedule.noSchedule')</td>';
                        }

                        row += '</tr>';
                        showScheduleBody.innerHTML += row;
                    });
                });
            });

        });
    </script>
@endsection
