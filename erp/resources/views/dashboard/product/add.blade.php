@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('product.AddProduct')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('products.list') }}'">
                            @lang('product.Products')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('product.create') }}">@lang('product.AddProduct')</a>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('product.AddProduct')</div>
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
                            <form method="POST" action="{{ route('product.store') }}" class="needs-validation" novalidate enctype="multipart/form-data">
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-12">
                                        <p class="mb-2 text-muted">@lang('product.Brand')</p>
                                        <select name="brand_id" class=" form-control" required>
                                            <option value="" selected disabled>@lang('product.ChooseBrand')</option>
                                            @foreach ($Brands as $Brand)
                                                <option value="{{ $Brand->id }}">{{ $Brand->name_ar . " | ".$Brand->name_en }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterBrand')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="name_ar" class="form-label">@lang('product.ArabicName')</label>
                                        <input type="text" name="name_ar" id="name_ar" class="form-control" placeholder="@lang('product.ArabicName')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="name_en" class="form-label">@lang('product.EnglishName')</label>
                                        <input type="text" name="name_en" id="name_en" class="form-control" placeholder="@lang('product.EnglishName')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_ar" class="form-label">@lang('product.ArabicDesc')</label>
                                        <textarea name="description_ar" id="description_ar" class="form-control" rows="2"></textarea>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicDesc')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_en" class="form-label">@lang('product.EnglishDesc')</label>
                                        <textarea name="description_en" id="description_en" class="form-control" rows="2"></textarea>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishDesc')</div>
                                    </div>

                                    <div class="col-xl-4">
                                        <label for="main_unit_id" class="form-label">@lang('product.Unit')</label>
                                        <select name="main_unit_id" id="main_unit_id" class=" form-control" required>
                                            <option value="" selected disabled>@lang('product.ChooseUnit')</option>
                                            @foreach ($Units as $Unit)
                                                <option value="{{ $Unit->id }}">{{ $Unit->name_ar . " | ".$Unit->name_en }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterUnit')</div>
                                    </div>
{{--
                                    <div class="col-xl-4">
                                        <label for="factor" class="form-label">@lang('product.Factor')</label>
                                        <input type="number" name="factor" id="factor" class="form-control"
                                               placeholder="@lang('product.Factor')" step="0.1" required>
                                        <div class="invalid-feedback">@lang('validation.EnterFactor')</div>
                                    </div> --}}

                                    <div class="col-xl-4">
                                        <label for="currency_code" class="form-label">@lang('product.Currency')</label>
                                        <select name="currency_code" id="currency_code" class="form-control" required>
                                            <option value="" selected disabled>@lang('product.ChooseCurrency')</option>
                                            @foreach ($Currencies as $Currency)
                                                <option value="{{ $Currency['code'] }}">{{ $Currency['code'] }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterCurrency')</div>
                                    </div>


                                    <div class="col-xl-4">
                                        <label for="category_id" class="form-label">@lang('product.Category')</label>
                                        <select name="category_id" id="category_id" class=" form-control" required>
                                            <option value="" selected disabled>@lang('product.ChooseCategory')</option>
                                            @foreach ($Categories as $Category)
                                                <option value="{{ $Category->id }}">{{ $Category->name_ar. " | ".$Category->name_en }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterCategory')</div>
                                    </div>
{{--
                                    <div class="col-xl-4">
                                        <label for="store_id" class="form-label">@lang('product.Store')</label>
                                        <select name="store_id" id="store_id" class="js-example-basic-single form-control" required>
                                            <option value="" selected disabled>@lang('product.ChooseStore')</option>
                                            @foreach ($Stores as $Store)
                                                <option value="{{ $Store->id }}">{{ $Store->name_ar . " | ".$Store->name_en}}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterStore')</div>
                                    </div> --}}

                                    <div class="col-xl-4">
                                        <label for="barcode" class="form-label">@lang('product.Barcode')</label>
                                        <input type="text" name="barcode" id="barcode" class="form-control" placeholder="@lang('product.Barcode')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterBarcode')</div>
                                    </div>
{{--
                                    <div class="col-xl-4">
                                        <label for="price" class="form-label">@lang('product.Price')</label>
                                        <input type="number" name="price" id="price" class="form-control" placeholder="@lang('product.Price')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterPrice')</div>
                                    </div> --}}
                                    <div class="col-xl-4">
                                        <label for="min_limit" class="form-label">@lang('product.MinLimit')</label>
                                        <input type="number" name="min_limit" id="min_limit" class="form-control" placeholder="@lang('product.MinLimit')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterMinLimit')</div>
                                    </div>
                                    <div class="col-xl-4">
                                        <label for="max_limit" class="form-label">@lang('product.MaxLimit')</label>
                                        <input type="number" name="max_limit" id="max_limit" class="form-control" placeholder="@lang('product.MaxLimit')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterMaxLimit')</div>
                                    </div>

                                    <div class="col-xl-4">
                                        <label for="sku" class="form-label">@lang('product.Sku')</label>
                                        <input type="text" name="sku" id="sku" class="form-control" placeholder="@lang('product.Sku')" required>
                                        <div class="invalid-feedback">@lang('validation.Entercode')</div>
                                    </div>

                                    <div class="col-xl-4">
                                        <p class="mb-2 text-muted">@lang('product.Type')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="type" value="raw" class="form-check-input" checked required>
                                            <label class="form-check-label">@lang('product.Raw')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="type" value="complete" class="form-check-input" required>
                                            <label class="form-check-label">@lang('product.Complete')</label>
                                        </div>
                                    </div>

                                    <div class="col-xl-4">
                                        <p class="mb-2 text-muted">@lang('product.Remind')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="is_remind" value="1" class="form-check-input" checked required>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="is_remind" value="0" class="form-check-input" required>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <div class="col-xl-4">
                                        <p class="mb-2 text-muted">@lang('product.IsHaveExpired')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="is_have_expired" value="1" class="form-check-input" checked required>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="is_have_expired" value="0" class="form-check-input" required>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <div class="col-xl-4">
                                        <label for="main_image" class="form-label">@lang('product.Image')</label>
                                        <input type="file" name="main_image" id="main_image" class="form-control" required>
                                        <div class="invalid-feedback">@lang('validation.EnterImage')</div>
                                    </div>

                                    <div class="col-xl-4">
                                        <label for="images" class="form-label">@lang('product.Images')</label>
                                        <input type="file" name="images[]" id="images" class="form-control" multiple>
                                        <div class="invalid-feedback">@lang('validation.EnterImages')</div>
                                    </div>
                                    {{-- <div class="col-xl-12">
                                        <label for="branches" class="form-label">@lang('dishes.SelectItemCode')</label>
                                        <select name="item_code_id" id="ItemCode" class="form-control select2"  required>
                                            <option disabled selected>@lang('dishes.AllCodes')</option>
                                            @foreach (getItemCodes() as $codes)
                                                <option value="{{ $codes->id }}" >                {{ app()->getLocale() == 'en' ? $codes->codeName : $codes->codeNameAr }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div> --}}
                                    {{-- <div class="col-xl-4">
                                        <label for="expiry_date" class="form-label">@lang('product.ExpiryDate')</label>
                                        <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                                        <div class="invalid-feedback">@lang('validation.expiry_date')</div>
                                    </div> --}}

                                    <center>
                                        <div class="col-xl-4">
                                            <button type="submit" class="btn btn-primary form-control">@lang('category.save')</button>
                                        </div>
                                    </center>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Custom JS -->
    @vite('resources/assets/js/validation.js')
    <script>
        $(document).ready(function () {
            $('.js-example-basic-single').select2();
        });
    </script>
@endsection
