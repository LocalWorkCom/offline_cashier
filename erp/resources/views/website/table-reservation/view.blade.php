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
                                class="select-btn d-flex justify-content-between align-items-center dropdown-toggle"
                                type="button" data-bs-toggle="dropdown">
                                <div class="text-muted">
                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}" alt="" />
                                    <span class="mx-2">عدد الأفراد</span>
                                </div>
                                <i class="fas fa-chevron-down text-muted"></i>
                            </button>
                            <div class="dropdown-menu p-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <button id="family-btn" class="normal-btn w-50 rounded-pill">
                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/family.svg') }}" alt="" />
                                        <span> عائلة </span>
                                    </button>
                                    <button id="individuals-btn" class="normal-btn w-50 rounded-pill">
                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/people.svg') }}" alt="" />
                                        أشخاص
                                    </button>
                                </div>

                                <div id="family-options">
                                    <div class="mb-3 d-flex justify-content-between">
                                        <h5 class="fw-bold">عدد الأشخاص</h5>
                                        <div class="d-flex align-items-center justify-content-end w-50">
                                            <span class="dec" id="decrease-adults">
                                                <i class="fa fa-minus"></i>
                                            </span>
                                            <input type="text" id="adults-count" class="w-25 text-center border-0"
                                                value="0" readonly>
                                            <span class="inc" id="increase-adults">
                                                <i class="fa fa-plus"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3 d-flex justify-content-between">
                                        <h5 class="fw-bold">عدد الأطفال</h5>
                                        <div class="d-flex align-items-center justify-content-end w-50">
                                            <span class="dec" id="decrease-children">
                                                <i class="fa fa-minus"></i>
                                            </span>
                                            <input type="text" id="children-count" class="w-25 text-center border-0"
                                                value="0" readonly>
                                            <span class="inc" id="increase-children">
                                                <i class="fa fa-plus"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Individuals Counters (Hidden Initially) -->
                                <div id="individuals-options" style="display: none;">
                                    <div class="mb-3 d-flex justify-content-between">
                                        <h5 class="fw-bold">عدد الرجال</h5>
                                        <div class="d-flex align-items-center justify-content-end w-50">
                                            <span class="dec" id="decrease-men">
                                                <i class="fa fa-minus"></i>
                                            </span>
                                            <input type="text" id="men-count" class="w-25 text-center border-0"
                                                value="0" readonly>
                                            <span class="inc" id="increase-men">
                                                <i class="fa fa-plus"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3 d-flex justify-content-between">
                                        <h5 class="fw-bold">عدد النساء</h5>
                                        <div class="d-flex align-items-center justify-content-end w-50">
                                            <span class="dec" id="decrease-women">
                                                <i class="fa fa-minus"></i>
                                            </span>
                                            <input type="text" id="women-count" class="w-25 text-center border-0"
                                                value="0" readonly>
                                            <span class="inc" id="increase-women">
                                                <i class="fa fa-plus"></i>
                                            </span>
                                        </div>
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
                                <span id="selected-date" class="mx-2">@php($today = date('Y-m-d'))
                                    {{ $today }}</span>
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
                                class="select-btn dropdown-toggle d-flex align-items-center justify-content-between"
                                type="button" data-bs-toggle="dropdown">
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
                                class="select-btn dropdown-toggle d-flex align-items-center justify-content-between"
                                type="button" data-bs-toggle="dropdown">
                                <div class="text-muted">
                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/note.svg') }}" alt="" />
                                    <span class="mx-2" id="reserv_type"> نوع الحجز</span>
                                </div>
                                <i class="fas fa-chevron-down text-muted"></i>
                            </button>
                            <div class="dropdown-menu p-2 text-center">
                                <button class="dropdown-item select-option" data-value="بطلب وجبة"
                                    data-type="with">بطلب وجبة</button>
                                <button class="dropdown-item select-option" data-value="بدون طلب"
                                    data-type="without">بدون طلب</button>
                            </div>
                        </div>
                    </div>

                    <!-- Search Button -->
                    <div class="col-md-3">
                        <button id="search-tables-btn" class="btn search-btn w-100">بحث</button>
                    </div>
                </div>
                <div id="available-tables-section" class="mt-4 d-none">
                    <h5 class="fw-bold" id="available-text">الطاولات المتاحة</h5>
                    <div class="tables-carousel owl-carousel owl-theme">
                        <!-- Dynamic table items will be injected here -->
                    </div>
                
                    <!-- Select Departure Time -->
                    <div class="mt-3" id="leave-div">
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
                        <button id="confirm-booking-btn" class="btn btn-green px-5">اكمل الحجز </button>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>


@include('website.table-reservation.js')
