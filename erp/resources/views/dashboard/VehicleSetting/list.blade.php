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
        <h4 class="fw-medium mb-0">@lang('vehicle_setting.VehicleSettings')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('vehicle_settings.index') }}">@lang('vehicle_setting.VehicleSettings')</a>
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
                                        <th>@lang('vehicle_setting.ID')</th>
                                        <th>@lang('vehicle_setting.vehicle_type')</th>
                                        <th>@lang('vehicle_setting.vehicle_min')</th>
                                        <th>@lang('vehicle_setting.vehicle_max')</th>
                                        {{-- <th>@lang('vehicle_setting.CreatedAt')</th> --}}
                                        <th>@lang('vehicle_setting.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($VehicleSettings as $setting)
                                        <tr>
                                            <td>{{ $setting->id }}</td>
                                            <td>{{ trans('vehicle_setting.' . strtolower($setting->vehicle_type)) }}</td>
                                            <td>{{ $setting->vehicle_min }}</td>
                                            <td>{{ $setting->vehicle_max }}</td>
                                            {{-- <td>{{ $setting->created_at ? \Carbon\Carbon::parse($setting->created_at)->format('Y-m-d H:i') : '-' }} --}}
                                            </td>
                                            <td>

                                                @if (auth('admin')->user()->hasPermissionTo('update vehicle_settings', 'admin'))

                                                <a href="{{ route('vehicle_setting.edit', ['id' => $setting->id]) }}"
                                                    class="btn btn-orange-light btn-wave">
                                                    @lang('vehicle_setting.EditVehicleSetting')
                                                </a>

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
