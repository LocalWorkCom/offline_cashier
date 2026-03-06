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
        <h4 class="fw-medium mb-0">@lang('violation.Violations')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);"
                            onclick="window.location.href='{{ route('violations.list') }}'">@lang('violation.Violations')</a>
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
                        <div class="card-header"
                            style="
                        display: flex;
                        justify-content: space-between;">
                            <div class="card-title">@lang('violation.Violations')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create violations', 'admin'))
                                <button type="button" class="btn btn-primary label-btn"
                                    onclick="window.location.href='{{ route('violation.create') }}'">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('violation.AddViolation')
                                </button>
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
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>@lang('violation.Employee')</th>
                                        <th>@lang('violation.ViolationType')</th>
                                        <th>@lang('violation.ViolationDate')</th>
                                        <th>@lang('violation.ViolationLocation')</th>
                                        <th>@lang('violation.Description')</th>
                                        <th>@lang('violation.Penalty')</th>
                                        <th>@lang('violation.Status')</th>
                                        <th>@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($violations as $index => $violation)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $violation->employee->first_name . '-' . $violation->employee->last_name ?? '-' }}
                                            </td>
                                            <td>{{ app()->getLocale() == 'ar' ? $violation->violationType->name_ar : $violation->violationType->name_en }}
                                            </td>
                                            <td>{{ $violation->violation_date }}</td>
                                            <td>{{ $violation->violation_location }}</td>
                                            <td>{{ $violation->description }}</td>
                                            <td>{{ $violation->penalty }}</td>
                                            <td>{{ $violation->status }}</td>

                                            <td>
                                                @if ($violation->status != 'resolved')
                                                    <form method="POST"
                                                        action="{{ route('violation.resolve', $violation->id) }}"
                                                        style="display:inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            @lang('violation.MarkResolved')
                                                        </button>
                                                    </form>
                                                @endif
                                                <!-- Example Actions -->
                                                <a href="{{ route('violation.edit', $violation->id) }}"
                                                    class="btn btn-sm btn-warning">
                                                    @lang('violation.edit')
                                                </a>
                                                <a href="{{ route('violation.show', $violation->id) }}"
                                                    class="btn btn-sm btn-warning">
                                                    @lang('violation.show')
                                                </a>
                                                <form id="delete-form-{{ $violation->id }}"
                                                    action="{{ route('violation.delete', $violation->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="delete_item({{ $violation->id }})">
                                                        @lang('violation.delete')
                                                    </button>
                                                </form>
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
</script>
