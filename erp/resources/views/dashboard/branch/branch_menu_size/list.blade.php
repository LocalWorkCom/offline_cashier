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
        <h4 class="fw-medium mb-0">@lang('branch_menu_size.DishSizes')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('branches.list') }}">@lang('branch.Branches')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch_menu_size.DishSizes')</li>
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
                                @lang('branch_menu_size.DishSizes')</div>

                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-menu-form" action="" method="POST" class="needs-validation"
                                            novalidate>
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
                                                <h6 class="modal-title" id="editModalLabel">@lang('branch_menu_size.EditSize')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="branch" class="form-label">@lang('branch_menu_size.Price')</label>
                                                        <input type="number" id="edit-price" class="form-control"
                                                            name="price">
                                                    </div>

                                                    <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12">
                                                        <label class="form-label fw-bold">Add Menu Integration</label>
                                                        <div class="input-group mb-3">
                                                            <select id="integration-select" class="form-select">
                                                                <option value="">Select Integration</option>
                                                                @foreach ($all_menu_integrations as $integration)
                                                                    <option value="{{ $integration->id }}">{{ $integration->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button class="btn btn-outline-primary" type="button" id="add-integration-btn">
                                                                Add
                                                            </button>
                                                        </div>

                                                        <ul id="integration-list" class="list-group"></ul>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label class="form-label">@lang('branch_menu_size.Activation')</label>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="is_active"
                                                                id="edit-active" value="1" required>
                                                            <label class="form-check-label" for="active">
                                                                @lang('branch_menu_size.Active')
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="is_active"
                                                                id="edit-not-active" value="0"> <label
                                                                class="form-check-label" for="not-active">
                                                                @lang('branch_menu_size.NotActive')
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

                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('branch_menu_size.ShowCategory')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_size.Branch')</label>
                                                    <p id="show-menu-size-branch" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_size.DishSize')</label>
                                                    <p id="show-menu-size-dish" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_size.Price')</label>
                                                    <p id="show-menu-size-price" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_size.Activation')</label>
                                                    <p id="show-menu-size-activation" class="form-control-static"></p>
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
                                        <th scope="col">@lang('branch_menu_size.ID')</th>
                                        <th scope="col">@lang('branch_menu_size.Branch')</th>
                                        <th scope="col">@lang('branch_menu_size.Dish')</th>
                                        <th scope="col">@lang('branch_menu_size.DishSize')</th>
                                        <th scope="col">@lang('branch_menu_size.DefaultSize')</th>
                                        <th scope="col">@lang('branch_menu_size.Activation')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($branch_menu_sizes as $branch_menu_size)
                                        <tr>
                                            <td>{{ $branch_menu_size->id }}</td>
                                            <td>{{ $branch_menu_size->branches ? $branch_menu_size->branches->name_site : '' }}
                                            </td>
                                            <td>{{ $branch_menu_size->dishes ? $branch_menu_size->dishes->name_site : '' }}
                                            </td>
                                            <td>{{ $branch_menu_size->dishSizes ? $branch_menu_size->dishSizes->name_site : '' }}
                                            </td>
                                            <td>
                                                @if ($branch_menu_size->dishSizes && $branch_menu_size->dishSizes->default_size == 1)
                                                    {{ __('branch_menu_size.YesDefaultSize') }}
                                                @else
                                                    {{ __('branch_menu_size.NotDefaultSize') }}
                                                @endif
                                            </td>
                                            <td id="branch_menu_size_status_{{ $branch_menu_size->id }}">
                                                @if ($branch_menu_size->is_active == 1)
                                                    <span
                                                        class="badge bg-success">{{ __('branch_menu_size.Active') }}</span>
                                                @else
                                                    <span
                                                        class="badge bg-secondary">{{ __('branch_menu_size.NotActive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view branch_menu_sizes', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-info-light btn-wave show-menu-size-btn"
                                                        data-id="{{ $branch_menu_size->id }}" data-bs-toggle="modal"
                                                        data-bs-target="#showModal">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif


                                                @if (auth('admin')->user()->hasPermissionTo('update branch_menu_sizes', 'admin'))
                                                    <!-- Edit Button -->
                                                    <button type="button"
                                                        class="btn btn-orange-light btn-wave edit-menu-btn"
                                                        data-id="{{ $branch_menu_size->id }}" data-bs-toggle="modal"
                                                        data-bs-target="#editModal">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif


                                                @if (auth('admin')->user()->hasPermissionTo('active branch_menu_sizes', 'admin'))
                                                    <button type="button"
                                                        id="branch_menu_size_activation_{{ $branch_menu_size->id }}"
                                                        onclick="change_status_item({{ $branch_menu_size->id }})"
                                                        class="btn btn-{{ $branch_menu_size->is_active == 1 ? 'danger' : 'success' }}-light btn-wave">
                                                        {{ $branch_menu_size->is_active == 0 ? __('branch_menu_size.Active') : __('branch_menu_size.NotActive') }}
                                                    </button>
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
        let currentDishMenuId = null;
        let currentBranchMenuId = null;
        let integrationIndex = 0;
        let currentMenusIntegrationDish = [];

        $('.edit-menu-btn').on('click', function () {
            const dishMenuId = $(this).data('id');
            currentDishMenuId = dishMenuId;

            const getUrl = "{{ route('branch.menu.sizes.show', 'id') }}".replace('id', dishMenuId);
            const editUrl = "{{ route('branch.menu.sizes.update', 'id') }}".replace('id', dishMenuId);

            // Show loading state
            $('#integration-list').html('<li class="list-group-item text-center text-muted">Loading...</li>');
            $('#editModal').modal('show');

            $.ajax({
                url: getUrl,
                type: 'GET',
                success: function (data) {
                    // Fill main fields
                    $('#edit-price').val(data.price ?? '');
                    $('#edit-active').prop('checked', data.is_active == 1);
                    $('#edit-not-active').prop('checked', data.is_active != 1);

                    const sizes = Array.isArray(data.menus_integration_dish_sizes)
                        ? data.menus_integration_dish_sizes
                        : [];                        

                    // Save branch_menu_id globally
                    currentBranchMenuId = data.branch_menu_id ?? '';
                    currentMenusIntegrationDish = data.menus_integration_dish ?? [];
                    let integrationsHtml = '';

                    if (sizes.length > 0) {
                        sizes.forEach((item, index) => {
                            integrationsHtml += buildIntegrationItem(item, dishMenuId, currentBranchMenuId, index);
                        });
                        integrationIndex = sizes.length;
                    } else {
                        integrationsHtml = `
                            <li class="list-group-item text-muted text-center">
                                No menu integrations found
                            </li>`;
                        integrationIndex = 0;
                    }

                    $('#integration-list').html(integrationsHtml);
                    $('#edit-menu-form').attr('action', editUrl);
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    $('#integration-list').html('<li class="list-group-item text-danger text-center">Error loading data</li>');
                    alert('Failed to load menu details. Please try again.');
                }
            });
        });

        function buildIntegrationItem(item, dishMenuId, branchMenuId, index) {
            const name = item.menus_integrations?.name ?? item.name ?? '—';
            const integrationId = item.menus_integration_id ?? item.id ?? '';
            const price = item.price ?? '';

            return `
                <li class="list-group-item integration-item" data-index="${index}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">${name}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-integration">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    <input type="hidden" name="menus_integration_dish_sizes[${index}][menus_integration_id]" value="${integrationId}">
                    <input type="hidden" name="menus_integration_dish_sizes[${index}][id]" value="${item.id ?? ''}">
                    <input type="hidden" name="menus_integration_dish_sizes[${index}][branch_menu_id]" value="${branchMenuId}">
                    <input type="hidden" name="menus_integration_dish_sizes[${index}][menus_size_id]" value="${dishMenuId}">

                    <div class="mb-2">
                        <label class="form-label d-block mb-1">Price</label>
                        <input type="number" step="0.01" class="form-control"
                            name="menus_integration_dish_sizes[${index}][price]"
                            value="${price}" placeholder="Enter price">
                    </div>
                </li>`;
        }

        $('#add-integration-btn').on('click', function () {
            if (!Array.isArray(currentMenusIntegrationDish) || currentMenusIntegrationDish.length === 0) {
                alert('Please add an integration to dish menu first');
                return;
            }

            const selectedId = $('#integration-select').val();
            const selectedText = $('#integration-select option:selected').text();

            if (!selectedId) {
                alert('Please select an integration first');
                return;
            }

            // Prevent duplicates
            const exists = $(`#integration-list input[name*="[menus_integration_id]"][value="${selectedId}"]`).length > 0;
            if (exists) {
                alert('This integration is already added.');
                return;
            }

            const index = integrationIndex++;
            const newIntegration = {
                id: '',
                menus_integration_id: selectedId,
                name: selectedText,
                price: ''
            };

            const newItem = $(buildIntegrationItem(newIntegration, currentDishMenuId, currentBranchMenuId, index)).hide().fadeIn();
            $('#integration-list').append(newItem);
            $('#integration-select').val('').trigger('change');
        });

        $(document).on('click', '.remove-integration', function () {
            $(this).closest('.integration-item').fadeOut(function () {
                $(this).remove();
            });
        });

        $('#edit-menu-form').on('submit', function (e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                success: function (response) {
                    $('#editModal').modal('hide');
                    alert('Menu sizes updated successfully!');
                    location.reload();
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    alert('Failed to update menu sizes. Please check your input.');
                }
            });
        });


        $('.show-menu-size-btn').on('click', function() {
            var dishMenuId = this.getAttribute('data-id');
            var get_url = "{{ route('branch.menu.sizes.show', 'id') }}";
            get_url = get_url.replace('id', dishMenuId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data
                    console.log(data);

                    $('#show-menu-size-branch').text(data.branches.name_site);
                    $('#show-menu-size-dish').text(data.dish_sizes.name_site);
                    $('#show-menu-size-price').text(data.price);
                    if (data.is_active == 1) {
                        $('#show-menu-size-activation').text(
                            '{{ __('branch_menu_size.Active') }}');
                    } else {
                        $('#show-menu-size-activation').text(
                            '{{ __('branch_menu_size.NotActive') }}');
                    }

                    // Show the modal
                    $('#showModal').modal('show');
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

    function change_status_item(dishMenuId) {
        Swal.fire({
            title: @json(__('validation.Alert')),
            text: @json(__('validation.ChangeStatucConfirm')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: @json(__('validation.Change')),
            cancelButtonText: @json(__('validation.Cancel')),
            confirmButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                var edit_status_url = "{{ route('branch.menu.sizes.changeStatus', 'id') }}";
                edit_status_url = edit_status_url.replace('id', dishMenuId);
                $.ajax({
                    url: edit_status_url,
                    type: 'GET',
                    success: function(data) {
                        if (data.is_active == 1) {
                            $('#branch_menu_size_status_' + dishMenuId).text(
                                '{{ __('branch_menu_size.Active') }}');
                            $('#branch_menu_size_activation_' + dishMenuId).text(
                                '{{ __('branch_menu_size.NotActive') }}');
                        } else {
                            $('#branch_menu_size_status_' + dishMenuId).text(
                                '{{ __('branch_menu_size.NotActive') }}');
                            $('#branch_menu_size_activation_' + dishMenuId).text(
                                '{{ __('branch_menu_size.Active') }}');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error: ' + error);
                    }
                });
            }
        });
    }
</script>
