@extends('website.layouts.master')
@push('style')
@endpush
@section('content')
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"> @lang('auth.home')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('showAddress') }}"> @lang('header.myaddress')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @if ($address)
                            @lang('auth.editaddress')
                        @else
                            @lang('auth.addaddress')
                        @endif
                    </li>
                </ol>
            </nav>
        </div>

    </section>
    <section class="addresses">
        <div class="container pb-sm-5 pb-4">
            <div class="d-flex justify-content-between align-items-center">
            </div>
            <div class="card p-5 mt-3 mb-5">
                <div class="first-phase {{ $secondPhase == true ? 'd-none' : '' }}">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                     <div class="mb-3 d-flex ">
                        {{-- select area --}}
                        <select class="form-control me-4" id="areaSelect" onchange="checkArea(this)">
                            <option value="">@lang('header.select_area')</option>
                            @foreach (Areas() as $area)
                                <option value="{{ $area->id }}"
                                    {{ old('area_id', $address ? $address->area_id : '') == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}</option>
                            @endforeach
                        </select>
                        @error('area_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <h5 class="fw-bold">
                        @lang('header.deliverylocation') <span class="btn-info"> (@lang('header.move_mark'))</span>
                    </h5>
                    {{-- search scoop the selected area --}}
                    <div class="search-group ">
                        <span class="search-icon">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="searchBox" class="form-control" placeholder="@lang('header.search')" />

                        <button onclick="searchLocation()" class="btn">@lang('header.search')</button>
                    </div>
                    <div class="map position-relative my-3">
                        <div id="map" style="height: 500px;"></div>
                    </div>


                    <div class="tab-footer justify-content-between d-flex align-items-center">
                        <button type="button" class="btn" id="locationButton"
                            onclick="handleLocationPhase()">@lang('header.locationcomplete')
                        </button>

                    </div>
                </div>
                <div class="second-phase {{ $secondPhase == true ? '' : 'd-none' }}">
                    <form action="{{ route('handle.Address') }}" method="POST">
                        @csrf
                        <h6 class="fw-bold mb-3">
                            @lang('header.locationcomplete')
                        </h6>
                        <!-- Hidden input fields for latitude and longitude -->
                        <input type="hidden" name="is_delivery" value="{{ $isDelivery ? 'true' : 'false' }}">
                        <input type="hidden" name="is_cart" value="{{ $secondPhase == true ? 'true' : 'false' }}">

                        <input type="hidden" id="latitude" name="latitude"
                            value="{{ old('latitude', $address ? $address->latitude : null) }}">
                        <input type="hidden" id="longitude" name="longitude"
                            value="{{ old('longitude', $address ? $address->longtitude : null) }}">

                        <input type="hidden" name="id" value="{{ $address ? $address->id : null }}"> <input
                            type="hidden" id="area_id" name="area_id" value="{{ old('area_id') }}">


                        <div class="delivery-places px-0 mb-3">
                            <div class="btn-group" role="group" aria-label="Delivery Place Selector">
                                <!-- Apartment Radio Button -->
                                <input type="radio" class="btn-check" name="deliveryPlace" id="radio-home"
                                    autocomplete="off"
                                    {{ old('deliveryPlace', 'apartment') === 'apartment' ? 'checked' : '' }}
                                    value="apartment">
                                <label class="nav-link rounded-pill" for="radio-home">
                                    <i class="fas fa-city"></i> @lang('header.apartment')
                                </label>

                                <!-- Villa Radio Button -->
                                <input type="radio" class="btn-check"
                                    {{ old('deliveryPlace', $address ? $address->address_type : '') === 'villa' ? 'checked' : '' }}
                                    name="deliveryPlace" value="villa" id="radio-villa" autocomplete="off">
                                <label class="nav-link rounded-pill" for="radio-villa">
                                    <i class="fas fa-home"></i> @lang('header.villa')
                                </label>

                                <input type="radio" class="btn-check"
                                    {{ old('deliveryPlace', $address ? $address->address_type : '') === 'hotel' ? 'checked' : '' }}
                                    name="deliveryPlace" value="hotel" id="radio-hotel" autocomplete="off"
                                    onchange="onHotelSelected()">
                                <label class="nav-link rounded-pill" for="radio-hotel">
                                    <i class="fas fa-hotel"></i> @lang('header.hotel')
                                </label>

                                <!-- Office Radio Button -->
                                {{-- <input type="radio" class="btn-check"
                                    {{ old('deliveryPlace', $address ? $address->address_type : '') === 'office' ? 'checked' : '' }}
                                    name="deliveryPlace" value="office" id="radio-work" autocomplete="off">
                                <label class="nav-link rounded-pill" for="radio-work">
                                    <i class="fas fa-building"></i> @lang('header.office')
                                </label> --}}
                            </div>
                        </div>

                        <div class="delivery-content">
                            <div
                                class="delivery-section home-section {{ old('deliveryPlace', $address ? $address->address_type : 'apartment') === 'apartment' ? '' : 'd-none' }}">

                                <div class="mb-3 d-flex">
                                    <input type="text" class="form-control" name="nameapart"
                                        value="{{ old('nameapart', $address && $address->address_type === 'apartment' ? $address->building : null) }}"
                                        placeholder="@lang('header.nameapart')">
                                    @error('nameapart')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                    <span class="required_star">*</span>
                                </div>
                                <div class="mb-3 d-flex ">
                                    <input type="text" class="form-control "
                                        value="{{ old('numapart', $address && $address->address_type === 'apartment' ? $address->apartment_number : null) }}"
                                        name="numapart" placeholder="@lang('header.numapart')"><span
                                        class="required_star">*</span>
                                    @error('numapart')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                    <input type="text" class="form-control me-4"
                                        value="{{ old('floorapart', $address && $address->address_type === 'apartment' ? $address->floor_number : null) }}"
                                        name="floorapart" placeholder="@lang('header.Floor')"><span
                                        class="required_star">*</span>
                                    @error('floorapart')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                </div>
                                <div class="mb-3 d-flex">
                                    <input type="text" class="form-control"
                                        value="{{ old('addressdetailapart', $address && $address->address_type === 'apartment' ? $address->address : null) }}"
                                        name="addressdetailapart" placeholder="@lang('header.addressdetail')"><span
                                        class="required_star">*</span>
                                    @error('addressdetailapart')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control"
                                        value="{{ old('markapart', $address && $address->address_type === 'apartment' ? $address->notes : null) }}"
                                        name="markapart" placeholder="@lang('header.mark')">
                                    @error('markapart')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 d-flex">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            value="{{ old('phoneapart', $address && $address->address_type === 'apartment' ? $address->address_phone : null) }}"
                                            name="phoneapart" placeholder="@lang('header.phoneenter')">
                                        <select id="country" name="country_code_apart" class="selectpicker me-2"
                                            data-live-search="true">
                                            <option value="" selected disabled>@lang('header.select_country_code')</option>
                                            @foreach (GetCountries() as $country)
                                                <option
                                                    data-content='<img src="{{ $country->flag }}" class="flag-icon"> {{ $country->phone_code }}'
                                                    value="{{ $country->phone_code }}"
                                                    {{ old('country_code_apart', $address && $address->address_type === 'apartment' && $country->phone_code == $address->country_code ? $address->country_code : '') == $country->phone_code ? 'selected' : '' }}>
                                                    {{ $country->phone_code }}
                                                </option>
                                            @endforeach
                                        </select><span class="required_star">*</span>

                                    </div>
                                    @error('phoneenter')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                    @error('country_code_apart')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div
                                class="delivery-section villa-section {{ old('deliveryPlace', $address ? $address->address_type : '') === 'villa' ? '' : 'd-none' }}">

                                <div class="mb-3 d-flex">
                                    <input type="text" class="form-control"
                                        value="{{ old('namevilla', $address && $address->address_type === 'villa' ? $address->building : null) }}"
                                        name="namevilla" placeholder="@lang('header.namevilla')"><span
                                        class="required_star">*</span>
                                </div>
                                @error('namevilla')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mb-3 d-flex">
                                    <input type="text" class="form-control"
                                        value="{{ old('villanumber', $address && $address->address_type === 'villa' ? $address->apartment_number : null) }}"
                                        name="villanumber" placeholder="@lang('header.villanumber')"><span
                                        class="required_star">*</span>
                                </div>
                                @error('villanumber')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mb-3 d-flex">
                                    <input type="text" class="form-control"
                                        value="{{ old('addressdetailvilla', $address && $address->address_type === 'villa' ? $address->address : null) }}"
                                        name="addressdetailvilla" placeholder="@lang('header.addressdetail')"><span
                                        class="required_star">*</span>
                                </div>
                                @error('addressdetailvilla')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mb-3">
                                    <input type="text" class="form-control"
                                        value="{{ old('markvilla', $address && $address->address_type === 'villa' ? $address->notes : null) }}"
                                        name="markvilla" placeholder="@lang('header.mark')">
                                </div>
                                @error('markvilla')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mb-3 d-flex">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="phonevilla"
                                            value="{{ old('phonevilla', $address && $address->address_type === 'villa' ? $address->address_phone : null) }}"
                                            placeholder="@lang('header.phoneenter')">
                                        <select id="country" name="country_code_villa" class="selectpicker me-2"
                                            data-live-search="true">
                                            <option value="" selected disabled>@lang('header.select_country_code')</option>
                                            @foreach (GetCountries() as $country)
                                                <option
                                                    data-content='<img src="{{ $country->flag }}" class="flag-icon"> {{ $country->phone_code }}'
                                                    value="{{ $country->phone_code }}"
                                                    {{ old('country_code_villa', $address && $address->address_type === 'villa' && $country->phone_code == $address->country_code ? $address->country_code : '') == $country->phone_code ? 'selected' : '' }}>
                                                    {{ $country->phone_code }}
                                                </option>
                                            @endforeach
                                        </select><span class="required_star">*</span>
                                        @error('country_code_villa')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                @error('phonevilla')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </div>

                            <div
                                class="delivery-section hotel-section {{ old('deliveryPlace', $address ? $address->address_type : '') === 'hotel' ? '' : 'd-none' }}">
                                <div class="mb-3 {{ old('deliveryPlace', $address ? $address->address_type : '') === 'hotel' ? '' : 'd-none' }}"
                                    id="hotel_div">
                                    <p class="mb-2 text-muted">@lang('header.select_hotel')*</p>
                                    <select name="hotel_id" id="hotel_id" class="select2 form-control">
                                        <option value="" disabled selected>@lang('employee.select_hotel')</option>
                                        @if ($address && $address->address_type === 'hotel' && $address->hotel_id)
                                            @php $hotel = \App\Models\Hotel::find($address->hotel_id); @endphp
                                            <option value="{{ $address->hotel_id }}" selected
                                                data-address="{{ $hotel->address ?? '' }}">
                                                {{ $hotel->name ?? '' }}
                                            </option>
                                        @endif
                                    </select>
                                    <div class="wizard-form-error"></div>
                                    <div class="invalid-feedback">@lang('validation.EnterHotel')</div>
                                </div>
                                @error('hotelname')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mb-3 d-flex">
                                    <input type="text" class="form-control"
                                        value="{{ old('roomnumber', $address && $address->address_type === 'hotel' ? $address->apartment_number : null) }}"
                                        name="roomnumber" placeholder="@lang('header.roomnumber')"><span
                                        class="required_star">*</span>
                                </div>
                                @error('roomnumber')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mb-3 d-flex">
                                    <input type="text" class="form-control" name="addressdetailhotel"
                                        id="addressdetailhotel" readonly
                                        value="{{ old('addressdetailhotel', $address && $address->address_type === 'hotel' ? $address->hotel->address : null) }}"
                                        placeholder="@lang('header.addressdetail')">
                                </div>
                                @error('addressdetailhotel')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mb-3">
                                    <input type="text" class="form-control"
                                        value="{{ old('markhotel', $address && $address->address_type === 'hotel' ? $address->notes : null) }}"
                                        name="markhotel" placeholder="@lang('header.mark')">
                                </div>
                                @error('markhotel')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mb-3 d-flex">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="phonehotel"
                                            value="{{ old('phonehotel', $address && $address->address_type === 'hotel' ? $address->address_phone : null) }}"
                                            placeholder="@lang('header.phoneenter')">
                                        <select  name="country_code_hotel" class="selectpicker me-2"
                                            data-live-search="true">
                                            <option value="" selected disabled>@lang('header.select_country_code')</option>
                                            @foreach (GetCountries() as $country)
                                                <option
                                                    data-content='<img src="{{ $country->flag }}" class="flag-icon"> {{ $country->phone_code }}'
                                                    value="{{ $country->phone_code }}"
                                                    {{ old('country_code_hotel', $address && $address->address_type === 'hotel' && $country->phone_code == $address->country_code ? $address->country_code : '') == $country->phone_code ? 'selected' : '' }}>
                                                    {{ $country->phone_code }}
                                                </option>
                                            @endforeach
                                        </select><span class="required_star">*</span>
                                        @error('country_code_hotel')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                @error('phonehotel')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </div>
                        </div>

                        <div class="tab-footer justify-content-end d-flex">
                            <button type="submit" class="btn"> @lang('header.saveaddressc')</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>
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
@endsection
{{-- @include('website.address_js') --}}

@push('scripts')
    <script>
        let map, marker, pendingBranchId = null;
        let selectedAreaBounds = null;
        let selectedAreaName = null;
        let selectedAreaId = null;
        let initialLoad = true;

        // Set initial coordinates from address or default
        const defaultCoords = {
            lat: parseFloat("{{ old('latitude', $address ? $address->latitude : 29.9536889) }}"),
            lng: parseFloat("{{ old('longitude', $address ? $address->longitude : 31.097306) }}")
        };

        // Initialize Map
        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                center: defaultCoords,
                zoom: 13
            });

            marker = new google.maps.Marker({
                position: defaultCoords,
                map: map,
                draggable: true,
                title: 'Drag or click to update location'
            });

            // Event Listeners
            google.maps.event.addListener(marker, 'dragend', updatePosition);
            google.maps.event.addListener(map, 'click', (event) => {
                marker.setPosition(event.latLng);
                updatePosition(event.latLng);
            });

            // If editing an address, focus on the saved location
            if ("{{ $address }}" && initialLoad) {
                initialLoad = false;
                focusOnSavedLocation();
            }
        }

        // Focus on saved location when editing
        function focusOnSavedLocation() {
            const lat = parseFloat("{{ $address ? $address->latitude : '' }}");
            const lng = parseFloat("{{ $address ? $address->longitude : '' }}");

            if (lat && lng) {
                const savedLocation = new google.maps.LatLng(lat, lng);
                map.setCenter(savedLocation);
                map.setZoom(16);
                marker.setPosition(savedLocation);

                // Update hidden fields
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            }
        }

        // Update position and geocode
        function updatePosition(position) {
            const lat = position.lat();
            const lng = position.lng();

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            geocodePosition(position);
        }

        // Geocode position to address
        function geocodePosition(position) {
            new google.maps.Geocoder().geocode({
                location: position
            }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const addressInput = document.querySelector(
                        '.delivery-section:not(.d-none) input[name^="addressdetail"]');
                    if (addressInput) addressInput.value = results[0].formatted_address;
                }
            });
        }

        // Check area serviceability
        function checkArea(selectElement) {
            const areaId = selectElement.value;
            if (!areaId) return;
            selectedAreaId = areaId;

            // Update hidden area_id field
            document.getElementById('area_id').value = areaId;

            // Find the selected option text (area name)
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            selectedAreaName = selectedOption.text;

            $.get(`/check-area-branch/${areaId}`, (data) => {
                if (data.status === 'served_by_another_branch') {
                    pendingBranchId = data.branch;
                    new bootstrap.Modal('#notfoundddressModal').show();
                } else if (data.status === 'not_served') {
                    alert("@lang('auth.not_served_message')");
                }

                // Focus map on the selected area
                if (data.namearea) {
                    focusMapOnArea(data.namearea);
                }
            }).fail(console.error);
        }

        // Hotel selection handler
        function onHotelSelected() {
            if (!selectedAreaId) {
                alert("@lang('header.select_area_first')");
                return;
            }

            $('#hotel_div').removeClass('d-none');

            // Load hotels for the selected area
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

                    // If editing a hotel address, select the saved hotel
                    @if($address && $address->address_type === 'hotel' && $address->hotel_id)
                        $('#hotel_id').val("{{ $address->hotel_id }}").trigger('change');
                    @endif
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
            $('#addressdetailhotel').val(address);
            $('#addressdetailhotel').attr('readonly', true);

            // If this is a new hotel selection, geocode its address
            if (address) {
                geocodeHotelAddress(address);
            }
        });

        // Geocode hotel address to get coordinates
        function geocodeHotelAddress(address) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: address }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const location = results[0].geometry.location;
                    map.setCenter(location);
                    map.setZoom(16);
                    marker.setPosition(location);

                    // Update hidden fields
                    document.getElementById('latitude').value = location.lat();
                    document.getElementById('longitude').value = location.lng();
                }
            });
        }

        // Search location within selected area
        window.searchLocation = function() {
            const raw = document.getElementById('searchBox').value.trim();
            if (!raw) return;

            const fullQuery = selectedAreaName ? `${raw}, ${selectedAreaName}, Egypt` : raw;

            const geocoder = new google.maps.Geocoder();
            const request = {
                address: fullQuery
            };

            if (selectedAreaBounds) request.bounds = selectedAreaBounds;

            geocoder.geocode(request, (results, status) => {
                if (status === 'OK' && results[0]) {
                    let chosen = results.find(r =>
                        !selectedAreaBounds || selectedAreaBounds.contains(r.geometry.location)
                    ) || results[0];

                    if (selectedAreaBounds && !selectedAreaBounds.contains(chosen.geometry.location)) {
                        alert('@lang('header.location_outside_area')');
                        return;
                    }

                    const loc = chosen.geometry.location;
                    map.setCenter(loc);
                    marker.setPosition(loc);
                    updatePosition(loc);
                } else {

                    alert('@lang('header.location_not_found')');
                }
            });
        };

        // Switch branch handler
        document.getElementById('btn-switch-branch').addEventListener('click', () => {
            if (pendingBranchId) {
                localStorage.removeItem('cart');
                document.cookie = `branch_id=${pendingBranchId}; path=/; max-age=${60*60*24*30}`;
                location.reload();
            }
        });

        // DOM Ready
        document.addEventListener('DOMContentLoaded', () => {
            // Address type toggle
            document.querySelectorAll('.btn-check').forEach(radio => {
                radio.addEventListener('change', () => {
                    document.querySelectorAll('.delivery-section').forEach(section => {
                        section.classList.add('d-none');
                    });
                    document.querySelector(`.${radio.value}-section`).classList.remove('d-none');
                });
            });

            // Initialize map when Google Maps API is loaded
            if (typeof google !== 'undefined') {
                initMap();

                // If editing and area is set, focus on that area
                @if($address && $address->area_id)
                    const areaSelect = document.getElementById('areaSelect');
                    if (areaSelect) {
                        areaSelect.value = "{{ $address->area_id }}";
                        checkArea(areaSelect);
                    }
                @endif
            } else {
                console.error("Google Maps API is not loaded.");
            }
        });

        function isLocationSelected() {
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
                        let area = document.getElementById('area_id').value;

console.log(area);
 if (!area) {
                 toastr.warning("@lang('header.Please_select_area')");
                return false;
            }
            if (!lat || !lng) {
                                 toastr.warning("@lang('header.pick_location_message')");

                document.getElementById('map').scrollIntoView({
                    behavior: 'smooth'
                });
                document.getElementById('map').classList.add('border', 'border-danger');
                return false;
            }

            document.getElementById('map').classList.remove('border', 'border-danger');
            return true;
        }

        function handleLocationPhase() {
            if (isLocationSelected()) {
                             showSecondPhase();

            }
        }

         // Helper function to focus map on area
        function focusMapOnArea(areaName) {
            if (!areaName) return;
            selectedAreaName = areaName;

            document.body.style.cursor = 'progress';
            const geocoder = new google.maps.Geocoder();

            geocoder.geocode({
                address: `${areaName}, Egypt`
            }, (results, status) => {
                document.body.style.cursor = 'default';

                if (status === 'OK' && results[0]) {
                    const {
                        location,
                        viewport
                    } = results[0].geometry;
                    selectedAreaBounds = viewport;
                    map.fitBounds(viewport);

                    // Only move marker if we don't have existing coordinates
                    if (!"{{ $address }}" || initialLoad) {
                        marker.setPosition(location);
                        updatePosition(location);
                    }
                } else {
                    console.error(`Geocode failed: ${status}`);
                                        toastr.warning("@lang('header.move_mark')");

                }
            });
        }

    </script>
@endpush
