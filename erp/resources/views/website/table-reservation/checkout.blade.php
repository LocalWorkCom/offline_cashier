@extends('website.layouts.master')
@section('content')
    <main>
        <section class="inner-header pt-5 mt-5">
            <div class="container pt-sm-5 pt-4">
                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="cart.html"> عربة التسوق</a></li>
                        <li class="breadcrumb-item active" aria-current="page"> الدفع </li>
                    </ol>
                </nav>
            </div>
        </section>
        <section class="checkout-page">
            <div class="container py-sm-5 py-4">
                <div class="row mx-0">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-bold"> تفاصيل الحجز </h5>
                                {{-- <button class="btn reversed main-color fw-bold" type="button">
                                    تعديل
                                </button> --}}
                            </div>
                            <div class="card-body p-4">
                                <h5 class="fw-bold">
                                    <i class="fas fa-user main-color ms-2"></i>
                                    {{ Auth::guard('client')->user()->name }}
                                </h5>
                                <p class="text-muted">
                                    <span>رقم الهاتف:
                                    </span> {{ Auth::guard('client')->user()->phone }}
                                </p>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-bold"> تفاصيل حجز الطاولة </h5>
                                <button class="btn reversed main-color fw-bold" type="button">
                                    تعديل
                                </button>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between">
                                    <h5 class="fw-bold mb-0">
                                        <i class="fas fa-map-marker-alt main-color ms-2"></i>
                                        <span id="address">

                                        </span>
                                    </h5>

                                </div>
                                <p class="text-muted" id="detail">
                                </p>
                                <div class="table-details w-50">
                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-table.svg') }}"
                                        alt="" />
                                    <h5 class="fw-bold d-inline">طاولة <span class="text-warning">(بدون طلب)</span></h5>
                                    <div>
                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/clock.svg') }}"
                                            alt="" />
                                        <span class="text-muted mb-0">
                                            الوصول:
                                            <span id="tableSession">
                                            </span>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/calender.svg') }}"
                                                alt="" />
                                            <span class="text-muted mb-0" id="reservationDate">
                                            </span>
                                        </div>
                                        <div>
                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                                                alt="" />
                                            <span class="text-muted mb-0" id="peopleCount">
                                            </span>
                                        </div>

                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}"
                                                alt="" />
                                            <span class="text-muted mb-0" id="partitionName">
                                            </span>
                                        </div>
                                        <div>
                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-clock.svg') }}"
                                                alt="" />
                                            <span class="text-muted mb-0 main-color">
                                                المغادرة:
                                                <span id="leaveTime">
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title fw-bold"> اختر طريقة الدفع </h5>
                            </div>
                            <div class="card-body p-4">

                                {{-- Delivery Payment Options --}}
                                <div id="payment-options-without" class="payment-options d-none">
                                    @php $firstChecked = false; @endphp
                                    @foreach (getBranchPolicyPayment($branchId, 'reservation_without_order') as $policy)
                                        @include('website.checkout.payment-option', [
                                            'type' => 'reservation_without_order',
                                            'policy' => $policy,
                                            'firstChecked' => &$firstChecked,
                                            'name' => 'payment_method',
                                        ])
                                    @endforeach
                                </div>

                                {{-- Takeaway Payment Options --}}
                                <div id="payment-options-with" class="payment-options d-none">
                                    @php $firstChecked = false; @endphp
                                    @foreach (getBranchPolicyPayment($branchId, 'reservation_with_order') as $policy)
                                        @include('website.checkout.payment-option', [
                                            'type' => 'reservation_without_order',
                                            'policy' => $policy,
                                            'firstChecked' => &$firstChecked,
                                            'name' => 'payment_method',
                                        ])
                                    @endforeach
                                </div>

                            </div>


                        </div>


                    </div>
                    <div class="col-md-4">
                        <div class="card">
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
                        <!-- with order -->
                        <div class="card mt-4" id="pay_deposit" style="display: none">
                            <div class="card-header">
                                <h5 class="card-title fw-bold"> ملخص الطلب </h5>
                            </div>
                            <div class="card-body p-4">
                                <div style="display: flex;justify-content: space-between;" id="">
                                    <p id="payment-text">
                                        دفع عربون
                                    </p>
                                    <p id="payment-value">
                                    </p>
                                </div>
                            </div>
                        </div>


                        <button class="btn w-100 mt-5" id="confirmTable" data-url="{{ route('table-reservation.store') }}">
                            تنفيذ الطلب
                        </button>
                    </div>

                </div>
            </div>
        </section>

        <section class="before-footer"></section>

    </main>

    <!-- more Modal -->
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



    <!-- checkout modal -->
    <div class="modal fade" id="tableConfirmModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class=" d-flex justify-content-center">
                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/donee.png') }}" alt="Done"
                            class="done-img">
                    </div>
                    <h5 class="fw-bold">تم حجز الطاولة بنجاح وسوف تصلك رسالة على هاتفك او بريدك الالكترونى بتفاصيل الحجز
                    </h5>
                </div>
                <div class="modal-footer d-flex flex-column border-0">
                    <button type="button" class="btn w-100" id="continue-order-btn">متابعة الطلب</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@include('website.table-reservation.js')
<script>
    if (!localStorage.getItem('TableReservation')) {
        // If it's not found, redirect to the home page
        window.location.href = '/'; // or the URL of your home page
    }
</script>
