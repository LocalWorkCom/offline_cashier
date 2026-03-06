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
        <h4 class="fw-medium mb-0">@lang('sidebar.cancellation_reasons')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('sidebar.cancellation_reasons')</li>
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
                                @lang('sidebar.cancellation_reasons')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create cancellation_reasons', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('cancellation_reasons.cancellation_reasonsadd')
                                </button>
                            @endif
                            <!-- Add country Modal -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('cancel_reasons.store') }}" method="POST"
                                            class="needs-validation" novalidate id="addReasonForm">
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
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('cancellation_reasons.cancellation_reasonsadd')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <!-- Arabic Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('cancellation_reasons.name_ar')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder=" @lang('cancellation_reasons.name_ar')" value="{{ old('reason_ar') }}"
                                                            name="reason_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>

                                                    <!-- English Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('cancellation_reasons.name_en')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('cancellation_reasons.name_en')" name="reason_en"
                                                            value="{{ old('reason_en') }}" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                                    </div>

                                                    <div class="col-xl-12">
                                                        <label class="form-label">@lang('cancellation_reasons.type')</label>
                                                        <div class="d-flex flex-wrap gap-3">
                                                            @foreach (['waiter', 'driver', 'client', 'branch manager'] as $type)
                                                                <div class="form-check">
                                                                    <input class="form-check-input type-checkbox"
                                                                        type="checkbox" name="type[]"
                                                                        value="{{ $type }}"
                                                                        id="type-{{ $type }}"
                                                                        @if (is_array(old('type')) && in_array($type, old('type'))) checked @endif
                                                                        data-required="true">
                                                                    <label class="form-check-label"
                                                                        for="edit-type-{{ $type }}">
                                                                        @lang('cancellation_reasons.' . $type)
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="invalid-feedback type-error" style="display: none;">
                                                            @lang('validation.SelectAtLeastOneType')</div>
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
                                                <h6 class="modal-title" id="editModalLabel">@lang('cancellation_reasons.cancellation_reasons_edit')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <!-- Arabic Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-ar"
                                                            class="form-label">@lang('cancellation_reasons.name_ar')</label>
                                                        <input type="text" id="edit-name-ar" class="form-control"
                                                            name="reason_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>

                                                    <!-- English Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-en"
                                                            class="form-label">@lang('cancellation_reasons.name_en')</label>
                                                        <input type="text" id="edit-name-en" class="form-control"
                                                            name="reason_en" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <label class="form-label">@lang('cancellation_reasons.type')</label>
                                                        <div class="d-flex flex-wrap gap-3">
                                                            @foreach (['waiter', 'driver', 'client', 'branch manager'] as $type)
                                                                <div class="form-check">
                                                                    <input class="form-check-input edit-type-checkbox"
                                                                        type="checkbox" name="type[]"
                                                                        value="{{ $type }}"
                                                                        id="edit-type-{{ $type }}">
                                                                    <label class="form-check-label"
                                                                        for="edit-type-{{ $type }}">
                                                                        @lang('cancellation_reasons.' . $type)
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="invalid-feedback edit-type-error"
                                                            style="display: none;">
                                                            @lang('validation.SelectAtLeastOneType')
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
                                        <th scope="col">@lang('cancellation_reasons.name_ar')</th>
                                        <th scope="col">@lang('cancellation_reasons.name_en')</th>
                                        <th scope="col">@lang('cancellation_reasons.type')</th>
                                        <th scope="col">@lang('country.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reasons as $reason)
                                        <tr>
                                            <td>{{ $reason->reason_ar }}</td>
                                            <td>{{ $reason->reason_en }}</td>
                                            <td>
                                                @foreach ($reason->type ?? [] as $type)
                                                    <span class="badge bg-primary">@lang('cancellation_reasons.' . $type)</span>
                                                @endforeach
                                            </td>
                                            <td>

                                                @if (auth('admin')->user()->hasPermissionTo('update cancellation_reasons', 'admin'))
                                                    <!-- Edit Button -->
                                                    <button type="button"
                                                        class="btn btn-orange-light btn-wave edit-country-btn"
                                                        data-id="{{ $reason->id }}"
                                                        data-name-ar="{{ $reason->reason_ar }}"
                                                        data-name-en="{{ $reason->reason_en }}"
                                                        data-types="{{ json_encode($reason->type ?? []) }}"
                                                        data-route="{{ route('cancel_reasons.update', ':id') }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('delete cancellation_reasons', 'admin'))
                                                    <!-- Delete Button -->
                                                    <button type="button" onclick="delete_item('{{ $reason->id }}')"
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
            const form = document.querySelector('form.needs-validation');
            const typeCheckboxes = document.querySelectorAll('.type-checkbox');
            const typeError = document.querySelector('.type-error');

            // Validate checkboxes on form submission
            form.addEventListener('submit', function(e) {
                let atLeastOneChecked = false;
                typeCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        atLeastOneChecked = true;
                    }
                });

                if (!atLeastOneChecked) {
                    e.preventDefault();
                    typeError.style.display = 'block';

                    // Add Bootstrap's is-invalid class to the container for styling
                    typeError.closest('.col-xl-12').classList.add('was-validated');
                } else {
                    typeError.style.display = 'none';
                }

                // Bootstrap's native validation
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                form.classList.add('was-validated');
            });

            // Validate checkboxes when user interacts with them
            typeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    let atLeastOneChecked = false;
                    typeCheckboxes.forEach(cb => {
                        if (cb.checked) {
                            atLeastOneChecked = true;
                        }
                    });

                    if (atLeastOneChecked) {
                        typeError.style.display = 'none';
                        typeError.closest('.col-xl-12').classList.remove('was-validated');
                    }
                });
            });
        });
    </script>
    <script>
        // Add this script to your edit modal validation
        document.addEventListener('DOMContentLoaded', function() {
            // Edit button click handler
            document.querySelectorAll('.edit-country-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const reasonId = this.getAttribute('data-id');
                    const nameAr = this.getAttribute('data-name-ar');
                    const nameEn = this.getAttribute('data-name-en');
                    const types = JSON.parse(this.getAttribute('data-types') || '[]');
                    const route = this.getAttribute('data-route').replace(':id', reasonId);

                    // Set form action
                    const editForm = document.getElementById('edit-country-form');
                    editForm.action = route;

                    // Set input values
                    document.getElementById('edit-name-ar').value = nameAr;
                    document.getElementById('edit-name-en').value = nameEn;

                    // Clear all checkboxes first
                    document.querySelectorAll('.edit-type-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });

                    // Check the appropriate checkboxes based on types
                    types.forEach(type => {
                        const checkbox = document.querySelector(
                            `.edit-type-checkbox[value="${type}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });
                });
            });

            // Edit form validation
            const editForm = document.getElementById('edit-country-form');
            const editTypeCheckboxes = document.querySelectorAll('.edit-type-checkbox');
            const editTypeError = document.querySelector('.edit-type-error');

            editForm.addEventListener('submit', function(e) {
                let atLeastOneChecked = false;
                editTypeCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        atLeastOneChecked = true;
                    }
                });

                if (!atLeastOneChecked) {
                    e.preventDefault();
                    editTypeError.style.display = 'block';
                    editTypeError.closest('.col-xl-12').classList.add('was-validated');
                } else {
                    editTypeError.style.display = 'none';
                }

                if (!editForm.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                editForm.classList.add('was-validated');
            });

            editTypeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    let atLeastOneChecked = false;
                    editTypeCheckboxes.forEach(cb => {
                        if (cb.checked) {
                            atLeastOneChecked = true;
                        }
                    });

                    if (atLeastOneChecked) {
                        editTypeError.style.display = 'none';
                        editTypeError.closest('.col-xl-12').classList.remove('was-validated');
                    }
                });
            });
        });

        function delete_item(id) {
            Swal.fire({
                title: '{{ __('cancellation_reasons.warning_title') }}',
                text: '{{ __('cancellation_reasons.delete_confirmation') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('country.confirm_delete') }}',
                cancelButtonText: '{{ __('country.cancel') }}',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('cancel_reasons.delete', ':id') }}'.replace(':id', id),
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
                                    title: '{{ __('cancellation_reasons.delete_success') }}',
                                    showConfirmButton: false,
                                    timer: 1500
                                });

                                // Optionally refresh or update the UI
                                location.reload();
                            } else {
                                // Handle cases where the country cannot be deleted
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('cancellation_reasons.delete_error') }}',
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
