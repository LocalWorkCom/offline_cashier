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
        <h4 class="fw-medium mb-0">@lang('socialMediaInformationSetting.social_media_information_setting')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    @if (auth('admin')->user()->hasPermissionTo('view company_profile_setting', 'admin'))
                        <li class="breadcrumb-item active" aria-current="page">
                            <a href="javascript:void(0);"
                                onclick="window.location.href='{{ route('social_media_information_setting.list') }}'">@lang('socialMediaInformationSetting.social_media_information_setting')</a>
                        </li>
                    @endif
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
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">
                                @lang('socialMediaInformationSetting.social_media_information_setting')
                            </div>

                            <div>
                                @if (auth('admin')->user()->hasPermissionTo('create company_profile_setting', 'admin'))
                                    <a href="{{ route('social_media_information_setting.create') }}"
                                        class="btn btn-primary label-btn">
                                        <i class="fe fe-plus label-btn-icon me-2"></i>
                                        @lang('socialMediaInformationSetting.AddSocialMediaInformationSetting')
                                    </a>
                                @endif
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

                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('socialMediaInformationSetting.ID')</th>
                                        <th scope="col">@lang('socialMediaInformationSetting.android_app_link')</th>
                                        <th scope="col">@lang('socialMediaInformationSetting.ios_app_link')</th>
                                        <th scope="col">@lang('socialMediaInformationSetting.facebook')</th>
                                        <th scope="col">@lang('socialMediaInformationSetting.company')</th>
                                        <th scope="col">@lang('socialMediaInformationSetting.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($socialMediaInformationSetting as $setting)
                                        <tr>
                                            <td>{{ $setting->id }}</td>
                                            <td><a href="{{ $setting->android_app_link }}"
                                                    target="_blank">{{ $setting->android_app_link }}</a></td>
                                            <td><a href="{{ $setting->ios_app_link }}"
                                                    target="_blank">{{ $setting->ios_app_link }}</a></td>
                                            <td><a href="{{ $setting->facebook }}"
                                                    target="_blank">{{ $setting->facebook }}</a></td>
                                            <td>
                                                    {{ app()->getLocale() == 'ar'
                                                        ? optional($setting->CompanyProfileSetting)->name_ar
                                                        : optional($setting->CompanyProfileSetting)->name_en }}
                                            </td>

                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view company_profile_setting', 'admin'))
                                                    <a href="{{ route('social_media_information_setting.show', $setting->id) }}"
                                                        class="btn btn-info-light btn-wave">
                                                        @lang('socialMediaInformationSetting.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update company_profile_setting', 'admin'))
                                                    <a href="{{ route('social_media_information_setting.edit', $setting->id) }}"
                                                        class="btn btn-orange-light btn-wave">
                                                        @lang('socialMediaInformationSetting.edit') <i class="ri-edit-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete company_profile_setting', 'admin'))
                                                    <form class="d-inline" id="delete-form-{{ $setting->id }}"
                                                        action="{{ route('social_media_information_setting.delete', $setting->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item('{{ $setting->id }}')"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('socialMediaInformationSetting.delete') <i class="ri-delete-bin-line"></i>
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
    </script>
@endsection
