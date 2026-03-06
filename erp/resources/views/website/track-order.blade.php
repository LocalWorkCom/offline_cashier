@extends('website.layouts.master')
<style>
    .sender-avatar {
        vertical-align: middle;
    }

    .message {
        margin-bottom: 15px;
    }

    #driverChatButton {
        position: fixed;
        bottom: 100px;
        right: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        overflow: visible;
    }

    /* #driverChatButton .chat-close-btn {
        position: absolute;
        top: -5px;
        right: -5px;
        background: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        z-index: 1101;
    } */
    #driverChatButton .default-text {
        display: none;
    }

    #driverChatButton.minimized {
        display: flex;
    }

    #driverChatButton.minimized .user-icon {
        display: block;
    }

    #driverChatButton.minimized .chat-close-btn {
        display: flex;
    }

    #driverChatButton.minimized .default-icon {
        display: none;
    }

    /* Chat Box */
    #driverChatBox {
        position: fixed;
        bottom: 170px;
        right: 20px;
        width: 300px;
        max-height: 400px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        z-index: 1099;
    }

    #driverChatBox.show {
        display: flex;
    }

    #driverChatBox .chat-header {
        padding: 10px;
        background: #f5f5f5;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    #driverChatBox .chat-header .user-img img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    #driverChatBox .chat-messages {
        flex: 1;
        padding: 10px;
        overflow-y: auto;
        max-height: 300px;
    }

    #driverChatBox .message {
        display: flex;
        align-items: flex-end;
        margin: 10px 0;
        max-width: 100%;
        word-wrap: break-word;
    }

    #driverChatBox .bot-message {
        justify-content: flex-start;
    }

    #driverChatBox .user-message {
        justify-content: flex-end;
    }

    #driverChatBox .message span {
        background: #f1f1f1;
        padding: 8px 12px;
        border-radius: 10px;
        max-width: 70%;
        word-break: break-word;
        display: inline-block;
    }

    #driverChatBox .bot-message span {
        background: #e9ecef;
    }

    #driverChatBox .message img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-left: 10px;
    }

    #driverChatBox .bot-message img {
        margin-left: 0;
        margin-right: 10px;
    }

    #driverChatBox .chat-input {
        padding: 10px;
        border-top: 1px solid #ddd;
        display: flex;
        align-items: center;
    }

    #driverChatBox .chat-input .form-control {
        flex: 1;
        margin: 0 5px;
    }

    #driverChatBox .chat-icon {
        width: 20px;
        height: 20px;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.show.active {
        display: block;
    }

    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
    }

    .nav-tabs .nav-item {
        margin-bottom: -2px;
    }

    .nav-tabs .nav-link {
        color: #495057;
        background-color: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: #007bff;
        border-bottom: 2px solid #007bff;
        background-color: #f8f9fa;
    }

    .nav-tabs .nav-link.active {
        color: #007bff;
        background-color: #fff;
        border-bottom: 2px solid #007bff;
        font-weight: 600;
    }

    .nav-tabs .nav-link:focus {
        outline: none;
        box-shadow: none;
    }
