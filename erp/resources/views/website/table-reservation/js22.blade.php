<script>
    document.addEventListener("DOMContentLoaded", function() {

        let barnch_id = getCookie('branch_id');
        let current_date = new Date();
        let day_number = current_date.getDay();
        let day_name = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][new Date().getDay()];

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
        let menCount = 1;
        let womenCount = 0;
        let selectedType = "أشخاص";
        let selectedTypeVal = "person";
        let reservationType = "";

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
            familyBtn.classList.add("selected-btn");
            familyBtn.classList.remove("normal-btn");
            individualsBtn.classList.remove("selected-btn");
            individualsBtn.classList.add("normal-btn");

            familyOptions.style.display = "block";
            individualsOptions.style.display = "none";

            updateCounts();
        });

        // Switch to Individuals
        individualsBtn.addEventListener("click", () => {
            selectedType = "أشخاص";
            individualsBtn.classList.add("selected-btn");
            individualsBtn.classList.remove("normal-btn");
            familyBtn.classList.remove("selected-btn");
            familyBtn.classList.add("normal-btn");
            familyOptions.style.display = "none";
            individualsOptions.style.display = "block";

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

            peopleDropdownBtn.innerHTML = `<small class="text-muted">${text}</small> 
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
            if (event.target.closest("button")) {
                let selectedBtn = event.target.closest("button");
                let locationText = selectedBtn.querySelector("span").innerText; // Get partition name

                selectTableLocation(selectedBtn, locationText);
            }
        });

        function selectTableLocation(selectedBtn, locationText) {
            selectedTableLocation = locationText;

            // Remove selection from all buttons
            document.querySelectorAll("#floor_partitions button, #window-btn").forEach(btn => {
                btn.classList.remove("selected-btn");
                btn.classList.add("normal-btn");
            });

            // Highlight selected button
            selectedBtn.classList.remove("normal-btn");
            selectedBtn.classList.add("selected-btn");
        }


        // let table_session = JSON.parse(`{!! json_encode(get_table_session(11, '2025-03-25', 5)) !!}`);
        // console.log(table_session);




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

        options.forEach(option => {
            option.addEventListener("click", function() {
                dropdownBtn.innerHTML = `
          <div class="text-muted">
                <img src="SiteAssets/images/table.svg" alt="" />
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
            console.log(TableReservation);

            localStorage.setItem('TableReservation', JSON.stringify(TableReservation));
            window.location.href = '{{route("cart")}}';

        });
    });
</script>