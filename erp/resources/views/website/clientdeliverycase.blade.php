@auth('client')
    <div class="fourth-phase">
        <h5 class="fw-bold">
            @lang('header.deliverylocation')
        </h5>
        <div class="search-group">
            <input type="text" class="form-control" id="addressSearchauth" placeholder="@lang('header.search')" />
            <button type="button" onclick="filterAddresses()">
                <span class="search-icon">
                    <i class="fas fa-search"></i>
                </span>
            </button>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <h4 class="my-4 fw-bold"> @lang('header.myaddress')</h4>
            <button class="btn fw-bold" type="button"
                onclick="window.location.href='{{ route('create.Address', ['is_delivery' => 'true']) }}'">
                <span>+</span> @lang('auth.addaddress')
            </button>
        </div>

        <!-- Address List -->
        <ul class="list-unstyled px-0" id="addressListauth">
            @if (getUserAddress())
                @if (getUserAddress()->count() > 0)
                    @foreach (getUserAddress() as $item)
                        <li class="d-flex justify-content-between align-items-start mb-4 address-item">
                            <div class="address-content">
                                <h5>
                                    <i class="fas fa-city text-muted fa-xs ms-2"></i>
                                    @if($item->address_type === 'hotel')
                                        <small class="text-muted">
                                            {{ $item->hotel?->name }}
                                        </small>
                                    @else
                                        {{ $item->building }}
                                    @endif
                                </h5>
                                @if ($item->building === 'apartment' || $item->building === 'office')
                                    <small class="text-muted">
                                        {{ $item->address_type . ' - ' . $item->apartment_number . ' - ' . $item->floor_number . ' - ' . $item->address . ' , ' . $item->area?->name . ' , ' . $item->city?->name . ' , ' . $item->country?->name }}
                                    </small>
                                @elseif($item->address_type === 'hotel')
                                    <small class="text-muted">
                                        {{ $item->hotel?->address . ' - ' . $item->apartment_number . ' - ' . $item->area?->name . ' , ' . $item->city?->name . ' , ' . $item->country?->name }}
                                    </small>
                                @else
                                    <small class="text-muted">
                                        {{ $item->apartment_number . ' - ' . $item->area?->name . ' - ' . $item->city?->name . ' - ' . $item->country?->name }}
                                    </small>
                                @endif
                            </div>
                            <div class="dropdown">
                                <a id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <li id="default-btn-{{ $item->id }}" style="display: none;">
                                        <button class="dropdown-item w-100" onclick="setAuthAddress({{ $item->id }})">
                                            @lang('header.selectfordelivery')
                                        </button>
                                    </li>

                                    @if ($item->has_inprogress_or_pending_orders == 0)
                                        <li>
                                            <button class="dropdown-item w-100"
                                                onclick="window.location.href='{{ route('edit.Address', ['id' => $item->id]) }}'">
                                                @lang('auth.edit')
                                            </button>
                                        </li>
                                        @if (getUserAddress()->count() > 1)
                                            @if ($item->is_default === 0)
                                                <li>
                                                    <button class="dropdown-item w-100" data-bs-toggle="modal"
                                                        data-bs-target="#deleteaddressauthModal"
                                                        data-id="{{ $item->id }}">
                                                        @lang('auth.delete')
                                                    </button>
                                                </li>
                                            @endif
                                        @endif
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endforeach
                @endif
            @else
                <div class="p-5 w-50 text-center mx-auto mt-5">
                    <img class="noAddress-img"
                        src="{{ asset('front/AlKout-Resturant/SiteAssets/images/mdi_file-location.png') }}"
                        alt="" />
                    <h4 class="my-4 fw-bold">@lang('auth.noaddress')</h4>
                </div>
            @endif
        </ul>
    </div>
