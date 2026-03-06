@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('recipes.CreateRecipe')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.recipes.index') }}">@lang('recipes.Recipes')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('recipes.CreateRecipe')</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('recipes.CreateRecipe')</div>
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

                            <form method="POST" action="{{ route('dashboard.recipes.store') }}" class="needs-validation"
                                enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_ar" class="form-label">@lang('recipes.NameArabic')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar"
                                            value="{{ old('name_ar') }}" placeholder="@lang('recipes.NameArabic')" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('recipes.EnterNameArabic')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_en" class="form-label">@lang('recipes.NameEnglish')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en"
                                            value="{{ old('name_en') }}" placeholder="@lang('recipes.NameEnglish')" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('recipes.EnterNameEnglish')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="description_ar" class="form-label">@lang('recipes.DescriptionArabic')</label>
                                        <textarea class="form-control" id="description_ar" name="description_ar" rows="4" required>{{ old('description_ar') }}</textarea>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="description_en" class="form-label">@lang('recipes.DescriptionEnglish')</label>
                                        <textarea class="form-control" id="description_en" name="description_en" rows="4" required>{{ old('description_en') }}</textarea>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="time" class="form-label">@lang('recipes.time')</label>
                                        <input type="number" class="form-control" id="time" name="time"
                                            value="{{ old('time') }}" min="1" placeholder="@lang('recipes.time')">
                                        <div class="invalid-feedback">@lang('recipes.time')</div>
                                    </div>

                                    <!-- Item Code -->
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
                                        <div class="invalid-feedback">@lang('validation.required', ['attribute' => __('dishes.SelectItemCode')])</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="type" class="form-label">@lang('recipes.Type')</label>
                                        <select class="form-control select2" name="type" id="type" required>
                                            <option value="1">@lang('recipes.recipe')</option>
                                            <option value="2">@lang('recipes.addon')</option>
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.required', ['attribute' => __('recipes.Type')])</div>
                                    </div>

                                    {{-- <div class="col-xl-6">
                                        <label for="type" class="form-label">@lang('recipes.Type')</label>
                                        <select class="form-control select2" name="type" id="type" required>
                                            <option value="1" {{ $recipe->type == 1 ? 'selected' : '' }}>
                                                @lang('recipes.MainDish')</option>
                                            <option value="2" {{ $recipe->type == 2 ? 'selected' : '' }}>
                                                @lang('recipes.Drink')</option>
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.required', ['attribute' => __('recipes.Type')])</div>
                                    </div> --}}

                                    <div class="col-xl-12">
                                        <label for="ingredients" class="form-label">@lang('recipes.Ingredients')</label>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('recipes.Product')</th>
                                                    <th>@lang('recipes.Quantity')</th>
                                                    <th>@lang('recipes.LossPercent')</th>
                                                    <th>@lang('recipes.Actions')</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ingredients-table">
                                                <tr>
                                                    <td>
                                                    <select name="ingredients[0][product_id]" class="form-control select2" required>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}">{{ $product->name_ar. " | ".$product->name_en }}</option>
                                                        @endforeach
                                                    </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="ingredients[0][quantity]" class="form-control" min="0" step="0.01" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="ingredients[0][loss_percent]" class="form-control" min="0" max="100" step="0.01">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-ingredient">@lang('recipes.Remove')</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <button type="button" id="add-ingredient" class="btn btn-success btn-sm">@lang('recipes.AddIngredient')</button>
                                    </div>

                                    <div class="col-xl-12">
                                        <label for="images" class="form-label">@lang('recipes.Images')</label>
                                        <input class="form-control" type="file" id="images" name="images[]" multiple>
                                    </div>

                                    <div class="col-xl-6">
                                        <p class="mb-2 text-muted">@lang('recipes.IsActive')</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_active" id="is_active_yes" value="1" checked>
                                            <label class="form-check-label" for="is_active_yes">@lang('recipes.Yes')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_active" id="is_active_no" value="0">
                                            <label class="form-check-label" for="is_active_no">@lang('recipes.No')</label>
                                        </div>
                                    </div>

                                    <div class="col-xl-4">
                                        <input type="submit" class="form-control btn btn-primary" value="@lang('recipes.Save')">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row -->
        </div>
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- SELECT2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
            let ingredientIndex = 1;

            $('#add-ingredient').on('click', function() {
                $('#ingredients-table').append(`
                    <tr>
                        <td>
                            <select name="ingredients[${ingredientIndex}][product_id]" class="form-control select2" required>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name_ar. " | ".$product->name_en }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="ingredients[${ingredientIndex}][quantity]" class="form-control" min="0" step="0.01" required>
                        </td>
                        <td>
                            <input type="number" name="ingredients[${ingredientIndex}][loss_percent]" class="form-control" min="0" max="100" step="0.01">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-ingredient">@lang('recipes.Remove')</button>
                        </td>
                    </tr>
                `);
                ingredientIndex++;
                $('.select2').select2();
            });

            $(document).on('click', '.remove-ingredient', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
@endsection
