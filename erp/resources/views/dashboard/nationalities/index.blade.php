@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .action-buttons .btn {
            margin: 2px;
        }
    </style>
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('nationality.Nationalities')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('nationality.Nationalities')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                @lang('nationality.Nationalities')
                            </div>

                            @if (auth('admin')->user()->hasPermissionTo('create nationalities', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#createModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('nationality.AddNationality')
                                </button>
                            @endif
                        </div>

                        <div class="card-body">

                            <table id="nationalities-table" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('category.ID')</th>
                                        <th>@lang('nationality.Name')</th>
                                        <th>@lang('nationality.employees_num')</th>

                                        <th>@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nationalities as $nationality)
                                        <tr>
                                            <td>{{ $nationality->id }}</td>
                                            <td>{{ $nationality->getNameSiteAttribute() }}</td>

                                            <td>
                                                <button class="btn btn-link text-primary p-0 employee-count-btn"
                                                    data-id="{{ $nationality->id }}"
                                                    data-names="{{ $nationality->employees->map(fn($e) => $e->first_name . ' ' . $e->last_name)->implode(',') }}">
                                                    {{ $nationality->employees->count() }}
                                                </button>
                                            </td>

                                            <td class="action-buttons">
                                                @if (auth('admin')->user()->hasPermissionTo('view nationalities', 'admin'))
                                                    <button class="btn btn-info-light btn-wave show-btn"
                                                        data-id="{{ $nationality->id }}"
                                                        data-name-ar="{{ $nationality->name_ar }}"
                                                        data-name-en="{{ $nationality->name_en }}">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </button>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update nationalities', 'admin'))
                                                    <button class="btn btn-warning-light btn-wave edit-btn"
                                                        data-id="{{ $nationality->id }}"
                                                        data-name-ar="{{ $nationality->name_ar }}"
                                                        data-name-en="{{ $nationality->name_en }}">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete nationalities', 'admin'))
                                                    <form class="d-inline"
                                                        action="{{ route('nationality.delete', $nationality->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-danger-light btn-wave delete-btn">
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
        </div>
    </div>
    <!-- employees  Modal -->
    <div class="modal fade" id="employeesModal" tabindex="-1" aria-labelledby="employeesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeesModalLabel">@lang('nationality.employees_list')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="@lang('category.close')"></button>
                </div>
                <div class="modal-body">
                    <ul id="employeesList" class="list-group">
                        <!-- List of employees will be inserted here -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('nationality.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">@lang('nationality.AddNationality')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-12">
                                <label for="name_ar" class="form-label">@lang('nationality.name_ar')</label>
                                <input type="text" class="form-control" id="name_ar" name="name_ar" required>
                                <div class="invalid-feedback">@lang('validation.Entername_ar')</div>
                            </div>
                            <div class="col-12">
                                <label for="name_en" class="form-label">@lang('nationality.name_en')</label>
                                <input type="text" class="form-control" id="name_en" name="name_en" required>
                                <div class="invalid-feedback">@lang('validation.Entername_en')</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">@lang('modal.close')</button>
                        <button type="submit" class="btn btn-primary">@lang('modal.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">@lang('nationality.EditNationality')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-12">
                                <label for="edit-name_ar" class="form-label">@lang('nationality.name_ar')</label>
                                <input type="text" class="form-control" id="edit-name_ar" name="name_ar" required>
                                <div class="invalid-feedback">@lang('validation.Entername_ar')</div>
                            </div>
                            <div class="col-12">
                                <label for="edit-name_en" class="form-label">@lang('nationality.name_en')</label>
                                <input type="text" class="form-control" id="edit-name_en" name="name_en" required>
                                <div class="invalid-feedback">@lang('validation.Entername_en')</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">@lang('modal.close')</button>
                        <button type="submit" class="btn btn-primary">@lang('modal.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Show Modal -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showModalLabel">@lang('nationality.NationalityDetails')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-4">
                        <div class="col-12">
                            <label class="form-label">@lang('nationality.name_ar')</label>
                            <p id="show-name_ar" class="form-control-plaintext"></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">@lang('nationality.name_en')</label>
                            <p id="show-name_en" class="form-control-plaintext"></p>
                        </div>
                    </div>
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
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>

    <!-- DATA-TABLES CDN -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#nationalities-table').DataTable({
                responsive: true,
                language: {
                    url: "{{ asset('lang/' . app()->getLocale() . '/datatable.json') }}"
                }
            });

            // Show modal handler
            $('.show-btn').click(function() {
                const id = $(this).data('id');
                const nameAr = $(this).data('name-ar');
                const nameEn = $(this).data('name-en');

                $('#show-name_ar').text(nameAr);
                $('#show-name_en').text(nameEn);

                $('#showModal').modal('show');
            });

            // Edit modal handler
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const nameAr = $(this).data('name-ar');
                const nameEn = $(this).data('name-en');

                $('#edit-name_ar').val(nameAr);
                $('#edit-name_en').val(nameEn);

                // Set form action URL
                $('#editForm').attr('action', '{{ route('nationality.update', ':id') }}'.replace(':id',
                    id));

                $('#editModal').modal('show');
            });

            // Delete confirmation
            $('.delete-btn').click(function(e) {
                e.preventDefault();
                const form = $(this).closest('form');

                Swal.fire({
                    title: '@lang('nationality.confirm_delete_title')',
                    text: '@lang('nationality.confirm_delete_text')',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '@lang('nationality.confirm_delete_confirm')',
                    cancelButtonText: '@lang('nationality.confirm_delete_cancel')'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Form validation
            (function() {
                'use strict';
                var forms = document.querySelectorAll('.needs-validation');

                Array.prototype.slice.call(forms)
                    .forEach(function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault();
                                event.stopPropagation();
                            }

                            form.classList.add('was-validated');
                        }, false);
                    });
            })();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.employee-count-btn');
            const modal = new bootstrap.Modal(document.getElementById('employeesModal'));
            const employeesList = document.getElementById('employeesList');

            buttons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const names = this.dataset.names.split(',');

                    // Clear previous list
                    employeesList.innerHTML = '';

                    // Append new list items
                    names.forEach(name => {
                        const li = document.createElement('li');
                        li.classList.add('list-group-item');
                        li.textContent = name;
                        employeesList.appendChild(li);
                    });

                    // Show the modal
                    modal.show();
                });
            });
        });
    </script>
@endsection
