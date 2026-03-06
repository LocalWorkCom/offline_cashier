    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
        {{-- this for pickup  --}}
        <script>
            //fetch selects of country , city ,region

            // When a country is selected
            $('#countrySelect').on('change', function() {
                let countryId = $(this).val();

                // Fetch cities
                $.ajax({
                    url: '/get-cities/' + countryId,
                    type: 'GET',
                    success: function(data) {

                        $('#citySelect').html(
                            '<option selected disabled>@lang('header.choosecity')</option>');
                        $('#areaSelect').html(
                            '<option selected disabled>@lang('header.choosearea')</option>');

                        $.each(data, function(key, city) {
                            $('#citySelect').append('<option value="' + city.id + '">' +
                                city.name + '</option>');
                        });
                    }
                });
            });

            // When a city is selected
            $('#citySelect').on('change', function() {
                let cityId = $(this).val();

                // Fetch areas
                $.ajax({
                    url: '/get-areas/' + cityId,
                    type: 'GET',
                    success: function(data) {
                        $('#areaSelect').html(
                            '<option selected disabled>@lang('header.choosearea')</option>');

                        $.each(data, function(key, area) {
                            $('#areaSelect').append('<option value="' + area.id + '">' +
                                area.name + '</option>');
                        });
                    }
                });
            });

            let date = null;
            let time = null;
            let allow = null;
            let branchStatus = null;
            let branchOpen = null;

            // Handle "Use My Location" button click
            function fetchBranches(data) {
                $.ajax({
                    url: "{{ route('search.branches') }}",
                    method: 'POST',
                    data: {
                        ...data,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function(response) {
                        let branchContainer = $('.section-three');
                        branchId = response.id;
                        let branchContent = $('#content');
                        branchContent.empty();
                        branchContainer.removeClass('d-none');
                        console.log("Response:", response);
                        if (!response || (typeof response === 'object' && Object.keys(response).length === 0) ||
                            response === '{}') {
                            branchContent.append(
                                `<p class="text-muted text-center">@lang('header.No branches available in this area.')</p>`
                            );
                            $('#continue').addClass('d-none');

                            return;
                        }
                        // if (!response.empty) {
                        let branchName = response.name || "@lang('header.Unknown')";
                        let branchAddress = response.address || "@lang('header.Not available')";
                        branchStatus = response.is_open ? "@lang('header.Open')" : "@lang('header.Closed')";
                        let branchStatusClass = response.is_open ? "success" : "danger";
                        let branchAvailabilityClass = response.is_open ? "light-green" : "grey";
                        let branchAvailabilityText = response.is_open ? "text-success" : "text-muted";
                        branchOpen = response.is_open;

                        // Format working times - handle both string and array days
                        let workingTimesHTML = response.working_times && response.working_times.length > 0 ?
                            response.working_times.map(time => {
                                // Handle days whether it's a string or array
                                let daysDisplay = Array.isArray(time.days) ?
                                    time.days.join(', ') :
                                    time.days;

                                // Use the pre-formatted times from the response
                                let openingTime = time.opening_hour || '--';
                                let closingTime = time.closing_hour || '--';

                                // For Arabic: "09:00 صباحاً - 22:00 مساءً"
                                // For English: "09:00 AM - 10:00 PM"
                                return `<p class="text-muted">${daysDisplay}: ${openingTime} - ${closingTime}</p>`;
                            }).join('') :
                            `<p class="text-muted">@lang('header.Noworkinghoursavailable')</p>`;

                        allow = response.takeaway;
                        let branchHTML = `
                <h6 class="fw-bold">@lang('header.Pickupfrom')</h6>
                <div class="location border-red mb-1">
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold mt-2">
                            <i class="fas fa-map-marker-alt main-color mx-2"></i>${branchName}
                        </h6>
                        <span class="badge text-${branchStatusClass} mt-2">
                            ${branchStatus}
                        </span>
                    </div>
                    <p class="text-muted mx-2">${branchAddress}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="bg-${branchAvailabilityClass} p-2 ${branchAvailabilityText}">
                            @lang('header.Available'): (${response.services})
                        </p>
                        <p class="main-color fw-bold">
                            <span>@lang('header.Phone'):</span> ${response.phone}
                        </p>
                        <div class="text-muted">
                            ${workingTimesHTML}
                        </div>
                    </div>
                </div>
            `;

                        branchContent.append(branchHTML);
                        selectedBranch = response;

                        $('#continue').removeClass('d-none');

                        // } else {

                        // }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching branches:", error);
                        console.log("Response Text:", xhr.responseText);
                    }
                });
            }
            // Event Listener for "Use My Location"
            document.getElementById('useMyLocationReceiveBtn').addEventListener('click', function() {
                const latitude = getCookie('latitude');
                const longitude = getCookie('longitude');

                if (latitude && longitude) {
                    fetchBranches({
                        latitude,
                        longitude
                    });
                } else if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude;
                            const long = position.coords.longitude;

                            setBranchCookie(lat, long, false); // Do not update branch_id or reload

                            fetchBranches({
                                latitude: lat,
                                longitude: long
                            });
                        },
                        function(error) {
                            // ✅ Handle permission denied
                            if (error.code === error.PERMISSION_DENIED) {
                                const modalElement = document.getElementById('modal_access');
                                if (modalElement) {
                                    const modal = new bootstrap.Modal(modalElement);
                                    modal.show();
                                }
                            } else {
                                handleGeolocationError(error); // Optional: handle other errors
                            }
                        }
                    );
                } else {
                    // Geolocation not supported
                    const modalElement = document.getElementById('modal_access');
                    if (modalElement) {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                    }
                }

                function handleGeolocationError(error) {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            alert('@lang('header.permissiondenied')');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            alert('@lang('header.positionunavailable')');
                            break;
                        case error.TIMEOUT:
                            alert('@lang('header.requesttimeout')');
                            break;
                        default:
                            alert('@lang('header.unknownerror')');
                            break;
                    }
                }
            });

            // Event Listener for "Search" by country/city/region
            $('#searchButton').off('click').on('click', function() {
                let countryId = $('#countrySelect').val();
                let cityId = $('#citySelect').val();
                let regionId = $('#areaSelect').val();

                if (!countryId || !cityId || !regionId) {
                    // Use a more user-friendly notification than alert()
                    toastr.warning("@lang('header.Please select')");
                    return;
                }

                fetchBranches({
                    country_id: countryId,
                    city_id: cityId,
                    area_id: regionId
                });
            });
            const selectedDateSpan = document.getElementById("selected-date2");

            document.addEventListener("DOMContentLoaded", function() {
                const sectionOne = document.querySelector(".section-one");
                const sectionThree = document.querySelector(".section-three");
                const sectionFour = document.querySelector(".section-four");

                const approveBtn = sectionOne.querySelector(".btn"); // "موافق" button
                const continueBtn = sectionThree.querySelector("#continue"); // "تابع" button
                const previousBtn = document.getElementById("previousBtn"); // "السابق" button
                let LocationBtn = document.getElementById("useMyLocationReceiveBtn");
                let SearchBtn = document.getElementById("searchButton");

                // Show section three directly when clicking "موافق"
                approveBtn.addEventListener("click", function() {
                    sectionThree.classList.remove("d-none");
                    sectionThree.classList.add("d-block");
                });

                // Hide sections 1 & 3, show section 4 when clicking "تابع"
                continueBtn.addEventListener("click", function() {
                    console.log("Branch ID:", branchStatus, "not_allow:", allow);

                    if (!allow || !branchOpen || !selectedBranch) {
                        alert("لا يمكنك الطلب من هذا المطعم");
                        return;
                    }
                    sectionOne.classList.add("d-none");
                    sectionFour.classList.remove("d-none");
                    continueBtn.classList.add("d-none");
                    approveBtn.classList.add("d-none");
                    sectionFour.classList.add("d-block");
                    LocationBtn.classList.add("d-none");
                });

                // Show section 1 again, hide section 4 when clicking "السابق"
                previousBtn.addEventListener("click", function() {
                    sectionOne.classList.remove("d-none");
                    SearchBtn.classList.remove("d-none");
                    sectionThree.classList.remove("d-none");
                    continueBtn.classList.remove("d-none");
                    sectionFour.classList.add("d-none");
                });


                document.querySelectorAll(".dropdown-menu").forEach(menu => {
                    menu.addEventListener("click", function(event) {
                        event.stopPropagation();
                    });
                });
                document.querySelectorAll(".dropdown").forEach(dropdown => {
                    const dropdownBtn = dropdown.querySelector(".dropdown-toggle");
                    const confirmBtn = dropdown.querySelector(
                        ".confirm-btn"); // Add "confirm-btn" class to your confirm buttons

                    if (confirmBtn) {
                        confirmBtn.addEventListener("click", function() {
                            let dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(dropdownBtn);
                            dropdownInstance.hide();
                        });
                    }
                });
                const calendarBtn = document.getElementById("calendar-btn2");
                const calendarContainer = document.getElementById("calendar-container2");
                const prevMonthBtn = document.getElementById("prev-month2");
                const nextMonthBtn = document.getElementById("next-month2");
                const monthYearDisplay = document.getElementById("calendar-month-year2");
                const calendarDaysTitle = document.getElementById("calendar-days-title2");
                const calendarDays = document.getElementById("calendar-days2");

                const arabicDays = ["السبت", "الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة"];

                let currentDate = new Date();
                let currentMonth = currentDate.getMonth();
                let currentYear = currentDate.getFullYear();

                function generateCalendar(month, year) {
                    calendarDays.innerHTML = "";
                    calendarDaysTitle.innerHTML = "";

                    arabicDays.forEach(day => {
                        const th = document.createElement("th");
                        th.textContent = day;
                        calendarDaysTitle.appendChild(th);
                    });

                    const firstDay = new Date(year, month, 1).getDay();
                    const totalDays = new Date(year, month + 1, 0).getDate();

                    let adjustedFirstDay = (firstDay + 1) % 7;
                    let row = document.createElement("tr");

                    for (let i = 0; i < adjustedFirstDay; i++) {
                        row.appendChild(document.createElement("td"));
                    }

                    for (let day = 1; day <= totalDays; day++) {
                        const td = document.createElement("td");
                        td.textContent = day;

                        // Create a Date object for the current cell
                        const cellDate = new Date(year, month, day);
                        const today = new Date();
                        today.setHours(0, 0, 0, 0); // reset to start of today for accurate comparison

                        if (cellDate < today) {
                            td.classList.add("disabled-date"); // Add class for styling
                            td.style.color = "gray"; // Optional: visual hint
                            td.style.pointerEvents = "none"; // Disable click
                        } else {
                            td.addEventListener("click", function() {
                                //selectedDateSpan.textContent = `${year}-${month + 1}-${day}`;
                                selectedDateSpan.textContent =
                                    `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                                calendarContainer.style.display = "none";
                            });
                        }

                        row.appendChild(td);

                        if ((adjustedFirstDay + day) % 7 === 0) {
                            calendarDays.appendChild(row);
                            row = document.createElement("tr");
                        }
                    }


                    if (row.children.length > 0) {
                        calendarDays.appendChild(row);
                    }

                    monthYearDisplay.textContent = `${year} - ${month + 1}`;
                }

                calendarBtn.addEventListener("click", function(e) {
                    calendarContainer.style.display =
                        calendarContainer.style.display === "block" ? "none" : "block";
                    generateCalendar(currentMonth, currentYear);
                    e.stopPropagation();
                });

                prevMonthBtn.addEventListener("click", function() {
                    currentMonth--;
                    if (currentMonth < 0) {
                        currentMonth = 11;
                        currentYear--;
                    }
                    generateCalendar(currentMonth, currentYear);
                });

                nextMonthBtn.addEventListener("click", function() {
                    currentMonth++;
                    if (currentMonth > 11) {
                        currentMonth = 0;
                        currentYear++;
                    }
                    generateCalendar(currentMonth, currentYear);
                });

                document.addEventListener("click", function(e) {
                    if (!calendarContainer.contains(e.target) && e.target !== calendarBtn) {
                        calendarContainer.style.display = "none";
                    }
                });

                generateCalendar(currentMonth, currentYear);

            });
            const searchTimeBtn = document.getElementById("search-time-btn");
            const availableTimesSection = document.getElementById("available-times-section");
            const confirmrecieveBtn = document.getElementById("confirm-recieve-btn");
            const timesCarousel = document.querySelector(".times-carousel");
            const recieveNow = document.getElementById("recieve-now");
            // const selectedDateSpan = document.getElementById("selected-date2");
            let selectedTime = null;
            let today = new Date();
            today.setHours(0, 0, 0, 0);
            searchTimeBtn.addEventListener("click", function() {
                const selectedDate = selectedDateSpan.textContent.trim();

                // Regular expression to match YYYY-MM-DD format
                const dateRegex = /^\d{4}-\d{1,2}-\d{1,2}$/;

                // Validate format and check if it's a real date
                if (!dateRegex.test(selectedDate) || isNaN(new Date(selectedDate).getTime())) {
                    alert("يرجى اختيار تاريخ صحيح بصيغة YYYY-MM-DD قبل البحث عن الأوقات المتاحة.");
                    return;
                }


                const selected = new Date(selectedDate);
                selected.setHours(0, 0, 0, 0);

                if (selected < today) {
                    alert("لا يمكن اختيار تاريخ سابق لتاريخ اليوم.");
                    return;
                }


                // Clear previous content
                timesCarousel.innerHTML = '';
                availableTimesSection.classList.add("d-none");
                recieveNow.classList.add("d-none");

                $.ajax({
                    url: "{{ route('search.branches.checkAvailability') }}",
                    method: 'POST',
                    data: {
                        date: selectedDate,
                        branch_id: branchId, // Make sure branchId is defined
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        // Show loading indicator if needed
                        searchTimeBtn.disabled = true;
                    },
                    success: function(response) {
                        console.log("Response:", response);

                        if (response.success === true) {
                            displayTimeSlots(response, selectedDate);
                        } else {
                            showNoTimesAvailable();
                        }
                    },
                    error: function(xhr, status, error) {
                        const response = JSON.parse(xhr.responseText);
                        const message = response.message;
                        console.error("Error fetching branches:", error);
                        showNoTimesAvailable(message);
                    },
                    complete: function() {
                        searchTimeBtn.disabled = false;
                    }
                });
            });

            function displayTimeSlots(response, selectedDate) {
                // Clear previous content
                timesCarousel.innerHTML = '';

                const timeSlots = response.data.time_slots;
                const date = response.data.date;

                timeSlots.forEach(slot => {
                    const timeItem = document.createElement('div');
                    timeItem.className = 'item time-item';
                    timeItem.dataset.start = slot.start;
                    timeItem.dataset.end = slot.end;
                    timeItem.dataset.display = slot.display;

                    timeItem.innerHTML = `
            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/clock.svg') }}" alt="${slot.display.split(' - ')[0]}" />
            <p class="mb-0">@lang('branch.timeslot')</p>
            <small class="text-muted">${slot.display.split(' - ')[0]}</small>
        `;
                    timesCarousel.appendChild(timeItem);
                });

                // Initialize Owl Carousel
                setTimeout(() => {
                    if ($.fn.owlCarousel) {
                        $(".times-carousel").trigger('destroy.owl.carousel');
                    }

                    if (timeSlots.length > 0) {
                        $(".times-carousel").owlCarousel({
                            items: 6,
                            loop: false,
                            dots: true,
                            nav: true,
                            margin: 10,
                            rtl: true,
                            responsive: {
                                0: {
                                    items: 2,
                                    nav: true,
                                    dots: false
                                },
                                600: {
                                    items: 3,
                                    nav: true
                                },
                                900: {
                                    items: 6,
                                    nav: true
                                }
                            }
                        });

                        $(".owl-prev > span").html('<i class="fas fa-chevron-right"></i>');
                        $(".owl-next > span").html('<i class="fas fa-chevron-left"></i>');

                        availableTimesSection.classList.remove("d-none");

                        let parts = selectedDate.split('-');
                        let selected = new Date(parts[0], parts[1] - 1, parts[2]);
                        selected.setHours(0, 0, 0, 0);

                        if (selected.getTime() === today.getTime()) {
                            recieveNow.classList.remove("d-none");
                        }
                    } else {
                        showNoTimesAvailable(response.message);
                    }
                }, 100);
            }


            function showNoTimesAvailable(message) {
                timesCarousel.innerHTML = '';
                availableTimesSection.classList.remove("d-none");
                recieveNow.classList.add("d-none");

                const noTimesMessage = document.createElement('p');
                noTimesMessage.className = 'text-muted text-center';
                noTimesMessage.textContent = message;
                availableTimesSection.appendChild(noTimesMessage);
            }

            // Handle time slot selection
            $(document).on('click', '.time-item', function() {
                $('.time-item').removeClass('selected-time');
                $(this).addClass('selected-time');
                selectedTime = $(this).data('display');
                console.log("Selected Time:", selectedTime);
            });

            // Handle immediate pickup checkbox
            $('#recieve-now input').change(function() {
                if ($(this).is(':checked')) {
                    $('.time-item').removeClass('selected-time');
                    selectedTime = 'now';
                    availableTimesSection.classList.add("d-none");
                } else {
                    availableTimesSection.classList.remove("d-none");
                }
            });

            confirmrecieveBtn.addEventListener("click", function() {
                // Validate selection
                if (!selectedTime && !$('#recieve-now input').is(':checked')) {
                    alert("يرجى اختيار التوقيت قبل التأكيد.");
                    return;
                }

                // Get selected date and time
                const selectedDate = selectedDateSpan.textContent.trim();
                let pickupTime;

                if ($('#recieve-now input').is(':checked')) {
                    // For immediate pickup, use current time
                    const now = new Date();
                    pickupTime =
                        `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
                } else {
                    // For scheduled pickup, use selected time (format: "HH:MM")
                    pickupTime = selectedTime.split(' - ')[0]; // Extract start time from "HH:MM - HH:MM"
                }

                // Show loading indicator
                confirmrecieveBtn.disabled = true;
                confirmrecieveBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري التحقق...';

                // Check capacity
                $.ajax({
                    url: "{{ route('check.order.capacity') }}",
                    method: 'POST',
                    data: {
                        branch_id: branchId,
                        date: selectedDate,
                        pickup_time: pickupTime,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response) {
                            if (response.available) {

                                if (branchId == getCookie('branch_id')) {
                                    // Clear any existing delivery/dine-in data
                                    ['TableReservation', 'addressDetails', 'authaddress'].forEach(
                                        key => {
                                            if (localStorage.getItem(key)) {
                                                localStorage.removeItem(key);
                                                console.log(`Removed ${key} from localStorage`);
                                            }
                                        });
                                } else {
                                    ['TableReservation', 'addressDetails', 'authaddress', 'cart'].forEach(
                                        key => {
                                            if (localStorage.getItem(key)) {
                                                localStorage.removeItem(key);
                                                console.log(`Removed ${key} from localStorage`);
                                            }
                                        });
                                }


                                // Store takeaway details
                                const takeawayDetails = {
                                    branch_id: branchId,
                                    branch_name: response.branch.name,
                                    branch_address: response.branch.address,
                                    branch_phone: response.branch.phone,
                                    selected_date: selectedDate,
                                    selected_time: pickupTime,
                                    order_type: 'takeaway' // Explicitly mark as takeaway order
                                };

                                localStorage.setItem('takeaway_details', JSON.stringify(takeawayDetails));
                                setCookie('branch_takaway', 'true', 7);
                                setCookie('branch_id', branchId, 7);
                                let cart = JSON.parse(localStorage.getItem('cart'));

                                if (cart && typeof cart === 'object') {
                                    cart.order_type = 'Takeaway';
                                    localStorage.setItem('cart', JSON.stringify(cart));
                                }


                                // Clear session data on server
                                fetch('/forget-session', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector(
                                                'meta[name="csrf-token"]').content,
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({})
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            console.log('Session data cleared successfully');
                                        }
                                    })
                                    .catch(error => console.error('Error clearing session:', error));

                                // Close modal and redirect
                                $("#branchSelectionModal").modal("hide");
                                window.location.href = "{{ route('menu') }}";
                            } else {
                                // Capacity full
                                alert("عذراً، لا يوجد أماكن متاحة في هذا التوقيت. يرجى اختيار وقت آخر.");
                            }
                        } else {
                            alert("حدث خطأ أثناء التحقق من السعة. يرجى المحاولة مرة أخرى.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error checking capacity:", error);
                        alert("حدث خطأ في الاتصال بالخادم. يرجى المحاولة مرة أخرى.");
                    },
                    complete: function() {
                        // Reset button state
                        confirmrecieveBtn.disabled = false;
                        confirmrecieveBtn.textContent = "تأكيد";
                    }
                });
            });
        </script>
    @endpush

