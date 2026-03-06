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
        <h4 class="fw-medium mb-0">@lang('product.Products')</h4>
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
                            onclick="window.location.href='{{ route('products.list') }}'">@lang('product.Products')</a>
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
                            <div class="card-title">@lang('product.Products')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create products', 'admin'))

                                <button type="button" class="btn btn-primary label-btn"
                                    onclick="window.location.href='{{ route('product.create') }}'">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('product.AddProduct')
                                </button>
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
                                        <th scope="col">@lang('product.ID')</th>
                                        <th scope="col">@lang('product.Image')</th>
                                        <th>@lang('dishes.ItemCode')</th>

                                        <th scope="col">@lang('product.ArabicName')</th>
                                        <th scope="col">@lang('product.EnglishName')</th>
                                        <th scope="col">@lang('product.EnglishUnit')</th>
                                        <th scope="col">@lang('product.ArabicUnit')</th>
                                        <th scope="col">@lang('product.Type')</th>
                                        <th scope="col">@lang('product.ArabicCategory')</th>
                                        <th scope="col">@lang('product.EnglishCategory')</th>
                                        <th scope="col">@lang('product.Barcode')</th>
                                        <th scope="col">@lang('product.Sku')</th>
                                        <th scope="col">@lang('product.IsHaveExpired')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>{{ $product->id }}</td>
                                            <td>
                                                <img src="{{ $product->main_image ? url($product->main_image) : asset('images/noimage.jpg') }}"
                                                    alt="Product Image" width="100" height="100">
                                            </td>
                                            <td>{{ $product->getItemCodeNameAttribute() }}</td>

                                            <td>{{ $product->name_ar }}</td>
                                            <td>{{ $product->name_en }}</td>
                                            <td>{{ $product->mainUnit ? $product->mainUnit->name_en : 'N/A' }}</td>
                                            <td>{{ $product->mainUnit ? $product->mainUnit->name_ar : 'N/A' }}</td>
                                            <td>@lang('product.' . ucfirst($product->type))</td>
                                            <td>{{ $product->Category ? $product->Category->name_en : 'N/A' }}</td>
                                            <td>{{ $product->Category ? $product->Category->name_ar : 'N/A' }}</td>
                                            <td>{{ $product->barcode }}</td>
                                            <td>{{ $product->sku }}</td>
                                            @php
                                                $translationKey = $product->is_have_expired
                                                    ? 'category.yes'
                                                    : 'category.no';
                                            @endphp

                                            <td> @lang($translationKey)</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view products', 'admin'))

                                                    <!-- Show Button -->
                                                    <a href="{{ route('product.show', $product->id) }}"
                                                        class="btn btn-info-light btn-wave show-category">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                    @endif

                                                    @if (auth('admin')->user()->hasPermissionTo('update products', 'admin'))

                                                        <!-- Edit Button -->
                                                        <a href="{{ route('product.edit', $product->id) }}"
                                                            class="btn btn-orange-light btn-wave">
                                                            @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </a> @endif

                                                    @if (auth('admin')->user()->hasPermissionTo('delete products', 'admin'))

                                                        <form class="d-inline" id="delete-form-{{ $product->id }}"
                                                            action="{{ route('product.delete', $product->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" onclick="delete_item({{ $product->id }})"
                                                                class="btn btn-danger-light btn-wave">
                                                                @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <!-- Delete Button -->
                                                    {{--                                                <form class="d-inline" action="{{ route('product.delete', $product->id) }}" --}}
                                                    {{--                                                    method="POST" onsubmit="return confirmDelete()"> --}}
                                                    {{--                                                    @csrf --}}
                                                    {{--                                                    @method('DELETE') --}}
                                                    {{--                                                    <button type="submit" class="btn btn-danger-light btn-wave"> --}}
                                                    {{--                                                        @lang('category.delete') <i class="ri-delete-bin-line"></i> --}}
                                                    {{--                                                    </button> --}}
                                                    {{--                                                </form> --}}
                                                    {{-- <button type="button" class="btn btn-success btn-wave"
                                                    onclick="navigateOption('units', {{ $product->id }})">
                                                    @lang('YourNewButtonLabel') <i class="fe fe-custom-icon"></i>
                                                </button> --}}

                                                    <!-- Dropdown Menu -->
                                                    <select class="form-select d-inline btn-outline-teal" style="width: auto;"
                                                        onchange="navigateOption(this.value, {{ $product->id }})">
                                                        <option value="">{{ __('product.options') }}</option>

                                                        @if (auth('admin')->user()->hasPermissionTo('view product_sizes', 'admin'))
                                                            <option class="form-select" value="sizes">@lang('product.Sizes')</option>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('view product_colors', 'admin'))
                                                            <option class="form-select" value="colors">@lang('product.Colors')</option>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('view product_units', 'admin'))
                                                            <option class="form-select" value="units">@lang('product.Units')</option>
                                                        @endif

                                                    </select>

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
    function navigateOption(value, productId) {
        if (value) {
            const routes = {
                sizes: "{{ url('dashboard/products/size/list') }}/" + productId,
                colors: "{{ url('dashboard/products/color/list') }}/" + productId,
                units: "{{ url('dashboard/products/unit/list') }}/" + productId
            };
            console.log("Redirecting to: ", routes[value]); // Debugging output
            window.location.href = routes[value];
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
</script>
