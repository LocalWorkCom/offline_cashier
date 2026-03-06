@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('addons.AddonDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.addons.index') }}">@lang('addons.Addons')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('addons.AddonDetails')</li>
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
                            <div class="card-title">@lang('addons.AddonDetails')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6">
                                    <label class="form-label">@lang('addons.NameArabic')</label>
                                    <p class="form-text">{{ $addon->name_ar }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('addons.NameEnglish')</label>
                                    <p class="form-text">{{ $addon->name_en }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('addons.DescriptionArabic')</label>
                                    <p class="form-text">{{ $addon->description_ar ?? __('addons.NoDescription') }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('addons.DescriptionEnglish')</label>
                                    <p class="form-text">{{ $addon->description_en ?? __('addons.NoDescription') }}</p>
                                </div>

                                <!-- <div class="col-xl-6">
                                    <label class="form-label">@lang('addons.Price')</label>
                                    <p class="form-text">{{ $addon->price }}</p>
                                </div> -->

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('addons.IsActive')</label>
                                    <p class="form-text">{{ $addon->is_active ? __('addons.Yes') : __('addons.No') }}</p>
                                </div>

                                <div class="col-xl-12">
                                    <label class="form-label">@lang('addons.Images')</label>
                                    @if ($addon->images && $addon->images->isNotEmpty())
                                        <div class="row">
                                            @foreach ($addon->images as $image)
                                                <div class="col-xl-2 col-lg-3 col-md-4">
                                                    <img src="{{ asset($image->image_path) }}" alt="Addon Image" class="img-fluid rounded">
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="form-text">@lang('addons.NoImages')</p>
                                    @endif
                                </div>

                                <div class="col-xl-12">
                                    <label class="form-label">@lang('addons.Ingredients')</label>
                                    @if ($addon->ingredients && $addon->ingredients->isNotEmpty())
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('addons.Product')</th>
                                                    <th>@lang('addons.Quantity')</th>
                                                    <th>@lang('addons.LossPercent')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($addon->ingredients as $ingredient)
                                                    <tr>
                                                        <td>{{ $ingredient->product->name_ar. " | ".$ingredient->product->name_en }}</td>
                                                        <td>{{ $ingredient->quantity }}</td>
                                                        <td>{{ $ingredient->loss_percent }}%</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="form-text">@lang('addons.NoIngredients')</p>
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
