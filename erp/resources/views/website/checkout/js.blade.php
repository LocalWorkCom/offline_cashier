<script>
    let cartData = JSON.parse(localStorage.getItem('cart')) || {};
    let cash_limit = "<?php echo $cash_limit; ?>";
    let local_storage_2 = JSON.parse(localStorage.getItem('TableReservation')) || {};
    let local_storage_3 = JSON.parse(localStorage.getItem('takeaway_details')) || {};
    let full_payment_preparation_setting = @json(getBranchSettings($branchId, 'full_payment_preparation_setting'));
    let deposit_preparation_setting = @json(getBranchSettings($branchId, 'deposit_preparation_setting'));
    let no_payment_preparation_setting = @json(getBranchSettings($branchId, 'no_payment_preparation_setting'));
    let order_reservation_deposit = @json(getBranchSettings($branchId, 'order_reservation_deposit'));
    const formatCurrency = (amount) => `${amount.toFixed(2)} ${currency_symbol}`;

    $(document).ready(function() {
        //check if item is un active
        setCookie('check_cart_items', 0, 7);
        let check_items = 0;
        let check_branch = 0;
        const cart = cartData.items;
        const idBranch = `{{ $branchId }}`;
        let new_local_storage = JSON.parse(localStorage.getItem('cart')) || {
            items: []
        };
        let branchRequest = $.get("{{ url('cart/checkDishBranch') }}/" + idBranch)
            .done(function(branchStatus) {
                if (branchStatus == 0) {
                    check_items++;
                    check_branch++;
                }
            })
            .fail(function() {
                console.error(`Error checking dish status for dish`);
            });

        let requests = cart.flatMap(item => {
            let dishRequest = $.get("{{ url('cart/checkDish') }}/" + item.dish_id + "/" + idBranch)
                .done(function(dishStatus) {
                    if (dishStatus == 0) {
                        let itemIndex = cart.findIndex(cartItem => cartItem.dish_id === item
                            .dish_id);
                        if (itemIndex !== -1) {
                            removeCartItem(cart, cart[itemIndex]['dish_id']);
                            check_items++;
                        }
                    }
                })
                .fail(function() {
                    console.error(`Error checking dish status for dish ID: ${item.dish_id}`);
                });

            let sizeRequest = item.size && item.size.id ?
                $.get("{{ url('cart/checkDishSize') }}/" + item.dish_id + "/" + item.size.id + "/" +
                    idBranch)
                .done(function(dishSizeStatus) {
                    if (dishSizeStatus == 0) {
                        let itemIndex = cart.findIndex(cartItem => cartItem.dish_id === item
                            .dish_id);
                        if (itemIndex !== -1) {
                            removeCartItem(cart, cart[itemIndex]['dish_id']);
                            check_items++;
                        }
                    }
                })
                .fail(function() {
                    console.error(`Error checking dish size status for dish ID: ${item.dish_id}`);
                }) :
                null;

            let addonRequests = item.addons.length > 0 ?
                item.addons.map(addon => {
                    return $.get("{{ url('cart/checkDishAddon') }}/" + item.dish_id + "/" + addon
                            .id + "/" + idBranch)
                        .done(function(addonStatus) {
                            if (addonStatus == 0) {
                                let itemIndex = cart.findIndex(cartItem => cartItem.dish_id ===
                                    item.dish_id);
                                if (itemIndex !== -1) {
                                    removeCartItem(cart, cart[itemIndex]['dish_id']);
                                    check_items++;
                                }
                            }
                        })
                        .fail(function() {
                            console.error(
                                `Error checking addon status for dish ID: ${item.dish_id}, addon ID: ${addon.id}`
                            );
                        });
                }) : [];

            return [dishRequest, sizeRequest, ...addonRequests].filter(Boolean);
        });

        Promise.all([branchRequest, ...requests]).then(() => {
            new_local_storage.items = cart;
            if (check_branch > 0) {
                localStorage.removeItem('cart');
            } else {
                localStorage.setItem('cart', JSON.stringify(new_local_storage));
            }
            updateCartCounts();
            if (check_items > 0) {
                setCookie('check_cart_items', check_items, 7);
                setCookie('check_branch_items', check_branch, 7);
                window.location.href = "{{ route('cart') }}";
            }
        });

    });


    function fetchGoogleMapAddress(lat, lng) {
        const geocoder = new google.maps.Geocoder();
        const latLng = new google.maps.LatLng(lat, lng);

        geocoder.geocode({
            location: latLng
        }, (results, status) => {
            if (status === "OK" && results[0]) {
                // Use the first result from the geocoding results
                const result = results[0];
                const addressComponents = result.address_components;

                // Extract address details
                const road = addressComponents.find(component =>
                    component.types.includes('route')
                )?.long_name || 'N/A';

                const city = addressComponents.find(component =>
                    component.types.includes('locality') ||
                    component.types.includes('administrative_area_level_1')
                )?.long_name || 'N/A';

                // Display the address in the container
                deliveryAddressContainer.innerHTML = `
                <p class="fw-bold">
                    <i class="fas fa-map-marker-alt main-color ms-2"></i>
                    ${road}, ${city}
                </p>
            `;
            } else {
                console.error('Geocoding failed:', status);
            }
        });
    }



    // Optional: trigger on page load



    document.addEventListener('DOMContentLoaded', function() {
        const orderType = cartData.order_type; // 'delivery' or 'takeaway'
        document.querySelectorAll('.payment-options').forEach(div => div.classList.add('d-none'));

        if (orderType === 'Delivery') {
            document.getElementById('payment-options-delivery').classList.remove('d-none');
        } else if (orderType == 'Takeaway') {
            document.getElementById('payment-options-takeaway').classList.remove('d-none');
        } else {
            document.getElementById('payment-options-with').classList.remove('d-none');


        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // var payment_method_value;
        // var order_type_value;
        $('input[name=payment_method], input[name=payment_method2]').on('click', function() {
            if ($(this).is(':checked')) {
                payment_method_value = $(this).val();
                console.log(payment_method_value);
                if (cartData.order_type == 'reservation') {

                    if (full_payment_preparation_setting == 1 && payment_method_value ==
                        'full_payment_required') {
                        document.getElementById('meal-appiontments').classList.remove('d-none');

                    } else if (deposit_preparation_setting == 1 && payment_method_value ==
                        'deposit_required') {
                        document.getElementById('meal-appiontments').classList.remove('d-none');

                    } else if (no_payment_preparation_setting == 1 && payment_method_value ==
                        'no_payment_required') {
                        document.getElementById('meal-appiontments').classList.remove('d-none');

                    } else {
                        console.log("mkk,hjkh");

                        document.getElementById('meal-appiontments').classList.add('d-none');

                    }
                }

                // $('input[name="payment_method"][value="${payment_method_value}"]').prop('checked', true).trigger('click');
            }
        });

        $('#place_order').on('click', function(e) {
            const isPaymentSelected = $('input[name="payment_method2"]:checked').length > 0;
            const isPaymentSelected2 = $('input[name="payment_method"]:checked').length > 0;

            if (!isPaymentSelected && !isPaymentSelected2) {
                alert('يرجى اختيار طريقة الدفع لإتمام الطلب');
                e.preventDefault();
                return; // Make sure it doesn't continue
            }
            // order_type_value = cartData.order_type;
            // alert(payment_method_value);
            //$('#makeOrderForm').submit();
        });
    });
    // Fetch cart data from localStorage
    if (cartData) {
        if (cartData.order_type == 'reservation') {

            $('#place_order').prop('disabled', true);
        }

        const cart = cartData.items;
        const istakeaway = getCookie('branch_takaway');
        // $('#cartTypeInput').val(istakeaway);

        var address_title = 'موقع التوصيل';
        var open_reservation = false;
        var open_takeaway = false;
        var open_address = true;
        if (cartData.order_type == 'reservation') {
            address_title = 'حجز طاولة';
            open_reservation = true;
            open_takeaway = false;
            open_address = false;

        } else if (cartData.order_type == 'Takeaway') {
            address_title = 'تفاصيل موقع الاستلام';
            open_takeaway = true;
            open_reservation = false;
            open_address = false;

        }

        if (istakeaway === 'true' && Object.keys(local_storage_3).length > 0) {
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
                        // $('#branchReceiveName').text(data.name_site);
                        $('#checkout-address-v1').addClass('d-none');
                        $('#checkout-address-v3').removeClass('d-none');
                    }
                },
                error: function() {
                    $('#cart-address-v1').addClass('d-none');
                    $('#cart-address-v2').removeClass('d-none');
                }
            });
        } else {
            $('#checkout-address-v1').removeClass('d-none');
            $('#checkout-address-v3').addClass('d-none');
        }


        document.getElementById('cartDataInput').value = JSON.stringify(cartData);
        document.getElementById('takeawayDataInput').value = JSON.stringify(local_storage_3);
        document.getElementById('reservation_data').value = JSON.stringify(local_storage_2);


        let deliveryAddressContainer = $('#deliveryAddress');
        const isAuthenticated = @json(auth('client')->user());
        const translations = @json(__('cart'));

        if (isAuthenticated) {
            const addressId = localStorage.getItem('authaddress');
            const istakeaway = getCookie('branch_takaway');

            if (istakeaway === 'true' && Object.keys(local_storage_3).length > 0) {
                $.ajax({
                    url: "{{ route('get-bracnh-info') }}",
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    },
                    data: {
                        branch_id: getCookie('branch_id')
                    },
                    success: function(data) {
                        if (data) {
                            $('#branchReceiveName').html(`
                                        <p class="fw-bold">
                                            <i class="fas fa-user main-color ms-2"></i>
                                            ${'{{ auth('client')->user()->name }}'}
                                        </p>
                                        <p class="text-muted">
                                            <span>@lang('checkout.phone_number'):</span> ${data.phone}
                                        </p>

                                            <div class="d-flex justify-content-between">
                                            <h5 class="fw-bold mb-0">
                                                <i class="fas fa-map-marker-alt main-color ms-2"></i>
                                                ${data.name_site}
                                            </h5>

                                            </div>
                                            <p class="text-muted">
                                            ${data.address}
                                            </p>

                                    `);
                        }
                    }
                });
            } else {
                if (addressId) {

                    // Fetch address details via AJAX
                   $.ajax({
                        url: "{{ route('get-address-detail') }}",
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: addressId
                        },
                        success: function(data) {
                            console.log(data);

                            if (data.data) {
                                $('#edit-address').attr('href',
                                    `{{ route('edit.Address', ':id') }}`
                                    .replace(
                                        ':id', data.data.id));

                                let floorInfo = '';
                                let apartmentInfo = '';

                                // Only include floor and apartment for non-villa types
                                if (data.data.address_type && data.data.address_type
                                    .toLowerCase() ===
                                    'villa') {
                                    apartment_numberInfo =
                                        `@lang('checkout.villaname') ${data.data.building}, `;
                                    buldingInfo =
                                        `@lang('checkout.villa') ${data.data.apartment_number} `;

                                } else if (data.data.address_type && data.data.address_type
                                    .toLowerCase() ===
                                    'office') {
                                    floorInfo =
                                        ` @lang('checkout.floor') ${data.data.floor_number} ,`;
                                    apartment_numberInfo =
                                        `@lang('checkout.number') ${data.data.apartment_number} ,`;
                                    buldingInfo = ` @lang('checkout.office') ${data.data.building}`;
                                } else {
                                    floorInfo = `@lang('checkout.floor') ${data.data.floor_number},`;
                                    apartment_numberInfo =
                                        `@lang('checkout.apartment') ${data.data.apartment_number},`;
                                    buldingInfo = `@lang('checkout.street') ${data.data.building}`;
                                }
                                deliveryAddressContainer.html(`
                                        <input type='hidden' name='client_address_id' value='${data.data.id}'>
                                        <p class="fw-bold">
                                            <i class="fas fa-user main-color ms-2"></i>
                                            ${data.data.name || '{{ auth('client')->user()->name }}'}
                                        </p>
                                        <p class="text-muted">
                                            <span>@lang('checkout.phone_number'):</span> ${data.data.address_phone}
                                        </p>
                                        <p class="fw-bold">
                                            <i class="fas fa-map-marker-alt main-color ms-2"></i>
                                            ${data.data.address}
                                        </p>
                                        <p class="text-muted">
                                            ${apartment_numberInfo}
                                            ${floorInfo}
                                                        ${buldingInfo}

                                        </p>
                                        <p class="text-muted">
                                            @lang('checkout.landmark'): ${data.data.notes || '@lang('checkout.no_notes')'}
                                        </p>
                                    `);
                                $('#edit-address').attr('href',
                                    `{{ route('edit.Address', ':id') }}`
                                    .replace(':id', data.data.id));
                            }

                        }

                    });



                } else {
                    deliveryAddressContainer.innerHTML = `
                <p class="text-danger">@lang('checkout.no_address_found')</p>
            `;
                }

                if (cartData.order_type == 'reservation') {

                    $('#reservation-policy').removeClass('d-none');

                    $.ajax({
                        url: "{{ route('get-bracnh-info') }}",
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                .attr(
                                    'content')
                        },
                        data: {
                            branch_id: getCookie('branch_id')
                        },
                        success: function(data) {
                            if (data) {
                                deliveryAddressContainer.html(`
                                        <p class="fw-bold">
                                            <i class="fas fa-user main-color ms-2"></i>
                                            ${'{{ auth('client')->user()->name }}'}
                                        </p>
                                        <p class="text-muted">
                                            <span>@lang('checkout.phone_number'):</span> ${data.phone}
                                        </p>

                                            <div class="d-flex justify-content-between">
                                            <h5 class="fw-bold mb-0">
                                                <i class="fas fa-map-marker-alt main-color ms-2"></i>
                                                ${data.name_site}
                                            </h5>

                                            </div>
                                            <p class="text-muted">
                                            ${data.address}
                                            </p>
                                            <div class="table-details w-50">
                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-table.svg') }}" alt="" />
                                            <h5 class="fw-bold d-inline"> ${local_storage_2.tableName}
                                                <span class="text-warning">(طلب
                                                وجبة)</span>
                                            </h5>
                                            <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/clock.svg') }}" alt="" />
                                                <span class="text-muted mb-0">
                                                <span>
                                                    الوصول:
                                                </span>
                                                ${local_storage_2.tableSession}
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/calender.svg') }}" alt="" />
                                                <span class="text-muted mb-0">
                                                  ${local_storage_2.date}
                                                </span>
                                                </div>
                                                <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}" alt="" />
                                                   <span class="text-muted mb-0"id="persons">
                                                            ${local_storage_2.personal.adult + local_storage_2.personal.men + local_storage_2.personal.women} شخص
                                                </span>
                                                <span class="text-muted mb-0" id="kids">
                                                    ${local_storage_2.personal.kids} طفل
                                                </span>

                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}" alt="" />
                                                <span class="text-muted mb-0">
                                                ${local_storage_2.partitionName}
                                                </span>
                                                </div>
                                                <div>
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-clock.svg') }}" alt="" />
                                                <span class="text-muted mb-0 main-color">
                                                    <span>
                                                    المغادرة:
                                                    </span> ${local_storage_2.leaveTime && local_storage_2.leaveTime.trim() !== '' ? local_storage_2.leaveTime : '------------'}

                                                </span>
                                                </div>
                                            </div>
                                    `);

                                $('#branch_receive_name').text(data
                                    .name_site);
                                $('#cart-address-v3').removeClass(
                                    'd-none');
                                $('#cart-address-v2').addClass(
                                    'd-none');

                            }
                        }
                    });

                } else {
                    // $.ajax({
                    //     url: "{{ route('get-address-detail') }}",
                    //     method: 'GET',
                    //     headers: {
                    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    //     },
                    //     data: {
                    //         id: addressId
                    //     },
                    //     success: function(data) {
                    //         if (data.data) {
                    //             deliveryAddressContainer.html(`
                    //                     <input type='hidden' name='client_address_id' value='${data.data.id}'>
                    //                     <p class="fw-bold">
                    //                         <i class="fas fa-user main-color ms-2"></i>
                    //                         ${data.data.name || '{{ auth('client')->user()->name }}'}
                    //                     </p>
                    //                     <p class="text-muted">
                    //                         <span>undefined:</span> ${data.data.address_phone}
                    //                     </p>
                    //                     <p class="fw-bold">
                    //                         <i class="fas fa-map-marker-alt main-color ms-2"></i>
                    //                         ${data.data.address}
                    //                     </p>
                    //                     <p class="text-muted">
                    //                         ${apartment_numberInfo}
                    //                         ${floorInfo}
                    //                                     ${buldingInfo}

                    //                     </p>
                    //                     <p class="text-muted">
                    //                         undefined: ${data.data.notes || 'undefined'}
                    //                     </p>
                    //                 `);
                    //             $('#edit-address').attr('href',
                    //                 `{{ route('edit.Address', ':id') }}`
                    //                 .replace(':id', data.data.id));
                    //         }
                    //     }

                    // });

                }


            }
        }

        // Populate order summary section
        const orderSummaryContainer = document.getElementById('orderSummary');
        let totalPrice = 0;
        let discount = 0;
        let coupon_code = '';

        cart.forEach(item => {
            const itemSizePrice = item.size && item.size.price ? item.size.price :
                item.price; // Check if size exists and has a price
            const itemAddons = item.addons || [];
            const addonTotal = itemAddons.reduce((sum, addon) => sum + parseFloat(addon.price ||
                0), 0);
            const itemTotal = (item.quantity * itemSizePrice) + addonTotal * item.quantity;
            totalPrice += itemTotal;

        });


        discount = cartData.coupon_value; // Coupon value
        coupon_code = cartData.coupon; // Coupon value
        const service_fees_type = @json(getBranchSettings($branchId, 'service_fees_type'));
        const service_fees = @json(getBranchSettings($branchId, 'service_fees'));
        const currency_symbol = cartData.symbol;
        @php
            $sessionAddressId = session('new_address_id'); // This should come from JS via AJAX or a redirect
        @endphp

        const SHIPPING_FEES = istakeaway === "false" && cartData.order_type != 'reservation' &&
            local_storage_2
            .reservType != 'with' ?
            @json(getDeliveryFees($sessionAddressId, $branchId)) :
            0;
        var tax_application_check = @json(getBranchSettings($branchId, 'tax_application'));
        const SERVICE_FEES = 0;
        const TAX_RATE = tax_application_check == 0 ?
            @json(getBranchSettings($branchId, 'tax_percentage')) / 100 : 0; // 14% VAT
        const coupon_application = @json(getBranchSettings($branchId, 'coupon_application')) /
            100; // 14% VAT
        let tax = totalPrice * TAX_RATE;
        let totalPrice_v1 = totalPrice;
        if (cartData.order_type == 'reservation' && local_storage_2.reservType == 'with') {
            service_fees_total = totalPrice * (service_fees / 100);
            totalPrice = service_fees_type === 'fixed' ?
                (parseFloat(totalPrice) + parseFloat(service_fees)) :
                (parseFloat(totalPrice) + parseFloat(service_fees_total));
            tax = totalPrice * TAX_RATE; // ✅ now allowed
        }

        const finalTotal = parseFloat(totalPrice) + parseFloat(SHIPPING_FEES) + parseFloat(tax) - parseFloat(discount);
        const branchIdN = @json($branchId);
        const taxApplication = @json(getBranchSettings($branchId, 'tax_application'));
        const taxPercentage = @json(getBranchSettings($branchId, 'tax_percentage'));
        let serviceFeesHtml = '';
        if (cartData.order_type == 'reservation' && local_storage_2.reservType == 'with') {
            serviceFeesHtml = `
       <li class="order-list" id="total-div">
            <p>@lang('cart.mintotal')</p>
            <p class="fw-bold" id="total-value">${formatCurrency(totalPrice_v1)}</p>
        </li>

   <li class="order-list" id="service-div">
        <p>@lang('cart.service_fees')</p>
        <p class="fw-bold" id="service_fees">
            ${formatCurrency(service_fees_type === 'fixed' ? parseFloat(service_fees) : parseFloat(service_fees_total))}
        </p>
    </li>
    `;
        }
         orderSummaryContainer.innerHTML = `
    <ul class="list-unstyled p-0">
        ${serviceFeesHtml}
        <li class="order-list">
            <p>@lang('cart.subtotal')</p>
            <p class="fw-bold" id="total-before-coupon">${formatCurrency(totalPrice)}</p>
        </li>

        ${coupon_code ? `
        <li class="order-list">
            <p class="main-color">@lang('checkout.coupon') <span id="code">${coupon_code}</span></p>
            <p class="fw-bold main-color">${formatCurrency(discount)}</p>
        </li>` : ''}
        ${cartData.order_type != 'reservation' && local_storage_2.reservType != 'with' ?
        `
        <li class="order-list">
            <p>@lang('checkout.delivery_fee')</p>
            <p class="fw-bold">${formatCurrency(parseFloat(SHIPPING_FEES))}</p>
        </li>`:''}
        ${taxApplication == 0 ? `
       <div class="alert alert-warning d-flex align-items-center gap-2 p-3 rounded-3 shadow-sm">
    <i class="bi bi-exclamation-triangle-fill fs-5 text-dark"></i>
    <div>
        <strong>@lang('checkout.alert'):</strong>  @lang('cart.tax_included') <strong>${(TAX_RATE*100).toFixed(2)} % </strong> @lang('cart.means')
        <span id="tax" class="text-dark fw-bold">${formatCurrency(tax)}</span>  @lang('cart.on_invoice')
    </div>
