@extends('layouts.master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('recipes.EditRecipe')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.recipes.index') }}">@lang('recipes.Recipes')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('recipes.EditRecipe')</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('recipes.EditRecipe')</div>
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

                            <form method="POST" action="{{ route('dashboard.recipes.update', $recipe->id) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <!-- Name Arabic -->
                                    <div class="col-xl-6">
                                        <label for="name_ar" class="form-label">@lang('recipes.NameArabic')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar"
                                            value="{{ old('name_ar', $recipe->name_ar) }}">
                                    </div>

                                    <!-- Name English -->
                                    <div class="col-xl-6">
                                        <label for="name_en" class="form-label">@lang('recipes.NameEnglish')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en"
                                            value="{{ old('name_en', $recipe->name_en) }}">
                                    </div>

                                    <!-- Description Arabic -->
                                    <div class="col-xl-6">
                                        <label for="description_ar" class="form-label">@lang('recipes.DescriptionArabic')</label>
                                        <textarea class="form-control" id="description_ar" name="description_ar">{{ old('description_ar', $recipe->description_ar) }}</textarea>
                                    </div>

                                    <!-- Description English -->
                                    <div class="col-xl-6">
                                        <label for="description_en" class="form-label">@lang('recipes.DescriptionEnglish')</label>
                                        <textarea class="form-control" id="description_en" name="description_en">{{ old('description_en', $recipe->description_en) }}</textarea>
                                    </div>

                                    {{-- {{ dd($recipe->time) }} --}}

                                    <div class="col-xl-6">
                                        <label for="time" class="form-label">@lang('recipes.time')</label>

                                        <input type="number" class="form-control" id="time" name="time"
                                            value="{{ old('time', $recipe->time) }}" min="1">

                                    </div>
                                    <div class="col-xl-6">
                                        <label for="branches" class="form-label">@lang('dishes.SelectItemCode')</label>
                                        <select name="item_code_id" id="ItemCode" class="form-control select2" required>
                                            <option disabled selected>@lang('dishes.AllCodes')</option>
                                            @foreach (getItemCodes() as $codes)
                                                <option value="{{ $codes->id }}"
                                                    {{ $recipe->item_code_id == $codes->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'en' ? $codes->codeName : $codes->codeNameAr }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="type" class="form-label">@lang('recipes.Type')</label>
                                        <select class="form-control select2" name="type" id="type" required>
                                            <option value="1" {{ $recipe->type == 1 ? 'selected' : '' }}>
                                                @lang('recipes.MainDish')</option>
                                            <option value="2" {{ $recipe->type == 2 ? 'selected' : '' }}>
                                                @lang('recipes.Drink')</option>
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.required', ['attribute' => __('recipes.Type')])</div>
                                    </div>

                                    <!-- Ingredients -->
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
                                                @foreach ($recipe->ingredients as $index => $ingredient)
                                                    <tr>
                                                        <td>
                                                            <input type="hidden"
                                                                name="ingredients[{{ $index }}][id]"
                                                                value="{{ $ingredient->id }}">
                                                            <select name="ingredients[{{ $index }}][product_id]"
                                                                class="form-control select2" >
                                                                @foreach ($products as $product)
                                                                    <option value="{{ $product->id }}"
                                                                        {{ $product->id == $ingredient->product_id ? 'selected' : '' }}>
                                                                        {{ $product->name_ar . ' | ' . $product->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="ingredients[{{ $index }}][quantity]"
                                                                class="form-control" value="{{ $ingredient->quantity }}"
                                                                min="0" step="0.01" >
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="ingredients[{{ $index }}][loss_percent]"
                                                                class="form-control"
                                                                value="{{ $ingredient->loss_percent }}" min="0"
                                                                max="100" step="0.01">
                                                        </td>
                                                        <td>
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm remove-ingredient">@lang('recipes.Remove')</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <button type="button" id="add-ingredient"
                                            class="btn btn-success btn-sm">@lang('recipes.AddIngredient')</button>
                                    </div>

                                    <!-- Images -->
                                    <div class="col-xl-12">
                                        <label for="images" class="form-label">@lang('recipes.Images')</label>
                                        <div>
                                            @foreach ($recipe->images as $image)
                                                <img src="{{ asset($image->image_path) }}" alt="Recipe Image"
                                                    width="100" class="img-thumbnail">
                                            @endforeach
                                        </div>
                                        <input type="file" class="form-control mt-3" id="images" name="images[]"
                                            multiple>
                                    </div>

                                    <!-- Is Active -->
                                    <div class="col-xl-6">
                                        <label for="is_active" class="form-label">@lang('recipes.IsActive')</label>
                                        <div>
                                            <input type="radio" name="is_active" value="1"
                                                {{ $recipe->is_active ? 'checked' : '' }}> @lang('recipes.Yes')
                                            <input type="radio" name="is_active" value="0"
                                                {{ !$recipe->is_active ? 'checked' : '' }}> @lang('recipes.No')
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-xl-12">
                                        <button type="submit" class="btn btn-primary">@lang('recipes.Save')</button>
                                    </div>
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
            let ingredientIndex = {{ $recipe->ingredients->count() }};

            $('#add-ingredient').on('click', function() {
                $('#ingredients-table').append(`
                    <tr>
                        <td>
                            <select name="ingredients[${ingredientIndex}][product_id]" class="form-control select2">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name_ar . ' | ' . $product->name_en }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="ingredients[${ingredientIndex}][quantity]" class="form-control" min="0" step="0.01">
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
