@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('offer.AddOffer')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('offers.list')}}">@lang('offer.Offers')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('offer.AddOffer')</li>
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
                                @lang('offer.AddOffer')
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
                            <form method="POST" action="{{ route('offer.store') }}" class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label class="form-label">@lang('offer.Active')</label>
                                        <div class="d-flex">
                                            <div class="form-check me-4">
                                                <input class="form-check-input" type="radio" name="is_active" value="1" checked>
                                                <label class="form-check-label">@lang('category.yes')</label>
                                            </div>
                                            <div class="form-check me-4">
                                                <input class="form-check-input" type="radio" name="is_active" value="0">
                                                <label class="form-check-label">@lang('category.no')</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label class="form-label">@lang('offer.BranchSelection')</label>
                                        <div class="d-flex">
                                            <div class="form-check me-4">
                                                <input class="form-check-input" type="radio" name="branch_selection" id="allBranches" value="all" checked>
                                                <label class="form-check-label" for="allBranches">@lang('offer.AllBranches')</label>
                                            </div>
                                            <div class="form-check me-4">
                                                <input class="form-check-input" type="radio" name="branch_selection" id="specificBranches" value="specific">
                                                <label class="form-check-label" for="specificBranches">@lang('offer.SpecificBranches')</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Checklist for Specific Branches -->
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mt-3" id="branchChecklist" style="display: none;">
                                        <label class="form-label">@lang('offer.SelectBranches')</label>
                                        <div class="form-check">
                                            @foreach($branches as $branch)
                                                <div>
                                                    <input class="form-check-input" type="checkbox" name="branches[]" id="branch_{{ $branch->id }}" value="{{ $branch->id }}">
                                                    <label class="form-check-label" for="branch_{{ $branch->id }}">
                                                        {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('offer.DiscountType')</label>
                                        <div class="d-flex">
                                            <div class="form-check me-4">
                                                <input class="form-check-input" type="radio" name="discount_type" value="fixed" checked>
                                                <label class="form-check-label">@lang('offer.fixed')</label>
                                            </div>
                                            <div class="form-check me-4">
                                                <input class="form-check-input" type="radio" name="discount_type" value="percentage">
                                                <label class="form-check-label">@lang('offer.percentage')</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('offer.DiscountValue')</label>
                                        <input type="number" class="form-control" name="discount_value" value="{{ old('discount_value') }}"
                                               placeholder="@lang('offer.DiscountValue')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterDiscountValue')
                                        </div>
                                    </div>
                                    <!-- Arabic Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('offer.ArabicName')</label>
                                        <input type="text" class="form-control" name="name_ar" value="{{ old('name_ar') }}"
                                               placeholder="@lang('offer.ArabicName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterArabicName')
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('offer.EnglishName')</label>
                                        <input type="text" class="form-control" name="name_en" value="{{ old('name_en') }}"
                                               placeholder="@lang('offer.EnglishName')" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterEnglishName')
                                        </div>
                                    </div>

                                    <!-- Description Arabic -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('offer.ArabicDescription')</label>
                                        <textarea class="form-control" name="description_ar" rows="2" required>{{ old('description_ar') }}</textarea>
                                    </div>

                                    <!-- Description English -->
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('offer.EnglishDescription')</label>
                                        <textarea class="form-control" name="description_en" rows="2" required>{{ old('description_en') }}</textarea>
                                    </div>

{{--                                    <!-- Country -->--}}
{{--                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">--}}
{{--                                        <label class="form-label">@lang('offer.Country')</label>--}}
{{--                                        <select class="form-control select2" name="country_id" required>--}}
{{--                                            <option value="" disabled selected>@lang('offer.SelectCountry')</option>--}}
{{--                                            @foreach($countries as $country)--}}
{{--                                                <option value="{{ $country->id }}">{{ $country->name_ar." | ". $country->name_en }}</option>--}}
{{--                                            @endforeach--}}
{{--                                        </select>--}}
{{--                                        <div class="invalid-feedback">--}}
{{--                                            @lang('validation.EnterCountry')--}}
{{--                                        </div>--}}
{{--                                    </div>--}}

                                    <!-- Opening and Closing Hours -->
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">@lang('offer.StartDate')</label>
                                        <input type="date" class="form-control" name="start_date" value="{{ old('start_date') }}" required>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">@lang('offer.EndDate')</label>
                                        <input type="date" class="form-control" name="end_date" value="{{ old('start_date') }}" required>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label for="input-file" class="form-label">@lang('offer.ImageAr')</label>
                                        <input class="form-control" type="file" id="image" name="image_ar" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterImage')
                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label for="input-file" class="form-label">@lang('offer.ImageEn')</label>
                                        <input class="form-control" type="file" id="image" name="image_en" required>
                                        <div class="valid-feedback">
                                            @lang('validation.Correct')
                                        </div>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterImage')
                                        </div>
                                    </div>

                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary " id="input-submit"
                                                   value="@lang('category.save')">
                                        </div>
                                    </center>


                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->
        </div>
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
    <!-- SELECT2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();
        });
         document.addEventListener('DOMContentLoaded', function () {
            const allBranchesRadio = document.getElementById('allBranches');
            const specificBranchesRadio = document.getElementById('specificBranches');
            const branchChecklist = document.getElementById('branchChecklist');

            allBranchesRadio.addEventListener('change', function () {
            if (this.checked) {
            branchChecklist.style.display = 'none';
        }
        });

            specificBranchesRadio.addEventListener('change', function () {
            if (this.checked) {
            branchChecklist.style.display = 'block';
        }
        });
        });

         //prevent spaces
        document.addEventListener('DOMContentLoaded', function () {
            // Select all input and textarea elements where spaces should not be allowed at the start
            const inputs = document.querySelectorAll('input[type="text"], textarea');

            // Add event listeners for each input/textarea
            inputs.forEach(input => {
                // On input, trim leading spaces
                input.addEventListener('input', function () {
                    if (this.value.startsWith(' ')) {
                        this.value = this.value.trimStart();
                    }
                });

                // Prevent spaces as the first character on keydown
                input.addEventListener('keydown', function (e) {
                    if (e.key === ' ' && this.selectionStart === 0) {
                        e.preventDefault(); // Block the space
                    }
                });
            });
        });
    </script>
@endsection
