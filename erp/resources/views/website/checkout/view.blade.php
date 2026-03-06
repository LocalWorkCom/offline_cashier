@extends('website.layouts.master')
@section('content')
    <main>
        <section class="inner-header pt-5 mt-5">
            <div class="container pt-sm-5 pt-4">
                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('checkout.home')</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cart') }}">@lang('checkout.cart')</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@lang('checkout.payment')</li>
                    </ol>
                </nav>
            </div>
        </section>
        <section class="checkout-page">
            <div class="container py-sm-5 py-4">
                @if ($errors->has('error'))
                    <div class="alert alert-danger">
                        {{ $errors->first('error') }}
                    </div>
                @endif

                <form action="{{ route('web.order.add') }}" id="makeOrderForm" method="POST">
                    <div class="row mx-0">
                        @csrf
                        <div class="col-md-8">


                            <div class="card my-4 d-none" id="checkout-address-v1">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title fw-bold">@lang('checkout.delivery_address')</h5>
                                    <a class="btn reversed main-color fw-bold" id="edit-address" href="">
                                        @lang('checkout.edit')
                                    </a>
                                </div>
                                <div class="card-body p-4" id="deliveryAddress">
                                    {{-- //input client_address_id --}}
                                    <!-- Dynamic content will be injected here -->
                                </div>
                            </div>

                            <div class="card my-4 d-none" id="checkout-address-v3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title fw-bold">@lang('checkout.receive_address')</h5>
                                    <a class="btn reversed main-color fw-bold" data-bs-toggle="modal"
                                        data-bs-target="#branchSelectionModal">
                                        @lang('checkout.edit')
                                    </a>
                                </div>
                                <div class="card-body p-4" id="branchReceiveName">
                                    <!-- Dynamic content will be injected here -->
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title fw-bold">@lang('checkout.choose_payment')</h5>
                                </div>

                                <div class="card-body p-4">

                                    {{-- Delivery Payment Options --}}
                                    <div id="payment-options-delivery" class="payment-options d-none">
                                        @php $firstChecked = false; @endphp
                                        @foreach (getBranchPolicyPayment($branchId, 'delivery') as $policy)
                                            @include('website.checkout.payment-option', [
                                                'policy' => $policy,
                                                'firstChecked' => &$firstChecked,
                                                'name' => 'payment_method',
                                                
                                            ])
                                        @endforeach
                                    </div>

                                    {{-- Takeaway Payment Options --}}
                                    <div id="payment-options-takeaway" class="payment-options d-none">
                                        @php $firstChecked = false; @endphp
                                        @foreach (getBranchPolicyPayment($branchId, 'takeaway') as $policy)
                                            @include('website.checkout.payment-option', [
                                                'policy' => $policy,
                                                'firstChecked' => &$firstChecked,
                                                'name' => 'payment_method2',
                                            ])
                                        @endforeach
                                    </div>

                                    <div id="payment-options-with" class="payment-options d-none">
                                        @php $firstChecked = false; @endphp
                                        @foreach (getBranchPolicyPayment($branchId, 'reservation_with_order') as $policy)
                                            @include('website.checkout.payment-option', [
                                                'policy' => $policy,
                                                'firstChecked' => &$firstChecked,
                                                'name' => 'payment_method2',
                                            ])
                                        @endforeach
                                    </div>

                                </div>
                            </div>


                            <div class="card mt-4 d-none" id="meal-appiontments">
                                <div class="card-header">
                                    <h5 class="card-title fw-bold">حدد معاد تجهيز الوجبات</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="form-check">
                                        <label class="form-check-label" for="at">
                                            عند الوصول </label>
                                        <input class="form-check-input" type="radio" value="at" id="at"
                                            name="appiontment">
                                    </div>
                                    <div class="form-check mt-3">

                                        <label class="form-check-label" for="before">
                                            قبل الوصول </label>
                                        <input class="form-check-input" type="radio" name="appiontment" id="before"
                                            value="before">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            @if (request()->getHttpHost() === 'erpsystem.testdomain100.online'|| request()->getHttpHost() === 'erp.test')
                            <div class="card d-none" id="reservation-policy">
                                <div class="card-header">
                                    <h5 class="card-title fw-bold">سياسة الحجز</h5>
                                </div>
                                <div class="card-body p-4">
                                    <?php $shortText = mb_strlen($reservation_policy->description_site) > 50 ? mb_substr($reservation_policy->description_site, 0, 50) . '...' : $reservation_policy->description_site;
                                    ?>

                                    <p>{!! $shortText !!}</p>
                                    <div class="d-flex justify-content-end">
                                        <a class="main-color text-decoration-underline" data-bs-target="#moreModal"
                                            data-bs-toggle="modal">المزيد</a>
                                    </div>


                                </div>

                                <div class="card-footer p-4">
                                    <div class="d-flex align-items-center">
                                        <input class="form-check-input ms-2" type="checkbox" id="agree-policy">
                                        <label class="form-check-label d-flex align-items-center" for="agree-policy">
                                            اوافق على سياسة الحجز
                                        </label>
                                    </div>

                                </div>
                            </div>
  @endif

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title fw-bold">@lang('checkout.order_summary')</h5>
                                </div>
                                <div class="card-body p-4" id="orderSummary">
                                    <!-- Dynamic content will be injected here -->
                                </div>
                                <div class="card-footer p-4">
                                    <div class="total">
                                        <p class="fw-bold" id="totalText">@lang('checkout.total')</p>
                                        <p class="fw-bold" id="totalAmount">580 ج.م</p>
                                    </div>
                                    @if (getBranchSettings($branchId, 'tax_application') == 1)
                                        <div class="message bg-warning p-2 rounded-3">
                                            <small>
                                                @lang('checkout.tax_message')
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <input type="hidden" name="cart_data" id="cartDataInput">
                            <input type="hidden" name="takeaway_data" id="takeawayDataInput">
                            <input type="hidden" name="reservation_data" id="reservation_data">
                            <input type="hidden" name="cart_type" id="cartTypeInput">
                            <button type="submit" class="btn w-100 mt-5" id="place_order">
                                <!-- <button type="button" class="btn w-100 mt-5" id="place_order"> -->
                                @lang('checkout.place_order')
                            </button>

                        </div>
                    </div>

                </form>

            </div>
        </section>
    </main>
    <div class="modal fade" id="moreModal" tabindex="-1" aria-labelledby="moreModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">سياسة الحجز</h5>
                </div>
                <div class="modal-body">
                    <div class="card p-4">
                        <div class="card-header bg-white p-0">
                            <h6 class="card-title fw-bold">{{ $reservation_policy->name_site }}</h6>
                        </div>
                        <p>{!! $reservation_policy->description_site !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('website.checkout.js')
    @include('website.cart.global')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>


    <script type="text/javascript">
        function noBack() {
            var backButton = getCookie('backButton');
            if (backButton === "false") {
                //location.reload();
                location.href = "{{ route('home') }}";
            }
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
