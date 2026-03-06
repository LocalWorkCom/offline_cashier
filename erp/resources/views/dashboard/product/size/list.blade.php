@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('product.Products')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a>
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
            <!-- Product Units Form -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('product.Product Sizes')</div>
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
                            <form action="{{ route('product.sizes.save', $product->id) }}" method="POST">
                                @csrf
                                <div class="col-xl-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>@lang('product.Size')</th>
                                                <th>@lang('product.Code_size')</th>
                                                <th>@lang('product.Actions')</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sizes-table" data-size-count="{{ count($product->productSizes) }}">
                                            @foreach ($product->productSizes as $index => $productSize)
                                                <tr>
                                                    <td>
                                                        <select name="product_sizes[{{ $index }}][size_id]"
                                                            class="form-control" required>
                                                            <option value="">Select Size</option>
                                                            @foreach ($sizes as $size)
                                                                <option value="{{ $size->id }}"
                                                                    @if ($productSize->size_id == $size->id) selected @endif>
                                                                    {{ app()->getLocale() === 'en' ? $size->name_en : $size->name_ar }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="product_size_id[]"
                                                            value="{{ $productSize->id }}">
                                                        <input type="text"
                                                            name="product_sizes[{{ $index }}][code_size]"
                                                            class="form-control" value="{{ $productSize->code_size }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-size"
                                                            data-size-id="{{ $size->pivot->size_id ?? 'No Size ID' }}"
                                                            data-product-id="{{ $product->id }}">
                                                            @lang('product.Remove')
                                                        </button>


                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" id="add-size"
                                    class="btn btn-success btn-sm">@lang('product.Add Size')</button>

                                <button type="submit" class="btn btn-primary mt-3">@lang('product.Save')</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function confirmDelete() {
            return confirm("@lang('validation.DeleteConfirm')");
        }
        $(document).ready(function() {
            $('.select2').select2();

            // Get the initial size index from the data-size-count attribute
            let sizeIndex = $('#sizes-table').data(
                'size-count'); // This will start from the count of existing sizes

            // Add Size Row
            $('#add-size').on('click', function() {
                $('#sizes-table').append(`
            <tr>
                <td>
                    <select name="product_sizes[${sizeIndex}][size_id]" class="form-control select2" required>
                        @foreach ($sizes as $size)
                            <option value="{{ $size->id }}">{{ app()->getLocale() === 'en' ? $size->name_en : $size->name_ar }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" name="product_sizes[${sizeIndex}][code_size]" class="form-control" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-size">@lang('product.Remove')</button>
                </td>
            </tr>
        `);

                sizeIndex++; // Increment sizeIndex for the next unit row
                $('.select2').select2();
            });

            $(document).on('click', '.remove-size', function() {
                // Get the current row and remove it
                let $row = $(this).closest('tr');
                $row.remove();

                // Reindex all the size fields in the table
                $('#sizes-table tr').each(function(index) {
                    $(this).find('select, input').each(function() {
                        let name = $(this).attr('name');
                        // Update the name attributes to reflect the new index
                        if (name) {
                            let newName = name.replace(/\[\d+\]/, `[${index}]`);
                            $(this).attr('name', newName);
                        }
                    });
                });

                // Update the sizeIndex to the correct value (based on remaining rows)
                unitIndex = $('#sizes-table tr').length;
            });
        });
    </script>

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
@endsection
