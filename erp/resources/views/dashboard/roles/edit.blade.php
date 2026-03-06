@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('roles.edit')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('roles.list') }}">@lang('roles.roles')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);">@lang('roles.edit')</a>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid ">
            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('roles.edit')
                            </div>
                        </div>
                        <div class="card-body">
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
                            <form method="POST" action="{{ route('role.update', $role->id) }}" class="needs-validation">
                                @csrf

                                @method('PUT')
                                <div class="row gy-4">
                                    <!-- Role Name -->
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label class="form-label">@lang('roles.name')</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', $role->name) }}" placeholder="@lang('roles.name')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterName')
                                        </div>
                                    </div>

                                    {{-- <!-- Guard -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('roles.guard')</label>
                                        <input type="text" class="form-control" name="guard" value="admin"
                                            placeholder="@lang('roles.guard')" disabled>
                                    </div> --}}

                                    <!-- Permissions -->
                                    <div class="col-xl-10 col-lg-10 col-md-10 col-sm-12">
                                        <div class="row">
                                            @foreach ($groupedPermissions as $group => $groupPermissions)
                                                <div class="col-md-6 mb-4">
                                                    <div class="permission-group">
                                                        <h5 class="group-title"> {{  __("permissions.{$group}")}}</h5>
                                                        <div class="d-flex flex-wrap">
                                                            @foreach ($groupPermissions as $permission)
                                                                <div class="form-check me-3">
                                                                    <input
                                                                        class="form-check-input form-checked-outline form-checked-success"
                                                                        type="checkbox" name="permissions_ids[]"
                                                                        id="permission_{{ $loop->parent->index }}_{{ $loop->index }}"
                                                                        value="{{ $permission->name }}"
                                                                        {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="permission_{{ $loop->parent->index }}_{{ $loop->index }}">
                                                                        {{ __("permissions.{$permission->name}") }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>


                                    <!-- Submit Button -->
                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary " id="input-submit"
                                                value="@lang('roles.SaveChanges')">
                                        </div>
                                    </center>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->
        </div>
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- INTERNAL SELECT2 JS -->
    @vite('resources/assets/js/select2.js')

    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection
