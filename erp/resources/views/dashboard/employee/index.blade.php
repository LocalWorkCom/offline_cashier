@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <!-- Bootstrap CSS -->
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('employee.employees')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('employee.employees')</li>
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
                                <div class="col-md-12">
                                    <form action="{{ route('employees.list') }}" method="GET">
                                        <div class="row g-2 align-items-center">
                                            <!-- Existing search fields -->

                                            <div class="col-md-3">
                                                <p>@lang('employee.name')</p>
                                                <input type="text" name="name" class="form-control"
                                                    placeholder="@lang('employee.name_placeholder')" value="{{ request('name') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <p>@lang('employee.phone')</p>
                                                <input type="text" name="phone" class="form-control"
                                                    placeholder="@lang('employee.phone_placeholder')" value="{{ request('phone') }}">
                                            </div>

                                            <div class="col-md-3">
                                                <p>@lang('employee.id_placeholder')</p>
                                                <input type="text" name="employee_id" class="form-control"
                                                    placeholder="@lang('employee.id_placeholder')" value="{{ request('employee_id') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <p>@lang('employee.hire_date')</p>
                                                <input type="date" name="hire_date" class="form-control"
                                                    placeholder="@lang('employee.hire_date')" value="{{ request('hire_date') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <select name="status" class="form-control">
                                                    <option value="" placeholder="@lang('employee.status')">
                                                        @lang('employee.status')</option>
                                                    <option value="active"
                                                        {{ request('status') == 'active' ? 'selected' : '' }}>
                                                        @lang('employee.active')
                                                    </option>
                                                    <option value="not active"
                                                        {{ request('status') == 'not active' ? 'selected' : '' }}>
                                                        @lang('employee.not_active')
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select name="department_id" class="form-control">
                                                    <option value="" placeholder="@lang('employee.department_id')">
                                                        @lang('employee.department_id')</option>
                                                    @foreach ($departments as $department)
                                                        <option value="{{ $department->id }}"
                                                            {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                            {{ app()->getLocale() === 'ar' ? $department->name_ar : $department->name_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <select name="position_id" class="form-control"
                                                    placeholder="@lang('employee.position_id')">
                                                    <option value="">@lang('employee.position_id')</option>
                                                    @foreach ($positions as $position)
                                                        <option value="{{ $position->id }}"
                                                            {{ request('position_id') == $position->id ? 'selected' : '' }}>
                                                            {{ app()->getLocale() === 'ar' ? $position->name_ar : $position->name_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Action buttons -->
                                            <div class="col-md-3 d-flex gap-1">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fe fe-search"></i> @lang('employee.search')
                                                </button>
                                                <a href="{{ route('employees.list') }}" class="btn btn-danger">
                                                    <i class="fe fe-x"></i> @lang('employee.reset')
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                @if (auth('admin')->user()->hasPermissionTo('create employees', 'admin'))
                                    <a href="{{ route('employee.create') }}" type="button"
                                        class="btn btn-primary label-btn">
                                        <i class="fe fe-plus label-btn-icon me-2"></i>
                                        @lang('employee.addEmployee')
                                    </a>
                                @endif

                                <!-- Import Excel Button -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#importModal">
                                    @lang('employee.importExcel')
                                </button>

                                <!-- Import Modal -->
                                <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('employees.import') }}" method="POST"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="file" class="form-label">Upload Excel File</label>
                                                    <input type="file" name="file" id="file"
                                                        class="form-control" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Import</button>
                                            </form>

                                        </div>
                                    </div>
                                </div>

                            </div>


                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if ($errors->has('excel_file'))
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            var importExcelModal = new bootstrap.Modal(document.getElementById('importExcelModal'));
                                            importExcelModal.show();
                                        });
                                    </script>
                                @endif

                                <table class="table table-bordered text-nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th scope="col">@lang('employee.ID')</th>
                                            <th scope="col">@lang('employee.name')</th>
                                            <th scope="col">@lang('employee.code')</th>
                                            <th scope="col">@lang('employee.email')</th>
                                            <th scope="col">@lang('employee.phone')</th>
                                            <th scope="col">@lang('employee.national_id')</th>
                                            <th scope="col">@lang('employee.nationality')</th>
                                            <th scope="col">@lang('employee.department')</th>
                                            <th scope="col">@lang('employee.position')</th>
                                            <th scope="col">@lang('employee.supervisor')</th>
                                            <th scope="col">@lang('employee.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($employees as $employee)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td>{{ $employee->first_name . ' ' . $employee->last_name }}</td>
                                                <td>{{ $employee->employee_code }}</td>
                                                <td>{{ $employee->email }}</td>
                                                <td>{{ $employee->phone_number ?? '-----' }}</td>
                                                <td>{{ $employee->national_id }}</td>
                                                <td>{{ $employee->nationality ? (app()->getLocale() === 'ar' ? $employee->nationality->name_ar : $employee->nationality->name_en) : '-----' }}
                                                </td>
                                                <td>{{ $employee->department ? (app()->getLocale() === 'ar' ? $employee->department->name_ar : $employee->department->name_en) : '-----' }}
                                                </td>
                                                <td>{{ $employee->position ? (app()->getLocale() === 'ar' ? $employee->position->name_ar : $employee->position->name_en) : '-----' }}
                                                </td>
                                                <td>
                                                    @if ($employee->supervisor)
                                                        {{ $employee->supervisor->first_name . ' ' . $employee->supervisor->last_name . ' | ' . $employee->supervisor->employee_code }}
                                                    @else
                                                        {{ '-----' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (auth('admin')->user()->hasPermissionTo('view employees', 'admin'))
                                                        <!-- Show Button -->
                                                        <a href="{{ route('employee.show', $employee->id) }}"
                                                            class="btn btn-info-light btn-wave">
                                                            @lang('employee.show') <i class="ri-eye-line"></i>
                                                        </a>
                                                    @endif

                                                    @if (auth('admin')->user()->hasPermissionTo('update employees', 'admin'))
                                                        <!-- Edit Button -->
                                                        <a href="{{ route('employee.edit', $employee->id) }}"
                                                            class="btn btn-orange-light btn-wave">
                                                            @lang('employee.edit') <i class="ri-edit-line"></i>
                                                        </a>
                                                    @endif

                                                    @if (auth('admin')->user()->hasPermissionTo('delete employees', 'admin'))
                                                        <!-- Delete Button -->
                                                        <form class="d-inline" id="delete-form-{{ $employee->id }}"
                                                            action="{{ route('employee.delete', $employee->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-danger-light btn-wave"
                                                                onclick="delete_item({{ $employee->id }})">
                                                                @lang('employee.delete') <i class="ri-delete-bin-line"></i>
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- INTERNAL DATADABLES JS -->
        @vite('resources/assets/js/datatables.js')
    @endsection

    <script>
        function delete_item(id) {
            Swal.fire({
                title: "@lang('employee.warning')",
                text: "@lang('employee.deleteMsg')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "@lang('employee.yesDelete')",
                cancelButtonText: "@lang('employee.cancelDelete')",
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-${id}`).submit();
                }
            });
        }

        $(document).ready(function() {
            $('#file-export').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: ['print', 'excel', 'pdf'],
                pageLength: 10,
            });
        });
    </script>
