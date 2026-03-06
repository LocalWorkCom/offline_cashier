<div class="table-reservation modal fade" tabindex="-1" id="table-reservation" aria-labelledby="tableReservationLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h3 class="text-center fw-bold mb-4"> حجز الطاولة</h3>
                <div class="location border-bottom mb-1" id="branch_information"></div>

                <h4 class="fw-bold my-4">من فضلك قم بحجز الطاولة المفضلة لك</h4>
                <div class="row g-2 bg-warning justify-content-between selectors-container">
                    <div class="col-md-2">
                        <div class="dropdown">
                            <button id="people-dropdown-btn"
                                class="select-btn d-flex justify-content-between align-items-center dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <div class="text-muted">
                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}" alt="" />
                                    <span class="mx-2">عدد الأفراد</span>
                                </div>
                                <i class="fas fa-chevron-down text-muted"></i>
                            </button>
                            <div class="dropdown-menu p-3">
                                <!-- Selection Buttons -->
                                <div class="d-flex justify-content-between mb-3">
                                    <button id="family-btn" class="normal-btn w-50 rounded-pill">
                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/family.svg') }}" alt="" />
                                        <span>
                                            عائلة
                                        </span>
                                    </button>
                                    <button id="individuals-btn" class="normal-btn w-50 rounded-pill">
                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/people.svg') }}" alt="" />
                                        أشخاص
                                    </button>
                                </div>
                                <!-- Counters -->
                                <div class="mb-3 d-flex justify-content-between">
                                    <h5 class="fw-bold">عدد الاشخاص</h5>
                                    <div class="d-flex align-items-center justify-content-end w-50">
                                        <span class="dec" id="decrease-adults">
                                            <i class="fa fa-minus" aria-hidden="true"></i>
                                        </span>
                                        <input type="text" id="adults-count" class="w-25 text-center border-0" value="1" readonly>
                                        <span class="inc" id="increase-adults"> <i class="fa fa-plus" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-3 d-flex justify-content-between">
                                    <h5 class="fw-bold">عدد الاطفال</h5>
                                    <div class="d-flex align-items-center justify-content-end w-50">
                                        <span class="dec" id="decrease-children">
                                            <i class="fa fa-minus" aria-hidden="true"></i>
                                        </span>
                                        <input type="text" id="children-count" class="w-25 text-center border-0" value="0" readonly>
                                        <span class="inc" id="increase-children"> <i class="fa fa-plus" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                </div>
                                <!-- Confirm Button -->
                                <button id="confirm-selection" class="btn w-100">تأكيد</button>
                            </div>
                        </div>
                    </div>



                    <div class="col-md-2 position-relative">
                        <button id="calendar-btn" class="select-btn d-flex align-items-center justify-content-between">
                            <div class="text-muted">
                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/calender.svg') }}" alt="" />
                                <span id="selected-date" class="mx-2">اختر اليوم</span>
                            </div>
                            <i class="fas fa-chevron-down text-muted"></i>
                        </button>

                        <!-- Calendar Dropdown -->
                        <div id="calendar-container" class="calendar-container">
                            <div class="calendar-header">
                                <button id="prev-month" class="nav-btn">&lt;</button>
                                <span id="calendar-month-year"></span>
                                <button id="next-month" class="nav-btn">&gt;</button>
                            </div>
                            <table class="calendar-table">
                                <thead>
                                    <tr id="calendar-days-title"></tr>
                                </thead>
                                <tbody id="calendar-days"></tbody>
                            </table>
                        </div>
                    </div>


                    <div class="col-md-2">
                        <div class="dropdown" data-bs-auto-close="outside">
                            <button id="table-dropdown-btn"
                                class="select-btn dropdown-toggle d-flex align-items-center justify-content-between" type="button"
                                data-bs-toggle="dropdown">
                                <div class="text-muted">
                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="" />
                                    <span class="mx-2">اختر الطاولة</span>
                                </div>
                                <i class="fas fa-chevron-down text-muted"></i>

                            </button>
                            <div class="dropdown-menu p-3 text-center">
                                <div class="d-flex gap-2 mb-3" id="floor_partitions">
                                </div>
                                <!-- Confirm Button -->
                                <button id="confirm-table-selection" class="btn w-100 confirm-btn">تأكيد</button>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-2">
                        <div class="dropdown">
                            <button id="reservation-dropdown-btn"
                                class="select-btn dropdown-toggle d-flex align-items-center justify-content-between" type="button"
                                data-bs-toggle="dropdown">
                                <div class="text-muted">
                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/note.svg') }}" alt="" />
                                    <span class="mx-2"> نوع الحجز</span>
                                </div>
                                <i class="fas fa-chevron-down text-muted"></i>
                            </button>
                            <div class="dropdown-menu p-2 text-center">
                                <button class="dropdown-item select-option" data-value="بطلب وجبة" data-type="with">بطلب وجبة</button>
                                <button class="dropdown-item select-option" data-value="بدون طلب" data-type="without">بدون طلب</button>
                            </div>
                        </div>
                    </div>


                    <!-- Search Button -->
                    <div class="col-md-3">
                        <button id="search-tables-btn" class="btn search-btn w-100">بحث</button>
                    </div>
                </div>
                <div id="available-tables-section" class="mt-4 d-none">
                    <h5 class="fw-bold">الطاولات المتاحة</h5>
                    <div class="tables-carousel owl-carousel owl-theme">
                        <div class="item table-item" data-table="طاولة 1">
                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="Table 1" />
                            <p class="mb-0">طاولة 1</p>
                            <small class="text-muted">12:30 م </small>

                        </div>
                        <div class="item table-item" data-table="طاولة 2">
                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="Table 2" />
                            <p class="mb-0">طاولة 2</p>
                            <small class="text-muted">1:00 م </small>

                        </div>
                        <div class="item table-item" data-table="طاولة 3">
                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="Table 3" />
                            <p class="mb-0">طاولة 3</p>
                            <small class="text-muted">12:00 م </small>

                        </div>
                        <div class="item table-item" data-table="طاولة 4">
                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="Table 4" />
                            <p class="mb-0">طاولة 4</p>
                            <small class="text-muted">1:30 م </small>
                        </div>
                        <div class="item table-item" data-table="طاولة 5">
                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="Table 5" />
                            <p class="mb-0">طاولة 5</p>
                            <small class="text-muted">1:20 م </small>

                        </div>
                        <div class="item table-item" data-table="طاولة 6">
                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="Table 6" />
                            <p class="mb-0">طاولة 6</p>
                            <small class="text-muted">2:30 م </small>
                        </div>
                        <div class="item table-item" data-table="طاولة 7">
                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="Table 7" />
                            <p class="mb-0">طاولة 7</p>
                            <small class="text-muted">3:30 م </small>
                        </div>
                    </div>

                    <!-- Select Departure Time -->
                    <div class="mt-3">
                        <label for="departure-time" class="fw-bold">موعد المغادرة (اختياري)</label>
                        <select id="departure-time" class="form-select mt-3">
                            <option value="">اختر موعد المغادرة</option>
                            <option value="30 دقيقة">30 دقيقة</option>
                            <option value="ساعة">ساعة</option>
                            <option value="ساعتين">ساعتين</option>
                        </select>
                    </div>

                    <!-- Confirm Booking Button -->
                    <div class="mt-3 d-flex justify-content-end">
                        <button id="confirm-booking-btn" class="btn px-5">اكمل الحجز </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('website.table-reservation.js')