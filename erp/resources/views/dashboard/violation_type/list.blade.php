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
        <h4 class="fw-medium mb-0">@lang('violation_types.ViolationTypes')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('violation_types.ViolationTypes')</li>
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
                                @lang('violation_types.ViolationTypes')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create violation_types', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#addViolationTypeModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('violation_types.AddViolationType')
                                </button>
                            @endif
                            <!-- Add Color Modal -->
                            <!-- Add Violation Type Modal -->
                            <div class="modal fade" id="addViolationTypeModal" tabindex="-1"
                                aria-labelledby="addViolationTypeLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('violation_type.store') }}" method="POST"
                                            class="needs-validation" novalidate>
                                            @csrf
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="addViolationTypeLabel">@lang('violation.AddViolationType')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="name_ar" class="form-label">@lang('violation.NameAr')</label>
                                                    <input type="text" class="form-control" name="name_ar" required>
                                                    <div class="invalid-feedback">@lang('validation.EnterNameAr')</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="name_en" class="form-label">@lang('violation.NameEn')</label>
                                                    <input type="text" class="form-control" name="name_en" required>
                                                    <div class="invalid-feedback">@lang('validation.EnterNameEn')</div>
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

                            <!-- Edit Violation Type Modal -->
                            <div class="modal fade" id="editViolationTypeModal" tabindex="-1"
                                aria-labelledby="editViolationTypeModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-violation-type-form" action="" method="POST"
                                            class="needs-validation" novalidate>
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h6 class="modal-title" id="editViolationTypeModalLabel">@lang('violation_type.EditViolationType')
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <!-- Arabic Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-violation-name-ar"
                                                            class="form-label">@lang('violation_type.ArabicName')</label>
                                                        <input type="text" id="edit-violation-name-ar"
                                                            class="form-control" name="name_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>

                                                    <!-- English Name Input -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-violation-name-en"
                                                            class="form-label">@lang('violation_type.EnglishName')</label>
                                                        <input type="text" id="edit-violation-name-en"
                                                            class="form-control" name="name_en" required>
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

                            <!-- Show Violation Type Modal -->
                            <div class="modal fade" id="showViolationTypeModal" tabindex="-1"
                                aria-labelledby="showViolationTypeModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showViolationTypeModalLabel">@lang('violation_type.ShowViolationType')
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <!-- Arabic Name -->
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('violation_type.ArabicName')</label>
                                                    <p id="show-violation-name-ar" class="form-control-static"></p>
                                                </div>

                                                <!-- English Name -->
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('violation_type.EnglishName')</label>
                                                    <p id="show-violation-name-en" class="form-control-static"></p>
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
                            @if (session('message'))
                                <div class="alert alert-solid-info alert-dismissible fade show">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endif
                            <table class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>@lang('violation.NameAr')</th>
                                        <th>@lang('violation.NameEn')</th>
                                        <th>@lang('violation.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($violationTypes as $violation)
                                        <tr>
                                            <td>{{ $violation->id }}</td>
                                            <td>{{ $violation->name_ar }}</td>
                                            <td>{{ $violation->name_en }}</td>
                                            <td>
                                                <!-- Similar edit/show/delete buttons as before, just update IDs and routes -->
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
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-color-btn');
        const editForm = document.getElementById('edit-color-form');
        const nameArInput = document.getElementById('edit-name-ar');
        const nameEnInput = document.getElementById('edit-name-en');
        const hexaCodeInput = document.getElementById('edit-hexa-code');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Get color details from data attributes
                const colorId = this.getAttribute('data-id');
                const nameAr = this.getAttribute('data-name-ar');
                const nameEn = this.getAttribute('data-name-en');
                const hexaCode = this.getAttribute('data-hexa-code'); // Get hex code
                const routeTemplate = this.getAttribute('data-route');

                // Set form action URL dynamically
                const updateRoute = routeTemplate.replace(':id', colorId);
                editForm.action = updateRoute;

                // Populate the modal fields
                nameArInput.value = nameAr;
                nameEnInput.value = nameEn;
                hexaCodeInput.value = hexaCode; // Set hex code value
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const showButtons = document.querySelectorAll('.show-color-btn');
        const nameArDisplay = document.getElementById('show-name-ar');
        const nameEnDisplay = document.getElementById('show-name-en');
        const hexaCodeDisplay = document.getElementById('show-hexa-code');

        showButtons.forEach(button => {
            button.addEventListener('click', function() {
                const nameAr = this.getAttribute('data-name-ar');
                const nameEn = this.getAttribute('data-name-en');
                const hexaCode = this.getAttribute('data-hexa-code'); // Get hex code

                // Display the color details in the modal
                nameArDisplay.textContent = nameAr;
                nameEnDisplay.textContent = nameEn;
                hexaCodeDisplay.textContent = hexaCode; // Display hex code
            });
        });
    });

    function confirmDelete() {
        return confirm("@lang('validation.DeleteConfirm')");
    }

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
