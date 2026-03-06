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
        <h4 class="fw-medium mb-0">@lang('cuisines.Cuisines')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('cuisines.Cuisines')</li>
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
                                @lang('cuisines.AllCuisines')
                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('create cuisines', 'admin'))
                                <a href="{{ route('dashboard.cuisines.create') }}" class="btn btn-primary label-btn">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('cuisines.AddCuisine')
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
                                        <th>@lang('cuisines.ID')</th>
                                        <th>@lang('cuisines.NameArabic')</th>
                                        <th>@lang('cuisines.NameEnglish')</th>
                                        <th>@lang('cuisines.IsActive')</th>
                                        <th>@lang('cuisines.Image')</th>
                                        <th>@lang('cuisines.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cuisines as $cuisine)
                                        <tr>
                                            <td>{{ $cuisine->id }}</td>
                                            <td>{{ $cuisine->name_ar }}</td>
                                            <td>{{ $cuisine->name_en }}</td>
                                            <td>
                                                {{ $cuisine->is_active ? __('cuisines.Active') : __('cuisines.Inactive') }}
                                            </td>
                                            <td>
                                                @if ($cuisine->image_path)
                                                    <img src="{{ asset($cuisine->image_path) }}" alt="Cuisine Image"
                                                        width="50" height="50" class="img-thumbnail">
                                                @else
                                                    @lang('cuisines.NoImage')
                                                @endif
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view cuisines', 'admin'))
                                                    <!-- Show -->

                                                    <a href="{{ route('dashboard.cuisines.show', $cuisine->id) }}"
                                                        class="btn btn-info-light">
                                                        @lang('cuisines.View') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update cuisines', 'admin'))
                                                    <!-- Edit -->
                                                    <a href="{{ route('dashboard.cuisines.edit', $cuisine->id) }}"
                                                        class="btn btn-orange-light">
                                                        @lang('cuisines.Edit') <i class="ri-edit-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete cuisines', 'admin'))
                                                    <!-- Delete -->

                                                    <form class="d-inline" id="delete-form-{{ $cuisine->id }}"
                                                        action="{{ route('dashboard.cuisines.destroy', $cuisine->id) }}"
                                                        method="Post">
                                                        @csrf
                                                        <button type="button" onclick="delete_item({{ $cuisine->id }})"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('cuisines.Delete') <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('restore cuisines', 'admin'))
                                                    <!-- Restore -->
                                                    @if ($cuisine->trashed())
                                                        <form
                                                            action="{{ route('dashboard.cuisines.restore', $cuisine->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success-light">
                                                                @lang('cuisines.Restore') <i class="ri-refresh-line"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update cuisines', 'admin'))
                                                    <button class="btn btn-success" data-bs-toggle="modal"
                                                        data-bs-target="#assignModal{{ $cuisine->id }}">
                                                        @lang('cuisines.Assign Categories')
                                                    </button>

                                                    <!-- Assign Categories Modal -->
                                                    <div class="modal fade" id="assignModal{{ $cuisine->id }}"
                                                        tabindex="-1" aria-labelledby="assignModalLabel"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <form
                                                                action="{{ route('dashboard.cuisines.assign', $cuisine->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">@lang('cuisines.Assign Dish Categories')</h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"
                                                                            aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <select name="dish_categories[]"
                                                                            class="form-control" multiple>
                                                                            @foreach ($dishCategories as $category)
                                                                                <option value="{{ $category->id }}"
                                                                                    @if ($cuisine->dishCategories->contains($category->id)) selected @endif>
                                                                                    {{ app()->getLocale() == 'en' ? $category->name_en : $category->name_ar }}

                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="submit"
                                                                            class="btn btn-primary">@lang('cuisines.Assign')</button>
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">@lang('cuisines.Close')</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
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
                form.submit();
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll("form").forEach(form => {
            form.addEventListener("submit", function(event) {
                let selectField = form.querySelector("select");
                let selected = selectField.selectedOptions;
                let errorContainer = form.querySelector(".error-message");

                // Remove previous error message if exists
                if (errorContainer) {
                    errorContainer.remove();
                }

                if (selected.length === 0) {
                    event.preventDefault();

                    // Create error message element
                    let errorMessage = document.createElement("div");
                    errorMessage.classList.add("error-message");
                    errorMessage.style.color = "red";
                    errorMessage.style.marginTop = "5px";
                    errorMessage.innerText = "@lang('cuisines.Please select at least one dish category.')";

                    // Append the error message after the select field
                    selectField.insertAdjacentElement("afterend", errorMessage);
                }
            });
        });
    });
</script>
