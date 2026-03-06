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
        <h4 class="fw-medium mb-0">@lang('mashiens.cashier_machines')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('mashiens.cashier_machines')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">
                                @lang('mashiens.allcashier_machines')
                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('create cashier_machines', 'admin'))
                                <a data-bs-toggle="modal" data-bs-target="#exampleModal" class="btn btn-primary label-btn">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('mashiens.addcashier_machines')
                                </a>
                            @endif
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
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>@lang('mashiens.NameArabic')</th>
                                        <th>@lang('mashiens.NameEnglish')</th>
                                        <th>@lang('mashiens.machienId')</th>
                                        <th>@lang('mashiens.branch')</th>
                                        <th>@lang('mashiens.date')</th>
                                        <th>@lang('mashiens.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mashiens as $mashien)
                                        <tr>
                                            <td>{{ $mashien->id }}</td>
                                            <td>{{ $mashien->name_ar }}</td>
                                            <td>{{ $mashien->name_en }}</td>
                                            <td>{{ $mashien->device_id }}</td>
                                            <td>{{ $mashien->branches->getNameAttribute2() }}</td>
                                            <td>{{ $mashien->date }}</td>

                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view cashier_machines', 'admin'))
                                                    <a class="btn btn-info btn-wave show-country-btn"
                                                        data-id="{{ $mashien->id }}"
                                                        data-name-ar="{{ $mashien->name_ar }}"
                                                        data-name-en="{{ $mashien->name_en }}"
                                                        data-device_id="{{ $mashien->device_id }}"
                                                        data-employees='@json(
                                                            $mashien->employees
                                                                ? $mashien->employees->map(function ($emp) {
                                                                    return ['full_name' => $emp->first_name . ' ' . $emp->last_name];
                                                                })
                                                                : []
                                                        )'
                                                        data-branch_id="{{ $mashien->branch_id }}"
                                                        data-date="{{ $mashien->date }}" data-bs-toggle="modal"
                                                        data-bs-target="#showModal">
                                                        @lang('category.show') <i class="ri-eye-line"></i>

                                                    </a>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('update cashier_machines', 'admin'))
                                                    <!-- Edit -->
                                                    <button type="submit"
                                                        class="btn btn-orange-light btn-wave edit-country-btn"
                                                        data-id="{{ $mashien->id }}"
                                                        data-name-ar="{{ $mashien->name_ar }}"
                                                        data-name-en="{{ $mashien->name_en }}"
                                                        data-device_id="{{ $mashien->device_id }}"
                                                        data-branch_id="{{ $mashien->branch_id }}"
                                                        data-date="{{ $mashien->date }}"
                                                        data-route="{{ route('dashboard.cashierMachines.update', $mashien->id) }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif

                                                <!-- Delete -->
                                                @if (auth('admin')->user()->hasPermissionTo('delete cashier_machines', 'admin'))
                                                    <button type="button" onclick="delete_item('{{ $mashien->id }}')"
                                                        class="btn btn-danger-light btn-wave">
                                                        @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                @endif

                                                {{-- Route::delete('/{id}', [mashienController::class, 'destroy'])->name('dashboard.mashiens.destroy'); --}}
                                                {{-- @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))('restore cashier_machines')
                                                    <!-- Restore -->
                                                    @if ($mashien->trashed())
                                                        <form
                                                            action="{{ route('dashboard.cashierMachines.restore', $mashien->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success-light">
                                                                @lang('mashiens.Restore') <i class="ri-refresh-line"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan --}}

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row -->
        </div>
    </div>


    <!-- Add country Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('dashboard.cashierMachines.store') }}" method="POST" class="needs-validation"
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
                        <h6 class="modal-title" id="exampleModalLabel1">@lang('mashiens.addcashier_machines')</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label for="input-placeholder" class="form-label">@lang('mashiens.ArabicName')</label>
                                <input type="text" class="form-control" placeholder="@lang('mashiens.ArabicName')"
                                    value="{{ old('name_ar') }}" name="name_ar" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                            </div>

                            <!-- English Name Input -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label for="input-placeholder" class="form-label">@lang('mashiens.EnglishName')</label>
                                <input type="text" class="form-control" placeholder="@lang('mashiens.EnglishName')"
                                    name="name_en" value="{{ old('name_en') }}" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                            </div>

                            <!-- device id Input -->

                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label for="add-device_id" class="form-label">@lang('mashiens.deviceid')</label>
                                <input type="text" id="add-device_id" class="form-control" name="device_id"
                                    value="{{ old('device_id') }}" required placeholder="@lang('mashiens.deviceid')">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.deviceid')</div>
                            </div>
                            <!-- date Input -->

                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label for="add-date" class="form-label">@lang('mashiens.date')</label>
                                <input type="date" id="add-date" class="form-control" name="date"
                                    value="{{ old('date') }}" required placeholder="@lang('mashiens.date')">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.date')</div>
                            </div>
                            <!-- branch Input -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <p class="form-label">@lang('mashiens.branch')</p>
                                <select name="branch_id" id="branch" class="form-control">
                                    <option value="" selected disabled>@lang('mashiens.selectBranch')</option>
                                    @foreach ($Branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->getNameAttribute2() }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">@lang('validation.branch_id')</div>
                            </div>

                            <!-- Change the employee container to this: -->
                            <div class="col-12 d-none" id="employee-container">
                                <label class="form-label">@lang('mashiens.assign_employees')</label>
                                <div class="form-group" id="add-employee-container">
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
    <!-- Edit country Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-country-form" action="" method="POST" class="needs-validation" novalidate>
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
                        <h6 class="modal-title" id="editModalLabel">@lang('country.Edit')</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label for="input-placeholder" class="form-label">@lang('mashiens.ArabicName')</label>
                                <input type="text" class="form-control" placeholder="@lang('mashiens.ArabicName')"
                                    value="{{ old('name_ar') }}" id="edit-name-ar" name="name_ar" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                            </div>

                            <!-- English Name Input -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label for="input-placeholder" class="form-label">@lang('mashiens.EnglishName')</label>
                                <input type="text" class="form-control" placeholder="@lang('mashiens.EnglishName')"
                                    name="name_en" id="edit-name-en" value="{{ old('name_en') }}" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                            </div>

                            <!-- device id Input -->

                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label for="add-device_id" class="form-label">@lang('mashiens.deviceid')</label>
                                <input type="text" id="edit-device_id" class="form-control" name="device_id"
                                    value="{{ old('device_id') }}" required placeholder="@lang('mashiens.deviceid')">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.deviceid')</div>
                            </div>
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <label for="add-date" class="form-label">@lang('mashiens.date')</label>
                                <input type="date" id="edit-date" class="form-control" name="date"
                                    value="{{ old('date', $cashierMachine->date ?? '') }}" required
                                    placeholder="@lang('mashiens.date')">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.date')</div>
                            </div>

                            <!-- branch Input -->
                            <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                <p class="form-label">@lang('mashiens.branch')</p>
                                <select name="branch_id" id="edit-branch" class="form-control" required>
                                    <option value="" selected disabled>@lang('mashiens.selectBranch')</option>
                                    @foreach ($Branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->getNameAttribute2() }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">@lang('validation.branch_id')</div>
                            </div>

                            <!-- Employee Selection -->
                            <div class="col-12">
                                <label class="form-label">@lang('mashiens.assign_employees')</label>
                                <div class="form-group" id="employee-checkboxes">
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
    <!-- Show country Modal -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="showModalLabel">@lang('mashiens.View')</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-4">
                        <!-- Arabic Name -->
                        <div class="col-xl-6">
                            <label class="form-label">@lang('mashiens.ArabicName')</label>
                            <p id="show-name-ar" class="form-control-static"></p>
                        </div>

                        <!-- English Name -->
                        <div class="col-xl-6">
                            <label class="form-label">@lang('mashiens.EnglishName')</label>
                            <p id="show-name-en" class="form-control-static"></p>
                        </div>

                        <!-- Arabic Currency -->
                        <div class="col-xl-6">
                            <label class="form-label">@lang('mashiens.deviceid')</label>
                            <p id="show-device_id" class="form-control-static"></p>
                        </div>
                        <!-- English Currency -->
                        <div class="col-xl-6">
                            <label class="form-label">@lang('mashiens.branch')</label>
                            <p id="show-branch_id" class="form-control-static"></p>
                        </div>

                        <!-- Code -->
                        <div class="col-xl-6">
                            <label class="form-label">@lang('mashiens.date')</label>
                            <p id="show-date" class="form-control-static"></p>
                        </div>
                        <!-- Assigned Employees -->
                        <div class="col-12">
                            <label class="form-label">@lang('mashiens.assigned_cashiers')</label>
                            <ul id="show-employees" class="list-group"></ul>
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
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#branch').on('change', function() {
                let branchId = $(this).val();

                if (branchId) {
                    $('#employee-container').removeClass('d-none'); // Show container

                    $.ajax({
                        url: '{{ route('dashboard.cashierMachines.cashier.getEmployeesByBranch') }}', // make sure you create this route
                        type: 'GET',
                        data: {
                            branch_id: branchId
                        },
                        success: function(response) {
                            let container = $('#add-employee-container');
                            container.empty(); // clear previous results

                            if (response.data.length > 0) {
                                response.data.forEach(function(employee) {
                                    container.append(`
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="employee_ids[]" value="${employee.id}" id="emp-${employee.id}">
                                    <label class="form-check-label" for="emp-${employee.id}">
                                        ${employee.first_name} ${employee.last_name}
                                    </label>
                                </div>
                            `);
                                });
                            } else {
                                container.append(
                                    '<p class="text-muted"> @lang('mashiens.no_cashiers_available_for_branch').</p>'
                                );
                            }
                        },
                        error: function() {
                            alert('Error fetching employees. Please try again.');
                        }
                    });
                }
            });
        });
        $(document).on('click', '.edit-country-btn', function() {
            let button = $(this);

            let id = button.data('id');
            let nameAr = button.data('name-ar');
            let nameEn = button.data('name-en');
            let deviceId = button.data('device_id');
            let branchId = button.data('branch_id');
            let date = button.data('date');
            let route = button.data('route');

            // Fill the form fields
            $('#edit-name-ar').val(nameAr);
            $('#edit-name-en').val(nameEn);
            $('#edit-device_id').val(deviceId);
            $('#edit-date').val(date);
            $('#edit-country-form').attr('action', route);

            // Set branch selection
            $('#edit-branch').val(branchId).trigger('change');

            // Fetch Employees for this branch and machine
            $.ajax({
                url: '{{ route('dashboard.cashierMachines.cashier.getEmployeesForEdit') }}',
                type: 'GET',
                data: {
                    branch_id: branchId,
                    machine_id: id
                },
                success: function(response) {
                    let container = $('#employee-checkboxes');
                    container.empty();

                    if (response.data.assigned.length > 0 || response.data.unassigned.length > 0) {
                        // Add assigned cashiers section
                        if (response.data.assigned.length > 0) {
                            response.data.assigned.forEach(function(cashier) {
                                container.append(`
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="employees[]"
                                       value="${cashier.id}"
                                       id="emp-${cashier.id}" checked>
                                <label class="form-check-label" for="emp-${cashier.id}">
                                    ${cashier.name}
                                </label>
                            </div>
                        `);
                            });
                        }

                        // Add unassigned cashiers section
                        // if (response.data.unassigned.length > 0) {
                        response.data.unassigned.forEach(function(cashier) {
                            console.log(0);

                            container.append(`
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="employees[]"
                                       value="${cashier.id}"
                                       id="emp-${cashier.id}">
                                <label class="form-check-label" for="emp-${cashier.id}">
                                    ${cashier.name}
                                </label>
                            </div>
                        `);
                        });
                        // }
                    } else {
                        container.append('<p class="text-muted">@lang('mashiens.no_cashiers_available')</p>');
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Error fetching cashier list.');
                }
            });
        });
        $(document).on('click', '.show-country-btn', function() {
            let button = $(this);

            // Basic machine info
            $('#show-name-ar').text(button.data('name-ar'));
            $('#show-name-en').text(button.data('name-en'));
            $('#show-device_id').text(button.data('device_id'));
            $('#show-branch_id').text(button.data('branch_id'));
            $('#show-date').text(button.data('date'));

            // Assigned employees
            let employees = button.data('employees'); // now a clean array of objects

            let employeeList = $('#show-employees');
            employeeList.empty();

            if (employees.length > 0) {
                employees.forEach(function(emp) {
                    employeeList.append(`<li class="list-group-item">${emp.full_name}</li>`);
                });
            } else {
                employeeList.append(`<li class="list-group-item text-muted">@lang('mashiens.no_cashiers_assigned')</li>`);
            }


        });

        // Delete function
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
                        url: '{{ route('dashboard.cashierMachines.delete', ':id') }}'.replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '{{ __('mashiens.delete_success') }}',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('mashiens.delete_error') }}',
                                    text: response.message || '',
                                    showConfirmButton: true
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('mashiens.delete_error') }}',
                                text: xhr.responseJSON?.message || '',
                                showConfirmButton: true
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
