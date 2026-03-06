@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- Custom CSS to stabilize layout -->
    <style>
        .dataTables_wrapper {
            position: relative;
            overflow: auto;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('shift.shifts')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('shift.shifts')</li>
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
                                @lang('shift.shifts')
                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('create shifts', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#createModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('shift.add')
                                </button>
                            @endif
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
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th scope="col">@lang('shift.ID')</th>
                                            <th scope="col">@lang('shift.name')</th>
                                            <th scope="col">@lang('shift.description')</th>
                                            <th scope="col">@lang('shift.days')</th>
                                            <th scope="col">@lang('shift.Actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($shifts as $shift)
                                            <tr>
                                                <td>{{ $shift->id }}</td>
                                                <td>{{ app()->getLocale() === 'ar' ? $shift->name_ar : $shift->name_en }}</td>
                                                <td>{{ app()->getLocale() === 'ar' ? ($shift->description_ar ?? '------') : ($shift->description_en ?? '------') }}</td>
                                                <td>
                                                    @if ($shift->details->count() > 0)
                                                        <ul>
                                                            @foreach ($shift->details as $detail)
                                                                <li>
                                                                    @switch($detail->day_index)
                                                                        @case(0)
                                                                            @lang('shiftDetail.sunday')
                                                                        @break
                                                                        @case(1)
                                                                            @lang('shiftDetail.monday')
                                                                        @break
                                                                        @case(2)
                                                                            @lang('shiftDetail.tuesday')
                                                                        @break
                                                                        @case(3)
                                                                            @lang('shiftDetail.wednesday')
                                                                        @break
                                                                        @case(4)
                                                                            @lang('shiftDetail.thursday')
                                                                        @break
                                                                        @case(5)
                                                                            @lang('shiftDetail.friday')
                                                                        @break
                                                                        @case(6)
                                                                            @lang('shiftDetail.saturday')
                                                                        @break
                                                                    @endswitch
                                                                    : {{ $detail->timetable->name_ar ?? 'N/A' }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        @lang('shift.noDaysAssigned')
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (auth('admin')->user()->hasPermissionTo('view shifts', 'admin'))
                                                        <button type="button"
                                                            class="btn btn-info-light btn-wave show-shift-btn"
                                                            data-id="{{ $shift->id }}"
                                                            data-name-ar="{{ $shift->name_ar }}"
                                                            data-name-en="{{ $shift->name_en }}"
                                                            data-description-ar="{{ $shift->description_ar ?? '------' }}"
                                                            data-description-en="{{ $shift->description_en ?? '------' }}"
                                                            data-details="{{ json_encode($shift->details) }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#showModal">
                                                            @lang('shift.show') <i class="ri-eye-line"></i>
                                                        </button>
                                                    @endif
                                                    @if (auth('admin')->user()->hasPermissionTo('update shifts', 'admin'))
                                                        <button type="button"
                                                            class="btn btn-orange-light btn-wave edit-shift-btn"
                                                            data-id="{{ $shift->id }}"
                                                            data-name-ar="{{ $shift->name_ar }}"
                                                            data-name-en="{{ $shift->name_en }}"
                                                            data-description-ar="{{ $shift->description_ar }}"
                                                            data-description-en="{{ $shift->description_en }}"
                                                            data-details="{{ json_encode($shift->details) }}"
                                                            data-route="{{ route('shift.update', ':id') }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal">
                                                            @lang('shift.edit') <i class="ri-edit-line"></i>
                                                        </button>
                                                    @endif
                                                    @if (auth('admin')->user()->hasPermissionTo('delete shifts', 'admin'))
                                                        <form class="d-inline" id="delete-form-{{ $shift->id }}"
                                                            action="{{ route('shift.delete', $shift->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" onclick="delete_item({{ $shift->id }})"
                                                                class="btn btn-danger-light btn-wave">
                                                                @lang('shift.delete') <i class="ri-delete-bin-line"></i>
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
            </div>
            <!-- End:: row-4 -->
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('shift.store') }}" method="POST" class="needs-validation" novalidate>
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
                        <h6 class="modal-title" id="createModalLabel">@lang('shift.AddShift')</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-xl-6">
                                <label for="name_ar" class="form-label">@lang('shift.arabicName')</label>
                                <input type="text" id="name_ar" class="form-control" placeholder="@lang('shift.arabicName')"
                                    name="name_ar" required>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('validation.EnterArabicName')
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <label for="name_en" class="form-label">@lang('shift.englishName')</label>
                                <input type="text" id="name_en" class="form-control" placeholder="@lang('shift.englishName')"
                                    name="name_en" required>
                                <div class="valid-feedback">
                                    @lang('validation.Correct')
                                </div>
                                <div class="invalid-feedback">
                                    @lang('validation.EnterEnglishName')
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <label for="description_ar" class="form-label">@lang('shift.ArabicDesc')</label>
                                <textarea id="description_ar" class="form-control" name="description_ar"></textarea>
                            </div>
                            <div class="col-xl-6">
                                <label for="description_en" class="form-label">@lang('shift.EnglishDesc')</label>
                                <textarea id="description_en" class="form-control" name="description_en"></textarea>
                            </div>
                            <!-- Shift Details Section -->
                            <div class="col-12">
                                <h5>@lang('shift.shiftDetails')</h5>
                                <div id="shift-details-container">
                                    <div class="row shift-detail-row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('shiftDetail.day')</label>
                                            <select name="shift_details[0][day_index]" class="form-control select2" required>
                                                <option value="">@lang('shiftDetail.selectDay')</option>
                                                <option value="0">@lang('shiftDetail.sunday')</option>
                                                <option value="1">@lang('shiftDetail.monday')</option>
                                                <option value="2">@lang('shiftDetail.tuesday')</option>
                                                <option value="3">@lang('shiftDetail.wednesday')</option>
                                                <option value="4">@lang('shiftDetail.thursday')</option>
                                                <option value="5">@lang('shiftDetail.friday')</option>
                                                <option value="6">@lang('shiftDetail.saturday')</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('shiftDetail.timetable')</label>
                                            <select name="shift_details[0][timetable_id]" class="form-control select2" required>
                                                <option value="">@lang('shiftDetail.selectTimetable')</option>
                                                @foreach ($timetables as $timetable)
                                                    <option value="{{ $timetable->id }}">
                                                        {{ app()->getLocale() == 'ar' ? $timetable->name_ar : $timetable->name_en }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-detail-btn"
                                                style="display: none;">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="add-detail-btn" class="btn btn-sm btn-primary mt-2">
                                    <i class="ri-add-line"></i> @lang('shift.addDay')
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">@lang('modal.close')</button>
                        <button type="submit" class="btn btn-outline-primary">@lang('modal.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Show Modal -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="showModalLabel">@lang('shift.showshift')</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-4">
                        <div class="col-12">
                            <label class="form-label">@lang('shift.arabicName')</label>
                            <p id="show-name-ar" class="form-control-static"></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">@lang('shift.englishName')</label>
                            <p id="show-name-en" class="form-control-static"></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">@lang('shift.arabicDescr')</label>
                            <p id="show-description-ar" class="form-control-static"></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">@lang('shift.englishDescr')</label>
                            <p id="show-description-en" class="form-control-static"></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">@lang('shift.shiftDetails')</label>
                            <ul id="show-details-list" class="form-control-static"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('modal.close')</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="edit-shift-form" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
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
                        <h6 class="modal-title" id="editModalLabel">@lang('shift.Editshift')</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-xl-6">
                                <label for="edit-name-ar" class="form-label">@lang('shift.arabicName')</label>
                                <input type="text" id="edit-name-ar" class="form-control" name="name_ar" required>
                            </div>
                            <div class="col-xl-6">
                                <label for="edit-name-en" class="form-label">@lang('shift.englishName')</label>
                                <input type="text" id="edit-name-en" class="form-control" name="name_en" required>
                            </div>
                            <div class="col-xl-6">
                                <label for="edit-description-ar" class="form-label">@lang('shift.arabicDescr')</label>
                                <textarea id="edit-description-ar" class="form-control" name="description_ar"></textarea>
                            </div>
                            <div class="col-xl-6">
                                <label for="edit-description-en" class="form-label">@lang('shift.englishDescr')</label>
                                <textarea id="edit-description-en" class="form-control" name="description_en"></textarea>
                            </div>
                            <!-- Shift Details Section -->
                            <div class="col-12">
                                <h5>@lang('shift.shiftDetails')</h5>
                                <div id="edit-shift-details-container">
                                    <!-- Dynamic content will be added here by JavaScript -->
                                </div>
                                <button type="button" id="edit-add-detail-btn" class="btn btn-sm btn-primary mt-2">
                                    <i class="ri-add-line"></i> @lang('shift.addDay')
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">@lang('modal.close')</button>
                        <button type="submit" class="btn btn-outline-primary">@lang('modal.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <!-- DATA-TABLES CDN -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Show Modal
            const showButtons = document.querySelectorAll('.show-shift-btn');
            const nameArElement = document.getElementById('show-name-ar');
            const nameEnElement = document.getElementById('show-name-en');
            const descriptionArElement = document.getElementById('show-description-ar');
            const descriptionEnElement = document.getElementById('show-description-en');
            const detailsListElement = document.getElementById('show-details-list');

            showButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const nameAr = this.getAttribute('data-name-ar') || 'N/A';
                    const nameEn = this.getAttribute('data-name-en') || 'N/A';
                    const descriptionAr = this.getAttribute('data-description-ar') || '------';
                    const descriptionEn = this.getAttribute('data-description-en') || '------';
                    let details;
                    try {
                        details = JSON.parse(this.getAttribute('data-details')) || [];
                    } catch (e) {
                        console.error('Error parsing details:', e);
                        details = [];
                    }

                    // Populate modal fields
                    nameArElement.textContent = nameAr;
                    nameEnElement.textContent = nameEn;
                    descriptionArElement.textContent = descriptionAr;
                    descriptionEnElement.textContent = descriptionEn;

                    // Populate shift details
                    detailsListElement.innerHTML = '';
                    if (details.length > 0) {
                        details.forEach(detail => {
                            const dayName = getDayName(detail.day_index);
                            const li = document.createElement('li');
                            li.textContent = `${dayName}: ${detail.timetable ? detail.timetable.name : 'N/A'}`;
                            detailsListElement.appendChild(li);
                        });
                    } else {
                        const li = document.createElement('li');
                        li.textContent = "@lang('shift.noDaysAssigned')";
                        detailsListElement.appendChild(li);
                    }
                });
            });

            // Edit Modal
            const editButtons = document.querySelectorAll('.edit-shift-btn');
            const editForm = document.getElementById('edit-shift-form');
            const nameArInput = document.getElementById('edit-name-ar');
            const nameEnInput = document.getElementById('edit-name-en');
            const descriptionArInput = document.getElementById('edit-description-ar');
            const descriptionEnInput = document.getElementById('edit-description-en');
            const editDetailsContainer = document.getElementById('edit-shift-details-container');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const shiftId = this.getAttribute('data-id');
                    const nameAr = this.getAttribute('data-name-ar') || '';
                    const nameEn = this.getAttribute('data-name-en') || '';
                    const descriptionAr = this.getAttribute('data-description-ar') || '';
                    const descriptionEn = this.getAttribute('data-description-en') || '';
                    let details;
                    try {
                        details = JSON.parse(this.getAttribute('data-details')) || [];
                    } catch (e) {
                        console.error('Error parsing details:', e);
                        details = [];
                    }
                    const routeTemplate = this.getAttribute('data-route');

                    // Set form action dynamically
                    editForm.action = routeTemplate.replace(':id', shiftId);

                    // Populate fields
                    nameArInput.value = nameAr;
                    nameEnInput.value = nameEn;
                    descriptionArInput.value = descriptionAr;
                    descriptionEnInput.value = descriptionEn;

                    // Populate shift details
                    editDetailsContainer.innerHTML = '';
                    let editDetailCounter = details.length > 0 ? details.length : 1;
                    if (details.length > 0) {
                        details.forEach((detail, index) => {
                            const row = createDetailRow(index, detail.id, detail.day_index, detail.timetable_id, details.length > 1);
                            editDetailsContainer.appendChild(row);
                        });
                    } else {
                        const row = createDetailRow(0, '', '', '', false);
                        editDetailsContainer.appendChild(row);
                    }

                    // Reinitialize Select2 for new elements
                    $(editDetailsContainer).find('.select2').select2({
                        dropdownParent: $('#editModal')
                    });
                });
            });

            // Add new detail row in create modal
            let detailCounter = 1;
            document.getElementById('add-detail-btn').addEventListener('click', function() {
                const container = document.getElementById('shift-details-container');
                const newRow = createDetailRow(detailCounter, '', '', '', true);
                container.appendChild(newRow);
                detailCounter++;

                // Initialize Select2 for the new element
                $(newRow).find('.select2').select2({
                    dropdownParent: $('#createModal')
                });

                // Show remove buttons for all rows if there's more than one
                if (container.children.length > 1) {
                    container.querySelectorAll('.remove-detail-btn').forEach(btn => {
                        btn.style.display = 'block';
                    });
                }
            });

            // Add new detail row in edit modal
            let editDetailCounter = 1;
            document.getElementById('edit-add-detail-btn').addEventListener('click', function() {
                const container = document.getElementById('edit-shift-details-container');
                const newRow = createDetailRow(editDetailCounter, '', '', '', true);
                container.appendChild(newRow);
                editDetailCounter++;

                // Initialize Select2 for the new element
                $(newRow).find('.select2').select2({
                    dropdownParent: $('#editModal')
                });

                // Show remove buttons for all rows if there's more than one
                if (container.children.length > 1) {
                    container.querySelectorAll('.remove-detail-btn').forEach(btn => {
                        btn.style.display = 'block';
                    });
                }
            });

            // Remove detail row
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-detail-btn') || e.target.closest('.remove-detail-btn')) {
                    const row = e.target.closest('.shift-detail-row');
                    row.remove();

                    // Hide remove buttons if only one row left
                    const container = row.parentElement;
                    if (container.children.length === 1) {
                        container.querySelector('.remove-detail-btn').style.display = 'none';
                    }
                }
            });

            // Helper function to create a detail row
            function createDetailRow(index, detailId, dayIndex, timetableId, showRemoveButton) {
                const row = document.createElement('div');
                row.className = 'row shift-detail-row mb-3';
                row.innerHTML = `
                    <input type="hidden" name="shift_details[${index}][id]" value="${detailId || ''}">
                    <div class="col-md-4">
                        <label class="form-label">@lang('shiftDetail.day')</label>
                        <select name="shift_details[${index}][day_index]" class="form-control select2" required>
                            <option value="">@lang('shiftDetail.selectDay')</option>
                            <option value="0" ${dayIndex == 0 ? 'selected' : ''}>@lang('shiftDetail.sunday')</option>
                            <option value="1" ${dayIndex == 1 ? 'selected' : ''}>@lang('shiftDetail.monday')</option>
                            <option value="2" ${dayIndex == 2 ? 'selected' : ''}>@lang('shiftDetail.tuesday')</option>
                            <option value="3" ${dayIndex == 3 ? 'selected' : ''}>@lang('shiftDetail.wednesday')</option>
                            <option value="4" ${dayIndex == 4 ? 'selected' : ''}>@lang('shiftDetail.thursday')</option>
                            <option value="5" ${dayIndex == 5 ? 'selected' : ''}>@lang('shiftDetail.friday')</option>
                            <option value="6" ${dayIndex == 6 ? 'selected' : ''}>@lang('shiftDetail.saturday')</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">@lang('shiftDetail.timetable')</label>
                        <select name="shift_details[${index}][timetable_id]" class="form-control select2" required>
                            <option value="">@lang('shiftDetail.selectTimetable')</option>
                            @foreach ($timetables as $timetable)
                                <option value="{{ $timetable->id }}" ${timetableId == '{{ $timetable->id }}' ? 'selected' : ''}>
                                    {{ app()->getLocale() == 'ar' ? $timetable->name_ar : $timetable->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-detail-btn" style="${showRemoveButton ? 'display: block;' : 'display: none;'}">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                `;
                return row;
            }

            // Helper function to get day name
            function getDayName(dayIndex) {
                const days = {
                    0: "@lang('shiftDetail.sunday')",
                    1: "@lang('shiftDetail.monday')",
                    2: "@lang('shiftDetail.tuesday')",
                    3: "@lang('shiftDetail.wednesday')",
                    4: "@lang('shiftDetail.thursday')",
                    5: "@lang('shiftDetail.friday')",
                    6: "@lang('shiftDetail.saturday')"
                };
                return days[dayIndex] || "@lang('shift.noDaysAssigned')";
            }

            editForm.addEventListener('submit', function(e) {
                // Re-index all detail rows before submitting
                const rows = editDetailsContainer.querySelectorAll('.shift-detail-row');
                rows.forEach((row, idx) => {
                    // Update all input/select names to use the new index
                    row.querySelectorAll('input, select').forEach(input => {
                        input.name = input.name.replace(/shift_details\[\d+\]/, `shift_details[${idx}]`);
                    });
                });
            });
        });

        function delete_item(id) {
            Swal.fire({
                title: "@lang('shift.warning')",
                text: "@lang('shift.deleteMsg')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "@lang('shift.yesDelete')",
                cancelButtonText: "@lang('shift.cancelDelete')",
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection