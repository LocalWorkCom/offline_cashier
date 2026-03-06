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
        <h4 class="fw-medium mb-0">@lang('country.cities')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item "><a href="{{ route('countries.list') }}">@lang('country.Countries')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('country.cities')</li>

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
                                @lang('country.cities')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create cities', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('country.Addcity')
                                </button>
                            @endif
                            <!-- Add country Modal -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('city.store') }}" method="POST" class="needs-validation"
                                            novalidate enctype="multipart/form-data">
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
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('country.Addcity')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <input type="text" class="form-control" name="country_id"
                                                        value="{{ $country }}" hidden>
                                                    <!-- Arabic Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('country.ArabicName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('country.ArabicName')" value="{{ old('name_ar') }}"
                                                            name="name_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>

                                                    <!-- English Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('country.EnglishName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('country.EnglishName')" name="name_en"
                                                            value="{{ old('name_en') }}" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
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
                            <!-- Edit country Modal -->
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-country-form" action="" method="POST" class="needs-validation"
                                            novalidate enctype="multipart/form-data">
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
                                                <h6 class="modal-title" id="editModalLabel">@lang('country.Editcity')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <input type="text" class="form-control" id="country-edit-id" name="country_id"
                                                    value="{{ $country }}" hidden>
                                                    <!-- Arabic Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-ar"
                                                            class="form-label">@lang('country.ArabicName')</label>
                                                        <input type="text" id="edit-name-ar" class="form-control"
                                                            name="name_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>

                                                    <!-- English Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-en"
                                                            class="form-label">@lang('country.EnglishName')</label>
                                                        <input type="text" id="edit-name-en" class="form-control"
                                                            name="name_en" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
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
                                        <th scope="col">@lang('country.ArabicName')</th>
                                        <th scope="col">@lang('country.EnglishName')</th>
                                        <th scope="col">@lang('country.countryname')</th>
                                        <th scope="col">@lang('country.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cities as $city)
                                        <tr>
                                            <td>{{ $city->name_ar }}</td>
                                            <td>{{ $city->name_en }}</td>
                                            <td>{{ $city->country->name }}</td>

                                            <td>

                                                @if (auth('admin')->user()->hasPermissionTo('update cities', 'admin'))
                                                    <!-- Edit Button -->
                                                    <button type="button"
                                                        class="btn btn-orange-light btn-wave edit-country-btn"
                                                        data-id="{{ $city->id }}" data-name-ar="{{ $city->name_ar }}"
                                                        data-name-en="{{ $city->name_en }}"
                                                        data-country-id="{{ $city->country_id }}"
                                                        data-route="{{ route('city.update', ':id') }}" data-bs-toggle="modal"
                                                        data-bs-target="#editModal">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('delete cities', 'admin'))
                                                    <!-- Delete Button -->
                                                    <button type="button" onclick="delete_item('{{ $city->id }}')"
                                                        class="btn btn-danger-light btn-wave">
                                                        @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('view regions', 'admin'))
                                                    <a href="{{ route('region.list', ['id' => $city->id]) }}"
                                                        class="btn btn-success-light">
                                                        @lang('country.regions') <i class="ri-eye-line"></i>
                                                    </a>
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
            const nameArInput = document.getElementById('edit-name-ar');
            const nameEnInput = document.getElementById('edit-name-en');
            const countryInput = document.getElementById('country-edit-id');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get country details from data attributes
                    const countryId = this.getAttribute('data-id');
                    const nameAr = this.getAttribute('data-name-ar');
                    const nameEn = this.getAttribute('data-name-en');
                    const countrybId = this.getAttribute('data-country-id');

                    const routeTemplate = this.getAttribute('data-route');

                    // Set form action URL dynamically
                    const updateRoute = routeTemplate.replace(':id', countryId);
                    editForm.action = updateRoute;

                    // Populate the modal fields
                    nameArInput.value = nameAr;
                    nameEnInput.value = nameEn;
                    countryInput.value = countrybId;

                });
            });
        });


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
                        url: '{{ route('city.delete', ':id') }}'.replace(':id', id),
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
                                    title: '{{ __('country.delete_success') }}',
                                    showConfirmButton: false,
                                    timer: 1500
                                });

                                // Optionally refresh or update the UI
                                location.reload();
                            } else {
                                // Handle cases where the country cannot be deleted
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('country.delete_error') }}',
                                    text: response.message,
                                    showConfirmButton: true
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('country.delete_error') }}',
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
