@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('dishes.EditCategory')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.dish-categories.index') }}">@lang('dishes.DishCategories')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('dishes.EditCategory')</li>
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
                            <div class="card-title">
                                @lang('dishes.EditCategory')
                            </div>
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
                            <form method="POST" action="{{ route('dashboard.dish-categories.update', $category->id) }}" class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_ar" class="form-label">@lang('dishes.NameArabic')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ old('name_ar', $category->name_ar) }}" placeholder="@lang('dishes.NameArabic')" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="name_en" class="form-label">@lang('dishes.NameEnglish')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en', $category->name_en) }}" placeholder="@lang('dishes.NameEnglish')" required>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="description_ar" class="form-label">@lang('dishes.DescriptionArabic')</label>
                                        <textarea class="form-control" id="description_ar" name="description_ar" rows="4" required>{{ old('description_ar', $category->description_ar) }}</textarea>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicDesc')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="description_en" class="form-label">@lang('dishes.DescriptionEnglish')</label>
                                        <textarea class="form-control" id="description_en" name="description_en" rows="4" required>{{ old('description_en', $category->description_en) }}</textarea>
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishDesc')</div>
                                    </div>

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label for="image" class="form-label">@lang('dishes.Image')</label>
                                        @if ($category->image_path)
                                            <div class="mb-3">
                                                <img src="{{ asset($category->image_path) }}" alt="Current Image" width="150" height="150">
                                            </div>
                                        @endif
                                        <input class="form-control" type="file" id="image" name="image_path">
                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label for="parent_id" class="form-label">@lang('dishes.ParentCategory')</label>
                                        <select class="form-control select2" name="parent_id" id="parent_id">
                                            <option value="">{{ __('dishes.None') }}</option>
                                            @foreach($categories as $parent)
                                                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                                    {{ $parent->name_ar . ' | ' . $parent->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                        <p class="mb-2 text-muted">@lang('dishes.IsActive')</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="is_active_yes" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active_yes">@lang('dishes.Yes')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="is_active_no" name="is_active" value="0" {{ !$category->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active_no">@lang('dishes.No')</label>
                                        </div>
                                    </div>

                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary" value="@lang('dishes.Save')">
                                        </div>
                                    </center>
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
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- INTERNAL SELECT2 JS -->
    @vite('resources/assets/js/select2.js')

    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
@endsection
