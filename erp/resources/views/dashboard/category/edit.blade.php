@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('category.edit')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('categories.list') }}'">@lang('category.Categories')</a>

                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('category.edit', ['id' => $id]) }}">@lang('category.edit')</a>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid ">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('category.edit')
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
                            <form method="POST" action="{{ route('category.update', $category->id) }}" class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="input-placeholder" class="form-label">@lang('category.ArabicName')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ old('name_ar', $category->name_ar) }}"
                                            placeholder="@lang('category.ArabicName')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicName')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="input-placeholder" class="form-label">@lang('category.EnglishName')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en', $category->name_en) }}"
                                            placeholder="@lang('category.EnglishName')" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishName')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="text-area" class="form-label">@lang('category.description_ar')</label>
                                        <textarea class="form-control" id="description_ar" name="description_ar" rows="4">{{ old('description_ar', $category->description_ar) }}</textarea>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicDesc')
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label for="text-area" class="form-label">@lang('category.description_en')</label>
                                        <textarea class="form-control" id="description_en" name="description_en" rows="4">{{ old('description_en', $category->description_en) }}</textarea>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishDesc')
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label for="input-file" class="form-label">@lang('category.Image')</label>

                                        <!-- Show the current image if it exists -->
                                        @if ($category->image)
                                            <div class="mb-3">
                                                <img src="{{ asset($category->image) }}" alt="Category Image" width="150" height="150">
                                            </div>
                                            <!-- Hidden input to send the current image -->
                                            <input type="hidden" name="image" value="{{ $category->image }}">
                                        @endif

                                        <input class="form-control" type="file" id="image" name="image">
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterImage')
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <p class="mb-2 text-muted">@lang('category.parent')</p>
                                        <select class="form-control" name="parent_id" id="choices-single-default">
                                            <option value="">{{ __('category.none') }}</option>
                                            @foreach($categories as $parentCategory)
                                                <option value="{{ $parentCategory->id }}" 
                                                    {{ old('parent_id', $category->parent_id) == $parentCategory->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'ar' ? $parentCategory->name_ar : $parentCategory->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        
                                        

                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCategory')
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                        <p class="mb-2 text-muted">@lang('category.IsFreeze')</p>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="Radio-md" name="is_freeze" value="1" {{ $category->is_freeze == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="Radio-md">
                                                @lang('category.yes')
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="Radio-md" name="is_freeze" value="0" {{ $category->is_freeze == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="Radio-md">
                                                @lang('category.no')
                                            </label>
                                        </div>
                                    </div>
                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary " id="input-submit"
                                                value="@lang('category.save')">
                                        </div>
                                    </center>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End:: row-1 -->
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection


@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- INETRNAL SELECT2 JS -->
    @vite('resources/assets/js/select2.js')


    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
@endsection
