@extends('website.layouts.master')
<style>
    .favorite-icon {
        cursor: pointer;
        color: red;
        font-size: 24px;
        margin-bottom:30px;
    }

    .favorite-icon:hover {
        opacity: 0.8;
    }
</style>
@section('content')
    <section class="intro intro-desktop">
        <div class="container py-sm-5 py-4">
            <div class="py-5 owl-slider owl-carousel owl-theme">
                @if ($sliders->isEmpty())
                    <div class="item">
                        <div class="row m-0 justify-content-center align-items-center">
                            <div class="col-md-6">
                                <h1 class="slide-title">
                                    {{ app()->getLocale() == 'en' ? 'Enjoy your best meal' : 'استمتع بتجربة رائعة لدينا' }}
                                </h1>
                                <p class="slide-text my-5">
                                    {{ app()->getLocale() == 'en' ? 'Order now the best types of food and the most famous dishes through our website' : 'يمكنك طلب افضل انواع المأكولات واشهر الاطباق من خلال موقعنا واستمتع بتجربة مميزه لك' }}
                                </p>
                                <a href="{{ route('menu') }}"
                                    class="btn">{{ app()->getLocale() == 'en' ? 'Order now' : 'اطلب الان' }}</a>
                            </div>
                            <div class="col-md-6">
                                <figure class="intro-img">
                                    <img src="{{ 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png' }}"
                                        alt="">
                                </figure>
                            </div>
                        </div>
                    </div>
                @endif
                @foreach ($sliders as $slider)
                    <div class="item">
                        <div class="row m-0 justify-content-center align-items-center">
                            <div class="col-md-6">
                                <h1 class="slide-title">
                                    {{ app()->getLocale() == 'ar' ? $slider->name_ar : $slider->name_en }}</h1>
                                <p class="slide-text my-5">
                                    {{ app()->getLocale() == 'ar' ? $slider->description_ar : $slider->description_en }}
                                </p>
                                <a href="{{ route('menu') }}"
                                    class="btn">{{ app()->getLocale() == 'ar' ? 'اطلب الان' : 'Order now' }}</a>
                            </div>
                            <div class="col-md-6">
                                <figure class="intro-img">
                                    <img src="{{ asset($slider->image ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                        alt="">
                                </figure>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="container overflow-plates">
            <div class="d-flex justify-content-between">
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/overflow-left.png') }}"
                    class="small-img right" data-aos="zoom-in" />
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/overflow-plate.png') }}" class="big-img"
                    data-aos="zoom-in" />
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/overflow-right.png') }}"
                    class="small-img left" data-aos="zoom-in" />
            </div>
        </div>
    </section>



    <section class="intro intro-mobile ">
        <div class="container py-sm-5 py-4">
            <div class="py-5 owl-slider owl-carousel owl-theme">
                @if ($sliders->isEmpty())
                    <div class="item">
                        <div class="row m-0 justify-content-center align-items-center">
                            <div class="col-md-6">
                                  <figure class="intro-img">
                                    <img src="{{ 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png' }}"
                                        alt="">
                                </figure>
                            </div>

                            <div class="col-md-6 text-center">
                                <h3 class="slide-title">
                                    {{ app()->getLocale() == 'en' ? 'Enjoy your best meal' : 'استمتع بتجربة رائعة لدينا' }}
                                </h3>
                                <p class="slide-text my-1">
                                    <small>
                                        {{ app()->getLocale() == 'en' ? 'Order now the best types of food and the most famous dishes through our website' : 'يمكنك طلب افضل انواع المأكولات واشهر الاطباق من خلال موقعنا واستمتع بتجربة مميزه لك' }}</small>
                                </p>
                                <a href="{{ route('menu') }}"
                                    class="btn">{{ app()->getLocale() == 'en' ? 'Order now' : 'اطلب الان' }}</a>
                            </div>

                        </div>
                    </div>
                @endif
                @foreach ($sliders as $slider)
                    <div class="item">
                        <div class="row m-0 justify-content-center align-items-center">
                            <div class="col-md-6">
                                <h1 class="slide-title">
                                    {{ app()->getLocale() == 'ar' ? $slider->name_ar : $slider->name_en }}</h1>
                                <p class="slide-text my-5">
                                    {{ app()->getLocale() == 'ar' ? $slider->description_ar : $slider->description_en }}
                                </p>
                                <a href="{{ route('menu') }}"
                                    class="btn">{{ app()->getLocale() == 'ar' ? 'اطلب الان' : 'Order now' }}</a>
                            </div>
                            <div class="col-md-6">
                                <figure class="intro-img">
                                    <img src="{{ asset($slider->image ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                        alt="">
                                </figure>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </section>
    @if ($menuCategories)
        <section class="categories pt-5">
            <div class="container px-0 py-sm-5 py-4">
                <div class="section-titles px-3 d-flex justify-content-between" data-aos="fade-down">
                    <h2 class="fw-bold">@lang('header.menuopen')</h2>
                    <div class="section-titles px-3 d-flex justify-content-end mb-2" data-aos="fade-down">
                        <a href="{{ route('menu') }}" class="section-btn text-decoration-none">
                            <span class="ms-2">@lang('header.viewall')</span>
                            <span class="icon"><i class="fas fa-arrow-left"></i></span>
                        </a>
                    </div>
                </div>
                <div class="categories-slider owl-carousel owl-theme">
                    @foreach ($menuCategories as $category)
                        @if ($category->dish_categories->is_active)
                            <div class="item mb-4 category position-relative" data-aos="zoom-in">
                                <a href="{{ route('menu', ['category_id' => $category->dish_categories->id]) }}">
                                    <figure class="category-img m-0">
                                        <img src="{{ asset($category->dish_categories->image_path ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                            alt="{{ app()->getLocale() == 'en' ? $category->dish_categories->name_en : $category->dish_categories->name_ar }}">
                                        <figcaption class="pt-4">
                                            <h5>{{ app()->getLocale() == 'en' ? $category->dish_categories->name_en : $category->dish_categories->name_ar }}
                                            </h5>
                                        </figcaption>
                                    </figure>
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif


    @if ($discounts)
        <section class="offers">
            <div class="container py-sm-5 py-4">
                <div class="section-titles  px-3 d-flex justify-content-end mb-2" data-aos="fade-down">
                    <a href="{{ route('menu', ['category_id' => 'discounts']) }}" class="section-btn text-decoration-none">
                        <span class="ms-2">@lang('header.viewall')</span>
                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    </a>
                </div>
                <div class="offers-slider owl-carousel owl-theme">
                    @if ($discounts->isNotEmpty())
                        @foreach ($discounts as $discount)
                            <div class="item mb-4 category position-relative" data-aos="zoom-in">
                                <div class="item three row mx-0 p-4" data-aos="zoom-in">
                                    <div class="col-md-5">
                                        <img class="offer-img"
                                            src="{{ asset($discount->dish->image ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                            alt="{{ app()->getLocale() == 'en' ? $discount->dish->name_en : $discount->dish->name_ar }}">
                                    </div>
                                    <div class="col-md-7">
                                        <h2 class="main-color fw-bold">
                                            @lang('header.discount')
                                            {{ $discount->discount->type == 'percentage' ? '%' : $discount->currency_symbol }}
                                            {{ (int) $discount->discount->value }}
                                        </h2>
                                        <h5 class="pb-4">
                                            {{ app()->getLocale() == 'en' ? $discount->dish->name_en : $discount->dish->name_ar }}
                                        </h5>
                                        <a class="btn" onclick="fill_cart('{{ $discount->id }}', 'discount')">
                                            <h4 class="fw-bold">@lang('header.ordernow')</h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </section>
    @endif
    <section class="plates text-center">
        <div class="container py-sm-5 py-4">
            <h2 class="fw-bold" data-aos="fade-down">@lang('header.mostpopular')</h2>
            <p class="text-muted pt-4" data-aos="fade-down">@lang('header.popularnote')</p>
            <div class="plates-slider owl-carousel owl-theme">
                @foreach ($popularDishes as $dish)
                    <div class="item mb-4" data-aos="zoom-in">
                        <div class="plate">
                            <a href="#">
                                <figure class="plate-img m-0">
                                    <img src="{{ asset($dish->image ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                        alt="{{ app()->getLocale() == 'en' ? $dish->name_en : $dish->name_ar }}">
                                </figure>
                            </a>
                            <div class="fav" style="border: none">
                                <form action="{{ route('add.favorite') }}" method="POST" class="favorite-form">
                                    @csrf
                                    <input type="hidden" name="dish_id" value="{{ $dish->id }}">
                                    <i class="favorite-icon {{ in_array($dish->id, $userFavorites) ? 'fas' : 'far' }} fa-heart"
                                        data-dish-id="{{ $dish->id }}"></i>
                                </form>
                            </div>
                            <div class="text-center pt-4">
                                <h5>{{ app()->getLocale() == 'en' ? $dish->name_en : $dish->name_ar }}</h5>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star"></i> @lang('header.rateall')
                                </span>
                                <div class="d-flex justify-content-between pt-4  ">
                                    {{-- <button class="btn add-to-cart-btn {{ $isOpen ? '' : 'disabled' }}"
                                        onclick="fill_cart('{{ $dish->id }}', 'dish')"
                                        {{ $isOpen ? '' : 'disabled' }} data-dish-id="{{ $dish->id }}"
                                        data-bs-toggle="modal" data-bs-target="#productModal">
                                        @lang('header.addtocart') +
                                    </button> --}}
                                    <span>
                                        @php
                                            $has_size = $dish->has_sizes;
                                            if ($has_size) {
                                                $defaultSize = $dish->dishSizes->where('default_size', 1)->first();
                                                $default_size_id = $defaultSize->id ?? null;

                                                $branch_dish_size = App\Models\BranchMenuSize::where(
                                                    'dish_id',
                                                    $dish->id,
                                                )
                                                    ->where('dish_size_id', $default_size_id)
                                                    ->where('branch_id', $branchId)
                                                    ->where('is_active', 1)
                                                    ->first();
                                                $total = $branch_dish_size->price ?? $dish->price;
                                            } else {
                                                $total = $dish->price;
                                            }
                                        @endphp
                                        {{ $total }} {{ $currencySymbol }}
                                    </span>
                                </div>
                                @if (!$isOpen)
                                    <small class="text-danger branch-closed-text">@lang('header.branchClosed')</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="info text-center overflow-hidden">
        <div class="container py-sm-5 py-4">
            <div class="row m-0 justify-content-center align-items-center">
                <div class="col-md-5 offset-md-1" data-aos="fade-left">
                    <h1 class="fw-bold">@lang('header.orderonline')</h1>
                    <p class="text-muted my-5">@lang('header.fewmin')</p>
                    <a href="{{ route('menu') }}" class="btn btn-lg info-btn-desktop">@lang('header.ordernow')</a>
                </div>
                <div class="col-md-6" data-aos="fade-right">
                    <figure class="info-img">
                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/info.png') }}" alt="">
                    </figure>
                      <a href="{{ route('menu') }}" class="btn btn-lg info-btn-mobile">@lang('header.ordernow')</a>
                </div>

            </div>
            {{-- <button type="button" class="btn cart-btn" onclick="openCart()">
                <i class="fas fa-shopping-cart"></i>
            </button> --}}
            {{-- <button type="button" class="btn cart-btn " data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                aria-controls="offcanvasRight">
                <i class="fas fa-shopping-cart"></i>
            </button> --}}
        </div>

    </section>

    <section class="apps overflow-hidden text-center">
        <div class="container pt-sm-5 pt-4">
            <div class="row m-0 justify-content-center align-items-center text-center">
                <div class="col-md-6" data-aos="fade-left">
                    <h3 class="mb-5">@lang('header.downloadapp')</h3>
                    <a href="#" class="app-store-btn">
                        <div>
                            <small>@lang('header.download')</small>
                            <h5>@lang('header.app')</h5>
                        </div>
                        <i class="fab fa-apple"></i>
                    </a>
                    <a href="#" class="google-play-btn">
                        <div>
                            <small>@lang('header.get')</small>
                            <h5>@lang('header.play')</h5>
                        </div>
                        <i class="fab fa-google-play"></i>
                    </a>
                </div>
                <div class="col-md-6" data-aos="fade-right">
                    <img class="apps-img" src="{{ asset('front/AlKout-Resturant/SiteAssets/images/apps-img.png') }}" />
                </div>
            </div>
        </div>
    </section>

    <!-- rateModal -->
    @if (Auth::guard('client')->check() && isset($lastOrder) && !$rate)
        <div class="modal fade" id="rateModal" tabindex="-1" aria-labelledby="rateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mx-0 justify-content-around">
                            <form id="feedbackForm" class="row mx-0 justify-content-around" method="POST"
                                action="{{ route('rate.store') }}">
                                @csrf
                                <div class="col-md-4">
                                    <div class="card container-faces p-4">
                                        <input type="hidden" name="order_id" value="{{ $lastOrder->id ?? '' }}">
                                        <div class="feedback">
                                            <div id="ratingError" class="text-danger mb-2" style="display: none;">
                                                Please select a rating before submitting.
                                            </div>
                                            <div class="rating">
                                                <input type="radio" name="rate" value="5" id="rating-5">
                                                <label for="rating-5"></label>
                                                <input type="radio" name="rate" value="4" id="rating-4">
                                                <label for="rating-4"></label>
                                                <input type="radio" name="rate" value="3" id="rating-3">
                                                <label for="rating-3"></label>
                                                <input type="radio" name="rate" value="2" id="rating-2">
                                                <label for="rating-2"></label>
                                                <input type="radio" name="rate" value="1" id="rating-1">
                                                <label for="rating-1"></label>
                                                <div class="emoji-wrapper">
                                                    <div class="emoji">
                                                        <img class="rating-0"
                                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/0.svg') }}"
                                                            alt="" />
                                                        <img class="rating-1"
                                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/1.svg') }}"
                                                            alt="" />
                                                        <img class="rating-2"
                                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/2.svg') }}"
                                                            alt="" />
                                                        <img class="rating-3"
                                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/3.svg') }}"
                                                            alt="" />
                                                        <img class="rating-4"
                                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/4.svg') }}"
                                                            alt="" />
                                                        <img class="rating-5"
                                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/5.svg') }}"
                                                            alt="" />
                                                    </div>
                                                </div>
                                            </div>
                                            <h5 class="fw-bold rating-message">@lang('slider.rateYourOrder')</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h4 class="fw-bold">@lang('slider.SendYourNotes')</h4>
                                    <h6 class="text-muted">@lang('slider.SendYourNotesDesc')</h6>
                                    <div class="my-3">
                                        <textarea class="form-control" rows="3" placeholder="@lang('slider.WriteYourNote')" name="complain"></textarea>
                                    </div>
                                    <button type="submit" class="btn w-100" data-bs-toggle="modal"
                                        data-bs-target="rateModal">@lang('slider.Send')</button>
                                </div>
                            </form>
                            <div class="col-12">
                                <div class="accordion mt-3" id="accordionExample">
                                    <div class="accordion-item bg-dark-gray">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseOne" aria-expanded="true"
                                                aria-controls="collapseOne">
                                                <h5 class="fw-bold">
                                                    <i class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                    @lang('slider.OrderDetails')
                                                </h5>
                                            </button>
                                        </h2>
                                        <div id="collapseOne" class="accordion-collapse collapse show"
                                            aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                            <div class="accordion-body p-3">
                                                @foreach ($orderDetails as $orderDetail)
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span
                                                                class="p-1 bg-secondary text-light rounded">{{ $orderDetail->quantity }}</span>
                                                            <span class="mx-1">x</span>
                                                            <span>{{ app()->getLocale() == 'en' ? $orderDetail->dish->name_en : $orderDetail->dish->name_ar }}</span>
                                                            <p class="text-muted mt-1 mb-0 mx-4">
                                                                <small>{{ app()->getLocale() == 'en' ? $orderDetail->dish->description_en : $orderDetail->dish->description_ar }}</small>
                                                            </p>
                                                        </div>
                                                        <p class="mb-0 fw-bold">{{ $orderDetail->price_after_tax }}
                                                            {{ $orderDetail->order->Branch->country->currency_symbol }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="accordion-footer border-top p-3">
                                                <div class="d-flex justify-content-end">
                                                    <p class="bg-success text-white px-3 rounded">@lang('slider.Completed')</p>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <p class="p-1 bg-warning text-dark rounded d-inline-block">
                                                        @lang('slider.OrderNumber') {{ $lastOrder->order_number }}</p>
                                                    <p class="fw-bold">
                                                        {{ Carbon\Carbon::parse($lastOrder->updated_at)->translatedFormat('d F Y') }}
                                                    </p>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="modal" id="confirmRate" aria-labelledby="confirmRateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="d-flex justify-content-center">
                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/donee.png') }}" alt="Logo"
                            height="110">
                    </div>
                    <h4> شكرا لك تم التقيم بنجاح</h4>
                </div>
                <div class="modal-footer d-flex flex-column border-0">
                    <a href="{{ route('menu') }}" type="button" class="btn w-100"> الذهاب للقائمة </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    @if (Auth::guard('client')->check() && $notifications->isNotEmpty())
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="d-flex justify-content-center mb-3">
                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/cancel.png') }}"
                                alt="Notification" height="110">
                        </div>
                        <h4>{{ app()->getLocale() == 'ar' ? $notifications->first()->title_ar : $notifications->first()->title_en }}
                        </h4>
                        <p>{{ app()->getLocale() == 'ar' ? $notifications->first()->description_ar : $notifications->first()->description_en }}
                        </p>
                    </div>
                    <div class="modal-footer d-flex flex-column border-0">
                        <a href="{{ $notifications->first()->url }}" type="button" class="btn w-100">
                            {{ app()->getLocale() == 'ar' ? 'عرض التفاصيل' : 'View Details' }}
                        </a>
                    </div>
                    <input type="hidden" id="notificationId" value="{{ $notifications->first()->id }}">
                </div>
            </div>
        </div>
    @endif

    @include('website.cart.cart-modal')

@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        setCookie('backButton', 'true', 7);

        // Use event delegation for dynamically loaded content
        $(document).on('click', '.favorite-icon', function(e) {
            e.preventDefault();
            var dishId = $(this).data('dish-id');
            var icon = $(this);
            var isFavorite = icon.hasClass('fas'); // Check current state

            $.ajax({
                url: '{{ route('add.favorite') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    dish_id: dishId
                },
                success: function(response) {
                    if (response.status === 'added') {
                        icon.removeClass('far').addClass('fas');
                        showSuccessAlert('تمت الإضافة إلى المفضلة',
                            'تمت إضافة الطبق إلى المفضلة بنجاح!');
                    } else if (response.status === 'removed') {
                        icon.removeClass('fas').addClass('far');
                        showInfoAlert('تمت الإزالة من المفضلة',
                            'تمت إزالة الطبق من المفضلة بنجاح!');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        $('#loginModal').modal('show');
                    } else {
                        showErrorAlert('خطأ', 'حدث خطأ ما. يرجى المحاولة مرة أخرى.');
                    }
                }
            });
        });

        // Helper functions for alerts
        function showSuccessAlert(title, text) {
            Swal.fire({
                icon: 'success',
                title: title,
                text: text,
                confirmButtonText: 'حسناً'
            });
        }

        function showInfoAlert(title, text) {
            Swal.fire({
                icon: 'info',
                title: title,
                text: text,
                confirmButtonText: 'حسناً'
            });
        }

        function showErrorAlert(title, text) {
            Swal.fire({
                icon: 'error',
                title: title,
                text: text,
                confirmButtonText: 'حسناً'
            });
        }

        // Show notification modal if present
        @if (Auth::guard('client')->check() && $notifications->isNotEmpty())
            $('#notificationModal').modal('show');
        @endif

        // Mark notification as read when modal is closed
        $('#notificationModal').on('hidden.bs.modal', function() {
            var notificationId = $('#notificationId').val();
            if (notificationId) {
                $.ajax({
                    url: '{{ route('notification.markAsRead', ['id' => '__ID__']) }}'.replace(
                        '__ID__', notificationId),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('Notification marked as read');
                        }
                    },
                    error: function() {
                        showErrorAlert('خطأ', 'فشل في تحديث حالة الإشعار.');
                    }
                });
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#rateModal').modal('show');
    });
</script>
<script>
    document.getElementById('feedbackForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Frontend validation
        const ratingSelected = document.querySelector('input[name="rate"]:checked');
        if (!ratingSelected) {
            document.getElementById('ratingError').style.display = 'block';
            document.getElementById('ratingError').textContent = 'Please select a rating';
            return;
        } else {
            document.getElementById('ratingError').style.display = 'none';
        }

        const form = this;
        const formData = new FormData(form);

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json' // Important for Laravel to return JSON errors
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    setTimeout(() => {
                        new bootstrap.Modal(document.getElementById('rateModal')).hide();
                        new bootstrap.Modal(document.getElementById('confirmRate')).show();
                    }, 300);
                } else {
                    // Handle other non-validation errors
                    alert(data.message || 'حدث خطأ أثناء إرسال التقييم. حاول مرة أخرى.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.errors) {
                    // Handle validation errors from backend
                    if (error.errors.rate) {
                        const errorElement = document.getElementById('ratingError');
                        errorElement.style.display = 'block';
                        errorElement.textContent = error.errors.rate[0];
                    }
                } else {
                    alert('حدث خطأ في الاتصال بالخادم.');
                }
            });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('show_confirmation_modal'))
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmRate'));
            confirmModal.show();
        @endif

        function isFutureDate(selectedDate) {
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Reset time for comparison
            const selected = new Date(selectedDate);
            selected.setHours(0, 0, 0, 0);
            return selected > today;
        }

        const takeawayDetails = JSON.parse(localStorage.getItem('takeaway_details'));
        const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
        const branchClosedTexts = document.querySelectorAll('.branch-closed-text');

        if (takeawayDetails && takeawayDetails.selected_date) {
            const isFuture = isFutureDate(takeawayDetails.selected_date);

            addToCartButtons.forEach(button => {
                if (isFuture) {
                    // Enable button for future takeaway orders
                    button.classList.remove('disabled');
                    button.removeAttribute('disabled');
                } else {
                    // Keep button disabled if branch is closed and date is today
                    if (!{{ $isOpen ? 'true' : 'false' }}) {
                        button.classList.add('disabled');
                        button.setAttribute('disabled', 'disabled');
                    }
                }
            });

            branchClosedTexts.forEach(text => {
                if (isFuture) {
                    // Hide branch closed message for future dates
                    text.style.display = 'none';
                } else if (!{{ $isOpen ? 'true' : 'false' }}) {
                    text.style.display = 'block';
                }
            });
        } else {
            // No takeaway_details; use isOpen status
            addToCartButtons.forEach(button => {
                if (!{{ $isOpen ? 'true' : 'false' }}) {
                    button.classList.add('disabled');
                    button.setAttribute('disabled', 'disabled');
                }
            });
        }
    });
</script>
