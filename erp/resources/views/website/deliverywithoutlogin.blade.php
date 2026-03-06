<div class="first-phase  @auth('client') d-none @endauth">
    <select class="form-control" id="areaSelect" onchange="checkArea(this)">
        <option value="">@lang('header.select_area')</option>
        @foreach (Areas() as $area)
            <option value="{{ $area->id }}">
                {{ $area->name }}</option>
        @endforeach
    </select>
    @error('area_id')
        <small class="text-danger">{{ $message }}</small>
    @enderror
    <h5 class="fw-bold">
        @lang('header.deliverylocation') </h5>
    <div class="search-group ">
        <span class="search-icon">
            <i class="fas fa-search"></i>
        </span>
        <input type="text" id="searchBox" class="form-control" placeholder="@lang('header.search')" />
        <button onclick="searchLocation()" class="btn btn-green">@lang('header.search')</button>
    </div>
    <div class="py-2">
        <h5 class="text-danger"><span class="text-danger"> <i class="fas fa-map-marker-alt main-color ms-2"></i></span>
            @lang('header.move_mark')</h5>
    </div>
    <div class="map position-relative my-1">
        <div id="map" style="height: 500px;"></div>


        <div class="notes">
            <small>@lang('header.saveaddress')</small>
            <button class="btn btn-green" data-bs-toggle="modal" data-bs-target="#loginModal">
                @lang('header.login')</button>
        </div>
    </div>
    {{-- this will show with data of location mark after search --}}
    <div class="tab-footer justify-content-between d-flex align-items-center">
        <div>
            <h6 class="fw-bold">
                @lang('header.location_delivery') </h6>
            <p id="details">
                <i class="fas fa-map-marker-alt main-color ms-2"></i>
                <span id="address"></span>

            </p>
        </div>
        <button class="btn  btn-green fw-bold d-none" id="editoffirst" type="button"
            onclick="showSecondPhase(); editAddress();">
            @lang('header.edit')
        </button>
        <button type="submit" class="btn btn-green" id="confirmeaddress" onclick="showSecondPhase()">
            @lang('header.confirmeaddress')
        </button>
    </div>
</div>
<div class="second-phase d-none">
    <h6 class="fw-bold">
        @lang('header.deliverylocation') </h6>
    <div class="d-flex justify-content-between">
        <p id="secounddetailaddress">
            <i class="fas fa-map-marker-alt main-color ms-2"></i>
        </p>
        <button class="btn  btn-green fw-bold" type="button" onclick="showFirstPhase2()">
            @lang('header.edit') </button>
    </div>
    <h6 class="fw-bold mb-3">
        @lang('header.confirmeaddress')
    </h6>
    <div class="delivery-places px-0 mb-3">
        <!-- Radio Buttons for Delivery Places -->
        <div class="btn-group gap-2" role="group" aria-label="Delivery Place Selector">
            <input type="radio" class="btn-check border-0" name="deliveryPlace" id="radio-home" autocomplete="off"
                checked>
            <label class="btn  rounded-pill" for="radio-home">
                <i class="fas fa-city"></i> @lang('header.apartment')
            </label>

            <input type="radio" class="btn-check border-0" name="deliveryPlace" id="radio-villa" autocomplete="off">
            <label class="btn  rounded-pill" for="radio-villa">
                <i class="fas fa-home"></i> @lang('header.villa')
            </label>

            <input type="radio" class="btn-check border-0" name="deliveryPlace" id="radio-hotel" autocomplete="off"
                onchange="onHotelSelected()">
            <label class="btn  rounded-pill" for="radio-hotel">
                <i class="fas fa-hotel"></i> @lang('header.hotel')
            </label>

            <!-- <input type="radio" class="btn-check border-0" name="deliveryPlace" id="radio-work" autocomplete="off">
            <label class="btn  rounded-pill" for="radio-work">
                <i class="fas fa-building"></i> @lang('header.office')
            </label> -->
        </div>
    </div>

    <!-- Content Sections -->
    <div class="tab-content">
        <!-- Home Form -->
        <div class="delivery-section home-section active">
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> @lang('header.nameapart')<span style="color:red">*</span></label>
                <input type="text" class="form-control" name="name-home" id="namehome"
                    placeholder="@lang('header.nameapart')" value="">

            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> @lang('header.numapart')<span style="color:red">*</span></label>
                <input type="text" class="form-control" name="num-home" id="numhome" value=""
                    placeholder="@lang('header.numapart')">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> @lang('header.Floor') <span style="color:red">*</span></label>
                <input type="text" class="form-control" name="floor-home" id="floorhome" value=""
                    placeholder="@lang('header.Floor')">
            </div>
            <div class="mb-3 d-flex">
                <input type="text" class="form-control" name="detail-home" id="detailhome"
                    placeholder="@lang('header.addressdetail')">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> @lang('header.mark')</label>
                <input type="text" class="form-control" name="mark-home" id="markhome" value=""
                    placeholder="@lang('header.mark')">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> @lang('header.phoneenter')<span style="color:red">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" name="phone-home" value="" id="phonehome"
                        placeholder="@lang('header.phoneenter')">
                    <select id="country" name="country_code_apart" class="selectpicker me-2"
                        data-live-search="true">
                        <option value="" selected disabled>@lang('header.select_country_code')</option>
                        @foreach (GetCountries() as $country)
                            <option
                                data-content='<img src="{{ $country->flag }}" class="flag-icon"> {{ $country->phone_code }}'
                                value="{{ $country->phone_code }}">
                                {{ $country->phone_code }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Villa Form -->
        <div class="delivery-section villa-section d-none">
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> @lang('header.namevilla')<span style="color:red">*</span></label>
                <input type="text" class="form-control" name="name-villa" id="namevilla"
                    placeholder="@lang('header.namevilla')">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> @lang('header.villanumber')<span style="color:red">*</span></label>
                <input type="text" class="form-control" name="num-villa" id="numvilla"
                    placeholder="@lang('header.villanumber')">
            </div>
            <div class="mb-3 d-flex">
                <input type="text" class="form-control" name="detail-villa" id="detailvilla"
                    placeholder="@lang('header.addressdetail')">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> @lang('header.mark')</label>
                <input type="text" class="form-control" name="mark-villa" id="markvilla"
                    placeholder="@lang('header.mark')">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> @lang('header.phoneenter')<span style="color:red">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" name="phone-villa" id="phone-villa"
                        placeholder="@lang('header.phoneenter')">
                    <select id="country_code_villa" name="country_code_villa" class="selectpicker me-2"
                        data-live-search="true">
                        <option value="" selected disabled>@lang('header.select_country_code')</option>
                        @foreach (GetCountries() as $country)
                            <option
                                data-content='<img src="{{ $country->flag }}" class="flag-icon"> {{ $country->phone_code }}'
                                value="{{ $country->phone_code }}">
                                {{ $country->phone_code }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>


        <!-- Hotel Form -->
        <div class="delivery-section hotel-section d-none">

            <div class="mb-3">
                <label for="hotel_id" class="mb-1 "> @lang('header.hotel_name')<span style="color:red">*</span></label>
                <select id="hotel_id" name="hotel_id" class="selectpicker me-2" data-live-search="true">
                    <option value="" selected disabled>@lang('header.select_hotel')</option>
                    @foreach (GetHotels() as $hotel)
                        <option value="{{ $hotel->id }}" data-address="{{ $hotel->address_ar }}">
                            {{ $hotel->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="roomhotel" class="mb-1 "> @lang('header.rome_number')<span style="color:red">*</span></label>
                <input type="text" class="form-control" name="num-hotel" id="roomhotel"
                    placeholder="@lang('header.roomnumber')">
            </div>
            <div class="mb-3 d-flex">
                <input type="text" class="form-control" name="detail-hotel" id="detailhotel"
                    placeholder="@lang('header.addressdetail')">
            </div>
            <div class="mb-3">
                <label for="markhotel" class="mb-1 "> @lang('header.mark')</label>
                <input type="text" class="form-control" name="mark-hotel" id="markhotel"
                    placeholder="@lang('header.mark')">
            </div>
            <div class="mb-3">
                <label for="phonehotel" class="mb-1 "> @lang('header.phoneenter')<span style="color:red">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" name="phone-hotel" id="phone-hotel"
                        placeholder="@lang('header.phoneenter')">
                    <select id="country_code_hotel" name="country_code_hotel" class="selectpicker me-2"
                        data-live-search="true">
                        <option value="" selected disabled>@lang('header.select_country_code')</option>
                        @foreach (GetCountries() as $country)
                            <option
                                data-content='<img src="{{ $country->flag }}" class="flag-icon"> {{ $country->phone_code }}'
                                value="{{ $country->phone_code }}">{{ $country->phone_code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Work Form -->
        <!-- <div class="delivery-section work-section d-none">
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> اسم المكتب<span style="color:red">*</span></label>
                <input type="text" name="name-office" id="name-office" class="form-control"
                    placeholder="@lang('header.nameoffice')" value="">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> رقم  المبنى<span style="color:red">*</span></label>
                <input type="text" name="num-office" id="num-office" class="form-control" value=""
                    placeholder="@lang('header.numaoffice')">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 ">  الدور<span style="color:red">*</span></label>
                <input type="text" name="floor-office" id="floor-office" value="" class="form-control"
                    placeholder="@lang('header.Floor')">
            </div>
            <div class="mb-3 d-flex">
                <input type="text" name="detail-office" id="detail-office" value="" class="form-control"
                    placeholder="@lang('header.addressdetail')">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> علامة مييزة</label>
                <input type="text" name="mark-office" id="mark-office" value="" class="form-control"
                    placeholder="@lang('header.mark')">
            </div>
            <div class="mb-3">
                <label for="namehome" class="mb-1 "> رقم الهاتف <span style="color:red">*</span></label>
                <div class="input-group">
                    <input type="text" name="phone-office" id="phone-office" value="" class="form-control"
                        placeholder="@lang('header.phoneenter')">
                    <select id="country-office" name="country_code_office" class="selectpicker me-2"
                        data-live-search="true">
                        <option value="" selected disabled>@lang('header.select_country_code')</option>
                        @foreach (GetCountries() as $country)
<option
                                data-content='<img src="{{ $country->flag }}" class="flag-icon"> {{ $country->phone_code }}'
                                value="{{ $country->phone_code }}">
                                {{ $country->phone_code }}
                            </option>
@endforeach
                    </select>
                </div>
            </div>
        </div> -->

    </div>

    <div class="tab-footer justify-content-end d-flex mb-3">
        <button type="submit" class="btn btn-green"
            onclick="showThirdPhase(); saveAddressDetails(); viewAddressDetails();">
            @lang('header.saveaddressc')</button>
    </div>
</div>
<div class="third-phase d-none">
    <div>
        <h6 class="fw-bold">
            @lang('header.deliverylocation') </h6>
        <div class="d-flex justify-content-between">
            <p id="addressDisplay">
                <i class="fas fa-map-marker-alt main-color ms-2"></i>
            </p>
            <button class="btn btn-green fw-bold" type="button" id="editBtn" onclick="showSecondPhase2()">
                @lang('header.edit')
            </button>
        </div>

        <p class="text-muted" id="detailsDisplay">
        </p>

    </div>
</div>
{{-- This modal will be shown if the selected area is not served by the current branch --}}
<div class="logout-modal modal fade" tabindex="-1" id="notfoundddressModal">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h4 class="mt-4"> @lang('auth.areanotforcurrentbranch')</h4>
            </div>
            <div class="modal-footer d-flex border-0 align-items-center justify-content-center">
                <button type="button" class="btn main-color w-25 mx-2" id="btn-switch-branch">
                    @lang('auth.ok')
                </button>

                <button type="button" class="btn reversed main-color w-25 mx-2" data-bs-dismiss="modal">
                    {{-- NO --}}
                    @lang('auth.no')
                </button>

            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        function checkArea(selectElement) {
            const areaId = selectElement.value;
            if (!areaId) return;
            selectedAreaId = areaId;

            // Update hidden area_id field
            document.getElementById('areaSelect').value = areaId;

            // Find the selected option text (area name)
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            selectedAreaName = selectedOption.text;

            $.get(`/check-area-branch/${areaId}`, (data) => {
                console.log(data.status);

                if (data.status === 'served_by_another_branch') {
                    pendingBranchId = data.branch;
                    new bootstrap.Modal('#notfoundddressModal').show();
                } else if (data.status === 'not_served') {
                    alert("@lang('auth.not_served_message')");
                }

                // Focus map on the selected area
                // if (data.namearea) {
                //     focusMapOnArea(data.namearea);
                // }
            }).fail(console.error);
        }
        // Hotel selection handler
        function onHotelSelected() {
            console.log(0);

            if (!selectedAreaId) {
                alert("@lang('header.select_area_first')");
                return;
            }

            // Show the hotel input section
            $('.hotel-section').removeClass('d-none');

            // Load hotels for the selected area
            $.ajax({
                url: `/get-hotels-by-area/${selectedAreaId}`,
                method: 'GET',
                success: function(response) {
                    console.log(response.data);

                    $('#hotel_id').empty().append(
                        '<option disabled selected>@lang('header.select_hotel')</option>'
                    );

                    $.each(response.data, function(key, hotel) {
                        $('#hotel_id').append(
                            '<option value="' + hotel.id + '" data-address="' + hotel.address +
                            '">' +
                            hotel.name + '</option>'
                        );
                    });
                },
                error: function(err) {
                    console.error(err);
                    alert("@lang('header.failed_load_hotels')");
                }
            });
        }


        // Set address when hotel is selected
        $('#hotel_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const address = selectedOption.data('address');
            $('#detailhotel').val(address);
            $('#detailhotel').attr('readonly', true);

            // If this is a new hotel selection, geocode its address
            // if (address) {
            //     geocodeHotelAddress(address);
            // }
        });

        function setCookie(name, value, days) {
            let expires = '';
            if (days) {
                let date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toUTCString();
            }
            document.cookie = name + '=' + value + expires + '; path=/';
        }
        // Default location values
        let defaultLat = 30.060475;
        let defaultLng = 31.2079694;

        // Retrieve map details from localStorage or cookies
        const mapDetails = JSON.parse(localStorage.getItem('mapDetail'));
        const cookieLatitude = getCookie('latitude');
        const cookieLongitude = getCookie('longitude');

        if (cookieLatitude && cookieLongitude) {
            defaultLat = parseFloat(cookieLatitude);
            defaultLng = parseFloat(cookieLongitude);
        }

        let initialLat = mapDetails?.lat || defaultLat;
        let initialLng = mapDetails?.lng || defaultLng;

        // Function to toggle visibility of edit and confirm buttons
        function toggleEditButton(lat) {
            const editButton = document.getElementById('editoffirst');
            const confirmButton = document.getElementById('confirmeaddress');
            if (lat !== defaultLat) {
                editButton.classList.remove('d-none');
                confirmButton.classList.add('d-none');
            } else {
                editButton.classList.add('d-none');
                confirmButton.classList.remove('d-none');
            }
        }

        // Initialize Google Map
        const map = new google.maps.Map(document.getElementById("map"), {
            center: {
                lat: initialLat,
                lng: initialLng
            },
            zoom: 13,
        });

        // Draggable marker
        const marker = new google.maps.Marker({
            position: {
                lat: initialLat,
                lng: initialLng
            },
            map: map,
            draggable: true,
            title: 'Drag or click to update location',
        });

        updateLocationDetails(initialLat, initialLng);

        // Update on marker drag
        google.maps.event.addListener(marker, 'dragend', (event) => {
            updateLocationDetails(event.latLng.lat(), event.latLng.lng());
        });

        // Update on map click
        google.maps.event.addListener(map, 'click', (event) => {
            marker.setPosition(event.latLng);
            updateLocationDetails(event.latLng.lat(), event.latLng.lng());
        });

        // Function to update location details using reverse geocoding
        function updateLocationDetails(lat, lng) {
            // Get the language from the document's lang attribute (default to 'en')
            const language = document.documentElement.lang || 'en';

            const geocoder = new google.maps.Geocoder();

            geocoder.geocode({
                location: {
                    lat: lat,
                    lng: lng
                },
                language: language // Set language here
            }, (results, status) => {
                if (status === google.maps.GeocoderStatus.OK && results[0]) {
                    const address = results[0].formatted_address || "Address not found";

                    // Split address components (if available)
                    const addressComponents = results[0].address_components;
                    let country = '';
                    let countryCode = '';
                    let postalCode = '';
                    let state = '';
                    let city = '';

                    // Loop through address components and extract relevant details
                    addressComponents.forEach(component => {
                        if (component.types.includes('country')) {
                            country = component.long_name;
                        }
                        if (component.types.includes('postal_code')) {
                            postalCode = component.long_name;
                        }
                        if (component.types.includes('administrative_area_level_1')) {
                            state = component.long_name;
                        }
                        if (component.types.includes('locality')) {
                            city = component.long_name; // City (locality) is typically here
                        }
                    });

                    // If no city is found, use broader state or region as a fallback
                    if (!city) {
                        city = state;
                    }

                    // Create the mapDetail object
                    const mapDetail = {
                        address,
                        lat,
                        lng,
                        country,
                        city,
                        state,
                        postalCode,
                        country_code: countryCode
                    };

                    // Save details to localStorage
                    localStorage.setItem('mapDetail', JSON.stringify(mapDetail));

                    // Update the address in the UI
                    let extractedAddress = address;
                    // Only try to split if the address contains 'مصر'
                    if (address.includes('مصر')) {
                        extractedAddress = address.split('مصر')[0].trim();
                    }

                    document.getElementById('address').textContent = extractedAddress;
                    document.getElementById('searchBox').value = extractedAddress;

                    document.getElementById('secounddetailaddress').textContent = extractedAddress;

                    // Update address inputs
                    document.getElementById('detailhome').value = extractedAddress;
                    document.getElementById('detailvilla').value = extractedAddress;
                    document.getElementById('detailhotel').value = extractedAddress;
                    document.getElementById('detail-office').value = extractedAddress;

                } else {
                    console.error('Geocoder failed due to: ' + status);
                }
            });
        }
        // Function to search for a location using Google Places API
        window.searchLocation = function() {
            var query = document.getElementById('searchBox').value;
            if (!query) {
                var modal = new bootstrap.Modal(document.getElementById('notfoundddressModal'));
                modal.show();
                return;
            }

            // Get the language from the document's lang attribute (default to 'en')
            const language = document.documentElement.lang || 'en';

            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                address: query,
                language: language // Pass the language parameter to use the correct language
            }, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    var lat = results[0].geometry.location.lat();
                    var lng = results[0].geometry.location.lng();

                    // Set map center to the new location
                    map.setCenter(results[0].geometry.location);
                    marker.setPosition(results[0].geometry.location);

                    var infowindow = new google.maps.InfoWindow({
                        content: results[0].formatted_address
                    });
                    infowindow.open(map, marker);

                    // Update the #address element with the retrieved address
                    document.getElementById('address').textContent = results[0].formatted_address;
                    document.getElementById('secounddetailaddress').textContent = results[0].formatted_address;

                    // Optionally, update the entire #details section with the new address
                    document.getElementById('details').innerHTML = `
                        <i class="fas fa-map-marker-alt main-color ms-2"></i>
                        <span id="address">${results[0].formatted_address}</span>
                    `;
                    document.getElementById('secounddetailaddress').innerHTML = `
                        <i class="fas fa-map-marker-alt main-color ms-2"></i>
                        <span id="address">${results[0].formatted_address}</span>
                    `;
                } else {
                    var modal = new bootstrap.Modal(document.getElementById('notfoundddressModal'));
                    modal.show();
                }
                updateLocationDetails(lat, lng);

            });
        };

        // Save address details from user input
        function saveAddressDetails() {
            // Identify the checked radio button for the delivery place
            const selectedDeliveryPlace = document.querySelector('input[name="deliveryPlace"]:checked');
            if (selectedDeliveryPlace) {
                // Find the corresponding delivery section based on the checked radio button
                const selectedSection = document.querySelector(
                    `.${selectedDeliveryPlace.id.replace('radio-', '')}-section`
                );
                const name = selectedDeliveryPlace.id.replace('radio-', '');

                if (selectedSection) {
                    const inputs = selectedSection.querySelectorAll('input, select');
                    const addressDetails = {
                        type: name // Save the type of address (villa, home, office)
                    };

                    inputs.forEach(input => {
                        const nameKey = input.name.replace('-home', '').replace('-villa', '').replace('-hotel',
                            '');

                        // Handle select elements
                        if (input.tagName === 'SELECT') {
                            console.log(nameKey);

                            addressDetails[nameKey] = input.value || ''; // Default to empty string if no selection
                        }
                        // Handle text and tel inputs
                        else if (input.type === 'text' || input.type === 'tel') {
                            addressDetails[nameKey] = input.value.trim(); // Remove leading/trailing spaces
                        }
                    });

                    // Ensure all required fields have values before proceeding
                    const formattedAddress = {
                        country_code: addressDetails['country_code_hotel'] || addressDetails['country_code_apart'] ||
                            addressDetails['country_code_villa'] || '',
                        detail: addressDetails['detail'] || '',
                        floor: addressDetails['floor'] || '',
                        mark: addressDetails['mark'] || '',
                        name: addressDetails['hotelname'] || addressDetails['name'] || '',
                        num: addressDetails['num'] || '',
                        phone: addressDetails['phone'] || '',
                        type: addressDetails['type'] || 'home' // Default to 'home' if no type is specified
                    };

                    // Save the formatted address details to localStorage
                    localStorage.setItem('addressDetails', JSON.stringify(formattedAddress));
                    ['TableReservation', 'takeaway_details', 'cart'].forEach(
                        key => {
                            if (localStorage.getItem(key)) {
                                localStorage.removeItem(key);
                                console.log(`Removed ${key} from localStorage`);
                            }
                        });
                    setCookie('branch_takaway', 'false', 7); // Set the branch cookie (optional)
                }
            }
        }

        // Display saved address details
        function viewAddressDetails() {
            const addressData = JSON.parse(localStorage.getItem('addressDetails'));
            if (addressData) {
                const formattedAddress =
                    `${addressData.name || ''} - ${addressData.num || ''} - ${addressData.floor || ''} (${addressData.country_code || ''})${addressData.phone || ''}`;
                document.querySelector('#addressDisplay').textContent = formattedAddress;
            } else {
                document.querySelector('#addressDisplay').textContent = "No address data available.";
            }
        }

        // Edit saved address details
        function editAddress() {
            let addressData = JSON.parse(localStorage.getItem('addressDetails'));
            if (!addressData) return;
            const selectedRadio = document.querySelector(`input[name="deliveryPlace"][id="radio-${addressData.type}"]`);
            if (selectedRadio) {
                selectedRadio.checked = true;
            }

            document.querySelectorAll('.delivery-section').forEach(section => {
                section.classList.add('d-none');
                section.classList.remove('active');
            });

            const selectedSection = document.querySelector(`.${addressData.type}-section`);
            if (selectedSection) {
                selectedSection.classList.remove('d-none');
                selectedSection.classList.add('active');

                const inputs = selectedSection.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const inputName = input.name;

                    let cleanedInputName = inputName.replace(`-${addressData.type}`, '');

                    if (addressData[cleanedInputName] !== undefined) {
                        const value = addressData[cleanedInputName];

                        if (input.tagName === 'SELECT') {
                            const optionExists = Array.from(input.options).some(option => option.value === value);

                            if (optionExists) {
                                input.value = value;
                                if ($(input).hasClass('selectpicker')) {
                                    $(input).selectpicker('refresh');
                                }
                                input.dispatchEvent(new Event('change'));
                            } else {
                                input.value = '';
                            }
                        } else if (input.type === 'text' || input.type === 'tel') {
                            input.value = value.trim();
                        }
                    } else {
                        input.value = '';
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateLocationDetails(initialLat, initialLng);
            toggleEditButton(initialLat);
        });
    </script>

    <script>
        document.querySelectorAll('.btn-check').forEach((radio) => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.delivery-section').forEach((section) => {
                    section.classList.add('d-none');
                });

                if (radio.id === 'radio-home') {
                    document.querySelector('.home-section').classList.remove('d-none');
                } else if (radio.id === 'radio-villa') {
                    document.querySelector('.villa-section').classList.remove('d-none');
                } else if (radio.id === 'radio-hotel') {
                    document.querySelector('.hotel-section').classList.remove('d-none');
                } else if (radio.id === 'radio-work') {
                    document.querySelector('.work-section').classList.remove('d-none');
                }
            });
        });

        function showFirstPhase2() {
            document.querySelector('.second-phase').classList.add('d-none');
            document.querySelector('.first-phase').classList.remove('d-none');
        }

        function showSecondPhase2() {
            document.querySelector('.third-phase').classList.add('d-none');
            document.querySelector('.second-phase').classList.remove('d-none');
        }
        // Add this to your scripts (best at the end of your JavaScript section)
        document.getElementById('deliveryModal').addEventListener('hidden.bs.modal', function() {
            // Reset to first phase
            showFirstPhase();

            // Reset form fields if needed
            document.querySelectorAll('.delivery-section input, .delivery-section select').forEach(element => {
                element.value = '';
            });

            // Reset radio buttons to default (home/apartment)
            document.getElementById('radio-home').checked = true;

            // Reset any other state as needed
        });
        // Phase management functions
        function showFirstPhase() {
            document.querySelector('.first-phase').classList.remove('d-none');
            document.querySelector('.second-phase').classList.add('d-none');
            document.querySelector('.third-phase').classList.add('d-none');
        }

        function showSecondPhase() {
            document.querySelector('.first-phase').classList.add('d-none');
            document.querySelector('.second-phase').classList.remove('d-none');
            document.querySelector('.third-phase').classList.add('d-none');

            // Make sure to populate the address in the second phase
            const address = document.getElementById('address').textContent;
            document.getElementById('secounddetailaddress').innerHTML = `
        <i class="fas fa-map-marker-alt main-color ms-2"></i>
        <span>${address}</span>
    `;
        }

        function showThirdPhase() {
            document.querySelector('.first-phase').classList.add('d-none');
            document.querySelector('.second-phase').classList.add('d-none');
            document.querySelector('.third-phase').classList.remove('d-none');

            // Update the displayed address in the third phase
            viewAddressDetails();
        }
        document.addEventListener('DOMContentLoaded', function() {
            const hotelSelect = document.getElementById('hotelname');
            const addressInput = document.querySelector('input[name="detail-hotel"]');

            hotelSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const address = selectedOption.getAttribute('data-address');

                if (address) {
                    addressInput.value = address;
                } else {
                    addressInput.value = '';
                }
            });
        });
    </script>
@endpush
