<div id="print-section" dir="rtl" style="font-family: Tahoma, sans-serif;">
    <h2>@lang('order.order_id'): {{ $order->order_number }}</h2>



    @if ($order->waiter_id && isset($order->waiter))
        <p>@lang('employee.name'): {{ $order->waiter->first_name ?? '' }} {{ $order->waiter->last_name ?? '' }}</p>
    @endif

    @if ($order->table_id && isset($order->table))
        <p>@lang('order.table'): {{ $order->table->name ?? '' }}</p>
    @endif

    <p>@lang('order.invoice_number'): {{ $order->invoice_number }}</p>
    <p>@lang('order.date'): {{ $order->date }} {{ $order->time }}</p>
    <p>@lang('order.type'): {{ __('order.' . $order->type) }}</p>

    <h4>@lang('order.details')</h4>
    <table dir="rtl" style="width: 100%; font-family: Tahoma, sans-serif; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th>@lang('order.dish_name')</th>
                <th>@lang('order.size')</th>
                <th>@lang('order.quantity')</th>
                <th>@lang('order.price_after_tax')</th>
            </tr>
        </thead>
        <tbody>
            @foreach (collect($order->details)->unique('id') as $item)
                <tr>
                    <td>{{ $item->dish->name_site ?? '' }}</td>
                    <td>{{ $item->dish_size->name_site ?? '' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price_after_tax, 2) }}</td>
                </tr>

                @php
                    $itemAddons = collect($order->addons)->where('order_details_id', $item->id);
                @endphp

                @if ($itemAddons->count() > 0)
                    <tr>
                        <td colspan="4">
                            <strong style="color: #444">@lang('order.addons'):</strong>
                            <table style="width: 100%; border-collapse: collapse; margin-top: 5px;" border="1">
                                <thead>
                                    <tr>
                                        {{-- <th>@lang('order.dish_name')</th> --}}
                                        <th>@lang('order.quantity')</th>
                                        <th>@lang('order.price_after_tax')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($itemAddons as $addon)
                                        <tr>
                                            {{-- <td>{{ $addon->order_detail->dish->name_site ?? '' }}</td> --}}
                                            <td>{{ $addon->quantity }}</td>
                                            <td>{{ number_format($addon->price_after_tax, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>



    <hr>
    <p>@lang('order.subtotal'): {{ number_format($order->total_price_befor_tax, 2) }}</p>
    <p>@lang('order.tax'): {{ number_format($order->tax_value, 2) }}</p>
    <p>@lang('order.service_fees'): {{ number_format($order->service_fees, 2) }}</p>
    <p>@lang('order.total'): <strong>{{ number_format($order->total_price_after_tax, 2) }}</strong></p>
    @if (!empty($order->transactions) && count($order->transactions) > 0)
      
@foreach ( $order->transactions as $transaction)
   <p>@lang('order.payment_method'): {{ __('order.' . $transaction->payment_method) }}</p>
        <p>@lang('order.payment_status'): {{ __('order.' . $transaction->payment_status) }}</p>
        <p>@lang('order.paid'): {{ number_format($transaction->paid, 2) }}</p>
@endforeach

    @endif


</div>
