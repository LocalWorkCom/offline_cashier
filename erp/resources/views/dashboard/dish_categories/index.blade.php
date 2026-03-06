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
        <h4 class="fw-medium mb-0">@lang('dishes.DishCategories')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a
                            href="{{ route('dashboard.dish-categories.index') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('dishes.DishCategories')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">
                                @lang('dishes.AllCategories')
                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('create dish_categories', 'admin'))

                            <a href="{{ route('dashboard.dish-categories.create') }}" class="btn btn-primary label-btn">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('dishes.AddCategory')
                            </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-solid-info alert-dismissible fade show">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('dishes.ID')</th>
                                        <th>@lang('dishes.Image')</th>
                                        <th>@lang('dishes.NameArabic')</th>
                                        <th>@lang('dishes.NameEnglish')</th>
                                        <th>@lang('dishes.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categories as $category)
                                        <tr>
                                            <td>{{ $category->id }}</td>
                                            <td>
                                                <img src="{{ asset($category->image_path) }}" alt="Category Image"
                                                    width="100" height="100">
                                            </td>
                                            <td>{{ $category->name_ar }}</td>
                                            <td>{{ $category->name_en }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view dish_categories', 'admin'))

                                                <!-- Show -->
                                                <a href="{{ route('dashboard.dish-categories.show', $category->id) }}"
                                                    class="btn btn-info-light">
                                                    @lang('dishes.View') <i class="ri-eye-line"></i>
                                                </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update dish_categories', 'admin'))

                                                <!-- Edit -->
                                                <a href="{{ route('dashboard.dish-categories.edit', $category->id) }}"
                                                    class="btn btn-orange-light">
                                                    @lang('dishes.Edit') <i class="ri-edit-line"></i>
                                                </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete dish_categories', 'admin'))

                                                <!-- Delete -->
                                                <button type="button" class="btn btn-danger-light"
                                                    onclick="deleteCategory({{ $category->id }})">
                                                    @lang('dishes.Delete') <i class="ri-delete-bin-line"></i>
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
            <!-- End:: row -->
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
    <script>
        function deleteCategory(id) {
            Swal.fire({
                title: "@lang('dishes.Warning')",
                text: "@lang('dishes.ConfirmDelete')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "@lang('dishes.ConfirmDelete')",
                cancelButtonText: "@lang('dishes.Cancel')",
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('dashboard.dish-categories.delete', '') }}/' + id,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.message) {
                                Swal.fire(
                                    "@lang('dishes.Delete')",
                                    response.message, // Display custom success message
                                    'success'
                                ).then(() => {
                                    location.reload(); // Reload the page to reflect changes
                                });
                            }
                        },
                        error: function(error) {
                            let errorMessage = error.responseJSON?.message || "@lang('dishes.DeleteFailed')";
                            Swal.fire(
                                "@lang('dishes.Delete')",
                                errorMessage,
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection
