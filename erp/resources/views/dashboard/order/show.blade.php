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
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('orders.list') }}'">
                            @lang('order.orders')
                        </a>
                    </li>
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
                                    <p class="form-text">
                                        @if ($order->branch)
                                            <td>{{ app()->getLocale() == 'en' ? $order->branch->name_en : $order->branch->name_ar }}
                                            </td>
                                        @else
                                            <span class="text-danger">@lang('order.branch_not_found')</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.currency_symbol')</label>
                                    <p class="form-text">
                                        @if ($order->branch)
                                            <td>{{ $order->branch->country->currency_symbol }}</td>
                                        @else
                                            <span class="text-danger">@lang('order.branch_not_found')</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.type')</label>
                                    <p class="form-text"> <span class="badge bg-primary-transparent">
                                            @lang('order.' . strtolower($order->type))</span></p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.status')</label>
                                    <p class="form-text">
                                        @if ($order->tracking->isNotEmpty())
                                            <span class="badge bg-warning-transparent">
                                                @lang('order.' . strtolower($order->tracking->last()->order_status))
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">@lang('order.status_unknown')</span>
                                        @endif
                                    </p>
                                </div>
                                @if ($order->type == 'dine-in' && $order->table_id)
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('order.table')</label>
                                        <p class="form-text">
                                            

                                            <span class="badge bg-secondary">{{ app()->getLocale() == 'ar' ? $order->table?->name_ar : $order->table?->name_en }}</span>


                                        </p>
                                    </div>
                                @endif
                                @if ($order->type == 'dine-in' && !in_array($order->status, ['completed', 'cancelled']))

                                    @if (auth('admin')->user()->hasPermissionTo('changeTable', 'admin'))
                                        @if (session('success'))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                {{ session('success') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        @endif

                                        @if (session('error'))
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                {{ session('error') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        @endif

                                        <button type="button" class="btn btn-orange-light btn-wave edit-color-btn"
                                            data-id="{{ $order->id }}" data-bs-toggle="modal" data-bs-target="#addModal">
                                            @lang('order.changetable') <i class="ri-edit-line"></i>
                                        </button>
                                    @endif
                                @endif
                                <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form id="add-color-form" action="{{ route('order.table.change') }}"
                                                method="POST" class="needs-validation" novalidate>
                                                @csrf

                                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                {{-- Pass the current order ID --}}

                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="editModalLabel">@lang('order.changetable')</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="row gy-4">
                                                        <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                            <label for="table_id"
                                                                class="form-label">@lang('order.table')</label>
                                                            <select name="table_id" id="table_id" class="form-select"
                                                                required>
                                                                <option value="">@lang('order.choose_table')</option>
                                                                @foreach (TablesWithState(1, $order->branch_id) ?? [] as $table)
                                                                    <option value="{{ $table->id }}">

                                                                        {{ app()->getLocale() == 'ar' ? $table?->name_ar : $table?->name_en }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                            <div class="valid-feedback">@lang('validation.Correct')</div>
                                                            <div class="invalid-feedback">@lang('validation.EnglishName')</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">@lang('modal.close')</button>
                                                    <button type="submit"
                                                        class="btn btn-outline-primary">@lang('modal.save')</button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.comments')</label>
                                    <p class="form-text">
                                        @foreach ($order->cancellationReasons as $reason)
                                            {{ $reason->reason }}
                                        @endforeach
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.reasons')</label>
                                    <p class="form-text">
                                        @foreach ($order->cancellationReasons as $cancellationReason)
                                            {{ app()->isLocale('ar') ? $cancellationReason->reasonModel->reason_ar : $cancellationReason->reasonModel->reason_en }}
                                        @endforeach

                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.servicefees')</label>
                                    <p class="form-text total-price-display">
                                        {{ $order->service_fees }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.deliveryfees')</label>
                                    <p class="form-text total-price-display">
                                        {{ $order->delivery_fees }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <label class="form-label">@lang('order.total_price')</label>
                                    <p class="form-text total-price-display">
                                        {{ $order->total_price_after_tax }}
                                    </p>
                                </div>
                                <h4>@lang('order.ordernotes')</h4>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.note')</label>
                                    <p class="form-text">
                                        {{ $order->note }}
                                    </p>
                                </div>

                                <h4>@lang('order.client_details')</h4>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.client')</label>
                                    <p class="form-text">
                                        {{ $order->responsible_person }}
                                    </p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.phone')</label>
                                    <p class="form-text">{{ $order->responsible_person_phone }}</p>
                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('order.email')</label>
                                    <p class="form-text">{{ $order->responsible_person_email }}</p>
                                </div>

                                <h4>@lang('order.address_details')</h4>
                                @if ($order->address)
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('order.city')</label>
                                        <p class="form-text">{{ $order->address->city->name }}</p>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('order.state')</label>
                                        <p class="form-text">{{ $order->address->area->name }}</p>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('order.address')</label>
                                        <p class="form-text">{{ $order->address->address }}</p>
                                    </div>
                                @else
                                    <div class="col-xl-12">
                                        <p class="text-danger">@lang('order.address_not_found')</p>
                                    </div>
                                @endif

                                <h4>@lang('order.payment_details')</h4>
                                @if (isset($order['transactions']))
                                    @php
                                        $hasUnpaid = false;
                                        $hasPart = false;
                                        $hasPaid = false;

                                        foreach ($order['transactions'] as $transaction) {
                                            if ($transaction->payment_status === 'unpaid') {
                                                $hasUnpaid = true;
                                                break; // Found unpaid - highest priority
                                            } elseif ($transaction->payment_status === 'part') {
                                                $hasPart = true;
                                            } elseif ($transaction->payment_status === 'paid') {
                                                $hasPaid = true;
                                            }
                                        }

                                        // Determine final status
                                        if ($hasUnpaid) {
                                            $paymentStatus = 'unpaid';
                                        } elseif ($hasPart && !$hasPaid) {
                                            $paymentStatus = 'part'; // Only show part if no paid transactions exist
                                        } else {
                                            $paymentStatus = 'paid'; // Default when no unpaid or only part+paid exists
                                        }
                                    @endphp

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">@lang('order.status_paid')</label>
                                        <p class="form-text">
                                            <span
                                                class="badge bg-{{ $paymentStatus === 'paid' ? 'success' : ($paymentStatus === 'part' ? 'warning' : 'danger') }}-transparent">
                                                @lang('order.' . $paymentStatus)
                                            </span>
                                        </p>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label class="form-label">@lang('order.total_paid')</label>
                                        <p class="form-text">
                                            <span class="badge bg-primary-transparent">
                                                {{ $order['transactions']->sum('paid') }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="col-12">
                                        <h5>@lang('order.transactions')</h5>
                                        @if ($order['transactions']->isNotEmpty())
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('order.paid')</th>
                                                        <th>@lang('order.refunded')</th>
                                                        <th>@lang('order.payment_method')</th>
                                                        <th>@lang('order.payment_status')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($order['transactions'] as $transaction)
                                                        <tr>
                                                            <td class="text-success">{{ $transaction->paid ?? 0 }}</td>
                                                            <td class="text-danger">{{ $transaction->refund ?? 0 }}</td>
                                                            <td>@lang('order.' . strtolower($transaction->payment_method ?? 'unknown'))</td>
                                                            <td>
                                                                <span
                                                                    class="badge bg-{{ $transaction->payment_status === 'paid'
                                                                        ? 'success'
                                                                        : ($transaction->payment_status === 'part'
                                                                            ? 'warning'
                                                                            : 'danger') }}-transparent">
                                                                    @lang('order.' . strtolower($transaction->payment_status ?? 'unknown'))
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="alert alert-info">@lang('order.no_transactions')</div>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-warning">@lang('order.transactions_not_loaded')</div>
                                @endif
                            </div>

                            <h4>@lang('order.dish_details')</h4>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>@lang('order.dish')</th>
                                        <th>@lang('order.quantity')</th>
                                        <th>@lang('order.size')</th>
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
                                    @if (!empty($order['details']) && is_iterable($order['details']))
                                        @foreach ($order['details'] as $detail)
                                            <tr>
                                                <td>
                                                    @if ($detail->dish)
                                                        {{ $detail->dish->name }}
                                                    @else
                                                        <span class="text-danger">@lang('order.dish_not_found')</span>
                                                    @endif
                                                </td>
                                                <td>{{ $detail->quantity }}</td>
                                                @if ($detail->dishSize)
                                                    <td>{{ app()->getLocale() == 'ar' ? $detail->dishSize->size_name_ar : $detail->dishSize->size_name_en }}
                                                    </td>
                                                @else
                                                    <td>
                                                        <span class="text-danger">@lang('order.nosize')</span>
                                                    </td>
                                                @endif
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
                                                    <input type="hidden" id="itemStatus_{{ $detail->id }}"
                                                        value="{{ $detail->status }}">
                                                    <input type="hidden" id="orderId_{{ $detail->id }}"
                                                        value="{{ $order->id }}">

                                                    @if ($detail->status !== 'completed' && $detail->status !== 'cancel')
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <button type="button"
                                                                onclick="ChangeItemOrder('{{ $detail->id }}', 'cancel')"
                                                                class="btn btn-danger btn-sm d-flex align-items-center">
                                                                <i class="ri-close-circle-line me-1"></i>@lang('order.cancel')
                                                            </button>
                                                        </div>
                                                    @endif
                                                </td>


                                                {{-- <td>
                                                        @if ($detail->status === 'pending' && (getBranchSettings($order->branch->id, 'time_cancellation') ?? 0) >= ($order->created_at->diffInMinutes(Carbon\Carbon::now()) ?? 0))
                                                            <div class="d-flex gap-2 align-items-center">
                                                                <button type="button"
                                                                    onclick="ChangeItemOrder('{{ $detail->id }}', 'cancel')"
                                                                    class="btn btn-danger btn-sm d-flex align-items-center">
                                                                    <i
                                                                        class="ri-close-circle-line me-1"></i>@lang('order.cancel')
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td> --}}
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>


                            <h4>@lang('order.order_addons')</h4>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>@lang('order.name')</th>
                                        <th>@lang('order.quantity')</th>
                                        {{-- <th>@lang('order.price')</th> --}}
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

                                            <td>
                                                {{ $addon->Addon->addons->name }}
                                            </td>


                                            <td>{{ $addon->quantity }}</td>
                                            <td>{{ $addon->price_before_tax }}</td>
                                            <td>
                                                {{ (getBranchSettings($addon->order->branch_id, 'tax_percentage') / 100) * $addon->price_before_tax }}
                                            </td>


                                            </td>
                                            <td>{{ $addon->price_after_tax }}</td>
                                            <td>
                                                <p class="form-text"> <span class="badge bg-warning-transparent">
                                                        @lang('order.' . strtolower($addon->status))</span></p>
                                            </td>
                                            <td>
                                                {{-- @if ($detail->status !== 'completed' && $detail->status !== 'cancel') --}}

                                                @if (
                                                    $addon->status === 'pending' &&
                                                        (getBranchSettings($order->branch->id, 'time_cancellation') ?? 0) >=
                                                            ($order->created_at->diffInMinutes(Carbon\Carbon::now()) ?? 0))
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
                        <!-- CANCEL MODAL -->
                        <div class="modal fade" id="cancelOrderModal" tabindex="-1"
                            aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="cancelOrderModalLabel">@lang('order.cancel_order')</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="cancelOrderForm">
                                            @csrf
                                            <input type="hidden" id="cancelOrderDetailId" name="order_detail_id">
                                            <input type="hidden" id="cancelOrderStatus" name="status">
                                            <div class="mb-3">
                                                <label for="reasonSelect" class="form-label">@lang('order.select_reason')</label>
                                                <select class="form-select" id="reasonSelect" name="reason_id" required>
                                                    <option value="">@lang('order.choose_reason')</option>
                                                    @foreach ($reasons as $reason)
                                                        <option value="{{ $reason->id }}">
                                                            {{ app()->getLocale() == 'ar' ? $reason->reason_ar : $reason->reason_en }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="cancelReason" class="form-label">@lang('order.or_write_reason')</label>
                                                <textarea class="form-control" id="cancelReason" name="reason" rows="3"></textarea>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            @lang('order.close')
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="sendCancelRequest()">
                                            @lang('order.submit')
                                        </button>
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
            const currentStatus = $('#itemStatus_' + order_detail_id).val();
            const orderId = $('#orderId_' + order_detail_id).val();

            $.ajax({
                url: '{{ route('check.payment.status', '') }}/' + orderId,
                type: 'GET',
                success: function(response) {
                    // if (response.payment_status === 'paid') {
                    //     Swal.fire(
                    //         '@lang('order.error')',
                    //         '@lang('order.cannot_cancel_paid_order_item')',
                    //         'error'
                    //     );
                    //     return;
                    // }

                    if (currentStatus === 'inprogress') {
                        Swal.fire({
                            title: '@lang('order.warning')',
                            text: '@lang('order.dishes_preparing')',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: '@lang('order.yes_proceed')',
                            cancelButtonText: '@lang('order.no_cancel')'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Set values and show the modal
                                $('#cancelOrderDetailId').val(order_detail_id);
                                $('#cancelOrderStatus').val(status);
                                $('#cancelOrderModal').modal('show');
                            }
                        });
                    } else if (currentStatus === 'pending') {
                        // For pending, cancel directly
                        $('#cancelOrderDetailId').val(order_detail_id);
                        $('#cancelOrderStatus').val(status);
                        $('#cancelOrderModal').modal('show');
                    }
                },
                error: function(xhr) {
                    Swal.fire(
                        '@lang('order.error')',
                        xhr.responseJSON?.message || 'Failed to check payment status',
                        'error'
                    );
                }
            });
        }

        function sendCancelRequest() {
            const order_detail_id = $('#cancelOrderDetailId').val();
            const status = $('#cancelOrderStatus').val();
            const reason_id = $('#reasonSelect').val();
            const reason = $('#cancelReason').val();

            if (!reason_id && !reason) {
                Swal.fire(
                    '@lang('order.error')',
                    '@lang('order.reason_required')',
                    'error'
                );
                return;
            }

            $.ajax({
                url: '{{ route('order.detail.change') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_detail_id: order_detail_id,
                    status: status,
                    reason_id: reason_id,
                    reason: reason
                },
                success: function(response) {
                    $('#cancelOrderModal').modal('hide');

                    // Reset the form
                    $('#reasonSelect').val('');
                    $('#cancelReason').val('');

                    // Update the total price display
                    if (response.total_price_after_tax) {
                        // Update the main total display
                        $('.total-price-display').text(response.total_price_after_tax);

                        // Update any other elements that might show the total
                        $('td:contains("' + response.previous_total + '")').each(function() {
                            if ($(this).text().trim() === response.previous_total.toString()) {
                                $(this).text(response.total_price_after_tax);
                            }
                        });
                    }

                    // Update the status for the canceled item
                    const statusBadge = $(`tr:has(#itemStatus_${order_detail_id}) .badge`);
                    statusBadge
                        .removeClass('bg-warning-transparent')
                        .addClass('bg-danger-transparent')
                        .text('@lang('order.cancel')');

                    // Remove the cancel button for this item
                    $(`tr:has(#itemStatus_${order_detail_id}) .btn-danger`).remove();

                    // Update the hidden status field
                    $(`#itemStatus_${order_detail_id}`).val('cancel');

                    Swal.fire(
                        '@lang('order.success')',
                        response.message || '@lang('order.status_updated')',
                        'success'
                    ).then(() => {
                        // Optional: Reload the page to ensure all updates are reflected
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire(
                        '@lang('order.error')',
                        xhr.responseJSON?.message || '@lang('order.something_went_wrong')',
                        'error'
                    );
                }
            });
        }

        function ChangeOrder(orderId, status) {
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
                    $.ajax({
                        url: '{{ route('order.change') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            order_id: orderId,
                            status: status
                        },
                        success: function(response) {
                            Swal.fire(
                                '@lang('order.success')',
                                response.message || '@lang('order.status_updated')',
                                'success'
                            );
                            location.reload();
                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON?.message || 'An error occurred';
                            Swal.fire(
                                '@lang('order.error')',
                                errorMessage,
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection
