@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('product.show')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('products.list') }}'">
                            @lang('product.Products')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('product.show', ['id' => $id]) }}">@lang('product.show')</a>
                    </li>
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
                            <div class="card-title">@lang('product.show')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('product.ArabicName')</label>
                                    <p class="form-text">{{ $product->name_ar }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('product.EnglishName')</label>
                                    <p class="form-text">{{ $product->name_en }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('product.ArabicDesc')</label>
                                    <p class="form-text">
                                        {{ $product->description_ar == null ? __('category.none') : $product->description_ar }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('product.EnglishDesc')</label>
                                    <p class="form-text">
                                        {{ $product->description_en == null ? __('category.none') : $product->description_en }}
                                    </p>
                                </div>


                                {{-- <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.Price')</label>
                                    <p class="form-text">{{ $product->price }}</p>
                                </div> --}}
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.Currency')</label>
                                    <p class="form-text">
                                        {{ $Currencies[$product->currency_code] ?? __('category.none') }}
                                    </p>
                                </div>


                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.Barcode')</label>
                                    <p class="form-text">{{ $product->barcode }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.Unit')</label>
                                    <p class="form-text">
                                        {{ optional($product->mainUnit)->name_ar . ' | ' . optional($product->mainUnit)->name_en ?? __('category.none') }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.Brand')</label>
                                    <p class="form-text">
                                        {{ $product->brand->name_ar . ' | ' . $product->brand->name_en ?? __('category.none') }}
                                    </p>
                                </div>
                                {{-- <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.Store')</label>
                                    @if ($product->productLimit->isNotEmpty())
                                        <ul>
                                            @foreach ($product->productLimit as $limit)
                                                <li>{{ $limit->store->name_ar." | ".$limit->store->name_en }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="form-text">@lang('category.none')</p>
                                    @endif
                                </div> --}}

                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.Category')</label>
                                    <p class="form-text">
                                        {{ $product->category->name_ar . ' | ' . $product->category->name_en ?? __('category.none') }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.Type')</label>
                                    <p class="form-text">
                                        {{ $product->type == 'raw' ? __('product.Raw') : __('product.Complete') }}</p>
                                </div>
                                {{-- <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.ExpiryDate')</label>
                                    <p class="form-text">{{ $product->expiry_date ?? __('category.none') }}</p>
                                </div> --}}
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.IsHaveExpired')</label>
                                    <p class="form-text">
                                        {{ $product->is_have_expired ? __('category.yes') : __('category.no') }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('product.Remind')</label>
                                    <p class="form-text">{{ $product->is_remind ? __('category.yes') : __('category.no') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <label class="form-label">@lang('product.Image')</label>
                                    @if ($product->main_image)
                                        <div class="mb-3">
                                            <img src="{{ asset($product->main_image) }}" alt="Category Image"
                                                width="150" height="150">
                                        </div>
                                        <!-- Hidden input to send the current image -->
                                        <input type="hidden" name="main_image" value="{{ $product->main_image }}">
                                    @else
                                        <p class="form-text">@lang('category.none')</p>
                                    @endif
                                </div>


                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <label class="form-label">@lang('product.Images')</label>
                                    @if ($product->images->count())
                                        <div class="gallery">
                                            @foreach ($product->images as $image)
                                                <div class="mb-3">
                                                    <img src="{{ asset($image->image) }}" alt="Product Image"
                                                        width="150" height="150">
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="form-text">@lang('category.none')</p>
                                    @endif
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
