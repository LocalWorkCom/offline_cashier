@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('purchase.addPurchase')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">@lang('purchase.purchases')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('purchase.addPurchase')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid ">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('purchase.addPurchase')
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
                            <form method="POST" action="{{ route('purchase.store') }}" class="needs-validation"
                                enctype="multipart/form-data" novalidate>
                                @csrf

                                <!-- Invoice Details -->
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="date" class="form-label">@lang('purchase.date')</label>
                                        <input type="date" class="form-control" id="date" name="date"
                                            value="{{ old('date') }}" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('validation.EnterDate')</div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="invoice_number" class="form-label">@lang('purchase.invoiceNumber')</label>
                                        <input type="text" class="form-control" id="invoice_number" name="invoice_number"
                                            value="{{ old('invoice_number') }}" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('validation.EnterInvoiceNumber')</div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="vendor_id" class="form-label">@lang('purchase.vendor')</label>
                                        <select name="vendor_id" class="select2 form-control" required>
                                            <option value="" disabled selected>@lang('purchase.chooseVendor')</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">
                                                    {{ $vendor->name_ar . ' | ' . $vendor->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="store_id" class="form-label">@lang('purchase.store')</label>
                                        <select name="store_id" class="select2 form-control" required>
                                            <option value="" disabled selected>@lang('purchase.chooseStore')</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}">
                                                    {{ $store->name_ar . ' | ' . $store->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <p class="mb-2 text-muted">@lang('purchase.type')</p>
                                        <select name="type" class="select2 form-control" required>
                                            <option value="" disabled selected>@lang('purchase.chooseType')</option>
                                            <option value=0>@lang('purchase.purchase')</option>
                                            <option value=1>@lang('purchase.refund')</option>
                                        </select>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('validation.EnterType')</div>
                                    </div>
                                </div>

                                <!-- Product Details -->
                                <h4 class="mt-4">@lang('purchase.products')</h4>
                                <table class="table" id="productsTable">
                                    <thead>
                                        <tr>
                                            <th>@lang('purchase.category')</th>
                                            <th>@lang('purchase.product')</th>
                                            <th>@lang('purchase.unit')</th>
                                            <th>@lang('purchase.price')</th>
                                            <th>@lang('purchase.quantity')</th>
                                            <th>@lang('purchase.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productRows">
                                        <tr>
                                            <td>
                                                <select class="select2 form-control"
                                                    name="products[0][category_id]" required>
                                                    <option value="" disabled selected>@lang('purchase.chooseCategory')</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}">
                                                            {{ $category->name_ar . ' | ' . $category->name_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="select2 form-control"
                                                    name="products[0][product_id]" required>
                                                    <option value="" disabled selected>@lang('purchase.chooseProduct')</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}">
                                                            {{ $product->name_ar . ' | ' . $product->name_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="select2 form-control"
                                                    name="products[0][unit_id]" required>
                                                    <option value="" disabled selected>@lang('purchase.chooseUnit')</option>
                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit->id }}">
                                                            {{ $unit->name_ar . ' | ' . $unit->name_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.01" class="form-control"
                                                    name="products[0][price]" required></td>
                                            <td><input type="number" step="0.01" class="form-control"
                                                    name="products[0][quantity]" required></td>
                                            <td><button type="button"
                                                    class="btn btn-danger removeRow">@lang('purchase.remove')</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mt-4">
                                    <button type="button" class="btn btn-success"
                                        id="addRow">@lang('purchase.addProduct')</button>
                                </div>
                                <!-- Submit Button -->
                                <center>
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mt-4">
                                        <input type="submit" class="form-control btn btn-primary " id="input-submit"
                                            value="@lang('purchase.save')">
                                    </div>
                                </center>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- APP-CONTENT CLOSE -->
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- INTERNAL SELECT2 JS -->
    @vite('resources/assets/js/select2.js')

    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')

    <!-- Dynamic Row Management -->
    <script>
        document.getElementById('addRow').addEventListener('click', function() {
            const table = document.getElementById('productRows');
            const rowCount = table.children.length;
            const newRow = `
                <tr>
                    <td>
                        <select class="select2 form-control" name="products[${rowCount}][category_id]" required>
                            <option value="" disabled selected>@lang('purchase.chooseCategory')</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name_ar . ' | ' . $category->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="select2 form-control" name="products[${rowCount}][product_id]" required>
                            <option value="" disabled selected>@lang('purchase.chooseProduct')</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name_ar . ' | ' . $product->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="select2 form-control" name="products[${rowCount}][unit_id]" required>
                            <option value="" disabled selected>@lang('purchase.chooseUnit')</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->name_ar . ' | ' . $unit->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" step="0.01" class="form-control" name="products[${rowCount}][price]" required></td>
                    <td><input type="number" step="0.01" class="form-control" name="products[${rowCount}][quantity]" required></td>
                    <td><button type="button" class="btn btn-danger removeRow">Remove</button></td>
                </tr>
            `;
            table.insertAdjacentHTML('beforeend', newRow);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('removeRow')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
@endsection
