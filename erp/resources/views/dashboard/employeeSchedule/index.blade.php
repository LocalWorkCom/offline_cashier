@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- FLATPICKR CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
    <!-- CUSTOM CSS -->
    <style>
        .schedule-table th,
        .schedule-table td {
            vertical-align: middle;
            text-align: center;
        }

        .schedule-table .no-schedule {
            color: #6c757d;
            font-style: italic;
        }
    </style>
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('schedule.schedules')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('schedule.schedules')</li>
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
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">
                                @lang('schedule.schedules')
                            </div>
                            <div>
                                @if (auth('admin')->user()->hasPermissionTo('create timetables', 'admin'))
                                    <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                        data-bs-target="#createModal">
                                        <i class="fe fe-plus label-btn-icon me-2"></i>
                                        @lang('schedule.add')
                                    </button>
                                    <button type="button" class="btn btn-secondary label-btn" data-bs-toggle="modal"
                                        data-bs-target="#defaultScheduleModal">
                                        <i class="fe fe-calendar label-btn-icon me-2"></i>
                                        @lang('schedule.setDefaultSchedule')
                                    </button>
                                @endif
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
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('schedule.ID')</th>
                                        <th>@lang('schedule.employeeName')</th>
                                        <th>@lang('schedule.employeeCode')</th>
                                        <th>@lang('schedule.employeeFlag')</th>
                                        <th>@lang('schedule.shift')</th>
                                        <th>@lang('schedule.startDate')</th>
                                        <th>@lang('schedule.endDate')</th>
                                        <th>@lang('schedule.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($schedules as $schedule)
                                        <tr>
                                            <td>{{ $schedule->id }}</td>
                                            <td>{{ optional($schedule->employee)->first_name ?? 'N/A' }} {{ optional($schedule->employee)->last_name ?? '' }}</td>
                                            <td>{{ $schedule->employee->employee_code ?? 'N/A' }}</td>
                                            <td>{{ $schedule->employee->flag ?? 'N/A' }}</td>
                                            <td>{{ app()->getLocale() == 'ar' ? $schedule->shift->name_ar : $schedule->shift->name_en }}
                                            </td>
                                            <td>{{ $schedule->start_date }}</td>
                                            <td>{{ $schedule->end_date }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view timetables', 'admin'))
                                                    <button type="button" class="btn btn-info-light btn-wave show-btn"
                                                        data-id="{{ $schedule->id }}"
                                                        {{-- data-employee-name="{{ $schedule->employee->first_name . ' ' . $schedule->employee->last_name }}" --}}
                                                        data-employee-name="{{ optional($schedule->employee)->first_name . ' ' . optional($schedule->employee)->last_name }}"
                                                        data-shift-id="{{ $schedule->shift_id }}"
                                                        data-shift-name="{{ app()->getLocale() == 'ar' ? $schedule->shift->name_ar : $schedule->shift->name_en }}"
                                                        data-start-date="{{ $schedule->start_date }}"
                                                        data-end-date="{{ $schedule->end_date }}"
                                                        data-shift-details="{{ json_encode(
                                                            $schedule->shift->details->map(function ($detail) {
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
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('update timetables', 'admin'))
                                                    <button type="button" class="btn btn-orange-light btn-wave edit-btn"
                                                        data-id="{{ $schedule->id }}"
                                                        data-employee-id="{{ $schedule->employee_id }}"
                                                        data-shift-id="{{ $schedule->shift_id }}"
                                                        data-start-date="{{ $schedule->start_date }}"
                                                        data-end-date="{{ $schedule->end_date }}"
                                                        data-route="{{ route('employeeSchedule.update', ':id') }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        @lang('schedule.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('delete timetables', 'admin'))
                                                    <form class="d-inline" id="delete-form-{{ $schedule->id }}"
                                                        action="{{ route('employeeSchedule.delete', $schedule->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="deleteItem({{ $schedule->id }})"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('schedule.delete') <i class="ri-delete-bin-line"></i>
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

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('employeeSchedule.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
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
                    <div class="modal-header">
                        <h6 class="modal-title" id="createModalLabel">@lang('schedule.addSchedule')</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.employee')</label>
                                <select name="employee_id" class="form-control select2-employee" required>
                                    <option value="">@lang('schedule.selectEmployee')</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->first_name . ' ' . $employee->last_name }}
                                            ({{ $employee->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.selectEmployeeValidation')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.shift')</label>
                                <select name="shift_id" class="form-control select2-shift" required>
                                    <option value="">@lang('schedule.selectShift')</option>
                                    @foreach ($shifts as $shift)
                                        <option value="{{ $shift->id }}">
                                            {{ app()->getLocale() == 'ar' ? $shift->name_ar : $shift->name_en }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.selectShiftValidation')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.startDate')</label>
                                <input type="text" name="start_date" class="form-control datepicker" required>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.startDateValidation')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.endDate')</label>
                                <input type="text" name="end_date" class="form-control datepicker" required>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.endDateValidation')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">@lang('modal.close')</button>
                        <button type="submit" class="btn btn-outline-primary">@lang('modal.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Default Schedule Modal -->
    <div class="modal fade" id="defaultScheduleModal" tabindex="-1" aria-labelledby="defaultScheduleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('employeeSchedule.setDefault') }}" method="POST" class="needs-validation"
                    novalidate>
                    @csrf
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
                    <div class="modal-header">
                        <h6 class="modal-title" id="defaultScheduleModalLabel">@lang('schedule.setDefaultSchedule')</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.branches')</label>
                                <select name="branches[]" id="branches" class="form-control select2-multiple" multiple
                                    data-placeholder="@lang('schedule.selectBranches')">
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.departments')</label>
                                <select name="departments[]" id="departments" class="form-control select2-multiple"
                                    multiple data-placeholder="@lang('schedule.selectDepartments')">
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.positions')</label>
                                <select name="positions[]" id="positions" class="form-control select2-multiple" multiple
                                    data-placeholder="@lang('schedule.selectPositions')">
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                    @endforeach
                                </select>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.employees')</label>
                                <select name="employees[]" id="employees" class="form-control select2-multiple" multiple
                                    data-placeholder="@lang('schedule.selectEmployees')">
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->first_name . ' ' . $employee->last_name }}
                                            ({{ $employee->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.shift')</label>
                                <select name="shift_id" class="form-control select2-shift" required>
                                    <option value="">@lang('schedule.selectShift')</option>
                                    @foreach ($shifts as $shift)
                                        <option value="{{ $shift->id }}">
                                            {{ app()->getLocale() == 'ar' ? $shift->name_ar : $shift->name_en }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.selectShiftValidation')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.startDate')</label>
                                <input type="text" name="start_date" class="form-control datepicker" required>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.startDateValidation')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.endDate')</label>
                                <input type="text" name="end_date" class="form-control datepicker" required>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.endDateValidation')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">@lang('modal.close')</button>
                        <button type="submit" class="btn btn-outline-primary">@lang('modal.save')</button>
                    </div>
                </form>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-schedule-form" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
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
                    <div class="modal-header">
                        <h6 class="modal-title" id="editModalLabel">@lang('schedule.editSchedule')</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.employee')</label>
                                <select name="employee_id" id="edit-employee-id" class="form-control select2-employee"
                                    required>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->first_name . ' ' . $employee->last_name }}
                                            ({{ $employee->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.selectEmployeeValidation')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.shift')</label>
                                <select name="shift_id" id="edit-shift-id" class="form-control select2-shift" required>
                                    @foreach ($shifts as $shift)
                                        <option value="{{ $shift->id }}">
                                            {{ app()->getLocale() == 'ar' ? $shift->name_ar : $shift->name_en }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.selectShiftValidation')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.startDate')</label>
                                <input type="text" name="start_date" id="edit-start-date"
                                    class="form-control datepicker" required>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.startDateValidation')
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('schedule.endDate')</label>
                                <input type="text" name="end_date" id="edit-end-date" class="form-control datepicker"
                                    required>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('schedule.endDateValidation')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">@lang('modal.close')</button>
                        <button type="submit" class="btn btn-outline-primary">@lang('modal.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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

            // Edit Modal
            const editButtons = document.querySelectorAll('.edit-btn');
            const editForm = document.getElementById('edit-schedule-form');
            const editEmployeeId = document.getElementById('edit-employee-id');
            const editShiftId = document.getElementById('edit-shift-id');
            const editStartDate = document.getElementById('edit-start-date');
            const editEndDate = document.getElementById('edit-end-date');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const scheduleId = this.getAttribute('data-id');
                    const employeeId = this.getAttribute('data-employee-id');
                    const shiftId = this.getAttribute('data-shift-id');
                    const startDate = this.getAttribute('data-start-date');
                    const endDate = this.getAttribute('data-end-date');
                    const routeTemplate = this.getAttribute('data-route');

                    // Set form action dynamically
                    editForm.action = routeTemplate.replace(':id', scheduleId);

                    // Populate fields
                    $(editEmployeeId).val(employeeId).trigger('change');
                    $(editShiftId).val(shiftId).trigger('change');
                    editStartDate.value = startDate;
                    editEndDate.value = endDate;
                });
            });
        });

        function deleteItem(id) {
            Swal.fire({
                title: "@lang('schedule.warning')",
                text: "@lang('schedule.deleteMsg')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "@lang('schedule.yesDelete')",
                cancelButtonText: "@lang('schedule.cancelDelete')",
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        function validateSchedule(form, isEdit = false) {
            const employeeId = form.querySelector('[name="employee_id"]').value;
            const startDate = form.querySelector('[name="start_date"]').value;
            const endDate = form.querySelector('[name="end_date"]').value;
            const scheduleId = isEdit ? form.getAttribute('action').split('/').pop() : null;

            return new Promise((resolve) => {
                fetch('{{ route('employeeSchedule.checkConflict') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            employee_id: employeeId,
                            start_date: startDate,
                            end_date: endDate,
                            schedule_id: scheduleId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.conflict) {
                            Swal.fire({
                                title: "@lang('schedule.scheduleConflict')",
                                html: "@lang('schedule.scheduleConflictMessage')",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: "@lang('schedule.confirmOverride')",
                                cancelButtonText: "@lang('schedule.cancel')",
                                confirmButtonColor: '#3085d6'
                            }).then((result) => {
                                resolve(result.isConfirmed);
                            });
                        } else {
                            resolve(true);
                        }
                    })
                    .catch(error => {
                        console.error('Error checking schedule conflict:', error);
                        Swal.fire({
                            title: "@lang('schedule.error')",
                            text: "@lang('schedule.errorCheckingConflict')",
                            icon: 'error',
                            confirmButtonText: "@lang('modal.close')",
                        });
                        resolve(true); // Allow submission if check fails
                    });
            });
        }

        // Create form submission
        document.querySelector('#createModal form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;

            validateSchedule(form).then((proceed) => {
                if (proceed) {
                    form.submit();
                }
            });
        });

        // Edit form submission
        document.querySelector('#editModal form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;

            validateSchedule(form, true).then((proceed) => {
                if (proceed) {
                    form.submit();
                }
            });
        });

        // Default schedule form submission
        document.querySelector('#defaultScheduleModal form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const branches = form.querySelector('#branches').value;
            const departments = form.querySelector('#departments').value;
            const positions = form.querySelector('#positions').value;
            const employees = form.querySelector('#employees').value;

            if (!branches && !departments && !positions && !employees) {
                Swal.fire({
                    title: "@lang('schedule.noSelection')",
                    text: "@lang('schedule.noSelectionMessage')",
                    icon: 'error',
                    confirmButtonText: "@lang('modal.close')",
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            Swal.fire({
                title: "@lang('schedule.confirmDefaultSchedule')",
                text: "@lang('schedule.confirmDefaultScheduleMessage')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "@lang('schedule.confirm')",
                cancelButtonText: "@lang('schedule.cancel')",
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
@endsection
