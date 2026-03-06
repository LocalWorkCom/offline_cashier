@extends('layouts.master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('cuisines.AddCuisine')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.cuisines.index') }}">@lang('cuisines.Cuisines')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('cuisines.AddCuisine')</li>
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
                            <div class="card-title">@lang('cuisines.AddCuisine')</div>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endforeach
                            @endif

                            <form method="POST" action="{{ route('dashboard.cuisines.store') }}" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-xl-6">
                                        <label for="name_ar" class="form-label">@lang('cuisines.NameArabic')</label>
                                        <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="name_en" class="form-label">@lang('cuisines.NameEnglish')</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en') }}" required>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_ar" class="form-label">@lang('cuisines.DescriptionArabic')</label>
                                        <textarea class="form-control" id="description_ar" name="description_ar">{{ old('description_ar') }}</textarea>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_en" class="form-label">@lang('cuisines.DescriptionEnglish')</label>
                                        <textarea class="form-control" id="description_en" name="description_en">{{ old('description_en') }}</textarea>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="image" class="form-label">@lang('cuisines.Image')</label>
                                        <input type="file" class="form-control" id="image" name="image">
                                    </div>

                                    <div class="col-xl-6">
                                        <label class="form-label">@lang('cuisines.IsActive')</label>
                                        <div>
                                            <label class="form-check-label me-3">
                                                <input type="radio" name="is_active" value="1" checked> @lang('cuisines.Active')
                                            </label>
                                            <label class="form-check-label">
                                                <input type="radio" name="is_active" value="0"> @lang('cuisines.Inactive')
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <button type="submit" class="btn btn-primary">@lang('cuisines.Save')</button>
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
        });
    </script>
@endsection
