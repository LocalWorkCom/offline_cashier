@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('slider.AddSlider')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('sliders.list') }}">@lang('slider.Sliders')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('slider.AddSlider')</li>
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
                            <div class="card-title">@lang('slider.AddSlider')</div>
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
                            <form method="POST" action="{{ route('slider.store') }}" class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <!-- Radio Buttons -->
                                    <div class="col-xl-12">
                                        <label class="form-label">@lang('slider.Flag')</label>
                                        <div class="d-block">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="flag" id="dishOption" value="dish" required>
                                                <label class="form-check-label" for="dishOption">@lang('slider.Dish')</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="flag" id="offerOption" value="offer" required>
                                                <label class="form-check-label" for="offerOption">@lang('slider.Offer')</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="flag" id="discountOption" value="discount" required>
                                                <label class="form-check-label" for="discountOption">@lang('slider.Discount')</label>
                                            </div>
                                            <div class="invalid-feedback">@lang('validation.EnterFlag')</div>
                                        </div>
                                    </div>

                                    <!-- Dish Dropdown -->
                                    <div class="col-xl-12 d-none" id="dishDropdown">
                                        <label for="dish_id" class="form-label">@lang('slider.EnterDish')</label>
                                        <select name="dish_id" id="dish_id" class="form-control select2">
                                            <option value="" selected disabled>@lang('slider.ChooseDish')</option>
                                            @foreach($dishes as $dish)
                                               @if ($dish && $dish->id != null)
                                                    <option value="{{ $dish->id }}">{{ $dish->name_ar . " | " . $dish->name_en  }}</option>
                                                @else
                                                    <option value="">{{ __('slider.none')  }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('slider.EnterDish')</div>
                                    </div>

                                    <!-- Offer Dropdown -->
                                    <div class="col-xl-12 d-none" id="offerDropdown">
                                        <label for="offer_id" class="form-label">@lang('slider.EnterOffer')</label>
                                        <select name="offer_id" id="offer_id" class="form-control select2">
                                            <option value="" selected disabled>@lang('slider.ChooseOffer')</option>
                                            @foreach($offers as $offer)
                                                @if($offer && $offer->id != null)
                                                <option value="{{ $offer->id }}">{{ $offer->name_ar . " | " . $offer->name_en  }}</option>
                                                @else
                                                    <option value="">{{ __('slider.none')  }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('slider.EnterOffer')</div>
                                    </div>

                                    <!-- Discount Dropdown -->
                                    <div class="col-xl-12 d-none" id="discountDropdown">
                                        <label for="discount_id" class="form-label">@lang('slider.EnterDiscount')</label>
                                        <select name="discount_id" id="discount_id" class="form-control select2">
                                            <option value="" selected disabled>@lang('slider.ChooseDiscount')</option>
                                            @foreach($discounts as $discount)
                                                @if ($discount && $discount->dish && $discount->discount)
                                                    <option value="{{ $discount->id }}">
                                                        {{ $discount->dish->name_ar . " | " . $discount->dish->name_en . " | " .
                                                        number_format($discount->discount->value, 0) .
                                                        ($discount->discount->type == "percentage" ? "%" : "EGP") }}
                                                    </option>
                                                @else
                                                    <option value="">{{ __('slider.none') }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('slider.EnterDiscount')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="code" class="form-label">@lang('slider.ArabicName')</label>
                                        <input type="text" name="name_ar" id="code" class="form-control" placeholder="@lang('slider.ArabicName')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="value" class="form-label">@lang('slider.EnglishName')</label>
                                        <input type="text" name="name_en" id="value" class="form-control" placeholder="@lang('slider.EnglishName')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="code" class="form-label">@lang('slider.ArabicDescription')</label>
                                        <textarea type="text" name="description_ar" id="code" class="form-control" placeholder="@lang('slider.ArabicDescription')" required></textarea>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicDesc')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="value" class="form-label">@lang('slider.EnglishDescription')</label>
                                            <textarea type="text" name="description_en" id="value" class="form-control" placeholder="@lang('slider.EnglishDescription')" required></textarea>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishDesc')</div>
                                    </div>

                                    <div class="col-xl-12">
                                        <label for="image" class="form-label">@lang('slider.Image')</label>
                                        <input type="file" name="image" id="image" class="form-control" placeholder="@lang('slider.Image')" required>
                                        <div class="form-text">@lang('slider.ImageNote')</div>
                                        <div class="invalid-feedback">@lang('validation.EnterImage')</div>
                                    </div>

                                </div>

                                    <!-- Submit Button -->
                                    <center>
                                        <div class="col-xl-4 mt-3">
                                            <button type="submit" class="btn btn-primary form-control">@lang('category.save')</button>
                                        </div>
                                    </center>
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

    <script>
        //     $(document).ready(function () {
        //     $('.select2').select2();
        //
        //     // Toggle Dish/Offer dropdown
        //     $('input[name="flag"]').change(function () {
        //         const selected = $(this).val();
        //         if (selected === 'dish') {
        //             $('#dishDropdown').removeClass('d-none').find('select').prop('required', true).val('').trigger('change');
        //             $('#offerDropdown').addClass('d-none').find('select').prop('required', false).val('').trigger('change');
        //         } else if (selected === 'offer') {
        //             $('#offerDropdown').removeClass('d-none').find('select').prop('required', true).val('').trigger('change');
        //             $('#dishDropdown').addClass('d-none').find('select').prop('required', false).val('').trigger('change');
        //         }
        //     });
        //
        //     // Toggle New/Existing Names and Descriptions
        //     $('input[name="name_option"]').change(function () {
        //     if ($(this).val() === 'new') {
        //     $('#newNameInputs').removeClass('d-none');
        // } else {
        //     $('#newNameInputs').addClass('d-none');
        // }
        // });
        //
        //     // Toggle New/Existing Image
        //     $('input[name="image_option"]').change(function () {
        //     if ($(this).val() === 'new') {
        //     $('#newImageInput').removeClass('d-none');
        //     $('#existingImageDropdown').addClass('d-none');
        // } else {
        //     $('#existingImageDropdown').removeClass('d-none');
        //     $('#newImageInput').addClass('d-none');
        // }
        // });
        // });

        $(document).ready(function () {
            $('.select2').select2();

            // Toggle Dish/Offer/Discount dropdown
            $('input[name="flag"]').change(function () {
                const selected = $(this).val();
                $('#dishDropdown, #offerDropdown, #discountDropdown').addClass('d-none').find('select').prop('required', false).val('').trigger('change');

                if (selected === 'dish') {
                    $('#dishDropdown').removeClass('d-none').find('select').prop('required', true);
                } else if (selected === 'offer') {
                    $('#offerDropdown').removeClass('d-none').find('select').prop('required', true);
                } else if (selected === 'discount') {
                    $('#discountDropdown').removeClass('d-none').find('select').prop('required', true);
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



