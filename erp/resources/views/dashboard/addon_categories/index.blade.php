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
        <h4 class="fw-medium mb-0">@lang('addon_categories.AddonCategories')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('addon_categories.AddonCategories')</li>
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
                                @lang('addon_categories.AllAddonCategories')
                            </div>
                            <a href="{{ route('dashboard.addon_categories.create') }}" class="btn btn-primary label-btn">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('addon_categories.AddAddonCategory')
                            </a>
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
                                        <th>@lang('addon_categories.ID')</th>
                                        <th>@lang('addon_categories.NameArabic')</th>
                                        <th>@lang('addon_categories.NameEnglish')</th>
                                        <th>@lang('addon_categories.DescriptionArabic')</th>
                                        <th>@lang('addon_categories.DescriptionEnglish')</th>
                                        <th>@lang('addon_categories.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($addonCategories as $addonCategory)
                                        <tr>
                                            <td>{{ $addonCategory->id }}</td>
                                            <td>{{ $addonCategory->name_ar }}</td>
                                            <td>{{ $addonCategory->name_en }}</td>
                                            <td>{{ $addonCategory->description_ar ?? __('addon_categories.NoDescription') }}
                                            </td>
                                            <td>{{ $addonCategory->description_en ?? __('addon_categories.NoDescription') }}
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view addon_categories', 'admin'))
                                                    <!-- Show -->
                                                    <a href="{{ route('dashboard.addon_categories.show', $addonCategory->id) }}"
                                                        class="btn btn-info-light">
                                                        @lang('addon_categories.View') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update addon_categories', 'admin'))
                                                    <!-- Edit -->
                                                    <a href="{{ route('dashboard.addon_categories.edit', $addonCategory->id) }}"
                                                        class="btn btn-orange-light">
                                                        @lang('addon_categories.Edit') <i class="ri-edit-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete addon_categories', 'admin'))
                                                    <!-- Delete -->
                                                    <form class="d-inline" id="delete-form-{{ $addonCategory->id }}"
                                                        action="{{ route('dashboard.addon_categories.destroy', $addonCategory->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item({{ $addonCategory->id }})"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('addon_categories.Delete') <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('restore addon_categories', 'admin'))
                                                    <!-- Restore -->
                                                    @if ($addonCategory->trashed())
                                                        <form
                                                            action="{{ route('dashboard.addon_categories.restore', $addonCategory->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success-light">
                                                                @lang('addon_categories.Restore') <i class="ri-refresh-line"></i>
                                                            </button>
                                                        </form>
                                                    @endif
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
