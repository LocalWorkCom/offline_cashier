@if ($policy == 'no_payment_required')
    <div class="form-check" id="no_payment_required_div">
        <label class="form-check-label" for="paying-{{ $policy }}">
            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/pay.png') }}" alt="" />
            @if (isset($type) && $type == 'reservation_without_order')
                الدفع عند الوصول
            @else
                الدفع عند الاستلام
            @endif
        </label>
        <input class="form-check-input" type="radio" value="no_payment_required" id="paying-{{ $policy }}"
            name="{{ $name }}">
    </div>
    <div id="payment_error_message" style="display: none; color: red;"></div>

    @php $firstChecked = true; @endphp
@endif

@if ($policy == 'deposit_required')
    <div class="form-check mt-3">
        <label class="form-check-label" for="online-deposit-{{ $policy }}">
            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/online-payment.svg') }}" alt="" />
            دفع عربون اونلاين
        </label>
        <input class="form-check-input" type="radio" name="{{ $name }}"
            id="online-deposit-{{ $policy }}" value="deposit_required">
    </div>
    @php $firstChecked = true; @endphp
@endif

@if ($policy == 'full_payment_required')
    <div class="form-check  mt-3">
        <label class="form-check-label" for="full-payment-{{ $policy }}">
            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/online-payment.svg') }}" alt="" />
            دفع المبلغ بالكامل اون لاين
        </label>
        <input class="form-check-input" type="radio" value="full_payment_required"
            id="full-payment-{{ $policy }}" name="{{ $name }}">
    </div>
    @php $firstChecked = true; @endphp
@endif
