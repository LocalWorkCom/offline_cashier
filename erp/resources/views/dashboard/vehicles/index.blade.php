

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
        <h4 class="fw-medium mb-0">@lang('sidebar.vehicles')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('sidebar.vehicles')</li>
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
                                @lang('sidebar.vehicles')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create vehicles', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('vehicle_setting.vehicles_add')
                                </button>
                            @endif
                            <!-- Add country Modal -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('vehicles.store') }}" method="POST" class="needs-validation"
                                            novalidate>
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
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('vehicle_setting.vehicles_add')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <!-- Arabic Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="vehicle_type">@lang('employee.vehicle_type')</label>

                                                        <select name="vehicle_type" id="vehicle_type"
                                                            class="select2 form-control" required>
                                                            <option value="" selected disabled>
                                                                @lang('employee.choose_vehicle_type')</option>
                                                            @foreach (vehicles() as $vehicle)
                                                                <option value="{{ $vehicle->id }}">
                                                                    @lang('employee.' . $vehicle->vehicle_type)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>

                                                    <!-- English Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('vehicle_setting.vehicle_number')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('vehicle_setting.vehicle_number')" name="license"
                                                            value="{{ old('license') }}" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="employee">@lang('employee.employee')</label>
                                                        <select name="employee" id="employee" class="select2 form-control">
                                                            <option value="" selected disabled>@lang('employee.choose_employee')
                                                            </option>
                                                            @php
                                                                $branch_id = auth('admin')
                                                                    ->user()
                                                                    ->hasRole('Branch Manager')
                                                                    ? getBranchManagerID()
                                                                    : null;
                                                            @endphp

                                                            @foreach (drivers() as $employee)
                                                                @if (!$branch_id || $employee->branch_id == $branch_id)
                                                                    <option value="{{ $employee->id }}"
                                                                        @if (old('employee') == $employee->id) selected @endif>
                                                                        {{ $employee->first_name . ' ' . $employee->last_name }}
                                                                        {{-- @if ($employee->branch)
                                                                            ({{ $employee->branch->name }})
                                                                        @endif --}}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
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
                            <!-- Edit regions Modal -->
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
                                                <h6 class="modal-title" id="editModalLabel">@lang('vehicle_setting.vehicles_edit')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="row gy-4">
                                                <!-- Arabic Name Input -->
                                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                    <label for="vehicle_type">@lang('employee.vehicle_type')</label>

                                                    <select name="vehicle_type" id="edit-vehicle_type"
                                                        class="select2 form-control" required>
                                                        <option value="" selected disabled>
                                                            @lang('employee.choose_vehicle_type')</option>
                                                        @foreach (vehicles() as $vehicle)
                                                            <option value="{{ $vehicle->id }}">
                                                                @lang('employee.' . $vehicle->vehicle_type)

                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">@lang('validation.Correct')</div>
                                                    <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                </div>

                                                <!-- English Name Input -->
                                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                    <label for="input-placeholder"
                                                        class="form-label">@lang('vehicle_setting.vehicle_number')</label>
                                                    <input type="text" class="form-control" id="edit-vehicle_number"
                                                        placeholder="@lang('vehicle_setting.vehicle_number')" name="license"
                                                        value="{{ old('vehicle_number') }}" required>
                                                    <div class="valid-feedback">@lang('validation.Correct')</div>
                                                    <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                                </div>
                                                <!-- In your edit modal (replace the employee select options) -->
                                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                    <label for="employee">@lang('vehicle_setting.employee')</label>
                                                    <select name="employee" id="edit-employee"
                                                        class="select2 form-control">
                                                        <option value="" selected disabled>@lang('vehicle_setting.choose_employee')
                                                        </option>
                                                        @php
                                                            $branch_id = auth('admin')
                                                                ->user()
                                                                ->hasRole('Branch Manager')
                                                                ? getBranchManagerID()
                                                                : null;
                                                        @endphp

                                                        @foreach (drivers() as $employee)
                                                            @if (!$branch_id || $employee->branch_id == $branch_id)
                                                                <option value="{{ $employee->id }}">
                                                                    {{ $employee->first_name . ' ' . $employee->last_name }}
                                                                    {{-- @if ($employee->branch)
                                                                        ({{ $employee->branch->name }})
                                                                    @endif --}}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <div class="valid-feedback">@lang('validation.Correct')</div>
                                                    <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
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
                                        <th scope="col">@lang('vehicle_setting.type')</th>
                                        <th scope="col">@lang('vehicle_setting.license')</th>
                                        <th scope="col">@lang('vehicle_setting.employee')</th>
                                        <th scope="col">@lang('country.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vehicles as $vehicle)
                                        <tr>
                                            <td> @lang('employee.' . $vehicle->type->vehicle_type)</td>
                                            <td>{{ $vehicle->license }}</td>
                                            <td>{{ $vehicle->employee?->first_name . ' '. $vehicle->employee?->last_name}}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('update vehicles', 'admin'))
                                                    <!-- Edit Button -->
                                                    <button type="button"
                                                        class="btn btn-orange-light btn-wave edit-country-btn"
                                                        data-id="{{ $vehicle->id }}" data-type="{{$vehicle->vehicle_type }}"
                                                        data-license="{{ $vehicle->license }}" data-employee="{{ $vehicle->employee?->id }}"
                                                        data-route="{{ route('vehicles.update', ':id') }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('delete vehicles', 'admin'))
                                                    <!-- Delete Button -->
                                                    <button type="button" onclick="delete_item('{{ $vehicle->id }}')"
                                                        class="btn btn-danger-light btn-wave">
                                                        @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                    </button>
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

document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-country-btn');
    const editForm = document.getElementById('edit-country-form');
    const nameArInput = document.getElementById('edit-vehicle_type');
    const nameEnInput = document.getElementById('edit-vehicle_number');
    const employeeInput = document.getElementById('edit-employee');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get vehicle details from data attributes
            const vehicleId = this.getAttribute('data-id');
            const vehicleType = this.getAttribute('data-type');
            const vehicleNumber = this.getAttribute('data-license');
            const employeeName = this.getAttribute('data-employee');

            const routeTemplate = this.getAttribute('data-route');

            // Set form action URL dynamically
            const updateRoute = routeTemplate.replace(':id', vehicleId);
            editForm.action = updateRoute;

            // Populate the modal fields
            nameArInput.value = vehicleType;
            nameEnInput.value = vehicleNumber;
            employeeInput.value = employeeName;
        });
    });
});


        function delete_item(id) {
            Swal.fire({
                title: '{{ __('vehicle_setting.warning_title') }}',
                text: '{{ __('vehicle_setting.delete_confirmation') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('country.confirm_delete') }}',
                cancelButtonText: '{{ __('country.cancel') }}',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('vehicles.delete', ':id') }}'.replace(':id', id),
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
                                    title: '{{ __('vehicle_setting.delete_success') }}',
                                    showConfirmButton: false,
                                    timer: 1500
                                });

                                // Optionally refresh or update the UI
                                location.reload();
                            } else {
                                // Handle cases where the country cannot be deleted
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('vehicle_setting.delete_error') }}',
                                    text: response.message,
                                    showConfirmButton: true
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('cancellation_reasons.delete_error') }}',
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
    </script>
@endsection
