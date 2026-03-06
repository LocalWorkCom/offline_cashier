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
        <h4 class="fw-medium mb-0">@lang('brand.Brands')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    @if (auth('admin')->user()->hasPermissionTo('view brands', 'admin'))
                        <li class="breadcrumb-item active" aria-current="page">
                            <a href="javascript:void(0);"
                                onclick="window.location.href='{{ route('brands.list') }}'">@lang('brand.Brands')</a>
                        </li>
                    @endcan
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
                                @lang('brand.Brands')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create brands', 'admin'))
                                <a href="{{ route('brand.create') }}" type="button" class="btn btn-primary label-btn">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('brand.AddBrand')
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
                            <table class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('brand.ID')</th>
                                        <th scope="col">@lang('brand.Image')</th>
                                        <th scope="col">@lang('brand.ArabicName')</th>
                                        <th scope="col">@lang('brand.EnglishName')</th>
                                        <th scope="col">@lang('brand.ArabicDesc')</th>
                                        <th scope="col">@lang('brand.EnglishDesc')</th>
                                        <th scope="col">@lang('brand.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($brands as $brand)
                                        <tr>
                                            {{-- @dd( $brand) --}}
                                            <td>{{ $brand->id }}</td>
                                            <td>
                                                {!! $brand->logo_path 
                                                    ? '<img src="'.url($brand->logo_path).'" alt="Brand Logo" width="100" height="100">' 
                                                    : __('brand.noImage') !!}
                                            </td>
                                            <td>{{ $brand->name_ar }}</td>
                                            <td>{{ $brand->name_en }}</td>
                                            <td>{{ $brand->description_ar }}</td>
                                            <td>{{ $brand->description_en }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view brands', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="{{ route('brand.show', $brand->id) }}"
                                                        class="btn btn-info-light btn-wave show-brand">
                                                        @lang('brand.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update brands', 'admin'))
                                                    <!-- Edit Button -->
                                                    <a href="{{ route('brand.edit', $brand->id) }}"
                                                        class="btn btn-orange-light btn-wave">
                                                        @lang('brand.edit') <i class="ri-edit-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete brands', 'admin'))
                                                    <form class="d-inline" id="delete-form-{{ $brand->id }}"
                                                        action="{{ route('brand.delete', $brand->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item('{{ $brand->id }}')"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Delete Button -->

                                                {{-- <form class="d-inline" action="{{ route('brand.delete', $brand->id) }}"
                                                    method="POST" onsubmit="return confirmDelete()">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger-light btn-wave">
                                                        @lang('brand.delete') <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form> --}}

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
