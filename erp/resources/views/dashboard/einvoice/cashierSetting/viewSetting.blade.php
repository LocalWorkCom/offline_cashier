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
        <h4 class="fw-medium mb-0">@lang('permissions.einvoices')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('permissions.einvoices')</li>
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
                                @lang('permissions.einvoices')
                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('create officer_assign_setting', 'admin'))
                                <a href="{{ route('dashboard.einvoices.setting.create') }}" class="btn btn-primary label-btn">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('einvoice.addofficer')
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
                                        <th></th>
                                        <th>@lang('einvoice.employee_name')</th>
                                        <th>@lang('einvoice.branchname')</th>
                                        <th>@lang('einvoice.posname')</th>
                                        <th>@lang('einvoice.Actions')</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    @foreach ($data->groupBy('employee_id') as $employeeId => $settings)
                                    @php
                                        $firstSetting = $settings->first();
                                        $uniqueBranches = $settings->pluck('pos.branches.name')->unique()->filter();
                                    @endphp
                                    <tr>
                                        <td>{{ $employeeId }}</td>
                                        <td>{{ $firstSetting->employee->first_name ?? 'N/A' }}</td>
                                        <td>
                                            {{-- Show single branch name if all are the same, else join them --}}
                                            {{ $uniqueBranches->count() === 1 ? $uniqueBranches->first() : $uniqueBranches->join(' - ') }}
                                        </td>
                                        <td>
                                            {{-- Join all POS names --}}
                                            {{ $settings->pluck('pos.name')->filter()->join(' - ') ?? 'N/A' }}
                                        </td>
                                        <td>
                                            @if (auth('admin')->user()->hasPermissionTo('update officer_assign_setting', 'admin'))
                                                <a href="{{ route('dashboard.einvoices.setting.edit', ['id' => $employeeId]) }}"
                                                    class="btn btn-info-light btn-wave show-order">
                                                    @lang('einvoice.update') <i class="ri-eye-line"></i>
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
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
