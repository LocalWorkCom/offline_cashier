@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('dishes.AddProduct')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.dishes.index') }}">@lang('dishes.Dishes')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('dishes.AddProduct')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('dishes.AddProduct')</div>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form method="POST" action="{{ route('dashboard.dish_products.store') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row gy-4">
                                    <!-- Name Fields -->
                                    <div class="col-xl-6">
                                        <label for="name_ar" class="form-label">@lang('dishes.NameArabic')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar"
                                            value="{{ old('name_ar') }}">
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="name_en" class="form-label">@lang('dishes.NameEnglish')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en"
                                            value="{{ old('name_en') }}">
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_ar" class="form-label">@lang('dishes.DescriptionArabic')</label>
                                        <textarea class="form-control" id="description_ar" name="description_ar">{{ old('description_ar') }}</textarea>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="description_en" class="form-label">@lang('dishes.DescriptionEnglish')</label>
                                        <textarea class="form-control" id="description_en" name="description_en">{{ old('description_en') }}</textarea>
                                    </div>


                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="time" class="form-label">@lang('recipes.time')</label>
                                        <input type="number" min="1" class="form-control" id="time"
                                            name="time" value="{{ old('time') }}" placeholder="@lang('recipes.time')">
                                        <div class="invalid-feedback">@lang('recipes.time')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="price" class="form-label">@lang('dishes.Price')</label>
                                        <input type="number" class="form-control" id="price" name="price"
                                            step="0.01" min="0" value="{{ old('price') }}">
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="branches" class="form-label">@lang('dishes.Branches')</label>
                                        <select name="branches[]" id="branches" class="form-control select2" multiple>
                                            <option value="all"
                                                {{ in_array('all', old('branches', [])) ? 'selected' : '' }}>
                                                @lang('dishes.AllBranches')</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ in_array($branch->id, old('branches', [])) ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="complete_product" class="form-label">@lang('dishes.CompleteProduct')</label>
                                        <select name="complete_product" id="complete_product" class="form-control select2">
                                            <option value="">@lang('dishes.SelectCompleteProduct')</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ old('complete_product') == $product->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'en' ? $product->name_en : $product->name_ar }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="is_active" class="form-label">@lang('dishes.IsActive')</label>
                                        <select name="is_active" id="is_active" class="form-control">
                                            <option value="1" {{ old('is_active') == 1 ? 'selected' : '' }}>
                                                @lang('dishes.Active')</option>
                                            <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>
                                                @lang('dishes.Inactive')</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="branches" class="form-label">@lang('dishes.SelectItemCode')</label>
                                        <select name="item_code_id" id="ItemCode" class="form-control select2" required>
                                            <option disabled selected>@lang('dishes.AllCodes')</option>
                                            @foreach (getItemCodes() as $codes)
                                                <option value="{{ $codes->id }}">
                                                    {{ app()->getLocale() == 'en' ? $codes->codeName : $codes->codeNameAr }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-12">
                                        <label for="image" class="form-label">@lang('dishes.Image')</label>
                                        <input type="file" class="form-control" id="image" name="image">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">@lang('dishes.Save')</button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection
