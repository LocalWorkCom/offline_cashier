@extends('website.layouts.master')

@section('content')
    <main>
        <section class="inner-header pt-5 mt-5">
            <div class="container pt-sm-5 pt-4 mt-4">
                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">@lang('cart.home')</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('menu') }}">@lang('cart.menu')</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            @lang('cart.cart')
                        </li>
                    </ol>
                </nav>
            </div>
        </section>
        <div class="alert alert-warning cart-item-update" style="display: none">@lang('cart.losingItem')</div>
        <section class="cart-page pb-5">
            <div class="container ">

                <div class="row mx-0">
                    <div class="col-md-8">
                        <div class="card" id="reservation-card" style="display: none">
                            <div class="card-header">
                                <h5 class="card-title fw-bold"> لقد أوشكت على الانتهاء!</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="table-card row mb-4">
                                    <figure class="table-img m-0 col-md-3">
                                        <img src="" alt="" id="table-Img">
                                    </figure>

                                    <div class="table-details col-md-5 offset-md-4">
                                        <h4 class="fw-bold">
                                            <span id="table-name">

                                            </span>
                                            <span class="text-warning">(طلب وجبة )</span>
                                        </h4>
                                        <div>
                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/clock.svg') }}"
                                                alt="" />
                                            <span class="text-muted mb-0">
                                                الوصول:
                                                <span id="table-time">

                                                </span>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/calender.svg') }}"
                                                    alt="" />
                                                <span class="text-muted mb-0" id="table-date">
                                                </span>
                                            </div>
                                            <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                                                    alt="" />
                                                <span class="text-muted mb-0"id="persons">

                                                </span>
                                                <span class="text-muted mb-0" id="kids">

                                                </span>
                                            </div>

                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}"
                                                    alt="" />
                                                <span class="text-muted mb-0" id="table-place">
                                                    داخل المطعم
                                                </span>
                                            </div>
                                            <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-clock.svg') }}"
                                                    alt="" />
                                                <span class="text-muted mb-0 main-color">
                                                    <span>
                                                        المغادرة:
                                                    </span>
                                                    <span id="table-leave-time">

                                                        2:30 م
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-bold">@lang('cart.orders')</h5>
                                <a href="{{ route('menu') }}" class="btn reversed main-color d-flex fw-bold" type="button">
                                    <span class="inc ms-2">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </span>
                                    @lang('cart.add_item')
                                </a>
                            </div>
                            <div class="card-body p-4" id="item-list">

                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-body p-4" id="note-div">
                                <div class="coupons">
                                    <h4 class="fw-bold">
                                        <i class="fas fa-file-alt main-color fa-xs"></i>
                                        @lang('cart.discounts')
                                    </h4>
                                    <div class="my-3 d-flex">
                                        <input type="text" class="form-control" placeholder="@lang('cart.add_coupon')"
                                            id="coupon">
                                        <button type="button" class="btn me-4"
                                            id="apply-coupon-btn">@lang('cart.apply_coupon')</button>
                                        <button type="button" class="btn btn-danger me-4 d-none"
                                            id="remove-coupon-btn">@lang('cart.remove_coupon')</button>
                                    </div>
                                </div>
                                <div class="notes mt-4">
                                    <h4 class="fw-bold">
                                        <i class="fas fa-file-alt main-color fa-xs"></i>
                                        @lang('cart.notes_title')
                                    </h4>
                                    <div class="form-floating mt-3">
                                        <textarea class="form-control" placeholder="@lang('cart.add_note')" id="note-order" style="height: 100px"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" id="delivery-case">

                        <div class="card d-none" id="cart-address-v1">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-bold" id="address-title-v1">@lang('cart.delivery_location')</h5>
                                <a class="btn reversed main-color fw-bold" id="edit-address-v1" href="#">
                                    @lang('cart.edit')
                                </a>
                            </div>
                            <div class="card-body p-4" id="address_details-v1">
                                <p class="fw-bold">
                                    <i class="fas fa-map-marker-alt main-color ms-2"></i>
                                    <span id="address-name"></span>

                                    <span id="address-type"></span>
                                </p>
                                <small class="text-muted" id="address-desc-v1">
                                    <span id="address-city"></span>
                                    <span id="address-building"></span>
                                    <span id="address-floor"></span>
                                    <span id="address-num"></span>

                                </small>
                            </div>
                        </div>
                        <div class="card d-none" id="cart-address-v2">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-bold" id="address-title-v2">@lang('cart.delivery_location')</h5>
                                <a class="btn reversed main-color fw-bold" id="edit-address-v2" href="#">
                                    @lang('cart.edit')
                                </a>
                            </div>
                            <div class="card-body p-4" id="address_details-v2">
                                <div class="message bg-warning p-2 rounded-3">
                                    <small>
                                        @lang('cart.you should select address to complete your order')
                                    </small>
                                </div>
                                <br>
                                @auth('client')
                                    <a class="btn reversed main-color fw-bold" href="{{ route('create.Address') }}">
                                        @lang('cart.add_address')
                                    </a>
                                @else
                                    <a class="btn reversed main-color fw-bold" data-bs-toggle="modal"
                                        data-bs-target="#deliveryModal">
                                        @lang('cart.add_address')
                                    </a>
                                @endauth

                            </div>
                        </div>


                        <div class="card d-none" id="cart-address-v3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-bold" id="address-title-v3">@lang('cart.receive_location')</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="message bg-warning p-2 rounded-3">
                                    <small id="branch_receive_name"></small>
                                </div>
                                <br>
                                <a class="btn btn-green fw-bold" data-bs-toggle="modal"
                                    data-bs-target="#branchSelectionModal">
                                    @lang('cart.change_branch')
                                </a>
                            </div>
                        </div>

                        <div class="card mt-4" id="card-payment">
                            <div class="card-header">
                                <h5 class="card-title fw-bold">@lang('cart.order_summary')</h5>
                            </div>
                            <div class="card-body p-4">
                                <ul class="list-unstyled p-0">
                                    <li class="order-list d-none" id="total-div">
                                        <p>@lang('cart.mintotal') </p>
                                        <p class="fw-bold" id="total-value">0 ج.م</p>
                                    </li>
                                    <li class="order-list d-none" id="service-div">
                                        <p>@lang('cart.service_fees')</p>
                                        <p class="fw-bold" id="service_fees"></p>
                                    </li>
                                    <li class="order-list">
                                        <p>@lang('cart.subtotal')</p>
                                        <p class="fw-bold" id="total-before-coupon">0 ج.م</p>
                                    </li>
                                    <li class="order-list d-none" id="coupon-div">
                                        <p class="main-color">@lang('cart.discount_coupon') <span id="code"></span></p>
                                        <p class="fw-bold main-color" id="discount-value">0 ج.م</p>
                                    </li>
                               
                                    <li class="order-list" id="shipping-div">
                                        <p>@lang('cart.delivery_fee')</p>
                                        <p class="fw-bold" id="shipping-value">0 ج.م</p>
                                    </li>

                                </ul>
                                @if (getBranchSettings($branchId, 'tax_application') == 0)
                                    <div class="message bg-warning p-2 rounded-3">
                                        <small>
                                            @lang('cart.tax_included') {{ getBranchSettings($branchId, 'tax_percentage') }} %
                                            {{ trans('cart.means') }}
                                            <span id="tax">0 ج.م</span> {{ trans('cart.on_invoice') }}

                                        </small>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer p-4">
                                <div class="total">
                                    <p class="fw-bold">@lang('cart.total')</p>
                                    <p class="fw-bold" id="total">0 ج.م</p>
                                </div>
                                @if (getBranchSettings($branchId, 'tax_application') == 1)
                                    <div class="message bg-warning p-2 rounded-3">
                                        <small>
                                            @lang('checkout.tax_message')
                                        </small>
                                    </div>
                                @endif

                                     <a class="btn w-100 d-flex justify-content-between mt-1" id="checkout-btn">
                            <span>@lang('cart.continue_payment') <span class="me-2"> > </span></span>
                            <span id="total-pay">0 ج.م</span>
                        </a>
                            </div>
                        </div>
                   
                    </div>
                </div>
                </form>
            </div>
        </section>
    </main>
    @include('website.cart.cart-modal')
@endsection

@push('scripts')
    @include('website.cart.js')

    <script type="text/javascript">
        function noBack() {
            var backButton = getCookie('backButton');
            if (backButton === "false") {
                //location.reload();
                location.href = "{{ route('home') }}";
            }

            console.log(backButton);
        }

        window.onload = function() {
            noBack(); // Run on page load
        };

        window.onpageshow = function(event) {
            if (event.persisted) {
                noBack(); // Run when returning from cache
            }
        };
        window.onpopstate = function() {
            noBack(); // Prevent back navigation
        };
    </script>
@endpush
