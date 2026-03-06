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
        <h4 class="fw-medium mb-0">@lang('branch.Branches')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch.Branches')</li>
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
                                @lang('branch.Branches')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create branches', 'admin'))
                                <a href="{{ route('branch.create') }}" type="button" class="btn btn-primary label-btn">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('branch.AddBranch')
                                </a>
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
                                        <th scope="col">@lang('category.ID')</th>
                                        <th scope="col">@lang('branch.ArabicName')</th>
                                        <th scope="col">@lang('branch.EnglishName')</th>
                                        <th scope="col">@lang('branch.ArabicAddress')</th>
                                        <th scope="col">@lang('branch.EnglishAddress')</th>
                                        <th scope="col">@lang('branch.Country')</th>
                                        <th scope="col">@lang('bank_names.EmployeesCount')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($branches as $branch)
                                        <tr>
                                            {{-- @dd( $branch) --}}
                                            <td>{{ $branch->id }}</td>
                                            <td>
                                                <a href="{{ route('employees.list', ['branch_id' => $branch->id]) }}" class="text-primary">
                                                    {{ $branch->name_ar }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('employees.list', ['branch_id' => $branch->id]) }}" class="text-primary">
                                                    {{ $branch->name_en }}
                                                </a>
                                            </td>
                                            <td>{{ $branch->address_ar }}</td>
                                            <td>{{ $branch->address_en }}</td>
                                            <td>{{ $branch->country ? $branch->country->name_ar . ' | ' . $branch->country->name_en : __('category.none') }}
                                            </td>
                                            <td>
                                                {{-- @foreach ($branches as $branch) --}}
                                                    <a href="{{ route('employees.list', ['branch_id' => $branch->id]) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        {{ $branch->employees_count }}
                                                    </a>
                                                {{-- @endforeach --}}

                                            </td>

                                            <td id="branch_status_{{ $branch->id }}">
                                                @if ($branch->is_active == 1)
                                                    <span
                                                        class="badge bg-success">{{ __('branch_menu_size.Active') }}</span>
                                                @else
                                                    <span
                                                        class="badge bg-secondary">{{ __('branch_menu_size.NotActive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="{{ route('branch.show', $branch->id) }}"
                                                        class="btn btn-info-light btn-wave show-category">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update branches', 'admin'))
                                                    <!-- Edit Button -->
                                                    <a href="{{ route('branch.edit', $branch->id) }}"
                                                        class="btn btn-orange-light btn-wave">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('view dishes', 'admin'))
                                                    <!-- sync Button -->
                                                    <a href="{{ route('branch.sync', $branch->id) }}"
                                                        class="btn btn-orange-light btn-wave">
                                                        @lang('category.sync') <i class="ri-edit-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update branches', 'admin'))
                                                    <button type="button" id="branch_activation_{{ $branch->id }}"
                                                        onclick="change_status_item({{ $branch->id }})"
                                                        class="btn btn-{{ $branch->is_active == 1 ? 'danger' : 'success' }}-light btn-wave">
                                                        {{ $branch->is_active == 0 ? __('branch_menu_size.Active') : __('branch_menu_size.NotActive') }}
                                                    </button>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete branches', 'admin'))
                                                    <!-- Delete Button -->
                                                    <form class="d-inline" id="delete-form-{{ $branch->id }}"
                                                        action="{{ route('branch.delete', $branch->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item({{ $branch->id }})"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (auth('admin')->user()->hasRole('Branch Manager'))
                                               <a href="{{ route('floor.branch', $branch->id) }}" class="btn btn-orange-light btn-wave">
                                                                                                {{ __('floor.Floors') }} <i class="ri-show-line"></i>
                                                                                                </a>
                                                                                                @endif

                                                                                                {{-- <a href="{{ route('branch.categories.show.all', $branch->id) }}" class="btn btn-orange-light btn-wave">
                                                                                                {{ __('floor.DishesCategory') }} <i class="ri-show-line"></i>
                                                                                                </a> --}}
                                                @if (auth('admin')->user() &&
                                                        (auth('admin')->user()->hasPermissionTo('view branch_menu_categories', 'admin') ||
                                                            auth('admin')->user()->hasPermissionTo('view branch_menus', 'admin') ||
                                                            auth('admin')->user()->hasPermissionTo('view branch_menu_addons', 'admin') ||
                                                            auth('admin')->user()->hasPermissionTo('view branch_menu_category_addons', 'admin') ||
                                                            auth('admin')->user()->hasPermissionTo('view branch_menu_sizes', 'admin')))
                                                    <select class="form-select d-inline" style="width: auto;"
                                                        onchange="showBranchRalates(this.value, {{ $branch->id }})">
                                                        <option>@lang('branch.ChooseBranchDetails')</option>
                                                        @if (auth('admin')->user()->hasPermissionTo('view branch_menu_categories', 'admin'))
                                                            <option value="categories">@lang('branch.branchMenuCategory')</option>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('view branch_menus', 'admin'))
                                                            ()
                                                            <option value="menus">@lang('branch.branchMenu')</option>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('view branch_menu_category_addons', 'admin'))
                                                            <option value="addonsCategories">@lang('branch.branchMenuCategoryAddon')
                                                            </option>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('view branch_menu_addons', 'admin'))
                                                            <option value="addons">@lang('branch.branchMenuAddon')</option>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('view branch_menu_sizes', 'admin'))
                                                            <option value="sizes">@lang('branch.branchMenuSize')
                                                            </option>
                                                        @endif
                                                    </select>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
@endsection

<script>
    function showBranchRalates(relates, branchId) {
        if (relates) {
            const routes = {
                categories: "{{ url('dashboard/branch/categories/showAll') }}/" + branchId,
                menus: "{{ url('dashboard/branch/menus/showAll') }}/" + branchId,
                addonsCategories: "{{ url('dashboard/branch/menu/addon/categories/showAll') }}/" + branchId,
                addons: "{{ url('dashboard/branch/menu/addons/showAll') }}/" + branchId,
                sizes: "{{ url('dashboard/branch/menu/sizes/showAll') }}/" + branchId
            };
            console.log("Redirecting to: ", routes[relates]); // Debugging output
            window.location.href = routes[relates];
        }
    }

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
                var edit_status_url = "{{ route('branch.changeStatus', 'id') }}";
                edit_status_url = edit_status_url.replace('id', dishMenuId);
                $.ajax({
                    url: edit_status_url,
                    type: 'GET',
                    success: function(data) {
                        console.log(data);
                        if (data.is_active == -1) {
                            Swal.fire({
                                title: @json(__('validation.Alert')),
                                text: @json(__('validation.IsDefaultMessage')),
                                icon: 'error'
                            });
                            return;
                        } else if (data.is_active == 1) {
                            $('#branch_status_' + dishMenuId).text(
                                '{{ __('branch_menu_size.Active') }}');
                            $('#branch_activation_' + dishMenuId).text(
                                '{{ __('branch_menu_size.NotActive') }}');
                        } else {
                            $('#branch_status_' + dishMenuId).text(
                                '{{ __('branch_menu_size.NotActive') }}');
                            $('#branch_activation_' + dishMenuId).text(
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
