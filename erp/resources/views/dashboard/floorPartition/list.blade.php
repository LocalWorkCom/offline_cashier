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
        <h4 class="fw-medium mb-0">@lang('floor.Partitions')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('floor.Partitions')</li>
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
                                @lang('floor.Partitions')</div>

                            @if (auth('admin')->user()->hasPermissionTo('create floor_partitions', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('floor.AddPartition')
                                </button>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('create floor_partitions', 'admin'))
                                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('floorPartition.store') }}" method="POST"
                                                class="needs-validation" novalidate>
                                                @csrf
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
                                                    <h6 class="modal-title" id="exampleModalLabel1">@lang('floor.AddPartition')</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="row gy-4">
                                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                            <label for="floor"
                                                                class="form-label">@lang('floor.Floor')</label>
                                                            <select class="form-select" id="floor" name="floor_id"
                                                                required>
                                                                <option value="" disabled selected>@lang('floor.ChooseFloor')
                                                                </option>
                                                                @foreach ($floors as $floor)
                                                                    <option value="{{ $floor->id }}">
                                                                        {{ $floor->name_ar . ' | ' . $floor->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterFloor')
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('floor.ArabicName')</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="@lang('floor.ArabicName')" name="name_ar" required>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterArabicName')
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('floor.EnglishName')</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="@lang('floor.EnglishName')" name="name_en" required>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEnglishName')
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('floor.Capacity')</label>
                                                            <input type="number" class="form-control"
                                                                placeholder="@lang('floor.Capacity')" min="1"
                                                                name="capacity" required>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterCapacity')
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label class="form-label">@lang('floor.Type')</label>
                                                            <div class="form-check" id="typeIndoor-div"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="type" id="typeIndoor" value="1" required>
                                                                <label class="form-check-label" for="typeIndoor">
                                                                    @lang('floor.Indoor')
                                                                </label>
                                                            </div>
                                                            <div class="form-check" id="typeOutdoor-div"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="type" id="typeOutdoor" value="2">
                                                                <label class="form-check-label" for="typeOutdoor">
                                                                    @lang('floor.Outdoor')
                                                                </label>
                                                            </div>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEnglishName')
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label class="form-label">@lang('floor.Smoking')</label>
                                                            <div class="form-check" id="smoking-div"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="smoking" id="typeIndoor" value="1"
                                                                    required>
                                                                <label class="form-check-label" for="typeIndoor">
                                                                    @lang('floor.Smokin')
                                                                </label>
                                                            </div>
                                                            <div class="form-check" id="notSmoking-div"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="smoking" id="typeOutdoor" value="2">
                                                                <label class="form-check-label" for="typeOutdoor">
                                                                    @lang('floor.NoSmokin')
                                                                </label>
                                                            </div>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEnglishName')
                                                            </div>
                                                        </div>
                                                    </div>
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
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('update floor_partitions', 'admin'))
                                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form id="edit-floor-partition-form" action="" method="POST"
                                                class="needs-validation" novalidate>
                                                @csrf
                                                @method('PUT')
                                                @if ($errors->any())
                                                    @foreach ($errors->all() as $error)
                                                        <div class="alert alert-solid-danger alert-dismissible fade show">
                                                            {{ $error }}
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="alert" aria-label="Close">
                                                                <i class="bi bi-x"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="editModalLabel">@lang('floor.EditFloor')</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row gy-4">
                                                        <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="floor"
                                                                class="form-label">@lang('floor.Floor')</label>
                                                            <select id="edit_floor" class="form-select" name="floor_id"
                                                                required>
                                                                @foreach ($floors as $floor)
                                                                    <option value="{{ $floor->id }}">
                                                                        {{ $floor->name_ar . ' | ' . $floor->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="edit-name-ar"
                                                                class="form-label">@lang('floor.ArabicName')</label>
                                                            <input type="text" id="edit-name-ar" class="form-control"
                                                                name="name_ar" required>
                                                        </div>
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="edit-name-en"
                                                                class="form-label">@lang('floor.EnglishName')</label>
                                                            <input type="text" id="edit-name-en" class="form-control"
                                                                name="name_en" required>
                                                        </div>

                                                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                            <label for="input-placeholder"
                                                                class="form-label">@lang('floor.Capacity')</label>
                                                            <input type="number" class="form-control"
                                                                placeholder="@lang('floor.Capacity')" min="1"
                                                                id="edit-capacity" name="capacity" required>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterCapacity')
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label class="form-label">@lang('floor.Type')</label>
                                                            <div class="form-check" id="typeIndoor-divEdit"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="type" id="type-indoor" value="1"
                                                                    required>
                                                                <label class="form-check-label" for="type-indoor">
                                                                    @lang('floor.Indoor')
                                                                </label>
                                                            </div>
                                                            <div class="form-check" id="typeOutdoor-divEdit"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="type" id="type-outdoor" value="2">
                                                                <label class="form-check-label" for="type-outdoor">
                                                                    @lang('floor.Outdoor')
                                                                </label>
                                                            </div>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEnglishName')
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label class="form-label">@lang('floor.Smoking')</label>
                                                            <div class="form-check" id="smoking-divEdit"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="smoking" id="smoking-yes" value="1"
                                                                    required>
                                                                <label class="form-check-label" for="smoking-yes">
                                                                    @lang('floor.Smokin')
                                                                </label>
                                                            </div>
                                                            <div class="form-check" id="notSmoking-divEdit"
                                                                style="display:none">
                                                                <input class="form-check-input" type="radio"
                                                                    name="smoking" id="smoking-no" value="2">
                                                                <label class="form-check-label" for="smoking-no">
                                                                    @lang('floor.NoSmokin')
                                                                </label>
                                                            </div>
                                                            <div class="valid-feedback">
                                                                @lang('validation.Correct')
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                @lang('validation.EnterEnglishName')
                                                            </div>
                                                        </div>

                                                    </div>
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
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view floor_partitions', 'admin'))
                                <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="showModalLabel">@lang('floor.ShowFloor')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-12">
                                                        <label class="form-label">@lang('floor.Floor')</label>
                                                        <p id="show-floor" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.ArabicName')</label>
                                                        <p id="show-name-ar" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.EnglishName')</label>
                                                        <p id="show-name-en" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <label class="form-label">@lang('floor.Capacity')</label>
                                                        <p id="show-capacity" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.Type')</label>
                                                        <p id="show-type" class="form-control-static"></p>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label class="form-label">@lang('floor.Smoking')</label>
                                                        <p id="show-smoking" class="form-control-static"></p>
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
                            @endif

                        </div>

                        @if (auth('admin')->user()->hasPermissionTo('view floor_partitions', 'admin'))
                            <div class="card-body">
                                @if (session('message'))
                                    <div class="alert alert-solid-info alert-dismissible fade show">
                                        {{ session('message') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close">
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
                                <!-- Add this filter section -->
                                <div class="row mb-3">
                                    @unless (auth('admin')->user()->hasRole('Branch Manager'))
                                        <div class="col-md-3">
                                            <label for="branch-filter" class="form-label">@lang('floor.FilterByBranch')</label>
                                            <select class="form-select" id="branch-filter">
                                                <option value="">@lang('floor.AllBranches')</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}"
                                                        {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                        {{ $branch->name_ar . ' | ' . $branch->name_en }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endunless
                                </div>
                                @if ($Partitions)
                                    <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th scope="col">@lang('floor.ID')</th>
                                                <th scope="col">@lang('floor.ArabicName')</th>
                                                <th scope="col">@lang('floor.EnglishName')</th>
                                                <th scope="col">@lang('floor.Type')</th>
                                                <th scope="col">@lang('floor.Smoking')</th>
                                                <th scope="col">@lang('floor.Capacity')</th>
                                                <th scope="col">@lang('floor.Floor')</th>
                                                <th scope="col">@lang('branch.Branch')</th>
                                                <th scope="col">@lang('category.Actions')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($Partitions as $partition)
                                                <tr>
                                                    <td>{{ $partition->id }}</td>
                                                    <td>{{ $partition->name_ar }}</td>
                                                    <td>{{ $partition->name_en }}</td>
                                                    <td>{{ $partition->type == 1 ? __('floor.Indoor') : ($partition->type == 2 ? __('floor.Outdoor') : __('floor.Both')) }}
                                                    </td>
                                                    <td>{{ $partition->smoking == 1 ? __('floor.Smokin') : ($partition->smoking == 2 ? __('floor.NoSmokin') : __('floor.Both')) }}
                                                    </td>
                                                    <td>{{ $partition->capacity }}</td>
                                                    <td>{{ $partition->floors != null ? $partition->floors->name_ar . ' | ' . $partition->floors->name_en : '' }}
                                                    </td>
                                                    <td>{{ $partition->floors && $partition->floors->branches ? $partition->floors->branches->name_ar . ' | ' . $partition->floors->branches->name_en : '' }}
                                                    </td>
                                                    <td>

                                                        @if (auth('admin')->user()->hasPermissionTo('view floor_partitions', 'admin'))
                                                            <!-- Show Button -->
                                                            <a href="javascript:void(0);"
                                                                class="btn btn-info-light btn-wave show-floor-partition-btn"
                                                                data-id="{{ $partition->id }}" data-bs-toggle="modal"
                                                                data-bs-target="#showModal">
                                                                @lang('category.show') <i class="ri-eye-line"></i>
                                                            </a>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('update floor_partitions', 'admin'))
                                                            <!-- Edit Button -->
                                                            <button type="button"
                                                                class="btn btn-orange-light btn-wave edit-floor-partition-btn"
                                                                data-id="{{ $partition->id }}" data-bs-toggle="modal"
                                                                data-bs-target="#editModal">
                                                                @lang('category.edit') <i class="ri-edit-line"></i>
                                                            </button>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('delete floor_partitions', 'admin'))
                                                            <!-- Delete Button -->
                                                            <form class="d-inline" id="delete-form-{{ $partition->id }}"
                                                                action="{{ route('floorPartition.delete', $partition->id) }}"
                                                                method="POST" onsubmit="return confirmDelete()">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button"
                                                                    onclick="delete_item({{ $partition->id }})"
                                                                    class="btn btn-danger-light btn-wave">
                                                                    @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('view tables', 'admin'))
                                                            <button type="button"
                                                                onclick="showTables({{ $partition->id }}, 'partition')"
                                                                class="btn btn-danger-light btn-wave">
                                                                {{ __('floor.Tables') }}
                                                            </button>
                                                        @endif
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        @endcan

                </div>
            </div>
        </div>
        <!-- End:: row-4 -->

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

<!-- INTERNAL DATADABLES JS -->
@vite('resources/assets/js/datatables.js')
@vite('resources/assets/js/validation.js')
@vite('resources/assets/js/choices.js')
@vite('resources/assets/js/modal.js')
@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Branch filter change handler
        $('#branch-filter').on('change', function() {
            var branchId = $(this).val();
            var url = "{{ route('floorPartitions.list') }}";

            if (branchId) {
                url += "?branch_id=" + branchId;
            }

            window.location.href = url;
        });
        $('.edit-floor-partition-btn').on('click', function() {
            var floorPartitionId = this.getAttribute('data-id');
            var get_url = "{{ route('floorPartition.show', 'id') }}";
            var edit_url = "{{ route('floorPartition.update', 'id') }}";
            get_url = get_url.replace('id', floorPartitionId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    console.log(data);

                    $('input[name="type"]').prop('checked', false);
                    $('input[name="smoking"]').prop('checked', false);
                    $('#typeIndoor-divEdit, #typeOutdoor-divEdit, #smoking-divEdit, #notSmoking-divEdit')
                        .hide();

                    // Populate the modal with the data
                    $('#edit-name-ar').val(data.name_ar);
                    $('#edit-name-en').val(data.name_en);
                    $('#edit-capacity').val(data.capacity);
                    $('#edit_floor').val(data.floor_id);

                    if (data.floors.type == 1 || data.floors.type == 3) {
                        $('#typeIndoor-divEdit').css('display', 'block');
                    }
                    if (data.floors.type == 2 || data.floors.type == 3) {
                        $('#typeOutdoor-divEdit').css('display', 'block');
                    }

                    if (data.floors.smoking == 1 || data.floors.smoking == 3) {
                        $('#smoking-divEdit').css('display', 'block');
                    }
                    if (data.floors.smoking == 2 || data.floors.smoking == 3) {
                        $('#notSmoking-divEdit').css('display', 'block');
                    }

                    if (data.type == 1) {
                        $('#type-indoor').prop('checked', true);
                    } else {
                        $('#type-outdoor').prop('checked', true);
                    }

                    if (data.smoking == 1) {
                        $('#smoking-yes').prop('checked', true);
                    } else {
                        $('#smoking-no').prop('checked', true);
                    }

                    edit_url = edit_url.replace('id', floorPartitionId);
                    $('#edit-floor-partition-form').attr('action', edit_url);

                    // Show the modal
                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('.show-floor-partition-btn').on('click', function() {
            var floorPartitionId = this.getAttribute('data-id');
            var get_url = "{{ route('floorPartition.show', 'id') }}";
            get_url = get_url.replace('id', floorPartitionId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    console.log(data);

                    // Populate the modal with the data
                    $('#show-name-ar').text(data.name_ar);
                    $('#show-name-en').text(data.name_en);
                    $('#show-capacity').text(data.capacity);
                    $('#show-floor').text(data.floors.name_site);

                    if (data.type == 1) {
                        $('#show-type').text('{{ __('floor.Indoor') }}');
                    } else {
                        $('#show-type').text('{{ __('floor.Outdoor') }}');
                    }

                    if (data.smoking == 1) {
                        $('#show-smoking').text('{{ __('floor.Smokin') }}');
                    } else {
                        $('#show-smoking').text('{{ __('floor.NoSmokin') }}');
                    }

                    // Show the modal
                    $('#showModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('#floor').on('change', function() {
            var floorId = this.value;
            var get_url = "{{ route('floor.show', 'id') }}";
            get_url = get_url.replace('id', floorId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {

                    $('input[name="type"]').prop('checked', false);
                    $('input[name="smoking"]').prop('checked', false);
                    $('#typeIndoor-div, #typeOutdoor-div, #smoking-div, #notSmoking-div')
                        .hide();

                    if (data.type == 1 || data.type == 3) {
                        $('#typeIndoor-div').css('display', 'block');
                    }
                    if (data.type == 2 || data.type == 3) {
                        $('#typeOutdoor-div').css('display', 'block');
                    }

                    if (data.smoking == 1 || data.smoking == 3) {
                        $('#smoking-div').css('display', 'block');
                    }
                    if (data.smoking == 2 || data.smoking == 3) {
                        $('#notSmoking-div').css('display', 'block');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('#edit_floor').on('change', function() {
            var floorId = this.value;
            var get_url = "{{ route('floor.show', 'id') }}";
            get_url = get_url.replace('id', floorId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {

                    console.log(data);


                    $('input[name="type"]').prop('checked', false);
                    $('input[name="smoking"]').prop('checked', false);
                    $('#typeIndoor-divEdit, #typeOutdoor-divEdit, #smoking-divEdit, #notSmoking-divEdit')
                        .hide();

                    if (data.type == 1 || data.type == 3) {
                        $('#typeIndoor-divEdit').css('display', 'block');
                    }
                    if (data.type == 2 || data.type == 3) {
                        $('#typeOutdoor-divEdit').css('display', 'block');
                    }

                    if (data.smoking == 1 || data.smoking == 3) {
                        $('#smoking-divEdit').css('display', 'block');
                    }
                    if (data.smoking == 2 || data.smoking == 3) {
                        $('#notSmoking-divEdit').css('display', 'block');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });
    });

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

    function showTables(floorId, type) {
        if (floorId) {
            const links = "{{ url('dashboard/table/showAll') }}/" + floorId + "/" + type;
            window.location.href = links;
        }
    }
</script>
