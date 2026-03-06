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
                            <div class="card-title">@lang('product.Product Units')</div>
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
                            <form action="{{ route('product.units.save', $product->id) }}" method="POST">
                                @csrf
                                <div class="col-xl-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>@lang('product.Unit')</th>
                                                <th>@lang('product.Factor')</th>
                                                <th>@lang('product.Actions')</th>
                                            </tr>
                                        </thead>
                                        <tbody id="units-table" data-unit-count="{{ count($product->productUnits) }}">
                                            @foreach ($product->productUnits as $index => $productUnit)
                                                <tr>
                                                    <td>
                                                        <select name="units[{{ $index }}][unit_id]"
                                                            class="form-control" required>
                                                            <option value="">Select Unit</option>
                                                            @foreach ($units as $unit)
                                                                <option value="{{ $unit->id }}"
                                                                    @if ($productUnit->unit_id == $unit->id) selected @endif>
                                                                    {{ app()->getLocale() === 'en' ? $unit->name_en : $unit->name_ar }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="product_unit_id[]"
                                                            value="{{ $productUnit->id }}">
                                                        <input type="number" name="units[{{ $index }}][factor]"
                                                            class="form-control" value="{{ $productUnit->factor }}" min="1"
                                                            required>
                                                    </td>
                                                    <td>
                                                        {{-- @foreach ($product->units as $unit) --}}
                                                        <button type="button" class="btn btn-danger btn-sm remove-unit"
                                                            data-unit-id="{{ $unit->pivot->unit_id ?? 'No Unit ID' }}"
                                                            data-product-id="{{ $product->id }}">
                                                            @lang('product.Remove')
                                                        </button>
                                                        {{-- @endforeach --}}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" id="add-unit"
                                    class="btn btn-success btn-sm">@lang('product.Add Unit')</button>

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
    <script>
        function confirmDelete() {
            return confirm("@lang('validation.DeleteConfirm')");
        }
        $(document).ready(function() {
            $('.select2').select2();

            // Get the initial unit index from the data-unit-count attribute
            let unitIndex = $('#units-table').data(
                'unit-count'); // This will start from the count of existing units

            // Add Unit Row
            $('#add-unit').on('click', function() {
                $('#units-table').append(`
            <tr>
                <td>
                    <select name="units[${unitIndex}][unit_id]" class="form-control select2" required>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ app()->getLocale() === 'en' ? $unit->name_en : $unit->name_ar }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="units[${unitIndex}][factor]" class="form-control" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-unit">@lang('product.Remove')</button>
                </td>
            </tr>
        `);

                unitIndex++; // Increment unitIndex for the next unit row
                $('.select2').select2();
            });

            $(document).on('click', '.remove-unit', function() {
                // Get the current row and remove it
                let $row = $(this).closest('tr');
                $row.remove();

                // Reindex all the unit fields in the table
                $('#units-table tr').each(function(index) {
                    $(this).find('select, input').each(function() {
                        let name = $(this).attr('name');
                        // Update the name attributes to reflect the new index
                        if (name) {
                            let newName = name.replace(/\[\d+\]/, `[${index}]`);
                            $(this).attr('name', newName);
                        }
                    });
                });

                // Update the unitIndex to the correct value (based on remaining rows)
                unitIndex = $('#units-table tr').length;
            });


            // Remove Unit Row
            //     $(document).on('click', '.remove-unit', function() {
            //         const row = $(this).closest('tr');
            //         const unitId = $(this).data('unit-id'); // Correct attribute
            //         const productId = $(this).data('product-id'); // Correct attribute

            //         console.log('Unit ID:', unitId);
            //         console.log('Product ID:', productId);

            //         if (unitId) {
            //             if (confirm('@lang('Are you sure you want to remove this unit?')')) {
            //                 $.ajax({
            //                     url: `/units/remove/${unitId}`,
            //                     type: 'DELETE',
            //                     data: {
            //                         _token: '{{ csrf_token() }}',
            //                         product_id: productId, // Pass the product ID if needed
            //                     },
            //                     success: function(response) {
            //                         if (response.status) {
            //                             alert(response.message);
            //                             row.remove();
            //                         } else {
            //                             alert('@lang('Failed to remove unit.')');
            //                         }
            //                     },
            //                     error: function() {
            //                         alert('@lang('An error occurred.')');
            //                     },
            //                 });
            //             }
            //         } else {
            //             row.remove(); // For new rows
            //         }
            //     });
        });
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <!-- Load jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>

    <!-- Load Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

@endsection