</div>

        ` : ''}
    </ul>
`;

        if (tax_application_check == 0) {
            $('#tax').text(formatCurrency(tax));
        } else {
            $('#tax').hide();
        }
        // Update total amount
        if (finalTotal > cash_limit) {
            // $('#no_payment_required_div').hide();
            $('#paying-no_payment_required').prop('disabled', true);

            // Show a message indicating the issue
            document.getElementById('no_payment_required_div').style.backgroundColor = '#dbdbdb';
            $('#payment_error_message').text(`@lang('cart.cash_limit') ( ${formatCurrency(parseFloat(cash_limit))}).`).show();


        }
        document.getElementById('totalAmount').innerText = `${formatCurrency(finalTotal)}`;

        $(document).ready(function() {

            function validateOrderForm() {
                const hasPayment = $('input[name="payment_method2"]:checked').length > 0;
                const hasPayment2 = $('input[name="payment_method"]:checked').length > 0;
                let done = true;

                if (cartData.order_type == 'reservation') {
                    done = $('#agree-policy').is(':checked');
                }
                const shouldEnable = done && (hasPayment || hasPayment2);
                $('#place_order').prop('disabled', !shouldEnable);
            }


            // $('#agree-policy').on('change', validateOrderForm);
            $('input[name="payment_method2"]').on('change', function() {
                validateOrderForm(); // Call your existing function
                // Add your additional logic here
                if ($(this).val() == 'deposit_required') {

                    $('#totalText').text('دفع عربون');

                    $('#totalAmount').text(formatCurrency(finalTotal * (order_reservation_deposit /
                        100)));
                } else {
                    $('#totalText').text('المجموع الكلي');
                    $('#totalAmount').text(formatCurrency(finalTotal));

                }

            });
            $('input[name="payment_method"]').on('change', validateOrderForm);
            $('#agree-policy').on('change', validateOrderForm);

            // Initial check
            validateOrderForm();
        });

    }
    // document.querySelector('form').addEventListener('submit', function(event) {
    //     console.log('Local storage cleared.');
    //     localStorage.removeItem('takeaway_details');
    //     localStorage.removeItem('TableReservation');
    //     localStorage.removeItem('cart');

    //     setCookie('backButton', 'false', 7);
    // });

    function removeCartItem(cart, dishId) {
        $.each(cart, function(i) {
            if (cart[i].dish_id === dishId) {
                cart.splice(i, 1);
                return false;
            }
        });
    }

    function updateCartCounts() {
        let cart = JSON.parse(localStorage.getItem('cart')) || {
            items: []
        };
        let items = cart.items || []; // Safely access the items array
        let count = items.length; // Count the total number of items
        document.getElementById('cart-count').textContent = count;
    }
</script>
