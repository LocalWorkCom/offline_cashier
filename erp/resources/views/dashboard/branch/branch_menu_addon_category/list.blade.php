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
        <h4 class="fw-medium mb-0">@lang('branch_menu_addon_category.Categories')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('dashboard.home')}}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{route('branches.list')}}">@lang('branch.Branches')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch_menu_addon_category.Categories')</li>
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
                                @lang('branch_menu_addon_category.Categories')</div>

                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('branch_menu_addon_category.ShowCategory')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_addon_category.Branch')</label>
                                                    <p id="show-branch" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_addon_category.Category')</label>
                                                    <p id="show-dish-category" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_addon_category.Activation')</label>
                                                    <p id="show-activation" class="form-control-static"></p>
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
                                    <th scope="col">@lang('branch_menu_addon_category.ID')</th>
                                    <th scope="col">@lang('branch_menu_addon_category.Branch')</th>
                                    <th scope="col">@lang('branch_menu_addon_category.Category')</th>
                                    <th scope="col">@lang('branch_menu_addon_category.Activation')</th>
                                    <th scope="col">@lang('category.Actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($branch_menu_categories as $branch_menu_addon_category)
                                    <tr>
                                        <td>{{ $branch_menu_addon_category->id }}</td>
                                        <td>{{ ($branch_menu_addon_category->branches) ? $branch_menu_addon_category->branches->name_site : "" }}</td>
                                        <td>{{ ($branch_menu_addon_category->addonCategories) ? $branch_menu_addon_category->addonCategories->name_site : "" }}</td>
                                        <td id="branch_menu_addon_category_status_{{ $branch_menu_addon_category->id }}">
                                            @if(($branch_menu_addon_category->is_active == 1))
                                                <span class="badge bg-success">{{ __('branch_menu_addon_category.Active')}}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('branch_menu_addon_category.NotActive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (auth('admin')->user()->hasPermissionTo('view branch_menu_addons', 'admin'))
                                            <!-- Show Button -->
                                            <a href="javascript:void(0);"
                                               class="btn btn-info-light btn-wave show-category-btn"
                                               data-id="{{ $branch_menu_addon_category->id }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#showModal">
                                                @lang('category.show') <i class="ri-eye-line"></i>
                                            </a>
                                            @endif

                                            @if (auth('admin')->user()->hasPermissionTo('active branch_menu_addons', 'admin'))
                                            <button type="button" id="branch_menu_addon_category_activation_{{ $branch_menu_addon_category->id }}" onclick="change_status_item({{ $branch_menu_addon_category->id }})" class="btn btn-{{ ($branch_menu_addon_category->is_active == 1) ? 'danger' : 'success' }}-light btn-wave">
                                            {{ ($branch_menu_addon_category->is_active == 0) ? __('branch_menu_addon_category.Active') : __('branch_menu_addon_category.NotActive') }}
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

        $('.show-category-btn').on('click', function() {
            var dishCategoryId = this.getAttribute('data-id');
            var get_url = "{{ route('branch.menu.addon.category.show', 'id') }}";
            get_url = get_url.replace('id', dishCategoryId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {

                    // Populate the modal with the data
                    $('#show-dish-category').text(data.addon_categories.name_site);
                    $('#show-branch').text(data.branches.name_site);
                    if(data.is_active == 1){
                        $('#show-activation').text('{{ __('branch_menu_addon_category.Active')}}');
                    }else{
                        $('#show-activation').text('{{ __('branch_menu_addon_category.NotActive')}}');
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

    function change_status_item(dishCategoryId) {
        var edit_status_url = "{{ route('branch.menu.addon.category.changeStatus', 'id') }}";
        edit_status_url = edit_status_url.replace('id', dishCategoryId);
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
                var edit_status_url = "{{ route('branch.menu.addon.category.changeStatus', 'id') }}";
                edit_status_url = edit_status_url.replace('id', dishCategoryId);
                $.ajax({
                    url: edit_status_url,
                    type: 'GET',
                    success: function(data) {
                        if(data.is_active == 1){
                            $('#branch_menu_addon_category_status_'+dishCategoryId).text('{{ __('branch_menu_addon_category.Active')}}');
                            $('#branch_menu_addon_category_activation_'+dishCategoryId).text('{{ __('branch_menu_addon_category.NotActive')}}');
                        }else{
                            $('#branch_menu_addon_category_status_'+dishCategoryId).text('{{ __('branch_menu_addon_category.NotActive')}}');
                            $('#branch_menu_addon_category_activation_'+dishCategoryId).text('{{ __('branch_menu_addon_category.Active')}}');
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
