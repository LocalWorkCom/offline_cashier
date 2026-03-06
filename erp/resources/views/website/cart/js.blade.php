<script>
    $(document).ready(function() {
        const cartContainer = $('#item-list');
        const totalElement = $('#total-before-coupon');
        const totalPayElement = $('#total-pay');
        const shippingFeeElement = $('#shipping-value');
        const taxElement = $('#tax');
        const finalTotalElement = $('#total');
        const applyCouponButton = document.getElementById('apply-coupon-btn');
        const couponInput = document.getElementById('coupon');
        const removeCouponBtn = document.getElementById('remove-coupon-btn');
        const couponDiv = document.getElementById('coupon-div');
        const checkoutButton = $('#checkout-btn'); // Replace with your actual checkout button ID
        const SERVICE_FEES = 0;
        const service_fees_type = @json(getBranchSettings($branchId, 'service_fees_type'));
        const service_fees = @json(getBranchSettings($branchId, 'service_fees'));
        var tax_application_check = @json(getBranchSettings($branchId, 'tax_application'));
        var takeaway_check = getCookie('branch_takaway');
        let local_storage = JSON.parse(localStorage.getItem('cart')) || {};
        let local_storage_2 = JSON.parse(localStorage.getItem('TableReservation')) || {};
        let takeaway_details = JSON.parse(localStorage.getItem('takeaway_details')) || {};

        let currency_symbol = local_storage.symbol;

        const element = document.getElementById("total-before-coupon"); //nourhan
        @php
            $sessionAddressId = session('new_address_id'); // This should come from JS via AJAX or a redirect
        @endphp


        const SHIPPING_FEES = (takeaway_check === "false" &&
                local_storage.order_type !== 'reservation' &&
                local_storage_2.reservType !== 'with') ?
            @json(getDeliveryFees($sessionAddressId, $branchId)) :
            0;
            

        const TAX_RATE = tax_application_check == 0 ?
            @json(getBranchSettings($branchId, 'tax_percentage')) / 100 : 0;
        const coupon_application = @json(getBranchSettings($branchId, 'coupon_application')) /
            100;
        const formatCurrency = (amount) => `${amount.toFixed(2)} ${currency_symbol}`;

        let cart = local_storage.items; // Extract all items from the array
        let totalElementCoupon = 0;
        let branch_cart = getCookie('branch_id');
        let totalAmount = 0;
        let isAuthenticated = @json(auth('client')->user());
        let address = localStorage.getItem('authaddress');

        var lat_cart = getCookie('latitude');
        var long_cart = getCookie('longitude');
        // Function to extract the numeric value
        function updateTotal() { //nourhan
            const text = element.textContent.trim(); //nourhan
            const am = parseFloat(text.match(/[\d,.]+/)[0]) || 0; //nourhan
            totalAmount = am; // Update the variable //nourhan
        }
        // Create a MutationObserver to watch for text changes
        const observer = new MutationObserver(updateTotal); //nourhan

        // Start observing changes in the element's text
        observer.observe(element, {
            childList: true,
            subtree: true
        }); //nourhan

        updateTotal(); //nourhan

        function reverseGeocode(lat, lng) {
            const geocoder = new google.maps.Geocoder();
            const latLng = new google.maps.LatLng(lat, lng);

            geocoder.geocode({
                location: latLng
            }, (results, status) => {
                if (status === "OK" && results[0]) {
                    const result = results[0];
                    const addressComponents = result.address_components;


                    // Extract city using multiple fallback types
                    const city = addressComponents.find(component =>
                        component.types.includes('locality') ||
                        component.types.includes('administrative_area_level_1') ||
                        component.types.includes('sublocality')
                    )?.long_name || 'N/A';

                    // Extract street using fallback logic
                    const street = addressComponents.find(component =>
                        component.types.includes('route') ||
                        component.types.includes('neighborhood') ||
                        component.types.includes('political')
                    )?.long_name || 'N/A';

                    // Update the UI
                    document.getElementById('address-city').textContent = city;
                    document.getElementById('address-type').textContent = street;
                    document.getElementById('edit-address-v1').addEventListener('click', (event) => {
                        if (isAuthenticated) {
                            // Set the href attribute if authenticated
                            if (address) {
                                $('#edit-address-v1')
                                    .attr('data-bs-toggle', 'modal')
                                    .attr('data-bs-target', '#deliveryModal');
                            } else {
                                event.currentTarget.setAttribute('href',
                                    '{{ route('create.Address') }}');
                            }

                        } else {
                            // Prevent default behavior and open login modal
                            event.preventDefault();
                            $('#edit-address-v1')
                                .attr('data-bs-toggle', 'modal')
                                .attr('data-bs-target', '#loginModal');
                        }
                    });

                    // Show the address container
                    document.getElementById('cart-address-v1').classList.remove('d-none');
                    document.getElementById('cart-address-v2').classList.add('d-none');
                } else {
                    console.error('Geocoding failed:', status);
                }
            });
        }

        function recalculateTotalPrice(version, dishPrice, data) {
            let modal = $(`#productModal${version}`);
            let selectedSizePrice = parseFloat(modal.find('.size-option:checked')
                .val()) || 0;
            let price = 0;
            let addonsPrice = 0;
            if (selectedSizePrice) {
                price = selectedSizePrice;
            } else {
                price = dishPrice;

            }
            let selectedAddons = modal.find('.addon-option:checked').length;


            // Calculate addons price
            modal.find('.addon-option:checked').each(function() {
                addonsPrice += parseFloat($(this).val());
            });

            if (data.dish.has_addon != 0) {
                if (selectedAddons < data.min || selectedAddons > data.max) {
                    // Remove any existing error messages
                    modal.find('#addon-error').remove();

                    // Display error message
                    modal.find(`#div-addons`).append(`
                                    <div id="addon-error" class="text-danger">
                                        @lang('cart.maxNote') ${data.max} @lang('cart.choicese')
                                    </div>
                                `);

                    // Disable all add-ons if the max limit is reached
                    if (selectedAddons >= data.max) {
                        modal.find('.addon-option:not(:checked)').prop('disabled', true);
                    }

                    // Disable the "Add to Cart" button
                    modal.find(`#submit${version}`).prop('disabled', true);
                } else {
                    // Remove error message if validation passes
                    modal.find('#addon-error').remove();

                    // Enable all add-ons if under the max limit
                    modal.find('.addon-option').prop('disabled', false);

                    // Enable the "Add to Cart" button
                    modal.find(`#submit${version}`).prop('disabled', false);
                }
            }


            let quantity = parseInt(modal.find('.num').text()) || 1;
            let newTotalPrice = (price * quantity) + addonsPrice * quantity;

            // Update total price in the total-price span
            $(`#total-price${version}`).text(formatCurrency(newTotalPrice));

            // Update the dish-price span
            $(`#dish-total${version}`).html(formatCurrency(newTotalPrice));

        }

        function checkAddressBranchAllow() {
            let address = localStorage.getItem('authaddress');
            let branchId = getCookie('branch_id');

            if (!address || Object.keys(address).length === 0 || !branchId) {
                showChangeAddressModal();
                return;
            }
            $.ajax({
                url: '/check-branch-allow-delivery',
                method: 'GET',
                data: {
                    address: address,
                    branchId: branchId
                },
                success: function(response) {

                    window.location.href = "{{ route('cart.checkout') }}";
                },
                error: function(response) {
                    console.log("34344");

                    showChangeAddressModal();

                }
            });


        }

        function showChangeAddressModal() {
            // Open the modal to change the address
            $('#locationModal').modal('show');
        }
        $(document).on('click', '.inc', function() {

            const index = $(this).data('index');
            cart[index].quantity++;
            updateCart();
        });

        $(document).on('click', '.dec', function() {

            const index = $(this).data('index');
            if (cart[index].quantity > 1) {
                cart[index].quantity--;
                updateCart();
                updateTotal(); //nourhan
                validateCoupon(couponInput.value.trim(),
                    totalAmount); //nourhan from totalElementCoupon to totalAmount
            }
        });

        $(document).on('click', '.delete-item', function() {

            const index = $(this).data('index');
            cart.splice(index, 1);
            if (cart.length === 0) {
                localStorage.setItem('cart', []);
                localStorage.coupon = '';
                localStorage.couponValue = '';
                localStorage.notes = '';
                document.getElementById('cart-count').textContent = 0;

                // If empty, remove the entire cart from localStorage
                // localStorage.removeItem('cart');
            } else {
                // Otherwise, update the cart in localStorage
                localStorage.setItem('cart', JSON.stringify(cart));
            }
            updateCart();
        });

        $(document).on('click', '.edit-item', function() {

            const index = $(this).data('index');
            const item = cart[index];

            editItem(item, index);
        });

        checkoutButton.on('click', function(e) {
            e.preventDefault(); // Prevent default form submission or navigation
            updateCartBeforeCheckout();
            var istakeaway = getCookie('branch_takaway');

            const isAuthenticated = @json(auth('client')->check());

            if (!isAuthenticated) {
                const loginModal = document.querySelector('#loginModal');
                if (loginModal) {
                    const modalInstance = new bootstrap.Modal(loginModal);
                    $('#msg-error').show();
                    modalInstance.show();
                } else {}
            } else {

                if (Object.keys(takeaway_details).length == 0 && local_storage.order_type !=
                    'reservation') {

                    const addressCard1 = document.querySelector('#cart-address-v1');
                    const addressCard2 = document.querySelector('#cart-address-v2');

                    if (addressCard2 && !addressCard2.classList.contains('d-none')) {
                        Swal.fire({
                            title: '@lang('cart.missing address !')',
                            text: '@lang('cart.you should select address to complete your order')',
                            icon: 'warning',
                            confirmButtonText: '@lang('cart.okay')'

                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href =
                                    "{{ route('create.Address') }}";
                            }
                        });
                    } else if (addressCard1 && !addressCard1.classList.contains('d-none')) {
                        let addressCeckout = localStorage.getItem('authaddress');
                        let nolat = {{ session('nolat', false) ? 'true' : 'false' }};

                        // if (addressCeckout && ((!lat_cart && !long_cart) || nolat)) {
                        if (addressCeckout) {
                            checkAddressBranchAllow();
                        } else if (lat_cart && long_cart) {
                            Swal.fire({
                                title: '@lang('cart.missing address !')',
                                text: '@lang('cart.you should select address to complete your order')',
                                icon: 'warning',
                                confirmButtonText: '@lang('cart.complete_address')'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href =
                                        "{{ route('create.Address') }}";
                                }
                            });
                        }

                    } else {
                        console.log('Cart updated before checkout:');
                    }

                } else {

                    window.location.href = "{{ route('cart.checkout') }}";
                }

            }
        });


        const istakeaway = getCookie('branch_takaway');
        const renderCart = () => {
            const translations = @json(__('cart'));
            var address_title = 'موقع التوصيل';
            var open_reservation = false;
            var open_takeaway = false;
            var open_address = true;
            let table_reservation = JSON.parse(localStorage.getItem('TableReservation')) || {};
            if (Object.keys(table_reservation).length > 0) {
                order_type = 'reservation';
                local_storage.order_type = order_type;
                localStorage.setItem('cart', JSON.stringify(local_storage));

            }

            if (local_storage.order_type == 'reservation') {
                address_title = 'حجز طاولة';
                open_reservation = true;
                open_takeaway = false;
                open_address = false;

                $('#table-name').text(local_storage_2.tableName);
                $('#table-time').text(local_storage_2.tableSession);
                $('#table-date').text(local_storage_2.date);
                $('#persons').text((local_storage_2.personal.adult + local_storage_2.personal.men +
                        local_storage_2.personal.women) +
                    " شخص ");
                $('#kids').text(local_storage_2.personal.kids + " طفل ");
                $('#table-leave-time').text(local_storage_2.leaveTime == "" ? "=====" : local_storage_2
                    .leaveTime);
                $('#table-place').text(local_storage_2.tablePlace);

            } else if (local_storage.order_type == 'Takeaway') {

                address_title = 'تفاصيل موقع الاستلام';
                open_takeaway = true;
                open_reservation = false;
                open_address = false;
            } else if (local_storage_2.reservType == 'with') {

                open_reservation = true;
                $('#table-name').text(local_storage_2.tableName);
                $('#table-time').text(local_storage_2.tableSession);
                $('#table-date').text(local_storage_2.date);
                $('#persons').text((local_storage_2.personal.adult + local_storage_2.personal.men) +
                    " شخص ");
                $('#kids').text(local_storage_2.personal.kids + " طفل ");
                $('#table-leave-time').text(local_storage_2.leaveTime == "" ? "=====" : local_storage_2
                    .leaveTime);
                $('#table-place').text(local_storage_2.tablePlace);

            }
            $('#address-title-v1').text(address_title);
            $('#address-title-v2').text(address_title);
            $('#address-title-v3').text(address_title);

            if (open_address) {
                $('#edit-address-v2')
                    .attr('data-bs-toggle', 'modal')
                    .attr('data-bs-target', '#deliveryModal');
                $('#edit-address-v1')
                    .attr('data-bs-toggle', 'modal')
                    .attr('data-bs-target', '#deliveryModal');
                $('#edit-address-v3')
                    .attr('data-bs-toggle', 'modal')
                    .attr('data-bs-target', '#deliveryModal');
            } else if (open_reservation) {
                $('#edit-address-v2')
                    .attr('data-bs-toggle', 'modal')
                    .attr('data-bs-target', '#table-reservation');
                $('#edit-address-v1')
                    .attr('data-bs-toggle', 'modal')
                    .attr('data-bs-target', '#table-reservation');
                $('#edit-address-v3')
                    .attr('data-bs-toggle', 'modal')
                    .attr('data-bs-target', '#table-reservation');
            } else if (open_takeaway) {
                $('#edit-address-v2')
                    .attr('data-bs-toggle', 'modal')
                    .attr('data-bs-target', '#deliveryModal');
                $('#edit-address-v1')
                    .attr('data-bs-toggle', 'modal')
                    .attr('data-bs-target', '#table-reservation');
                $('#edit-address-v3')
                    .attr('data-bs-toggle', 'modal')
                    .attr('data-bs-target', '#branchSelectionModal');


            }

            if (istakeaway === 'true' && Object.keys(takeaway_details).length > 0) {
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
                            $('#branch_receive_name').text(data.name_site);
                            $('#cart-address-v3').removeClass('d-none');
                            $('#cart-address-v2').addClass('d-none');

                        }
                    },
                    error: function() {
                        $('#cart-address-v1').addClass('d-none');
                        $('#cart-address-v2').removeClass('d-none');
                    }
                });
                $('#reservation-card').hide();

            } else if (open_reservation) {
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
                            $('#address-desc-v1').text(data.address);
                            $('#address-name').text(data.name_site);
                            $('#address-type').hide();
                            $('#cart-address-v1').removeClass('d-none');
                            $('#cart-address-v2').addClass('d-none');
                        }
                    },
                    error: function() {
                        $('#cart-address-v1').addClass('d-none');
                        $('#cart-address-v2').removeClass('d-none');
                    }
                });
                $('#reservation-card').show();
            } else {
                $('#reservation-card').hide();

                var address_localstorage = JSON.parse(localStorage.getItem('addressDetails'));
                var address_session =
                    {{ session()->has('new_address_id') ? session('new_address_id') : 'null' }};
                //check if branch has delivery
                var branch_status = Number(@json(getBranchSettings($branchId, 'is_delivery')));
                if (branch_status === 1) { // Ensure it's a number
                    $('#checkout-btn').removeClass('disabled');
                } else {
                    $('#checkout-btn').addClass('disabled');
                }
                //end of check if branch has delivery

                if (isAuthenticated) {

                    let userAuth = '{{ auth('client')->user() ? auth('client')->user()->id : 0 }}';
                    if (address_session) {

                        $.ajax({
                            url: "{{ route('get-address-detail') }}",
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id: address_session
                            },
                            success: function(data) {
                                if (data.data) {
                                    const addressType = data.data.address_type || '';
                                    const localizedAddressType = translations[addressType] ||
                                        addressType;


                                    // Update the text in the DOM
                                    $('#address-type').text(localizedAddressType);
                                    // Populate the address details in the corresponding elements
                                    $('#address-name').text(data.data.address || '');
                                    $('#address-city').text(data.data.city || '');
                                    $('#address-building').text(data.data.building || '');
                                    $('#address-floor').text(data.data.floor_number || '');
                                    $('#address-num').text(data.data.apartment_number || '');
                                    // This part of the code is dependent on a successful geolocation response
                                    // $('#edit-address-v1')
                                    //     .attr('data-bs-toggle', 'modal')
                                    //     .attr('data-bs-target', '#deliveryModal');
                                    // Show cart-address-v1 and hide cart-address-v2

                                    $('#cart-address-v1').removeClass('d-none');
                                    $('#cart-address-v2').addClass('d-none');
                                }
                            },
                            error: function() {
                                $('#cart-address-v1').addClass('d-none');
                                $('#cart-address-v2').removeClass('d-none');
                            }
                        });

                    } else if (long_cart && lat_cart) {
                        reverseGeocode(lat_cart, long_cart);

                    } else if (address) {

                        $.ajax({
                            url: "{{ route('get-address-detail') }}",
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id: address
                            },
                            success: function(data) {
                                if (data.data) {
                                    const addressType = data.data.address_type || '';
                                    const localizedAddressType = translations[addressType] ||
                                        addressType;


                                    // Update the text in the DOM
                                    $('#address-type').text(localizedAddressType);
                                    // Populate the address details in the corresponding elements
                                    $('#address-name').text(data.data.address || '');
                                    $('#address-city').text(data.data.city || '');
                                    $('#address-building').text(data.data.building || '');
                                    $('#address-floor').text(data.data.floor_number || '');
                                    $('#address-num').text(data.data.apartment_number || '');
                                    // This part of the code is dependent on a successful geolocation response
                                    // $('#edit-address-v1')
                                    //     .attr('data-bs-toggle', 'modal')
                                    //     .attr('data-bs-target', '#deliveryModal');
                                    // Show cart-address-v1 and hide cart-address-v2

                                    $('#cart-address-v1').removeClass('d-none');
                                    $('#cart-address-v2').addClass('d-none');
                                }
                            },
                            error: function() {
                                $('#cart-address-v1').addClass('d-none');
                                $('#cart-address-v2').removeClass('d-none');
                            }
                        });
                    } else {
                        $('#cart-address-v2').removeClass('d-none');

                    }
                } else {
                    if (long_cart && lat_cart && !address_localstorage) {
                        reverseGeocode(lat_cart, long_cart);

                    } else if (address_localstorage) {
                        const addressType = address_localstorage.type || '';
                        const localizedAddressType = translations[addressType] ||
                            addressType;
                        $('#address-type').text(localizedAddressType);
                        $('#address-name').text(address_localstorage.name || '');
                        $('#address-city').text(address_localstorage.city || '');
                        $('#address-floor').text(address_localstorage.floor || '');
                        $('#address-num').text(address_localstorage.num || '');
                        $('#edit-address-v1')
                            .attr('data-bs-toggle', 'modal')
                            .attr('data-bs-target', '#deliveryModal');
                        $('#cart-address-v1').removeClass('d-none');
                        $('#cart-address-v2').addClass('d-none');
                    } else {
                        $('#cart-address-v2').removeClass('d-none');
                    }
                }
            }


            ///////////////////////////////////// address /////////////////////////////////
            cartContainer.empty();
            let total = 0;

            if (!Array.isArray(cart) || cart.length === 0) { // Check if cart is a valid array
                cartContainer.html('<p class="text-center">@lang('cart.cart is empty')</p>');
                updateCartSummary(0);
                $('#card-payment').addClass('d-none');
                $('#checkout-btn').addClass('d-none');
                $('#note-div').addClass('d-none');
                $('#cart-address').addClass('d-none');
                return;
            }
            // const address = '{{ $address }}';

            // if (address) {
            //$('#checkout-btn').addClass('disabled', true);
            // }
            cart.forEach((item, index) => {
                const itemSizePrice = item.size && item.size.price ? item.size.price :
                    item.price; // Check if size exists and has a price
                const itemAddons = item.addons || [];
                const addonTotal = itemAddons.reduce((sum, addon) => sum + parseFloat(addon.price ||
                    0), 0);

                const itemTotal = (item.quantity * itemSizePrice) + addonTotal * item.quantity;



                const itemSizeLabel = item.size && item.size.label ? item.size.label :
                    '@lang('cart.unknown')'; // Fallback if size is empty or undefined

                total += itemTotal;

                cartContainer.append(`
                <div class="sideCart-plate p-4 mb-4" data-index="${index}">
                    <div class="d-flex flex">
                        <a href="#">
                            <figure class="sideCart-plate-img m-0">
                                <img src="${item.image}" alt="${item.name}">
                            </figure>
                        </a>
                        <div class="cart-details pe-5">
                            <h5>${item.name}</h5>
                            <small class="text-muted d-block"><span>@lang('cart.size'):</span> ${itemSizeLabel}</small> <!-- Display the fallback text if size is empty -->
                            <small class="text-muted d-block"><span>@lang('cart.add'):</span> ${itemAddons.map(addon => addon.name).join(', ') || '@lang('cart.no addons')'}</small>
                            <small class="text-muted d-block"><span>@lang('cart.notes'):</span> ${item.notes || '@lang('cart.no any notes')'}</small>
                            <div class="qty mt-3">
                                <span class="dec minus" data-index="${index}"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                <span class="num">${item.quantity}</span>
                                <span class="inc plus" data-index="${index}"><i class="fa fa-plus" aria-hidden="true"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column justify-content-end">
                        <p class="fw-bold">${formatCurrency(itemTotal)}</p>
                        <div class="btns text-center">
                            <button class="btn reversed main-color mb-2 edit-item" data-index="${index}" type="button">@lang('cart.edit')</button>
                            <button class="btn mb-2 delete-item" data-index="${index}" type="button">@lang('cart.delete')</button>
                        </div>
                    </div>
                </div>
                `);
            });

            updateCartSummary(total, local_storage.coupon_value);

            checkAndRemoveInvalidCoupon();

            if (local_storage.coupon) {

                couponDiv.classList.remove('d-none');
                couponInput.value = local_storage.coupon;
                $('#code').html(`${local_storage.coupon}`);
                applyCouponButton.disabled = true;
                couponInput.disabled = true;

                $('#discount-value').text(`-${local_storage.coupon_value.toFixed(2)} ${currency_symbol}`);

                removeCouponBtn.classList.remove('d-none');
            }
            if (local_storage.notes) {
                $('#note-order').text(local_storage.notes);
                console.log(local_storage.notes);

            }

        };

        // Helper function to update the cart summary
        const updateCartSummary = (total, coupon) => {
            let tax = total * TAX_RATE;
            if (local_storage.order_type == 'reservation' && local_storage_2.reservType == 'with') {
                $('#total-value').html(`${formatCurrency(total)}`);
                service_fees_total = total * (service_fees / 100);

                total = service_fees_type === 'fixed' ?
                    (parseFloat(total) + parseFloat(service_fees)) :
                    (parseFloat(total) + parseFloat(service_fees_total));
                console.log(total);
                tax = total * TAX_RATE;
                var service_div = document.getElementById('service-div');
                var total_div = document.getElementById('total-div');
                service_div.classList.remove('d-none');
                total_div.classList.remove('d-none');

                $('#service_fees').html(
                    `${formatCurrency(service_fees_type === 'fixed'
        ? parseFloat(service_fees)
        : parseFloat(service_fees_total))}`);

            }
            const finalTotal = parseFloat(total) - parseFloat(coupon) + parseFloat(SHIPPING_FEES) +
                parseFloat(tax);

            totalElement.text(formatCurrency(total));


            if (local_storage.order_type != 'reservation' && local_storage_2.reservType != 'with') {
                // serviceFeeElement.text(formatCurrency(SERVICE_FEES));
                shippingFeeElement.text(formatCurrency(parseFloat(SHIPPING_FEES)));

            } else {
                $('#shipping-div').hide();
            }
            finalTotalElement.text(formatCurrency(finalTotal));
            console.log(TAX_RATE);
            console.log("dkjshfvdf");

            console.log(tax_application_check);

            if (tax_application_check == 0) {
                taxElement.text(formatCurrency(tax));
            } else {
                taxElement.hide();
            }
            totalPayElement.text(formatCurrency(finalTotal));
            totalElementCoupon = finalTotal;
            console.log(finalTotal);

        };
        // Helper function to update localStorage and re-render the cart
        const updateCart = (isDecrement = false) => {
            if (couponInput.value.trim() !== '' && isDecrement) {
                removeCoupon();
            }
            local_storage.items = cart;
            localStorage.setItem('cart', JSON.stringify(local_storage));
            renderCart();
        };
        const updateCartBeforeCheckout = () => {
            // Get coupon details
            const couponValueText = $('#discount-value').text().trim();

            const couponCode = couponInput.value.trim() || null;
            const couponValue = couponValueText ?
                Math.abs(parseFloat(couponValueText.replace(/[^\d.-]/g, ''))) // Extract numeric value
                :
                0;
            // Get notes
            const notes = $('#note-order').val().trim();
            let table_reservation = JSON.parse(localStorage.getItem('TableReservation')) || {};
            let order_type = 'Delivery';
            if (Object.keys(table_reservation).length > 0) {
                order_type = 'reservation';
            } else {
                if (istakeaway == 'true' && Object.keys(takeaway_details).length > 0) {
                    order_type = 'Takeaway';
                }
            }
            // Update cart object with additional data
            const updatedCart = {
                items: cart, // Existing cart items
                coupon: couponCode,
                coupon_value: couponValue,
                notes: notes,
                order_type: order_type,
                symbol: currency_symbol
            };

            // Save updated cart to localStorage
            localStorage.setItem('cart', JSON.stringify(updatedCart));

        };

        const removeCoupon = () => {
            // Reset the coupon input field and re-enable it
            const updatedCart = {
                items: cart, // Existing cart items
                coupon: '',
                coupon_value: 0,
                notes: local_storage.notes,
                order_type: local_storage.order_type,
                symbol: currency_symbol
            };

            // Save updated cart to localStorage
            localStorage.setItem('cart', JSON.stringify(updatedCart));
            couponInput.value = '';
            couponInput.disabled = false;

            applyCouponButton.disabled = false;
            removeCouponBtn.classList.add('d-none');
            couponDiv.classList.add('d-none');
            renderCart();
            Swal.fire({
                icon: 'info',
                title: '@lang('cart.deleted successfully')',
                text: '@lang('cart.coupon deleted successfully')',

            });
            console.log(cart);

            // updateCartSummary(cart.total, 0);
        };
        // AJAX call for editing item
        const editItem = (item, index) => {
            $.ajax({
                url: "{{ route('cart.dish-detail') }}",
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id: item.dish_id
                },
                success: function(data) {
                    if (data.status === 'success') {
                        var version = data.dish.has_size || data.dish.has_addon ? '_v1' : '_v2';
                        let modal = $(`#productModal${version}`);
                        const product = data.dish;
                        const dishPrice = parseFloat(product.price);

                        // Reset previous selections before applying new values
                        modal.find('.size-option').prop('checked', false); // Uncheck all sizes
                        modal.find('.addon-option').prop('checked',
                            false); // Uncheck all addons
                        modal.find('#note' + version).val(''); // Clear notes input
                        modal.find(`#submit${version}`).prop('disabled', false);

                        let dishHtml = `
                                <h5>${product.name}</h5>
                                ${product.mostOrdered ? `<span class="badge bg-warning text-dark"><i class="fas fa-star"></i>@lang('cart.most rated')</span>` : ''}
                                <small class="text-muted d-block py-2">${product.description}</small>
                                <h4 class="fw-bold">
                                    <span class="total-price" data-unit-price="${dishPrice}" id="total-price${version}">${formatCurrency(dishPrice)}</span>

                                </h4>
                                <div class="qty mt-3 d-flex justify-content-center align-items-center">
                                    <span class="pro-dec me-3" onclick="decreaseQuantity(this)"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                    <span class="num fs-4">${item.quantity}</span>
                                    <span class="pro-inc ms-3" onclick="increaseQuantity(this)"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                </div>
                                `;
                        $(`#dish-id${version}`).val(product.id);
                        $(`#dish-img${version}`).attr('src', product.image);
                        $(`#div-detail${version}`).html(dishHtml);
                        $(`#note${version}`).val(item.notes || '');
                        var itemTotal = 0;
                        if (data.dish.has_size && data.dish.has_addon) {
                            itemTotal = 0;
                            itemTotal = (item.quantity * item.size.price) +
                                (item.addons && item.addons.length > 0 ?
                                    (item.addons.reduce((sum, addon) => sum + addon.price, 0) *
                                        item.quantity) : 0
                                );

                            populateSizes(data.sizes, item, data.branch.currency_symbol);
                            populateAddons(data.addons, item, data.branch.currency_symbol, data
                                .min, data.max);
                        } else if (data.dish.has_addon && !data.dish.has_size) {
                            itemTotal = 0;
                            itemTotal = (item.quantity * item.price) +
                                (item.addons && item.addons.length > 0 ?
                                    (item.addons.reduce((sum, addon) => sum + addon.price, 0) *
                                        item.quantity) : 0
                                );
                            populateAddons(data.addons, item, data.branch.currency_symbol, data
                                .min, data.max);
                            $('#sizes-div').hide();
                        } else if (data.dish.has_size && !data.dish.has_addon) {
                            itemTotal = 0;
                            itemTotal = (item.quantity * item.size.price);
                            populateSizes(data.sizes, item, data.branch.currency_symbol);
                            $('#addons-div').hide();
                        } else {
                            itemTotal = 0;
                            itemTotal = (item.quantity * item.price);
                            $('#sizes-div').hide();
                            $('#addons-div').hide();
                        }
                        $(`#total-price${version}`).html(formatCurrency(itemTotal));
                        $(`#dish-total${version}`).html(formatCurrency(itemTotal));
                        // let dishPrice = parseFloat(data.dish.price);
                        $('#div-sizes .size-option').on('change', function() {
                            const selectedSize = parseFloat($(this).data('price')) ||
                                dishPrice; // Get the selected size price or fallback to dish price
                            recalculateTotalPrice(version, selectedSize, data);
                        });

                        $('#div-addons .addon-option').on('change', function() {
                            recalculateTotalPrice(version,
                                dishPrice, data
                            ); // Addons are handled dynamically in the recalculation
                        });


                        let selectedEditAddons = modal.find('.addon-option:checked').length;
                        if (data.dish.has_addon != 0) {
                            if (selectedEditAddons < data.min || selectedEditAddons > data
                                .max) {
                                // Remove any existing error messages
                                modal.find('#addon-error').remove();

                                // Display error message
                                modal.find(`#div-addons`).append(`
                                            <div id="addon-error" class="text-danger">
                                                @lang('cart.maxNote') ${data.max} @lang('cart.choicese')
                                            </div>
                                        `);

                                // Disable all add-ons if the max limit is reached
                                if (selectedEditAddons >= data.max) {
                                    modal.find('.addon-option:not(:checked)').prop('disabled',
                                        true);
                                }

                                // Disable the "Add to Cart" button
                                modal.find(`#submit${version}`).prop('disabled', true);
                            } else {
                                modal.find('#addon-error').remove();
                                modal.find('.addon-option').prop('disabled', false);
                                modal.find(`#submit${version}`).prop('disabled', false);
                            }
                        }

                        window.increaseQuantity = function(ele) {

                            let quantityElem = $(ele).siblings('.num');
                            let quantity = parseInt(quantityElem.text()) || 1;
                            quantity++;
                            quantityElem.text(quantity);
                            recalculateTotalPrice(version, dishPrice, data);

                        };

                        window.decreaseQuantity = function(ele) {
                            let quantityElem = $(ele).siblings('.num');
                            let quantity = parseInt(quantityElem.text()) || 1;
                            if (quantity > 1) {
                                quantity--;
                                quantityElem.text(quantity);
                                recalculateTotalPrice(version, dishPrice, data);
                            }
                        };

                        $('.submit').off('click').on('click', function() {
                            saveChanges(index, data.dish.has_size, data.dish.has_addon);
                            updateTotal(); //nourhan
                            validateCoupon(couponInput.value.trim(),
                                totalAmount
                            ); //nourhan from totalElementCoupon to totalAmount
                        });
                        modal.modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to fetch product details.',
                            text: 'Failed to fetch product details.',

                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: "@lang('cart.wrong')",
                        text: 'An error occurred while fetching the product details.',

                    });
                }
            });
        };
        const applyCoupon = () => {
            const couponCode = $('#coupon').val().trim();
            const totalBeforeCoupon = parseFloat(totalElement.text());
            console.log(totalBeforeCoupon);

            if (!couponCode) {
                Swal.fire({
                    icon: 'error',
                    title: '@lang('cart.wrong')',
                    text: '@lang('cart.put your coupon,please')',
                }).then(() => {
                    $('#coupon').val(''); // Clear input after message
                });
                return;
            }
            if (couponCode === '') {
                Swal.fire({
                    icon: 'error',
                    title: '@lang('cart.wrong')',
                    text: '@lang('cart.put your coupon,please')',
                }).then(() => {
                    $('#coupon').val(''); // Clear input after message
                });
                return;
            }

            $.ajax({
                url: '{{ route('cart.coupon-check') }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    code: couponCode,
                    amount: totalBeforeCoupon
                },
                success: function(data) {
                    if (data.success) {
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                title: '@lang('cart.success')',
                                text: '@lang('cart.coupon added successfully')',
                            });

                            // Disable the button after applying the coupon
                            applyCouponButton.disabled = true;
                            couponInput.disabled = true;
                            removeCouponBtn.classList.remove('d-none');

                        }, 500); // Simulate a short delay for user experience

                        const discountedTotal = data.data + data.coupon_value;
                        const discount = totalBeforeCoupon - discountedTotal;

                        const tax = discountedTotal * TAX_RATE;
                        const finalTotal = discountedTotal - data.coupon_value + SHIPPING_FEES +
                            tax;

                        couponDiv.classList.remove('d-none');
                        $('#discount-value').text(`-${formatCurrency(data.coupon_value)}`);

                        totalElement.text(`${formatCurrency(discountedTotal)}`);
                        taxElement.text(`${formatCurrency(tax)}`);
                        // serviceFeeElement.text(`${formatCurrency(SERVICE_FEES)}`);
                        shippingFeeElement.text(`${formatCurrency(SHIPPING_FEES)}`);
                        totalPayElement.text(`${formatCurrency(finalTotal)}`);
                        finalTotalElement.text(`${formatCurrency(finalTotal)}`);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: "@lang('cart.wrong')",
                            text: data.message,
                        }).then(() => {
                            $('#coupon').val(""); // Clear input after message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: "@lang('cart.wrong')",
                        text: 'An error occurred while applying the coupon.',
                    }).then(() => {
                        $('#coupon').val(""); // Clear input after message
                    });
                }
            });
        };

        const populateSizes = (sizes, item, currencySymbol) => {
            const sizesContainer = $('#div-sizes');
            sizesContainer.empty();

            if (sizes.length > 0) {
                $('#sizes-div').show();
            }
            sizes.forEach(size => {
                sizesContainer.append(`
                    <div class="form-check">
                        <input class="form-check-input size-option" type="radio" name="size_option" id="size-${size.id}" value="${size.price}" ${item.size.id == parseInt(size.id) ? 'checked' : ''}>
                            <label class="form-check-label" for="size-${size.id}">${size.name}</label>
                            <span>${formatCurrency(parseFloat(size.price))}</span>
                        </div>
                `);
            });

        };

        const populateAddons = (addons, item, currencySymbol, min, max) => {
            $('#addons-div').html(`
                                <h4> @lang('cart.addons')
                                </h4>
                                <div class="form-check" style="color:grey">
                                    <p class="form-check" >
                                     @lang('cart.maxNote') ${max} @lang('cart.choicese')
                                    </p>
                            </div>
                                <div class="choices my-3 addon" id="div-addons">

                                </div>`);
            const addonsContainer = $('#div-addons');
            addonsContainer.empty();
            if (addons.length > 0) {
                $('#addons-div').show();
            }
            addons.forEach(addon => {
                const price = parseFloat(addon.price);

                const isSelected = item.addons.find(selectedAddon => selectedAddon.id == addon.id);


                addonsContainer.append(`
                    <div class="form-check">
                        <input class="form-check-input addon-option" type="checkbox" id="addon-${addon.id}" value="${price}" ${isSelected ? 'checked' : ''}>
                            <label class="form-check-label" for="addon-${addon.id}">${addon.name}</label>
                            <span>${formatCurrency(price)}</span>
                        </div>
                `);
            });

            // Rebind the change event for addons


        };
        const saveChanges = (itemIndex, hasAddon, hasSize) => {
            const version = hasSize || hasAddon ? '_v1' : '_v2';
            const modal = $(`#productModal${version}`);
            const originalQuantity = cart[itemIndex].quantity;
            let updatedSizePrice = 0;
            let updatedSizeLabel = '';
            let updatedSizeId = 0;
            let updatedAddons = [];
            let sizeUpdate = [];

            // Updated size
            if (version === '_v1') {
                if (cart[itemIndex].size.price) {
                    const selectedSize = $(`#div-sizes .size-option:checked`);
                    updatedSizePrice = parseFloat(selectedSize.val()) || cart[itemIndex].size.price || 0;
                    updatedSizeLabel = selectedSize.siblings('label').text() || cart[itemIndex].size
                        .label || '';
                    updatedSizeId = selectedSize.attr('id').replace('size-', '') || cart[itemIndex].size
                        .id || 0;
                    sizeUpdate = {
                        id: updatedSizeId,
                        price: updatedSizePrice,
                        label: updatedSizeLabel
                    };
                }
            } else {
                sizeUpdate = {
                    label: ""
                };
            }

            // Updated addons
            if (version === '_v1') {
                $(`#div-addons .addon-option:checked`).each(function() {
                    updatedAddons.push({
                        id: $(this).attr('id').replace('addon-', ''), // Extract addon ID
                        price: parseFloat($(this).val()),
                        name: $(this).next('label').text()
                    });
                });


            }

            const updatedNotes = $(`#note${version}`).val();
            const updatedQuantity = parseInt(modal.find('.num').text()) || cart[itemIndex].quantity;
            if (cart[itemIndex]['dish_id']) {
                cart[itemIndex] = {
                    dish_id: cart[itemIndex]['dish_id'],
                    name: cart[itemIndex]['name'],
                    image: cart[itemIndex]['image'],
                    price: cart[itemIndex]['price'],
                    size: sizeUpdate,
                    addons: updatedAddons,

                    // addons: updatedAddons.length > 0 ? updatedAddons : cart[itemIndex][
                    //     'addons'
                    // ], // Preserve existing addons
                    quantity: updatedQuantity,
                    notes: updatedNotes,
                    totalPrice: (updatedQuantity * updatedSizePrice) + updatedAddons.reduce((sum,
                        addon) => sum + addon.price, 0)
                };

            }


            localStorage.setItem('cart', JSON.stringify(local_storage)); /////

            renderCart(); /////

            $(`#addons-div #div-addons .addon-option:checked`).prop('checked', false);
            $(`#div-sizes .size-option:checked`).prop('checked', false);
            $(`#note${version}`).val('');

            modal.modal('hide');
        };
        const validateCoupon = async (couponCode,
            totalAmount) => { //nourhan from totalElementCoupon to totalAmount
            updateTotal(); //nourhan
            console.log(totalAmount); //nourhan

            console.log("Validating coupon:", couponCode);

            try {
                const response = await $.ajax({
                    url: '{{ route('cart.coupon-validate') }}', // Ensure this route is correct
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        code: couponCode,
                        amount: totalAmount //nourhan from totalElementCoupon to totalAmount
                    }
                });
                if (response.valid == false && couponInput.value.trim() !== '') {
                    removeCoupon(); // Remove the coupon if it's invalid
                }

                return response.valid;
            } catch (error) {
                console.error('Error validating coupon:', error);
                return false; // Treat any error as an invalid coupon
            }
        };

        const checkAndRemoveInvalidCoupon = async () => {

            if (local_storage.coupon) {
                updateTotal(); //nourhan
                const isValid = await validateCoupon(local_storage.coupon,
                    totalAmount); //nourhan التحقق من الكوبون from totalElementCoupon to totalAmount
                console.log(isValid);

                if (!isValid) {
                    local_storage.coupon = '';
                    local_storage.coupon_value = 0;

                    localStorage.setItem('cart', JSON.stringify(local_storage));

                    const couponInput = document.getElementById('coupon');
                    if (couponInput) {
                        couponInput.value = '';
                        couponInput.disabled = false;
                    }

                    const applyCouponButton = document.getElementById(
                        'applyCouponButton');
                    if (applyCouponButton) {
                        applyCouponButton.disabled = false;
                    }

                    const removeCouponBtn = document.getElementById(
                        'removeCouponBtn');
                    if (removeCouponBtn) {
                        removeCouponBtn.classList.add('d-none');
                    }

                    // إخفاء قسم الكوبون
                    const couponDiv = document.getElementById('couponDiv');
                    if (couponDiv) {
                        couponDiv.classList.add('d-none');
                    }
                }
            }
        };

        renderCart();
        removeCouponBtn.addEventListener('click', removeCoupon);
        applyCouponButton.addEventListener(
            'click', applyCoupon);

    });

    function removeCartItem(cart, dishId) {
        $.each(cart, function(i) {
            if (cart[i].dish_id === dishId) {
                cart.splice(i, 1);
                return false;
            }
        });
    }
    // Bind the update function to the checkout button
</script>
