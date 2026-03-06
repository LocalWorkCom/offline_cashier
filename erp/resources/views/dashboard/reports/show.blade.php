@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('order.show')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('order.orders')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('order.show')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                <h4> @lang('order.order_details')</h4>

                            </div>
                            <div class="btn-group" role="group"> <button id="btnGroupVerticalDrop4" type="button"
                                    class="btn btn-primary dropdown-toggle show" data-bs-toggle="dropdown"
                                    aria-expanded="true"> تغيير الحالة
                                </button>

                                <ul class="dropdown-menu show" aria-labelledby="btnGroupVerticalDrop4"
                                    style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate(0px, 39px);"
                                    data-popper-placement="bottom-start">
                                    @foreach ($order['next_status'] as $status)
                                        <li>
                                            <a onclick="ChangeOrder('{{ $order->id }}', '{{ $status }}')"
                                                class="dropdown-item">
                                                @lang('order.' . $status)
                                                <i class="ri-{{ $status }}-line"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.date')</label>
                                    <p class="form-text">{{ $order->date }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.order_number')</label>
                                    <p class="form-text">{{ $order->order_number }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.invoice_num')</label>
                                    <p class="form-text">{{ $order->invoice_number }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.branch')</label>
                                    <p class="form-text">{{ $order->branch->name_ar }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.type')</label>
                                    <p class="form-text"> <span class="badge bg-primary-transparent">
                                            @lang('order.' . strtolower($order->type))</span></p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.status')</label>
                                    <p class="form-text"> <span class="badge bg-warning-transparent">
                                            @lang('order.' . strtolower($order->status))</span></p>
                                </div>

                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.total_price')</label>
                                    <p class="form-text">{{ $order->total_price_after_tax }}</p>
                                </div>
                                <h4>@lang('order.client_details')</h4>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.client')</label>
                                    <p class="form-text">{{ $order->client->name }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.phone')</label>
                                    <p class="form-text">{{ $order->client->phone }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.email')</label>
                                    <p class="form-text">{{ $order->client->email }}</p>
                                </div>
                                <h4>@lang('order.address_details')</h4>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.city')</label>
                                    <p class="form-text">{{ $order->address->city }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.state')</label>
                                    <p class="form-text">{{ $order->address->state }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.address')</label>
                                    <p class="form-text">{{ $order->address->address }}</p>
                                </div>


                                <h4> @lang('order.payment_details')</h4>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.status_paid')</label>
                                    <p class="form-text"> <span class="badge bg-primary-transparent">
                                            @lang('order.' . strtolower($order['transaction']['payment_status']))</span></p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.payment_method')</label>
                                    <p class="form-text"> <span class="badge bg-primary-transparent">
                                            @lang('order.' . strtolower($order['transaction']['payment_method']))</span></p>
                                </div>

                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.paid')</label>
                                    <p class="form-text"> <span class="badge bg-primary-transparent">
                                            {{ $order['transaction']['paid'] }}</span></p>
                                </div>

                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.status_paid')</label>
                                    <p class="form-text">
                                        <span class="badge bg-primary-transparent">
                                            {{ $order['transaction']['is_refund'] ? __('order.refund') : __('order.purchase') }}
                                        </span>
                                    </p>
                                </div>

                                <h4>@lang('order.dish_details')</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>@lang('order.dish')</th>
                                            <th>@lang('order.quantity')</th>
                                            <th>@lang('order.total_price')</th>
                                            <th>@lang('order.total_before_tax')</th>
                                            <th>@lang('order.tax')</th>
                                            <th>@lang('order.total_after_tax')</th>
                                            <th>@lang('order.status')</th>
                                            <th>@lang('order.note')</th>
                                            <th>@lang('order.actions')</th> <!-- New column for actions -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $temp_offer = 0; @endphp
                                        @foreach ($order['details'] as $detail)
                                            @if ($detail->offer_id && $detail->offer_id != $temp_offer)
                                                {{-- Display Offer Header --}}
                                                @php $temp_offer = $detail->offer_id; @endphp
                                                <tr> {{-- Offer row colored --}}
                                                    <td>عرض {{ $detail->offer->name_ar }}</td>
                                                    <td></td>
                                                    @if ($detail->offer->discount_type == 'fixed')
                                                        <td>{{ $detail->offer->discount_value }}</td>
                                                    @else
                                                        <td>{{ $detail->total }}</td>
                                                    @endif
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td>
                                                        <p class="form-text">
                                                            <span class="badge bg-warning-transparent">
                                                                @lang('order.' . strtolower($detail->status))
                                                            </span>
                                                        </p>
                                                    </td>
                                                    <td>{{ $detail->note }}</td>
                                                    <td>  @if ($detail->status != 'cancel')
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <button type="button"
                                                                onclick="ChangeItemOrder('{{ $detail->id }}', 'cancel')"
                                                                class="btn btn-danger btn-sm d-flex align-items-center">
                                                                <i class="ri-close-circle-line me-1"></i>@lang('order.cancel')
                                                            </button>
                                                        </div>
                                                    @endif</td>
                                                </tr>
                                        
                                                {{-- Display Offer Details --}}
                                                @foreach ($detail->offer->details as $offer_detail)
                                                    <tr> {{-- Offer details row colored --}}
                                                        <td> {{ $offer_detail->dish->name_ar }}</td>
                                                        <td>{{ $offer_detail->count }}</td>
                                                        <td>{{ $detail->offer->discount_type == 'fixed' ? 0 : $detail->total }}</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                @endforeach
                                            @elseif (!$detail->offer_id)
                                                {{-- Display Individual Dish Details --}}
                                                <tr class="table-default"> {{-- Default styling for individual dishes --}}
                                                    <td>{{ $detail->dish->name_ar }}</td>
                                                    <td>{{ $detail->quantity }}</td>
                                                    <td>{{ $detail->total }}</td>
                                                    <td>{{ $detail->price_befor_tax }}</td>
                                                    <td>{{ $detail->tax_value }}</td>
                                                    <td>{{ $detail->price_after_tax }}</td>
                                                    <td>
                                                        <p class="form-text">
                                                            <span class="badge bg-warning-transparent">
                                                                @lang('order.' . strtolower($detail->status))
                                                            </span>
                                                        </p>
                                                    </td>
                                                    <td>{{ $detail->note }}</td>
                                                    <td>
                                                        @if ($detail->status != 'cancel')
                                                            <div class="d-flex gap-2 align-items-center">
                                                                <button type="button"
                                                                    onclick="ChangeItemOrder('{{ $detail->id }}', 'cancel')"
                                                                    class="btn btn-danger btn-sm d-flex align-items-center">
                                                                    <i class="ri-close-circle-line me-1"></i>@lang('order.cancel')
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        
                                    </tbody>
                                </table>


                                <h4>@lang('order.order_addons')</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>@lang('order.name')</th>
                                            <th>@lang('order.quantity')</th>
                                            <th>@lang('order.price')</th>
                                            <th>@lang('order.total_before_tax')</th>
                                            <th>@lang('order.tax_value')</th>
                                            <th>@lang('order.total_after_tax')</th>
                                            <th>@lang('order.status')</th>
                                            <th>@lang('order.actions')</th> <!-- New column for actions -->

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order['addons'] as $addon)
                                            <tr>
                                                <td>{{ $addon->Addon->addons->name_ar }}</td>
                                                <td>{{ $addon->price }}</td>
                                                <td>{{ $addon->quantity }}</td>
                                                <td>{{ $addon->price_before_tax }}</td>
                                                <td>{{ getSetting('tax_percentage') * $addon->price_before_tax }}
                                                </td>
                                                <td>{{ $addon->price_after_tax }}</td>
                                                <td>
                                                    <p class="form-text"> <span class="badge bg-warning-transparent">
                                                            @lang('order.' . strtolower($addon->status))</span></p>
                                                </td>
                                                <td>
                                                    @if ($addon->status != 'cancel')
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <button type="button"
                                                                onclick="ChangeAddonOrder('{{ $addon->id }}', 'cancel')"
                                                                class="btn btn-danger btn-sm d-flex align-items-center">
                                                                <i class="ri-close-circle-line me-1"></i>
                                                                @lang('order.cancel')
                                                            </button>

                                                        </div>
                                                    @endif



                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>

                            <div class="main-content app-content">
                                <h4>@lang('order.order_trackings')</h4>
                                <div class="container-fluid">

                                    <div class="row justify-content-center">
                                        <div class="col-xxl-9 col-xl-10 col-sm-12">
                                            <ul class="timeline list-unstyled">
                                                @foreach ($order['tracking'] as $tracking)
                                                    <li>
                                                        <div class="timeline-time text-end">
                                                            <span
                                                                class="time d-inline-block">{{ date('Y-m-d', strtotime($tracking->created_at)) }}</span>
                                                        </div>
                                                        <div class="timeline-icon">
                                                            <a href="javascript:void(0);"></a>
                                                        </div>
                                                        <div class="timeline-body">
                                                            <div class="d-flex align-items-top timeline-main-content mt-0">
                                                                <div class="flex-fill">
                                                                    <div class="align-items-center">
                                                                        <div class="mt-sm-0 mt-2">
                                                                            <p class="mb-0 fs-14 fw-semibold">
                                                                                @lang('order.' . $tracking->order_status)
                                                                            </p>
                                                                            {{-- <p class="mb-0 text-muted">
                                                                                Changed the password
                                                                                of
                                                                                gmail 4 hrs ago. <span
                                                                                    class="badge bg-secondary">Update</span>
                                                                            </p> --}}
                                                                        </div>
                                                                        <div class="ms-auto">
                                                                            <span
                                                                                class="float-end badge bg-light text-muted timeline-badge mt-2 rounded-1">
                                                                                {{ date('H:i a', strtotime($tracking->time)) }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach


                                            </ul>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- End:: row-1 -->
        </div>
    </div>
    <!-- APP-CONTENT CLOSE -->
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function ChangeAddonOrder(order_addon_id, status) {
            Swal.fire({
                title: '@lang('order.confirm_action')',
                text: '@lang('order.are_you_sure')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '@lang('order.yes_proceed')',
                cancelButtonText: '@lang('order.no_cancel')'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Call the AJAX route
                    $.ajax({
                        url: '{{ route('order.addon.change') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            order_addon_id: order_addon_id,
                            status: status
                        },
                        success: function(response) {
                            Swal.fire(
                                '@lang('order.success')',
                                "response.message",
                                'success'
                            );
                            // Optionally reload the page or update the UI
                            location.reload();
                        },
                        error: function(error) {
                            Swal.fire(
                                '@lang('order.error')',
                                error.responseJSON.message,
                                'error'
                            );
                        }
                    });
                }
            });
        }

        function ChangeItemOrder(order_detail_id, status) {
            Swal.fire({
                title: '@lang('order.confirm_action')',
                text: '@lang('order.are_you_sure')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '@lang('order.yes_proceed')',
                cancelButtonText: '@lang('order.no_cancel')'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Call the AJAX route
                    $.ajax({
                        url: '{{ route('order.detail.change') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            order_detail_id: order_detail_id,
                            status: status
                        },
                        success: function(response) {
                            Swal.fire(
                                '@lang('order.success')',
                                "response.message",
                                'success'
                            );
                            // Optionally reload the page or update the UI
                            location.reload();
                        },
                        error: function(error) {
                            Swal.fire(
                                '@lang('order.error')',
                                error.responseJSON.message,
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection
