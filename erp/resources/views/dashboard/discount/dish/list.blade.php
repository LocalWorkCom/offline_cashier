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
        <h4 class="fw-medium mb-0">@lang('discount.Discounts')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);"
                            onclick="window.location.href='{{ route('discounts.list') }}'">@lang('discount.Discounts')</a>
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
                            <div class="card-title">@lang('discount.Discount Dish')</div>
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
                            <form action="{{ route('discount.dishes.save', $discount->id) }}" method="POST">
                                @csrf
                                <div class="col-xl-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>@lang('discount.dish')</th>
                                                <th>@lang('discount.Actions')</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dish-table" data-size-count="{{ count($discount->dishDiscounts) }}">
                                            @foreach ($discount->discount_dishes as $index => $dishDiscount)
                                                <tr>
                                                    <td>
                                                        <select name="discount_dishes[{{ $index }}][dish_id]"
                                                            class="form-control select2" required>
                                                            <option value="">Select Dishes</option>
                                                            @foreach ($dishes as $dish)
                                                                <option value="{{ $dish->id }}"
                                                                    @if ($dishDiscount->id == $dish->id) selected @endif>
                                                                    {{ $dish->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-dish"
                                                            data-dish-id="{{ $dishDiscount->id }}"
                                                            data-discount-id="{{ $discount->id }}">
                                                            @lang('discount.Remove')
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>
                                <button type="button" id="add-dish"
                                    class="btn btn-success btn-sm">@lang('discount.Discount Dish')</button>

                                <button type="submit" class="btn btn-primary mt-3">@lang('discount.Save')</button>
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
            // Initialize Select2
            $('.select2').select2();

            // Get the initial dish index from the data-size-count attribute
            let dishIndex = parseInt($('#dish-table').data('size-count'), 10);

            // Add Dish Row
            $('#add-dish').on('click', function() {
                $('#dish-table').append(`
            <tr>
                <td>
                    <select name="discount_dishes[${dishIndex}][dish_id]" class="form-control select2" required>
                        @foreach ($dishes as $dish)
                            <option value="{{ $dish->id }}">{{ $dish->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-dish">@lang('discount.Remove')</button>
                </td>
            </tr>
        `);

                dishIndex++; // Increment dishIndex for the next row
                $('.select2').select2(); // Reinitialize Select2 for new elements
            });

            // Remove Dish Row
            $(document).on('click', '.remove-dish', function() {
                // Get the current row and remove it
                $(this).closest('tr').remove();

                // Reindex all the fields in the table
                $('#dish-table tr').each(function(index) {
                    $(this).find('select, input').each(function() {
                        let name = $(this).attr('name');
                        if (name) {
                            // Update the name attributes to reflect the new index
                            let newName = name.replace(/\[\d+\]/, `[${index}]`);
                            $(this).attr('name', newName);
                        }
                    });
                });

                // Update dishIndex to the correct value (based on remaining rows)
                dishIndex = $('#dish-table tr').length;
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
