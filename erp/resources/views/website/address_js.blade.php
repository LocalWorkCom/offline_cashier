@push('scripts')
<script>
    // Shared variables and functions for both auth and unauth cases
    let map, marker, pendingBranchId = null;
    let selectedAreaBounds = null;
    let selectedAreaName = null;
    let selectedAreaId = null;
    let initialLoad = true;
    let defaultLat = 30.060475;
    let defaultLng = 31.2079694;

    // Initialize the map - works for both cases
    function initMap() {
        // Get initial coordinates based on auth state
        const initialCoords = @auth('client')
            {
                lat: parseFloat("{{ old('latitude', $address ? $address->latitude : defaultLat) }}"),
                lng: parseFloat("{{ old('longitude', $address ? $address->longtitude : defaultLng) }}")
            };
        @else
            const mapDetails = JSON.parse(localStorage.getItem('mapDetail'));
            const cookieLat = getCookie('latitude');
            const cookieLng = getCookie('longitude');

            {
                lat: cookieLat ? parseFloat(cookieLat) : (mapDetails?.lat || defaultLat),
                lng: cookieLng ? parseFloat(cookieLng) : (mapDetails?.lng || defaultLng)
            };
        @endauth

        map = new google.maps.Map(document.getElementById("map"), {
            center: initialCoords,
            zoom: 13
        });

        marker = new google.maps.Marker({
            position: initialCoords,
            map: map,
            draggable: true,
            title: 'Drag or click to update location'
        });

        // Common event listeners
        google.maps.event.addListener(marker, 'dragend', (event) => {
            updatePosition(event.latLng);
            updateLocationDetails(event.latLng.lat(), event.latLng.lng());
        });

        google.maps.event.addListener(map, 'click', (event) => {
            marker.setPosition(event.latLng);
            updatePosition(event.latLng);
            updateLocationDetails(event.latLng.lat(), event.latLng.lng());
        });

        // If editing an address (auth) or has saved location (unauth)
        if (@auth('client') "{{ $address }}" @else localStorage.getItem('mapDetail') @endauth) {
            initialLoad = false;
            focusOnSavedLocation();
        }
    }

    // Common functions
    function updatePosition(position) {
        const lat = position.lat();
        const lng = position.lng();

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        @auth('client')
            // For authenticated users, we might want to update the form fields
            geocodePosition(position);
        @else
            // For unauthenticated users, save to cookies/localStorage
            setCookie('latitude', lat, 7);
            setCookie('longitude', lng, 7);
            updateLocationDetails(lat, lng);
        @endauth
    }

    function checkArea(selectElement) {
        const areaId = selectElement.value;
        if (!areaId) return;
        selectedAreaId = areaId;

        document.getElementById('area_id').value = areaId;
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        selectedAreaName = selectedOption.text;

        $.get(`/check-area-branch/${areaId}`, (data) => {
            if (data.status === 'served_by_another_branch') {
                pendingBranchId = data.branch;
                new bootstrap.Modal('#notfoundddressModal').show();
            } else if (data.status === 'not_served') {
                alert("@lang('auth.not_served_message')");
            }

            if (data.namearea) {
                focusMapOnArea(data.namearea);
            }
        }).fail(console.error);
    }

    function onHotelSelected() {
        if (!selectedAreaId) {
            alert("@lang('header.select_area_first')");
            return;
        }

        $('#hotel_div').removeClass('d-none');

        $.ajax({
            url: `/get-hotels-by-area/${selectedAreaId}`,
            method: 'GET',
            success: function(response) {
                $('#hotel_id').empty().append(
                    '<option disabled selected>@lang('header.select_hotel')</option>'
                );

                $.each(response.data, function(key, hotel) {
                    $('#hotel_id').append(
                        '<option value="' + hotel.id + '" data-address="' + hotel.address + '">' +
                        hotel.name + '</option>'
                    );
                });

                @auth('client')
                    @if($address && $address->address_type === 'hotel' && $address->hotel_id)
                        $('#hotel_id').val("{{ $address->hotel_id }}").trigger('change');
                    @endif
                @endauth
            },
            error: function(err) {
                console.error(err);
                alert("@lang('header.failed_load_hotels')");
            }
        });
    }

    // Hotel selection handler
    $('#hotel_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const address = selectedOption.data('address');
        $('#addressdetailhotel').val(address).attr('readonly', true);

        if (address) {
            geocodeHotelAddress(address);
        }
    });

    function geocodeHotelAddress(address) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: address }, (results, status) => {
            if (status === 'OK' && results[0]) {
                const location = results[0].geometry.location;
                map.setCenter(location);
                map.setZoom(16);
                marker.setPosition(location);

                document.getElementById('latitude').value = location.lat();
                document.getElementById('longitude').value = location.lng();

                @auth('client')
                    // For auth users, update the form field
                    $('#addressdetailhotel').val(results[0].formatted_address);
                @else
                    // For unauth users, update localStorage
                    updateLocationDetails(location.lat(), location.lng());
                @endauth
            }
        });
    }

    // Common helper functions
    function setCookie(name, value, days) {
        let expires = '';
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + value + expires + '; path=/';
    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    function focusOnSavedLocation() {
        @auth('client')
            const lat = parseFloat("{{ $address ? $address->latitude : '' }}");
            const lng = parseFloat("{{ $address ? $address->longitude : '' }}");
        @else
            const savedData = JSON.parse(localStorage.getItem('mapDetail'));
            const lat = savedData?.lat || defaultLat;
            const lng = savedData?.lng || defaultLng;
        @endauth

        if (lat && lng) {
            const savedLocation = new google.maps.LatLng(lat, lng);
            map.setCenter(savedLocation);
            map.setZoom(16);
            marker.setPosition(savedLocation);

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            updateLocationDetails(lat, lng);
        }
    }

    function updateLocationDetails(lat, lng) {
        const language = document.documentElement.lang || 'en';
        const geocoder = new google.maps.Geocoder();

        geocoder.geocode({
            location: { lat, lng },
            language: language
        }, (results, status) => {
            if (status === 'OK' && results[0]) {
                const address = results[0].formatted_address;
                let extractedAddress = address.includes('مصر') ?
                    address.split('مصر')[0].trim() : address;

                // Update UI elements
                const addressElements = [
                    'address', 'secounddetailaddress',
                    'detailhome', 'detailvilla', 'detailhotel'
                ];

                addressElements.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = extractedAddress;
                });

                // For authenticated users, update form fields
                @auth('client')
                    document.querySelector('.delivery-section:not(.d-none) input[name^="addressdetail"]').value = extractedAddress;
                @else
                    // For unauthenticated users, save to localStorage
                    const mapDetail = {
                        address: extractedAddress,
                        lat,
                        lng,
                        // Add other relevant details from geocoding if needed
                    };
                    localStorage.setItem('mapDetail', JSON.stringify(mapDetail));
                @endauth
            }
        });
    }

    function isLocationSelected() {
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        const area = document.getElementById('area_id').value;

        if (!area) {
            toastr.warning("@lang('header.Please_select_area')");
            return false;
        }
        if (!lat || !lng) {
            toastr.warning("@lang('header.pick_location_message')");
            document.getElementById('map').scrollIntoView({ behavior: 'smooth' });
            document.getElementById('map').classList.add('border', 'border-danger');
            return false;
        }

        document.getElementById('map').classList.remove('border', 'border-danger');
        return true;
    }

    // Phase management (works for both cases)
    function showFirstPhase() {
        document.querySelector('.first-phase').classList.remove('d-none');
        document.querySelector('.second-phase').classList.add('d-none');
        document.querySelector('.third-phase').classList.add('d-none');
    }

    function showSecondPhase() {
        if (!isLocationSelected()) return;

        document.querySelector('.first-phase').classList.add('d-none');
        document.querySelector('.second-phase').classList.remove('d-none');
        document.querySelector('.third-phase').classList.add('d-none');

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
        viewAddressDetails();
    }

    // Address details handling
    function saveAddressDetails() {
        const selectedDeliveryPlace = document.querySelector('input[name="deliveryPlace"]:checked');
        if (!selectedDeliveryPlace) return;

        const type = selectedDeliveryPlace.id.replace('radio-', '');
        const section = document.querySelector(`.${type}-section`);
        if (!section) return;

        const inputs = section.querySelectorAll('input, select');
        const addressDetails = { type };

        inputs.forEach(input => {
            const nameKey = input.name.replace(`-${type}`, '');
            addressDetails[nameKey] = input.value.trim();
        });

        @auth('client')
            // For authenticated users, this will be handled by form submission
            // Just update the display for consistency
            viewAddressDetails();
        @else
            // For unauthenticated users, save to localStorage
            localStorage.setItem('addressDetails', JSON.stringify(addressDetails));
            ['TableReservation', 'takeaway_details', 'cart'].forEach(key => {
                localStorage.removeItem(key);
            });
            setCookie('branch_takaway', 'false', 7);
            viewAddressDetails();
        @endauth
    }

    function viewAddressDetails() {
        @auth('client')
            // For authenticated users, show the form data
            const formData = {
                name: document.querySelector('.delivery-section:not(.d-none) input[name^="name"]').value,
                num: document.querySelector('.delivery-section:not(.d-none) input[name^="num"]').value,
                floor: document.querySelector('.delivery-section:not(.d-none) input[name^="floor"]')?.value || '',
                phone: document.querySelector('.delivery-section:not(.d-none) input[name^="phone"]').value,
                country_code: document.querySelector('.delivery-section:not(.d-none) select[name^="country_code"]').value
            };
            const formattedAddress = `${formData.name} - ${formData.num}${formData.floor ? ' - ' + formData.floor : ''} (${formData.country_code})${formData.phone}`;
        @else
            // For unauthenticated users, use localStorage data
            const addressData = JSON.parse(localStorage.getItem('addressDetails')) || {};
            const formattedAddress = addressData.name ?
                `${addressData.name} - ${addressData.num || ''}${addressData.floor ? ' - ' + addressData.floor : ''} (${addressData.country_code || ''})${addressData.phone || ''}` :
                "No address data available";
        @endauth

        document.querySelector('#addressDisplay').textContent = formattedAddress;
    }

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map when Google Maps API is loaded
        if (typeof google !== 'undefined') {
            initMap();

            @auth('client')
                // For authenticated users editing an address
                @if($address && $address->area_id)
                    const areaSelect = document.getElementById('areaSelect');
                    if (areaSelect) {
                        areaSelect.value = "{{ $address->area_id }}";
                        checkArea(areaSelect);
                    }
                @endif
            @endauth
        } else {
            console.error("Google Maps API is not loaded.");
        }

        // Set up delivery place toggles
        document.querySelectorAll('.btn-check').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.delivery-section').forEach(section => {
                    section.classList.add('d-none');
                });
                document.querySelector(`.${radio.value}-section`).classList.remove('d-none');
            });
        });

        // Set up hotel select change handler
        document.getElementById('hotel_id')?.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const address = selectedOption.dataset.address;
            document.getElementById('detailhotel').value = address || '';
        });
    });

    // Search function
    window.searchLocation = function() {
        const query = document.getElementById('searchBox').value.trim();
        if (!query) {
            alert("@lang('header.enter_search_query')");
            return;
        }

        const language = document.documentElement.lang || 'en';
        const geocoder = new google.maps.Geocoder();

        geocoder.geocode({
            address: query,
            language: language,
            bounds: selectedAreaBounds
        }, (results, status) => {
            if (status === 'OK' && results[0]) {
                const location = results[0].geometry.location;
                map.setCenter(location);
                marker.setPosition(location);
                updatePosition(location);
                updateLocationDetails(location.lat(), location.lng());
            } else {
                alert("@lang('header.location_not_found')");
            }
        });
    };
</script>
@endpush