@endauth
@push('scripts')
    <script>
        let authAddress = localStorage.getItem('authaddress');

        const addressItems = @json(getUserAddress() ? getUserAddress()->pluck('id')->toArray() : []);

        addressItems.forEach(function(addressItem) {

            const buttonElement = document.getElementById(`default-btn-${addressItem}`);

            if (authAddress !== String(addressItem)) {
                if (buttonElement) {
                    buttonElement.style.display = "block";
                }
            }
        });

        function setAuthAddressAndClearCookies(id) {
            // Clear the lat and long cookies
            deleteCookie('latitude');
            deleteCookie('longitude');
            setCookie('locationPopupShown', true, 7);

            // Perform the required action (you can replace this with your custom logic)
            setAuthAddress(id);
        }

        function deleteCookie(name) {
            document.cookie = name + "=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; SameSite=Lax";
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteButtons = document.querySelectorAll('[data-bs-target="#deleteaddressauthModal"]');
            const deleteForm = document.getElementById('deleteaddressauthForm');

            deleteButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const addressId = button.getAttribute('data-id');
                    const deleteUrl = `{{ route('address.delete', ['id' => ':id']) }}`.replace(
                        ':id', addressId);
                    deleteForm.setAttribute('action', deleteUrl);
                });
            });
        });

        function setAuthAddress(addressId) {
            const branchIdtest = getCookie('branch_id');

            checkbranchallowdelivery(addressId, branchIdtest);
        }

        function checkbranchallowdelivery(addressId, branchIdtest) {
            $.ajax({
                url: '/check-branch-allow-delivery',
                method: 'GET',
                data: {
                    address: addressId,
                    branchId: branchIdtest
                },
                success: function(response) {

                    localStorage.setItem('authaddress', addressId);
                    ['TableReservation', 'takeaway_details', 'cart'].forEach(
                        key => {
                            if (localStorage.getItem(key)) {
                                localStorage.removeItem(key);
                                console.log(`Removed ${key} from localStorage`);
                            }
                        });

                    setCookie('branch_takaway', 'false', 7);
                    $.ajax({
                        url: '/set-new-address-session',
                        method: 'POST',
                        data: {
                            addressId: addressId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            if (localStorage.getItem('authaddress') == addressId) {
                                window.location.reload();
                            }
                        }
                    });
                    // window.location.href = "{{ route('menu') }}";
                },
                error: function(response) {
                    if (response.status == 400) {
                        shownotallow();
                    }
                }
            });
        }

        function shownotallow() {
            document.querySelector('#delivary').classList.add('d-none');
            document.querySelector('#notallowdelivary').classList.remove('d-none');
        }

        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
    </script>
    <script>
        // Store original address list HTML for reset
        let originalAddressesHTML = '';

        document.addEventListener('DOMContentLoaded', () => {
            // Get the original list HTML when the page loads
            originalAddressesHTML = document.getElementById('addressListauth').innerHTML;
        });

        function filterAddresses() {
            // Get the value of the search input and convert it to lowercase for case-insensitive search
            const searchInput = document.getElementById('addressSearchauth').value.toLowerCase();

            // Get the address list container
            const addressList = document.getElementById('addressListauth');

            // Get all the address list items
            const addresses = document.querySelectorAll('.address-item');

            // Array to hold the new filtered address HTML
            let filteredAddressesHTML = '';

            // Loop through each address item and filter
            addresses.forEach(item => {
                // Get the text content of the address item. We will check all text inside the item.
                const addressText = item.textContent || item.innerText;

                // If the address text contains the search input, show the address and add it to the filtered addresses array
                if (addressText.toLowerCase().includes(searchInput)) {
                    // Rebuild the list item HTML (include everything that was originally in each `li`)
                    filteredAddressesHTML += item.outerHTML;
                }
            });

            // If filtered addresses exist, update the address list HTML
            if (filteredAddressesHTML !== '') {
                addressList.innerHTML = filteredAddressesHTML;
            } else {
                // If no addresses match, show a message
                addressList.innerHTML = '<li class="text-center">No addresses found.</li>';
            }
        }

        // Reset search when input is cleared
        document.getElementById('addressSearchauth').addEventListener('input', (event) => {
            const searchInput = event.target.value;

            // If the input is empty, restore the original list
            if (searchInput === '') {
                document.getElementById('addressListauth').innerHTML = originalAddressesHTML;
            }
        });
    </script>
@endpush
