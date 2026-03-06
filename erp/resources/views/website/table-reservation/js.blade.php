<script>
    document.addEventListener("DOMContentLoaded", function() {


        let branch_id = getCookie('branch_id');
        let current_date = new Date();
        let day_number = current_date.getDay();
        let day_name = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][new Date().getDay()];

        if (branch_id != 0) {
            $.ajax({
                url: "{{ route('get-bracnh-info') }}",
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    branch_id: branch_id
                },
                success: function(response) {

                    if (response) {
                        let opening_hour = response.branch_time_day?.opening_hour_convert ||
                            'غير متاح';
                        let closing_hour = response.branch_time_day?.closing_hour_convert ||
                            'غير متاح';
                        let branch_status = response.is_open ? "مفتوح" : "مغلق";
                        let is_branch_takeaway = response.is_open ? "استلام" : "";
                        let is_branch_delivery = response.is_open ? "توصيل" : "";
                        let is_table_reservation = response.is_table_reservation ? "حجز طاولة" : "";
                        let view_table_reservation = document.getElementById(
                            "view_table_reservation");
                        if (is_table_reservation == 0) {
                            view_table_reservation.removeAttribute("data-bs-target");
                            view_table_reservation.querySelector("span").textContent =
                                "لا يوجد حجز طاولة"
                        }
                        $(`#branch_information`).empty();
                        $(`#branch_information`).append(`
                          <div class="d-flex justify-content-between">
                          <h6 class="fw-bold mt-2">
                              <i class="fas fa-map-marker-alt main-color mx-2"></i>${response.name_site}
                          </h6>
                          <span class="badge text-success mt-2">${branch_status}</span>
                          </div>
                          <p class="text-muted mx-2">${response.address_site}</p>
                          <div class="d-flex justify-content-between align-items-center">
                          <p class="bg-light-green p-2 text-success"> متاح: (${is_branch_delivery} , ${is_table_reservation} , ${is_branch_takeaway})</p>
                          <p class="main-color fw-bold">
                              <span>رقم الهاتف :</span> ${response.phone}
                          </p>
                          <p class="text-muted">
                              ${opening_hour} : ${closing_hour} - ${day_name}
                          </p>
                          </div>
                      `);

                        if (response.get_floor_partitions && response.get_floor_partitions.length >
                            0) {
                            response.get_floor_partitions.forEach(floor => {
                                if (floor.floor_partitions && floor.floor_partitions
                                    .length > 0) {
                                    floor.floor_partitions.forEach(partition => {
                                        // Check if partition contains tables
                                        if (partition.tables && partition.tables
                                            .length > 0) {
                                            $('#floor_partitions').append(`
                                              <button id="window-btn-${partition.id}" data-name="${partition.name}" data-value="${partition.id}" class="normal-btn rounded-pill">
                                                  <img src="{{ asset('front/AlKout-Resturant/${partition.image}') }}" alt="" />
                                                  <span>${partition.name}</span> <!-- Partition Name -->
                                              </button>
                                          `);
                                        }
                                    });
                                }
                            });
                        }

                    }
                },
                error: function() {
                    //////////
                }
            });
        }

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
        const calendarBtn = document.getElementById("calendar-btn");
        const calendarContainer = document.getElementById("calendar-container");
        const selectedDateSpan = document.getElementById("selected-date");
        const prevMonthBtn = document.getElementById("prev-month");
        const nextMonthBtn = document.getElementById("next-month");
        const monthYearDisplay = document.getElementById("calendar-month-year");
        const calendarDaysTitle = document.getElementById("calendar-days-title");
        const calendarDays = document.getElementById("calendar-days");

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

                // Create date object for this cell
                const cellDate = new Date(year, month, day);

                // Reset time for accurate comparison
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                cellDate.setHours(0, 0, 0, 0);

                if (cellDate < today) {
                    td.classList.add('disabled');
                } else {
                    td.addEventListener("click", function() {
                        //selectedDateSpan.textContent = `${year}-${month + 1}-${day}`;
                        selectedDateSpan.textContent =
                            `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        calendarContainer.style.display = "none";
                    });
                    td.style.cursor = 'pointer';
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

        let adultsCount = 0;
        let childrenCount = 0;
        let menCount = 0;
        let womenCount = 0;
        let selectedType = "عائلة"; // Default is Family
        let selectedTypeVal = "family";
        let partitionName = "";
        let partitionId = "";
        let notes = "";

        const peopleDropdownBtn = document.getElementById("people-dropdown-btn");
        const familyBtn = document.getElementById("family-btn");
        const individualsBtn = document.getElementById("individuals-btn");
        const confirmSelection = document.getElementById("confirm-selection");

        // Family Inputs
        const adultsInput = document.getElementById("adults-count");
        const childrenInput = document.getElementById("children-count");
        const increaseAdults = document.getElementById("increase-adults");
        const decreaseAdults = document.getElementById("decrease-adults");
        const increaseChildren = document.getElementById("increase-children");
        const decreaseChildren = document.getElementById("decrease-children");

        // Individuals Inputs
        const menInput = document.getElementById("men-count");
        const womenInput = document.getElementById("women-count");
        const increaseMen = document.getElementById("increase-men");
        const decreaseMen = document.getElementById("decrease-men");
        const increaseWomen = document.getElementById("increase-women");
        const decreaseWomen = document.getElementById("decrease-women");

        // Sections
        const familyOptions = document.getElementById("family-options");
        const individualsOptions = document.getElementById("individuals-options");

        function updateCounts() {
            if (selectedType === "عائلة") {
                adultsInput.value = adultsCount;
                childrenInput.value = childrenCount;
            } else {
                menInput.value = menCount;
                womenInput.value = womenCount;
            }
        }

        // Counter Functionality
        function updateCounter(buttonId, inputId, isIncrement, type) {
            document.getElementById(buttonId).addEventListener("click", function() {
                let input = document.getElementById(inputId);
                let value = parseInt(input.value, 10);
                if (isIncrement) {
                    value++;
                } else if (value > 0) {
                    value--;
                }

                input.value = value;

                // Update corresponding variable
                if (type === "adults") adultsCount = value;
                if (type === "children") childrenCount = value;
                if (type === "men") menCount = value;
                if (type === "women") womenCount = value;
            });
        }

        // Family Counters
        updateCounter("increase-adults", "adults-count", true, "adults");
        updateCounter("decrease-adults", "adults-count", false, "adults");
        updateCounter("increase-children", "children-count", true, "children");
        updateCounter("decrease-children", "children-count", false, "children");

        // Individuals Counters
        updateCounter("increase-men", "men-count", true, "men");
        updateCounter("decrease-men", "men-count", false, "men");
        updateCounter("increase-women", "women-count", true, "women");
        updateCounter("decrease-women", "women-count", false, "women");

        // Switch to Family
        familyBtn.addEventListener("click", () => {
            selectedType = "عائلة";
            selectedTypeVal = "family";
            familyBtn.classList.add("selected-btn");
            familyBtn.classList.remove("normal-btn");
            individualsBtn.classList.remove("selected-btn");
            individualsBtn.classList.add("normal-btn");

            familyOptions.style.display = "block";
            individualsOptions.style.display = "none";
            $('#men-count, #women-count').val(0);
            womenCount = 0;
            menCount = 0;
            updateCounts();
        });

        // Switch to Individuals
        individualsBtn.addEventListener("click", () => {
            selectedType = "أشخاص";
            selectedTypeVal = "person";
            individualsBtn.classList.add("selected-btn");
            individualsBtn.classList.remove("normal-btn");
            familyBtn.classList.remove("selected-btn");
            familyBtn.classList.add("normal-btn");

            familyOptions.style.display = "none";
            individualsOptions.style.display = "block";
            ('#adults-count, #children-count').val(0);
            adultsCount = 0;
            childrenCount = 0;
            updateCounts();
        });

        // Confirm Selection
        confirmSelection.addEventListener("click", () => {
            let text = "";
            if (selectedType === "عائلة") {
                text = `${selectedType}: ${adultsCount} بالغ, ${childrenCount} طفل`;
            } else {
                text = `${selectedType}: ${menCount} رجل, ${womenCount} امرأة`;
            }

            peopleDropdownBtn.innerHTML =
                `<small class="text-muted">${text}</small>
                                                                            <i class="fas fa-chevron-down text-muted"></i>`;

            const dropdownMenu = document.querySelector(".dropdown .dropdown-menu");
            const dropdownButton = document.getElementById("people-dropdown-btn");
            bootstrap.Dropdown.getInstance(dropdownButton)?.hide();
        });

        // Initialize Counts on Load
        updateCounts();

        let selectedTableLocation = "اختر الطاولة";

        const tableDropdownBtn = document.getElementById("table-dropdown-btn");
        const confirmTableSelection = document.getElementById("confirm-table-selection");

        document.getElementById("floor_partitions").addEventListener("click", (event) => {
            let selectedBtn = event.target.closest("button");
            if (selectedBtn) {
                let locationText = selectedBtn.querySelector("span").innerText; // Get partition name
                selectTableLocation(selectedBtn, locationText);
            }
        });

        function selectTableLocation(selectedBtn, locationText) {
            selectedTableLocation = locationText;

            // Remove selection from all buttons
            document.querySelectorAll("#floor_partitions button").forEach(btn => {
                btn.classList.remove("selected-btn");
                btn.classList.add("normal-btn");
            });

            // Highlight selected button
            selectedBtn.classList.remove("normal-btn");
            selectedBtn.classList.add("selected-btn");

            partitionId = parseInt(selectedBtn.dataset.value, 10);
            partitionName = locationText;
        }



        confirmTableSelection.addEventListener("click", function() {
            tableDropdownBtn.innerHTML = `
            <div class="text-muted">
                <img src="SiteAssets/images/table.svg" alt="" />
                <span class="mx-2">${selectedTableLocation}</span>
            </div>
            <i class="fas fa-chevron-down text-muted"></i>`;

            let dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(tableDropdownBtn);
            dropdownInstance.hide();
        });

        const dropdownBtn = document.getElementById("reservation-dropdown-btn");
        const options = document.querySelectorAll(".select-option");
        let reserv_type = "";
        options.forEach(option => {
            option.addEventListener("click", function() {


                dropdownBtn.innerHTML = `
          <div class="text-muted">
          <img src="SiteAssets/images/table.svg" alt="" />
          <span class="mx-2">${this.dataset.value}</span>
          </div>
          <i class="fas fa-chevron-down text-muted"></i>`;
                reserv_type = this.dataset.value;
                let dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(dropdownBtn);
                dropdownInstance.hide();
            });
        });

        const searchBtn = document.getElementById("search-tables-btn");
        const availableTablesSection = document.getElementById("available-tables-section");
        const tableItems = document.querySelectorAll(".table-item");
        const confirmBookingBtn = document.getElementById("confirm-booking-btn");

        let selectedTable = null;
        let selectedTableId = null;


        searchBtn.addEventListener("click", function() {
            let count = adultsCount + childrenCount + womenCount + menCount;

            // Get selected date
            let selectedDate = selectedDateSpan.textContent.trim();

            // Check reservation type
            let reservationType = $("#reserv_type").text().trim();

            // Validate inputs
            if (count === 0) {
                alert("يرجى اختيار عدد الأفراد."); // Please select number of people.
                return;
            }
            console.log(partitionId);

            if (!selectedDate || selectedDate === "") {
                alert("يرجى اختيار التاريخ."); // Please select a date.
                return;
            }

            if (typeof partitionId === "undefined" || partitionId === null || partitionId === "") {
                alert("يرجى اختيار الطاولة."); // Please select a table/floor partition.
                return;
            }

            if (!reserv_type) {
                alert("يرجى اختيار نوع الحجز."); // Please select reservation type.
                return;
            }

            // If all validations pass, continue to AJAX

            let table_partition = Number.isInteger(partitionId) ? partitionId : 0;

            $.ajax({
                url: "{{ url('getSession') }}/" + count + "/" + branch_id + "/" +
                    selectedDateSpan.textContent + "/" + table_partition + "/encode",
                method: "GET",
                success: function(tables) {
                    console.log("Fetched tables:", tables);

                    const carouselContainer = $(".tables-carousel");
                    carouselContainer.empty(); // Clear old items

                    // Add new table items for each available time
                    if (tables.length > 0) { // Check if the tables array is not empty
                        tables.forEach(table => {
                            // Repeat the table for each available time
                            if (table.available_times && table.available_times
                                .length > 0) {
                                table.available_times.forEach(time => {
                                    // Append the table item with its time
                                    carouselContainer.append(`
                                <div class="item table-item" data-table="${table.table_name}-${time}" data-id="${table.table_id}">
                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="${table.table_name}" />
                                    <p class="mb-0">${table.table_name}</p>
                                    <small class="text-muted">${convertTo12Hour(time)}</small>
                                </div>
                            `);
                                });
                            }
                        });
                        $('#available-text').text('االطاولات المتاحة');
                        $('#leave-div').show();
                    } else {
                        $('#available-text').text('No Table availables');
                        $('#leave-div').hide();
                        console.log('No tables available.');
                    }

                    // Show section
                    availableTablesSection.classList.remove("d-none");

                    // Destroy previous OwlCarousel instance if exists
                    if (carouselContainer.hasClass("owl-loaded")) {
                        carouselContainer.trigger('destroy.owl.carousel');
                        carouselContainer.removeClass("owl-loaded");
                        carouselContainer.find('.owl-stage-outer').children().unwrap();
                    }

                    // Initialize OwlCarousel
                    carouselContainer.owlCarousel({
                        items: 6,
                        loop: false,
                        dots: true,
                        nav: true,
                        margin: 10,
                        pagination: false,
                        autoplay: false,
                        autoplaySpeed: 1000,
                        autoplayTimeout: 3000,
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

                    // Attach click events
                    $(".table-item").on("click", function() {
                        $(".table-item").removeClass("selected-table");
                        $(this).addClass("selected-table");
                        selectedTable = $(this).data("table");
                        selectedTableId = $(this).data("id");
                        console.log("Selected table:", selectedTable);
                    });
                },
                error: function() {
                    console.error("Failed to fetch tables.");
                }
            });
        });



        confirmBookingBtn.addEventListener("click", function() {

            if (!selectedTable) {
                alert("يرجى اختيار طاولة قبل التأكيد.");
                return;
            }
            let reservationType = "";
            if (reserv_type == "بطلب وجبة") {
                reservationType = 'with';
            } else {
                reservationType = 'without';

            }

            const [tableName, time] = selectedTable.split('-');

            $("#table-reservation").modal("hide");
            var departure_time = $('#departure-time').val();
            // Example usage:
            const departureValue = document.getElementById("departure-time").value; // e.g., "ساعة"
            let resultTime = "";
            if (departureValue == "") {
                resultTime = "";
            } else {
                resultTime = addDepartureTimeToTime(time, departureValue);

            }
            let tableImage = "";
            let TableReservation;
            TableReservation = {
                "personal": {
                    "adult": selectedTypeVal == "family" ? adultsCount : 0,
                    "kids": selectedTypeVal == "family" ? childrenCount : 0,
                    "women": selectedTypeVal == "person" ? womenCount : 0,
                    "men": selectedTypeVal == "person" ? menCount : 0,
                    "type": selectedTypeVal
                },
                "date": selectedDateSpan.textContent,
                "partitionId": partitionId,
                "partitionName": partitionName,
                "tableId": selectedTableId,
                "tableName": tableName,
                "tableImage": tableImage,
                "tableSession": time,
                "reservType": reservationType,
                "leaveTime": resultTime,
                "notes": notes
            };

            localStorage.setItem('TableReservation', JSON.stringify(TableReservation));

            let cartUrl = "{{ route('cart') }}";
            let menuUrl = "{{ route('menu') }}";
            let confirmationUrl = "{{ route('table-reservation.confirmation') }}";

            console.log(reservationType);

            if (reservationType == "with") {
                const hasCart = localStorage.getItem('cart');

                if (hasCart) {
                    window.location.href = cartUrl;

                } else {
                    window.location.href = menuUrl;

                }

            } else {
                window.location.href = confirmationUrl;

            }

        });
    });


    var table_reservation_deposit =
        {{ getBranchSettings($branchId, 'table_reservation_deposit') ? getBranchSettings($branchId, 'table_reservation_deposit') : 0 }};
    var currency_symbol = "{{ $currency }}";

    function safeText(id, value) {
        const el = document.getElementById(id);
        if (el && value) el.textContent = value;
    }
    ////new  for confirmation blade////
    document.addEventListener("DOMContentLoaded", function() {
        // ✅ Always run AJAX
        $.ajax({
            url: "{{ route('get-bracnh-info') }}",
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                branch_id: getCookie('branch_id')
            },
            success: function(data) {
                if (data) {
                    if (document.getElementById('address')) {
                        document.getElementById('address').textContent = data.address;
                    }
                    if (document.getElementById('detail')) {
                        document.getElementById('detail').textContent = data.name_site;
                    }
                }
            },
            error: function() {}
        });

        const data = JSON.parse(localStorage.getItem('TableReservation'));
        if (!data) return;



        function safeImage(id, url) {
            const el = document.getElementById(id);
            if (el && url) el.src = url;
        }
        $('#reservation-card').show();

        // Safely fill each field
        safeImage('tableImage', data.tableImage);
        safeText('tableName', `طاولة ${data.tableName}`);
        safeText('reservType', data.reservType === "without" ? "(بدون طلب)" : "(مع طلب)");
        safeText('tableSession', convertTo12Hour(data.tableSession));
        safeText('leaveTime', (data.leaveTime) ? convertTo12Hour(data.leaveTime) : "---------");
        safeText('partitionName', data.partitionName);
        safeText('notes', data.notes);

        // Reservation date
        if (data.date && document.getElementById('reservationDate')) {
            const date = new Date(data.date);
            const formattedDate = date.toLocaleDateString('ar-EG', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            safeText('reservationDate', formattedDate);
        }

        // People count
        if (data.personal && document.getElementById('peopleCount')) {
            const total = data.personal.adult + data.personal.kids + data.personal.women + data.personal.men;
            let peopleText = `${total} شخص`;
            if (data.personal.kids > 0) {
                peopleText += ` , ${data.personal.kids} طفل`;
            }
            safeText('peopleCount', peopleText);
        }

        safeText('payment-value', `${table_reservation_deposit} ${currency_symbol} `);
    });

    function convertTo12Hour(timeStr) {
        const [hourStr, minuteStr] = timeStr.split(/[:.]/);
        let hour = parseInt(hourStr, 10);
        const minute = parseInt(minuteStr, 10);
        const isPM = hour >= 12;

        if (hour > 12) hour -= 12;
        if (hour === 0) hour = 12;

        return `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')} ${isPM ? 'م' : 'ص'}`;
    }
    document.addEventListener("DOMContentLoaded", function() {

        const checkoutButton = $('#checkout-btn'); // Replace with your actual checkout button ID

        checkoutButton.on('click', function(e) {
            const hasReserv = localStorage.getItem('TableReservation');
            const cart = localStorage.getItem('cart');

            if (hasReserv && !cart) {
                e.preventDefault(); // Prevent default form submission or navigation
                // updateCartBeforeCheckout();

                const isAuthenticated = @json(auth('client')->check());
                console.log(isAuthenticated);

                if (!isAuthenticated) {
                    const loginModal = document.querySelector('#loginModal');
                    if (loginModal) {
                        const modalInstance = new bootstrap.Modal(loginModal);
                        $('#msg-error').show();
                        modalInstance.show();
                    } else {}

                } else {
                    window.location.href = "{{ route('table-reservation.checkout') }}";

                }
            }

        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        const hasCart = localStorage.getItem('cart');
        var payment_method_value;

        document.querySelectorAll('.payment-options').forEach(div => div.classList.add('d-none'));
        if (hasCart) {
            document.getElementById('payment-options-with').classList.remove('d-none');
        } else {
            document.getElementById('payment-options-without').classList.remove('d-none');
        }

        $('input[name=payment_method], input[name=payment_method2]').on('click', function() {
            if ($(this).is(':checked')) {
                payment_method_value = $(this).val();
                console.log(payment_method_value);
            }
        });

        $('#confirmTable').off('click').on('click', function() {

            const isPaymentSelected = $('input[name="payment_method2"]:checked').length > 0;
            const isPaymentSelected2 = $('input[name="payment_method"]:checked').length > 0;

            if (!isPaymentSelected && !isPaymentSelected2) {
                alert('يرجى اختيار طريقة الدفع لإتمام الطلب');
                e.preventDefault();
                return; // Make sure it doesn't continue
            }

            var url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    table_id: localData.tableId,
                    branch_id: "{{ $branchId }}",
                    floor_partition_id: localData.partitionId,
                    client_id: "{{ auth('client')->id() }}",
                    date: localData.date,
                    time_from: localData.tableSession,
                    time_to: localData.leaveTime,
                    reservation_type: localData.reservType,
                    adult: localData.personal.adult,
                    kids: localData.personal.kids,
                    men: localData.personal.men,
                    women: localData.personal.women,
                    personal_type: localData.personal.type,
                    notes: localData.notes,
                    payment_method: payment_method_value,
                    _token: $('meta[name="csrf-token"]').attr('content') // important
                },

                success: function(response) {
                    console.log(response);

                    if (response.success) {
                        if(response.online == true){
                            let baseUrl = "{{ route('myfatoorah-payment', ['__ID__','__TYPE__']) }}";
                            baseUrl = baseUrl.replace('__ID__', response.data.id).replace('__TYPE__', response.data.reservation_type);
                            window.location.href = baseUrl;
                            localStorage.removeItem('TableReservation');
                        }else{
                            $('#tableConfirmModal').modal('show');
                            $('#continue-order-btn').on('click', function() {
                                window.location.href =
                                    "{{ route('orders.tracking') }}";
                                localStorage.removeItem('TableReservation');

                            });
                        }

                        

                    } else {
                        // alert('❌ Reservation failed.');
                        let errorMsg = Array.isArray(response.message) ? response.message
                            .join('\n') : response.message;
                        alert('❌ ' + errorMsg);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('❌ Something went wrong. Check console.');
                }
            });
        });

        // document.addEventListener('DOMContentLoaded', function() {

        // });

        $('#confirmTable').prop('disabled', true);

        $('#agree-policy').on('change', function() {
            if ($(this).is(':checked')) {
                $('#confirmTable').prop('disabled', false);
            } else {
                $('#confirmTable').prop('disabled', true);
            }
        });
        $('#payment-options-without').on('change', 'input[name="payment_method"]', function() {

            $('#pay_deposit').show();
            if ($(this).is(':checked')) {
                if ($(this).val() == 'no_payment_required') {
                    safeText('payment-value', `0.0  ${currency_symbol}`);
                    safeText('payment-text', 'مبلغ الدفع');

                } else if ($(this).val() == 'deposit_required') {
                    safeText('payment-value', `${table_reservation_deposit}  ${currency_symbol}`);
                    safeText('payment-text', ' دفع عربون');

                } else {
                    $('#pay_deposit').hide();

                }
            }
        });

        const localData = JSON.parse(localStorage.getItem('TableReservation'));

    });

    function addDepartureTimeToTime(baseTimeStr, departureStr) {
        // Convert base time (e.g., "08:20:00") to a Date object
        const [hours, minutes, seconds] = baseTimeStr.split(":").map(Number);
        const baseDate = new Date();
        baseDate.setHours(hours, minutes, seconds || 0);

        // Convert departure string to minutes
        let addedMinutes = 0;
        if (departureStr.includes("30")) {
            addedMinutes = 30;
        } else if (departureStr.includes("ساعة")) {
            addedMinutes = 60;
        } else if (departureStr.includes("ساعتين")) {
            addedMinutes = 120;
        }

        // Add minutes to base date
        baseDate.setMinutes(baseDate.getMinutes() + addedMinutes);

        // Format back to HH:mm:ss
        const finalHours = String(baseDate.getHours()).padStart(2, "0");
        const finalMinutes = String(baseDate.getMinutes()).padStart(2, "0");
        const finalSeconds = String(baseDate.getSeconds()).padStart(2, "0");

        return `${finalHours}:${finalMinutes}:${finalSeconds}`;
    }

    function addNotes() {
        let notes = document.getElementById("notes").value;
        let tableReservation = JSON.parse(localStorage.getItem('TableReservation'));
        if (tableReservation) {
            tableReservation.notes = notes;
            localStorage.setItem('TableReservation', JSON.stringify(tableReservation));
        }
    }
</script>
