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
        <h4 class="fw-medium mb-0">@lang('addons.Addons')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('addons.Addons')</li>
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
                                @lang('addons.AllAddons')
                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('create addons', 'admin'))

                            <a href="{{ route('dashboard.addons.create') }}" class="btn btn-primary label-btn">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('addons.AddAddon')
                            </a>
                            @endcan
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
                                        <th>@lang('addons.ID')</th>
                                        <th>@lang('addons.NameArabic')</th>
                                        <th>@lang('addons.NameEnglish')</th>
                                        <th>@lang('addons.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($addons as $addon)
                                        <tr>
                                            <td>{{ $addon->id }}</td>
                                            <td>{{ $addon->name_ar }}</td>
                                            <td>{{ $addon->name_en }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view addons', 'admin'))

                                                <!-- Show -->
                                                <a href="{{ route('dashboard.addons.show', $addon->id) }}" class="btn btn-info-light">
                                                    @lang('addons.View') <i class="ri-eye-line"></i>
                                                </a>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('update addons', 'admin'))

                                                <!-- Edit -->
                                                <a href="{{ route('dashboard.addons.edit', $addon->id) }}" class="btn btn-orange-light">
                                                    @lang('addons.Edit') <i class="ri-edit-line"></i>
                                                </a>
                                                @endif

                                                <!-- Delete -->
                                                @if (auth('admin')->user()->hasPermissionTo('delete addons', 'admin'))

                                                <form class="d-inline" id="delete-form-{{ $addon->id }}"
                                                    action="{{ route('dashboard.addons.destroy', $addon->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" onclick="delete_item({{ $addon->id }})"
                                                        class="btn btn-danger-light btn-wave">
                                                        @lang('addons.Delete') <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                {{-- Route::delete('/{id}', [AddonController::class, 'destroy'])->name('dashboard.addons.destroy'); --}}
                                                @if (auth('admin')->user()->hasPermissionTo('restore addons', 'admin'))

                                                <!-- Restore -->
                                                @if ($addon->trashed())
                                                    <form action="{{ route('dashboard.addons.restore', $addon->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success-light">
                                                            @lang('addons.Restore') <i class="ri-refresh-line"></i>
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
