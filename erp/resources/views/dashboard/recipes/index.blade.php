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
        <h4 class="fw-medium mb-0">@lang('recipes.Recipes')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('recipes.Recipes')</li>
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
                                @lang('recipes.AllRecipes')
                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('create recipes', 'admin'))

                            <a href="{{ route('dashboard.recipes.create') }}" class="btn btn-primary label-btn">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('recipes.AddRecipe')
                            </a>
                            @endif
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
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('recipes.ID')</th>
                                        <th>@lang('recipes.NameArabic')</th>
                                        <th>@lang('recipes.NameEnglish')</th>
                                        <th>@lang('recipes.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recipes as $recipe)
                                        <tr>
                                            <td>{{ $recipe->id }}</td>
                                            <td>{{ $recipe->name_ar }}</td>
                                            <td>{{ $recipe->name_en }}</td>

                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view recipes', 'admin'))

                                                <!-- Show -->
                                                <a href="{{ route('dashboard.recipes.show', $recipe->id) }}"
                                                    class="btn btn-info-light">
                                                    @lang('recipes.View') <i class="ri-eye-line"></i>
                                                </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update recipes', 'admin'))

                                                <!-- Edit -->
                                                <a href="{{ route('dashboard.recipes.edit', $recipe->id) }}"
                                                    class="btn btn-orange-light">
                                                    @lang('recipes.Edit') <i class="ri-edit-line"></i>
                                                </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete recipes', 'admin'))

                                                <!-- Delete -->

                                                <form class="d-inline" id="delete-form-{{ $recipe->id }}"
                                                    action="{{ route('dashboard.recipes.delete', $recipe->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" onclick="delete_item({{ $recipe->id }})"
                                                        class="btn btn-danger-light btn-wave">
                                                        @lang('recipes.Delete') <i class="ri-delete-bin-line"></i>
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
            <!-- End:: row -->
        </div>
    </div>
@endsection

<script>
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
                if (form) {
                    form.submit();
                } else {
                    console.error("Form not found for ID:", id);
                }
            }
        });
    }
</script>
