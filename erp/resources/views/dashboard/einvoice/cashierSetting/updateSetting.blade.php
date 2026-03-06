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

                            <form
                                action="{{ route('dashboard.einvoices.setting.update', ['id' => $cashierSettings->first()->employee_id]) }}"
                                method="POST">
                                @csrf
                                @method('POST')
                                @foreach ($cashierSettings->pluck('id') as $id)
                                    <input type="hidden" name="ids[]" value="{{ $id }}">
                                @endforeach
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="gender" class="form-label">@lang('employee.employee')</label>
                                        <select class="select2 form-control" id="employee_id" name="employee_id">
                                            <option value="" selected disabled>@lang('employee.employee')</option>
                                            @foreach (employees() as $employee)
                                                <option value="{{ $employee->id }}"
                                                    {{ $employee->id == $cashierSettings->first()->employee_id ? 'selected' : '' }}>
                                                    {{ $employee->first_name }}
                                                </option>
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
                                                <option value="{{ $branch->id }}"
                                                    {{ $branch->id == $branchId ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
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
                                <div id="pos_per_branch">
                                    @foreach ($cashierSettings as $setting)
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5>POS: {{ $setting->pos->name ?? 'N/A' }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <input type="hidden" name="settings[{{ $setting->id }}][pos_id]"
                                                    value="{{ $setting->pos_id }}">

                                                <div class="row gy-3">
                                                    <div class="col-md-6">
                                                        <label for="min_balance_{{ $setting->id }}" class="form-label">Min
                                                            Balance</label>
                                                        <input type="number" class="form-control"
                                                            id="min_balance_{{ $setting->id }}"
                                                            name="settings[{{ $setting->id }}][min_balance]"
                                                            value="{{ $setting->min_balance }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="max_balance_{{ $setting->id }}" class="form-label">Max
                                                            Balance</label>
                                                        <input type="number" class="form-control"
                                                            id="max_balance_{{ $setting->id }}"
                                                            name="settings[{{ $setting->id }}][max_balance]"
                                                            value="{{ $setting->max_balance }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="min_count_{{ $setting->id }}" class="form-label">Min
                                                            Count</label>
                                                        <input type="number" class="form-control"
                                                            id="min_count_{{ $setting->id }}"
                                                            name="settings[{{ $setting->id }}][min_count]"
                                                            value="{{ $setting->min_count }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="max_count_{{ $setting->id }}" class="form-label">Max
                                                            Count</label>
                                                        <input type="number" class="form-control"
                                                            id="max_count_{{ $setting->id }}"
                                                            name="settings[{{ $setting->id }}][max_count]"
                                                            value="{{ $setting->max_count }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="auto_run_time_{{ $setting->id }}"
                                                            class="form-label">Auto
                                                            Run Time</label>
                                                        <input type="time" class="form-control"
                                                            id="auto_run_time_{{ $setting->id }}"
                                                            name="settings[{{ $setting->id }}][auto_run_time]"
                                                            value="{{ $setting->auto_run_time }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-primary">Update POS Settings</button>
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
        $(document).ready(function() {
            // Pre-select branch and load POS data on page load
            const branchId = $('#branch_id').val();
            const posIds = @json($posIds);

            if (branchId) {
                loadPOSData(branchId, posIds);
            }
            // Handle branch change
            $('#branch_id').change(function() {
                const branchId = $(this).val();
                loadPOSData(branchId, posIds);
            });

            function loadPOSData(branchId, posSelected = null) {
                if (branchId) {
                    $.ajax({
                        url: "{{ route('dashboard.einvoices.get.pos') }}",
                        type: "POST",
                        data: {
                            branch_id: branchId,
                            posSelected: posSelected,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            $('#pos_per_branch').empty();

                            if (data.length > 0) {
                                $('#pos_per_branch').append(
                                    '<label class="form-check-label">Select POS:</label>');

                                $.each(data, function(index, pos) {
                                    console.log(data);

                                    const isChecked = pos.selected || (posSelected &&
                                        posSelected.includes(
                                            pos.id));

                                    $('#pos_per_branch').append(`
                            <div class="form-check pos-check-container mb-3">
                                <input class="form-check-input pos-checkbox"
                                       type="checkbox" name="pos_ids[]" id="pos_${pos.id}"
                                       value="${pos.id}" ${isChecked ? 'checked' : ''}>
                                <label class="form-check-label" for="pos_${pos.id}">${pos.name}</label>
                            </div>
                            <div id="per-machien-setting-${pos.id}" class="per-machine-settings mt-3"
                                 style="display: ${isChecked ? 'block' : 'none'};">
                                <div class="row gy-4">
                                    <div class="col-xl-6">
                                        <label for="min_total_${pos.id}" class="form-label">@lang('einvoice.mintotal')</label>
                                        <input type="number" name="min_total_${pos.id}" id="min_total_${pos.id}"
                                               class="form-control pos-input"
                                               placeholder="@lang('einvoice.mintotal')" ${isChecked ? 'required' : ''} value="${pos.min_total || ''}">
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="max_total_${pos.id}" class="form-label">@lang('einvoice.maxtotal')</label>
                                        <input type="number" name="max_total_${pos.id}" id="max_total_${pos.id}"
                                               class="form-control pos-input"
                                               placeholder="@lang('einvoice.maxtotal')" ${isChecked ? 'required' : ''} value="${pos.max_total || ''}">
                                    </div>
                                </div>

                                <div class="row gy-4 mt-1">
                                    <div class="col-xl-6">
                                        <label for="minnum_${pos.id}" class="form-label">@lang('einvoice.minnum')</label>
                                        <input type="number" name="minnum_${pos.id}" id="minnum_${pos.id}"
                                               class="form-control pos-input"
                                               placeholder="@lang('einvoice.minnum')" ${isChecked ? 'required' : ''} value="${pos.minnum || ''}">
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="maxnum_${pos.id}" class="form-label">@lang('einvoice.maxnum')</label>
                                        <input type="number" name="maxnum_${pos.id}" id="maxnum_${pos.id}"
                                               class="form-control pos-input"
                                               placeholder="@lang('einvoice.maxnum')" ${isChecked ? 'required' : ''} value="${pos.maxnum || ''}">
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="time_${pos.id}" class="form-label">@lang('einvoice.time')</label>
                                        <input type="time" name="auto_run_time_${pos.id}" id="time_${pos.id}"
                                               class="form-control pos-input" ${isChecked ? 'required' : ''} value="${pos.auto_run_time || ''}">
                                    </div>
                                </div>
                            </div>
                        `);
                                });

                                // Show/hide machine settings based on checkbox selection
                                $('#pos_per_branch').on('change', '.pos-checkbox', function() {
                                    const posId = $(this).val();
                                    const settingsContainer = $(
                                        `#per-machien-setting-${posId}`);
                                    settingsContainer.toggle(this.checked);

                                    // Toggle required attributes for inputs
                                    settingsContainer.find('input.pos-input').prop('required',
                                        this
                                        .checked);
                                });

                            } else {
                                $('#pos_per_branch').html('<p>No POS available for this branch.</p>');
                            }
                        },
                        error: function() {
                            alert('Error fetching POS data.');
                        }
                    });
                }
            }

        });
    </script>
@endsection
