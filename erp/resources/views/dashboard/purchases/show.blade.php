@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('purchase.show')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">@lang('purchase.purchases')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('purchase.show')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('purchase.show')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <!-- General Purchase Information -->
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('purchase.date')</label>
                                    <p class="form-text">{{ $purchase->Date }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('purchase.invoiceNumber')</label>
                                    <p class="form-text">{{ $purchase->invoice_number }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('purchase.vendor')</label>
                                    <p class="form-text">
                                        {{ $purchase->vendor->name_ar . ' | ' . $purchase->vendor->name_en }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('purchase.type')</label>
                                    <p class="form-text">
                                        {{ $purchase->type === 0
                                            ? (app()->getLocale() === 'ar'
                                                ? 'شراء'
                                                : 'Purchase')
                                            : (app()->getLocale() === 'ar'
                                                ? 'استرجاع'
                                                : 'Refund') }}
                                    </p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('purchase.store')</label>
                                    <p class="form-text">{{ $purchase->store->name_ar . ' | ' . $purchase->store->name_en }}
                                    </p>
                                </div>
                            </div>

                            <hr>
                            <!-- Product Details -->
                            <h5 class="mb-3">@lang('purchase.products')</h5>
                            @foreach ($purchase->purchaseInvoicesDetails as $detail)
                                <div class="row gy-4">
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                        <label class="form-label">@lang('purchase.category')</label>
                                        <p class="form-text">
                                            {{ $detail->category->name_ar . ' | ' . $detail->category->name_en }}
                                        </p>
                                    </div>
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                        <label class="form-label">@lang('purchase.product')</label>
                                        <p class="form-text">
                                            {{ $detail->product->name_ar . ' | ' . $detail->product->name_en }}
                                        </p>
                                    </div>
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                        <label class="form-label">@lang('purchase.unit')</label>
                                        <p class="form-text">
                                            {{ $detail->unit->name_ar . ' | ' . $detail->unit->name_en }}
                                        </p>
                                    </div>
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                        <label class="form-label">@lang('purchase.price')</label>
                                        <p class="form-text">{{ $detail->price }}</p>
                                    </div>
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                        <label class="form-label">@lang('purchase.quantity')</label>
                                        <p class="form-text">{{ $detail->quantity }}</p>
                                    </div>
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                        <label class="form-label">@lang('purchase.totalPrice')</label>
                                        <p class="form-text">{{ $detail->total_price }}</p>
                                    </div>
                                </div>
                                <hr>
                            @endforeach

                            <!-- Total Invoice Price -->
                            <div class="row gy-4">
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('purchase.totalInvoicePrice')</label>
                                    <p class="form-text">
                                        {{ $purchase->purchaseInvoicesDetails->sum('total_price') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->
        </div>
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
