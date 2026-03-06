@extends('layouts.master')

@section('styles')
    <!-- Include SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('dishes.EditDish')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.dishes.index') }}">@lang('dishes.Dishes')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('dishes.EditDish')</li>
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
                            <div class="card-title">@lang('dishes.EditDish')</div>
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

                            <form method="POST" action="{{ route('dashboard.dishes.update', $dish->id) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Dish Basic Info -->
                                <div class="row gy-4">
                                    <div class="col-xl-6">
                                        <label for="name_ar" class="form-label">@lang('dishes.NameArabic')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar"
                                            value="{{ $dish->name_ar }}" required>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="name_en" class="form-label">@lang('dishes.NameEnglish')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en"
                                            value="{{ $dish->name_en }}" required>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="description_ar" class="form-label">@lang('dishes.DescriptionArabic')</label>
                                        <textarea class="form-control" id="description_ar" name="description_ar" required>{{ $dish->description_ar }}</textarea>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="description_en" class="form-label">@lang('dishes.DescriptionEnglish')</label>
                                        <textarea class="form-control" id="description_en" name="description_en" required>{{ $dish->description_en }}</textarea>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="time" class="form-label">@lang('recipes.time')</label>
                                        <input type="number" min="1" class="form-control" id="time"
                                            name="time" value="{{ old('time', $dish->time) }}">
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="category_id" class="form-label">@lang('dishes.Category')</label>
                                        <select name="category_id" class="form-control select2" required>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ $dish->category_id == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name_ar . ' | ' . $category->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="cuisine_id" class="form-label">@lang('dishes.Cuisine')</label>
                                        <select name="cuisine_id" class="form-control select2" required>
                                            @foreach ($cuisines as $cuisine)
                                                <option value="{{ $cuisine->id }}"
                                                    {{ $dish->cuisine_id == $cuisine->id ? 'selected' : '' }}>
                                                    {{ $cuisine->name_ar . ' | ' . $cuisine->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-6">
                                        {{-- <label for="price" class="form-label">@lang('dishes.Price')</label>
                                        <input type="number" class="form-control" id="price" name="price"
                                            value="{{ $dish->price }}" min="0" step="0.01" required>
                                     --}}
                                        <div id="price-field" class="{{ $dish->has_sizes ? 'd-none' : '' }}">
                                            <label for="price" class="form-label">@lang('dishes.Price')</label>
                                            <input type="number" class="form-control" id="price" name="price"
                                                value="{{ $dish->price }}" min="0" step="0.01" required>
                                        </div>
                                    </div>

                                    {{-- <div class="col-xl-6">
                                        <label for="branches" class="form-label">@lang('dishes.SelectBranches')</label>
                                        <select name="branches[]" id="branches" class="form-control select2" multiple required>
                                            <option value="all" {{ in_array('all', $selectedBranches ?? []) ? 'selected' : '' }}>
                                                @lang('dishes.AllBranches')
                                            </option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ in_array($branch->id, $selectedBranches ?? []) ? 'selected' : '' }}>
                                                    {{ $branch->name_ar . ' | ' . $branch->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div> --}}

                                    <div class="col-xl-6">
                                        <label for="time_cancelation" class="form-label">@lang('dishes.time_cancelation')</label>
                                        <input type="number" value="{{ $dish->time_cancelation }}" name="time_cancelation"
                                            id="time_cancelation" class="form-control" required>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="branches" class="form-label">@lang('dishes.SelectItemCode')</label>
                                        <select name="item_code_id" id="ItemCode" class="form-control select2"  required>
                                            <option disabled selected>@lang('dishes.AllCodes')</option>
                                            @foreach (getItemCodes() as $codes)
                                                <option value="{{ $codes->id }}" {{ $dish->item_code_id == $codes->id ? 'selected' : '' }}>                {{ app()->getLocale() == 'en' ? $codes->codeName : $codes->codeNameAr }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="image" class="form-label">@lang('dishes.Image')</label>
                                        <input type="file" class="form-control" id="image" name="image">
                                        @if ($dish->image)
                                            <img src="{{ asset($dish->image) }}" alt="Dish Image" width="100"
                                                class="mt-2">
                                        @endif
                                    </div>


                                    <!-- Recipes for Dishes Without Sizes -->
                                    <div id="dish-recipes-section"
                                        class="col-xl-12 {{ $dish->has_sizes ? 'd-none' : '' }}">
                                        <h5 class="mb-3">@lang('dishes.DishRecipes')</h5>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('dishes.Recipe')</th>
                                                    <th>@lang('dishes.Quantity')</th>
                                                    <th>@lang('dishes.Actions')</th>
                                                </tr>
                                            </thead>
                                            <tbody id="dish-recipes-table">
                                                @foreach ($dish->details as $index => $detail)
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="details[{{ $index }}][id]"
                                                                value="{{ $detail->id }}">
                                                            <select name="details[{{ $index }}][recipe_id]"
                                                                class="form-control select2" required>
                                                                @foreach ($recipes as $recipe)
                                                                    <option value="{{ $recipe->id }}"
                                                                        {{ $detail->recipe_id == $recipe->id ? 'selected' : '' }}>
                                                                        {{ $recipe->name_ar . ' | ' . $recipe->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="details[{{ $index }}][quantity]"
                                                                value="{{ $detail->quantity }}" class="form-control"
                                                                step="0.01" min="0" required>
                                                        </td>
                                                        <td>
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <button type="button" id="add-dish-recipe"
                                            class="btn btn-success btn-sm">@lang('dishes.AddRecipe')</button>
                                    </div>


                                    <div class="col-xl-6">
                                        <label for="has_sizes" class="form-label">@lang('dishes.HasSizes')</label>
                                        <select name="has_sizes" id="has_sizes" class="form-control select2" required>
                                            <option value="0" {{ $dish->has_sizes == 0 ? 'selected' : '' }}>
                                                @lang('dishes.No')</option>
                                            <option value="1" {{ $dish->has_sizes == 1 ? 'selected' : '' }}>
                                                @lang('dishes.Yes')</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="has_addon" class="form-label">@lang('dishes.HasAddon')</label>
                                        <select name="has_addon" id="has_addon" class="form-control select2" required>
                                            <option value="0" {{ $dish->has_addon == 0 ? 'selected' : '' }}>
                                                @lang('dishes.No')
                                            </option>
                                            <option value="1" {{ $dish->has_addon == 1 ? 'selected' : '' }}>
                                                @lang('dishes.Yes')
                                            </option>
                                        </select>
                                    </div>



                                    <div class="col-xl-6">
                                        <label for="is_active" class="form-label">@lang('dishes.IsActive')</label>
                                        <select name="is_active" id="is_active" class="form-control select2" required>
                                            <option value="1" {{ $dish->is_active == 1 ? 'selected' : '' }}>
                                                @lang('dishes.Active')</option>
                                            <option value="0" {{ $dish->is_active == 0 ? 'selected' : '' }}>
                                                @lang('dishes.Inactive')</option>
                                        </select>
                                    </div>


                                </div>

                                <!-- Dish Sizes -->
                                <div id="dish-sizes-section" class="col-xl-12 {{ $dish->has_sizes ? '' : 'd-none' }}">
                                    <h5 class="mb-3">@lang('dishes.DishSizes')</h5>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>@lang('dishes.SizeNameArabic')</th>
                                                <th>@lang('dishes.SizeNameEnglish')</th>
                                                <th>@lang('dishes.Price')</th>
                                                <th>@lang('dishes.DishRecipes')</th>
                                                <th>@lang('dishes.DefaultSize')</th>
                                                <th>@lang('dishes.Actions')</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dish-sizes-table">
                                            @foreach ($dish->sizes as $index => $size)
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="sizes[{{ $index }}][id]" value="{{ $size->id }}">
                                                        <input type="text" name="sizes[{{ $index }}][size_name_ar]" value="{{ $size->size_name_ar }}" class="form-control" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="sizes[{{ $index }}][size_name_en]" value="{{ $size->size_name_en }}" class="form-control" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="sizes[{{ $index }}][price]" value="{{ $size->price }}" class="form-control" step="0.01" min="0" required>
                                                    </td>
                                                    <td>
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>@lang('dishes.Recipe')</th>
                                                                    <th>@lang('dishes.Quantity')</th>
                                                                    <th>@lang('dishes.Actions')</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="recipes-table-{{ $index }}">
                                                                @foreach ($size->recipes as $recipeIndex => $recipe)
                                                                    <tr>
                                                                        <td>
                                                                            <select name="sizes[{{ $index }}][recipes][{{ $recipeIndex }}][recipe_id]" class="form-control select2" required>
                                                                                @foreach ($recipes as $r)
                                                                                    <option value="{{ $r->id }}" {{ $r->id == $recipe->recipe_id ? 'selected' : '' }}>{{ $r->name_ar . ' | ' . $r->name_en}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" name="sizes[{{ $index }}][recipes][{{ $recipeIndex }}][quantity]" value="{{ $recipe->quantity }}" class="form-control" step="0.01" min="0" required>
                                                                        </td>
                                                                        <td>
                                                                            <button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                        <button type="button" class="btn btn-success btn-sm add-recipe" data-size-index="{{ $index }}">@lang('dishes.AddRecipe')</button>
                                                    </td>
                                                    <td>
                                                        <input type="radio" name="default_size" value="{{ $index }}" {{ $size->default_size ? 'checked' : '' }} class="form-check-input" {{ $index == 0 ? 'required' : '' }}>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <button type="button" id="add-dish-size" class="btn btn-success btn-sm">@lang('dishes.AddSize')</button>
                                </div>

                                <!-- Dish Addons -->
                                <div id="addon-categories-section">
                                    @if (!empty($dish->dishAddonsDetails) && $dish->dishAddonsDetails->isNotEmpty())
                                        @foreach ($dish->dishAddonsDetails->groupBy('addon_category_id') as $addonCategoryIndex => $addons)
                                            @php
                                                $addonCategory = $addons->first();
                                            @endphp
                                            <div class="addon-category mt-4">
                                                <div class="row gy-2">
                                                    <div class="col-xl-4">
                                                        <label>@lang('dishes.AddonCategory')</label>
                                                        <select
                                                            name="addon_categories[{{ $addonCategoryIndex }}][addon_category_id]"
                                                            class="form-control select2" required>
                                                            @foreach ($addonCategories as $category)
                                                                <option value="{{ $category->id }}"
                                                                    {{ $addonCategory->addon_category_id == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name_ar . ' | ' . $category->name_en }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-xl-4">
                                                        <label>@lang('dishes.MinAddons')</label>
                                                        <input type="number"
                                                            name="addon_categories[{{ $addonCategoryIndex }}][min_addons]"
                                                            class="form-control" value="{{ $addonCategory->min_addons }}"
                                                            min="0" required>
                                                    </div>
                                                    <div class="col-xl-4">
                                                        <label>@lang('dishes.MaxAddons')</label>
                                                        <input type="number"
                                                            name="addon_categories[{{ $addonCategoryIndex }}][max_addons]"
                                                            class="form-control" value="{{ $addonCategory->max_addons }}"
                                                            min="1" required>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>@lang('dishes.Addon')</th>
                                                                <th>@lang('dishes.Quantity')</th>
                                                                <th>@lang('dishes.Price')</th>
                                                                <th>@lang('dishes.Actions')</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="addons-table-{{ $addonCategoryIndex }}">
                                                            @foreach ($addons as $addonIndex => $addon)
                                                                <tr>
                                                                    <td>
                                                                        <input type="hidden"
                                                                            name="addon_categories[{{ $addonCategoryIndex }}][addons][{{ $addonIndex }}][id]"
                                                                            value="{{ $addon->id }}">
                                                                        <select
                                                                            name="addon_categories[{{ $addonCategoryIndex }}][addons][{{ $addonIndex }}][addon_id]"
                                                                            class="form-control select2" required>
                                                                            @foreach ($allAddons as $availableAddon)
                                                                                <option value="{{ $availableAddon->id }}"
                                                                                    {{ $addon->addon_id == $availableAddon->id ? 'selected' : '' }}>
                                                                                    {{ $availableAddon->name_ar . ' | ' . $availableAddon->name_en }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            name="addon_categories[{{ $addonCategoryIndex }}][addons][{{ $addonIndex }}][quantity]"
                                                                            value="{{ $addon->quantity }}"
                                                                            class="form-control" min="0" required>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            name="addon_categories[{{ $addonCategoryIndex }}][addons][{{ $addonIndex }}][price]"
                                                                            value="{{ $addon->price }}"
                                                                            class="form-control" min="0"
                                                                            step="0.01" required>
                                                                    </td>
                                                                    <td>
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>

                                                    </table>
                                                    <button type="button" class="btn btn-success btn-sm add-addon"
                                                        data-category-index="{{ $addonCategoryIndex }}">
                                                        @lang('dishes.AddAddon')
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm remove-addon-category">@lang('dishes.RemoveAddonCategory')</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p>@lang('dishes.NoAddons')</p>
                                    @endif
                                </div>
                                <button type="button"
                                    class="btn btn-primary btn-sm mt-3 add-addon-category"
                                    id="add-addon-category"
                                    style="display: {{ $dish->has_addon == 1 ? 'block' : 'none' }};">
                                    @lang('dishes.AddAddonCategory')
                                </button>



                                <!-- Submit -->
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
            // Initialize Select2
            $('.select2').select2();

            // Initialize dynamic indices based on existing data
            let sizeIndex = {{ count($dish->sizes) }};
            let addonCategoryIndex = {{ count($dish->dishAddonsDetails->groupBy('addon_category_id') ?? []) }};

            // Function to toggle visibility based on `has_sizes` value
            function toggleSections() {
                if ($('#has_sizes').val() == 1) {
                    $('#dish-sizes-section').removeClass('d-none');
                    $('#price-field').addClass('d-none');
                    $('#dish-recipes-section').addClass('d-none');
                    $('#price').attr('required', false);
                } else {
                    $('#dish-sizes-section').addClass('d-none');
                    $('#price-field').removeClass('d-none');
                    $('#dish-recipes-section').removeClass('d-none');
                    $('#price').attr('required', true);
                }
            }

            // Initialize section visibility on page load
            toggleSections();

            // Toggle visibility when `has_sizes` value changes
            $('#has_sizes').on('change', toggleSections);

            // Add Dish Size Row
            $('#add-dish-size').on('click', function() {
                let recipeIndex = 0;
                $('#dish-sizes-table').append(`
                    <tr>
                        <td><input type="text" name="sizes[${sizeIndex}][size_name_ar]" class="form-control" required></td>
                        <td><input type="text" name="sizes[${sizeIndex}][size_name_en]" class="form-control" required></td>
                        <td><input type="number" name="sizes[${sizeIndex}][price]" class="form-control" step="0.01" min="0" required></td>
                        <td>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>@lang('dishes.Recipe')</th>
                                        <th>@lang('dishes.Quantity')</th>
                                        <th>@lang('dishes.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody id="recipes-table-${sizeIndex}">
                                    <tr>
                                        <td>
                                            <input type="hidden" name="sizes[${sizeIndex}][recipes][${recipeIndex}][id]" value="">
                                            <select name="sizes[${sizeIndex}][recipes][${recipeIndex}][recipe_id]" class="form-control select2" required>
                                                @foreach ($recipes as $recipe)
                                                    <option value="{{ $recipe->id }}">{{ $recipe->name_ar . ' | ' . $recipe->name_en }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="sizes[${sizeIndex}][recipes][${recipeIndex}][quantity]" class="form-control" step="0.01" min="0" required></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-sm add-recipe" data-size-index="${sizeIndex}">@lang('dishes.AddRecipe')</button>
                        </td>
                        <td><input type="radio" name="default_size" value="${sizeIndex}" class="form-check-input" ${sizeIndex == 0 ? "required" : ""}></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                    </tr>
                `);
                sizeIndex++;
                $('.select2').select2();
            });

            // Add Recipe Row for Dish Size
            $(document).on('click', '.add-recipe', function() {
                let sizeIndex = $(this).data('size-index');
                let recipeIndex = $(`#recipes-table-${sizeIndex} tr`).length;
                let selectedRecipes = [];

                // Collect already selected recipe IDs for this size
                $(`#recipes-table-${sizeIndex} select[name^="sizes[${sizeIndex}][recipes]"][name$="[recipe_id]"]`).each(function() {
                    selectedRecipes.push($(this).val());
                });

                // Filter available recipes to exclude already selected ones
                let newRecipeOptions = '';
                @foreach ($recipes as $recipe)
                    if (!selectedRecipes.includes('{{ $recipe->id }}')) {
                        newRecipeOptions += `<option value="{{ $recipe->id }}">{{ $recipe->name_ar . ' | ' . $recipe->name_en }}</option>`;
                    }
                @endforeach

                if (newRecipeOptions === '') {
                    alert("@lang('dishes.NoMoreRecipesAvailable')");
                    return;
                }

                $(`#recipes-table-${sizeIndex}`).append(`
                    <tr>
                        <td>
                            <input type="hidden" name="sizes[${sizeIndex}][recipes][${recipeIndex}][id]" value="">
                            <select name="sizes[${sizeIndex}][recipes][${recipeIndex}][recipe_id]" class="form-control select2" required>
                                ${newRecipeOptions}
                            </select>
                        </td>
                        <td><input type="number" name="sizes[${sizeIndex}][recipes][${recipeIndex}][quantity]" class="form-control" step="0.01" min="0" required></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                    </tr>
                `);

                $('.select2').select2();
            });

            // Add Recipe Row for Dishes Without Sizes
            let recipeIndex = {{ count($dish->details) }};
            $('#add-dish-recipe').on('click', function() {
                let selectedRecipes = [];
                $('#dish-recipes-table select[name^="details"]').each(function() {
                    selectedRecipes.push($(this).val());
                });

                let newRecipeOptions = '';
                @foreach ($recipes as $recipe)
                    if (!selectedRecipes.includes('{{ $recipe->id }}')) {
                        newRecipeOptions += `<option value="{{ $recipe->id }}">{{ $recipe->name_ar . ' | ' . $recipe->name_en }}</option>`;
                    }
                @endforeach

                if (newRecipeOptions === '') {
                    alert("@lang('dishes.NoMoreRecipesAvailable')");
                    return;
                }

                $('#dish-recipes-table').append(`
                    <tr>
                        <td>
                            <input type="hidden" name="details[${recipeIndex}][id]" value="">
                            <select name="details[${recipeIndex}][recipe_id]" class="form-control select2" required>
                                ${newRecipeOptions}
                            </select>
                        </td>
                        <td>
                            <input type="number" name="details[${recipeIndex}][quantity]" class="form-control" step="0.01" min="0" required>
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                    </tr>
                `);

                recipeIndex++;
                $('.select2').select2();
            });

            // Add Addon Category
            $(document).on('click', '.add-addon-category', function() {
                const idx = ++addonCategoryIndex;
                $('#addon-categories-section').append(`
                    <div class="addon-category mt-4">
                        <div class="row gy-2">
                            <div class="col-xl-4">
                                <label>@lang('dishes.AddonCategory')</label>
                                <select name="addon_categories[${idx}][addon_category_id]" class="form-control select2" required>
                                    @foreach ($addonCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name_ar . ' | ' . $category->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-4">
                                <label>@lang('dishes.MinAddons')</label>
                                <input type="number" name="addon_categories[${idx}][min_addons]" class="form-control" min="0" required>
                            </div>
                            <div class="col-xl-4">
                                <label>@lang('dishes.MaxAddons')</label>
                                <input type="number" name="addon_categories[${idx}][max_addons]" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>@lang('dishes.Addon')</th>
                                        <th>@lang('dishes.Quantity')</th>
                                        <th>@lang('dishes.Price')</th>
                                        <th>@lang('dishes.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody id="addons-table-${idx}"></tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-sm add-addon" data-category-index="${idx}">@lang('dishes.AddAddon')</button>
                            <button type="button" class="btn btn-danger btn-sm remove-addon-category">@lang('dishes.RemoveAddonCategory')</button>
                        </div>
                    </div>
                `);
                $('.select2').select2();
            });

            // Add Addon Row
            $(document).on('click', '.add-addon', function() {
                let categoryIndex = $(this).data('category-index');
                let addonIndex = $(`#addons-table-${categoryIndex} tr`).length;
                $(`#addons-table-${categoryIndex}`).append(`
                    <tr>
                        <td>
                            <input type="hidden" name="addon_categories[${categoryIndex}][addons][${addonIndex}][id]" value="">
                            <select name="addon_categories[${categoryIndex}][addons][${addonIndex}][addon_id]" class="form-control select2" required>
                                @foreach ($allAddons as $addon)
                                    <option value="{{ $addon->id }}">{{ $addon->name_ar . ' | ' . $addon->name_en }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="addon_categories[${categoryIndex}][addons][${addonIndex}][quantity]" class="form-control" min="0" required></td>
                        <td><input type="number" name="addon_categories[${categoryIndex}][addons][${addonIndex}][price]" class="form-control" step="0.01" min="0" required></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                    </tr>
                `);
                $('.select2').select2();
            });

            // Remove Row
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Remove Addon Category
            $(document).on('click', '.remove-addon-category', function() {
                $(this).closest('.addon-category').remove();
            });

            // Toggle Addons Section
            $('#has_addon').on('change', function() {
                if ($(this).val() == 1) {
                    $('#addon-categories-section').removeClass('d-none');
                    $('#addon-categories-section').show();
                    $('.add-addon-category').show();
                    if ($('#addon-categories-section .addon-category').length === 0) {
                        $('.add-addon-category').trigger('click');
                    }
                } else {
                    $('#addon-categories-section').addClass('d-none');
                    $('.add-addon-category').hide();
                    $('#addon-categories-section').empty();
                }
            });

            // Initialize addon visibility
            function toggleAddonButton() {
                if ($('#has_addon').val() == '1') {
                    $('.add-addon-category').show();
                    $('#addon-categories-section').show();
                } else {
                    $('.add-addon-category').hide();
                    $('#addon-categories-section').hide();
                }
            }
            toggleAddonButton();

            // Toggle price field
            function togglePriceField() {
                if ($('#has_sizes').val() == 1) {
                    $('#price-field').addClass('d-none');
                    $('#price').attr('required', false);
                } else {
                    $('#price-field').removeClass('d-none');
                    $('#price').attr('required', true);
                }
            }
            togglePriceField();
        });
    </script>
@endsection 