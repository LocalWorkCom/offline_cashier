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
        <h4 class="fw-medium mb-0">@lang('branch_menu.Menus')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('dashboard.home')}}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{route('branches.list')}}">@lang('branch.Branches')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch_menu.Menus')</li>
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
                                @lang('branch_menu.Menus')</div>

                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"aria-hidden="true">
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
                                                <h6 class="modal-title" id="editModalLabel">@lang('branch_menu_addon.EditAddon')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="branch" class="form-label">@lang('branch_menu_addon.Price')</label>
                                                        <input type="number" step="0.01" id="edit-price" class="form-control"
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
                                                        <label class="form-label">@lang('branch_menu_addon.Activation')</label>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="is_active"
                                                                id="edit-active" value="1" required>
                                                            <label class="form-check-label" for="active">
                                                                @lang('branch_menu_addon.Active')
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="is_active"
                                                                id="edit-not-active" value="0"> <label
                                                                class="form-check-label" for="not-active">
                                                                @lang('branch_menu_addon.NotActive')
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

                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('branch_menu.ShowCategory')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu.Branch')</label>
                                                    <p id="show-menu-branch" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu.Category')</label>
                                                    <p id="show-menu-category" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu.Dish')</label>
                                                    <p id="show-menu-dish" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu.Price')</label>
                                                    <p id="show-menu-price" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu.Activation')</label>
                                                    <p id="show-menu-activation" class="form-control-static"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('modal.close')</button>
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
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @endif
                                <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                <tr>
                                    <th scope="col">@lang('branch_menu.ID')</th>
                                    <th scope="col">@lang('branch_menu.Branch')</th>
                                    <th scope="col">@lang('branch_menu.Category')</th>
                                    <th scope="col">@lang('branch_menu.Dish')</th>
                                    <th scope="col">@lang('branch_menu.Activation')</th>
                                    <th scope="col">@lang('category.Actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($branch_menu_categories as $branch_menu)
                                    <tr>
                                        <td>{{ $branch_menu->id }}</td>
                                        <td>{{ ($branch_menu->branches) ? $branch_menu->branches->name_site : "" }}</td>
                                        <td>{{ ($branch_menu->branchMenuCategories) ? (($branch_menu->branchMenuCategories->dish_categories) ? $branch_menu->branchMenuCategories->dish_categories->name_site : "") : "" }}</td>
                                        <td>{{ ($branch_menu->dish) ? $branch_menu->dish->name_site : "" }}</td>
                                        <td id="branch_menu_status_{{ $branch_menu->id }}">
                                            @if(($branch_menu->is_active == 1))
                                                <span class="badge bg-success">{{ __('branch_menu.Active')}}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('branch_menu.NotActive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (auth('admin')->user()->hasPermissionTo('view branch_menus', 'admin'))

                                            <!-- Show Button -->
                                            <a href="javascript:void(0);"
                                               class="btn btn-info-light btn-wave show-menu-btn"
                                               data-id="{{ $branch_menu->id }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#showModal">
                                                @lang('category.show') <i class="ri-eye-line"></i>
                                            </a>
                                            @endif
                                            @if (auth('admin')->user()->hasPermissionTo('update branch_menus', 'admin'))

                                            <!-- Edit Button -->
                                            <button type="button"
                                                    class="btn btn-orange-light btn-wave edit-menu-btn"
                                                    data-id="{{ $branch_menu->id }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal">
                                                @lang('category.edit') <i class="ri-edit-line"></i>
                                            </button>
                                            @endif
                                            @if (auth('admin')->user()->hasPermissionTo('active branch_menus', 'admin'))

                                            <button type="button" id="branch_menu_activation_{{ $branch_menu->id }}" onclick="change_status_item({{ $branch_menu->id }})" class="btn btn-{{ ($branch_menu->is_active == 1) ? 'danger' : 'success' }}-light btn-wave">
                                            {{ ($branch_menu->is_active == 0) ? __('branch_menu.Active') : __('branch_menu.NotActive') }}
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
    $(document).ready(function(){
        <?php /*$('.edit-menu-btn').on('click', function() {
            var dishMenuId = this.getAttribute('data-id');
            var get_url = "{{ route('branch.menus.show', 'id') }}";
            var edit_url = "{{ route('branch.menus.update', 'id') }}";
            get_url = get_url.replace('id', dishMenuId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data
                    if (data.dish.has_sizes == 0) {
                        $('#price-input-container').show();
                        $('#price-input-container-no-size').hide();
                        $('#edit-price').val(data.price);
                    } else {
                        $('#price-input-container').hide();
                        $('#price-input-container-no-size').show();
                        $('#edit-price').val('');
                    }

                    if(data.is_active == 1){
                        $('#edit-active').prop('checked', true);
                    }else{
                        $('#edit-not-active').prop('checked', true);
                    }

                    edit_url = edit_url.replace('id', dishMenuId);
                    $('#edit-menu-form').attr('action', edit_url);

                    // Show the modal
                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });*/?>


        let currentDishMenuId = null;
        let integrationIndex = 0; // keep unique indexes

        $('.edit-menu-btn').on('click', function() {
            var dishMenuId = this.getAttribute('data-id');
            currentDishMenuId = dishMenuId; // save globally for later use
            var get_url = "{{ route('branch.menus.show', 'id') }}";
            var edit_url = "{{ route('branch.menus.update', 'id') }}";
            get_url = get_url.replace('id', dishMenuId);

            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    $('#edit-price').val(data.price);

                    if (data.is_active == 1) {
                        $('#edit-active').prop('checked', true);
                    } else {
                        $('#edit-not-active').prop('checked', true);
                    }

                    let integrationsHtml = '';
                    if (data.menus_integration_dishs && data.menus_integration_dishs.length > 0) {
                        data.menus_integration_dishs.forEach(function(dish_menu, index) {
                            integrationsHtml += buildIntegrationItem(dish_menu, dishMenuId, index);
                        });
                        integrationIndex = data.menus_integration_dishs.length; // set counter
                    } else {
                        integrationsHtml = `
                            <li class="list-group-item text-muted text-center">
                                No menu integrations found
                            </li>`;
                        integrationIndex = 0;
                    }

                    $('#integration-list').html(integrationsHtml);

                    edit_url = edit_url.replace('id', dishMenuId);
                    $('#edit-menu-form').attr('action', edit_url);

                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });


        // Helper to build integration HTML block
        function buildIntegrationItem(item, dishMenuId, index) {
            return `
                <li class="list-group-item integration-item" data-index="${index}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">${item.menus_integrations?.name ?? item.name ?? '—'}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-integration">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    <input type="hidden" name="menus_integration_dishs[${index}][menus_integration_id]"
                        value="${item.menus_integration_id ?? item.id ?? ''}">

                    <div class="mb-2">
                        <label class="form-label d-block mb-1">Price</label>
                        <input type="number" step="0.01" class="form-control"
                            name="menus_integration_dishs[${index}][price]"
                            value="${item.price ?? ''}" placeholder="Enter price">
                    </div>

                    <div class="mt-2">
                        <label class="form-label d-block mb-1">Is Taxed:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                name="menus_integration_dishs[${index}][is_taxed]"
                                value="1" ${item.is_taxed == 1 ? 'checked' : ''}>
                            <label class="form-check-label">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                name="menus_integration_dishs[${index}][is_taxed]"
                                value="0" ${item.is_taxed == 0 ? 'checked' : ''}>
                            <label class="form-check-label">No</label>
                        </div>
                    </div>

                    <div class="mt-2">
                        <label class="form-label d-block mb-1">Is Percentage:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input percentage-radio" type="radio"
                                data-index="${index}"
                                name="menus_integration_dishs[${index}][is_percentage]"
                                value="1" ${item.is_percentage == 1 ? 'checked' : ''}>
                            <label class="form-check-label">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input percentage-radio" type="radio"
                                data-index="${index}"
                                name="menus_integration_dishs[${index}][is_percentage]"
                                value="0" ${item.is_percentage == 0 ? 'checked' : ''}>
                            <label class="form-check-label">No</label>
                        </div>
                    </div>

                    <div class="mt-2 percentage-amount-field" id="percentage-amount-${index}"
                        style="display: ${item.is_percentage == 1 ? 'block' : 'none'};">
                        <label class="form-label d-block mb-1">Percentage Amount:</label>
                        <input type="text" class="form-control"
                            name="menus_integration_dishs[${index}][percentage_amount]"
                            value="${item.percentage_amount ?? ''}" placeholder="Enter % amount">
                    </div>

                    <input type="hidden" name="menus_integration_dishs[${index}][id]" value="${item.id ?? ''}">
                    <input type="hidden" name="menus_integration_dishs[${index}][branch_menu_id]" value="${dishMenuId ?? ''}">
                </li>`;
        }


        // Toggle percentage field dynamically
        $(document).on('change', '.percentage-radio', function() {
            const index = $(this).data('index');
            const value = $(this).val();
            $(`#percentage-amount-${index}`).toggle(value == '1');
        });


        // Add new integration from select
        $('#add-integration-btn').on('click', function() {
            const selectedId = $('#integration-select').val();
            const selectedText = $('#integration-select option:selected').text();

            if (!selectedId) {
                alert('Please select an integration first');
                return;
            }

            const index = integrationIndex++;
            const newIntegration = {
                id: '',
                menus_integration_id: selectedId,
                name: selectedText,
                is_taxed: 0,
                is_percentage: 0,
                price: '',
                percentage_amount: ''
            };

            $('#integration-list').append(buildIntegrationItem(newIntegration, currentDishMenuId, index));
            $('#integration-select').val('').trigger('change');
        });


        // Remove integration
        $(document).on('click', '.remove-integration', function() {
            $(this).closest('.integration-item').remove();
        });





        $('.show-menu-btn').on('click', function() {
            var dishMenuId = this.getAttribute('data-id');
            var get_url = "{{ route('branch.menus.show', 'id') }}";
            get_url = get_url.replace('id', dishMenuId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data

                    $('#show-menu-category').text(data.branch_menu_categories.dish_categories.name_site);
                    $('#show-menu-branch').text(data.branches.name_site);
                    $('#show-menu-dish').text(data.dish.name_site);
                    $('#show-menu-price').text(data.price);
                    if(data.is_active == 1){
                        $('#show-menu-activation').text('{{ __('branch_menu.Active')}}');
                    }else{
                        $('#show-menu-activation').text('{{ __('branch_menu.NotActive')}}');
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
                var edit_status_url = "{{ route('branch.menus.changeStatus', 'id') }}";
                edit_status_url = edit_status_url.replace('id', dishMenuId);
                $.ajax({
                    url: edit_status_url,
                    type: 'GET',
                    success: function(data) {
                        if(data.is_active == 1){
                            $('#branch_menu_status_'+dishMenuId).text('{{ __('branch_menu.Active')}}');
                            $('#branch_menu_activation_'+dishMenuId).text('{{ __('branch_menu.NotActive')}}');
                        }else{
                            $('#branch_menu_status_'+dishMenuId).text('{{ __('branch_menu.NotActive')}}');
                            $('#branch_menu_activation_'+dishMenuId).text('{{ __('branch_menu.Active')}}');
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
