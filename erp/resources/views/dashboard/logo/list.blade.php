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
        <h4 class="fw-medium mb-0">@lang('logo.Logos')</h4>
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
                            onclick="window.location.href='{{ route('logos.list') }}'">@lang('logo.Logos')</a>
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
                        <div class="card-header"
                            style="
                        display: flex;
                        justify-content: space-between;">
                            <div class="card-title">
                                @lang('logo.Logos')</div>
                                @if (auth('admin')->user()->hasPermissionTo('create logos', 'admin'))

                            <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                data-bs-target="#exampleModal">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('logo.AddLogo')
                            </button>
                            @endif
                            <!-- Modal for Adding a Logo -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('logo.store') }}" method="POST" class="needs-validation"
                                            enctype="multipart/form-data" novalidate>
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
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('logo.AddLogo')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="logo-name" class="form-label">@lang('logo.ArabicName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('logo.ArabicName')" name="name_ar" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterArabicName')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="logo-name" class="form-label">@lang('logo.EnglishName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('logo.EnglishName')" name="name_en" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="logo-expiration-date"
                                                            class="form-label">@lang('logo.Image')</label>
                                                        <input type="file" class="form-control"
                                                            accept=".jpeg, .png, .jpg, .gif, .svg" name="image" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterImage')
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
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-logo-form" action="" method="POST"
                                            enctype="multipart/form-data" class="needs-validation" novalidate>
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
                                                <h6 class="modal-title" id="editModalLabel">@lang('logo.EditLogo')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name"
                                                            class="form-label">@lang('logo.ArabicName')</label>
                                                        <input type="text" id="edit-name-ar" class="form-control"
                                                            name="name_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-expiration-date"
                                                            class="form-label">@lang('logo.EnglishName')</label>
                                                        <input type="text" id="edit-name-en" class="form-control"
                                                            name="name_en" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="edit-image"
                                                            class="form-label">@lang('logo.Image')</label>
                                                        <p>
                                                            <img id="edit-image-preview" src=""
                                                                alt="@lang('logo.ExistImage')" width="100" height="100"
                                                                style="margin-top: 10px;">
                                                        </p>
                                                        <input type="file" id="edit-image" class="form-control"
                                                            name="image">
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterImage')
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
                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('logo.ShowLogo')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('logo.ArabicName')</label>
                                                    <p id="show-name-ar" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('logo.EnglishName')</label>
                                                    <p id="show-name-en" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('logo.Image')</label>
                                                    <p>
                                                        <img id="show-image" alt="" width="100"
                                                            height="100">
                                                    </p>
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
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('category.ID')</th>
                                        <th scope="col">@lang('logo.Image')</th>
                                        <th scope="col">@lang('logo.ArabicName')</th>
                                        <th scope="col">@lang('logo.EnglishName')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php $rowNumber = 1; @endphp
                                @foreach ($logos as $logo)
                                        <tr>
{{--                                            <td>{{ $logo->id }}</td>--}}
                                            <td>{{ $rowNumber++  }}</td>
                                            <td><img src="{{ url($logo->image) }}" alt="" width="100"
                                                    height="100"></td>
                                            <td>{{ $logo->name_ar }}</td>
                                            <td>{{ $logo->name_en }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view logos', 'admin'))

                                                <!-- Show Button -->
                                                <a href="javascript:void(0);"
                                                    class="btn btn-info-light btn-wave show-logo-btn"
                                                    data-id="{{ $logo->id }}" data-name-ar="{{ $logo->name_ar }}"
                                                    data-name-en="{{ $logo->name_en }}" data-image="{{ $logo->image }}"
                                                    data-bs-toggle="modal" data-bs-target="#showModal">
                                                    @lang('category.show') <i class="ri-eye-line"></i>
                                                </a>

                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('update logos', 'admin'))

                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-orange-light btn-wave edit-logo-btn"
                                                    data-id="{{ $logo->id }}" data-name-ar="{{ $logo->name_ar }}"
                                                    data-name-en="{{ $logo->name_en }}"
                                                    data-image="{{ asset($logo->image) }}"
                                                    data-route="{{ route('logo.update', ':id') }}" data-bs-toggle="modal"
                                                    data-bs-target="#editModal">
                                                    @lang('category.edit') <i class="ri-edit-line"></i>
                                                </button>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete logos', 'admin'))

                                                <form class="d-inline" id="delete-form-{{ $logo->id }}"
                                                    action="{{ route('logo.delete', $logo->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" onclick="delete_item('{{ $logo->id }}')"
                                                        class="btn btn-danger-light btn-wave">
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
            <!-- End:: row-4 -->

        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->

    <!-- Include Select2 CSS -->
{{--    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />--}}

{{--    <!-- Include Select2 JS -->--}}
{{--    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>--}}


{{--    <!-- DATA-TABLES CDN -->--}}
{{--    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>--}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

     <!-- INTERNAL DATADABLES JS -->
     {{-- @vite('resources/assets/js/datatables.js')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')  --}}
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-logo-btn');
        const editForm = document.getElementById('edit-logo-form');
        const nameArInput = document.getElementById('edit-name-ar');
        const nameEnInput = document.getElementById('edit-name-en');
        const editImagePreview = document.getElementById(
        'edit-image-preview'); // Add this line for image preview

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const logoId = this.getAttribute('data-id');
                const nameAr = this.getAttribute('data-name-ar');
                const nameEn = this.getAttribute('data-name-en');
                const imagePath = this.getAttribute('data-image');
                const routeTemplate = this.getAttribute('data-route');

                editForm.action = routeTemplate.replace(':id', logoId);
                nameArInput.value = nameAr;
                nameEnInput.value = nameEn;

                // Show the image preview
                if (editImagePreview) {
                    editImagePreview.src = imagePath;
                }
            });
        });

        const showButtons = document.querySelectorAll('.show-logo-btn');
        const showArabicName = document.getElementById('show-name-ar');
        const showEnglishName = document.getElementById('show-name-en');
        const showImage = document.getElementById('show-image');

        showButtons.forEach(button => {
            button.addEventListener('click', function() {
                const nameAr = this.getAttribute('data-name-ar');
                const nameEn = this.getAttribute('data-name-en');
                const imagePath = this.getAttribute('data-image');

                showArabicName.textContent = nameAr;
                showEnglishName.textContent = nameEn;

                // Show the image
                showImage.src = imagePath.startsWith('http') ? imagePath :
                    `{{ asset('') }}${imagePath}`;
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
