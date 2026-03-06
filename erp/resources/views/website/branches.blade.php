<div class="branches-modal modal fade" tabindex="-1" id="branchesModal" aria-labelledby="branchesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="bg-grey py-2 rounded-3 mb-3">
                    <h5 class="text-dark fw-bold text-center mb-0">@lang('header.selectbranch')</h5>
                </div>
                <div class="d-flex justify-content-end my-2">
                    <button class="btn btn-no-modal useMyLocationReceiveBtn" id="useMyLocationReceiveBtn2">
                        @lang('header.useMyLocation')</button>
                </div>

                <div class="one my-4">
                    <div class="select-menu">
                        <select id="countrySelect2" class="form-select mb-3">
                            <option selected disabled> @lang('header.choosecountry')</option>
                            @foreach (GetCountries() as $countries)
                                <option value="{{ $countries->id }}">
                                    {{ app()->getLocale() === 'ar' ? $countries->name_ar : $countries->name_en }}
                                </option>
                            @endforeach
                        </select>
                        <select id="citySelect2" class="form-select mb-3">
                            <option selected disabled> @lang('header.choosecity')</option>

                        </select>
                        <select id="areaSelect2" class="form-select mb-3">
                            <option selected disabled>@lang('header.choosearea')</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn" id="searchButton2">@lang('header.accept')</button>
                    </div>
                </div>

                <div class="two my-4 d-none">
                    <h6 class="fw-bold">@lang('header.branch') </h6>
                    <div class="location border-red mb-1" id="content2">

                    </div>
                    <div class="d-flex justify-content-between my-3">
                        <button class="btn reversed main-color" id="backBtn">@lang('header.previous')</button>
                        <button class="btn" id="confirm_branch">@lang('header.Continue')</button>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        document.getElementById("backBtn").addEventListener("click", function() {
            document.querySelector(".two").classList.add("d-none");
        });
    </script>
    <script>
        //fetch selects of country , city ,region
        // $(document).ready(function() {
        // When a country is selected
        $('#countrySelect2').on('change', function() {
            let countryId = $(this).val();

            // Fetch cities
            $.ajax({
                url: '/get-cities/' + countryId,
                type: 'GET',
                success: function(data) {
                    $('#citySelect2').html(
                        '<option selected disabled>@lang('header.choosecity')</option>');
                    $('#areaSelect2').html(
                        '<option selected disabled>@lang('header.choosearea')</option>');

                    $.each(data, function(key, city) {
                        $('#citySelect2').append('<option value="' + city.id +
                            '">' +
                            city.name + '</option>');
                    });
                }
            });
        });

        // When a city is selected
        $('#citySelect2').on('change', function() {
            let cityId = $(this).val();

            // Fetch areas
            $.ajax({
                url: '/get-areas/' + cityId,
                type: 'GET',
                success: function(data) {
                    $('#areaSelect2').html(
                        '<option selected disabled>@lang('header.choosearea')</option>');

                    $.each(data, function(key, area) {
                        $('#areaSelect2').append('<option value="' + area.id +
                            '">' +
                            area.name + '</option>');
                    });
                }
            });
        });
        // });

        function fetchBranches2(data) {
            $.ajax({
                url: "{{ route('search.branches') }}",
                method: 'POST',
                data: {
                    ...data,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    let branchContainer2 = $('.two');
                    let branchContent2 = $('#content2');
                    branchContainer2.removeClass('d-none');

                    // Clear previous content
                    branchContent2.empty(); // Better than .html('') for performance

                    console.log("Response:", response);

                    // Check for empty response
                    if (!response || (typeof response === 'object' && Object.keys(response).length === 0) ||
                        response === '{}') {
                        branchContent2.html(`<p class="text-muted text-center">@lang('header.No branches available in this area.')</p>`);
                        $('#confirm_branch').addClass('d-none');
                        $('#backBtn').removeClass('d-none');

                        return;
                    }

                    // If response has data, build the full HTML structure
                    let branchName = response.name_site || "@lang('header.Unknown')";
                    let branchAddress = response.address || "@lang('header.Not available')";
                    let branchStatus = response.is_open ? "@lang('header.Open')" : "@lang('header.Closed')";
                    let branchStatusClass = response.is_open ? "text-success" : "text-danger";
                    let branchAvailabilityClass = response.is_open ? "bg-light-green text-success" :
                        "bg-grey text-muted";
                    let phoneNumber = response.phone || "@lang('header.No phone available')";

                    let workingTimesHTML = response.working_times && response.working_times.length > 0 ?
                        response.working_times.map(time => {
                            let daysDisplay = Array.isArray(time.days) ? time.days.join(', ') : time.days;
                            let openingTime = time.opening_hour || '--';
                            let closingTime = time.closing_hour || '--';
                            return `${daysDisplay}: ${openingTime} - ${closingTime}`;
                        }).join('<br>') :
                        "@lang('header.Noworkinghoursavailable')";

                    // Construct the full HTML for the branch data
                    let branchHTML = `
        <div class="d-flex justify-content-between">
                            <h6 class="fw-bold mt-2" id="branchName">
                                <i class="fas fa-map-marker-alt main-color mx-2"></i>${branchName}
                            </h6>
                            <span id="isopen" class="badge ${branchStatusClass} mt-2">${branchStatus}</span>
                        </div>
                        <p class="text-muted mx-2" id="address">${branchAddress}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <p id="avilability" class="${branchAvailabilityClass}"> @lang('header.Available'): (${response.services || '-'})</p>
                            <p class="main-color mx-2 fw-bold">
                                <span>@lang('header.Phone')</span>${phoneNumber}
                            </p>
                            <p class="text-muted" id="phone"></p>
                        </div>
                        <div id="times" class="text-muted mx-2">${workingTimesHTML}</div>
    `;

                    // Append the new HTML to the container
                    branchContent2.html(branchHTML).data('branch-id', response.id);
                    $('#confirm_branch').removeClass('d-none');

                },
                error: function(xhr, status, error) {
                    console.error("Error fetching branches:", error);
                    console.log("Response Text:", xhr.responseText);
                    $('#content2').html(`<p class="text-muted text-center">@lang('header.Error loading branch information.')</p>`);
                }
            });
        }
        // Event Listener for "Use My Location"
        document.getElementById('useMyLocationReceiveBtn2').addEventListener('click', function() {
            const latitude = getCookie('latitude');
            const longitude = getCookie('longitude');

            if (latitude && longitude) {
                fetchBranches2({
                    latitude,
                    longitude,
                    branchesStatus: true
                });
            } else if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const long = position.coords.longitude;

                    setBranchCookie(lat, long, false); // Do not update branch_id or reload

                    fetchBranches2({
                        latitude: lat,
                        longitude: long,
                        branchesStatus: true
                    });
                }, function(error) {
                    handleGeolocationError(error);
                });
            } else {
                const modalElement = document.getElementById('modal_access');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        });
        // Event Listener for "Search" by country/city/region
        $('#searchButton2').off('click').on('click', function() {
            let countryId = $('#countrySelect2').val();
            let cityId = $('#citySelect2').val();
            let regionId = $('#areaSelect2').val();

            if (!countryId || !cityId || !regionId) {
                // Use a more user-friendly notification than alert()
                toastr.warning("@lang('header.Please select')");
                return;
            } else {
                fetchBranches2({
                    country_id: countryId,
                    city_id: cityId,
                    area_id: regionId,
                    branchesStatus: true
                });
            }


        });
        document.getElementById("confirm_branch").addEventListener("click", function() {
            // Get the branch ID from the displayed branch info
            const branchId = $('#content2').data('branch-id');

            // Set cookie with 7 days expiration
            setCookie('branch_id', branchId, 7);
            ['TableReservation', 'takeaway_details', 'cart'].forEach(
                key => {
                    if (localStorage.getItem(key)) {
                        localStorage.removeItem(key);
                        console.log(`Removed ${key} from localStorage`);
                    }
                });
            // Hide the modal
            $("#branchesModal").modal("hide");
            window.location.reload();
            // Optional: Show success message
            toastr.success("@lang('header.Branch selected successfully')");
        });
    </script>
@endpush
