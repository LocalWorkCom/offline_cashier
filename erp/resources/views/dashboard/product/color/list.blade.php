@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
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
                            <div class="card-title">@lang('product.Product Colors')</div>
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

                            <form action="{{ route('product.colors.save', $product->id) }}" method="POST">
                                @csrf
                                <div class="col-xl-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>@lang('product.Color')</th>
                                                <th>@lang('product.Actions')</th>
                                            </tr>
                                        </thead>
                                        <tbody id="colors-table" data-color-count="{{ count($product->productColors) }}">
                                            @foreach ($product->productColors as $index => $productColor)
                                                <tr>
                                                    <td>
                                                        <select name="colors[{{ $index }}][color_id]"
                                                            class="form-control" required>
                                                            <option value="">Select Color</option>
                                                            @foreach ($colors as $color)
                                                                <option value="{{ $color->id }}"
                                                                    @if ($productColor->color_id == $color->id) selected @endif>
                                                                    {{ app()->getLocale() === 'en' ? $color->name_en : $color->name_ar }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    {{-- <td>
                                                        <input type="hidden" name="product_unit_id[]"
                                                            value="{{ $productColor->id }}">
                                                        <input type="number" name="units[{{ $index }}][factor]"
                                                            class="form-control" value="{{ $productColor->factor }}"
                                                            required>
                                                    </td> --}}
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-color"
                                                            data-color-id="{{ $color->pivot->color_id ?? 'No Color ID' }}"
                                                            data-product-id="{{ $product->id }}">
                                                            @lang('product.Remove')
                                                        </button>

                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" id="add-color"
                                    class="btn btn-success btn-sm">@lang('product.Add Color')</button>

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
        // Function to generate a new color row
        function addNewColorRow(index) {
            return `
                <tr>
                    <td>
                        <select name="colors[${index}][color_id]" class="form-control select2" required>
                            @foreach ($colors as $color)
                                <option value="{{ $color->id }}">{{ app()->getLocale() === 'en' ? $color->name_en : $color->name_ar }}</option>

                            @endforeach
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-color">@lang('product.Remove')</button>
                    </td>
                </tr>
            `;
        }

        function confirmDelete() {
            return confirm("@lang('validation.DeleteConfirm')");
        }

        $(document).ready(function() {
            $('.select2').select2();

            // Initialize color index based on existing rows
            let colorIndex = $('#colors-table').data('color-count');

            // Add a new color row
            $('#add-color').on('click', function() {
                const newRow = addNewColorRow(colorIndex);
                $('#colors-table').append(newRow);
                $('.select2').select2(); // Reinitialize Select2
                colorIndex++; // Increment the index
            });

            // Remove a color row
            $(document).on('click', '.remove-color', function() {
                $(this).closest('tr').remove();
                // Reindex rows after removal
                $('#colors-table tr').each(function(index) {
                    $(this).find('select, input').each(function() {
                        const name = $(this).attr('name');
                        if (name) {
                            const newName = name.replace(/\[\d+\]/, `[${index}]`);
                            $(this).attr('name', newName);
                        }
                    });
                });
                // Update the index
                colorIndex = $('#colors-table tr').length;
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
