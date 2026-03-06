@extends('website.layouts.master')
<style>
    .favorite-icon {
        cursor: pointer;
        color: red;
        font-size: 24px;
    }

    .favorite-icon:hover {
        opacity: 0.8;
    }
</style>
@section('content')
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('header.home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('header.favorite')</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="plates mb-5">
        <div class="container pb-sm-5 pb-4">
            <ul class="nav nav-pills mb-3 px-0 align-items-center" id="pills-tab" role="tablist">
                @foreach ($menuCategories as $key => $menuCategory)
                    @if ($menuCategory->branchMenus->filter(fn($menu) => $menu->dish && in_array($menu->dish->id, $userFavorites))->isNotEmpty())
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $key == 0 ? 'active' : '' }}" id="pills-{{ $menuCategory->id }}-tab"
                                data-bs-toggle="pill" data-bs-target="#pills-{{ $menuCategory->id }}" type="button"
                                role="tab" aria-controls="pills-{{ $menuCategory->id }}"
                                aria-selected="{{ $key == 0 ? 'true' : 'false' }}">
                                <div class="category-button">
                                    <img src="{{ asset($menuCategory->dish_categories->image_path ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                        alt="{{ $menuCategory->dish_categories->name_en }}" />
                                    <p class="me-3 mb-0">
                                        {{ app()->getLocale() === 'ar' ? $menuCategory->dish_categories->name_ar : $menuCategory->dish_categories->name_en }}
                                    </p>
                                </div>
                            </button>
                        </li>
                    @endif
                @endforeach
                <li class="nav-item" role="presentation">
                    <form id="searchForm">
                        <input id="searchInput" class="form-control py-2" type="search" placeholder="@lang('other.search_menu')"
                            aria-label="Search">
                    </form>
                </li>
            </ul>

            <div class="tab-content pt-5" id="pills-tabContent">
                @foreach ($menuCategories as $key => $menuCategory)
                    @if ($menuCategory->branchMenus->filter(fn($menu) => $menu->dish && in_array($menu->dish->id, $userFavorites))->isNotEmpty())
                        <div class="tab-pane fade {{ $key == 0 ? 'show active' : '' }}" id="pills-{{ $menuCategory->id }}"
                            role="tabpanel" aria-labelledby="pills-{{ $menuCategory->id }}-tab">
                            <div class="row mx-0">
                                @foreach ($menuCategory->branchMenus->filter(fn($menu) => $menu->dish && in_array($menu->dish->id, $userFavorites)) as $menu)
                                    <div class="col-md-4 mb-4 dish-card" data-dish-id="{{ $menu->dish->id }}"
                                        data-dish-name="{{ app()->getLocale() === 'ar' ? $menu->dish->name_ar : $menu->dish->name_en }}"
                                        data-aos="zoom-in">
                                        <div class="plate">
                                            <a href="#">
                                                <figure class="plate-img m-0">
                                                    <img src="{{ asset($menu->dish->image ?? 'front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                                                        alt="{{ $menu->dish->name_ar }}">
                                                </figure>
                                            </a>
                                            <div class="fav" style="border: none">
                                                <form action="{{ route('add.favorite') }}" method="POST"
                                                    class="favorite-form">
                                                    @csrf
                                                    <input type="hidden" name="dish_id" value="{{ $menu->dish->id }}">
                                                    <i class="favorite-icon {{ in_array($menu->dish->id, $userFavorites) ? 'fas' : 'far' }} fa-heart"
                                                        data-dish-id="{{ $menu->dish->id }}"></i>
                                                </form>
                                            </div>
                                            <div class="text-center pt-4">
                                                <h5>{{ app()->getLocale() === 'ar' ? $menu->dish->name_ar : $menu->dish->name_en }}
                                                </h5>
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
                                                <div class="d-flex justify-content-between pt-4">
                                                    {{-- <button class="btn {{ $isOpen ? '' : 'disabled' }}"
                                                        data-bs-toggle="modal" data-bs-target="#productModal"
                                                        onclick="fill_cart('{{ $menu->dish->id }}', 'dish')"
                                                        {{ $isOpen ? '' : 'disabled' }}>
                                                        @lang('header.addtocart') +
                                                    </button> --}}

                                                    <span>{{ $menu->defaultPrice }} {{ $currencySymbol }}</span>
                                                </div>

                                                @if (!$isOpen)
                                                    <small class="text-danger">@lang('header.branchClosed')</small>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endsection
@include('website.cart.cart-modal')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const dishCards = document.querySelectorAll('.dish-card');

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
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Use event delegation for dynamically loaded content
        $(document).on('click', '.favorite-icon', function(e) {
            e.preventDefault();
            var dishId = $(this).data('dish-id');
            var icon = $(this);
            var cardElement = $(this).closest('.dish-card'); // Get the parent card element

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
                        Swal.fire({
                            icon: 'success',
                            title: 'تمت الإضافة إلى المفضلة',
                            text: 'تمت إضافة الطبق إلى المفضلة بنجاح!',
                            confirmButtonText: 'حسناً'
                        });
                    } else if (response.status === 'removed') {
                        // Remove the card from the DOM with a fade out animation
                        cardElement.fadeOut(300, function() {
                            $(this).remove();

                            // Check if the category is now empty
                            var categoryContainer = cardElement.closest(
                            '.tab-pane');
                            if (categoryContainer.find('.dish-card').length === 0) {
                                // Remove the category tab if it's empty
                                var tabId = categoryContainer.attr('id');
                                $('button[data-bs-target="#' + tabId + '"]')
                                    .closest('li').remove();

                                // If we removed the active tab, activate the first remaining tab
                                if (categoryContainer.hasClass('show active')) {
                                    $('.nav-pills li:first button').tab('show');
                                }

                                // Remove the tab content
                                categoryContainer.remove();
                            }
                        });

                        Swal.fire({
                            icon: 'info',
                            title: 'تمت الإزالة من المفضلة',
                            text: 'تمت إزالة الطبق من المفضلة بنجاح!',
                            confirmButtonText: 'حسناً'
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        $('#loginModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: 'حدث خطأ ما. يرجى المحاولة مرة أخرى.',
                            confirmButtonText: 'حسناً'
                        });
                    }
                }
            });
        });
    });
</script>
