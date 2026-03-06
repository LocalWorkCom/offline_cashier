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
        <h4 class="fw-medium mb-0">@lang('rate.Rates')</h4>
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
                            onclick="window.location.href='{{ route('rates.list') }}'">@lang('rate.Rates')</a>
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
                            <div class="card-title">
                                @lang('rate.Rates')</div>

                                @if (auth('admin')->user()->hasPermissionTo('create rates', 'admin'))

                            <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                data-bs-target="#exampleModal">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('rate.AddRate')
                            </button>
                            @endif
                            <!-- Modal for Adding a Rate -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('rate.store') }}" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                                            @csrf
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
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('rate.AddRate')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="rate-value" class="form-label">@lang('rate.Value')</label>
                                                        <select class="form-select" name="value" required>
                                                            <option value="" disabled selected>@lang('rate.SelectValue')</option>
                                                            <option value="1" {{ old('value') == 1 ? 'selected' : '' }}>1</option>
                                                            <option value="2" {{ old('value') == 2 ? 'selected' : '' }}>2</option>
                                                            <option value="3" {{ old('value') == 3 ? 'selected' : '' }}>3</option>
                                                            <option value="4" {{ old('value') == 4 ? 'selected' : '' }}>4</option>
                                                            <option value="5" {{ old('value') == 5 ? 'selected' : '' }}>5</option>
                                                        </select>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterValue')</div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="rate-name" class="form-label">@lang('rate.Note')</label>
                                                        <textarea type="text" class="form-control" placeholder="@lang('rate.Note')" name="note">{{ old('note') }}</textarea>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterNote')</div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="rate-active" class="form-label">@lang('rate.active')</label>
                                                        <div>
                                                            <label class="form-check-label me-3">
                                                                <input type="radio" class="form-check-input" name="active" value="1" {{ old('active') == '1' ? 'checked' : '' }} required> @lang('rate.Active')
                                                            </label>
                                                            <label class="form-check-label">
                                                                <input type="radio" class="form-check-input" name="active" value="0" {{ old('active') == '0' ? 'checked' : '' }} required> @lang('rate.Inactive')
                                                            </label>
                                                        </div>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterIsActive')</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('modal.close')</button>
                                                <button type="submit" class="btn btn-outline-primary">@lang('modal.save')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal for Editing a Rate -->
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-rate-form" action="" method="POST"
                                            enctype="multipart/form-data" class="needs-validation" novalidate>
                                            @csrf
                                            @method('PUT')
                                            @if ($errors->any())
                                                @foreach ($errors->all() as $error)
                                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                                        {{ $error }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                            aria-label="Close">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endif
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="editModalLabel">@lang('rate.EditRate')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="edit-value" class="form-label">@lang('rate.Value')</label>
                                                        <select class="form-select" id="edit-value" name="value" required>
                                                            <option value="" disabled selected>@lang('rate.SelectValue')</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterValue')</div>
                                                    </div>                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="edit-expiration-date"
                                                            class="form-label">@lang('rate.Note')</label>
                                                        <textarea type="text" id="edit-note" class="form-control"
                                                                  name="note"></textarea>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterNote')</div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="edit-active" class="form-label">@lang('rate.active')</label>
                                                        <div>
                                                            <label class="form-check-label me-3">
                                                                <input type="radio" class="form-check-input" name="active" value="1" required> @lang('rate.Active')
                                                            </label>
                                                            <label class="form-check-label">
                                                                <input type="radio" class="form-check-input" name="active" value="0" required> @lang('rate.Inactive')
                                                            </label>
                                                        </div>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterIsActive')</div>
                                                    </div>                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">@lang('modal.close')</button>
                                                <button type="submit"
                                                    class="btn btn-outline-primary">@lang('modal.save')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal for Showing a Rate -->
                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('rate.ShowRate')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('rate.Value')</label>
                                                    <p id="show-value" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('rate.Note')</label>
                                                    <p id="show-note" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('rate.active')</label>
                                                    <p id="show-active" class="form-control-static"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">@lang('modal.close')</button>
                                        </div>
                                    </div>
                                </div>
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
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('category.ID')</th>
                                        <th scope="col">@lang('rate.Value')</th>
                                        <th scope="col">@lang('rate.Note')</th>
                                        <th scope="col">@lang('rate.active')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php $rowNumber = 1; @endphp
                                @foreach ($rates as $rate)
                                        <tr>
                                            <td>{{ $rowNumber++  }}</td>
                                            <td>{{ $rate->value }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($rate->note, 30) }}</td>
                                            <td>
                                            <span class="badge {{ $rate->active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $rate->active ? __('rate.Active') : __('rate.Inactive') }}
                                            </span>
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view rates', 'admin'))

                                                <!-- Show Button -->
                                                <a href="javascript:void(0);"
                                                    class="btn btn-info-light btn-wave show-rate-btn"
                                                    data-id="{{ $rate->id }}" data-value="{{ $rate->value }}"
                                                    data-note="{{ $rate->note }}" data-active="{{ $rate->active }}"
                                                    data-bs-toggle="modal" data-bs-target="#showModal">
                                                    @lang('category.show') <i class="ri-eye-line"></i>
                                                </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update rates', 'admin'))

                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-orange-light btn-wave edit-rate-btn"
                                                    data-id="{{ $rate->id }}" data-value="{{ $rate->value }}"
                                                    data-note="{{ $rate->note }}"
                                                    data-active="{{ $rate->active }}"
                                                    data-route="{{ route('rate.update', ':id') }}" data-bs-toggle="modal"
                                                    data-bs-target="#editModal">
                                                    @lang('category.edit') <i class="ri-edit-line"></i>
                                                </button>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete rates', 'admin'))

                                                <form class="d-inline" id="delete-form-{{ $rate->id }}"
                                                    action="{{ route('rate.delete', $rate->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" onclick="delete_item('{{ $rate->id }}')"
                                                        class="btn btn-danger-light btn-wave">
                                                        @lang('category.delete') <i class="ri-delete-bin-line"></i>
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
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-rate-btn');
        const editForm = document.getElementById('edit-rate-form');
        const valueInput = document.getElementById('edit-value'); // Dropdown for Value
        const noteInput = document.getElementById('edit-note'); // Note field
        const activeInputs = document.querySelectorAll('[name="active"]'); // Radio buttons for Active

        // Loop through all Edit buttons
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const rateId = this.getAttribute('data-id');
                const value = this.getAttribute('data-value');
                const note = this.getAttribute('data-note');
                const active = this.getAttribute('data-active'); // "1" or "0" for active status
                const routeTemplate = this.getAttribute('data-route');

                // Set form action dynamically
                editForm.action = routeTemplate.replace(':id', rateId);
                valueInput.value = value; // Set dropdown value
                noteInput.value = note; // Set note value

                // Set the correct radio button for 'active'
                activeInputs.forEach(radio => {
                    radio.checked = false; // Uncheck all radios first
                    if (radio.value === active) {
                        radio.checked = true; // Check the radio button that matches the active value
                    }
                });
            });
        });

        const showButtons = document.querySelectorAll('.show-rate-btn');
        const showValue = document.getElementById('show-value');
        const showNote = document.getElementById('show-note');
        const showActive = document.getElementById('show-active');

        showButtons.forEach(button => {
            button.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const note = this.getAttribute('data-note');
                const active = this.getAttribute('data-active'); // 1 or 0

                showValue.textContent = value;
                showNote.textContent = note;
                showActive.textContent = active == 1 ? '@lang("rate.Active")' : '@lang("rate.Inactive")';
            });
        });

    });

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
