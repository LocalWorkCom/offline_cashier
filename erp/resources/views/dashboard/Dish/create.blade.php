@extends('layouts.master')

@section('styles')
<!-- SELECT2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
    <h4 class="fw-medium mb-0">@lang('dishes.CreateDish')</h4>
    <div class="ms-sm-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.dishes.index') }}">@lang('dishes.Dishes')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('dishes.CreateDish')</li>
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
                        <div class="card-title">@lang('dishes.CreateDish')</div>
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

                        <form method="POST" action="{{ route('dashboard.dishes.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row gy-4">
                                <!-- Main Dish Info -->
                                <div class="col-xl-6">
                                    <label for="name_ar" class="form-label">@lang('dishes.NameArabic')</label>
                                    <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ old('name_ar') }}">
                                </div>

                                <div class="col-xl-6">
                                    <label for="name_en" class="form-label">@lang('dishes.NameEnglish')</label>
                                    <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en') }}">
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
                                    <input type="number" min="1" class="form-control" id="time" name="time" value="{{ old('time') }}" placeholder="@lang('recipes.time')">
                                    <div class="invalid-feedback">@lang('recipes.time')</div>
                                </div>
                                <div class="col-xl-6">
                                    <label for="category_id" class="form-label">@lang('dishes.Category')</label>
                                    <select name="category_id" class="form-control select2" required>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name_ar . ' | ' . $category->name_en }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xl-6">
                                    <label for="cuisine_id" class="form-label">@lang('dishes.Cuisine')</label>
                                    <select name="cuisine_id" class="form-control select2" required>
                                        @foreach ($cuisines as $cuisine)
                                        <option value="{{ $cuisine->id }}" {{ old('cuisine_id') == $cuisine->id ? 'selected' : '' }}>
                                            {{ $cuisine->name_ar . ' | ' . $cuisine->name_en }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xl-6" id="price-section">
                                    <label for="price" class="form-label">@lang('dishes.Price')</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="col-xl-6">
                                    <label for="branches" class="form-label">@lang('dishes.SelectItemCode')</label>
                                    <select name="item_code_id" id="ItemCode" class="form-control select2" required>
                                        <option disabled selected>@lang('dishes.AllCodes')</option>
                                        @foreach (getItemCodes() as $codes)
                                        <option value="{{ $codes->id }}"> {{ app()->getLocale() == 'en' ? $codes->codeName : $codes->codeNameAr }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xl-6">
                                    <label for="branches" class="form-label">@lang('dishes.SelectBranches')</label>
                                    <select name="branches[]" id="branches" class="form-control select2" multiple required>
                                        <option value="all">@lang('dishes.AllBranches')</option>
                                        @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name_ar . ' | ' . $branch->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-6">
                                    <label for="time_cancelation" class="form-label">@lang('dishes.time_cancelation')</label>
                                    <input type="number" class="form-control" id="time_cancelation" name="time_cancelation" required>
                                </div>
                                {{-- <div class="col-xl-6">
                                        <label for="branches" class="form-label">@lang('dishes.SelectItemCode')</label>
                                        <select name="item_code_id" id="ItemCode" class="form-control select2"  required>
                                            <option disabled selected>@lang('dishes.AllCodes')</option>
                                            @foreach (getItemCodes() as $codes)
                                                <option value="{{ $codes->id }}"> {{ app()->getLocale() == 'en' ? $codes->codeName : $codes->codeNameAr }}
                                </option>
                                @endforeach
                                </select>
                            </div> --}}
                            <div class="col-xl-6">
                                <label for="image" class="form-label">@lang('dishes.Image')</label>
                                <input type="file" class="form-control" id="image" name="image">
                            </div>
                            <!-- Recipes for Dishes Without Sizes -->
                            <div id="dish-recipes-section" class="col-xl-12 d-none">
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
                                        <tr>
                                            <td>
                                                <select name="details[0][recipe_id]" class="form-control select2" required>
                                                    @foreach ($recipes as $recipe)
                                                    <option value="{{ $recipe->id }}">{{ $recipe->name_ar . ' | ' . $recipe->name_en }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="details[0][quantity]" class="form-control recepe_quantity" step="0.01" min="0" required>
                                            </td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" id="add-dish-recipe" class="btn btn-success btn-sm">@lang('dishes.AddRecipe')</button>
                            </div>


                            <div class="col-xl-6">
                                <label for="has_sizes" class="form-label">@lang('dishes.HasSizes')</label>
                                <select name="has_sizes" id="has_sizes" class="form-control select2" required>
                                    <option value="0">@lang('dishes.No')</option>
                                    <option value="1">@lang('dishes.Yes')</option>
                                </select>
                            </div>

                            <div class="col-xl-6">
                                <label for="has_addon" class="form-label">@lang('dishes.HasAddon')</label>
                                <select name="has_addon" id="has_addon" class="form-control select2" required>
                                    <option value="0">@lang('dishes.No')</option>
                                    <option value="1">@lang('dishes.Yes')</option>
                                </select>
                            </div>

                            <div class="col-xl-6">
                                <label for="is_active" class="form-label">@lang('dishes.IsActive')</label>
                                <select name="is_active" id="is_active" class="form-control select2" required>
                                    <option value="1">@lang('dishes.Active')</option>
                                    <option value="0">@lang('dishes.Inactive')</option>
                                </select>
                            </div>

                            <!-- Dish Sizes -->
                            <div id="dish-sizes-section" class="col-xl-12 d-none">
                                <h5 class="mb-3">@lang('dishes.DishSizes')</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>@lang('dishes.SizeNameArabic')</th>
                                            <th>@lang('dishes.SizeNameEnglish')</th>
                                            <th>@lang('dishes.Price')</th>
                                            <th>@lang('dishes.applications-final-prices')</th>
                                            <th>@lang('dishes.DishRecipes')</th>
                                            <th>@lang('dishes.DefaultSize')</th>
                                            <th>@lang('dishes.Actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dish-sizes-table"></tbody>
                                </table>
                                <button type="button" id="add-dish-size" class="btn btn-success btn-sm">@lang('dishes.AddSize')</button>
                            </div>

                            <!-- Dish Applications -->
                            <div class="col-xl-6">
                                <label for="applications" class="form-label">@lang('dishes.SelectApplications')</label>
                                <select name="menus_integration_ids[]" id="applications" class="form-control select2" multiple>
                                    @foreach ($applications as $application)
                                    <option value="{{ $application->id }}">{{ $application->name_ar . ' | ' . $application->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dish Application Details -->
                            <div id="dish-application-section" class="col-xl-12 mt-4 d-none">
                                <h5 class="mb-3">@lang('dishes.DishApplication')</h5>
                                <div id="applications-container"></div>
                            </div>

                            <!-- Dish Addons -->
                            <div id="dish-addons-section" class="col-xl-12 d-none">
                                <h5 class="mb-3">@lang('dishes.DishAddons')</h5>
                                <div id="addon-categories-section"></div>
                                <button type="button" class="btn btn-primary btn-sm mt-3 add-addon-category">@lang('dishes.AddAddonCategory')</button>
                            </div>

                            <!-- Submit -->
                            <div class="col-xl-12">
                                <button type="submit" class="btn btn-primary">@lang('dishes.Save')</button>
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

        const applications = @json($applications);

        let sizeIndex = 0;
        let recipeIndex = 1;
        let addonCategoryIndex = 0;
        let appIndex = 0;

        function updateApplications() {
            const selectedApps = $('#applications').val() || [];
            const $container = $('#applications-container');
            $container.empty();
            appIndex = 0;

            const hasSizes = $('#has_sizes').val() == '1';

            if (selectedApps.length === 0) {
                $('#dish-application-section').addClass('d-none');
                refreshAllSizeAppPrices();
                refreshAllAddonAppPrices();
                return;
            }

            $('#dish-application-section').removeClass('d-none');

            selectedApps.forEach(appId => {
                const app = applications.find(a => a.id == appId);
                if (!app) return;

                const index = appIndex++;
                const finalPriceColClass = hasSizes ? 'd-none' : ''; // إخفاء إذا فيه أحجام

                $container.append(`
        <div class="card mb-3 application-card" data-app-id="${appId}" data-index="${index}">
            <div class="card-header bg-primary text-white">
                <strong>${app.name_ar} | ${app.name_en}</strong>
            </div>
            <div class="card-body">
                <input type="hidden" name="menus_integration_dishes[${index}][menus_integration_id]" value="${appId}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">@lang('dishes.IncludeTax')</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input include-tax-radio" type="radio" name="menus_integration_dishes[${index}][is_taxed]" value="1" checked>
                                <label class="form-check-label">@lang('dishes.yes')</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input include-tax-radio" type="radio" name="menus_integration_dishes[${index}][is_taxed]" value="0">
                                <label class="form-check-label">@lang('dishes.no')</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('dishes.IsPercentage')</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input percentage-radio" type="radio" name="menus_integration_dishes[${index}][is_percentage]" value="1" data-index="${index}">
                                <label class="form-check-label">@lang('dishes.yes')</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input percentage-radio" type="radio" name="menus_integration_dishes[${index}][is_percentage]" value="0" checked data-index="${index}">
                                <label class="form-check-label">@lang('dishes.no')</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">@lang('dishes.Percentage')</label>
                        <input type="number" name="menus_integration_dishes[${index}][percentage]" class="form-control percentage-input" min="0" step="0.01" value="0" disabled data-index="${index}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">@lang('dishes.Type')</label>
                        <select name="menus_integration_dishes[${index}][type]" class="form-control percentage-type" disabled data-index="${index}">
                            <option value="increase">@lang('dishes.increase')</option>
                            <option value="decrease">@lang('dishes.decrease')</option>
                        </select>
                    </div>
                    <!-- Final Price Column - مخفي إذا فيه أحجام -->
                    <div class="col-md-2 ${finalPriceColClass}">
                        <label class="form-label">@lang('dishes.FinalPrice')</label>
                        <input type="text" class="form-control final-price-display" value="0">
                        <input type="hidden" name="menus_integration_dishes[${index}][price]" class="final-price-hidden">
                        <input type="hidden" name="menus_integration_dishes[${index}][percentage_amount]" class="percentage-amount-hidden" value="0">
                    </div>
                </div>
            </div>
        </div>
        `);
            });

            calculateMainFinalPrices();
            attachMainAppEvents();
        }

        function attachMainAppEvents() {
            $(document).off('change', '.percentage-radio').on('change', '.percentage-radio', function() {
                const $card = $(this).closest('.application-card');
                const enabled = $(this).val() === '1';
                $card.find('.percentage-input, .percentage-type').prop('disabled', !enabled);
                calculateMainFinalPrices();
            });

            $(document).off('input change', '.percentage-input, .percentage-type').on('input change', '.percentage-input, .percentage-type', function() {
                calculateMainFinalPrices();
            });

            $(document).off('input', '#price').on('input', '#price', function() {
                let val = $(this).val().replace(/[^0-9.]/g, '');
                const parts = val.split('.');
                if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
                $(this).val(val);
                calculateMainFinalPrices();
            });
        }

        function calculateMainFinalPrices() {
            const basePrice = parseFloat($('#price').val()) || 0;

            $('.application-card').each(function() {
                const $card = $(this);
                const isPercentage = $card.find('.percentage-radio:checked').val() === '1';
                const percentage = parseFloat($card.find('.percentage-input').val()) || 0;
                const type = $card.find('.percentage-type').val();

                let finalPrice = basePrice;
                let amount = 0;

                if (isPercentage && percentage > 0) {
                    amount = (basePrice * percentage) / 100;
                    finalPrice = type === 'increase' ? basePrice + amount : Math.max(0, basePrice - amount);
                }

                $card.find('.final-price-display').val(finalPrice.toFixed(2));
                $card.find('.final-price-hidden').val(finalPrice.toFixed(2));
                $card.find('.percentage-amount-hidden').val(amount.toFixed(2));
            });

            setTimeout(refreshAllSizeAppPrices, 50);
            setTimeout(refreshAllAddonAppPrices, 50);
        }

        function refreshAllAddonAppPrices() {
            $('.addon-row').each(function() {
                const $row = $(this);
                const catIdx = $row.data('cat-index');
                const addonIdx = $row.data('addon-index');
                const basePrice = parseFloat($row.find('.addon-base-price').val()) || 0;
                const $container = $row.find('.addon-app-prices-container');

                const selectedApps = $('#applications').val() || [];
                if (selectedApps.length === 0 || basePrice <= 0) {
                    $container.html('<p class="text-muted small text-center">—</p>');
                    return;
                }

                $container.empty();

                selectedApps.forEach(appId => {
                    const app = applications.find(a => a.id == appId);
                    if (!app) return;

                    const $mainCard = $(`.application-card[data-app-id="${appId}"]`);
                    if (!$mainCard.length) return;

                    const isPercentage = $mainCard.find('.percentage-radio:checked').val() === '1';
                    const percentage = parseFloat($mainCard.find('.percentage-input').val()) || 0;
                    const type = $mainCard.find('.percentage-type').val();

                    let finalPrice = basePrice;
                    let amount = 0;

                    if (isPercentage && percentage > 0) {
                        amount = (basePrice * percentage) / 100;
                        finalPrice = type === 'increase' ? basePrice + amount : Math.max(0, basePrice - amount);
                    }

                    const prefix = `addon_categories[${catIdx}][addons][${addonIdx}][menus_integrations][${appId}]`;
                    $container.append(`
                <div class="app-price-item p-1 border-bottom">
                    <div class="d-flex justify-content-between align-items-center small">
                        <div>
                            <strong>${app.name_ar}</strong><br>
                            <small class="text-muted">${app.name_en}</small>
                        </div>
                        <div class="text-end">
                            <input type="text" class="form-control form-control-sm text-end" readonly value="${finalPrice.toFixed(2)}">
                            <input type="hidden" name="${prefix}[menus_integration_id]" value="${appId}">
                            <input type="hidden" name="${prefix}[price]" value="${finalPrice.toFixed(2)}">
                            <input type="hidden" name="${prefix}[is_percentage]" value="${isPercentage ? '1' : '0'}">
                            <input type="hidden" name="${prefix}[percentage]" value="${percentage}">
                            <input type="hidden" name="${prefix}[type]" value="${type}">
                            <input type="hidden" name="${prefix}[percentage_amount]" value="${amount.toFixed(2)}">
                        </div>
                    </div>
                </div>
            `);
                });
            });
        }

        function refreshAllSizeAppPrices() {
            $('.size-row').each(function() {
                const sizeIdx = $(this).data('size-index');
                const basePrice = parseFloat($(this).find('.size-base-price').val()) || 0;
                const $container = $(this).find('.app-prices-container');

                $container.empty();

                const selectedApps = $('#applications').val() || [];
                if (selectedApps.length === 0) {
                    $container.html('<p class="text-muted small text-center">—</p>');
                    return;
                }

                selectedApps.forEach(appId => {
                    const app = applications.find(a => a.id == appId);
                    if (!app) return;

                    const $mainCard = $(`.application-card[data-app-id="${appId}"]`);
                    if (!$mainCard.length) return;

                    const isPercentage = $mainCard.find('.percentage-radio:checked').val() === '1';
                    const percentage = parseFloat($mainCard.find('.percentage-input').val()) || 0;
                    const type = $mainCard.find('.percentage-type').val();

                    let finalPrice = basePrice;
                    let amount = 0;

                    if (isPercentage && percentage > 0) {
                        amount = (basePrice * percentage) / 100;
                        finalPrice = type === 'increase' ? basePrice + amount : Math.max(0, basePrice - amount);
                    }

                    const prefix = `sizes[${sizeIdx}][menus_integrations][${appId}]`;
                    $container.append(`
                        <div class="app-price-item p-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="small">${app.name_ar}</strong>
                                    <small class="text-muted d-block">${app.name_en}</small>
                                </div>
                                <div class="text-end">
                                    <input type="text" class="form-control form-control-sm size-app-price-display text-end" readonly value="${finalPrice.toFixed(2)}">
                                    <input type="hidden" name="${prefix}[menus_integration_id]" value="${appId}">
                                    <input type="hidden" name="${prefix}[price]" value="${finalPrice.toFixed(2)}">
                                    <input type="hidden" name="${prefix}[is_percentage]" value="${isPercentage ? '1' : '0'}">
                                    <input type="hidden" name="${prefix}[percentage]" value="${percentage}">
                                    <input type="hidden" name="${prefix}[type]" value="${type}">
                                    <input type="hidden" name="${prefix}[percentage_amount]" value="${amount.toFixed(2)}">
                                </div>
                            </div>
                        </div>
                    `);
                });
            });
        }

        $('#add-dish-size').on('click', function() {
            const sizeIdx = sizeIndex++;

            $('#dish-sizes-table').append(`
                <tr class="size-row" data-size-index="${sizeIdx}">
                    <td><input type="text" name="sizes[${sizeIdx}][size_name_ar]" class="form-control" required></td>
                    <td><input type="text" name="sizes[${sizeIdx}][size_name_en]" class="form-control" required></td>
                    <td>
                        <input type="number" name="sizes[${sizeIdx}][price]" class="form-control size-base-price" step="0.01" min="0" required data-size-index="${sizeIdx}">
                    </td>
                    <td class="app-prices-cell">
                        <div class="app-prices-container small">
                            <!-- سيتم ملؤه تلقائيًا -->
                        </div>
                    </td>
                    <td>
                        <table class="table table-sm mb-0">
                            <tbody id="size-recipes-${sizeIdx}">
                                <tr>
                                    <td>
                                        <select name="sizes[${sizeIdx}][recipes][0][recipe_id]" class="form-control select2" required>
                                            @foreach ($recipes as $recipe)
                                                <option value="{{ $recipe->id }}">{{ $recipe->name_ar . ' | ' . $recipe->name_en }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="sizes[${sizeIdx}][recipes][0][quantity]" class="form-control" step="0.01" min="0" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-success add-size-recipe mt-1" data-size-index="${sizeIdx}">@lang('dishes.AddRecipe')</button>
                    </td>
                    <td>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="default_size" value="${sizeIdx}" ${sizeIdx === 0 ? 'required' : ''}>
                        </div>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-size-row">@lang('dishes.Remove')</button></td>
                </tr>
            `);

            $('.select2').select2();

            setTimeout(() => {
                calculateMainFinalPrices();
            }, 100);
        });

        $(document).on('input', '.size-base-price', function() {

            setTimeout(refreshAllSizeAppPrices, 50);
        });

        $(document).on('click', '.add-size-recipe', function() {
            const sizeIdx = $(this).data('size-index');
            const $tbody = $(`#size-recipes-${sizeIdx}`);
            const rowCount = $tbody.find('tr').length;

            $tbody.append(`
                <tr>
                    <td>
                        <select name="sizes[${sizeIdx}][recipes][${rowCount}][recipe_id]" class="form-control select2" required>
                            @foreach ($recipes as $recipe)
                                <option value="{{ $recipe->id }}">{{ $recipe->name_ar . ' | ' . $recipe->name_en }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="sizes[${sizeIdx}][recipes][${rowCount}][quantity]" class="form-control" step="0.01" min="0" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                </tr>
            `);
            $tbody.find('.select2').last().select2();
        });

        $('#add-dish-recipe').on('click', function() {
            $('#dish-recipes-table').append(`
                <tr>
                    <td>
                        <select name="details[${recipeIndex}][recipe_id]" class="form-control select2" required>
                            @foreach ($recipes as $recipe)
                                <option value="{{ $recipe->id }}">{{ $recipe->name_ar . ' | ' . $recipe->name_en }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="details[${recipeIndex}][quantity]" class="form-control recepe_quantity" step="0.01" min="0" required>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                </tr>
            `);
            $('#dish-recipes-table .select2').last().select2();
            recipeIndex++;
        });

        $('.add-addon-category').on('click', function() {
            const idx = ++addonCategoryIndex;
            $('#addon-categories-section').append(`
        <div class="addon-category card mt-3 p-3">
            <div class="row gy-3">
                <div class="col-md-4">
                    <label>@lang('dishes.AddonCategory')</label>
                    <select name="addon_categories[${idx}][addon_category_id]" class="form-control select2" required>
                        @foreach ($addonCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name_ar . ' | ' . $category->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>@lang('dishes.MinAddons')</label>
                    <input type="number" name="addon_categories[${idx}][min_addons]" class="form-control" min="0" value="0" required>
                </div>
                <div class="col-md-3">
                    <label>@lang('dishes.MaxAddons')</label>
                    <input type="number" name="addon_categories[${idx}][max_addons]" class="form-control" min="1" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-addon-category">@lang('dishes.Remove')</button>
                </div>
            </div>
            <div class="mt-3">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>@lang('dishes.Addon')</th>
                            <th>@lang('dishes.Quantity')</th>
                            <th>@lang('dishes.Price')</th>
                            <th>@lang('dishes.applications-final-prices')</th>
                            <th>@lang('dishes.Actions')</th>
                        </tr>
                    </thead>
                    <tbody id="addons-table-${idx}"></tbody>
                </table>
                <button type="button" class="btn btn-success btn-sm add-addon" data-category-index="${idx}">@lang('dishes.AddAddon')</button>
            </div>
        </div>
    `);
            $(`#addon-categories-section .select2`).last().select2();
        });

        $(document).on('click', '.add-addon', function() {
            const catIdx = $(this).data('category-index');
            const rowCount = $(`#addons-table-${catIdx} tr`).length;
            const $tbody = $(`#addons-table-${catIdx}`);

            const selectedApps = $('#applications').val() || [];
            let appPricesHtml = '<p class="text-muted small text-center">—</p>';
            if (selectedApps.length > 0) {
                appPricesHtml = '<div class="addon-app-prices-container small"></div>';
            }

            $tbody.append(`
    <tr class="addon-row" data-cat-index="${catIdx}" data-addon-index="${rowCount}">
        <td>
            <select name="addon_categories[${catIdx}][addons][${rowCount}][addon_id]" class="form-control select2" required>
                @foreach ($addons as $addon)
                    <option value="{{ $addon->id }}">{{ $addon->name_ar . ' | ' . $addon->name_en }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="addon_categories[${catIdx}][addons][${rowCount}][quantity]" class="form-control" min="1" value="1" required>
        </td>
        <td>
            <input type="number" name="addon_categories[${catIdx}][addons][${rowCount}][price]"
                   class="form-control addon-base-price" step="0.01" min="0" required
                   data-cat-index="${catIdx}" data-addon-index="${rowCount}">
        </td>
        <td class="addon-app-prices-cell">
            ${appPricesHtml}
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button>
        </td>
    </tr>
`);

            $tbody.find('.select2').last().select2();

            setTimeout(refreshAllAddonAppPrices, 50);
        });

        $(document).on('input', '.addon-base-price', function() {
            setTimeout(refreshAllAddonAppPrices, 50);
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });
        $(document).on('click', '.remove-addon-category', function() {
            $(this).closest('.addon-category').remove();
        });
        $(document).on('click', '.remove-size-row', function() {
            $(this).closest('.size-row').remove();
            refreshAllSizeAppPrices();
        });

        function toggleSections() {
            const hasSizes = $('#has_sizes').val() == '1';
            $('#dish-sizes-section').toggleClass('d-none', !hasSizes);
            $('#price-section').toggleClass('d-none', hasSizes);
            $('#dish-recipes-section').toggleClass('d-none', hasSizes);
            $('#price').prop('required', !hasSizes);
            $('.recepe_quantity').prop('required', !hasSizes);

            updateApplications();
        }
        toggleSections();
        $('#has_sizes').on('change', toggleSections);

        $('#has_addon').on('change', function() {
            $('#dish-addons-section').toggleClass('d-none', $(this).val() != '1');
        }).trigger('change');

        $('#applications').on('change', function() {
            updateApplications();
            refreshAllAddonAppPrices();
        });

        $('.select2').select2();
        updateApplications();

        setTimeout(() => {
            if ($('.application-card').length > 0) {
                calculateMainFinalPrices();
            }
        }, 200);
    });

    $(document).off('input change', '.final-price-display').on('input change', '.final-price-display', function() {
        const $card = $(this).closest('.application-card');
        let val = $(this).val().replace(/[^0-9.]/g, '');
        const parts = val.split('.');
        if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
        $(this).val(val);
        $card.find('.final-price-hidden').val(parseFloat(val || 0).toFixed(2));
    });
</script>
@endsection
