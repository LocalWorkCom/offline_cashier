@extends('layouts.master')

@section('styles')
<!-- SELECT2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
    <h4 class="fw-medium mb-0">@lang('coupon.EditCoupon')</h4>
    <div class="ms-sm-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('coupons.list') }}">
                        @lang('coupon.Coupons')
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <a href="{{ route('coupon.edit', ['id' => $id]) }}">@lang('coupon.EditCoupon')</a>
                </li>
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
                        <div class="card-title">@lang('coupon.EditCoupon')</div>
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
                        <form method="POST" action="{{ route('coupon.update', $coupon->id) }}" class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')
                            <div class="row gy-4">
                                <!-- Coupon Code -->
                                <div class="col-xl-6">
                                    <label for="code" class="form-label">@lang('coupon.Code')</label>
                                    <input type="text" name="code" id="code" class="form-control"
                                        value="{{ old('code', $coupon->code) }}" placeholder="@lang('coupon.Code')" required>
                                    <div class="invalid-feedback">@lang('validation.Entercode')</div>
                                </div>

                                <!-- Discount Value -->
                                <div class="col-xl-6">
                                    <label for="value" class="form-label">@lang('coupon.Value')</label>
                                    <input type="number" step="0.01" name="value" id="value" class="form-control"
                                        value="{{ old('value', $coupon->value) }}" placeholder="@lang('coupon.Value')" required>
                                    <div class="invalid-feedback">@lang('validation.EnterDiscountValue')</div>
                                </div>

                                <!-- Minimum Spend -->
                                <div class="col-xl-6">
                                    <label for="minimum_spend" class="form-label">@lang('coupon.MinimumSpend')</label>
                                    <input type="number" step="0.01" name="minimum_spend" id="minimum_spend" class="form-control"
                                        value="{{ old('minimum_spend', $coupon->minimum_spend) }}" placeholder="@lang('coupon.MinimumSpend')">
                                    <div class="invalid-feedback">@lang('validation.EnterMinimumSpend')</div>
                                </div>

                                <!-- Usage Limit -->
                                <div class="col-xl-6">
                                    <label for="usage_limit" class="form-label">@lang('coupon.UsageLimit')</label>
                                    <input type="number" name="usage_limit" id="usage_limit" class="form-control"
                                        value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="@lang('coupon.UsageLimit')">
                                    <div class="invalid-feedback">@lang('validation.EnterUsageLimit')</div>
                                </div>

                                <!-- Start Date -->
                                <div class="col-xl-6">
                                    <label for="start_date" class="form-label">@lang('coupon.StartDate')</label>
                                    <input type="datetime-local" name="start_date" id="start_date" class="form-control"
                                        value="{{ old('start_date', $coupon->start_date ? \Carbon\Carbon::parse($coupon->start_date)->format('Y-m-d\TH:i') : '') }}" required>
                                    <div class="invalid-feedback">@lang('validation.EnterStartDate')</div>
                                </div>

                                <!-- End Date -->
                                <div class="col-xl-6">
                                    <label for="end_date" class="form-label">@lang('coupon.EndDate')</label>
                                    <input type="datetime-local" name="end_date" id="end_date" class="form-control"
                                        value="{{ old('end_date', $coupon->end_date ? \Carbon\Carbon::parse($coupon->end_date)->format('Y-m-d\TH:i') : '') }}" required>
                                    <div class="invalid-feedback">@lang('validation.EnterEndDate')</div>
                                </div>

                                <!-- Is Active -->
                                <div class="col-xl-6">
                                    <p class="mb-2 text-muted">@lang('coupon.IsActive')</p>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="is_active" value="1" class="form-check-input"
                                            {{ old('is_active', $coupon->is_active) == '1' ? 'checked' : '' }} required>
                                        <label class="form-check-label">@lang('coupon.Active')</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="is_active" value="0" class="form-check-input"
                                            {{ old('is_active', $coupon->is_active) == '0' ? 'checked' : '' }} required>
                                        <label class="form-check-label">@lang('coupon.Inactive')</label>
                                    </div>
                                </div>

                                <!-- Discount Type -->
                                <div class="col-xl-6">
                                    <p class="mb-2 text-muted">@lang('coupon.Type')</p>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="type" value="percentage" class="form-check-input coupon-type"
                                            {{ old('type', $coupon->type) == 'percentage' ? 'checked' : '' }} required>
                                        <label class="form-check-label">@lang('coupon.Percentage')</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="type" value="fixed" class="form-check-input coupon-type"
                                            {{ old('type', $coupon->type) == 'fixed' ? 'checked' : '' }} required>
                                        <label class="form-check-label">@lang('coupon.Fixed')</label>
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <label for="apply_type" class="form-label">@lang('coupon.ApplyType')</label>
                                    <select name="apply_type" id="apply_type" class="form-control" required
                                        {{ old('type', $coupon->type) == 'fixed' ? 'disabled' : '' }}>
                                        <option value="order" {{ old('apply_type', $coupon->apply_type) == 'order' ? 'selected' : '' }}>
                                            @lang('coupon.Order')</option>
                                        <option value="dish" {{ old('apply_type', $coupon->apply_type) == 'dish' && old('type', $coupon->type) != 'fixed' ? 'selected' : '' }}
                                            {{ old('type', $coupon->type) == 'fixed' ? 'disabled' : '' }}>
                                            @lang('coupon.Dish')</option>
                                    </select>
                                    <div class="invalid-feedback">@lang('validation.apply_type')</div>
                                </div>

                                <div class="col-xl-6">
                                    <label for="branches" class="form-label">@lang('coupon.Branches')</label>
                                    <select name="branches[]" id="branches" class="form-control" multiple required
                                        {{ auth('admin')->user()->hasRole('Branch Manager') ? 'disabled' : '' }}>
                                        @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ in_array($branch->id, old('branches', $coupon->branches->pluck('id')->toArray())) || 
                                                           (auth('admin')->user()->hasRole('Branch Manager') && $branch->id == getBranchManagerID()) ? 'selected' : '' }}>
                                            {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">@lang('validation.SelectBranches')</div>
                                </div>

                                <!-- Container for categories and dishes (hidden by default) -->
                                <div id="dish-application-container" style="display: none;">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>@lang('coupon.SelectDishes')</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="categories-container" class="row"></div>
                                            <div id="dishes-container" class="row mt-3"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
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
@php
$couponDishes = $coupon->branches->flatMap(function($branch) {
return is_array($branch->pivot->dish_ids)
? $branch->pivot->dish_ids
: (json_decode($branch->pivot->dish_ids, true) ?? []);
});
@endphp

<script>
    $(document).ready(function() {
        let branchCategoryDishMap = {};
        let prevSelectedBranches = [];
        let couponDishes = @json($couponDishes);

        // Handle coupon type change
        $('.coupon-type').change(function() {
            const selectedType = $(this).val();
            const applyTypeSelect = $('#apply_type');

            if (selectedType === 'fixed') {
                applyTypeSelect.val('order').prop('disabled', true);
                $('#branches').prop('disabled', false).trigger('change');
                $('#dish-application-container').hide();
            } else {
                applyTypeSelect.prop('disabled', false);
                $('#branches').prop('disabled', false).trigger('change');
            }

            $('#categories-container').empty();
            $('#dishes-container').empty();
            branchCategoryDishMap = {};
        });

        // Handle apply type change
        $('#apply_type').on('change', function() {
            if ($(this).val() === 'dish') {
                $('#dish-application-container').show();
                if ($('#branches').val()) {
                    loadCategoriesForBranches();
                }
            } else {
                $('#dish-application-container').hide();
                $('#categories-container').empty();
                $('#dishes-container').empty();
                branchCategoryDishMap = {};
            }
        });

        // Handle branch selection
        $('#branches').on('change', function() {
            if ($('#apply_type').val() === 'dish') {
                let selectedBranches = $(this).val() || [];

                prevSelectedBranches.forEach(branch_id => {
                    if (!selectedBranches.includes(branch_id)) {
                        delete branchCategoryDishMap[branch_id];
                        $(`#branch_${branch_id}_section`).remove();
                    }
                });

                selectedBranches.forEach(branch_id => {
                    if (!branchCategoryDishMap[branch_id]) {
                        const branchName = $(`#branches option[value="${branch_id}"]`).text();
                        branchCategoryDishMap[branch_id] = {
                            name: branchName,
                            categories: {}
                        };

                        $('#dishes-container').append(`
                        <div id="branch_${branch_id}_section" class="branch-section mb-4">
                            <h4 class="branch-header">${branchName}</h4>
                            <div id="branch_${branch_id}_categories" class="row"></div>
                            <div id="branch_${branch_id}_dishes" class="row mt-3"></div>
                        </div>
                    `);

                        loadCategoriesForBranch(branch_id);
                    }
                });

                prevSelectedBranches = selectedBranches;
            }
        });

        // Load categories for a specific branch
        function loadCategoriesForBranch(branch_id) {
            $.ajax({
                url: `/dashboard/coupons/branches/${branch_id}/categories`,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    const $branchCategories = $(`#branch_${branch_id}_categories`);
                    $branchCategories.empty();

                    // Get branch data with selected dishes
                    const branch = @json($coupon->branches->keyBy('id'));
                    const branchDishes = branch[branch_id]?.pivot?.dish_ids ?? [];

                    // Parse selected dishes (handle both array and JSON string)
                    const selectedDishes = (typeof branchDishes === 'string') ?
                        (JSON.parse(branchDishes) || []) :
                        (Array.isArray(branchDishes) ? branchDishes : []);

                    // Get menu categories that contain these dishes
                    $.ajax({
                        url: `/dashboard/coupon/branches/${branch_id}/categories`,
                        type: "GET",
                        dataType: "json",
                        success: function(menuCategories) {
                            data.categories.forEach(cat => {
                                if (!branchCategoryDishMap[branch_id].categories[cat.menu_id]) {
                                    // Find dishes in this category that are selected
                                    const categoryDishes = selectedDishes.filter(dishId => {
                                        return menuCategories.some(mc =>
                                            mc.dish_id === dishId && mc.menu_category_id === cat.menu_id
                                        );
                                    });

                                    const hasSelectedDishes = categoryDishes.length > 0;

                                    $branchCategories.append(`
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <input type="checkbox"
                                                       class="category-checkbox"
                                                       data-id="${cat.menu_id}"
                                                       data-branch="${branch_id}"
                                                       id="cat_${cat.menu_id}_branch_${branch_id}"
                                                       ${hasSelectedDishes ? 'checked' : ''}>
                                                <label for="cat_${cat.menu_id}_branch_${branch_id}">${cat.name}</label>
                                            </div>
                                        </div>
                                    </div>
                                `);

                                    if (hasSelectedDishes) {
                                        loadDishesForCategory(branch_id, cat.menu_id, categoryDishes);
                                    }
                                }
                            });
                        }
                    });
                }
            });
        }

        // Load dishes for a specific category
        function loadDishesForCategory(branch_id, category_id, selectedDishes = []) {
            $.ajax({
                url: `/dashboard/coupons/categories/${category_id}/dishes/${branch_id}`,
                type: "GET",
                dataType: "json",
                success: function(dishes) {
                    const $branchDishes = $(`#branch_${branch_id}_dishes`);
                    $(`#dishes_cat_${category_id}_branch_${branch_id}`).remove();

                    $branchDishes.append(`
                    <div id="dishes_cat_${category_id}_branch_${branch_id}" class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    ${branchCategoryDishMap[branch_id].categories[category_id].name}
                                    <input type="checkbox" class="check-all-dishes"
                                           data-category="${category_id}"
                                           data-branch="${branch_id}"
                                           ${dishes.every(d => selectedDishes.includes(d.id)) ? 'checked' : ''}>
                                    @lang('coupon.SelectAllDishes')
                                </h5>
                            </div>
                            <div class="card-body row">
                                ${dishes.map(dish => `
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input type="checkbox"
                                                   name="branch_dishes[${branch_id}][${category_id}][]"
                                                   value="${dish.id}"
                                                   class="form-check-input dish-checkbox cat_${category_id}_branch_${branch_id}"
                                                   id="dish_${dish.id}_branch_${branch_id}"
                                                   ${selectedDishes.includes(dish.id) ? 'checked' : ''}>
                                            <label class="form-check-label" for="dish_${dish.id}_branch_${branch_id}">
                                                ${dish.name}
                                            </label>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `);
                }
            });
        }

        // Handle category checkbox change
        $(document).on('change', '.category-checkbox', function() {
            let category_id = $(this).data('id');
            let branch_id = $(this).data('branch');

            if ($(this).is(':checked')) {
                loadDishesForCategory(branch_id, category_id);
            } else {
                branchCategoryDishMap[branch_id].categories[category_id].dishes = [];
                $(`#dishes_cat_${category_id}_branch_${branch_id}`).remove();
            }
        });

        // Handle "Select All" for dishes in a category
        $(document).on('change', '.check-all-dishes', function() {
            let category_id = $(this).data('category');
            let branch_id = $(this).data('branch');
            $(`.cat_${category_id}_branch_${branch_id}`).prop('checked', $(this).is(':checked'));
        });

        // Initialize form state
        function initializeFormState() {
            const selectedType = $('input[name="type"]:checked').val();
            const applyType = $('#apply_type').val();

            if (selectedType === 'fixed') {
                $('#apply_type').val('order').prop('disabled', true);
                $('#branches').prop('disabled', false);
                $('#dish-application-container').hide();
            } else {
                $('#apply_type').prop('disabled', false);
                $('#branches').prop('disabled', applyType !== 'dish');

                if (applyType === 'dish') {
                    $('#dish-application-container').show();
                    if ($('#branches').val()) {
                        $('#branches').trigger('change');
                    }
                } else {
                    $('#dish-application-container').hide();
                }
            }
        }

        initializeFormState();
    });
</script>
@endsection