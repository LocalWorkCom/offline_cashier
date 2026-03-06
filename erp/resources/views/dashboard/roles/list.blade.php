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
        <h4 class="fw-medium mb-0">@lang('roles.roles')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('roles.roles')</li>
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
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">@lang('roles.roles')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create roles', 'admin'))
                                <div class="card-header">
                                    <a href="{{ route('role.create') }}" type="button" class="btn btn-primary label-btn">
                                        <i class="fe fe-plus label-btn-icon me-2"></i>
                                        @lang('roles.Add')
                                    </a>
                                </div>
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
                                        <th scope="col">@lang('roles.Id')</th>
                                        <th scope="col">@lang('roles.name')</th>
                                        <th scope="col">@lang('roles.permission')</th>
                                        <th scope="col">@lang('roles.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                        <tr>
                                            <td>{{ $role->id }}</td>
                                            <td>{{ $role->name }}</td>
                                            <td>
                                                @foreach ($role->permissions->take(10) as $permission)
                                                    {{ __("permissions.{$permission->name}") }}{{ !$loop->last && $loop->iteration % 4 != 0 ? ' - ' : '' }}
                                                    @if ($loop->iteration % 4 == 0 && !$loop->last)
                                                        <br>
                                                    @endif
                                                @endforeach

                                                @if ($role->permissions->count() > 10)
                                                    ...
                                                @endif
                                            </td>

                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view roles', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="{{ route('role.show', $role->id) }}"
                                                        class="btn btn-info-light btn-wave show-category">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                {{-- Disable Edit and Delete for Officer --}}
                                                @if($role->name != 'officer')
                                                    @if($role->id != 1 || auth('admin')->user()->hasRole('LocalWork Admin') )
                                                        @if (auth('admin')->user()->hasPermissionTo('update roles', 'admin'))
                                                            <!-- Edit Button -->
                                                            <a href="{{ route('role.edit', $role->id) }}"
                                                                class="btn btn-orange-light btn-wave">
                                                                @lang('category.edit') <i class="ri-edit-line"></i>
                                                            </a>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('delete roles', 'admin'))
                                                            <!-- Delete Button -->
                                                            <form class="d-inline" id="delete-form-{{ $role->id }}"
                                                                action="{{ route('role.delete', $role->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" onclick="delete_item({{ $role->id }})"
                                                                    class="btn btn-danger-light btn-wave">
                                                                    @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            </form>
                                                        @endif
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
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- DATA-TABLES CDN -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    <script>
        function delete_item(id) {
            Swal.fire({
                title: '{{ __('roles.warning_title') }}',
                text: '{{ __('roles.delete_confirmation') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('roles.confirm_delete') }}',
                cancelButtonText: '{{ __('roles.cancel') }}',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = document.getElementById('delete-form-' + id);
                    form.submit();
                }
            });
        }
    </script>
@endsection
