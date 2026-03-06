<div id="blur-overlay" class="blur-overlay"></div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header cart-header">
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        <h4 id="offcanvasRightLabel">
            تفاصيل العربة
        </h4>
        <div class="cart-total-items" id="cart-side-count">

        </div>
    </div>
    <div class="offcanvas-body">
        <div class="cart-content" id="item-list">
        </div>
        <div class="cart-footer">
            <a class="btn w-100 d-flex justify-content-between align-items-center" href="{{ route('cart') }}"
                id="checkout-btn">
                <i class="fas fa-shopping-cart"></i>
                <span> أذهب الي العربة</span>
                <span id="total-side-pay"></span>
            </a>
        </div>
        <!-- </div> -->
    </div>
</div>
@push('scripts')
    <script>
        // Function to initialize and render the cart
        const initializeCart = () => {
            let local_storage = JSON.parse(localStorage.getItem('cart')) || {
                items: []
            };
            let cart = local_storage.items; // Extract all items from the array
            console.log(cart);

            let currency_symbol = local_storage.symbol || 'ج.م'; // Default currency symbol
            const cartContainer = $('#item-list');
            const totalElement = $('#total-side-pay');
            const cartCountElement = $('#cart-side-count');

            // Function to render the cart
            const renderCart = () => {
                cartContainer.empty();
                let total = 0;

                if (!Array.isArray(cart) || cart.length === 0) {
                    cartContainer.html('<p class="text-center">@lang('cart.cart is empty')</p>');
                    totalElement.text('0.00'.currency_symbol);
                    cartCountElement.text('0');
                    return;
                }

                cart.forEach((item, index) => {
                    const itemSizePrice = item.size && item.size.price ? item.size.price : item
                        .price;
                    const itemAddons = item.addons || [];
                    const addonTotal = itemAddons.reduce((sum, addon) => sum + parseFloat(addon
                        .price || 0), 0);
                    const itemTotal = (item.quantity * itemSizePrice) + addonTotal * item
                        .quantity;
                    total += itemTotal;

                    cartContainer.append(`
                        <div class="sideCart-plate p-4 mb-4" data-index="${index}">
                            <div class="d-flex">
                                <a href="#">
                                    <figure class="sideCart-plate-img m-0">
                                        <img src="${item.image}" alt="${item.name}">
                                    </figure>
                                </a>
                                <div class="cart-details pe-5">
                                    <h5>${item.name}</h5>
                                    <small class="text-muted d-block"><span>@lang('cart.size'):</span> ${item.size?.label || '@lang('cart.unknown')'}</small>
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

                totalElement.text(`${formatCurrency(total)}`);
                cartCountElement.text(cart.length);
                document.getElementById('cart-count').textContent = cart.length;
            };

            // Helper function to format currency
            const formatCurrency = (amount) => `${amount.toFixed(2)} ${currency_symbol}`;
            const updateCartSummary = (total, coupon) => {
                let tax = 0;
                // 0 before tax
                // 1 after tax
                // if (coupon_application) {

                //     tax = (total - coupon) * TAX_RATE;
                // } else {
                tax = total * TAX_RATE;

                // }

                const finalTotal = total - coupon + SERVICE_FEES + SHIPPING_FEES + tax;

                totalElement.text(formatCurrency(total));
                serviceFeeElement.text(formatCurrency(SERVICE_FEES));
                shippingFeeElement.text(formatCurrency(SHIPPING_FEES));
                finalTotalElement.text(formatCurrency(finalTotal));
                if (tax_application_check == 0) {
                    taxElement.text(' ( ' + formatCurrency(tax) + ' ) ');
                } else {
                    taxElement.hide();
                }
                totalPayElement.text(formatCurrency(finalTotal));
                totalElementCoupon = finalTotal;

            };
            // Function to update the cart in localStorage
            const updateCart = () => {
                local_storage.items = cart;
                localStorage.setItem('cart', JSON.stringify(local_storage));
                renderCart();
            };

            // Event listeners for quantity changes
            $(document).on('click', '.inc', function() {
                const index = $(this).data('index'); // Get the index from the button

                // Check if the index is valid and the cart item exists
                if (cart[index] && typeof cart[index].quantity === 'number') {
                    const item = cart[index];

                    // Determine the version based on the item's properties
                    const version = item.size || item.addons ? '_v1' : '_v2';

                    // Increment the quantity
                    cart[index].quantity++;

                    // Update the cart count
                    cartCountElement.text(cart.length);
                    document.getElementById('cart-count').textContent = cart.length;

                    // Recalculate the total price
                    recalculateTotalPrice1(version, item.price, item);

                    // Update the cart in localStorage and re-render
                    updateCart();
                } else {
                    console.error('Invalid cart item or index:', index);
                }
            });

            // $(document).on('click', '.dec', function() {
            //     const index = $(this).data('index');
            //     const version = item.size || item.addons ? '_v1' : '_v2';

            //     if (cart[index] && cart[index].quantity > 1) {
            //         cart[index].quantity--; // Decrement the quantity
            //     } else {
            //         cart.splice(index, 1); // Remove item if quantity is 1
            //     }
            //     recalculateTotalPrice1(version, dishPrice, item, item.quantity);

            //     updateCart(); // Update the cart in localStorage and re-render
            // });
            $(document).on('click', '.dec', function() {
                const index = $(this).data('index'); // Get the index from the button

                // Check if the index is valid and the cart item exists
                if (cart[index] && typeof cart[index].quantity === 'number') {
                    const item = cart[index];

                    // Determine the version based on the item's properties
                    const version = item.size || item.addons ? '_v1' : '_v2';

                    // Increment the quantity
                    if (cart[index] && cart[index].quantity > 1) {
                        cart[index].quantity--; // Decrement the quantity
                    } else {
                        cart.splice(index, 1); // Remove item if quantity is 1
                    }

                    // Update the cart count
                    cartCountElement.text(cart.length);
                    document.getElementById('cart-count').textContent = cart.length;

                    // Recalculate the total price
                    recalculateTotalPrice1(version, item.price, item);

                    // Update the cart in localStorage and re-render
                    updateCart();
                } else {
                    console.error('Invalid cart item or index:', index);
                }
            });
            $(document).on('click', '.edit-item', function() {
                let local_storage = JSON.parse(localStorage.getItem('cart')) || {
                    items: []
                };
                let cart = local_storage.items;
                const index = $(this).data('index');
                const item = cart[index];

                editItem1(item, index);
            });

            function recalculateTotalPrice1(version, dishPrice, item) {
                let modal = $(`#productModal${version}`);
                let selectedSizePrice = parseFloat(modal.find('.size-option:checked').val()) || 0;
                let price = 0;
                let addonsPrice = 0;

                // Use the selected size price if available, otherwise use the dish price
                if (selectedSizePrice) {
                    price = selectedSizePrice;
                } else {
                    price = dishPrice;
                }

                // Calculate addons price
                modal.find('.addon-option:checked').each(function() {
                    addonsPrice += parseFloat($(this).val());
                });

                // Calculate total price
                let quantity = parseInt(modal.find('.num').text()) || 1;
                let newTotalPrice = (price * quantity) + (addonsPrice * quantity);

                // Update total price in the modal
                $(`#total-price${version}`).text(formatCurrency(newTotalPrice));
                $(`#dish-total${version}`).html(formatCurrency(newTotalPrice));
            }
            const editItem1 = (item, index) => {
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

                            //var version = data.dish.has_size ? '_v1' : '_v2'
                            var version = data.dish.has_size || data.dish.has_addon ? '_v1' : '_v2';
                            console.log(version);

                            let modal = $(`#productModal${version}`);
                            const product = data.dish;
                            const dishPrice = parseFloat(product.price);

                            // Reset previous selections before applying new values
                            modal.find('.size-option').prop('checked', false); // Uncheck all sizes
                            modal.find('.addon-option').prop('checked', false); // Uncheck all addons
                            modal.find('#note' + version).val(''); // Clear notes input

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
                                        item.addons.reduce((sum, addon) => sum + addon.price, 0) : 0
                                    );

                                populateSizes1(data.sizes, item, data.branch.currency_symbol);
                                populateAddons1(data.addons, item, data.branch.currency_symbol, data
                                    .min, data.max);
                            } else if (data.dish.has_addon && !data.dish.has_size) {
                                itemTotal = 0;
                                itemTotal = (item.quantity * item.price) +
                                    (item.addons && item.addons.length > 0 ?
                                        item.addons.reduce((sum, addon) => sum + addon.price, 0) : 0
                                    );
                                populateAddons1(data.addons, item, data.branch.currency_symbol, data
                                    .min, data.max);
                                $('#sizes-div').hide();
                            } else if (data.dish.has_size && !data.dish.has_addon) {
                                itemTotal = 0;
                                itemTotal = (item.quantity * item.size.price);
                                console.log('size:' + itemTotal);

                                populateSizes1(data.sizes, item, data.branch.currency_symbol);
                                $('#addons-div').hide();
                            } else {
                                itemTotal = 0;
                                itemTotal = item.price * item.quantity;
                                $(`#dish-total${version}`).html(formatCurrency(itemTotal));

                                $('#sizes-div').hide();
                                $('#addons-div').hide();
                            }



                            $(`#total-price${version}`).html(formatCurrency(itemTotal));
                            $(`#dish-total${version}`).html(formatCurrency(itemTotal));

                            console.log(itemTotal);


                            // let dishPrice = parseFloat(data.dish.price);
                            $('#div-sizes .size-option').on('change', function() {
                                const selectedSize = parseFloat($(this).data('price')) ||
                                    dishPrice; // Get the selected size price or fallback to dish price
                                recalculateTotalPrice1(version, selectedSize, data);
                            });

                            $('#div-addons .addon-option').on('change', function() {
                                recalculateTotalPrice1(version,
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
                                    @lang('cart.please select between') ${data.min} @lang('cart.and') ${data.max} @lang('cart.addons').
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
                                    // Remove error message if validation passes
                                    modal.find('#addon-error').remove();

                                    // Enable all add-ons if under the max limit
                                    modal.find('.addon-option').prop('disabled', false);

                                    // Enable the "Add to Cart" button
                                    modal.find(`#submit${version}`).prop('disabled', false);
                                }
                            }

                            window.increaseQuantity = function(ele) {

                                let quantityElem = $(ele).siblings('.num');
                                let quantity = parseInt(quantityElem.text()) || 1;
                                quantity++;
                                quantityElem.text(quantity);
                                recalculateTotalPrice1(version, dishPrice, data);
                                // saveChanges(itemIndex);
                                // validateCoupon(couponInput.value.trim(), version); //nourhan ->hashing

                            };

                            window.decreaseQuantity = function(ele) {
                                let quantityElem = $(ele).siblings('.num');
                                let quantity = parseInt(quantityElem.text()) || 1;
                                if (quantity > 1) {
                                    quantity--;
                                    quantityElem.text(quantity);
                                    recalculateTotalPrice1(version, dishPrice, data);
                                    // saveChanges(itemIndex);
                                    // validateCoupon(couponInput.value.trim(), version); //nourhan ->hashing
                                }
                            };

                            // $('#dish-quantity').text(`+ أضف إلي العربة (${item.quantity})`);
                            $('.submit').off('click').on('click', function() {
                                saveChanges(index, data.dish.has_size, data.dish.has_addon);
                                //nourhan from totalElementCoupon to totalAmount
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

            const populateSizes1 = (sizes, item, currencySymbol) => {
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

                // Rebind the change event for sizes
            };

            // Helper function to populate addons
            const populateAddons1 = (addons, item, currencySymbol, min, max) => {
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
            // Apply coupon functionality

            const saveChanges = (itemIndex, hasAddon, hasSize) => {
                const version = hasSize || hasAddon ? '_v1' : '_v2';
                const modal = $(`#productModal${version}`);
                let updatedSizePrice = 0;
                let updatedSizeLabel = '';
                let updatedSizeId = 0;
                let updatedAddons = [];
                let sizeUpdate = {};

                // Updated size
                if (version === '_v1') {
                    if (cart[itemIndex].size && cart[itemIndex].size.price) {
                        const selectedSize = $(`#div-sizes .size-option:checked`);
                        updatedSizePrice = parseFloat(selectedSize.val()) || cart[itemIndex].size.price || 0;
                        updatedSizeLabel = selectedSize.siblings('label').text() || cart[itemIndex].size.label ||
                            '';
                        updatedSizeId = selectedSize.attr('id').replace('size-', '') || cart[itemIndex].size.id ||
                            0;
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
                            id: $(this).attr('id').replace('addon-', ''),
                            price: parseFloat($(this).val()),
                            name: $(this).next('label').text()
                        });
                    });
                }

                // Updated notes and quantity
                const updatedNotes = $(`#note${version}`).val() || '';
                const updatedQuantity = parseInt(modal.find('.num').text()) || cart[itemIndex].quantity;

                // Update the cart item
                if (cart[itemIndex]['dish_id']) {
                    cart[itemIndex] = {
                        dish_id: cart[itemIndex]['dish_id'],
                        name: cart[itemIndex]['name'],
                        image: cart[itemIndex]['image'],
                        price: cart[itemIndex]['price'],
                        size: sizeUpdate,
                        addons: updatedAddons.length > 0 ? updatedAddons : cart[itemIndex]['addons'],
                        quantity: updatedQuantity,
                        notes: updatedNotes,
                        totalPrice: (updatedQuantity * updatedSizePrice) + updatedAddons.reduce((sum, addon) =>
                            sum + addon.price, 0)
                    };

                    console.log("Updated cart:", cart);
                }

                // Save updated cart to localStorage
                localStorage.setItem('cart', JSON.stringify(local_storage));

                // Re-render the cart
                renderCart();

                // Hide the modal
                modal.modal('hide');
            };
            // Event listener for deleting items
            $(document).on('click', '.delete-item', function() {
                const index = $(this).data('index');
                cart.splice(index, 1);
                if (cart.length === 0) {
                    localStorage.removeItem('cart');
                    // totalElement.text('0.00'.currency_symbol);
                    document.querySelector('#total-side-pay').textContent = '0.00' + currency_symbol;

                } else {
                    localStorage.setItem('cart', JSON.stringify({
                        items: cart,
                        symbol: currency_symbol
                    }));
                }
                cartCountElement.text(cart.length);
                document.getElementById('cart-count').textContent = cart.length;

                updateCart();
            });

            // Initial render of the cart
            renderCart();
        };

        // Initialize the cart on page load
        initializeCart();
        $(document).ready(function() {

            // Re-render the cart when the cart button is clicked
            $('.cart-btn').on('click', function() {
                initializeCart();
            });
        });
    </script>
@endpush
