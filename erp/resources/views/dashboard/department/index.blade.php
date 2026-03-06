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
        <h4 class="fw-medium mb-0">@lang('department.departments')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('department.departments')</li>
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
                                @lang('department.departments')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create departments', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('department.add')
                                </button>
                            @endif
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('department.store') }}" method="POST"
                                            class="needs-validation" novalidate>
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
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('department.AddDepartment')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('department.arabicName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('department.arabicName')" name="name_ar" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterArabicName')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('department.englishName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('department.englishName')" name="name_en" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('department.ArabicDesc')</label>
                                                        <textarea type="text" class="form-control" name="description_ar"> </textarea>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterArabicDesc')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('department.EnglishDesc')</label>
                                                        <textarea type="text" class="form-control" name="description_en"> </textarea>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishDesc')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('department.ArabicInstructions')</label>
                                                        <textarea type="text" class="form-control" name="instructions_ar"> </textarea>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterArabicInstruction')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('department.EnglishInstructions')</label>
                                                        <textarea type="text" class="form-control" name="instructions_en"> </textarea>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishInstruction')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="branch_id"
                                                            class="form-label">@lang('department.branch')</label>
                                                        <select class="form-control" name="branch_id" id="branch_id">
                                                            <option value="">@lang('department.selectBranch')</option>
                                                            @foreach ($branches as $branch)
                                                                <option value="{{ $branch->id }}">
                                                                    {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}
                                                                </option>
                                                            @endforeach
                                                        </select>
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
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-department-form" method="POST" class="needs-validation"
                                            novalidate>
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
                                                <h6 class="modal-title" id="editModalLabel">@lang('department.Editdepartment')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-ar"
                                                            class="form-label">@lang('department.arabicName')</label>
                                                        <input type="text" id="edit-name-ar" class="form-control"
                                                            name="name_ar" required>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-en"
                                                            class="form-label">@lang('department.englishName')</label>
                                                        <input type="text" id="edit-name-en" class="form-control"
                                                            name="name_en" required>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-description-ar"
                                                            class="form-label">@lang('department.arabicDescr')</label>
                                                        <textarea type="text" id="edit-description-ar" class="form-control" name="description_ar"></textarea>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-description-en"
                                                            class="form-label">@lang('department.englishDescr')</label>
                                                        <textarea type="text" id="edit-description-en" class="form-control" name="description_en"></textarea>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-instructions-ar"
                                                            class="form-label">@lang('department.ArabicInstructions')</label>
                                                        <textarea type="text" id="edit-instructions-ar" class="form-control" name="instructions_ar"></textarea>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-instructions-en"
                                                            class="form-label">@lang('department.EnglishInstructions')</label>
                                                        <textarea type="text" id="edit-instructions-en" class="form-control" name="instructions_en"></textarea>
                                                    </div>

                                                    <!-- In the edit modal -->
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-branch-id"
                                                            class="form-label">@lang('department.branch')</label>
                                                        <select class="form-control" name="branch_id"
                                                            id="edit-branch-id">
                                                            <option value="">@lang('department.selectBranch')</option>
                                                            @foreach ($branches as $branch)
                                                                <option value="{{ $branch->id }}">
                                                                    {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}
                                                                </option>
                                                            @endforeach
                                                        </select>
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
                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('department.showdepartment')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-12">
                                                    <label class="form-label">@lang('department.arabicName')</label>
                                                    <p id="show-name-ar" class="form-control-static"></p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">@lang('department.englishName')</label>
                                                    <p id="show-name-en" class="form-control-static"></p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">@lang('department.arabicDescr')</label>
                                                    <p id="show-description-ar" class="form-control-static"></p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">@lang('department.englishDescr')</label>
                                                    <p id="show-description-en" class="form-control-static"></p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">@lang('department.ArabicInstructions')</label>
                                                    <p id="show-instructions-ar" class="form-control-static"></p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">@lang('department.EnglishInstructions')</label>
                                                    <p id="show-instructions-en" class="form-control-static"></p>
                                                </div>
                                                <!-- In the show modal -->
                                                <div class="col-12">
                                                    <label class="form-label">@lang('department.branch')</label>
                                                    <p id="show-branch" class="form-control-static"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">@lang('modal.close')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-body">
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
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('department.ID')</th>
                                        <th scope="col">@lang('department.name')</th>
                                        <th scope="col">@lang('department.description')</th>
                                        <th scope="col">@lang('department.instructions')</th>
                                        <th scope="col">@lang('department.branch')</th> <!-- Add this line -->
                                        <th scope="col">@lang('department.employee_count')</th>
                                        <th scope="col">@lang('department.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($departments as $department)
                                        <tr>
                                            <td>{{ $department->id }}</td>
                                            <td>{{ app()->getLocale() === 'ar' ? $department->name_ar : $department->name_en }}
                                            </td>
                                            <td>{{ app()->getLocale() === 'ar' ? $department->description_ar : $department->description_en }}
                                            </td>
                                            <td>{{ app()->getLocale() === 'ar' ? $department->instructions_ar : $department->instructions_en }}
                                            </td>
                                            <td>{{ $department->branch ? (app()->getLocale() === 'ar' ? $department->branch->name_ar : $department->branch->name_en) : '------' }}
                                            </td>
                                            <td>
                                                <a href="{{ route('employees.list', ['department_id' => $department->id]) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    {{ $department->employees_count }}
                                                </a>
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view departments', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-info-light btn-wave show-department-btn"
                                                        data-id="{{ $department->id }}"
                                                        data-name-ar="{{ $department->name_ar }}"
                                                        data-name-en="{{ $department->name_en }}"
                                                        data-description-ar="{{ $department->description_ar ?? '------' }}"
                                                        data-description-en="{{ $department->description_en ?? '------' }}"
                                                        data-instructions-ar="{{ $department->instructions_ar ?? '------' }}"
                                                        data-instructions-en="{{ $department->instructions_en ?? '------' }}"
                                                        data-branch-id="{{ $department->branch_id }}"
                                                        data-branch-name="{{ $department->branch ? (app()->getLocale() === 'ar' ? $department->branch->name_ar : $department->branch->name_en) : '------' }}"
                                                        data-bs-toggle="modal" data-bs-target="#showModal">
                                                        @lang('department.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update departments', 'admin'))
                                                    <!-- Edit Button -->
                                                    <button type="button"
                                                        class="btn btn-orange-light btn-wave edit-department-btn"
                                                        data-id="{{ $department->id }}"
                                                        data-name-ar="{{ $department->name_ar }}"
                                                        data-name-en="{{ $department->name_en }}"
                                                        data-description-ar="{{ $department->description_ar }}"
                                                        data-description-en="{{ $department->description_en }}"
                                                        data-instructions-ar="{{ $department->instructions_ar }}"
                                                        data-instructions-en="{{ $department->instructions_en }}"
                                                        data-branch-id="{{ $department->branch_id }}"
                                                        data-route="{{ route('department.update', ':id') }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        @lang('department.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete departments', 'admin'))
                                                    <!-- Delete Button -->
                                                    <form class="d-inline" id="delete-form-{{ $department->id }}"
                                                        action="{{ route('department.delete', $department->id) }}"
                                                        method="POST" onsubmit="return confirmDelete()">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            onclick="delete_item({{ $department->id }})"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('department.delete') <i class="ri-delete-bin-line"></i>
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
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit Modal
        const editButtons = document.querySelectorAll('.edit-department-btn');
        const editForm = document.getElementById('edit-department-form');
        const nameArInput = document.getElementById('edit-name-ar');
        const nameEnInput = document.getElementById('edit-name-en');
        const descriptionArInput = document.getElementById('edit-description-ar');
        const descriptionEnInput = document.getElementById('edit-description-en');
        const instructionsArInput = document.getElementById('edit-instructions-ar');
        const instructionsEnInput = document.getElementById('edit-instructions-en');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const departmentId = this.getAttribute('data-id');
                const nameAr = this.getAttribute('data-name-ar');
                const nameEn = this.getAttribute('data-name-en');
                const descriptionAr = this.getAttribute('data-description-ar');
                const descriptionEn = this.getAttribute('data-description-en');
                const instructionsAr = this.getAttribute('data-instructions-ar');
                const instructionsEn = this.getAttribute('data-instructions-en');
                const branchId = this.getAttribute('data-branch-id'); // Add this line
                const routeTemplate = this.getAttribute('data-route');

                // Set form action dynamically
                editForm.action = routeTemplate.replace(':id', departmentId);

                // Populate fields
                nameArInput.value = nameAr;
                nameEnInput.value = nameEn;
                descriptionArInput.value = descriptionAr;
                descriptionEnInput.value = descriptionEn;
                instructionsArInput.value = instructionsAr;
                instructionsEnInput.value = instructionsEn;
                document.getElementById('edit-branch-id').value = branchId; // Add this line
            });
        });

        // Show Modal
        const showButtons = document.querySelectorAll('.show-department-btn');
        const nameArElement = document.getElementById('show-name-ar');
        const nameEnElement = document.getElementById('show-name-en');
        const descriptionArElement = document.getElementById('show-description-ar');
        const descriptionEnElement = document.getElementById('show-description-en');
        const instructionsArElement = document.getElementById('show-instructions-ar');
        const instructionsEnElement = document.getElementById('show-instructions-en');
        const branchElement = document.getElementById('show-branch'); // Add this line

        showButtons.forEach(button => {
            button.addEventListener('click', function() {
                const nameAr = this.getAttribute('data-name-ar');
                const nameEn = this.getAttribute('data-name-en');
                const descriptionAr = this.getAttribute('data-description-ar');
                const descriptionEn = this.getAttribute('data-description-en');
                const instructionsAr = this.getAttribute('data-instructions-ar');
                const instructionsEn = this.getAttribute('data-instructions-en');
                const branchName = this.getAttribute('data-branch-name'); // Get branch name

                // Populate modal fields
                nameArElement.textContent = nameAr;
                nameEnElement.textContent = nameEn;
                descriptionArElement.textContent = descriptionAr;
                descriptionEnElement.textContent = descriptionEn;
                instructionsArElement.textContent = instructionsAr;
                instructionsEnElement.textContent = instructionsEn;
                branchElement.textContent = branchName; // Set branch name
            });
        });
    });


    function delete_item(id) {
        Swal.fire({
            title: "@lang('department.warning')",
            text: "@lang('department.deleteMsg')",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "@lang('department.yesDelete')",
            cancelButtonText: "@lang('department.cancelDelete')",
            confirmButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('delete-form-' + id);
                form.submit();
            }
        });
    }
</script>
