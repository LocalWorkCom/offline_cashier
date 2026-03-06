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
        <h4 class="fw-medium mb-0">@lang('militaryStatus.militaryStatuses')</h4>
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
                            onclick="window.location.href='{{ route('military_service_statuses.list') }}'">@lang('militaryStatus.militaryStatuses')</a>
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
                                @lang('militaryStatus.militaryStatuses')
                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('create military_service_statuses', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#createModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('militaryStatus.addMilitaryStatus')
                                </button>
                            @endif

                            <!-- Create Modal -->
                            <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('military_service_status.store') }}" method="POST"
                                            class="needs-validation" novalidate enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="createModalLabel">@lang('militaryStatus.addMilitaryStatus')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="country_id" class="form-label">@lang('militaryStatus.Country')</label>
                                                        <select class="form-control" id="country_id" name="country_id"
                                                            required>
                                                            <option value="">@lang('militaryStatus.SelectCountry')</option>
                                                            @foreach ($countries as $country)
                                                                <option value="{{ $country->id }}">
                                                                    {{ app()->getLocale() === 'ar' ? $country->name_ar : $country->name_en }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.SelectCountry')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="name_ar" class="form-label">@lang('militaryStatus.ArabicName')</label>
                                                        <input type="text" class="form-control" id="name_ar"
                                                            placeholder="@lang('militaryStatus.ArabicName')" name="name_ar" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterArabicName')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="name_en" class="form-label">@lang('militaryStatus.EnglishName')</label>
                                                        <input type="text" class="form-control" id="name_en"
                                                            placeholder="@lang('militaryStatus.EnglishName')" name="name_en" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
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

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-military-status-form" action="" method="POST"
                                            class="needs-validation" novalidate enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="editModalLabel">@lang('militaryStatus.editMilitaryStatus')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-country-id"
                                                            class="form-label">@lang('militaryStatus.Country')</label>
                                                        <select class="form-control" id="edit-country-id"
                                                            name="country_id" required>
                                                            <option value="">@lang('militaryStatus.SelectCountry')</option>
                                                            @foreach ($countries as $country)
                                                                <option value="{{ $country->id }}">
                                                                    {{ app()->getLocale() === 'ar' ? $country->name_ar : $country->name_en }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.SelectCountry')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-ar"
                                                            class="form-label">@lang('militaryStatus.ArabicName')</label>
                                                        <input type="text" id="edit-name-ar" class="form-control"
                                                            name="name_ar" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterArabicName')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-en"
                                                            class="form-label">@lang('militaryStatus.EnglishName')</label>
                                                        <input type="text" id="edit-name-en" class="form-control"
                                                            name="name_en" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
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

                            <!-- Show Modal -->
                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('militaryStatus.showMilitaryStatus')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('militaryStatus.ArabicName')</label>
                                                    <p id="show-name-ar" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('militaryStatus.EnglishName')</label>
                                                    <p id="show-name-en" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('militaryStatus.EmployeesCount')</label>
                                                    <p id="show-employees-count" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('militaryStatus.Country')</label>
                                                    <p id="show-country" class="form-control-static"></p>
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
                            <table id="file-export"class="table table-bordered text-nowrap" style="width:100%">

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
                                        <th scope="col">@lang('militaryStatus.id')</th>
                                        <th scope="col">@lang('militaryStatus.ArabicName')</th>
                                        <th scope="col">@lang('militaryStatus.EnglishName')</th>
                                        <th scope="col">@lang('militaryStatus.Country')</th>
                                        <th scope="col">@lang('militaryStatus.EmployeesCount')</th>
                                        <th scope="col">@lang('militaryStatus.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($militaryStatuses as $militaryStatus)
                                        <tr>
                                            <td>{{ $militaryStatus->id }}</td>
                                            <td>{{ $militaryStatus->name_ar }}</td>
                                            <td>{{ $militaryStatus->name_en }}</td>
                                            <td>
                                                {{ $militaryStatus->country ? (app()->getLocale() === 'ar' ? $militaryStatus->country->name_ar : $militaryStatus->country->name_en) : '----' }}
                                            </td>
                                            <td>
                                                <a href="{{ route('employees.list', ['military_service_status_id' => $militaryStatus->id]) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    {{ $militaryStatus->employees_count }}
                                                </a>
                                            </td>

                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view military_service_statuses', 'admin'))
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-info-light btn-wave show-military-status-btn"
                                                        data-id="{{ $militaryStatus->id }}"
                                                        data-name-ar="{{ $militaryStatus->name_ar }}"
                                                        data-name-en="{{ $militaryStatus->name_en }}"
                                                        data-country-id="{{ $militaryStatus->country_id }}"
                                                        data-country-name="{{ $militaryStatus->country ? (app()->getLocale() === 'ar' ? $militaryStatus->country->name_ar : $militaryStatus->country->name_en) : 'N/A' }}"
                                                        data-employees-count="{{ $militaryStatus->employees_count }}"
                                                        data-bs-toggle="modal" data-bs-target="#showModal">
                                                        @lang('militaryStatus.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('update military_service_statuses', 'admin'))
                                                    <button type="button"
                                                        class="btn btn-orange-light btn-wave edit-military-status-btn"
                                                        data-id="{{ $militaryStatus->id }}"
                                                        data-name-ar="{{ $militaryStatus->name_ar }}"
                                                        data-name-en="{{ $militaryStatus->name_en }}"
                                                        data-country-id="{{ $militaryStatus->country_id }}"
                                                        data-route="{{ route('military_service_status.update', ':id') }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        @lang('militaryStatus.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('delete military_service_statuses', 'admin'))
                                                    <form class="d-inline" id="delete-form-{{ $militaryStatus->id }}"
                                                        action="{{ route('military_service_status.delete', $militaryStatus->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            onclick="delete_item('{{ $militaryStatus->id }}')"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('militaryStatus.delete') <i class="ri-delete-bin-line"></i>
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
            const editButtons = document.querySelectorAll('.edit-military-status-btn');
            const editForm = document.getElementById('edit-military-status-form');
            const nameArInput = document.getElementById('edit-name-ar');
            const nameEnInput = document.getElementById('edit-name-en');
            const countrySelect = document.getElementById('edit-country-id');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const militaryStatusId = this.getAttribute('data-id');
                    const nameAr = this.getAttribute('data-name-ar');
                    const nameEn = this.getAttribute('data-name-en');
                    const countryId = this.getAttribute('data-country-id');
                    const routeTemplate = this.getAttribute('data-route');

                    const updateRoute = routeTemplate.replace(':id', militaryStatusId);
                    editForm.action = updateRoute;

                    nameArInput.value = nameAr;
                    nameEnInput.value = nameEn;
                    countrySelect.value = countryId;
                });
            });

            const showButtons = document.querySelectorAll('.show-military-status-btn');
            const showNameAr = document.getElementById('show-name-ar');
            const showNameEn = document.getElementById('show-name-en');
            const showEmployeesCount = document.getElementById('show-employees-count');
            const showCountry = document.getElementById('show-country');

            showButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const nameAr = this.getAttribute('data-name-ar');
                    const nameEn = this.getAttribute('data-name-en');
                    const employeesCount = this.getAttribute('data-employees-count');
                    const countryName = this.getAttribute('data-country-name');

                    showNameAr.textContent = nameAr;
                    showNameEn.textContent = nameEn;
                    showEmployeesCount.textContent = employeesCount;
                    showCountry.textContent = countryName || 'N/A';
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
                    var form = document.getElementById('delete-form-' + id);
                    form.submit();
                }
            });
        }
    </script>
@endsection
