@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('recipes.RecipeDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.recipes.index') }}">@lang('recipes.Recipes')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('recipes.RecipeDetails')</li>
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
                            <div class="card-title">@lang('recipes.RecipeDetails')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6">
                                    <label class="form-label">@lang('recipes.NameArabic')</label>
                                    <p class="form-text">{{ $recipe->name_ar }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('recipes.NameEnglish')</label>
                                    <p class="form-text">{{ $recipe->name_en }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('recipes.DescriptionArabic')</label>
                                    <p class="form-text">{{ $recipe->description_ar ?? __('recipes.NoDescription') }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('recipes.DescriptionEnglish')</label>
                                    <p class="form-text">{{ $recipe->description_en ?? __('recipes.NoDescription') }}</p>
                                </div>

                                
                                <div class="col-xl-6">
                                    <label class="form-label">@lang('recipes.time')</label>
                                    <p class="form-text">{{ $recipe->time ?? __('recipes.time') }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('recipes.IsActive')</label>
                                    <p class="form-text">{{ $recipe->is_active ? __('recipes.Yes') : __('recipes.No') }}</p>
                                </div>

                                <div class="col-xl-12">
                                    <label class="form-label">@lang('recipes.Images')</label>
                                    @if ($recipe->images && $recipe->images->isNotEmpty())
                                        <div class="row">
                                            @foreach ($recipe->images as $image)
                                                <div class="col-xl-2 col-lg-3 col-md-4">
                                                    <img src="{{ asset($image->image_path) }}" alt="Recipe Image" class="img-fluid rounded">
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="form-text">@lang('recipes.NoImages')</p>
                                    @endif
                                </div>

                                <div class="col-xl-12">
                                    <label class="form-label">@lang('recipes.Ingredients')</label>
                                    @if ($recipe->ingredients && $recipe->ingredients->isNotEmpty())
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('recipes.Product')</th>
                                                    <th>@lang('recipes.Quantity')</th>
                                                    <th>@lang('recipes.LossPercent')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($recipe->ingredients as $ingredient)
                                                    @if($ingredient->product)
                                                        <tr>
                                                            <td>{{ $ingredient->product->name_ar . " | " . $ingredient->product->name_en }}</td>
                                                            <td>{{ $ingredient->quantity }}</td>
                                                            <td>{{ $ingredient->loss_percent }}%</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="form-text">@lang('recipes.NoIngredients')</p>
                                    @endif
                                </div>
                            </div>
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
@endsection
