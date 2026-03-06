@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('discount.AddDiscount')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('discounts.list') }}'">
                            @lang('discount.Discounts')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="{{ route('discount.create') }}">@lang('discount.AddDiscount')</a>
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
                            <div class="card-title">@lang('discount.AddDiscount')</div>
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
                            <form method="POST" action="{{ route('discount.store') }}" class="needs-validation" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <!-- Discount name_ar -->
                                    <div class="col-xl-6">
                                        <label for="name_ar" class="form-label">@lang('discount.NameAr')</label>
                                        <input type="text" name="name_ar" id="name_ar" class="form-control"
                                            placeholder="@lang('discount.NameAr')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterNameAr')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="name_er" class="form-label">@lang('discount.NameEn')</label>
                                        <input type="text" name="name_en" id="name_en" class="form-control"
                                            placeholder="@lang('discount.NameEn')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterNameEn')</div>
                                    </div>

                                    <!-- Discount Value -->
                                    <div class="col-xl-6">
                                        <label for="value" class="form-label">@lang('discount.Value')</label>
                                        <input type="number" name="value" id="value" class="form-control"
                                            placeholder="@lang('discount.Value')" min="0" required>
                                        <div class="invalid-feedback">@lang('validation.EnterDiscountValue')</div>
                                    </div>


                                    <!-- Start Date -->
                                    <div class="col-xl-6">
                                        <label for="start_date" class="form-label">@lang('discount.StartDate')</label>
                                        <input type="datetime-local" name="start_date" id="start_date" class="form-control"
                                            required>
                                        <div class="invalid-feedback">@lang('validation.EnterStartDate')</div>
                                    </div>

                                    <!-- End Date -->
                                    <div class="col-xl-6">
                                        <label for="end_date" class="form-label">@lang('discount.EndDate')</label>
                                        <input type="datetime-local" name="end_date" id="end_date" class="form-control"
                                            required>
                                        <div class="invalid-feedback">@lang('validation.EnterEndDate')</div>
                                    </div>

                                    <!-- Discount Type -->
                                    <div class="col-xl-6">
                                        <p class="mb-2 text-muted">@lang('discount.Type')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="type" value="percentage" class="form-check-input"
                                                checked required>
                                            <label class="form-check-label">@lang('discount.Percentage')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="type" value="fixed" class="form-check-input"
                                                required>
                                            <label class="form-check-label">@lang('discount.Fixed')</label>
                                        </div>
                                    </div>

                                    <!-- Is Active -->
                                    <div class="col-xl-6">
                                        <p class="mb-2 text-muted">@lang('discount.IsActive')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="is_active" value="1" class="form-check-input"
                                                checked required>
                                            <label class="form-check-label">@lang('discount.Active')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="is_active" value="0" class="form-check-input"
                                                required>
                                            <label class="form-check-label">@lang('discount.Inactive')</label>
                                        </div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="branches" class="form-label">@lang('discount.Branches')</label>
                                        <select name="branches[]" id="branches" class="form-control" multiple required>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.SelectBranches')</div>
                                    </div>


                                    <!-- Submit Button -->
                                    <center>
                                        <div class="col-xl-4">
                                            <button type="submit"
                                                class="btn btn-primary form-control">@lang('category.save')</button>
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
@endsection
