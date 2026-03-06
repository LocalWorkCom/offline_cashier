@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('slider.EditSlider')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('sliders.list') }}">@lang('slider.Sliders')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('slider.EditSlider')</li>
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
                            <div class="card-title">@lang('slider.EditSlider')</div>
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
                            <form method="POST" action="{{ route('slider.update', $slider->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <!-- Flag (Dish or Offer) -->
                                    <div class="col-xl-12">
                                        <label class="form-label">@lang('slider.Flag')</label>
                                        <div class="d-block">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="flag" value="dish" id="dishOption"
                                                       {{ old('flag', $slider->flag) === 'dish' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="dishOption">@lang('slider.Dish')</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="flag" value="offer" id="offerOption"
                                                       {{ old('flag', $slider->flag) === 'offer' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="offerOption">@lang('slider.Offer')</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="flag" value="discount" id="discountOption"
                                                       {{ old('flag', $slider->flag) === 'discount' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="discountOption">@lang('slider.Discount')</label>
                                            </div>
                                            <div class="invalid-feedback">@lang('validation.EnterFlag')</div>
                                        </div>
                                    </div>

                                    <!-- Dish Selection -->
                                    <div class="col-xl-12 {{ old('flag', $slider->flag) == 'dish' ? '' : 'd-none' }}" id="dishDropdown">
                                        <label for="dish_id" class="form-label">@lang('slider.EnterDish')</label>
                                        <select name="dish_id" id="dish_id" class="form-control select2">
                                            <option value="" disabled>@lang('slider.ChooseDish')</option>
                                            @foreach($dishes as $dish)
                                                @if ($dish && $dish->id != null)
                                                <option value="{{ $dish->id }}"
                                                    {{ old('dish_id', $slider->dish_id) == $dish->id ? 'selected' : '' }}>
                                                    {{ $dish->name_ar . ' | ' . $dish->name_en }}
                                                </option>
                                                @else
                                                    <option value="">{{ __('slider.none')  }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Offer Selection -->
                                    <div class="col-xl-12 {{ old('flag', $slider->flag) === 'offer' ? '' : 'd-none' }}" id="offerDropdown">
                                        <label for="offer_id" class="form-label">@lang('slider.EnterOffer')</label>
                                        <select name="offer_id" id="offer_id" class="form-control select2">
                                            <option value="" disabled>@lang('slider.ChooseOffer')</option>
                                            @foreach($offers as $offer)
                                                @if($offer && $offer->id != null)
                                                <option value="{{ $offer->id }}"
                                                    {{ old('offer_id', $slider->offer_id) == $offer->id ? 'selected' : '' }}>
                                                    {{ $offer->name_ar . ' | ' . $offer->name_en }}
                                                </option>
                                                @else
                                                    <option value="">{{ __('slider.none')  }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Discount Selection -->
                                    <div class="col-xl-12 {{ old('flag', $slider->flag) === 'discount' ? '' : 'd-none' }}" id="discountDropdown">
                                        <label for="discount_id" class="form-label">@lang('slider.EnterDiscount')</label>
                                        <select name="discount_id" id="discount_id" class="form-control select2" {{ old('flag', $slider->flag) === 'discount' ? '' : 'disabled' }}>
                                            <option value="" disabled>@lang('slider.ChooseDiscount')</option>
                                            @foreach($discounts as $discount)
                                                @if ($discount && $discount->dish && $discount->discount)
                                                <option value="{{ $discount->id }}"
                                                    {{ old('discount_id', $slider->discount_id) == $discount->id ? 'selected' : '' }}>
                                                    {{ $discount->dish->name_ar . " | " . $discount->dish->name_en . " | " . number_format($discount->discount->value, 0) . ($discount->discount->type == "percentage" ? "%" : "EGP") }}
                                                </option>
                                                @else
                                                    <option value="">{{ __('slider.none')  }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Name Fields -->
                                    <div class="col-xl-6">
                                        <label for="name_ar" class="form-label">@lang('slider.ArabicName')</label>
                                        <input type="text" name="name_ar" id="name_ar" class="form-control" value="{{ old('name_ar', $slider->name_ar) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="name_en" class="form-label">@lang('slider.EnglishName')</label>
                                        <input type="text" name="name_en" id="name_en" class="form-control" value="{{ old('name_en', $slider->name_en) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                    </div>

                                    <!-- Description Fields -->
                                    <div class="col-xl-6">
                                        <label for="description_ar" class="form-label">@lang('slider.ArabicDescription')</label>
                                        <textarea name="description_ar" id="description_ar" class="form-control" required>{{ old('description_ar', $slider->description_ar) }}</textarea>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicDesc')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="description_en" class="form-label">@lang('slider.EnglishDescription')</label>
                                        <textarea name="description_en" id="description_en" class="form-control" required>{{ old('description_en', $slider->description_en) }}</textarea>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishDesc')</div>
                                    </div>

                                    <!-- Image Upload -->
                                    <div class="col-xl-12">
                                        <label for="image" class="form-label">@lang('slider.Image')</label>
                                        <p class="form-text">
                                            <img src="{{ url($slider->image) }}" alt=""
                                                 width="150" height="150">
                                        </p>
                                        <input type="file" name="image" id="image" class="form-control">
                                        <div class="form-text">@lang('slider.ImageNote')</div>
                                        <div class="invalid-feedback">@lang('validation.EnterImage')</div>
                                    </div>

                                    <!-- Submit Button -->
                                    <center>
                                        <div class="col-xl-4">
                                            <button type="submit" class="btn btn-primary form-control">@lang('category.save')</button>
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

    <script>
        // $(document).ready(function () {
        //     $('.select2').select2();
        //
        //     function toggleDropdowns(flag) {
        //         if (flag === 'dish') {
        //             $('#dishDropdown').removeClass('d-none');
        //             $('#dish_id').prop('disabled', false);
        //             $('#offerDropdown').addClass('d-none');
        //             $('#offer_id').prop('disabled', true);
        //         } else if (flag === 'offer') {
        //             $('#offerDropdown').removeClass('d-none');
        //             $('#offer_id').prop('disabled', false);
        //             $('#dishDropdown').addClass('d-none');
        //             $('#dish_id').prop('disabled', true);
        //         }
        //     }
        //
        //     // Initialize dropdown visibility on page load
        //     toggleDropdowns($('input[name="flag"]:checked').val());
        //
        //     // Change event for radio buttons
        //     $('input[name="flag"]').on('change', function () {
        //         toggleDropdowns($(this).val());
        //     });
        // });

        $(document).ready(function () {
            $('.select2').select2();

            function toggleDropdowns(flag) {
                if (flag === 'dish') {
                    $('#dishDropdown').removeClass('d-none');
                    $('#dish_id').prop('disabled', false);
                    $('#offerDropdown').addClass('d-none');
                    $('#offer_id').prop('disabled', true);
                    $('#discountDropdown').addClass('d-none');
                    $('#discount_id').prop('disabled', true);
                } else if (flag === 'offer') {
                    $('#offerDropdown').removeClass('d-none');
                    $('#offer_id').prop('disabled', false);
                    $('#dishDropdown').addClass('d-none');
                    $('#dish_id').prop('disabled', true);
                    $('#discountDropdown').addClass('d-none');
                    $('#discount_id').prop('disabled', true);
                } else if (flag === 'discount') {
                    $('#discountDropdown').removeClass('d-none');
                    $('#discount_id').prop('disabled', false);
                    $('#dishDropdown').addClass('d-none');
                    $('#dish_id').prop('disabled', true);
                    $('#offerDropdown').addClass('d-none');
                    $('#offer_id').prop('disabled', true);
                }
            }

            // Initialize dropdown visibility based on the current flag
            toggleDropdowns($('input[name="flag"]:checked').val());

            // Change event for radio buttons
            $('input[name="flag"]').on('change', function () {
                toggleDropdowns($(this).val());
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
