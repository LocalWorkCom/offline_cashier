@extends('layouts.master')

@section('styles')
    <!-- CUSTOM STYLES IF NEEDED -->
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('cuisines.CuisineDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.cuisines.index') }}">@lang('cuisines.Cuisines')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('cuisines.CuisineDetails')</li>
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
                            <div class="card-title">@lang('cuisines.CuisineDetails')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6">
                                    <label class="form-label">@lang('cuisines.NameArabic')</label>
                                    <p class="form-text">{{ $cuisine->name_ar }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('cuisines.NameEnglish')</label>
                                    <p class="form-text">{{ $cuisine->name_en }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('cuisines.DescriptionArabic')</label>
                                    <p class="form-text">{{ $cuisine->description_ar ?? __('cuisines.NoDescription') }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('cuisines.DescriptionEnglish')</label>
                                    <p class="form-text">{{ $cuisine->description_en ?? __('cuisines.NoDescription') }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('cuisines.IsActive')</label>
                                    <p class="form-text">{{ $cuisine->is_active ? __('cuisines.Active') : __('cuisines.Inactive') }}</p>
                                </div>

                                <div class="col-xl-6">
                                    <label class="form-label">@lang('cuisines.Image')</label>
                                    @if ($cuisine->image_path)
                               
                                        <img src="{{ asset($cuisine->image_path) }}" alt="@lang('cuisines.NoImage')" class="img-fluid rounded" style="max-width: 150px;">
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
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection