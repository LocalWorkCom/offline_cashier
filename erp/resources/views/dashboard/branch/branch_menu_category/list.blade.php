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
        <h4 class="fw-medium mb-0">@lang('branch_menu_category.Categories')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('branches.list') }}">@lang('branch.Branches')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch_menu_category.Categories')</li>
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
                                @lang('branch_menu_category.Categories')</div>

                            <?php /*
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-floor-form" action="" method="POST" class="needs-validation" novalidate>
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
                                                <h6 class="modal-title" id="editModalLabel">@lang('branch_menu_category.EditCategory')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="branch" class="form-label">@lang('branch_menu_category.Branch')</label>
                                                        <input type="text" id="edit-branch" class="form-control" name="branch" readonly>
                                                    </div>

                                                    <div class="col-xl-12 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="category" class="form-label">@lang('branch_menu_category.Category')</label>
                                                        <input type="text" id="edit-category" class="form-control" name="category" readonly>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label class="form-label">@lang('branch_menu_category.Activation')</label>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="is_active" id="edit-active" value="1" required>
                                                            <label class="form-check-label" for="active">
                                                                @lang('branch_menu_category.Active')
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="is_active" id="edit-not-active" value="0">                                                            <label class="form-check-label" for="not-active">
                                                                @lang('branch_menu_category.NotActive')
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
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('modal.close')</button>
                                                <button type="submit" class="btn btn-outline-primary">@lang('modal.save')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            */
                            ?>

                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('branch_menu_category.ShowCategory')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_category.Branch')</label>
                                                    <p id="show-branch" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_category.Category')</label>
                                                    <p id="show-dish-category" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('branch_menu_category.Activation')</label>
                                                    <p id="show-activation" class="form-control-static"></p>
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
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('branch_menu_category.ID')</th>
                                        <th scope="col">@lang('branch_menu_category.Branch')</th>
                                        <th scope="col">@lang('branch_menu_category.Category')</th>
                                        <th scope="col">@lang('branch_menu_category.Activation')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($branch_menu_categories as $branch_menu_category)
                                        <tr>
                                            <td>{{ $branch_menu_category->id }}</td>
                                            <td>{{ $branch_menu_category->branches ? $branch_menu_category->branches->name_site : '' }}
                                            </td>
                                            <td>{{ $branch_menu_category->dish_categories ? $branch_menu_category->dish_categories->name_site : '' }}
                                            </td>
                                            <td>
                                                <span id="branch_menu_category_status_{{ $branch_menu_category->id }}"
                                                    class="badge success {{ $branch_menu_category->is_active == 1 ? 'bg-success' : 'bg-danger' }}">{{ $branch_menu_category->is_active == 1 ? __('branch_menu_category.Active') : __('branch_menu_category.NotActive') }}</span>

                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view branch_menu_categories', 'admin'))
                                                <!-- Show Button -->
                                                <a href="javascript:void(0);"
                                                    class="btn btn-info-light btn-wave show-category-btn"
                                                    data-id="{{ $branch_menu_category->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#showModal">
                                                    @lang('category.show') <i class="ri-eye-line"></i>
                                                </a>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('active branch_menu_categories', 'admin'))
                                                <button type="button"
                                                    id="branch_menu_category_activation_{{ $branch_menu_category->id }}"
                                                    onclick="change_status_item({{ $branch_menu_category->id }})"
                                                    class="btn btn-{{ $branch_menu_category->is_active == 1 ? 'danger' : 'success' }}-light btn-wave">
                                                    {{ $branch_menu_category->is_active == 0 ? __('branch_menu_category.Active') : __('branch_menu_category.NotActive') }}
                                                </button>
                                                @endif

                                                <?php /*
                                            <!-- Edit Button -->
                                            <button type="button"
                                                    class="btn btn-orange-light btn-wave edit-category-btn"
                                                    data-id="{{ $branch_menu_category->id }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal">
                                                @lang('category.edit') <i class="ri-edit-line"></i>
                                            </button>

                                            <!-- Delete Button -->
                                            <form class="d-inline" id="delete-form-{{ $branch_menu_category->id }}" action="{{ route('branch.categories.delete', $branch_menu_category->id) }}" method="POST" onsubmit="return confirmDelete()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="delete_item({{ $branch_menu_category->id }})" class="btn btn-danger-light btn-wave">
                                                    @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                            */
                                                ?>

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
        $('.edit-category-btn').on('click', function() {
            var dishCategoryId = this.getAttribute('data-id');
            var get_url = "{{ route('branch.categories.show', 'id') }}";
            var edit_url = "{{ route('branch.categories.update', 'id') }}";
            get_url = get_url.replace('id', dishCategoryId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data
                    $('#edit-branch').val(data.branches.name_site);
                    $('#edit-category').val(data.dish_categories.name_site);

                    if (data.is_active == 1) {
                        $('#edit-active').prop('checked', true);
                    } else {
                        $('#edit-not-active').prop('checked', true);
                    }

                    edit_url = edit_url.replace('id', dishCategoryId);
                    $('#edit-category-form').attr('action', edit_url);

                    // Show the modal
                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        // $('.change-status-btn').on('click', function() {
        //     var dishCategoryId = this.getAttribute('data-id');
        //     var edit_status_url = "{{ route('branch.categories.changeStatus', 'id') }}";
        //     edit_status_url = edit_status_url.replace('id', dishCategoryId);
        //     //AJAX request to fetch user details
        //     $.ajax({
        //         url: edit_status_url,
        //         type: 'GET',
        //         success: function(data) {
        //             if(data.is_active == 1){
        //                 $('#branch_menu_category_status_'+dishCategoryId).text('{{ __('branch_menu_category.Active') }}');
        //             }else{
        //                 $('#branch_menu_category_status_'+dishCategoryId).text('{{ __('branch_menu_category.NotActive') }}');
        //             }
        //         },
        //         error: function(xhr, status, error) {
        //             console.log('Error: ' + error);
        //         }
        //     });
        // });

        $('.show-category-btn').on('click', function() {
            var dishCategoryId = this.getAttribute('data-id');
            var get_url = "{{ route('branch.categories.show', 'id') }}";
            get_url = get_url.replace('id', dishCategoryId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data
                    $('#show-dish-category').text(data.dish_categories.name_site);
                    $('#show-branch').text(data.branches.name_site);
                    if (data.is_active == 1) {
                        $('#show-activation').text(
                            '{{ __('branch_menu_category.Active') }}');
                    } else {
                        $('#show-activation').text(
                            '{{ __('branch_menu_category.NotActive') }}');
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

    // function change_status_item(dishCategoryId) {
    //     //var button = document.querySelector('.change-status-btn[data-id="' + dishCategoryId + '"]');
    //     var button = document.querySelector('.change-status-btn_' + dishCategoryId);
    //     console.log(button);

    //     if (button) {
    //         if (button.classList.contains('on')) {
    //         alert(11);
    //             button.removeClass('on');
    //             //button.classList.add('off');
    //         } else {
    //             alert(22);
    //             button.removeClass('off');
    //             //button.classList.add('on');
    //         }
    //     }
    // }

    function change_status_item(dishCategoryId) {
        var edit_status_url = "{{ route('branch.categories.changeStatus', 'id') }}";
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
                var edit_status_url = "{{ route('branch.categories.changeStatus', 'id') }}";
                edit_status_url = edit_status_url.replace('id', dishCategoryId);
                $.ajax({
                    url: edit_status_url,
                    type: 'GET',
                    success: function(data) {
                        if (data.is_active == 1) {
                            $('#branch_menu_category_status_' + dishCategoryId).text(
                                '{{ __('branch_menu_category.Active') }}').addClass(
                                'bg-success').removeClass('bg-danger');
                            $('#branch_menu_category_activation_' + dishCategoryId).text(
                                '{{ __('branch_menu_category.NotActive') }}').addClass(
                                'btn-danger-light').removeClass('btn-success-light');
                        } else {
                            $('#branch_menu_category_status_' + dishCategoryId).text(
                                '{{ __('branch_menu_category.NotActive') }}').addClass(
                                'bg-danger').removeClass('bg-success');
                            $('#branch_menu_category_activation_' + dishCategoryId).text(
                                '{{ __('branch_menu_category.Active') }}').addClass(
                                'btn-success-light').removeClass('btn-danger-light');
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
