<script>
    document.addEventListener("DOMContentLoaded", function() {
        let barnch_id = getCookie('branch_id');
        let current_date = new Date();
        let day_number = current_date.getDay();
        let day_name = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][new Date().getDay()];

        let table_session = JSON.parse(`{!! json_encode(get_table_session(11, '2025-03-25', 5)) !!}`);
        console.log(table_session);


        if (barnch_id != 0) {
            $.ajax({
                url: "{{ route('get-bracnh-info') }}",
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    branch_id: barnch_id
                },
                success: function(response) {

                    if (response) {
                        let opening_hour = response.branch_time_day?.opening_hour_convert || 'غير متاح';
                        let closing_hour = response.branch_time_day?.closing_hour_convert || 'غير متاح';
                        let branch_status = response.is_open ? "مفتوح" : "مغلق";
                        let is_branch_takeaway = response.is_open ? "استلام" : "";
                        let is_branch_delivery = response.is_open ? "توصيل" : "";
                        let is_table_reservation = response.is_table_reservation ? "حجز طاولة" : "";

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

                        if (response.get_floor_partitions && response.get_floor_partitions.length > 0) {
                            response.get_floor_partitions.forEach(floor => {
                                if (floor.floor_partitions && floor.floor_partitions.length > 0) {
                                    floor.floor_partitions.forEach(partition => {
                                        // Check if partition contains tables
                                        if (partition.tables && partition.tables.length > 0) {
                                            $('#floor_partitions').append(`
                                                <button id="window-btn-${partition.id}" class="normal-btn rounded-pill">
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
            const confirmBtn = dropdown.querySelector(".confirm-btn"); // Add "confirm-btn" class to your confirm buttons

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
                td.addEventListener("click", function() {
                    selectedDateSpan.textContent = `${year}-${month + 1}-${day}`;
                    calendarContainer.style.display = "none";
                });
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
        let adultsCount = 1;
        let childrenCount = 0;
        let selectedType = "أشخاص";
        let selectedTypeVal = "person";
        let reservationType = "";

        const peopleDropdownBtn = document.getElementById("people-dropdown-btn");
        const familyBtn = document.getElementById("family-btn");
        const individualsBtn = document.getElementById("individuals-btn");
        const adultsInput = document.getElementById("adults-count");
        const childrenInput = document.getElementById("children-count");
        const confirmSelection = document.getElementById("confirm-selection");


        function updateCounts() {
            adultsInput.value = adultsCount;
            childrenInput.value = childrenCount;
        }

        document.getElementById("increase-adults").addEventListener("click", () => {
            adultsCount++;
            updateCounts();
        });

        document.getElementById("decrease-adults").addEventListener("click", () => {
            if (adultsCount > 1) {
                adultsCount--;
                updateCounts();
            }
        });

        document.getElementById("increase-children").addEventListener("click", () => {
            childrenCount++;
            updateCounts();
        });

        document.getElementById("decrease-children").addEventListener("click", () => {
            if (childrenCount > 0) {
                childrenCount--;
                updateCounts();
            }
        });

        familyBtn.addEventListener("click", () => {
            selectedType = "عائلة";
            selectedTypeVal = "family";
            familyBtn.classList.add("selected-btn");
            familyBtn.classList.remove("normal-btn");
            individualsBtn.classList.remove("selected-btn");
            individualsBtn.classList.add("normal-btn");
        });

        individualsBtn.addEventListener("click", () => {
            selectedType = "أشخاص";
            selectedTypeVal = "person";
            individualsBtn.classList.add("selected-btn");
            individualsBtn.classList.remove("normal-btn");
            familyBtn.classList.remove("selected-btn");
            familyBtn.classList.add("normal-btn");
        });

        confirmSelection.addEventListener("click", () => {
            const text = `${selectedType}: ${adultsCount} بالغ, ${childrenCount} طفل`;
            peopleDropdownBtn.innerHTML = `<small class="text-muted">${text}</small> 
        <i class="fas fa-chevron-down text-muted"></i>`;

            const dropdownMenu = document.querySelector(".dropdown .dropdown-menu");
            const dropdownButton = document.getElementById("people-dropdown-btn");
            bootstrap.Dropdown.getInstance(dropdownButton)?.hide();
        });

        updateCounts();
        let selectedTableLocation = "اختر الطاولة";

        const tableDropdownBtn = document.getElementById("table-dropdown-btn");
        const windowBtn = document.getElementById("window-btn");
        const insideBtn = document.getElementById("inside-btn");
        const outsideBtn = document.getElementById("outside-btn");
        const confirmTableSelection = document.getElementById("confirm-table-selection");

        function selectTableLocation(selectedBtn, locationText) {
            selectedTableLocation = locationText;

            [windowBtn, insideBtn, outsideBtn].forEach(btn => {
                btn.classList.remove("selected-btn");
                btn.classList.add("normal-btn");
            });

            selectedBtn.classList.remove("normal-btn");
            selectedBtn.classList.add("selected-btn");
        }

        windowBtn.addEventListener("click", () => selectTableLocation(windowBtn, "بجوار النافذة"));
        insideBtn.addEventListener("click", () => selectTableLocation(insideBtn, "داخل المطعم"));
        outsideBtn.addEventListener("click", () => selectTableLocation(outsideBtn, "خارج المطعم"));

        confirmTableSelection.addEventListener("click", function() {
            tableDropdownBtn.innerHTML = `
            <div class="text-muted">
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="" />
                <span class="mx-2">${selectedTableLocation}</span>
            </div>
            <i class="fas fa-chevron-down text-muted"></i>`;

            let dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(tableDropdownBtn);
            dropdownInstance.hide();
        });

        const dropdownBtn = document.getElementById("reservation-dropdown-btn");
        const options = document.querySelectorAll(".select-option");

        options.forEach(option => {
            option.addEventListener("click", function() {
                reservationType = this.dataset.type;
                dropdownBtn.innerHTML = `
          <div class="text-muted">
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="" />
                <span class="mx-2">${this.dataset.value}</span>
            </div>
            <i class="fas fa-chevron-down text-muted"></i>`;

                let dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(dropdownBtn);
                dropdownInstance.hide();
            });
        });
        const searchBtn = document.getElementById("search-tables-btn");
        const availableTablesSection = document.getElementById("available-tables-section");
        const tableItems = document.querySelectorAll(".table-item");
        const confirmBookingBtn = document.getElementById("confirm-booking-btn");

        let selectedTable = null;

        searchBtn.addEventListener("click", function() {
            alert(1);
            let TableReservation;
            TableReservation = {
                "personal": {
                    "adult": adultsCount,
                    "kids": childrenCount,
                    "type": selectedTypeVal
                },
                "date": selectedDateSpan.textContent,
                "tableType": 1,
                "tableId": 2,
                "tableName": "table 1",
                "tableImage": "https://letsenhance.io/static/73136da51c245e80edc6ccfe44888a99/1015f/MainBefore.jpg",
                "tableSession": "13.30",
                "reservType": reservationType,
                "leaveTime": "16.30"
            };
            localStorage.setItem('TableReservation', JSON.stringify(TableReservation));

            availableTablesSection.classList.remove("d-none");
            $(".tables-carousel").owlCarousel({
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
                    },


                }
            });
            $(".owl-prev > span").html('<i class="fas fa-chevron-right"></i>');
            $(".owl-next > span").html('<i class="fas fa-chevron-left"></i>');
        });
        tableItems.forEach(item => {
            item.addEventListener("click", function() {
                tableItems.forEach(i => i.classList.remove("selected-table"));
                this.classList.add("selected-table");
                selectedTable = this.getAttribute("data-table");
            });
        });
        confirmBookingBtn.addEventListener("click", function() {
            if (!selectedTable) {
                alert("يرجى اختيار طاولة قبل التأكيد.");
                return;
            }
            $("#table-reservation").modal("hide");

            window.location.href = '{{route("cart")}}';

        });
    });
</script>