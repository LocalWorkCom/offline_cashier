@extends('website.layouts.master')
@section('content')
    <main>
        <section class="inner-header pt-5 mt-5">
            <div class="container pt-sm-5 pt-4">
                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page"> عربة التسوق</li>
                    </ol>
                </nav>
            </div>
        </section>

        <section class="cart-page">
            <div class="container py-sm-5 py-4">
                <div class="row mx-0">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title fw-bold"> لقد أوشكت على الانتهاء!</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-card p-4 row mb-4">
                                    <figure class="table-img m-0 col-md-3">
                                        <img id="tableImage" src="" alt="صورة الطاولة">
                                    </figure>

                                    <div class="table-details col-md-5 offset-md-4">
                                        <h4 class="fw-bold">
                                            <span id="tableName"></span>
                                            <span class="text-warning" id="reservType"></span>
                                        </h4>
                                        <div>
                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/clock.svg') }}"
                                                alt="" />
                                            <span class="text-muted mb-0">
                                                <span>الوصول:</span>
                                                <span id="tableSession"></span>
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/calender.svg') }}"
                                                    alt="" />
                                                <span class="text-muted mb-0" id="reservationDate"></span>
                                            </div>
                                            <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                                                    alt="" />
                                                <span class="text-muted mb-0" id="peopleCount"></span>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}"
                                                    alt="" />
                                                <span class="text-muted mb-0" id="partitionName"></span>
                                            </div>
                                            <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-clock.svg') }}"
                                                    alt="" />
                                                <span class="text-muted mb-0 main-color">
                                                    <span>المغادرة:</span>
                                                    <span id="leaveTime"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-body p-4">
                                <div class="notes">
                                    <h4 class="fw-bold">
                                        <i class="fas fa-file-alt main-color fa-xs"></i>
                                        هل لديك اى ملاحظات تود اضافتها ؟
                                    </h4>
                                    <div class="form-floating mt-3">
                                        <textarea class="form-control" placeholder="من فضلك اكتب ملاحظتك" id="notes" style="height: 100px"></textarea>
                                        <label for="floatingTextarea2">من فضلك اكتب ملاحظتك</label>
                                    </div>
                                </div>
                                <form>
                                    <div class="my-3 d-flex justify-content-end ">
                                        <button type="button" onclick="addNotes()" class="btn me-4">حفظ</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-4">
                        <div class="card" id="address-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-bold">حجز طاولة</h5>
                                <button class="btn reversed main-color fw-bold" type="button">
                                    تعديل
                                </button>

                            </div>
                            <div class="card-body p-4">
                                <p class="fw-bold">
                                    <i class="fas fa-map-marker-alt main-color ms-2"></i>
                                    <span id="address">

                                        {{-- مصدق الدقى و المهندسين وجيزه --}}
                                    </span>
                                </p>

                                <small class="text-muted" id="detail">
                                    121 مصدق , الدور 2 , شقة 12
                                </small>
                            </div>
                        </div>

                        <a class="btn w-100 d-flex justify-content-between mt-5"  id="checkout-btn">
                            <span> متابعة الحجز
                            </span>
                            <span>
                                <i class="fas fa-angle-left"></i>
                            </span>
                        </a>
                    </div>

                </div>
            </div>
        </section>
        <section class="before-footer"></section>

    </main>
@endsection
@include('website.table-reservation.js')
