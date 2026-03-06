@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .university-logo {
            max-width: 50px;
            max-height: 50px;
            border-radius: 50%;
        }
    </style>
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('university.universities')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);"
                            onclick="window.location.href='{{ route('university.list') }}'">@lang('university.universities')</a>
                    </li>
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
                                @lang('university.universities')
                            </div>

                            <h5>@lang('university.TotalUniversities'): {{ $TotalUniversity }}</h5>

                            @if (auth('admin')->user()->hasPermissionTo('create university', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#createModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('university.AddUniversity')
                                </button>
                            @endif

                            <!-- Create Modal -->
                            <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form action="{{ route('university.store') }}" method="POST"
                                            class="needs-validation" novalidate enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="createModalLabel">@lang('university.AddUniversity')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <!-- Modify the create modal body -->
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <label for="country_id" class="form-label">@lang('university.Country')</label>
                                                        <select class="form-control" id="country_id" name="country_id"
                                                            required>
                                                            <option value="">@lang('validation.SelectCountry')</option>
                                                            @foreach ($countries as $country)
                                                                <option value="{{ $country->id }}">{{ $country->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="universities-container">
                                                    <div class="university-entry card mb-3">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">@lang('university.ArabicName')</label>
                                                                    <input type="text" class="form-control"
                                                                        name="universities[0][name_ar]" required>
                                                                    <div class="valid-feedback">
                                                                        @lang('validation.Correct')
                                                                    </div>
                                                                    <div class="invalid-feedback">
                                                                        @lang('validation.EnterEnglishName')
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">@lang('university.EnglishName')</label>
                                                                    <input type="text" class="form-control"
                                                                        name="universities[0][name_en]" required>
                                                                    <div class="valid-feedback">
                                                                        @lang('validation.Correct')
                                                                    </div>
                                                                    <div class="invalid-feedback">
                                                                        @lang('validation.EnterEnglishName')
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 mt-2">
                                                                    <label class="form-label">@lang('university.Logo')</label>
                                                                    <input type="file" class="form-control"
                                                                        name="universities[0][logo]" accept="image/*"
                                                                        required>
                                                                    <div class="valid-feedback">
                                                                        @lang('validation.Correct')
                                                                    </div>
                                                                    <div class="invalid-feedback">
                                                                        @lang('validation.EnterImage')
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="button" class="btn btn-secondary mt-3"
                                                    id="add-more-universities">
                                                    <i class="fe fe-plus"></i> @lang('university.AddAnotherUniversity')
                                                </button>
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

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form id="edit-university-form" action="" method="POST"
                                            class="needs-validation" novalidate enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="editModalLabel">@lang('university.EditUniversity')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Main University Entry -->
                                                <div class="row mb-3">

                                                    <div class="col-12">
                                                        <label for="edit-country-id"
                                                            class="form-label">@lang('university.Country')</label>
                                                        <select class="form-control" id="edit-country-id"
                                                            name="country_id" required>
                                                            <option value="">@lang('validation.SelectCountry')</option>
                                                            @foreach ($countries as $country)
                                                                <option value="{{ $country->id }}">{{ $country->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-xl-6">
                                                        <label for="edit-name-ar"
                                                            class="form-label">@lang('university.ArabicName')</label>
                                                        <input type="text" id="edit-name-ar" class="form-control"
                                                            name="name_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label for="edit-name-en"
                                                            class="form-label">@lang('university.EnglishName')</label>
                                                        <input type="text" id="edit-name-en" class="form-control"
                                                            name="name_en" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <label for="edit-logo"
                                                            class="form-label">@lang('university.Logo')</label>
                                                        <input type="file" class="form-control" id="edit-logo"
                                                            name="logo" accept="image/*">
                                                        <div class="invalid-feedback">@lang('validation.EnterImage')</div>
                                                        <div class="mt-2">
                                                            <img id="current-logo" src="" alt="Current Logo"
                                                                class="university-logo">
                                                        </div>
                                                    </div>
                                                    
                                                </div>

                                                <!-- Dynamic University Entries Container -->
                                                <!-- <div id="edit-universities-container"></div>

                                           <div class="col-md-12">
                                                    <button type="button" class="btn btn-secondary mt-3"
                                                        id="edit-add-more-universities">
                                                        <i class="fe fe-plus"></i> @lang('university.AddAnotherUniversity')

                                                    </button>
                                                </div>  -->


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

                            <!-- Show Modal -->
                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('university.ShowUniversity')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('university.ArabicName')</label>
                                                    <p id="show-name-ar" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('university.EnglishName')</label>
                                                    <p id="show-name-en" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('university.Country')</label>
                                                    <p id="show-country" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('university.Logo')</label>
                                                    <img id="show-logo" width="100" height="50" src=""
                                                        alt=" @lang('university.University Logo')">
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
                        </div>
                        <div class="card-body">
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
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
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('university.ID')</th>
                                        <th scope="col">@lang('university.Logo')</th>
                                        <th scope="col">@lang('university.ArabicName')</th>
                                        <th scope="col">@lang('university.EnglishName')</th>
                                        <th scope="col">@lang('university.Country')</th>
                                        <th scope="col">@lang('university.EmployeesCount')</th>
                                        <th scope="col">@lang('university.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($university as $uni)
                                        <tr>
                                            <td>{{ $uni->id }}</td>
                                            <td>
                                                <img src="{{ $uni->logo }}" alt=" @lang('university.Bank Logo')" width="100"
                                                    height="50">
                                            </td>
                                            <td>{{ $uni->name_ar }}</td>
                                            <td>{{ $uni->name_en }}</td>
                                            <td>{{ $uni->country ? $uni->country->name_site : __('category.none') }}</td>
                                            <td>
                                                <a href="{{ route('employees.list', ['university_id' => $uni->id]) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    {{ $uni->employees_count }}
                                                </a>
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view university', 'admin'))
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-info-light btn-wave show-university-btn"
                                                        data-id="{{ $uni->id }}" data-name-ar="{{ $uni->name_ar }}"
                                                        data-name-en="{{ $uni->name_en }}"
                                                        data-country="{{ $uni->country ? $uni->country->name_site : __('category.none') }}"
                                                        data-logo="{{ $uni->logo }}" data-bs-toggle="modal"
                                                        data-bs-target="#showModal">
                                                        @lang('university.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('update university', 'admin'))
                                                    <button type="button"
                                                        class="btn btn-orange-light btn-wave edit-university-btn"
                                                        data-id="{{ $uni->id }}" data-name-ar="{{ $uni->name_ar }}"
                                                        data-name-en="{{ $uni->name_en }}"
                                                        data-logo="{{ $uni->logo }}"
                                                        data-country-id="{{ $uni->country_id }}"
                                                        data-action="{{ route('university.update', $uni->id) }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        @lang('university.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('delete university', 'admin'))
                                                    <form class="d-inline" id="delete-form-{{ $uni->id }}"
                                                        action="{{ route('university.delete', $uni->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            onclick="delete_item('{{ $uni->id }}')"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('university.delete') <i class="ri-delete-bin-line"></i>
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

    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== CREATE MODAL =====
            let createEntryCount = 1;

            // Add more universities in create modal
            document.getElementById('add-more-universities')?.addEventListener('click', function() {
                const container = document.querySelector('.universities-container');
                const newEntry = document.createElement('div');
                newEntry.className = 'university-entry card mb-3';
                newEntry.innerHTML = `
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">@lang('university.ArabicName')</label>
                                <input type="text" class="form-control" name="universities[${createEntryCount}][name_ar]" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">@lang('university.EnglishName')</label>
                                <input type="text" class="form-control" name="universities[${createEntryCount}][name_en]" required>
                            </div>
                            <div class="col-md-12 mt-2">
                                <label class="form-label">@lang('university.Logo')</label>
                                <input type="file" class="form-control" name="universities[${createEntryCount}][logo]" accept="image/*" required>
                            </div>
                            <div class="col-md-12 text-end mt-2">
                                <button type="button" class="btn btn-sm btn-danger remove-university">
                                    <i class="fe fe-trash"></i> @lang('university.Remove')
                                </button>
                            </div>
                        </div>
                    </div>`;
                container.appendChild(newEntry);
                createEntryCount++;

                // Add remove functionality
                newEntry.querySelector('.remove-university').addEventListener('click', function() {
                    this.closest('.university-entry').remove();
                });
            });

            // ===== EDIT MODAL =====
            let editEntryCount = 0;

            // Edit Modal - Populate Data
            document.querySelectorAll('.edit-university-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Get data from button attributes
                    const id = this.getAttribute('data-id');
                    const nameAr = this.getAttribute('data-name-ar');
                    const nameEn = this.getAttribute('data-name-en');
                    const countryId = this.getAttribute('data-country-id');
                    const logo = this.getAttribute('data-logo');
                    const action = this.getAttribute('data-action');

                    // Set form action and main fields
                    document.getElementById('edit-university-form').action = action;
                    document.getElementById('edit-name-ar').value = nameAr;
                    document.getElementById('edit-name-en').value = nameEn;
                    document.getElementById('edit-country-id').value = countryId;

                    // Set logo preview
                    const logoElement = document.getElementById('current-logo');
                    if (logo) {
                        logoElement.src = logo;
                        logoElement.style.display = 'block';
                    } else {
                        logoElement.style.display = 'none';
                    }

                    // Clear any existing dynamic entries
                    const container = document.getElementById('edit-universities-container');
                    container.innerHTML = '';
                    editEntryCount = 0;
                });
            });

            // Edit Modal - Add More
            document.getElementById('edit-add-more-universities')?.addEventListener('click', function() {
                const container = document.getElementById('edit-universities-container');
                const newEntry = document.createElement('div');
                newEntry.className = 'university-edit-entry card mb-3';
                newEntry.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">@lang('university.ArabicName')</label>
                    <input type="text" class="form-control" name="universities[${editEntryCount}][name_ar]" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">@lang('university.EnglishName')</label>
                    <input type="text" class="form-control" name="universities[${editEntryCount}][name_en]" required>
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">@lang('university.Logo')</label>
                    <input type="file" class="form-control" name="universities[${editEntryCount}][logo]" accept="image/*" required>
                </div>
                <div class="col-md-12 text-end mt-2">
                    <button type="button" class="btn btn-sm btn-danger remove-edit-entry">
                        <i class="fe fe-trash"></i> @lang('university.Remove')
                    </button>
                </div>
            </div>
        </div>`;
                container.appendChild(newEntry);
                editEntryCount++;

                // Add remove functionality
                newEntry.querySelector('.remove-edit-entry').addEventListener('click', function() {
                    this.closest('.university-edit-entry').remove();
                });
            });


            // ===== SHOW MODAL =====
            document.querySelectorAll('.show-university-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('show-name-ar').textContent = this.getAttribute(
                        'data-name-ar');
                    document.getElementById('show-name-en').textContent = this.getAttribute(
                        'data-name-en');
                    document.getElementById('show-country').textContent = this.getAttribute(
                        'data-country');

                    const logo = this.getAttribute('data-logo');
                    const logoEl = document.getElementById('show-logo');
                    if (logo) {
                        logoEl.src = logo;
                        logoEl.style.display = 'block';
                    } else {
                        logoEl.style.display = 'none';
                    }
                });
            });
        });

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
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection
