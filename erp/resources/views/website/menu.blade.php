@extends('website.layouts.master')
@section('style')
    <link rel="stylesheet"
        href="{{ asset('front/AlKout-Resturant/SiteAssets/OwlCarousel2-2.3.4/dist/assets/owl.carousel.min.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('front/AlKout-Resturant/SiteAssets/OwlCarousel2-2.3.4/dist/assets/owl.theme.default.min.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
@endsection
<style>
    .favorite-icon {
        cursor: pointer;
        font-size: 24px;
        transition: all 0.3s ease;
    }

    .favorite-icon.far {
        color: #ccc;
    }

    .favorite-icon.fas {
        color: red;
    }

    .favorite-icon:hover {
        opacity: 0.8;
        transform: scale(1.1);
    }
</style>
@section('content')
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4 mt-3">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('auth.home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('header.menu')</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="plates mb-5">
        <div class="container pb-sm-5 pb-4">

            {{-- Display Validation Messages --}}
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <ul class="nav nav-pills px-0 align-items-center d-flex flex-wrap" id="pills-tab" role="tablist">
                <!-- Fixed "discounts" Tab -->
                @if ($offers->isNotEmpty())
                    <li class="nav-item mb-2" role="presentation">
                        <button
                            class="nav-link {{ $categoryId == 'offers' || (is_null($categoryId) && !$menuCategories->count()) ? 'active' : '' }}"
                            id="pills-discount-tab" data-bs-toggle="pill" data-bs-target="#pills-discount" type="button"
                            role="tab" aria-controls="pills-discount"
                            aria-selected="{{ $categoryId == 'offers' ? 'true' : 'false' }}">
                            <div class="category-button">
                                <i class="fas fa-tags main"></i>
                                <p class="me-3 mb-0">
                                    {{ app()->getLocale() == 'en' ? 'Discounts' : 'الخصومات' }}
                                </p>
                            </div>
                        </button>
                    </li>

                    <!-- Fixed "Offers" Tab -->
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link {{ $categoryId == 'offers' || (is_null($categoryId) && !$menuCategories->count()) ? 'active' : '' }}"
                            id="pills-offers-tab" data-bs-toggle="pill" data-bs-target="#pills-offers" type="button"
                            role="tab" aria-controls="pills-offers"
                            aria-selected="{{ $categoryId == 'offers' ? 'true' : 'false' }}">
                            <div class="category-button">
                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/offers.png' ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                    alt="العروض" class="offer-img" />
                                <p class="me-3 mb-0">{{ app()->getLocale() == 'en' ? 'Offers' : 'العروض' }}</p>
                            </div>
                        </button>
                    </li>
                @endif

                @foreach ($menuCategories as $category)
                    @if ($category->dish_categories->is_active && $category->branchMenus->isNotEmpty())
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $categoryId == $category->dish_categories->id || (!$categoryId && $loop->first) ? 'active' : '' }}"
                                id="pills-{{ $category->dish_categories->id }}-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-{{ $category->dish_categories->id }}" type="button" role="tab"
                                data-category-id="{{ $category->dish_categories->id }}"
                                aria-controls="pills-{{ $category->dish_categories->id }}"
                                aria-selected="{{ $categoryId == $category->dish_categories->id || (!$categoryId && $loop->first) ? 'true' : 'false' }}">
                                <div class="category-button">
                                    <img src="{{ asset($category->dish_categories->image_path ?? 'default-image.png') }}"
                                        alt="{{ app()->getLocale() == 'en' ? $category->dish_categories->name_en : $category->dish_categories->name_ar }}" />
                                    <p class="me-3 mb-0">
                                        {{ app()->getLocale() == 'en' ? $category->dish_categories->name_en : $category->dish_categories->name_ar }}
                                    </p>
                                </div>
                            </button>
                        </li>
                    @endif
                @endforeach
                <li class="nav-item w-100 mt-3" role="presentation">
                    <form id="searchForm">
                        <input id="searchInput" class="form-control py-2 " type="search" placeholder="@lang('other.search_menu')"
                            aria-label="Search">
                    </form>
                </li>
            </ul>

            <div class="d-flex justify-content-end">
                <form class="form-select-lg mb-3 w-25" id="filterForm" method="GET" action="{{ route('menu') }}">
                    <input type="hidden" name="category_id" value="{{ $categoryId }}">
                    <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                    <select class="form-select" aria-label=".form-select-lg example" name="filter"
                        onchange="document.getElementById('filterForm').submit();">
                        <option value="" {{ $filter === '' ? 'selected' : '' }}>@lang('header.orderby')</option>
                        <option value="most_ordered" {{ $filter === 'most_ordered' ? 'selected' : '' }}>@lang('header.mostOrdered')</option>
                        </option>
                        <option value="recently_added" {{ $filter === 'recently_added' ? 'selected' : '' }}>
                            @lang('header.newadd')</option>
                    </select>
                </form>
            </div>
            <div class="tab-content pt-5" id="pills-tabContent">
                <!-- Offers Tab Content -->
                <div class="tab-pane fade {{ $categoryId == 'offers' ? 'show active' : '' }}" id="pills-offers"
                    role="tabpanel" aria-labelledby="pills-offers-tab">
                    <div class="row mx-0">
                        @if ($offers->isNotEmpty())
                            @foreach ($offers as $offer)
                                <div class="col-md-4 mb-4" data-aos="zoom-in">
                                    <div class="plate">
                                        <figure class="plate-img m-0">
                                            <img src="{{ asset((app()->getLocale() == 'en' ? $offer->image_en : $offer->image_ar) ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                                alt="">
                                            <figcaption class="offers-badge">
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/offers.png' ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                                    alt="">
                                                {{ app()->getLocale() == 'en' ? 'Offer' : 'عرض' }}
                                            </figcaption>
                                        </figure>
                                        <div class="text-center pt-4">
                                            <h5>{{ app()->getLocale() == 'en' ? $offer->name_en : $offer->name_ar }} </h5>
                                            <div class="d-flex justify-content-between pt-4">
                                                {{-- <button class="btn "
                                                    onclick="fill_cart('{{ $offer->id }}', 'offer')">
                                                    @lang('header.addtocart')</button> --}}
                                                <div>
                                                    <span class="discount"> {{ $offer->discount_value }}
                                                        @if ($offer->discount_type == 'percentage')
                                                            %
                                                        @else
                                                            {{ $currencySymbol }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>

                <!-- Categories and Dishes -->
                @foreach ($menuCategories as $category)
                    <div class="tab-pane fade {{ $categoryId == $category->dish_categories->id || (is_null($categoryId) && $loop->first) ? 'show active' : '' }}"
                        id="pills-{{ $category->dish_categories->id }}" role="tabpanel"
                        aria-labelledby="pills-{{ $category->dish_categories->id }}-tab">
                        <div class="row mx-0">
                            @foreach ($category->branchMenus as $menu)
                                @if ($menu->dish)
                                    <div class="col-md-4 mb-4 dish-card {{ in_array($menu->dish->id, $userFavorites) ? 'favorite' : '' }}"
                                        data-dish-id="{{ $menu->dish->id }}"
                                        data-dish-name="{{ app()->getLocale() == 'en' ? $menu->dish->name_en : $menu->dish->name_ar }}"
                                        data-aos="zoom-in">
                                        <div class="plate"  >
                                            <figure class="plate-img m-0" data-bs-toggle="modal" data-bs-target="#productModal"
                                                        onclick="fill_cart('{{ $menu->dish->id }}', 'dish')"
                                                        {{ $isOpen ? '' : 'disabled' }}
                                                        data-dish-id="{{ $menu->dish->id }}">
                                                <img src="{{ asset($menu->dish->image ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                                    alt="{{ app()->getLocale() == 'en' ? $menu->dish->name_en : $menu->dish->name_ar }}">
                                            </figure>
                                            <div class="fav" style="border: none">
                                                <form action="{{ route('add.favorite') }}" method="POST"
                                                    class="favorite-form">
                                                    @csrf
                                                    <input type="hidden" name="dish_id" value="{{ $menu->dish->id }}">
                                                    <i class="favorite-icon {{ in_array($menu->dish->id, $userFavorites) ? 'fas' : 'far' }} fa-heart"
                                                        data-dish-id="{{ $menu->dish->id }}"></i>
                                                </form>
                                            </div>
                                            <div class="text-center pt-4" data-bs-toggle="modal" data-bs-target="#productModal"
                                                        onclick="fill_cart('{{ $menu->dish->id }}', 'dish')"
                                                        {{ $isOpen ? '' : 'disabled' }}
                                                        data-dish-id="{{ $menu->dish->id }}">
                                                <h5 >{{ app()->getLocale() == 'en' ? $menu->dish->name_en : $menu->dish->name_ar }}
                                                </h5>
                                                @if ($popularDishes)
                                                    @php
                                                        $popularDishIds = $popularDishes->pluck('id')->toArray();
                                                    @endphp
                                                    @if (in_array($menu->dish->id, $popularDishIds))
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-star"></i> @lang('header.rateall')
                                                        </span>
                                                    @else
                                                        <span class="badge"><i></i></span>
                                                    @endif
                                                @endif
                                                <div class="d-flex justify-content-between pt-4">
                                                    {{-- <button class="btn {{ $isOpen ? '' : 'disabled' }}"
                                                        data-bs-toggle="modal" data-bs-target="#productModal"
                                                        onclick="fill_cart('{{ $menu->dish->id }}', 'dish')"
                                                        {{ $isOpen ? '' : 'disabled' }}
                                                        data-dish-id="{{ $menu->dish->id }}">
                                                        @lang('header.addtocart') +
                                                    </button> --}}
                                                    <span>{{ $menu->defaultPrice }} {{ $currencySymbol }}</span>
                                                </div>

                                                @if (!$isOpen)
                                                    <small
                                                        class="text-danger branch-closed-text">@lang('header.branchClosed')</small>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {{-- <button type="button" class="btn cart-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
            aria-controls="offcanvasRight">
            <i class="fas fa-shopping-cart"></i>
        </button> --}}
    </section>
    @include('website.cart.cart-modal')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const dishCards = document.querySelectorAll('.dish-card');
            const categoryTabs = document.querySelectorAll('[data-category-id]');

            categoryTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const selectedCategoryId = this.getAttribute('data-category-id');
                    const url = new URL(window.location.href);

                    url.searchParams.set('category_id', selectedCategoryId);
                    history.replaceState(null, '', url.toString());
                });
            });
            searchInput.addEventListener('input', function() {
                const query = searchInput.value.toLowerCase();

                dishCards.forEach(function(card) {
                    const dishName = card.getAttribute('data-dish-name').toLowerCase();
                    if (dishName.includes(query)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });


        $(document).ready(function() {
            var check_dish_details_id = {{ $id_details }};
            if (check_dish_details_id != 0) {
                fill_cart({{ $id_details }}, 'dish');
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/OwlCarousel2-2.3.4/dist/owl.carousel.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Use event delegation for dynamically loaded content
            $(document).on('click', '.favorite-icon', function(e) {
                e.preventDefault();
                var icon = $(this);
                var dishId = icon.data('dish-id');
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

                        // Toggle the favorite class on the card if needed
                        icon.closest('.dish-card').toggleClass('favorite', response.status ===
                            'added');
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
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function isFutureDate(selectedDate) {
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time for comparison
                const selected = new Date(selectedDate);
                selected.setHours(0, 0, 0, 0);
                return selected > today;
            }

            // Check takeaway_details in local storage
            const takeawayDetails = JSON.parse(localStorage.getItem('takeaway_details'));
            const addToCartButtons = document.querySelectorAll('button[data-dish-id]');
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
@endpush
