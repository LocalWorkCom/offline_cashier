@extends('website.layouts.master')

@section('content')
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-5">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('header.home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> @lang('header.myorder')</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="my-orders">
        <div class="container py-2">
            <h4 class="fw-bold">@lang('header.myorder')</h4>
            <div class="card p-4">
                @php
                    $hasOrders = $orders->isNotEmpty();
                    $hasReservations = $reservations->isNotEmpty();
                @endphp

                @if ($hasOrders || $hasReservations)
                    <!-- Orders Section -->
                    @if ($hasOrders)
                        <h5><i class="fas fa-file-alt main-color fa-xs"></i> @lang('header.ordersum')</h5>
                        @foreach ($orders as $order)
                            <div class="card-body border-bottom">
                                <div class="d-flex justify-content-between pt-3">
                                    <h5 class="fw-bold m-0">
                                        {{ $order->created_at ? $order->created_at->translatedFormat('j F .g:i A') : '' }}
                                    </h5>
                                    @php
                                        $lastTrackingStatus = $order->tracking->last()?->order_status;
                                    @endphp
                                    @if ($lastTrackingStatus == 'pending')
                                        <p class="bg-warning text-dark rounded fw-bold p-1 mb-0"> @lang('order.pending')</p>
                                    @elseif ($lastTrackingStatus == 'in_progress')
                                        <p class="bg-warning text-dark rounded fw-bold p-1 mb-0"> @lang('order.in_progress')</p>
                                    @elseif ($lastTrackingStatus == 'on_way')
                                        <p class="bg-warning text-dark rounded fw-bold p-1 mb-0"> @lang('order.on_way')</p>
                                    @elseif ($lastTrackingStatus == 'delivered')
                                        <p class="bg-success text-white rounded fw-bold p-1 mb-0"> @lang('order.delivered')</p>
                                    @elseif ($lastTrackingStatus == 'readyForPickup')
                                        <p class="bg-success text-white rounded fw-bold p-1 mb-0"> @lang('order.readyForPickup')</p>
                                    @elseif ($lastTrackingStatus == 'cancelled')
                                        <p class="bg-danger text-dark rounded fw-bold p-1 mb-0"> @lang('order.cancelled')</p>
                                    @endif
                                </div>
                                <small class="text-muted py-1"> @lang('header.ordermark') : {{ $order->order_number }}</small>
                                @foreach ($order->orderDetails as $detail)
                                    @php
                                        $addons = $order->orderAddons->filter(
                                            fn($addon) => $addon->order_details_id == $detail->id &&
                                                $addon->Addon?->addons?->name_ar,
                                        );
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="p-1 bg-secondary text-light rounded">{{ $detail->quantity }}</span>
                                            <span class="mx-1">x</span>
                                            <span>{{ app()->getLocale() === 'ar' ? $detail->dish?->name_ar : $detail->dish?->name_en }}</span>
                                            @if ($detail->dishSize)
                                                <span>({{ app()->getLocale() === 'ar' ? $detail->dishSize->size_name_ar : $detail->dishSize->size_name_en }})</span>
                                            @endif
                                        </div>
                                        <p class="mb-0 fw-bold">
                                            @if ($order->tax_applications == 0)
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
                                @endforeach
                                <a href="{{ route('order.paymentdetails', ['id' => $order->id]) }}"
                                    class="text-decoration-underline py-1 fw-bold"> @lang('header.paymentdetails')</a>
                                @if (
                                    $lastTrackingStatus == 'pending' ||
                                        $lastTrackingStatus == 'in_progress' ||
                                        $lastTrackingStatus == 'on_way' ||
                                        $lastTrackingStatus == 'readyForPickup')
                                    <form method="GET" action="{{ route('orders.tracking') }}">
                                        <div class="d-flex justify-content-end">
                                            <button class="btn reversed main-color"
                                                type="submit">@lang('order.order_trackings')</button>
                                        </div>
                                    </form>
                                @else
                                    <div class="d-flex justify-content-end">

                                        <div class="d-flex justify-content-end">
                                            <button class="btn reversed main-color" onclick="reorder({{ $order->id }})"
                                                type="button">@lang('order.reorder')</button>
                                        </div>

                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif

                    <!-- Reservations Section -->
                    @if ($hasReservations)
                        <h5 class="mt-4"><i class="fas fa-calendar-alt main-color fa-xs"></i> @lang('order.myreservations')</h5>
                        @foreach ($reservations as $reservation)
                            <div class="card-body border-bottom">
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
                                <small class="text-muted py-1">@lang('order.reservationNumber') :
                                    {{ $reservation->reservation_number }}</small>

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
                                                {{ $reservation->time_to ? $reservation->time_to->format('h:i') : '' }}
                                                @lang($reservation->time_to ? ($reservation->time_to->format('A') === 'AM' ? 'header.am' : 'header.pm') : 'order.noTime')
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @if ($reservation->reservation_type == 'with')
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
                                @endif
                                <a href="{{ route('order.paymentdetails', ['id' => $reservation->id]) }}"
                                    class="text-decoration-underline py-1 fw-bold"> @lang('header.paymentdetails')</a>
                                {{-- @if ($reservation->status == 'confirm')
                                    <div class="d-flex justify-content-end">
                                        <button class="btn reversed main-color">@lang('order.order_trackings')</button>
                                    </div>
                                @endif --}}
                            </div>
                        @endforeach
                    @endif
                @else
                    <!-- No Orders or Reservations -->
                    <div class="p-5 w-50 text-center mx-auto mt-5">
                        <img class="noAddress-img"
                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/mdi_file-location.png') }}"
                            alt="" />
                        <h4 class="my-4 fw-bold">@lang('auth.noorders')</h4>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
