@extends('layouts.master')

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('order.order_details')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.orders.list') }}">@lang('order.orders')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('order.order_details')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- END PAGE HEADER -->

    <!-- APP CONTENT -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start::row-1 -->
            <div class="row" id="printable-content">
                <div class="col-xl-8">
                    <!-- Order Details -->
                    <div class="row" id="printable-content">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="card-title">
                                        @lang('order.order_number') - <span class="text-primary">#{{ $order->order_number }}</span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th scope="col">@lang('order.image')</th>
                                                    <th scope="col">@lang('order.item')</th>
                                                    <th scope="col">@lang('order.size')</th>
                                                    <th scope="col">@lang('order.order_addons')</th>
                                                    <th scope="col">@lang('order.quantity')</th>
                                                    <th scope="col">@lang('order.total_price')</th>
                                                    <th scope="col">@lang('order.notes')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($order->orderDetails)
                                                    @forelse ($order->orderDetails as $detail)
                                                        @php
                                                            $addons = $order->orderAddons->filter(
                                                                fn($addon) => $addon->order_details_id == $detail->id &&
                                                                    $addon->Addon?->addons?->name_ar,
                                                            );
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                @if ($detail->dish && $detail->dish->image)
                                                                    <img src="{{ asset($detail->dish->image) ?? asset('default-dish.jpg') }}"
                                                                        alt="Dish Image"
                                                                        style="width: 100px; height: 100px;">
                                                                @else
                                                                    @lang('order.no_image')
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($detail->dish)
                                                                    {{ app()->getLocale() === 'ar' ? $detail->dish->name_ar : $detail->dish->name_en }}
                                                                @else
                                                                    <span class="text-danger">@lang('order.dish_not_found')</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($detail->dish && $detail->dishSize)
                                                                    {{ app()->getLocale() === 'ar' ? $detail->dishSize->size_name_ar : $detail->dishSize->size_name_en }}
                                                                @else
                                                                    {{ __('order.nosize') }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($addons->isNotEmpty())
                                                                    {{ $addons->map(fn($addon) => app()->getLocale() === 'ar' ? $addon->Addon->addons->name_ar : $addon->Addon->addons->name_en)->implode(', ') }}
                                                                @else
                                                                    {{ __('order.noaddons') }}
                                                                @endif
                                                            </td>
                                                            <td>{{ $detail->quantity }}</td>
                                                            <td>
                                                                @if ($order->tax_application == 0)
                                                                    {{ $detail->price_befor_tax + $addons->sum('price_before_tax') }}
                                                                @else
                                                                    {{ $detail->price_after_tax + $addons->sum('price_after_tax') }}
                                                                @endif
                                                                {{ $order->Branch->country->currency_symbol }}
                                                                </p>
                                                            </td>
                                                            <td>{{ $detail->note ?? __('order.nonotes') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">@lang('order.no_details')</td>
                                                        </tr>
                                                    @endforelse
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer border-top-0">
                                    <div class="btn-list float-end">
                                        <button type="button" class="d-inline-flex btn btn-primary btn-wave print-button"
                                            onclick="printFormattedReport()">
                                            <i class="ri-printer-line me-1 align-middle"></i>@lang('order.print')
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking Section -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">@lang('order.order_status')</div>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group">
                                        <!-- Order Tracking Section -->
                                        <li class="list-group-item">
                                            <strong>@lang('order.order_trackings')</strong>
                                            <div class="d-flex justify-content-between mt-2">
                                                <span
                                                    class="badge fs-5 {{ [
                                                        'pending' => 'bg-warning-transparent text-warning',
                                                        'in_progress' => 'bg-primary-transparent text-primary',
                                                        'completed' => 'bg-success-transparent text-success',
                                                        'on_way' => 'bg-info-transparent text-info',
                                                        'delivered' => 'bg-secondary-transparent text-secondary',
                                                        'cancelled' => 'bg-danger-transparent text-danger',
                                                    ][strtolower($tracking->order_status ?? 'unknown')] ?? 'bg-light text-dark' }}">
                                                    @lang('order.' . strtolower($tracking->order_status ?? 'unknown'))
                                                </span>
                                                <span class="text-muted">
                                                    {{ $tracking->created_at->format('d-m-Y H:i') }}
                                                </span>
                                            </div>
                                        </li>
                                        <!-- Payment Method Section -->
                                        <li class="list-group-item">
                                            <strong>@lang('order.payment_method')</strong>
                                            <div class="mt-2">
                                                <span
                                                    class="badge fs-5 {{ [
                                                        'cash' => 'bg-primary-transparent text-primary',
                                                        'credit_card' => 'bg-info-transparent text-info',
                                                        'online' => 'bg-success-transparent text-success',
                                                    ][strtolower($transaction->payment_method ?? 'unknown')] ?? 'bg-light text-dark' }}">
                                                    @lang('order.' . strtolower($transaction->payment_method ?? 'unknown'))
                                                </span>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>@lang('order.status_paid')</strong>
                                            <div class="mt-2">
                                                <span
                                                    class="badge fs-5 {{ [
                                                        'paid' => 'bg-success-transparent',
                                                        'unpaid' => 'bg-danger-transparent',
                                                    ][strtolower($transaction->payment_status ?? 'unknown')] ?? 'bg-light text-dark' }}">
                                                    @lang('order.' . strtolower($transaction->payment_status ?? 'unknown'))
                                                </span>
                                            </div>
                                        </li>
                                        @if ($cancellationReason)
                                            <li class="list-group-item">
                                                <strong>@lang('order.comments')</strong>
                                                <div class="mt-2">
                                                    <span class="badge fs-5 bg-info-transparent text-info">
                                                        {{ $cancellationReason->reason }}
                                                    </span>
                                                </div>
                                            </li>
                                        @endif
                                        @if ($cancellationReason)
                                            <li class="list-group-item">
                                                <strong>@lang('order.reasons')</strong>
                                                <div class="mt-2">
                                                    <span class="badge fs-5 bg-info-transparent text-info">
                                                        @foreach ($order->cancellationReasons as $cancellationReason)
                                                            {{ app()->isLocale('ar') ? $cancellationReason->reasonModel->reason_ar : $cancellationReason->reasonModel->reason_en }}
                                                        @endforeach
                                                    </span>
                                                </div>
                                            </li>
                                        @endif
                                    </ul>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <!-- Customer Details -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('order.client_details')</div>
                        </div>
                        <div class="card-body">
                            @if ($order->client)
                                <ul class="list-unstyled order-details-list">
                                    <li>
                                        <span class="text-muted">@lang('order.name'):</span>
                                        {{ $order->responsible_person ?? __('order.unknown') }}
                                    </li>
                                    <li>
                                        <span class="text-muted">@lang('order.email'):</span>
                                        {{ $order->responsible_person_email ?? __('order.unknown') }}
                                    </li>
                                    <li>
                                        <span class="text-muted">@lang('order.phone'):</span>
                                        {{ $order->responsible_person_phone ?? __('order.unknown') }}
                                    </li>
                                    <li>
                                        <span class="text-muted">@lang('order.flag'):</span>
                                        {{ $order->responsible_person_flag ?? __('order.unknown') }}
                                    </li>
                                </ul>
                            @else
                                <span class="text-danger">@lang('order.client_deleted')</span>
                            @endif
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    @if ($order->address)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">@lang('order.delivery_address')</div>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled order-details-list">
                                    <li>
                                        <span class="text-muted">@lang('order.address'):</span>
                                        {{ $order->address->address ?? __('order.unknown') }}
                                    </li>
                                    <li>
                                        <span class="text-muted">@lang('order.city'):</span>
                                        {{ $order->address->city ?? __('order.unknown') }}
                                    </li>
                                    <li>
                                        <span class="text-muted">@lang('order.state'):</span>
                                        {{ $order->address->state ?? __('order.unknown') }}
                                    </li>
                                    <li>
                                        <span class="text-muted">@lang('order.zip'):</span>
                                        {{ $order->address->postal_code ?? __('order.unknown') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Summary -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('order.payment_summary')</div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <span>@lang('order.sub_total')</span>
                                <span>
                                    @if ($order->tax_application == 0)
                                        {{ $order->total_price_befor_tax }}
                                    @else
                                        {{ $order->total_price_befor_tax + $order->tax_value }}
                                    @endif
                                    {{ $order->Branch->country->currency_symbol }}</p>
                                </span>
                            </div>
                            @if ($order->coupon_id)
                                <div class="d-flex justify-content-between">
                                    <span>@lang('header.coupon')</span>
                                    @if ($order->coupon->type === 'percentage')
                                        <span class="main-color">
                                            -{{ ($order->total_price_befor_tax * $order->coupon->value) / 100 }}
                                            {{ $order->Branch->country->currency_symbol }} </span>
                                    @elseif ($order->coupon->type === 'fixed')
                                        <span class="main-color">
                                            -{{ $order->coupon->value }}
                                            {{ $order->Branch->country->currency_symbol }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                            <div class="d-flex justify-content-between">
                                <span>@lang('header.feesdelivery')</span>
                                <span>{{ $order->delivery_fees ?? 0 }}
                                    {{ $order->Branch->country->currency_symbol }}</p>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>@lang('header.serviceFees')</span>
                                <span>{{ $order->service_fees ?? 0 }}
                                    {{ $order->Branch->country->currency_symbol }}</p>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>@lang('header.taxPercentage')</span>
                                <span>{{ number_format(($order->tax_value / $order->total_price_befor_tax) * 100, 2) }}%
                                    </p>
                                </span>
                            </div>
                            {{-- <div class="d-flex justify-content-between">
                                @if ($order->tax_value != 0.0 && $order->tax_value != null && $order->total_price_befor_tax == $order->total_price_after_tax)
                                    <span>@lang('header.includefees')
                                        <span class="fw-bold">
                                            {{ $order->tax_value / $order->total_price_befor_tax) * 100, 2) . '%' }}
                                        </span>@lang('header.anotherway')

                                        {{ $order->tax_value }} {{ $order->Branch->country->currency_symbol }}</p>
                                    </span>
                                @endif
                            </div> --}}
                            <div class="d-flex justify-content-between">
                                <span>@lang('order.total')</span>
                                <span>{{ number_format($order->total_price_after_tax, 2) }}
                                    {{ $order->Branch->country->currency_symbol }}</p>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Order Notes -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('order.ordernotes')</div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <span>{{ $order->note ?? __('order.nonotes') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End::row-1 -->
        </div>
    </div>
    <!-- END APP CONTENT -->
@endsection

@section('scripts')
    <script>
        function printFormattedReport() {
            // Get the printable content
            const printableContent = document.getElementById('printable-content').innerHTML;

            // Create an iframe element dynamically
            const iframe = document.createElement('iframe');

            // Set the iframe to be invisible
            iframe.style.position = 'absolute';
            iframe.style.width = '0px';
            iframe.style.height = '0px';
            iframe.style.border = 'none';
            document.body.appendChild(iframe);

            // Get the iframe's document object
            const doc = iframe.contentWindow.document;

            // Generate content for the iframe
            const content = `
        <!DOCTYPE html>
        <html lang="{{ app()->getLocale() }}">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{{ app()->getLocale() == 'en' ? 'Order Report' : 'تقرير طلب' }}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    direction: {{ app()->getLocale() == 'en' ? 'ltr' : 'rtl' }};
                    margin: 30px;
                }
                .table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .table th, .table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
                }
                .table th {
                    background-color: #f2f2f2;
                }
                @media print {
                    .print-button {
                        display: none !important;
                    }
                }
            </style>
        </head>
        <body>
            ${printableContent}
        </body>
        </html>
    `;

            // Write content to the iframe
            doc.open();
            doc.write(content);
            doc.close();

            // Wait for the content to load before printing
            iframe.onload = function() {
                iframe.contentWindow.print();
                document.body.removeChild(iframe); // Remove iframe after printing
            };
        }
    </script>
@endsection
