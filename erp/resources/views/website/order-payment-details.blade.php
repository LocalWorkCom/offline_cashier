@extends('website.layouts.master')

@section('content')
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('header.home')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('orders.show') }}"> @lang('header.myorder')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> @lang('header.paymentdetails')</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="payment-details">
        <div class="container py-2">
            <h4 class="fw-bold">@lang('header.paymentdetails')</h4>
            <div class="card p-4">
                <div class="card-body">
                    @if (isset($order))
                        <!-- Order Payment Details -->
                        <div class="d-flex justify-content-between pt-3">
                            <h5 class="fw-bold m-0">
                                {{ $order->created_at ? $order->created_at->translatedFormat('j F .g:i A') : '' }}
                            </h5>
                            @php
                                $lastTrackingStatus = $order->tracking->last()?->order_status;
                            @endphp
                            @if ($lastTrackingStatus == 'pending')
                                <p class="bg-warning text-dark rounded fw-bold p-1 mb-0">@lang('order.pending')</p>
                            @elseif ($lastTrackingStatus == 'in_progress')
                                <p class="bg-warning text-dark rounded fw-bold p-1 mb-0">@lang('order.in_progress')</p>
                            @elseif ($lastTrackingStatus == 'on_way')
                                <p class="bg-warning text-dark rounded fw-bold p-1 mb-0">@lang('order.on_way')</p>
                            @elseif ($lastTrackingStatus == 'delivered')
                                <p class="bg-success text-white rounded fw-bold p-1 mb-0">@lang('order.delivered')</p>
                            @elseif ($lastTrackingStatus == 'readyForPickup')
                                <p class="bg-success text-white rounded fw-bold p-1 mb-0">@lang('order.readyForPickup')</p>
                            @elseif ($lastTrackingStatus == 'cancelled')
                                <p class="bg-danger text-dark rounded fw-bold p-1 mb-0">@lang('order.cancelled')</p>
                            @endif
                        </div>

                        <div class="accordion" id="accordionPanelsStayOpenExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header py-2" id="panelsStayOpen-headingOne">
                                    <button class="accordion-button p-0" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true"
                                        aria-controls="panelsStayOpen-collapseOne">
                                        <h6 class="fw-bold">
                                            <i class="fas fa-file-alt main-color fa-xs"></i>
                                            @lang('header.orderdetails')
                                        </h6>
                                    </button>
                                </h2>
                                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show"
                                    aria-labelledby="panelsStayOpen-headingOne">
                                    <div class="accordion-body p-0">
                                        <ul class="list-unstyled p-0 mb-0">
                                            @foreach ($order->orderDetails as $detail)
                                                <div>
                                                    <span
                                                        class="p-1 bg-secondary text-light rounded">{{ $detail->quantity }}</span>
                                                    <span class="mx-1">x</span>
                                                    <span>{{ app()->getLocale() === 'ar' ? $detail->dish?->name_ar : $detail->dish?->name_en }}</span>
                                                    @if ($detail->dishSize || $order->orderAddons->where('order_details_id', $detail->id)->isNotEmpty())
                                                        <p class="text-muted mt-1 mb-0">
                                                            @if ($detail->dishSize)
                                                                <small>@lang('header.size'):
                                                                    <span>{{ app()->getLocale() === 'ar' ? $detail->dishSize->size_name_ar : $detail->dishSize->size_name_en }}</span>
                                                                    <br>
                                                            @endif
                                                            @if ($order->orderAddons->where('order_details_id', $detail->id)->isNotEmpty())
                                                                <small>@lang('header.addons'):
                                                                    @php
                                                                        $addons = $order->orderAddons->where(
                                                                            'order_details_id',
                                                                            $detail->id,
                                                                        );
                                                                    @endphp
                                                                    @foreach ($addons as $addon)
                                                                        {{ app()->getLocale() === 'ar' ? $addon->Addon?->addons?->name_ar : $addon->Addon?->addons?->name_en }},
                                                                    @endforeach
                                                                </small>
                                                            @endif
                                                            <small class="text-muted py-1 d-block">
                                                                @if ($detail->note)
                                                                    @lang('header.note') : {{ $detail->note }}
                                                                @endif
                                                            </small>
                                                        </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                            <li class="order-list">
                                                <p class="mb-0">@lang('header.totalorder')</p>
                                                <p class="mb-0 py-1">
                                                    {{ $order->total_price_after_tax }}
                                                    {{ $order->Branch->country->currency_symbol }}
                                                </p>
                                            </li>
                                            <li class="order-list">
                                                <p class="mb-0">@lang('header.feesdelivery')</p>
                                                <p class="mb-0 py-1">
                                                    @if ($order->delivery_fees)
                                                        {{ $order->delivery_fees }}
                                                        {{ $order->Branch->country->currency_symbol }}
                                                    @else
                                                        @lang('header.nodliveryfees')
                                                    @endif
                                                </p>
                                            </li>
                                            <li class="order-list">
                                                <p class="mb-0">@lang('header.fees')</p>
                                                <p class="mb-0 py-1">
                                                    @if ($order->delivery_fees)
                                                        {{ $order->service_fees }}
                                                        {{ $order->Branch->country->currency_symbol }}
                                                    @else
                                                        @lang('header.nofees')
                                                    @endif
                                                </p>
                                            </li>
                                        </ul>
                                        @if ($order->tax_application == 0)
                                            <div class="d-flex justify-content-between">
                                                <p class="mb-0">@lang('header.includefees')
                                                    <span class="main-color fw-bold">
                                                        {{ number_format(($order->tax_value / $order->total_price_befor_tax) * 100, 2) . '%' }}
                                                    </span>@lang('header.anotherway')
                                                    {{ $order->tax_value }}
                                                    {{ $order->Branch->country->currency_symbol }}
                                                </p>
                                            </div>
                                        @else
                                            <div class="d-flex justify-content-between">
                                                <p class="mb-0">@lang('header.notincludefees')</p>
                                            </div>
                                        @endif
                                        <ul class="list-unstyled p-0 mb-0">
                                            <li class="order-list">
                                                <p class="mb-0">@lang('header.paymentmethod')</p>
                                                <p class="mb-0 py-1">
                                                    @switch($order->orderTransactions->first()?->payment_method)
                                                        @case('cash')
                                                            @lang('header.cash')
                                                        @break

                                                        @case('credit_card')
                                                            @lang('header.credit_card')
                                                        @break

                                                        @case('online')
                                                            @lang('header.online')
                                                        @break

                                                        @default
                                                            @lang('header.cash')
                                                    @endswitch
                                                </p>
                                            </li>
                                            <li class="order-list">
                                                <p class="mb-0">@lang('header.deliveryTime')</p>
                                                <p class="mb-0 py-1">
                                                    {{ getSetting('delivery_time') . __('header.min') }}
                                                </p>
                                            </li>
                                            <li class="order-list">
                                                @if ($order->note)
                                                    <p class="mb-0">@lang('header.note')</p>
                                                    <p class="mb-0 py-1">{{ $order->note }}</p>
                                                @else
                                                    @lang('header.nonote')
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif(isset($reservation))
                        <!-- Reservation Payment Details -->
                        <div class="d-flex justify-content-between pt-3">
                            <h5 class="fw-bold m-0">
                                {{ $reservation->created_at ? $reservation->created_at->translatedFormat('j F .g:i A') : '' }}
                            </h5>
                            @if ($reservation->status == 'confirm')
                                <div class="bg-grey text-dark rounded fw-bold p-1 mb-0">
                                    <img class="small-done"
                                        src="{{ asset('front/AlKout-Resturant/SiteAssets/images/donee.png') }}"
                                        alt="" />
                                    <span>@lang('order.reservationConfirmed')</span>
                                </div>
                            @elseif ($reservation->status == 'cancel')
                                <div class="bg-danger text-dark rounded fw-bold p-1 mb-0">
                                    <span>@lang('order.cancelled')</span>
                                </div>
                            @endif
                        </div>

                        <div class="accordion" id="reservationAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header py-2" id="reservationHeadingOne">
                                    <button class="accordion-button p-0" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#reservationCollapseOne" aria-expanded="true"
                                        aria-controls="reservationCollapseOne">
                                        <h6 class="fw-bold">
                                            <i class="fas fa-file-alt main-color"></i>
                                            @lang('header.orderdetails')
                                        </h6>
                                    </button>
                                </h2>
                                <div id="reservationCollapseOne" class="accordion-collapse collapse show"
                                    aria-labelledby="reservationHeadingOne" data-bs-parent="#reservationAccordion">
                                    <div class="accordion-body p-0">
                                        @if ($reservation->reservation_type == 'without')
                                            @if ($reservation->transaction->payment_status == 'unpaid')
                                                <p>@lang('order.withoutDeposit')</p>
                                            @elseif($reservation->transaction->payment_status == 'part')
                                                <div class="d-flex justify-content-between">
                                                    <p>{{ $reservation->transaction->paid }}
                                                        {{ $reservation->branch->country->currency_symbol }}
                                                    </p>
                                                    @if ($reservation->transaction->payment_method == 'online')
                                                        <p>@lang('order.paidOnline')</p>
                                                    @elseif($reservation->transaction->payment_method == 'cash')
                                                        <p>@lang('order.paidCash')</p>
                                                    @elseif($reservation->transaction->payment_method == 'credit_card')
                                                        <p>@lang('order.paidCredit')</p>
                                                    @endif
                                                </div>
                                            @endif
                                        @elseif ($reservation->reservation_type == 'with')
                                            <div class="table-details w-50">
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-table.svg') }}"
                                                    alt="" />
                                                <h5 class="fw-bold d-inline">@lang('order.table')
                                                    ({{ $reservation->tables->table_number }})
                                                    @if ($reservation->reservation_type == 'with')
                                                        <span class="text-warning">(@lang('order.withOrder'))</span>
                                                    @endif
                                                </h5>
                                                <div>
                                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/clock.svg') }}"
                                                        alt="" />
                                                    <span class="text-muted mb-0">
                                                        <span>
                                                            @lang('order.arrival'):
                                                        </span>
                                                        {{ $reservation->time_from ? $reservation->time_from->format('h:i') : 'N/A' }}
                                                        @lang($reservation->time_from->format('A') === 'AM' ? 'header.am' : 'header.pm')
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/calender.svg') }}"
                                                            alt="" />
                                                        <span class="text-muted mb-0">
                                                            {{ $reservation->date->translatedFormat('j F , Y') }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}"
                                                            alt="" />
                                                        <span class="text-muted mb-0">
                                                            {{ $reservation->adult + $reservation->men + $reservation->women }}@lang('order.person')
                                                            @if ($reservation->kids != 0)
                                                                ,{{ $reservation->kids }}@lang('order.kid')
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table.svg') }}"
                                                            alt="" />
                                                        <span class="text-muted mb-0">
                                                            @if ($reservation->tables->type == 1)
                                                                @lang('order.inside')
                                                            @else
                                                                @lang('order.outside')
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-clock.svg') }}"
                                                            alt="" />
                                                        <span class="text-muted mb-0 main-color">
                                                            <span>
                                                                @lang('order.leaving'):
                                                            </span>
                                                            {{ $reservation->time_to ? $reservation->time_to->format('h:i') : 'N/A' }}
                                                            @lang($reservation->time_to->format('A') === 'AM' ? 'header.am' : 'header.pm')
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <h5 class="fw-bold mt-5">@lang('order.items')</h5>
                                            @foreach ($reservation->order->orderDetails as $detail)
                                                @php
                                                    $addons = $reservation->order->orderAddons->filter(
                                                        fn($addon) => $addon->order_details_id == $detail->id &&
                                                            $addon->Addon?->addons?->name_ar,
                                                    );
                                                @endphp
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span
                                                            class="p-1 bg-secondary text-light rounded">{{ $detail->quantity }}</span>
                                                        <span class="mx-1">x</span>
                                                        <span>{{ app()->getLocale() === 'ar' ? $detail->dish?->name_ar : $detail->dish?->name_en }}</span>
                                                        @if ($detail->dishSize)
                                                            <span>({{ app()->getLocale() === 'ar' ? $detail->dishSize->size_name_ar : $detail->dishSize->size_name_en }})</span>
                                                        @endif
                                                    </div>
                                                    <p class="mb-0 fw-bold">
                                                        @if ($reservation->order->tax_applications == 0)
                                                            {{ $detail->price_befor_tax + $addons->sum('price_before_tax') }}
                                                        @else
                                                            {{ $detail->price_after_tax + $addons->sum('price_after_tax') }}
                                                        @endif
                                                        {{ $reservation->order->Branch->country->currency_symbol }}
                                                    </p>
                                                </div>
                                                @if ($addons->isNotEmpty())
                                                    <div class="text-muted">
                                                        <small>
                                                            {{ $addons->map(fn($addon) => app()->getLocale() === 'ar' ? $addon->Addon->addons->name_ar : $addon->Addon->addons->name_en)->implode(', ') }}
                                                        </small>
                                                    </div>
                                                @endif
                                            @endforeach
                                            <ul class="list-unstyled p-0 mb-0 mt-3">
                                                <li class="order-list">
                                                    <p class="mb-0">@lang('header.totalorder')</p>
                                                    <p class="mb-0 py-1">
                                                        @php
                                                            $subTotalPrice = $reservation->order->total_price_befor_tax;
                                                            if ($reservation->order->tax_application == 1) {
                                                                $subTotalPrice += $reservation->order->tax_value;
                                                            }
                                                        @endphp
                                                        {{ $subTotalPrice }}
                                                        {{ $reservation->order->Branch->country->currency_symbol }}
                                                    </p>
                                                </li>
                                                <li class="order-list">
                                                    @if ($reservation->order->coupon_value != 0)
                                                        <p class="main-color mb-0"> @lang('header.coupon')</p>
                                                        <p class="main-color mb-0">
                                                            -{{ $reservation->order->coupon_value }}
                                                            {{ $reservation->order->Branch->country->currency_symbol }}
                                                        </p>
                                                    @endif
                                                </li>
                                                @if ($reservation->order->transaction->first()->payment_status === 'part')
                                                    <li class="order-list">
                                                        <p class="mb-0"> @lang('order.withDeposit')</p>
                                                        <p class="main-color mb-0">
                                                            -{{ $reservation->order->transaction->first()->paid }}
                                                            {{ $reservation->order->Branch->country->currency_symbol }}
                                                        </p>
                                                    </li>
                                                    <li class="order-list">
                                                        <p class="mb-0">@lang('order.remainingPrice')</p>
                                                        <p class="mb-0">
                                                            @if ($reservation->order->tax_application == 0)
                                                                {{ $reservation->order->total_price_befor_tax - $reservation->order->transaction->first()->paid }}
                                                            @else
                                                                {{ $reservation->order->total_price_befor_tax + $reservation->order->tax_value - $reservation->order->transaction->first()->paid }}
                                                            @endif
                                                            {{ $reservation->order->Branch->country->currency_symbol }}
                                                        </p>
                                                    </li>
                                                @endif
                                                <li class="order-list">
                                                    <p class="mb-0">@lang('header.fees')</p>
                                                    <p class="mb-0">
                                                        @if ($reservation->order->service_fees)
                                                            {{ $reservation->order->service_fees }}
                                                            {{ $reservation->order->Branch->country->currency_symbol }}
                                                        @else
                                                            @lang('header.nofees')
                                                        @endif
                                                    </p>
                                                </li>
                                            </ul>
                                            @if ($reservation->order->tax_application == 0)
                                                <div class="d-flex justify-content-between">
                                                    <p class="mb-0">@lang('header.includefees')
                                                        <span class="main-color fw-bold">
                                                            {{ number_format(($reservation->order->tax_value / $reservation->order->total_price_befor_tax) * 100, 2) . '%' }}
                                                        </span>@lang('header.anotherway')
                                                        {{ $reservation->order->tax_value }}
                                                        {{ $reservation->order->Branch->country->currency_symbol }}
                                                    </p>
                                                </div>
                                            @else
                                                <div class="d-flex justify-content-between">
                                                    <p class="mb-0">@lang('header.notincludefees')</p>
                                                </div>
                                            @endif
                                            <ul class="list-unstyled p-0 mb-0">
                                                <li class="order-list">
                                                    <p class="mb-0">@lang('header.paymentmethod')</p>
                                                    <p class="mb-0 py-1">
                                                        @switch($reservation->order->orderTransactions->first()?->payment_method)
                                                            @case('cash')
                                                                @lang('header.cash')
                                                            @break

                                                            @case('credit_card')
                                                                @lang('header.credit_card')
                                                            @break

                                                            @case('online')
                                                                @lang('header.online')
                                                            @break

                                                            @default
                                                                @lang('header.cash')
                                                        @endswitch
                                                    </p>
                                                </li>
                                            </ul>
                                            <div class="d-flex justify-content-between border-top pt-2">
                                                <h5 class="fw-bold"> @lang('header.total') </h5>
                                                <h5 class="fw-bold">
                                                    {{ $reservation->order->total_price_after_tax }}
                                                    {{ $reservation->order->Branch->country->currency_symbol }} </h5>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