</style>
@section('content')
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('header.home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> @lang('header.track-order')</li>
                </ol>
            </nav>
        </div>
    </section>

    <section>
        <div class="container py-2">
            <div class="row">
                <div class="col-12">
                    @php
                        $hasOrders = false;
                        $hasDeliveryOrders = $orders && $orders->where('type', 'Delivery')->isNotEmpty();
                        $hasTakeawayOrders = $orders && $orders->where('type', 'Takeaway')->isNotEmpty();
                        $hasReservations = $reservations && $reservations->isNotEmpty();
                        $deliveryStatuses = [
                            'pending' => [
                                'label' => 'فى انتظار الموافقه',
                                'icon' => 'fa-mobile-alt',
                            ],
                            'in_progress' => [
                                'label' => 'يتم تحضير طلبك',
                                'icon' => 'fa-utensils',
                            ],
                            'on_way' => [
                                'label' => 'طلبك فى الطريق اليك',
                                'icon' => 'fa-map-marker-alt',
                            ],
                            'delivered' => [
                                'label' => 'تم التوصيل',
                                'icon' => 'fa-check-circle',
                            ],
                        ];
                        $takeawayStatuses = [
                            'pending' => [
                                'label' => __('trackOrder.pending'),
                                'icon' => 'fa-mobile-alt',
                            ],
                            'in_progress' => [
                                'label' => __('trackOrder.in_progress'),
                                'icon' => 'fa-utensils',
                            ],
                            'completed' => [
                                'label' => __('trackOrder.ready'),
                                'icon' => 'fa-check-circle',
                            ],
                        ];

                    @endphp

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-4" id="orderTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $hasDeliveryOrders ? 'active' : '' }}" id="delivery-tab"
                                data-bs-toggle="tab" data-bs-target="#delivery" type="button" role="tab"
                                aria-controls="delivery"
                                aria-selected="{{ $hasDeliveryOrders ? 'true' : 'false' }}">@lang('header.delivery')</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !$hasDeliveryOrders && $hasTakeawayOrders ? 'active' : '' }}"
                                id="takeaway-tab" data-bs-toggle="tab" data-bs-target="#takeaway" type="button"
                                role="tab" aria-controls="takeaway"
                                aria-selected="{{ !$hasDeliveryOrders && $hasTakeawayOrders ? 'true' : 'false' }}">@lang('header.takeaway')</button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ !$hasDeliveryOrders && !$hasTakeawayOrders && $hasReservations ? 'active' : '' }}"
                                id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button"
                                role="tab" aria-controls="reservations"
                                aria-selected="{{ !$hasDeliveryOrders && !$hasTakeawayOrders && $hasReservations ? 'true' : 'false' }}">@lang('header.reservations')</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="orderTabsContent">
                        <!-- Delivery Tab -->
                        <div class="tab-pane fade {{ $hasDeliveryOrders ? 'show active' : '' }}" id="delivery"
                            role="tabpanel" aria-labelledby="delivery-tab">
                            @if ($orders)
                                @foreach ($orders as $order)
                                    @if ($order->type === 'Delivery')
                                        @php $hasOrders = true; @endphp
                                        <div id="order-{{ $order->id }}" class="card mt-2 p-3"
                                            data-current-order="{{ $order->id }}"
                                            data-driver-id="{{ $order->delivery->id ?? '' }}"
                                            data-driver-image="{{ $order->delivery && $order->delivery->image ? asset($order->delivery->image) : asset('front/AlKout-Resturant/SiteAssets/images/delivery-man.png') }}"
                                            data-channel-id="{{ $order->chatChannel->id ?? '' }}">
                                            <div class="card-header bg-white">
                                                <div class="d-flex justify-content-between">
                                                    <h5 class="card-title fw-bold mb-3"> @lang('header.orderstatus')</h5>
                                                    @php
                                                        $lastTrackingStatus = $order->tracking->last()?->order_status;
                                                    @endphp
                                                    @if ($order->status === 'pending')
                                                        <button
                                                            class="btn reversed main-color d-flex fw-bold cancel-order-btn"
                                                            data-order-id="{{ $order->id }}">
                                                            @lang('header.cancelorder')
                                                        </button>
                                                    @endif
                                                </div>
                                                @php
                                                    $lastTracking = $order->tracking->last();

                                                    $orderCreationTime = $order->time;

                                                    $fromTime = $orderCreationTime
                                                        ? \Carbon\Carbon::parse($orderCreationTime)
                                                        : null;

                                                    $deliveryTime = getBranchSettings(
                                                        $order->branch_id,
                                                        'delivery_time',
                                                    );
                                                    // $bufferTime = 15;

                                                    $toTime = $fromTime
                                                        ? $fromTime->copy()->addMinutes($deliveryTime) //+ $bufferTime)
                                                        : null;

                                                    $estimatedMinutes = $lastTracking?->time;
                                                @endphp

                                                <h6 class="mb-3"> @lang('header.deliverytimeexpect')</h6>
                                                <h5 class="main-color fw-bold mb-3 d-flex flex-wrap">
                                                    @if ($fromTime && $toTime)
                                                        <span class="to-time">
                                                            {{ $fromTime ? $fromTime->format('h:i') : 'N/A' }}
                                                            @lang($fromTime && $fromTime->format('A') === 'AM' ? 'header.am' : 'header.pm') -
                                                        </span>
                                                        <span class="from-time">
                                                            {{ $toTime ? $toTime->format('h:i') : 'N/A' }}
                                                            @lang($toTime && $toTime->format('A') === 'AM' ? 'header.am' : 'header.pm')
                                                        </span>
                                                    @endif
                                                </h5>
                                                <h5 class="p-1 bg-warning text-dark rounded d-inline-block">
                                                    @lang('header.ordernum') {{ $order->order_number }}
                                                </h5>
                                            </div>

                                            <div class="card-body p-0">
                                                <div class="wizard my-3">
                                                    {{-- code for websockit update dynamic --}}
                                                    @php
                                                        $trackingStatuses = $order->tracking
                                                            ->pluck('order_status')
                                                            ->toArray();
                                                        // Map readyForPickup to in_progress for display purposes
                                                        $trackingStatuses = array_map(function ($status) {
                                                            return $status === 'readyForPickup'
                                                                ? 'in_progress'
                                                                : $status;
                                                        }, $trackingStatuses);
                                                        $currentStatus = last($trackingStatuses);
                                                    @endphp
                                                    <ul class="nav nav-tabs justify-content-between w-100 timeline"
                                                        id="myTab" role="tablist">
                                                        @foreach ($deliveryStatuses as $key => $status)
                                                            @php
                                                                $isCompleted =
                                                                    in_array($key, $trackingStatuses) ||
                                                                    ($key === 'in_progress' &&
                                                                        in_array(
                                                                            'readyForPickup',
                                                                            $order->tracking
                                                                                ->pluck('order_status')
                                                                                ->toArray(),
                                                                        ));
                                                                $isActive =
                                                                    $key === $currentStatus ||
                                                                    ($key === 'in_progress' &&
                                                                        $currentStatus === 'readyForPickup');
                                                            @endphp
                                                            <li class="nav-item" role="presentation">
                                                                <a class="nav-link rounded-circle d-flex align-items-center justify-content-center
                                                            {{ $isActive ? 'active' : '' }}
                                                            {{ $isCompleted && !$isActive ? 'completed' : '' }}
                                                            {{ !$isCompleted && !$isActive ? 'disabled' : '' }}"
                                                                    href="javascript:void(0);"
                                                                    id="step{{ $loop->index }}-tab{{ $order->id }}"
                                                                    data-status="{{ $key }}" role="tab">
                                                                    <i class="fas {{ $status['icon'] }}"></i>
                                                                </a>
                                                                <span class="d-block mt-2">{{ $status['label'] }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold"> @lang('header.orderSummary')</h5>
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold"> <i
                                                            class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                        @lang('header.deliveryinfo')
                                                    </h5>
                                                    @if ($order->address)
                                                        <p>
                                                            <strong> @lang('header.address') :</strong>
                                                            {{ $order->address->address }}
                                                        </p>
                                                        <p>
                                                            <strong>@lang('header.state') :</strong>
                                                            {{ $order->address->state }}
                                                        </p>
                                                        <p>
                                                            <strong>@lang('header.extranote') :</strong>
                                                            @if ($order->address->building || $order->address->floor_number || $order->address->apartment_number)
                                                                <div>
                                                                    @if ($order->address->building)
                                                                        {{ __('header.bulding') }}:
                                                                        {{ $order->address->building }}<br>
                                                                    @endif
                                                                    @if ($order->address->floor_number)
                                                                        {{ __('header.floor') }}:
                                                                        {{ $order->address->floor_number }}<br>
                                                                    @endif
                                                                    @if ($order->address->apartment_number)
                                                                        {{ __('header.apartment') }}:
                                                                        {{ $order->address->apartment_number }}<br>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div>@lang('header.noextrainfo')</div>
                                                            @endif
                                                        </p>
                                                        <p>
                                                            <strong>@lang('header.note') :</strong>
                                                            {{ $order->address->notes ?? __('header.nonote') }}
                                                        </p>
                                                        <p>
                                                            <strong> @lang('header.phone') :</strong>
                                                            {{ $order->address->address_phone ?? __('header.nophone') }}
                                                        </p>
                                                    @else
                                                        <p>@lang('header.noaddress')</p>
                                                    @endif
                                                    <p><strong> @lang('header.name') :</strong> {{ $order->client?->name }}
                                                    </p>
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold"> <i
                                                            class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                        @lang('header.paymentinfo')

                                                    </h5>
                                                    @if ($order->orderTransactions->isNotEmpty())
                                                        @foreach ($order->orderTransactions as $transaction)
                                                            <p>
                                                                @lang('header.paymentmethod')
                                                                <strong>
                                                                    @switch($transaction->payment_method)
                                                                        @case('cash')
                                                                            <span class="p-1 bg-success text-light rounded">
                                                                                @lang('header.cash') </span>
                                                                        @break

                                                                        @case('credit_card')
                                                                            <span class="p-1 bg-success text-light rounded">
                                                                                @lang('header.credit_card') </span>
                                                                        @break

                                                                        @case('online')
                                                                            <span class="p-1 bg-success text-light rounded">
                                                                                @lang('header.online') </span>
                                                                        @break

                                                                        @default
                                                                            <span class="p-1 bg-success text-light rounded">
                                                                                @lang('header.cash') </span>
                                                                    @endswitch
                                                            </p>
                                                        @endforeach
                                                    @else
                                                        <p>
                                                            @lang('header.nopaymentmethod')
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold">
                                                        <i class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                        @lang('header.orderdetails')
                                                    </h5>
                                                    @foreach ($order->orderDetails as $detail)
                                                        @php
                                                            $addons = $order->orderAddons->filter(
                                                                fn($addon) => $addon->order_details_id == $detail->id &&
                                                                    $addon->Addon?->addons?->name_ar,
                                                            );
                                                        @endphp
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="p-1">{{ $detail->quantity }}</span>
                                                                <span class="mx-1">x</span>
                                                                <span>{{ app()->getLocale() === 'ar' ? $detail->dish?->name_ar : $detail->dish?->name_en }}</span>
                                                                @if ($detail->dishSize)
                                                                    <span>({{ app()->getLocale() === 'ar' ? $detail->dishSize->size_name_ar : $detail->dishSize->size_name_en }})</span>
                                                                @endif
                                                            </div>
                                                            <p class="mb-0 fw-bold">
                                                                @if ($order->tax_application == 0)
                                                                    {{ $detail->price_befor_tax + $addons->sum('price_before_tax') }}
                                                                @else
                                                                    {{ $detail->price_after_tax + $addons->sum('price_after_tax') }}
                                                                @endif
                                                                {{ $order->Branch->country->currency_symbol }}
                                                            </p>
                                                        </div>
                                                        @if ($addons->isNotEmpty())
                                                            <div class="text-muted">
                                                                <small>
                                                                    {{ $addons->map(fn($addon) => app()->getLocale() === 'ar' ? $addon->Addon->addons->name_ar : $addon->Addon->addons->name_en)->implode(', ') }}
                                                                </small>
                                                            </div>
                                                        @endif
                                                        @if ($detail->note)
                                                            <div class="text-muted">
                                                                <small>@lang('order.notes'):{{ $detail->note }}</small>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                    @if ($order->note)
                                                        <div>
                                                            <span>@lang('order.orderNote'):</span>
                                                            <span>{{ $order->note }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold"> <i
                                                            class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                        @lang('header.reset')
                                                    </h5>
                                                    <div class="d-flex justify-content-between">
                                                        <p>@lang('header.totalorder')</p>
                                                        <p class="mb-0 fw-bold">
                                                            @php
                                                                $subTotalPrice = $order->total_price_befor_tax;
                                                                if ($order->tax_application == 1) {
                                                                    $subTotalPrice += $order->tax_value;
                                                                }
                                                            @endphp
                                                            {{ $subTotalPrice }}
                                                            {{ $order->Branch->country->currency_symbol }}
                                                        </p>
                                                    </div>
                                                    @if ($order->coupon_value != 0)
                                                        <div class="d-flex justify-content-between">
                                                            <p class="main-color"> @lang('header.coupon')</p>
                                                            <p class="main-color">
                                                                -{{ $order->coupon_value }}
                                                                {{ $order->Branch->country->currency_symbol }}
                                                            </p>
                                                        </div>
                                                    @endif
                                                    <div class="d-flex justify-content-between">
                                                        <p> @lang('header.feesdelivery')</p>
                                                        <p>{{ $order->delivery_fees ?? 0 }}
                                                            {{ $order->Branch->country->currency_symbol }} </p>
                                                    </div>
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
                                                            <p class="mb-0">@lang('header.notincludefees')
                                                            </p>
                                                        </div>
                                                    @endif
                                                    <div class="d-flex justify-content-between border-top pt-2">
                                                        <h5 class="fw-bold"> @lang('header.total') </h5>
                                                        <h5 class="fw-bold">
                                                            {{ $order->total_price_after_tax }}
                                                            {{ $order->Branch->country->currency_symbol }} </h5>
                                                    </div>
                                                </div>
                                                @if ($currentStatus == 'pending' || $currentStatus == 'in_progress')
                                                    <div id="contactRestaurant-{{ $order->id }}"
                                                        class="bg-dark-gray p-3 my-4 {{ $currentStatus == 'on_way' ? 'd-none' : '' }}">
                                                        <h5 class="fw-bold"> @lang('order.contactRestaurant') </h5>
                                                        <div class="d-flex justify-content-between">
                                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/small-logo.svg') }}"
                                                                alt="" />
                                                            <div class="contact-icons">
                                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/whatsapp-icon.svg') }}"
                                                                    alt="" />
                                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/call.svg') }}"
                                                                    alt="" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div id="contactDriver-{{ $order->id }}"
                                                    class="bg-dark-gray p-3 my-4 {{ $currentStatus == 'on_way' ? '' : 'd-none' }}">
                                                    <h5 class="fw-bold mb-3">@lang('order.contactDriver')</h5>
                                                    <div class="d-flex justify-content-between">
                                                        <div class="courier-content">
                                                            <img class="contact-img"
                                                                src="{{ $order->delivery && $order->delivery?->image ? asset($order->delivery?->image) : asset('front/AlKout-Resturant/SiteAssets/images/delivery-man.png') }}"
                                                                alt="" />
                                                            <span
                                                                class="mx-2">{{ $order->delivery?->first_name . ' ' . $order->delivery?->last_name }}</span>
                                                        </div>
                                                        <div class="contact-icons">
                                                            <button class="btn bg-success rounded-pill"
                                                                onclick="startDriverChat({{ $order->id }}, '{{ $order->delivery?->first_name }} {{ $order->delivery?->last_name }}', '{{ asset($order->delivery?->image ?? 'front/AlKout-Resturant/SiteAssets/images/delivery-man.png') }}')">

                                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/chat-icon.svg') }}"
                                                                    alt="">
                                                                <span>@lang('order.startConversation')</span>
                                                            </button>
                                                            <button class="call btn rounded-pill"
                                                                data-bs-target="#captainContact-{{ $order->id }}"
                                                                data-bs-toggle="modal">
                                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/circle-call.svg') }}"
                                                                    alt="" />
                                                                <span>@lang('order.contactDriver')</span>
                                                            </button>
                                                            <div class="modal fade"
                                                                id="captainContact-{{ $order->id }}"
                                                                aria-labelledby="captainContactModalLabel"
                                                                aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header border-0">
                                                                            <button type="button" class="btn btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body text-center">
                                                                            <div class="d-flex justify-content-center">
                                                                                <img class="done-img"
                                                                                    src="{{ asset('front/AlKout-Resturant/SiteAssets/images/contact-icon.svg') }}"
                                                                                    alt="contact us">
                                                                            </div>
                                                                            <h4 class="mt-2">@lang('order.callDriver')</h4>
                                                                        </div>
                                                                        <div
                                                                            class="modal-footer d-flex justify-content-between border-0">
                                                                            <p class="fw-bold main-color">
                                                                                {{ $order->delivery?->phone_number }}</p>
                                                                            <div style="cursor: pointer;"
                                                                                onclick="copyToClipboard('{{ $order->delivery?->phone_number }}')">
                                                                                <span>@lang('order.copy')</span>
                                                                                <img class="small-done"
                                                                                    src="{{ asset('front/AlKout-Resturant/SiteAssets/images/copy.svg') }}"
                                                                                    alt="" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Floating Chat Button -->
                                        <button class="chat-button d-none" id="driverChatButton"
                                            onclick="openDriverChat({{ $order->id }}, '{{ $order->delivery?->first_name }} {{ $order->delivery?->last_name }}', '{{ asset($order->delivery?->image ?? 'https://erpsystem.testdomain100.online/images/employees/profile-default.png') }}')">
                                            <img src="" alt="Driver" class="user-icon"
                                                id="driverChatButtonImage" />
                                            {{-- <span class="chat-close-btn">&times;</span> --}}
                                        </button>
                                    @endif
                                @endforeach
                            @endif
                            @if (!$hasOrders)
                                <div class="card p-5 w-50 text-center mx-auto mt-5">
                                    <img class="noAddress-img"
                                        src="{{ asset('front/AlKout-Resturant/SiteAssets/images/mdi_file-location.png') }}"
                                        alt="" />
                                    <h4 class="my-4 fw-bold">@lang('auth.noorders')</h4>
                                </div>
                            @endif

                        </div>
                        <div class="tab-pane fade {{ !$hasDeliveryOrders && $hasTakeawayOrders ? 'show active' : '' }}"
                            id="takeaway" role="tabpanel" aria-labelledby="takeaway-tab">
                            @if ($orders)
                                @foreach ($orders as $order)
                                    @if ($order->type === 'Takeaway')
                                        @php
                                            $hasOrders = true;
                                            $trackingStatuses = $order->tracking->pluck('order_status')->toArray();
                                            $currentStatus = last($trackingStatuses);
                                        @endphp
                                        <div id="order-{{ $order->id }}" class="card mt-2 p-3"
                                            data-current-order="{{ $order->id }}">
                                            <div class="card-header bg-white">
                                                <div class="d-flex justify-content-between">
                                                    <h5 class="card-title fw-bold mb-3">@lang('header.orderstatus')</h5>
                                                    @php
                                                        $lastTrackingStatus = $order->tracking->last()?->order_status;
                                                    @endphp
                                                    @if ($order->status === 'pending')
                                                        <button
                                                            class="btn reversed main-color d-flex fw-bold cancel-order-btn"
                                                            data-order-id="{{ $order->id }}">
                                                            @lang('header.cancelorder')
                                                        </button>
                                                    @endif
                                                </div>
                                                @lang('header.ordernum'){{ $order->order_number }}
                                                </h5>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="wizard my-3">
                                                    <ul class="nav nav-tabs justify-content-between w-100 timeline"
                                                        id="myTab" role="tablist">
                                                        @foreach ($takeawayStatuses as $key => $status)
                                                            @php
                                                                $isCompleted = in_array($key, $trackingStatuses);

                                                                $isActive = $key === $currentStatus;
                                                            @endphp
                                                            <li class="nav-item" role="presentation">
                                                                <a class="nav-link rounded-circle d-flex align-items-center justify-content-center
                                                            {{ $isActive ? 'active' : '' }}
                                                            {{ $isCompleted && !$isActive ? 'completed' : '' }}
                                                            {{ !$isCompleted && !$isActive ? 'disabled' : '' }}"
                                                                    href="javascript:void(0);"
                                                                    id="step{{ $loop->index }}-tab{{ $order->id }}"
                                                                    data-status="{{ $key }}" role="tab">
                                                                    <i class="fas {{ $status['icon'] }}"></i>
                                                                </a>
                                                                <span class="d-block mt-2">{{ $status['label'] }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold">@lang('header.orderSummary') </h5>
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold"> <i
                                                            class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                        @lang('header.pickupFrom')
                                                    </h5>
                                                    <p class="fw-bold mb-0">
                                                        <span><i class="fas fa-map-marker-alt ms-2 main-color"></i></span>
                                                        {{ app()->getLocale() === 'ar' ? $order->branch->address_ar : $order->branch->address_en }}
                                                    </p>
                                                    <p class="text-muted">
                                                        {{ app()->getLocale() === 'ar' ? $order->branch->name_ar : $order->branch->name_en }}
                                                    </p>
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold"> <i
                                                            class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>@lang('header.paymentinfo')
                                                    </h5>
                                                    @if ($order->orderTransactions->isNotEmpty())
                                                        @foreach ($order->orderTransactions as $transaction)
                                                            <p><strong>
                                                                    @lang('header.paymentmethod')
                                                                    @switch($transaction->payment_method)
                                                                        @case('cash')
                                                                            <span class="p-1 bg-success text-light rounded">
                                                                                @lang('header.cash') </span>
                                                                        @break

                                                                        @case('credit_card')
                                                                            <span class="p-1 bg-success text-light rounded">
                                                                                @lang('header.credit_card') </span>
                                                                        @break

                                                                        @case('online')
                                                                            <span class="p-1 bg-success text-light rounded">
                                                                                @lang('header.online') </span>
                                                                        @break

                                                                        @default
                                                                            <span class="p-1 bg-success text-light rounded">
                                                                                @lang('header.cash') </span>
                                                                    @endswitch
                                                                </strong>
                                                            </p>
                                                        @endforeach
                                                    @else
                                                        <p>
                                                            @lang('header.nopaymentmethod')
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold">
                                                        <i class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                        @lang('header.orderdetails')
                                                    </h5>
                                                    @foreach ($order->orderDetails as $detail)
                                                        @php
                                                            $addons = $order->orderAddons->filter(
                                                                fn($addon) => $addon->order_details_id == $detail->id &&
                                                                    $addon->Addon?->addons?->name_ar,
                                                            );
                                                        @endphp
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="p-1">{{ $detail->quantity }}</span>
                                                                <span class="mx-1">x</span>
                                                                <span>{{ app()->getLocale() === 'ar' ? $detail->dish?->name_ar : $detail->dish?->name_en }}</span>
                                                                @if ($detail->dishSize)
                                                                    <span>({{ app()->getLocale() === 'ar' ? $detail->dishSize->size_name_ar : $detail->dishSize->size_name_en }})</span>
                                                                @endif
                                                            </div>
                                                            <p class="mb-0 fw-bold">
                                                                @if ($order->tax_application == 0)
                                                                    {{ $detail->price_befor_tax + $addons->sum('price_before_tax') }}
                                                                @else
                                                                    {{ $detail->price_after_tax + $addons->sum('price_after_tax') }}
                                                                @endif
                                                                {{ $order->Branch->country->currency_symbol }}
                                                            </p>
                                                        </div>
                                                        @if ($addons->isNotEmpty())
                                                            <div class="text-muted">
                                                                <small>
                                                                    {{ $addons->map(fn($addon) => app()->getLocale() === 'ar' ? $addon->Addon->addons->name_ar : $addon->Addon->addons->name_en)->implode(', ') }}
                                                                </small>
                                                            </div>
                                                        @endif
                                                        @if ($detail->note)
                                                            <div class="text-muted">
                                                                <small>@lang('order.notes'):{{ $detail->note }}</small>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                    @if ($order->note)
                                                        <div>
                                                            <span>@lang('order.orderNote'):</span>
                                                            <span>{{ $order->note }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold"> <i
                                                            class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                        @lang('header.reset')
                                                    </h5>
                                                    <div class="d-flex justify-content-between">
                                                        <p>@lang('header.totalorder') </p>
                                                        <p class="mb-0 fw-bold">
                                                            @php
                                                                $basePrice = $order->total_price_befor_tax;
                                                                $couponDiscount = 0;
                                                                if ($order->coupon_id) {
                                                                    if ($order->coupon->type === 'percentage') {
                                                                        $couponDiscount =
                                                                            ($basePrice * $order->coupon->value) / 100;
                                                                    } elseif ($order->coupon->type === 'fixed') {
                                                                        $couponDiscount = $order->coupon->value;
                                                                    }
                                                                }
                                                                $subTotalPrice = $basePrice + $couponDiscount;
                                                                if ($order->tax_application == 1) {
                                                                    $subTotalPrice += $order->tax_value;
                                                                }
                                                            @endphp
                                                            {{ $subTotalPrice }}
                                                            {{ $order->Branch->country->currency_symbol }}
                                                        </p>
                                                    </div>
                                                    @if ($order->coupon_id)
                                                        <div class="d-flex justify-content-between">
                                                            <p class="main-color"> @lang('header.coupon') </p>
                                                            @if ($order->coupon->type === 'percentage')
                                                                <p class="main-color">
                                                                    -{{ ($order->total_price_befor_tax * $order->coupon->value) / 100 }}
                                                                    {{ $order->Branch->country->currency_symbol }} </p>
                                                            @elseif ($order->coupon->type === 'fixed')
                                                                <p class="main-color">
                                                                    -{{ $order->coupon->value }}
                                                                    {{ $order->Branch->country->currency_symbol }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endif
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
                                                            <p class="mb-0">@lang('header.notincludefees')
                                                            </p>
                                                        </div>
                                                    @endif
                                                    <div class="d-flex justify-content-between border-top pt-2">
                                                        <h5 class="fw-bold"> @lang('header.total') </h5>
                                                        <h5 class="fw-bold">{{ $order->total_price_after_tax }}
                                                            {{ $order->Branch->country->currency_symbol }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                            @if (!$hasOrders)
                                <div class="card p-5 w-50 text-center mx-auto mt-5">
                                    <img class="noAddress-img"
                                        src="{{ asset('front/AlKout-Resturant/SiteAssets/images/mdi_file-location.png') }}"
                                        alt="" />
                                    <h4 class="my-4 fw-bold">@lang('auth.noorders')</h4>
                                </div>
                            @endif
                        </div>
                        <!-- Reservations Tab -->
                        <div class="tab-pane fade {{ !$hasDeliveryOrders && !$hasTakeawayOrders && $hasReservations ? 'show active' : '' }}"
                            id="reservations" role="tabpanel" aria-labelledby="reservations-tab">
                            @if ($reservations)
                                @foreach ($reservations as $reservation)
                                    <div id="reservation-{{ $reservation->id }}" class="card mt-2 p-3">
                                        <div class="card-header bg-white">
                                            <div class="d-flex justify-content-between">

                                                <h5 class="card-title fw-bold mb-3">@lang('header.orderstatus')</h5>
                                                <button
                                                    class="btn reversed main-color d-flex fw-bold cancel-reservation-btn"
                                                    data-reservation-id="{{ $reservation->id }}">
                                                    @lang('header.cancelreservation')
                                                </button>
                                            </div>
                                            <h6 class="mb-3">@lang('order.arrivingTime')</h6>
                                            <h5 class="main-color fw-bold mb-3 d-flex flex-wrap">
                                                <span
                                                    class="to-time">{{ $reservation->time_from ? $reservation->time_from->format('h:i') : 'N/A' }}
                                                    @lang($reservation->time_from->format('A') === 'AM' ? 'header.am' : 'header.pm')</span>
                                            </h5>
                                            <h5 class="p-1 bg-warning text-dark rounded d-inline-block">
                                                {{ $reservation->reservation_number }}</h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class=" d-flex flex-column align-items-center">
                                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/donee.png') }}"
                                                    alt="Done" class="done-img">
                                                <h5 class="fw-bold mt-4">@lang('order.reservationConfirmed')</h5>
                                            </div>
                                            <div class="bg-dark-gray p-3 my-4">
                                                <h5 class="fw-bold">@lang('header.orderSummary')</h5>
                                            </div>
                                            <div class="bg-dark-gray p-3 my-4">
                                                <h5 class="fw-bold">
                                                    <i class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                    @lang('header.paymentinfo')
                                                </h5>
                                                @if ($reservation->reservation_type == 'without')
                                                    @if ($reservation->transaction->payment_status == 'unpaid')
                                                        <p>@lang('order.withoutDeposit')</p>
                                                    @elseif($reservation->transaction->payment_status == 'part')
                                                        <div class="d-flex justify-content-between">
                                                            <p>@lang('order.withDeposit')
                                                                <span class="main-color">
                                                                    ({{ $reservation->transaction->paid }}
                                                                    {{ $reservation->branch->country->currency_symbol }})
                                                                </span>
                                                            </p>
                                                            @if ($reservation->transaction->payment_method == 'online')
                                                                <p class="bg-success text-white px-5">@lang('order.paidOnline')
                                                                </p>
                                                            @elseif($reservation->transaction->payment_method == 'cash')
                                                                <p class="bg-success text-white px-5">@lang('order.paidCash')
                                                                </p>
                                                            @elseif($reservation->transaction->payment_method == 'credit_card')
                                                                <p class="bg-success text-white px-5">@lang('order.paidCredit')
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @elseif ($reservation->reservation_type == 'with')
                                                    @if ($reservation->order->transaction->payment_status == 'unpaid')
                                                        <p>@lang('order.withoutDeposit')</p>
                                                    @elseif($reservation->order->transaction->payment_status == 'part')
                                                        <div class="d-flex justify-content-between">
                                                            <p>@lang('order.withDeposit')
                                                                <span class="main-color">
                                                                    ({{ $reservation->order->transaction->paid }}
                                                                    {{ $reservation->branch->country->currency_symbol }})
                                                                </span>
                                                            </p>
                                                            @if ($reservation->order->transaction->payment_method == 'online')
                                                                <p class="bg-success text-white px-5">@lang('order.paidOnline')
                                                                </p>
                                                            @elseif($reservation->order->transaction->payment_method == 'cash')
                                                                <p class="bg-success text-white px-5">@lang('order.paidCash')
                                                                </p>
                                                            @elseif($reservation->order->transaction->payment_method == 'credit_card')
                                                                <p class="bg-success text-white px-5">@lang('order.paidCredit')
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @elseif($reservation->order->transaction->payment_status == 'paid')
                                                        <div class="d-flex justify-content-between">
                                                            <p>@lang('order.wholeDeposit')
                                                                <span class="main-color">
                                                                    ({{ $reservation->order->transaction->paid }}
                                                                    {{ $reservation->branch->country->currency_symbol }})
                                                                </span>
                                                            </p>
                                                            @if ($reservation->order->transaction->payment_method == 'online')
                                                                <p class="bg-success text-white px-5">@lang('order.paidOnline')
                                                                </p>
                                                            @elseif($reservation->order->transaction->payment_method == 'cash')
                                                                <p class="bg-success text-white px-5">@lang('order.paidCash')
                                                                </p>
                                                            @elseif($reservation->order->transaction->payment_method == 'credit_card')
                                                                <p class="bg-success text-white px-5">@lang('order.paidCredit')
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="bg-dark-gray p-3 my-4">
                                                <h5 class="fw-bold">
                                                    <i class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                    @lang('header.orderdetails')
                                                </h5>
                                                <div class="table-details mt-5 w-50">
                                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-table.svg') }}"
                                                        alt="" />
                                                    <h5 class="fw-bold d-inline">@lang('order.table')
                                                        ({{ $reservation->tables->table_number }})</h5>
                                                    <div>
                                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/clock.svg') }}"
                                                            alt="" />
                                                        <span class="text-muted mb-0">
                                                            <span>
                                                                @lang('order.arrival'):
                                                            </span>
                                                            {{ $reservation->time_from ? $reservation->time_from->format('h:i') : 'N/A' }}
                                                            @lang($reservation->time_from->format('A') === 'AM' ? 'header.am' : 'header.pm') </span>
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
                                                                {{ $reservation->time_to ? $reservation->time_to->format('h:i') : '' }}
                                                                @lang($reservation->time_to ? ($reservation->time_to->format('A') === 'AM' ? 'header.am' : 'header.pm') : 'order.noTime')
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if ($reservation->reservation_type == 'with')
                                                    <div class="order bg-white p-4 my-3">
                                                        @foreach ($reservation->order->orderDetails as $detail)
                                                            @php
                                                                $addons = $reservation->order->orderAddons->filter(
                                                                    fn($addon) => $addon->order_details_id ==
                                                                        $detail->id && $addon->Addon?->addons?->name_ar,
                                                                );
                                                            @endphp
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span class="p-1">{{ $detail->quantity }}</span>
                                                                    <span class="mx-1">x</span>
                                                                    <span>{{ app()->getLocale() === 'ar' ? $detail->dish?->name_ar : $detail->dish?->name_en }}</span>
                                                                    @if ($detail->dishSize)
                                                                        <span>({{ app()->getLocale() === 'ar' ? $detail->dishSize->size_name_ar : $detail->dishSize->size_name_en }})</span>
                                                                    @endif
                                                                </div>
                                                                <p class="mb-0 fw-bold">
                                                                    @if ($reservation->order->tax_application == 0)
                                                                        {{ $detail->price_befor_tax + $addons->sum(fn($addon) => $addon->price_before_tax) }}
                                                                    @else
                                                                        {{ $detail->price_after_tax + $addons->sum(fn($addon) => $addon->price_after_tax) }}
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
                                                            @if ($detail->note)
                                                                <div class="text-muted">
                                                                    <small>@lang('order.notes'):{{ $detail->note }}</small>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            @if ($reservation->reservation_type == 'with')
                                                <div class="bg-dark-gray p-3 my-4">
                                                    <h5 class="fw-bold"> <i
                                                            class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
                                                        @lang('header.reset')
                                                    </h5>
                                                    <div class="d-flex justify-content-between">
                                                        <p>@lang('header.totalorder')</p>
                                                        <p class="mb-0 fw-bold">
                                                            @php
                                                                $basePrice = $reservation->order->total_price_befor_tax;
                                                                $couponDiscount = 0;
                                                                if ($reservation->order->coupon_id) {
                                                                    if (
                                                                        $reservation->order->coupon->type ===
                                                                        'percentage'
                                                                    ) {
                                                                        $couponDiscount =
                                                                            ($basePrice *
                                                                                $reservation->order->coupon->value) /
                                                                            100;
                                                                    } elseif (
                                                                        $reservation->order->coupon->type === 'fixed'
                                                                    ) {
                                                                        $couponDiscount =
                                                                            $reservation->order->coupon->value;
                                                                    }
                                                                }
                                                                $subTotalPrice = $basePrice + $couponDiscount;

                                                                if ($reservation->order->tax_application == 1) {
                                                                    $subTotalPrice += $reservation->order->tax_value;
                                                                }
                                                            @endphp
                                                            {{ $subTotalPrice }}
                                                            {{ $reservation->order->Branch->country->currency_symbol }}
                                                        </p>
                                                    </div>
                                                    @if ($reservation->order->coupon_id)
                                                        <div class="d-flex justify-content-between">
                                                            <p class="main-color"> @lang('header.coupon')</p>
                                                            @if ($reservation->order->coupon->type === 'percentage')
                                                                <p class="main-color">
                                                                    -{{ ($reservation->order->total_price_befor_tax * $order->coupon->value) / 100 }}
                                                                    {{ $reservation->order->Branch->country->currency_symbol }}
                                                                </p>
                                                            @elseif ($reservation->order->coupon->type === 'fixed')
                                                                <p class="main-color">
                                                                    -{{ $reservation->order->coupon->value }}
                                                                    {{ $reservation->order->Branch->country->currency_symbol }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    <div class="d-flex justify-content-between">
                                                        <p> @lang('header.serviceFees')</p>
                                                        <p>{{ $reservation->order->service_fees ?? 0 }}
                                                            {{ $reservation->order->Branch->country->currency_symbol }}
                                                        </p>
                                                    </div>
                                                    @if ($reservation->order->tax_application == 0)
                                                        <div class="d-flex justify-content-between">
                                                            <p class="mb-0">@lang('header.includefees')
                                                                <span class="main-color fw-bold">
                                                                    {{ number_format(($order->tax_value / $order->total_price_befor_tax) * 100, 2) . '%' }}
                                                                </span>@lang('header.anotherway')
                                                                {{ $reservation->order->tax_value }}
                                                                {{ $reservation->order->Branch->country->currency_symbol }}
                                                            </p>
                                                        </div>
                                                    @else
                                                        <div class="d-flex justify-content-between">
                                                            <p class="mb-0">@lang('header.notincludefees')
                                                            </p>
                                                        </div>
                                                    @endif
                                                    <div class="d-flex justify-content-between border-top pt-2">
                                                        <h5 class="fw-bold"> @lang('header.total') </h5>
                                                        <h5 class="fw-bold">
                                                            {{ $reservation->order->total_price_after_tax }}
                                                            {{ $reservation->order->Branch->country->currency_symbol }}
                                                        </h5>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="bg-dark-gray p-3 my-4">
                                                <h5 class="fw-bold"> @lang('order.contactRestaurant') </h5>
                                                <div class="d-flex justify-content-between">
                                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/small-logo.svg') }}"
                                                        alt="" />
                                                    <div class="contact-icons">
                                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/whatsapp-icon.svg') }}"
                                                            alt="" />
                                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/call.svg') }}"
                                                            alt="" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @if ($reservations->isEmpty())
                                <div class="card p-5 w-50 text-center mx-auto mt-5">
                                    <img class="noAddress-img"
                                        src="{{ asset('front/AlKout-Resturant/SiteAssets/images/mdi_file-location.png') }}"
                                        alt="" />
                                    <h4 class="my-4 fw-bold">@lang('auth.noorders')</h4>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Single Shared Chat Box (placed outside of the loop) -->
    <div class="chat-box d-none" id="sharedDriverChatBox">
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <div class="user-img">
                    <img id="driverImage" src="" alt="Driver">
                </div>
                <div class="user-content mx-2">
                    <h6 class="fw-bold" id="driverName"></h6>
                    <small class="text-muted">@lang('order.driver')</small>
                </div>
            </div>
            <audio id="newMessageSound" src="{{ asset('front/AlKout-Resturant/SiteAssets/new-message.mp3') }}"
                preload="auto"></audio>
            <div class="header-buttons">
                <button onclick="minimizeDriverChat()">
                    <i class="fas fa-window-minimize main-color"></i>
                </button>
                <button onclick="closeDriverChat()">
                    <i class="fas fa-times-circle main-color fs-3"></i>
                </button>
            </div>
        </div>
        <div class="chat-messages" id="driverChatMessages">
            <div class="bot-message message static-welcome">
                <span>@lang('order.welcomeMsg')</span>
                <img id="driverWelcomeImage" src="" alt="Driver">
            </div>
        </div>
        <div class="chat-input">
            <input type="file" id="driverFileInput" accept="image/*,video/*" style="display: none;">
            <a href="#" class="mx-1" id="driverAttachFile">
                <img class="chat-icon" src="{{ asset('front/AlKout-Resturant/SiteAssets/images/paperclip.svg') }}"
                    alt="" />
            </a>
            <div class="emoji-picker-container">
                <a type="button" class="mx-1 emoji-btn" id="driverEmojiBtn">
                    <img class="chat-icon" src="{{ asset('front/AlKout-Resturant/SiteAssets/images/emoji.svg') }}"
                        alt="@lang('chat.emoji')" />
                </a>
                <emoji-picker id="driverEmojiPicker" style="display: none;"></emoji-picker>
            </div>
            <a href="#" class="mx-1" id="driverSend">
                <img class="chat-icon" src="{{ asset('front/AlKout-Resturant/SiteAssets/images/send.svg') }}"
                    alt="" />
            </a>
            <input type="text" id="driverChatInput" class="form-control" placeholder="@lang('order.writeMsg')">
        </div>
        <div id="driverMediaPreview" class="mt-2" style="display: none;">
            <div class="d-flex align-items-center">
                <img id="driverPreviewImage" src="" style="max-height: 100px; display: none;">
                <video id="driverPreviewVideo" controls style="max-height: 100px; display: none;"></video>
                <button id="driverRemoveMedia" class="btn btn-sm btn-danger ms-2" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@include('website.cart.global')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>


@include('website.track_order.chatDelivery')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabList = document.querySelectorAll('#orderTabs .nav-link');
        const tabContent = document.querySelectorAll('#orderTabsContent .tab-pane');

        function activateTab(tabId) {
            tabList.forEach(tab => tab.classList.remove('active'));
            tabContent.forEach(pane => pane.classList.remove('show', 'active'));

            const targetTab = document.querySelector(`#${tabId}-tab`);
            const targetPane = document.querySelector(`#${tabId}`);
            if (targetTab && targetPane) {
                targetTab.classList.add('active');
                targetPane.classList.add('show', 'active');
            }
        }

        tabList.forEach(tab => {
            tab.addEventListener('click', function(event) {
                event.preventDefault();
                const targetId = this.getAttribute('data-bs-target').replace('#', '');
                activateTab(targetId);
            });
        });

        @if ($hasDeliveryOrders)
            activateTab('delivery');
        @elseif ($hasTakeawayOrders)
            activateTab('takeaway');
        @elseif ($hasReservations)
            activateTab('reservations');
        @endif

        if (getCookie('backButton') === "false") {
            localStorage.removeItem('takeaway_details');
            localStorage.removeItem('TableReservation');
            localStorage.removeItem('cart');
            updateCartCount();
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (getCookie('backButton') == "false") {
            localStorage.removeItem('takeaway_details');
            localStorage.removeItem('TableReservation');
            localStorage.removeItem('cart');
            updateCartCount();
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cancelOrderButtons = document.querySelectorAll('.cancel-order-btn');

        cancelOrderButtons.forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');

                fetch('{{ route('order.validateCancellation') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            order_id: orderId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status !== 'success') {
                            Swal.fire('@lang('validation.notAllowed')', data.message, 'error')
                            return;
                        }

                        Swal.fire({
                            title: '@lang('validation.sure')',
                            text: '@lang('validation.sureCancel')',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: '@lang('validation.yesCancel')',
                            cancelButtonText: '@lang('validation.noKeep')'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch('{{ route('order.cancel') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document
                                                .querySelector(
                                                    'meta[name="csrf-token"]')
                                                .getAttribute('content')
                                        },
                                        body: JSON.stringify({
                                            order_id: orderId
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            Swal.fire('@lang('validation.cancelled')',
                                                data
                                                .message, 'success').then(
                                                () => {
                                                    location.reload();
                                                });
                                        } else {
                                            Swal.fire('@lang('validation.notAllowed')',
                                                data
                                                .message, 'error');
                                        }
                                    })
                                    .catch(error => {
                                        Swal.fire('@lang('validation.notAllowed')',
                                            '@lang('validation.cantCancel')', 'error');
                                    });
                            }
                        });
                    })
                    .catch(error => {
                        Swal.fire('@lang('Error!')', '@lang('Something went wrong')', 'error');
                    });
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cancelReservationButtons = document.querySelectorAll('.cancel-reservation-btn');

        cancelReservationButtons.forEach(button => {
            button.addEventListener('click', function() {
                const reservationId = this.getAttribute('data-reservation-id');

                fetch('{{ route('order.validateReservationCancellation') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            reservation_id: reservationId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status !== 'success') {
                            Swal.fire('@lang('validation.notAllowed')', data.message, 'error').then(
                                () => {
                                    location.reload();
                                });
                            return;
                        }

                        // Build confirmation message based on cancellation details
                        let confirmationMessage = data.details.message;

                        if (data.details.will_charge) {
                            confirmationMessage += '<br><br>' +
                                '@lang('validation.chargeApplied')' +
                                ' ' + data.details.charge_percentage + '%';
                        }

                        if (!data.details.is_within_window) {
                            confirmationMessage += '<br><br>' +
                                '@lang('validation.lateCancellationWarning')';
                        }

                        Swal.fire({
                            title: '@lang('validation.sure')',
                            html: confirmationMessage,
                            icon: data.details.is_within_window ? 'question' :
                                'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: '@lang('validation.yesCancel')',
                            cancelButtonText: '@lang('validation.noKeep')'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch('{{ route('order.cancelReservation') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document
                                                .querySelector(
                                                    'meta[name="csrf-token"]')
                                                .getAttribute('content')
                                        },
                                        body: JSON.stringify({
                                            reservation_id: reservationId
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            let resultMessage = data.message;

                                            if (data.details.will_charge) {
                                                resultMessage += '<br><br>' +
                                                    '@lang('validation.amountDeducted')' +
                                                    ' ' + data.details
                                                    .charge_percentage + '%';
                                            }

                                            Swal.fire({
                                                title: '@lang('validation.cancelled')',
                                                html: resultMessage,
                                                icon: 'success'
                                            }).then(() => {
                                                location.reload();
                                            });
                                        } else {
                                            Swal.fire('@lang('validation.notAllowed')',
                                                data.message, 'error');
                                        }
                                    })
                                    .catch(error => {
                                        Swal.fire('@lang('no no')',
                                            '@lang('validation.cantCancel')', 'error');
                                    });
                            }
                        });
                    })
                    .catch(error => {
                        Swal.fire('@lang('Error!')', '@lang('Something went wrong')', 'error');
                    });
            });
        });
    });
    // ✅ Only run this on the success page
    localStorage.removeItem('takeaway_details');
    localStorage.removeItem('TableReservation');
    localStorage.removeItem('cart');
    document.cookie = "backButton=false; path=/;";
    console.log('Cart and session data cleared after order success.');
</script>
