@extends('layouts.master')

@section('styles')
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0"></h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active" aria-current="page">@lang('einvoice.einvoicessetting')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('einvoice.einvoicessetting')</div>
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
                            <form method="POST" action="{{ route('dashboard.einvoices.setting.store') }}"
                                class="needs-validation" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="gender" class="form-label">@lang('employee.employee')</label>
                                        <select class="select2 form-control" id="employee_id" name="employee_id">
                                            <option value="" selected disabled>@lang('employee.employee')</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->first_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.selectGender')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="gender" class="form-label">@lang('employee.branch')</label>
                                        <select class="select2 form-control" id="branch_id" name="branch_id">
                                            <option value="" selected disabled>@lang('employee.branch')</option>
                                            @foreach (branches() as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.selectGender')
                                        </div>
                                    </div>
                                </div>
                                <div class="row gy-4 mt-1">
                                    <div class="col-xl-12 col-lg-12 col-md-8 col-sm-12 d-flex" id="pos_per_branch">

                                    </div>
                                </div>
                                <div id="per-machien-setting">

                                </div>
                                <!-- Submit Button -->
                                <center>
                                    <div class="col-xl-4 mt-3">
                                        <button type="submit"
                                            class="btn btn-primary form-control">@lang('category.save')</button>
                                    </div>
                                </center>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
  $(document).ready(function () {
    $('#branch_id').change(function () {
        const branchId = $(this).val();

        if (branchId) {
            $.ajax({
                url: "{{ route('dashboard.einvoices.get.pos') }}",
                type: "POST",
                data: {
                    branch_id: branchId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    $('#pos_per_branch').empty();

                    if (data.length > 0) {
                        $('#pos_per_branch').append(
                            '<label class="form-check-label">Select POS:</label>'
                        );
                        $.each(data, function (index, pos) {
                            $('#pos_per_branch').append(`
                                <div class="form-check me-3 pos-check-container">
                                    <input class="form-check-input form-checked-outline form-checked-success pos-checkbox"
                                           type="checkbox" name="pos_ids[]" id="pos_${pos.id}" value="${pos.id}">
                                    <label class="form-check-label" for="pos_${pos.id}">${pos.name}</label>
                                </div>
                                <div id="per-machien-setting-${pos.id}" class="per-machine-settings mt-3" style="display: none;">
                                    <div class="row gy-4">
                                        <div class="col-xl-6">
                                            <label for="min_total_${pos.id}" class="form-label">@lang('einvoice.mintotal')</label>
                                            <input type="number" name="min_total_${pos.id}" id="min_total_${pos.id}" class="form-control pos-input"
                                                   placeholder="@lang('einvoice.mintotal')">
                                        </div>
                                        <div class="col-xl-6">
                                            <label for="max_total_${pos.id}" class="form-label">@lang('einvoice.maxtotal')</label>
                                            <input type="number" name="max_total_${pos.id}" id="max_total_${pos.id}" class="form-control pos-input"
                                                   placeholder="@lang('einvoice.maxtotal')">
                                        </div>
                                    </div>

                                    <div class="row gy-4 mt-1">
                                        <div class="col-xl-6">
                                            <label for="minnum_${pos.id}" class="form-label">@lang('einvoice.minnum')</label>
                                            <input type="number" name="minnum_${pos.id}" id="minnum_${pos.id}" class="form-control pos-input"
                                                   placeholder="@lang('einvoice.minnum')">
                                        </div>

                                        <div class="col-xl-6">
                                            <label for="maxnum_${pos.id}" class="form-label">@lang('einvoice.maxnum')</label>
                                            <input type="number" name="maxnum_${pos.id}" id="maxnum_${pos.id}" class="form-control pos-input"
                                                   placeholder="@lang('einvoice.maxnum')">
                                        </div>

                                        <div class="col-xl-6">
                                            <label for="time_${pos.id}" class="form-label">@lang('einvoice.time')</label>
                                            <input type="time" name="auto_run_time_${pos.id}" id="time_${pos.id}" class="form-control pos-input">
                                        </div>
                                    </div>
                                </div>
                            `);

                            // Handle checkbox change event
                            $(`#pos_${pos.id}`).change(function () {
                                const settingsContainer = $(`#per-machien-setting-${pos.id}`);
                                const isChecked = $(this).is(':checked');

                                settingsContainer.toggle(isChecked);

                                // Toggle 'required' for inputs inside the settings container
                                settingsContainer.find('input.pos-input').prop('required', isChecked);
                            });
                        });

                    } else {
                        $('#pos_per_branch').html('<p>No POS available for this branch.</p>');
                    }
                },
                error: function () {
                    alert('Error fetching POS data.');
                }
            });
        }
    });
});

    </script>
@endsection
