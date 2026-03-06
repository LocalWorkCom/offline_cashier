<script>
    document.getElementById('productModal_v1').addEventListener('show.bs.modal', function() {
        this.setAttribute('aria-hidden', 'false');
    });

    document.getElementById('productModal_v1').addEventListener('hide.bs.modal', function() {
        this.setAttribute('aria-hidden', 'true');
    });
    $(document).ready(function() {
        $('#productModal_v1, #productModal_v2, #productDiscountModal').on('hidden.bs.modal', function() {
            // Reset all inputs and textareas inside the modal
            $(this).find('input[type="hidden"], textarea').val('');

            // Reset image src
            $(this).find('img').attr('src', '');

            // Clear any dynamic content
            $(this).find('#div-detail, #div-detail_v1, #div-detail_v2').empty();
            $(this).find('#dish-total, #dish-total_v1, #dish-total_v2').empty();
            $(this).find('#dish-quantity, #dish-quantity_v2').html('+ @lang('cart.add to cart')');

            // Clear sizes and addons
            $(this).find('#div-sizes, #addons-div').empty();
            $(this).find('#sizes-div, #addons-div').hide();
        });
    });


    // Function to update the cart count
    function fill_cart(id, type) {

        $.ajax({
            url: "{{ route('cart.dish-detail') }}",
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token for security
            },
            data: {
                'id': id,
                'type': type
            },
            success: function(data) {
                let sizesHtml = '',
                    addonsHtml = '',
                    dishHtml = '';

                if (type == 'discount') {
                    let modal = $(`#productDiscountModal`);
                    let discountPrice = parseFloat(data.details.price);
                    let originalPrice = parseFloat(data.original);
                    dishHtml += `
                                <h5>${data.details.name} </h5>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-star"></i>
                                        @lang('cart.most rated')
                                    </span>
                                    <small class="text-muted d-block py-2">
                                    ${data.details.description}
                                    </small>
                                    <div>
                                        <span class="discount fw-bold h5">${originalPrice.toFixed(2)} ${data.branch.currency_symbol} </span>
                                        <span class="h5 fw-bold"> ${discountPrice.toFixed(2)} ${data.branch.currency_symbol} </span>
                                    </div>
                                    <div class="qty mt-3 d-flex justify-content-center align-items-center">
                                        <span class="pro-dec me-3" onclick="decreaseQuantity(this)">
                                            <i class="fa fa-minus" aria-hidden="true"></i>
                                        </span>
                                        <span class="num fs-4" id="quantity">1</span>
                                        <span class="pro-inc ms-3" onclick="increaseQuantity(this)">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                `;

                    modal.find(`#dish-img`).attr('src', data.details.image);

                    modal.find(`#div-detail`).html(dishHtml);

                    modal.find(`#dish-total`).html(
                        `${discountPrice.toFixed(2)} ${data.branch.currency_symbol}`
                    );
                    modal.find(`#id`).val(data.details.id);
                    // Function to decrease quantity
                    function decreaseQuantity(button) {
                        const quantityElement = button.closest('.qty').querySelector('.num');
                        let quantity = parseInt(quantityElement.textContent);
                        if (quantity > 1) {
                            quantity--;
                            quantityElement.textContent = quantity;
                            updateTotalPrice(quantity, button.closest('.dishHtml'));
                        }
                    }

                    function increaseQuantity(button) {
                        const quantityElement = button.closest('.qty').querySelector('.num');
                        let quantity = parseInt(quantityElement.textContent);
                        quantity++;
                        quantityElement.textContent = quantity;
                        updateTotalPrice(quantity, button.closest('.dishHtml'));
                    }

                    // Function to update the total price
                    function updateTotalPrice(quantity, dishElement) {
                        const unitPrice = parseFloat(dishElement.querySelector('.total-price').getAttribute(
                            'data-unit-price'));
                        const totalPriceElement = dishElement.querySelector('#total-price' + dishElement
                            .dataset.version);
                        const newTotal = unitPrice * quantity;
                        totalPriceElement.textContent = newTotal.toFixed(2) + ' ' + dishElement
                            .querySelector('.branch-currency-symbol').textContent;
                    }

                    modal.modal('show');


                } else if (type == 'dish') {
                    var version = data.dish.has_size || data.dish.has_addon ? '_v1' : '_v2';
                    let modal = $(`#productModal${version}`);

                    modal.find('.size-option').prop('checked', false); // Uncheck all sizes
                    modal.find('.addon-option').prop('checked', false); // Uncheck all addons
                    modal.find('#note' + version).val(''); // Clear notes input
                    modal.find(`#submit${version}`).prop('disabled', false);

                    if (data.dish.has_size) {

                        $('#sizes-div').show();
                        for (const size of data.sizes) {
                            sizesHtml += `
                            <div class="form-check">
                                <div>
                                    <input class="form-check-input size-option" type="radio" name="size_option" id="size-${size.id}" value="${size.price}" data-id="${size.id}"
                                    ${size.default_size ? 'checked' : ''}>
                                    <label class="form-check-label" for="size-${size.id}">
                                        ${size.name}
                                    </label>
                                </div>
                                <span>${size.price} ${data.branch.currency_symbol}</span>
                            </div>
                            `;
                        }

                    } else {
                        $('#sizes-div').hide();
                    }

                    if (data.dish.has_addon) {
                        $('#addons-div').show();
                        $('#addons-div').html(`
                                <h4> @lang('cart.addons')
                                </h4>
                                <div class="form-check" style="color:grey">
                                    <p class="form-check" >
                                     @lang('cart.maxNote') ${data.max} @lang('cart.choicese')
                                    </p>
                                </div>
                                <div class="choices my-3 addon" id="div-addons">
                                </div>`);
                        for (const addon of data.addons) {
                            addonsHtml += `
                            <div class="form-check">
                                <div>
                                    <input class="form-check-input addon-option" type="checkbox" name="addons" id="addon-${addon.id}" value="${addon.price}">
                                    <label class="form-check-label" for="addon-${addon.id}">
                                        ${addon.name}
                                    </label>
                                </div>
                                <span>${addon.price} ${data.branch.currency_symbol}</span>
                            </div>
                            `;
                        }
                    } else {
                        $('#addons-div').hide();
                    }


                    let dishPrice = parseFloat(data.dish.price);

                    dishHtml += `
                            <h5>${data.dish.name}</h5>
                            ${data.dish.mostOrdered ? `
                            <span class="badge bg-warning text-dark">
                            <i class="fas fa-star"></i>
                            </span>` : ''}
                            <small class="text-muted d-block py-2">${data.dish.description}</small>
                            <h4 class="fw-bold">
                                <span class="total-price" data-unit-price="${dishPrice}" id="total-price${version}">
                                    ${dishPrice.toFixed(2)}
                                    ${data.branch.currency_symbol}
                                </span>
                            </h4>
                            <div class="qty mt-3 d-flex justify-content-center align-items-center">
                                <span class="pro-dec me-3" onclick="decreaseQuantity(this)">
                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                </span>
                                <span class="num fs-4">1</span>
                                <span class="pro-inc ms-3" onclick="increaseQuantity(this)">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                </span>
                            </div>`;

                    modal.find(`#dish-img${version}`).attr('src', data.dish.image);
                    modal.find(`#div-sizes`).html(sizesHtml);
                    modal.find(`#div-addons`).append(addonsHtml);
                    modal.find(`#div-detail${version}`).html(dishHtml);
                    modal.find(`#dish-total${version}`).html(
                        `${dishPrice.toFixed(2)} ${data.branch.currency_symbol}`);
                    modal.find(`#dish_id${version}`).val(data.dish.id);
                    modal.find(`#currency_symbol${version}`).val(data.branch.currency_symbol);

                    let selectedEditAddons = modal.find('.addon-option:checked').length;
                    if (data.dish.has_addon != 0) {
                        if (selectedEditAddons < data.min || selectedEditAddons > data.max) {
                            modal.find('#addon-error').remove();
                            modal.find(`#div-addons`).append(`
                                            <div id="addon-error" class="text-danger">
                                                @lang('cart.maxNote') ${data.max} @lang('cart.choicese')
                                            </div>`);
                            if (selectedEditAddons >= data.max) {
                                modal.find('.addon-option:not(:checked)').prop('disabled', true);
                            }
                            modal.find(`#submit${version}`).prop('disabled', true);
                        } else {
                            modal.find('#addon-error').remove();
                            modal.find('.addon-option').prop('disabled', false);
                            modal.find(`#submit${version}`).prop('disabled', false);
                        }
                    }
                    // Function to recalculate total price
                    function recalculateTotalPrice() {
                        let selectedSizePrice = parseFloat(modal.find('.size-option:checked')
                            .val()) || 0;
                        let price = 0;
                        let addonsPrice = 0;
                        let selectedAddons = modal.find('.addon-option:checked').length;
                        if (selectedSizePrice) {
                            price = selectedSizePrice;
                        } else {
                            price = dishPrice;
                        }
                        // Calculate addons price
                        modal.find('.addon-option:checked').each(function() {
                            addonsPrice += parseFloat($(this).val());
                        });
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
                            modal.find(`#submit${version}`).prop('disabled', true);
                        } else {
                            modal.find('#addon-error').remove();
                            modal.find('.addon-option').prop('disabled', false);
                            modal.find(`#submit${version}`).prop('disabled', false);
                        }

                        let quantity = parseInt(modal.find('.num').text()) || 1;
                        let newTotalPrice = (price * quantity) + addonsPrice * quantity;

                        $(`#total-price${version}`).text(
                            `${newTotalPrice.toFixed(2)} ${data.branch.currency_symbol}`);

                        $(`#dish-total${version}`).text(
                            `${newTotalPrice.toFixed(2)} ${data.branch.currency_symbol}`);

                    }

                    modal.find('.size-option').on('change', recalculateTotalPrice);
                    modal.find('.addon-option').on('change', recalculateTotalPrice);
                    window.increaseQuantity = function(ele) {
                        let quantityElem = $(ele).siblings('.num');
                        let quantity = parseInt(quantityElem.text()) || 1;
                        quantity++;
                        quantityElem.text(quantity);
                        recalculateTotalPrice();
                    };
                    window.decreaseQuantity = function(ele) {
                        let quantityElem = $(ele).siblings('.num');
                        let quantity = parseInt(quantityElem.text()) || 1;
                        if (quantity > 1) {
                            quantity--;
                            quantityElem.text(quantity);
                            recalculateTotalPrice();
                        }
                    };
                    // Show the modal
                    modal.modal('show');
                }

            },
            error: function(xhr, status, error) {
                alert('Failed to retrieve dish details. Please try again.');
            }
        });
    }
    $('.submit').on('click', function() {
        console.log("Button clicked");
        setCookie('backButton', 'true', 7);

        let id = $(this).attr('id');
        let parts = id.split('_');
        let version = '_' + parts[1];
        let type = $(this).parent().parent().find('#type').val()

        let modal, detailDiv, dishPrice, dish_id, currency_symbol;
        if (type == 'discount') {
            modal = $('#productDiscountModal');
            detailDiv = '#div-detail';
            dishPrice = parseFloat($(`#dish-total`).text().replace(/[^\d.]/g, ''));
            dish_id = $(`#id`).val();
            console.log(dishPrice);
            currency_symbol = $(`#currency_symbol`).val();


        } else {

            modal = $(`#productModal${version}`);
            detailDiv = `#div-detail${version}`;
            dishPrice = parseFloat($(`#total-price${version}`).data('unit-price'));
            dish_id = $(`#dish_id${version}`).val();
            currency_symbol = $(`#currency_symbol${version}`).val();

        }

        const quantity = parseInt($(detailDiv).find('.num').text()) || 1;
        const notes = $(`#note${version}`).val();
        const dishName = $(detailDiv).find('h5').text().trim();
        const dishImage = $(`#dish-img${version}`).attr('src');
        const selectedSize = $('#div-sizes').find('.size-option:checked');
        const selectedSizePrice = $('#div-sizes').find('.size-option:checked').val();
        const selectedSizeLabel = $('#div-sizes').find('.size-option:checked').siblings('label').text();
        const selectedSizeId = $('#div-sizes').find('.size-option:checked').data('id');
        const selectedAddons = [];

        $('#div-addons').find('.addon-option:checked').each(function() {
            // Extract addonId from the ID attribute
            const idAttr = $(this).attr('id'); // e.g., "addon-27"
            const addonId = idAttr ? idAttr.split('-')[1] : null; // Extract "27" from "addon-27"

            if (addonId) {
                selectedAddons.push({
                    id: addonId,
                    name: $(this).siblings('label').text().trim(),
                    price: parseFloat($(this).val())
                });
            }
        });

        const cartItem = {
            dish_id: dish_id,
            name: dishName,
            image: dishImage,
            type: type,
            price: dishPrice,
            size: {
                id: selectedSizeId,
                price: selectedSizePrice,
                label: selectedSizeLabel
            },
            addons: selectedAddons,
            quantity: quantity,
            notes: notes,
            totalPrice: dishPrice * quantity
        };

        const istakeaway = getCookie('branch_takaway');
        let table_reservation = JSON.parse(localStorage.getItem('TableReservation')) || {};
        let takeaway_details = JSON.parse(localStorage.getItem('takeaway_details')) || {};
        let order_type = 'Delivery';
        if (Object.keys(table_reservation).length > 0) {
            order_type = 'reservation';
        } else {
            if (istakeaway == 'true' && Object.keys(takeaway_details).length > 0) {
                order_type = 'Takeaway';
            }
        }
        let cart = JSON.parse(localStorage.getItem('cart')) || {
            items: [],
            coupon: '',
            coupon_value: 0,
            symbol: currency_symbol,
            order_type: order_type
        };
        const existingItemIndex = cart.items.findIndex(item =>
            item.dish_id === cartItem.dish_id &&
            item.size.id === cartItem.size.id &&
            item.notes === cartItem.notes &&
            JSON.stringify(item.addons) === JSON.stringify(cartItem.addons)
        );

        if (existingItemIndex !== -1) {
            cart.items[existingItemIndex].quantity += cartItem.quantity;
            cart.items[existingItemIndex].totalPrice += cartItem.totalPrice;
        } else {
            cart.items.push(cartItem);
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();

        $('#div-addons').find('.addon-option:checked').prop('checked', false);
        $(`#note${version}`).val('');
        selectedAddons.length = 0; // Clear the array

        // Uncheck the selected size option
        selectedSize.prop('checked', false);
        let resetSizeId = null;
        let resetSizePrice = 0;
        let resetSizeLabel = '';
        modal.modal('hide');
    });

    function updateCartCount() {
        let cart = JSON.parse(localStorage.getItem('cart')) || {
            items: []
        };
        let items = cart.items || []; // Safely access the items array
        let count = items.length; // Count the total number of items
        document.getElementById('cart-count').textContent = count;
    }
    // Call this function whenever the cart changes
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('branchSearch');
        const branchItems = document.querySelectorAll('.branch-item');

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();

            branchItems.forEach((item) => {
                const name = item.querySelector(
                        '.branch-name').textContent
                    .toLowerCase();
                const address = item.querySelector(
                        '.branch-address').textContent
                    .toLowerCase();

                if (name.includes(query) || address
                    .includes(query)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });


        updateCartCount();

    });
</script>
