<div class="modal fade" tabindex="-1" id="productModal_v1" aria-labelledby="productModalLabel" aria-modal="true"
    role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="dish_id" id="dish_id_v1">
                <input type="hidden" name="currency_symbol" id="currency_symbol_v1">
                <h2 class="text-center mb-4">@lang('cart.choose your meal,please')</h2>
                <div class="row mx-0">
                    <div class="col-md-6">
                        <div class="product-details">
                            <div id="sizes-div" style="display: none">

                                <h4>@lang('cart.choose size')
                                </h4>
                                <div class="choices my-3" id="div-sizes">

                                </div>
                            </div>
                            <div id="addons-div" style="display: none">

                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="product">
                            <figure class="product-img m-0">
                                <img src="" alt="" id="dish-img_v1">
                                <figcaption class="pt-3" id="div-detail_v1">

                                </figcaption>
                            </figure>
                        </div>
                        <div class="notes my-3">
                            <h4>
                                <i class="fas fa-file-alt main-color fa-xs"></i>
                                @lang('cart.Do you have any additional comments?')
                            </h4>
                            <div class="form-floating mt-3">
                                <textarea class="form-control" placeholder=" @lang('cart.add your notes,please')" id="note_v1" style="height: 100px"></textarea>
                            </div>
                        </div>
                        <button class="btn w-100 d-flex justify-content-between mt-3 submit" id="submit_v1">
                            <span id="dish-quantity"> +
                                @lang('cart.add to cart')
                            </span>
                            <span id="dish-total_v1"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" id="productModal_v2" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="dish_id" id="dish_id_v2">

                <input type="hidden" name="currency_symbol" id="currency_symbol_v2">


                <h2 class="text-center mb-4"> @lang('cart.choose your meal,please') </h2>
                <div>
                    <div class="product">
                        <figure class="product-img m-0">
                            <img src="SiteAssets/images/plate1.png" alt="" id="dish-img_v2">
                            <figcaption class="pt-3" id="div-detail_v2">

                            </figcaption>
                        </figure>
                    </div>
                    <div class="notes my-3">
                        <h4>
                            <i class="fas fa-file-alt main-color fa-xs"></i>

                            @lang('cart.Do you have any additional comments?')
                        </h4>
                        <div class="form-floating mt-3">
                            <textarea class="form-control" placeholder="@lang('cart.add your notes,please')" id="note_v2" style="height: 100px"></textarea>
                            <label for="note"> @lang('cart.add your notes,please')</label>
                        </div>
                    </div>
                    <button class="btn w-100 d-flex justify-content-between mt-3 submit" id="submit_v2">
                        <span id="dish-quantity_v2"> + @lang('cart.add to cart')
                        </span>
                        <span id="dish-total_v2"></span>
                    </button>

                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" id="productDiscountModal" aria-labelledby="productDiscountModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="type" id="type" value="discount">
                <input type="hidden" name="currency_symbol" id="currency_symbol">


                <h2 class="text-center mb-4">@lang('choose your meal,please')</h2>
                <div>
                    <div class="product">
                        <figure class="product-img m-0">
                            <img src="SiteAssets/images/plate1.png" alt="" id="dish-img">
                            <figcaption class="pt-3" id="div-detail">

                            </figcaption>
                        </figure>
                    </div>
                    <div class="notes my-3">
                        <h4>
                            <i class="fas fa-file-alt main-color fa-xs"></i>
                            @lang('cart.Do you have any additional comments?')
                        </h4>
                        <div class="form-floating mt-3">
                            <textarea class="form-control" placeholder="@lang('cart.add your notes,please')" id="note" style="height: 100px"></textarea>
                            <label for="floatingTextarea2"> @lang('cart.add your notes,please') </label>
                        </div>
                    </div>
                    <button class="btn w-100 d-flex justify-content-between mt-3 submit" id="submit">
                        <span id="dish-quantity"> + @lang('cart.add to cart')
                        </span>
                        <span id="dish-total"></span>
                    </button>

                </div>
            </div>
        </div>
    </div>
</div>
