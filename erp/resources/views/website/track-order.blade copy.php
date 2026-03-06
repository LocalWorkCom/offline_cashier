@extends('website.layouts.master')
<style>
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
</style>

@section('content')
    {{-- <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body register" id="registerBody">
                    <div class="row justify-content-center text-center">
                        <div class="col-12">
                            <i class="fas fa-gift text-warning fs-1 my-3"></i>
                            <h2 class="fw-bold main-color"> @lang('header.surprise')</h2>
                            <h5 class="text-muted">@lang('header.getcopun')</h5>
                            <h3 class="fw-bold text-dark">@lang('header.isdelivered')</h3>
                            <div class="d-flex justify-content-center py-4">
                                <a href="#" class="btn mx-1 w-50" data-bs-target="#exampleModalToggle2"
                                    data-bs-toggle="modal" data-bs-dismiss="modal"> @lang('header.yes')</a>
                                <a href="#" class="btn-no-modal mx-1 w-50" data-bs-dismiss="modal" aria-label="Close">
                                    @lang('header.no')</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    {{-- <div class="modal fade" id="exampleModalToggle2" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2"
        tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-close text-light" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body register" id="registerBody">
                    <div class="row justify-content-center text-center">
                        <div class="col-12">
                            <i class="fas fa-gift text-warning fs-1 my-3"></i>
                            <h2 class="fw-bold main-color"> @lang('header.congrate')</h2>
                            <h5 class="text-muted"> @lang('header.getcopon')</h5>
                            <h4 class="fw-bold">#58768467 </h4>
                            <div class="d-flex justify-content-center py-4">
                                <a href="#" class="btn mx-1 w-100"> @lang('header.copy')</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

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
                    @endphp
                    @php
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

                        // Define statuses for takeaway orders
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
                    @if ($orders)
                        @foreach ($orders as $order)
                            @php
                                // Fetch the statuses from the tracking table
                                $trackingStatuses = $order->tracking->pluck('order_status')->toArray();
                                $currentStatus = last($trackingStatuses);
                            @endphp

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
                                                @if (now()->diffInMinutes($order->created_at) <= $order->branch->time_cancellation)
                                                    <button class="btn reversed main-color d-flex fw-bold cancel-order-btn"
                                                        data-order-id="{{ $order->id }}">
                                                        @lang('header.cancelorder')
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                        @php
                                            $lastTracking = $order->tracking->last();

                                            $orderCreationTime = $order->created_at;

                                            $fromTime = $orderCreationTime
                                                ? \Carbon\Carbon::parse($orderCreationTime)
                                                : null;

                                            $deliveryTime = getBranchSettings($order->branch_id, 'delivery_time');
                                            // $bufferTime = 15;

                                            $toTime = $fromTime
                                                ? $fromTime->copy()->addMinutes($deliveryTime) //+ $bufferTime)
                                                : null;

                                            $estimatedMinutes = $lastTracking?->time;
                                        @endphp

                                        <h6 class="mb-3"> @lang('header.deliverytimeexpect')</h6>
                                        <h5 class="main-color fw-bold mb-3 d-flex flex-wrap">
                                            <span class="to-time">
                                                {{ $fromTime ? $fromTime->format('h:i') : 'N/A' }}
                                                @lang($fromTime && $fromTime->format('A') === 'AM' ? 'header.am' : 'header.pm') -
                                            </span>
                                            <span class="from-time">
                                                {{ $toTime ? $toTime->format('h:i') : 'N/A' }}
                                                @lang($toTime && $toTime->format('A') === 'AM' ? 'header.am' : 'header.pm')
                                            </span>
                                        </h5>

                                        <h5 class="p-1 bg-warning text-dark rounded d-inline-block">
                                            @lang('header.ordernum') {{ $order->order_number }}
                                        </h5>
                                    </div>

                                    <div class="card-body p-0">
                                        <div class="wizard my-3">


                                            {{-- code for websockit update dynamic --}}
                                            @php
                                                // Fetch the statuses from the tracking table
                                                $trackingStatuses = $order->tracking->pluck('order_status')->toArray();
                                                $currentStatus = last($trackingStatuses);
                                            @endphp
                                            <ul class="nav nav-tabs justify-content-between w-100 timeline" id="myTab"
                                                role="tablist">
                                                @foreach ($deliveryStatuses as $key => $status)
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
                                            <h5 class="fw-bold"> @lang('header.orderSummary')</h5>
                                        </div>
                                        <div class="bg-dark-gray p-3 my-4">
                                            <h5 class="fw-bold"> <i class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
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
                                            <p><strong> @lang('header.name') :</strong> {{ $order->client?->name }}</p>
                                        </div>
                                        <div class="bg-dark-gray p-3 my-4">
                                            <h5 class="fw-bold"> <i class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
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
                                            <h5 class="fw-bold"> <i class="fas fa-file-alt main-color fa-xs fs-5 mx-1"></i>
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
                                            <div class="d-flex justify-content-between">
                                                <p> @lang('header.serviceFees')</p>
                                                <p>{{ $order->service_fees ?? 0 }}
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
                                        @if ($currentStatus == 'pending' || $currentStatus == 'in-progress')
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
                                        @endif
                                        @if ($currentStatus == 'on_way')
                                            <div class="bg-dark-gray p-3 my-4">
                                                <h5 class="fw-bold mb-3">@lang('order.contactDriver')</h5>
                                                <div class="d-flex justify-content-between">
                                                    <div class="courier-content">
                                                        <img class="contact-img"
                                                            src="{{ $order->delivery && $order->delivery->image ? asset($order->delivery->image) : asset('front/AlKout-Resturant/SiteAssets/images/delivery-man.png') }}"
                                                            alt="" />
                                                        <span
                                                            class="mx-2">{{ $order->delivery->first_name . ' ' . $order->delivery->last_name }}</span>
                                                    </div>
                                                    <div class="contact-icons">
                                                        <button class="btn bg-success rounded-pill" id="startDriverChat">
                                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/chat-icon.svg') }}"
                                                                alt="">
                                                            <span>@lang('order.startConversation')</span>
                                                        </button>
                                                        <button class="call btn rounded-pill"
                                                            data-bs-target="#captainContact" data-bs-toggle="modal">
                                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/circle-call.svg') }}"
                                                                alt="" />
                                                            <span>@lang('order.contactDriver')</span>
                                                        </button>
                                                        <div class="modal fade" id="captainContact"
                                                            aria-labelledby="captainContactModalLabel" aria-hidden="true">
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
                                                                            {{ $order->delivery->phone_number }}</p>
                                                                        <div style="cursor: pointer;"
                                                                            onclick="copyToClipboard('{{ $order->delivery->phone_number }}')">
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

                                            <!-- Floating Chat Button -->
                                            <button class="chat-button d-none" id="driverChatButton"
                                                onclick="toggleDriverChat()">
                                                <img src="{{ $order->delivery && $order->delivery->image ? asset($order->delivery->image) : asset('front/AlKout-Resturant/SiteAssets/images/delivery-man.png') }}"
                                                    alt="Driver" class="user-icon" />
                                                {{-- <span class="chat-close-btn">&times;</span> --}}
                                            </button>

                                            <!-- Chat Box -->
                                            <div class="chat-box" id="driverChatBox">
                                                <div class="chat-header">
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-img">
                                                            <img src="{{ $order->delivery && $order->delivery->image ? asset($order->delivery->image) : asset('front/AlKout-Resturant/SiteAssets/images/delivery-man.png') }}"
                                                                alt="Driver">
                                                        </div>
                                                        <div class="user-content mx-2">
                                                            <h6 class="fw-bold">
                                                                {{ $order->delivery->first_name . ' ' . $order->delivery->last_name }}
                                                            </h6>
                                                            <small class="text-muted">@lang('order.driver')</small>
                                                        </div>
                                                    </div>
                                                    <audio id="newMessageSound"
                                                        src="{{ asset('front/AlKout-Resturant/SiteAssets/new-message.mp3') }}"
                                                        preload="auto"></audio>
                                                    <div class="header-buttons">
                                                        <button id="minimizeDriver" onclick="minimizeDriverChat()">
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
                                                        <img src="{{ $order->delivery && $order->delivery->image ? asset($order->delivery->image) : asset('front/AlKout-Resturant/SiteAssets/images/delivery-man.png') }}"
                                                            alt="Driver">
                                                    </div>
                                                </div>
                                                <div class="chat-input">
                                                    <input type="file" id="driverFileInput" accept="image/*,video/*"
                                                        style="display: none;">
                                                    <a href="#" class="mx-1" id="driverAttachFile">
                                                        <img class="chat-icon"
                                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/paperclip.svg') }}"
                                                            alt="" />
                                                    </a>
                                                    <div class="emoji-picker-container">
                                                        <a type="button" class="mx-1 emoji-btn" id="driverEmojiBtn">
                                                            <img class="chat-icon"
                                                                src="{{ asset('front/AlKout-Resturant/SiteAssets/images/emoji.svg') }}"
                                                                alt="@lang('chat.emoji')" />
                                                        </a>
                                                        <emoji-picker id="driverEmojiPicker"
                                                            style="display: none;"></emoji-picker>
                                                    </div>
                                                    <a href="#" class="mx-1" id="driverSend">
                                                        <img class="chat-icon"
                                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/send.svg') }}"
                                                            alt="" />
                                                    </a>
                                                    <input type="text" id="driverChatInput" class="form-control"
                                                        placeholder="@lang('order.writeMsg')">
                                                </div>
                                                <div id="driverMediaPreview" class="mt-2" style="display: none;">
                                                    <div class="d-flex align-items-center">
                                                        <img id="driverPreviewImage" src=""
                                                            style="max-height: 100px; display: none;">
                                                        <video id="driverPreviewVideo" controls
                                                            style="max-height: 100px; display: none;"></video>
                                                        <button id="driverRemoveMedia" class="btn btn-sm btn-danger ms-2"
                                                            style="display: none;">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if ($order->type === 'Takeaway')
                                @php $hasOrders = true; @endphp
                                <div id="order-{{ $order->id }}" class="card mt-2 p-3"
                                    data-current-order="{{ $order->id }}">
                                    <div class="card-header bg-white">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="card-title fw-bold mb-3">@lang('header.orderstatus')</h5>
                                            @php
                                                $lastTrackingStatus = $order->tracking->last()?->order_status;
                                            @endphp
                                            @if ($order->status === 'pending')
                                                @if (now()->diffInMinutes($order->created_at) <= $order->branch->time_cancellation)
                                                    <button class="btn reversed main-color d-flex fw-bold cancel-order-btn"
                                                        data-order-id="{{ $order->id }}">
                                                        @lang('header.cancelorder')
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                        @lang('header.ordernum'){{ $order->order_number }}
                                        </h5>
                                    </div>

                                    <div class="card-body p-0">
                                        <div class="wizard my-3">
                                            <ul class="nav nav-tabs justify-content-between w-100 timeline" id="myTab"
                                                role="tablist">
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
                    @if ($reservations)
                        @foreach ($reservations as $reservation)
                            <div id="reservation-{{ $reservation->id }}" class="card mt-2 p-3">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title fw-bold mb-3">@lang('header.orderstatus')</h5>
                                        <button class="btn reversed main-color d-flex fw-bold cancel-reservation-btn"
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
                                                        {{ $reservation->adult }}@lang('order.person')
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
                                        @if ($reservation->reservation_type == 'with')
                                            <div class="order bg-white p-4 my-3">
                                                @foreach ($reservation->order->orderDetails as $detail)
                                                    @php
                                                        $addons = $reservation->order->orderAddons->filter(
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
                                                            if ($reservation->order->coupon->type === 'percentage') {
                                                                $couponDiscount =
                                                                    ($basePrice * $reservation->order->coupon->value) /
                                                                    100;
                                                            } elseif ($reservation->order->coupon->type === 'fixed') {
                                                                $couponDiscount = $reservation->order->coupon->value;
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
                                                    {{ $reservation->order->Branch->country->currency_symbol }} </p>
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
                                                    {{ $reservation->order->Branch->country->currency_symbol }} </h5>
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
                    @if (!$hasOrders && $reservations->isEmpty())
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
    </section>
@endsection

@include('website.cart.global')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
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
                            Swal.fire('@lang('validation.notAllowed')', data.message, 'error').then(
                                () => {
                                    location.reload();
                                });
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
</script>
{{-- @if ($orders && isset($statuses)) --}}
<script>
    // Pass statuses and orders from Blade to JavaScript
    const statuses = {
        Delivery: {!! json_encode(array_keys($deliveryStatuses)) !!}, // Statuses for delivery orders
        Takeaway: {!! json_encode(array_keys($takeawayStatuses)) !!}, // Statuses for takeaway orders
    };

    const orders = {!! json_encode(
        $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'client_id' => $order->client_id,
                    'type' => $order->type,
                ];
            })->toArray(),
    ) !!};

    // Map status IDs to status texts
    const statusMap = {
        1: 'pending',
        2: 'in_progress',
        3: 'on_way',
        4: 'delivered',
        5: 'readyForPickup',
        6: 'completed',

    };
</script>
<script>
    // Function to update the UI based on the new status
    function updateOrderStatusUI(orderId, status, orderStatuses) {
        // Find the timeline for the specific order
        const timeline = document.querySelector(`#order-${orderId} .timeline`);

        if (timeline) {
            // Iterate through timeline items and update their classes
            orderStatuses.forEach((key, index) => {
                const stepElement = timeline.querySelector(`#step${index}-tab${orderId}`);
                if (stepElement) {
                    stepElement.classList.remove('active', 'completed', 'disabled');

                    if (key === status) {
                        stepElement.classList.add('active'); // Current status
                    } else if (orderStatuses.indexOf(key) < orderStatuses.indexOf(status)) {
                        stepElement.classList.add('completed'); // Completed status
                    } else {
                        stepElement.classList.add('disabled'); // Upcoming status
                    }
                }
            });
        }
    }
</script>
<script>
    // const pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
    //     cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
    //     forceTLS: true,
    //     authEndpoint: '/broadcasting/auth',
    //     auth: {
    //         headers: {
    //             'X-CSRF-Token': "{{ csrf_token() }}"
    //         }
    //     }
    // });

    // pusher.connection.bind('connected', function() {
    //     console.log('Pusher connected');
    // });
    // pusher.connection.bind('error', function(error) {
    //     console.error('Pusher error:', error);
    // });

    $(document).ready(function() {
        // Subscribe to channels for each order
        if (orders && orders.length > 0) {
            orders.forEach(order => {
                const channelName =
                    `order-channel-${order.id}-client-${order.client_id}`; // Unique channel name for each order and client
                const channel = pusher.subscribe(channelName);

                channel.bind('order-status-update', function(data) {
                    console.log('Order #' + data.orderId + ' status changed to: ' + data
                        .status);
                    const statusText = statusMap[data.status]; // Map status ID to text
                    console.log('Mapped status text: ', statusText);

                    // Determine which statuses to use based on order type
                    const orderStatuses = statuses[order.type];
                    updateOrderStatusUI(order.id, statusText, orderStatuses);
                });
            });
        }
        let selectedFile = null;
        let pusherChannel = null;
        let chatChannelId = localStorage.getItem('driverChatChannelId'); // Persist channel ID
        let unreadMessages = 0;
        let isUserInteracted = false;
        const isChatButtonVisible = localStorage.getItem('driverChatButtonVisible') === 'true';
        const chatButton = $('#driverChatButton');
        const chatBox = $('#driverChatBox');

        // Initialize visibility
        if (isChatButtonVisible) {
            chatButton.removeClass('d-none').addClass('visible minimized');
            if (chatChannelId) {
                setupPusherChannel(); // Re-subscribe to existing channel
                loadChatHistory(); // Load history immediately if visible
            }
        } else {
            chatButton.addClass('d-none').removeClass('visible minimized');
        }

        // Start chat button handler
        $('#startDriverChat').on('click', function(event) {
            initDriverChat(event);
        });

        // Close button handler on the chat circle
        $('#driverChatButton .chat-close-btn').on('click', function(e) {
            e.stopPropagation();
            closeDriverChat();
        });

        function initDriverChat(event) {
            event.stopPropagation();
            console.log('initDriverChat called');
            const currentOrderElement = document.querySelector('[data-current-order]');
            if (!currentOrderElement) {
                console.error('No current order element found');
                alert('No active order found');
                return;
            }

            const driverId = currentOrderElement.dataset.driverId;
            chatChannelId = localStorage.getItem('driverChatChannelId') || currentOrderElement.dataset
                .channelId;

            if (!driverId) {
                console.error('No driver ID found');
                alert("@lang('header.getcopun')");
                return;
            }

            console.log('Driver ID:', driverId, 'Chat Channel ID:', chatChannelId);

            if (chatChannelId) {
                console.log('Using existing channel:', chatChannelId);
                setupPusherChannel();
                toggleDriverChat();
            } else {
                console.log('Creating new channel');
                $.ajax({
                    url: "{{ route('chat.sender') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        receiver: driverId,
                        guard_type: 'driver',
                        type: 'web'
                    },
                    success: function(response) {
                        console.log('AJAX Success:', response);
                        if (response.status === 'success') {
                            chatChannelId = response.data.channel_id;
                            localStorage.setItem('driverChatChannelId',
                                chatChannelId); // Persist channel ID
                            currentOrderElement.dataset.channelId = chatChannelId;
                            setupPusherChannel();
                            toggleDriverChat();
                        } else {
                            console.error('Failed to start chat:', response.message);
                            alert('Failed to start chat: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX Error:', xhr.responseText);
                        alert('Failed to start chat: ' + (xhr.responseJSON?.message ||
                            'Server error'));
                    }
                });
            }
        }

        function setupPusherChannel() {
            if (!chatChannelId) {
                console.error('No chat channel ID');
                return;
            }

            if (pusherChannel && pusherChannel.name !== 'chat-' + chatChannelId) {
                pusherChannel.unbind_all();
                pusher.unsubscribe(pusherChannel.name);
                pusherChannel = null;
            }

            if (!pusherChannel) {
                console.log('Subscribing to channel: chat-' + chatChannelId);
                pusherChannel = pusher.subscribe('chat-' + chatChannelId);

                pusherChannel.bind('chatMessage', function(data) {
                    console.log('New message received:', data);
                    const isMe = data.sender.id == "{{ auth('client')->user()->id }}";
                    if (isMe) return;
                    renderMessage(data, isMe);
                    scrollToBottom();

                    if (!$('#driverChatBox').hasClass('show')) {
                        unreadMessages++;
                        updateUnreadBadge();
                        if (isUserInteracted) playNotificationSound();
                    }
                });

                pusherChannel.bind('pusher:subscription_succeeded', function() {
                    console.log('Subscription to chat-' + chatChannelId + ' succeeded');
                });
            }
        }

        function loadChatHistory() {
            if (!chatChannelId) {
                console.error('No chat channel ID to load history');
                return;
            }
            let url = "{{ route('chat.messages', ['channel_id' => ':id']) }}".replace(':id', chatChannelId);

            console.log('Loading chat history for channel:', chatChannelId);
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    channel_id: chatChannelId
                },
                success: function(response) {
                    console.log('Chat history response:', response);

                    const messages = response.data.messages;

                    $('#driverChatMessages .message:not(.static-welcome)').remove();

                    const clientImage =
                        "{{ auth('client')->user()->image ? asset(auth('client')->user()->image) : asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}";
                    const driverImage = document.querySelector('[data-current-order]').dataset
                        .driverImage ||
                        "{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}";

                    messages.forEach(message => {
                        const isMe = message.sender_id ==
                            "{{ auth('client')->user()->id }}";
                        renderMessage({
                            sender: {
                                id: message.sender_id,
                                image: isMe ? clientImage : driverImage
                            },
                            message: message.media || message.message,
                            type: message.media ? (message.media.includes(
                                'images') ? 'image' : 'video') : 'text'
                        }, isMe, false);
                    });

                    scrollToBottom();
                },
                error: function(xhr) {
                    console.error('Failed to load chat history:', xhr.responseText);
                }
            });
        }

        window.toggleDriverChat = function() {
            console.log('Toggling driver chat');
            if (chatBox.hasClass('show')) {
                minimizeDriverChat();
            } else {
                chatBox.addClass('show');
                chatButton.removeClass('d-none').removeClass('minimized').addClass('visible');
                chatButton.removeClass('has-new-message');
                localStorage.setItem('driverChatButtonVisible', 'true');
                if (!chatChannelId) {
                    initDriverChat(new Event('toggle'));
                } else {
                    setupPusherChannel();
                    loadChatHistory();
                }
                scrollToBottom();
                if (unreadMessages > 0) markMessagesAsRead();
            }
        };

        window.minimizeDriverChat = function() {
            chatBox.removeClass('show');
            chatButton.removeClass('d-none').addClass('visible minimized');
            localStorage.setItem('driverChatButtonVisible', 'true');
        };

        window.closeDriverChat = function() {
            chatBox.removeClass('show');
            chatButton.addClass('d-none').removeClass('visible minimized');
            localStorage.setItem('driverChatButtonVisible', 'false');
            // Optional: Uncomment to clear channel ID on close
            // localStorage.removeItem('driverChatChannelId');
            // chatChannelId = null;
        };

        $('#driverAttachFile').click(function() {
            $('#driverFileInput').click();
        });

        $('#driverFileInput').change(function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime'];
            if (!validTypes.includes(file.type)) {
                alert('Only images (JPEG, PNG, GIF) and videos (MP4, MOV) are allowed');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert('File size should be less than 10MB');
                return;
            }

            selectedFile = file;
            showMediaPreview(file);
        });

        $('#driverRemoveMedia').click(function() {
            selectedFile = null;
            $('#driverFileInput').val('');
            $('#driverMediaPreview').hide();
            $('#driverPreviewImage').attr('src', '').hide();
            $('#driverPreviewVideo').attr('src', '').hide();
            $(this).hide();
        });

        $('#driverEmojiBtn').click(function(e) {
            e.stopPropagation();
            $('#driverEmojiPicker').toggle();
        });

        document.querySelector('#driverEmojiPicker')?.addEventListener('emoji-click', event => {
            const currentInput = $('#driverChatInput').val();
            $('#driverChatInput').val(currentInput + event.detail.unicode);
            $('#driverEmojiPicker').hide();
        });

        $('#driverSend').click(sendMessage);
        $('#driverChatInput').keypress(function(e) {
            if (e.which == 13) sendMessage();
        });

        $('#driverChatBox, #driverChatButton').on('click', function() {
            if (!isUserInteracted) isUserInteracted = true;
        });

        function sendMessage() {
            const message = $('#driverChatInput').val().trim();
            if (!message && !selectedFile) return;

            const formData = new FormData();
            formData.append('_token', "{{ csrf_token() }}");
            if (message) formData.append('message', message);
            if (selectedFile) formData.append('media', selectedFile);
            formData.append('channel_id', chatChannelId);
            formData.append('guard_type', 'driver');
            formData.append('type', 'web');

            if (message && !selectedFile) {
                const clientImage =
                    "{{ auth('client')->user()->image ? asset(auth('client')->user()->image) : asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}";
                renderMessage({
                    sender: {
                        id: "{{ auth('client')->user()->id }}",
                        image: clientImage
                    },
                    message: message,
                    type: 'text'
                }, true);
                $('#driverChatInput').val('');
                scrollToBottom();
            }

            $.ajax({
                url: "{{ route('chat.sender') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success' && selectedFile) {
                        selectedFile = null;
                        $('#driverMediaPreview').hide();
                        $('#driverFileInput').val('');
                        $('#driverChatInput').val('');
                    }
                },
                error: function(xhr) {
                    console.error('Failed to send message:', xhr.responseText);
                    alert('Failed to send message: ' + (xhr.responseJSON?.message ||
                        'Server error'));
                }
            });
        }

        function renderMessage(data, isMe, animate = true) {
            console.log('Rendering message:', data);
            let messageContent;
            const defaultImage = "{{ asset('front/AlKout-Resturant/SiteAssets/images/user.svg') }}";
            const senderImage = data.sender.image || defaultImage;

            if (data.type === 'text' && data.message) {
                messageContent = `<span>${data.message}</span>`;
            } else if (data.type === 'image') {
                messageContent =
                    `<img src="${data.message}" alt="image" style="max-width: 200px; max-height: 200px;">`;
            } else if (data.type === 'video') {
                messageContent =
                    `<video controls style="max-width: 200px; max-height: 200px;"><source src="${data.message}" type="video/mp4"></video>`;
            } else {
                console.log('No valid message content to render');
                return;
            }

            const messageHtml = isMe ?
                `<div class="user-message message">${messageContent}<img src="${senderImage}" alt="You" onerror="this.src='${defaultImage}';"></div>` :
                `<div class="bot-message message">${messageContent}<img src="${senderImage}" alt="Driver" onerror="this.src='${defaultImage}';"></div>`;

            const $message = $(messageHtml);
            if (animate) {
                $message.hide();
                $('#driverChatMessages').append($message);
                $message.fadeIn('slow');
            } else {
                $('#driverChatMessages').append($message);
            }
            scrollToBottom();
        }

        function scrollToBottom() {
            const chatMessages = document.getElementById('driverChatMessages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            } else {
                console.error('driverChatMessages element not found');
            }
        }

        function playNotificationSound() {
            const sound = document.getElementById('newMessageSound');
            if (sound) sound.play().catch(e => console.log('Sound playback prevented:', e));
        }

        function markMessagesAsRead() {
            if (!chatChannelId) return;
            $.ajax({
                url: "{{ route('chat.markAsRead') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    channel_id: chatChannelId
                },
                success: function() {
                    unreadMessages = 0;
                    updateUnreadBadge();
                },
                error: function(xhr) {
                    console.error('Failed to mark messages as read:', xhr.responseText);
                }
            });
        }

        function updateUnreadBadge() {
            if (unreadMessages > 0) {
                $('#driverChatButton').addClass('has-new-message');
                $('.default-text').text(`New Messages (${unreadMessages})`);
            } else {
                $('#driverChatButton').removeClass('has-new-message');
                $('.default-text').text("@lang('header.no')");
            }
        }

        function showMediaPreview(file) {
            const previewContainer = $('#driverMediaPreview');
            const previewImage = $('#driverPreviewImage');
            const previewVideo = $('#driverPreviewVideo');
            const removeButton = $('#driverRemoveMedia');

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.attr('src', e.target.result).show();
                    previewVideo.hide();
                    removeButton.show();
                    previewContainer.show();
                };
                reader.readAsDataURL(file);
            } else if (file.type.startsWith('video/')) {
                previewVideo.attr('src', URL.createObjectURL(file)).show();
                previewImage.hide();
                removeButton.show();
                previewContainer.show();
            }
        }
    });
</script>
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('تم نسخ الرقم: ' + text);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
</script>
