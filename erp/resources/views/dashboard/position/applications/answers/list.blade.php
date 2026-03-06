@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .nav-tabs .nav-link {
            margin-top: 50px;
            margin-right: 10px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }

        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        /* RTL support */
        html[dir="rtl"] .nav-tabs .nav-link {
            margin-right: 0;
            margin-left: 10px;
        }
    </style>
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('applications.applications')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @lang('applications.applications')
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div>
                        <ul class="nav nav-tabs mb-4" id="filter-tabs" role="tablist">
                            @php
                                $statuses = [
                                    'all' => __('applications.all'),
                                    'new_request' => __('applications.new_request'),
                                    'accepted' => __('applications.accepted'),
                                    'pending_interview' => __('applications.pending_interview'),
                                    'rejected' => __('applications.rejected'),
                                    'incomplete_information' => __('applications.incomplete_information'),
                                    'on_hold' => __('applications.on_hold'),
                                ];
                                $currentStatus = request('status') ?? 'all';
                            @endphp

                            @foreach ($statuses as $key => $label)
                                <li class="nav-item">
                                    <a class="nav-link {{ $currentStatus === $key ? 'active' : '' }}"
                                        href="{{ route('position.applications.answers', ['id' => $id, 'status' => $key]) }}">
                                        {{ $label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                    </div>

                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">
                                @lang('applications.applications_list')
                            </div>
                        </div>

                        <div class="card-body">

                            <table id="applications-table" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('applications.id')</th>
                                        <th>@lang('applications.position')</th>
                                        <th>@lang('applications.status')</th>
                                        <th>@lang('applications.date')</th>
                                        <th>@lang('applications.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($applications->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                {{ __('No applications found for this status.') }}
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($applications as $application)
                                            <tr>
                                                <td>{{ $application->id }}</td>
                                                <td>{{ $application->position->getNameAttribute() ?? '-' }}</td>
                                                <td data-search="{{ $application->status }}">
                                                    <span
                                                        class="badge
                                                    @switch($application->status)
                                                        @case('accepted') bg-success @break
                                                        @case('rejected') bg-danger @break
                                                        @case('pending_interview') bg-warning text-dark @break
                                                        @case('new_request') bg-info @break
                                                        @case('incomplete_information') bg-secondary @break
                                                        @case('on_hold') bg-dark @break
                                                        @default bg-primary
                                                    @endswitch">
                                                        @lang('applications.' . $application->status)
                                                    </span>
                                                </td>
                                                <td>{{ $application->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    @if (auth('admin')->user()->hasPermissionTo('view applications answers', 'admin'))
                                                        <a href="{{ route('position.applications.answer', $application->id) }}"
                                                            class="btn btn-info-light btn-wave">
                                                            @lang('applications.viewanswer') <i class="ri-plus-line"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif

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
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#applications-table').DataTable({
                responsive: true,
                language: {
                    url: "{{ asset('lang/' . app()->getLocale() . '/datatable.json') }}"
                }
            });
        });
    </script>
@endsection
