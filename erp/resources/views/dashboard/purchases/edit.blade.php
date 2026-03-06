@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('purchase.edit')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">@lang('purchase.purchases')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('purchase.edit')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid ">
            <!-- Start:: row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('purchase.edit')
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
                            <form method="POST" action="{{ route('purchase.update', $purchase->id) }}"
                                class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <!-- Basic Details -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="date" class="form-label">@lang('purchase.date')</label>
                                        <input type="date" class="form-control" id="date" name="date"
                                            value="{{ $purchase->Date }}">
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="invoice_number" class="form-label">@lang('purchase.invoiceNumber')</label>
                                        <input type="text" class="form-control" id="invoice_number" name="invoice_number"
                                            value="{{ $purchase->invoice_number }}">
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="vendor_id" class="form-label">@lang('purchase.vendor')</label>
                                        <select name="vendor_id" class="form-control select2" required>
                                            <option value="" disabled>@lang('purchase.chooseVendor')</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}"
                                                    {{ $purchase->vendor_id == $vendor->id ? 'selected' : '' }}>
                                                    {{ $vendor->name_ar . ' | ' . $vendor->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="type" class="form-label">@lang('purchase.type')</label>
                                        <select name="type" class="form-control select2" required>
                                            <option value="0" {{ $purchase->type == 0 ? 'selected' : '' }}>
                                                @lang('purchase.purchase')
                                            </option>
                                            <option value="1" {{ $purchase->type == 1 ? 'selected' : '' }}>
                                                @lang('purchase.refund')
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="store_id" class="form-label">@lang('purchase.store')</label>
                                        <select name="store_id" class="form-control select2" required>
                                            <option value="" disabled>@lang('purchase.chooseStore')</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}"
                                                    {{ $purchase->store_id == $store->id ? 'selected' : '' }}>
                                                    {{ $store->name_ar . ' | ' . $store->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <hr>
                                <h5>@lang('purchase.products')</h5>

                                <div id="product-container">
                                    @foreach ($purchase->purchaseInvoicesDetails as $index => $detail)
                                        <div class="row gy-4 product-row">
                                            <input type="hidden" name="products[{{ $index }}][id]"
                                                value="{{ $detail->id }}">
                                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                                                <label class="form-label">@lang('purchase.category')</label>
                                                <select name="products[{{ $index }}][category_id]"
                                                    class="form-control select2" required>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ $detail->category_id == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name_ar . ' | ' . $category->name_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                                                <label class="form-label">@lang('purchase.product')</label>
                                                <select name="products[{{ $index }}][product_id]"
                                                    class="form-control select2" required>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            {{ $detail->product_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name_ar . ' | ' . $product->name_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12">
                                                <label class="form-label">@lang('purchase.unit')</label>
                                                <select name="products[{{ $index }}][unit_id]"
                                                    class="form-control select2" required>
                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit->id }}"
                                                            {{ $detail->unit_id == $unit->id ? 'selected' : '' }}>
                                                            {{ $unit->name_ar . ' | ' . $unit->name_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12">
                                                <label class="form-label">@lang('purchase.price')</label>
                                                <input type="number" class="form-control"
                                                    name="products[{{ $index }}][price]"
                                                    value="{{ $detail->price }}" required>
                                            </div>
                                            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12">
                                                <label class="form-label">@lang('purchase.quantity')</label>
                                                <input type="number" class="form-control"
                                                    name="products[{{ $index }}][quantity]"
                                                    value="{{ $detail->quantity }}" required>
                                            </div>
                                            <input type="hidden" name="products[{{ $index }}][id]"
                                                value="{{ $detail->id }}">
                                        </div>
                                    @endforeach
                                </div>

                                <hr>
                                <center>
                                    <button type="submit" class="btn btn-success">
                                        @lang('purchase.save')
                                    </button>
                                </center>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row -->
        </div>
    </div>
@endsection

