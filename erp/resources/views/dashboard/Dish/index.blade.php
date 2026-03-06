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
        <h4 class="fw-medium mb-0">@lang('dishes.Dishes')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('dishes.Dishes')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">
                                @lang('dishes.AllDishes')
                            </div>
                            @if (auth('admin')->user()->hasPermissionTo('create dishes', 'admin'))

                            <a href="{{ route('dashboard.dishes.create') }}" class="btn btn-primary label-btn">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('dishes.AddDish')
                            </a>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('create products', 'admin'))

                            <a href="{{ route('dashboard.dish_products.create') }}" class="btn btn-secondary label-btn">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('dishes.AddProduct')
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
                                        <th>@lang('dishes.ID')</th>
                                        <th>@lang('dishes.Image')</th>
                                        <th>@lang('dishes.ItemCode')</th>

                                        <th>@lang('dishes.NameArabic')</th>
                                        <th>@lang('dishes.NameEnglish')</th>
                                        <th>@lang('dishes.Category')</th>
                                        <th>@lang('dishes.Cuisine')</th>
                                        <th>@lang('dishes.IsActive')</th>
                                        <th>@lang('dishes.HasSizes')</th>
                                        <th>@lang('dishes.HasAddon')</th>
                                        <th>@lang('dishes.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dishes as $dish)
                                        <tr>
                                            <td>{{ $dish->id }}</td>
                                            <td>
                                                @if ($dish->image)
                                                    <img src="{{ asset($dish->image) }}" alt="{{ $dish->name_en }}"
                                                        width="50" height="50" class="rounded">
                                                @else
                                                    <span>@lang('dishes.NoImage')</span>
                                                @endif
                                            </td>
                                            <td>{{ $dish->getItemCodeNameAttribute() }}</td>

                                            <td>{{ $dish->name_ar }}</td>
                                            <td>{{ $dish->name_en }}</td>
                                            <td>{{ $dish->dishCategory->name_site ?? __('dishes.NoCategory') }}</td>
                                            <td>{{ $dish->cuisine->name_site ?? __('dishes.NoCuisine') }}</td>
                                            <td>
                                                @if ($dish->is_active)
                                                    <span class="badge bg-success">@lang('dishes.Active')</span>
                                                @else
                                                    <span class="badge bg-danger">@lang('dishes.Inactive')</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($dish->has_sizes)
                                                    <span class="badge bg-info">@lang('dishes.HasSizes')</span>
                                                @else
                                                    <span class="badge bg-secondary">@lang('dishes.NoSizes')</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($dish->has_addon)
                                                    <span class="badge bg-info">@lang('dishes.HasAddon')</span>
                                                @else
                                                    <span class="badge bg-secondary">@lang('dishes.Noaddon')</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view dishes', 'admin'))

                                                <!-- Show -->
                                                <a href="{{ route('dashboard.dishes.show', $dish->id) }}"
                                                    class="btn btn-info-light">
                                                    @lang('dishes.View') <i class="ri-eye-line"></i>
                                                </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update dishes', 'admin'))

                                                <!-- Edit -->
                                                <a href="{{ route('dashboard.dishes.edit', $dish->id) }}"
                                                    class="btn btn-orange-light">
                                                    @lang('dishes.Edit') <i class="ri-edit-line"></i>
                                                </a>
                                                @endif
                                                <a href="{{ route('dashboard.dishes.ingredient', $dish->id) }}"
                                                    class="btn btn-orange-light">
                                                    @lang('dishes.ingredient') <i class="ri-edit-line"></i>
                                                </a>
                                                @if (auth('admin')->user()->hasPermissionTo('delete dishes', 'admin'))

                                                <form class="d-inline" id="delete-form-{{ $dish->id }}" action="{{ route('dashboard.dishes.destroy', $dish->id) }}" method="Post">
                                                    @csrf
                                                    <button type="button" onclick="delete_item({{ $dish->id }})" class="btn btn-danger-light btn-wave">
                                                        @lang('dishes.Delete') <i class="ri-delete-bin-line"></i>
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
    </div>
@endsection

<script>
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

</script>
