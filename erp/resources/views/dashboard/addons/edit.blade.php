@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('addons.EditAddon')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.addons.index') }}">@lang('addons.Addons')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('addons.EditAddon')</li>
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
                            <div class="card-title">@lang('addons.EditAddon')</div>
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

                            <form method="POST" action="{{ route('dashboard.addons.update', $addon->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_ar" class="form-label">@lang('addons.NameArabic')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ old('name_ar', $addon->name_ar) }}" placeholder="@lang('addons.NameArabic')" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('addons.EnterNameArabic')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_en" class="form-label">@lang('addons.NameEnglish')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en', $addon->name_en) }}" placeholder="@lang('addons.NameEnglish')" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('addons.EnterNameEnglish')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_ar" class="form-label">@lang('addons.DescriptionArabic')</label>
                                        <textarea class="form-control" id="description_ar" name="description_ar" rows="4" required>{{ old('description_ar', $addon->description_ar) }}</textarea>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_en" class="form-label">@lang('addons.DescriptionEnglish')</label>
                                        <textarea class="form-control" id="description_en" name="description_en" rows="4" required>{{ old('description_en', $addon->description_en) }}</textarea>
                                    </div>

                                    <!-- <div class="col-xl-6">
                                        <label for="price" class="form-label">@lang('addons.Price')</label>
                                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $addon->price) }}" min="0" step="0.01" required>
                                    </div> -->

                                    <div class="col-xl-6">
                                        <label for="is_active" class="form-label">@lang('addons.IsActive')</label>
                                        <div>
                                            <input type="radio" id="is_active_yes" name="is_active" value="1" {{ $addon->is_active ? 'checked' : '' }}>
                                            <label for="is_active_yes">@lang('addons.Yes')</label>

                                            <input type="radio" id="is_active_no" name="is_active" value="0" {{ !$addon->is_active ? 'checked' : '' }}>
                                            <label for="is_active_no">@lang('addons.No')</label>
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <label for="ingredients" class="form-label">@lang('addons.Ingredients')</label>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('addons.Product')</th>
                                                    <th>@lang('addons.Quantity')</th>
                                                    <th>@lang('addons.LossPercent')</th>
                                                    <th>@lang('addons.Actions')</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ingredients-table">
                                                @foreach ($addon->ingredients as $index => $ingredient)
                                                    <tr>
                                                        <td>
                                                            <select name="ingredients[{{ $index }}][product_brand_id]" class="form-control select2" required>
                                                                @foreach ($products as $one_product)
                                                                    <option value="{{ $one_product->id }}" {{ $one_product->id == $ingredient->product_brand_id ? 'selected' : '' }}>{{ $one_product->product->name_ar. " | ".$one_product->product->name_en }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="ingredients[{{ $index }}][quantity]" class="form-control" value="{{ $ingredient->quantity }}" min="0" step="0.01" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="ingredients[{{ $index }}][loss_percent]" class="form-control" value="{{ $ingredient->loss_percent }}" min="0" max="100" step="0.01">
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-danger btn-sm remove-ingredient">@lang('addons.Remove')</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <button type="button" id="add-ingredient" class="btn btn-success btn-sm">@lang('addons.AddIngredient')</button>
                                    </div>

                                    <div class="col-xl-12">
                                        <label for="images" class="form-label">@lang('addons.Images')</label>
                                        @if ($addon->images && $addon->images->isNotEmpty())
                                            @foreach ($addon->images as $image)
                                                <div class="mb-3">
                                                    <img src="{{ asset($image->image_path) }}" alt="Addon Image" width="150" height="150">
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="form-text">@lang('addons.NoImages')</p>
                                        @endif
                                        <input class="form-control" type="file" id="images" name="images[]" multiple>
                                    </div>

                                    <div class="col-xl-12">
                                        <input type="submit" class="btn btn-primary" value="@lang('addons.Save')">
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
        $(document).ready(function () {
            $('.select2').select2();
            let ingredientIndex = {{ $addon->ingredients->count() }};

            $('#add-ingredient').on('click', function () {
                $('#ingredients-table').append(`
                    <tr>
                        <td>
                            <select name="ingredients[${ingredientIndex}][product_brand_id]" class="form-control select2" required>
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
                            <button type="button" class="btn btn-danger btn-sm remove-ingredient">@lang('addons.Remove')</button>
                        </td>
                    </tr>
                `);
                ingredientIndex++;
                $('.select2').select2();
            });

            $(document).on('click', '.remove-ingredient', function () {
                $(this).closest('tr').remove();
            });
        });
    </script>
@endsection
