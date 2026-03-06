@extends('layouts.master')

@section('styles')
    <!-- Add any custom styles here -->
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('requests.waiterRequests')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('order.orders')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('requests.waiterRequests')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- END PAGE HEADER -->
    <!-- APP CONTENT -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start::row-1 -->

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-md-flex d-block">
                            <div class="h5 mb-0 d-sm-flex d-block align-items-center mr-2">
                                <div>
                                    <div class="h6 fw-semibold mb-0">@lang('requests.Type'): <span class="text-primary"></span>
                                        @if ($data['waiter_request']->type == 0)
                                            @lang('requests.split')
                                        @elseif ($data['waiter_request']->type == 1)
                                            @lang('requests.merge')
                                        @else
                                            @lang('requests.print')
                                        @endif
                                    </div>
                                </div>
                                <div class="ms-sm-2 ms-0 mt-sm-0 mt-2 mr-1">
                                    <div class="h6 fw-semibold mb-0">@lang('requests.Table'): <span
                                            class="text-primary">{{ $data['waiter_request']->tables?->name_site }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="ms-auto mt-md-0 mt-2">
                                @if ($data['waiter_request']->status == 1)
                                    @lang('requests.approved')
                                @elseif ($data['waiter_request']->status == 2)
                                    @lang('requests.rejected')
                                @else
                                    <button class="btn btn-secondary me-1" data-bs-toggle="modal"
                                        data-bs-target="#rejectRequest">
                                        @lang('requests.Reject')
                                    </button>
                                    <a href="{{ route('accept.waiter.request', ['id' => $data['waiter_request']->id]) }}"
                                        class="btn btn-primary"> @lang('requests.Accept')</a>
                                @endif

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                            <p class="text-muted mb-2">{{ __('requests.waiter') }}:
                                                {{ $data['waiter_request']->employee->first_name . ' ' . $data['waiter_request']->employee->last_name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                            <p class="text-muted mb-2">{{ __('requests.client_phone') }}:
                                                {{ $data['waiter_request']->phone }}</p>

                                        </div>
                                        {{-- <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                            <p class="text-muted mb-2">{{ __('requests.cashier_name') }}:
                                                {{ $data['cashier_name'] }}</p>

                                        </div> --}}
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="row">
                                        @if ($data['coupon'])
                                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                <p class="text-muted mb-2">{{ __('requests.coupon_code') }}:
                                                    {{ $data['coupon']->code }}</p>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                                <p class="text-muted mb-2">
                                                    @lang('requests.value')
                                                    {{ $data['coupon']->value . ' ' . $data['symbole'] }}
                                                </p>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    @foreach (['success', 'error', 'warning', 'info'] as $msg)
                                        @if (session($msg))
                                            <div class="alert alert-{{ $msg }}">
                                                {{ session($msg) }}
                                            </div>
                                        @endif
                                    @endforeach

                                    <div class="table-responsive">
                                        @if (!empty($data['orders']) && is_iterable($data['orders']))
                                            @foreach ($data['orders'] as $order)
                                                <table class="table nowrap text-nowrap border mt-4">
                                                    <thead>
                                                        <span class="text-primary">@lang('requests.Table')
                                                            {{ $order->table?->name_site ?? 'N/A' }}</span>
                                                        <tr>
                                                            <th> @lang('requests.dish_name')</th>
                                                            <th> @lang('requests.quantity')</th>
                                                            <th> @lang('requests.totalbefore')</th>

                                                            <th> @lang('requests.totalafter')</th>

                                                            <th> @lang('requests.size')</th>
                                                            <th> @lang('requests.addon')</th>

                                                            @if ($data['waiter_request']->type != 2)
                                                                <th> @lang('requests.selected')</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($order->orderDetails as $detail)

                                                            <tr
                                                                class="{{ $detail->selected && $data['waiter_request']->type != 2 ? 'table-success' : '' }}">
                                                                <td>
                                                                    <div class="fw-semibold">
                                                                        {{ $detail->dish->name_site ?? 'N/A' }}
                                                                    </div>
                                                                </td>
                                                                <td>{{ $detail->quantity ?? 0 }}</td>
                                                                <td>{{ $detail->price_befor_tax ?? 0 }}</td>
                                                                <td>{{ $detail->price_after_tax ?? 0 }}</td>

                                                                <td>
                                                                    <span>
                                                                        {{ $detail->dishSize->name_site ?? ' ' }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    @if ($detail->addons_list->isEmpty())
                                                                        <span class="text-danger">@lang('requests.no_addon')</span>
                                                                    @else
                                                                        <span class="text-success">
                                                                            @foreach ($detail->addons_list as $Addon)
                                                                                {{ $Addon['name'] }} (
                                                                                @lang('requests.price'):
                                                                                {{ $Addon['price'] }})
                                                                            @endforeach
                                                                        </span>
                                                                    @endif

                                                                </td>
                                                                @if ($data['waiter_request']->type != 2)
                                                                    <td>{{ $detail->selected ? __('requests.selected') : __('requests.not_selected') }}
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endforeach
                                        @else
                                            <p class="text-danger">@lang('requests.no_orders') </p>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--End::row-1 -->
        </div>
    </div>
    <!-- END APP CONTENT -->
    <div class="modal fade" id="rejectRequest" tabindex="-1" aria-labelledby="reejectRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reejectRequestModalLabel">@lang('requests.reason_for_reject')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectRequestForm">
                        @csrf
                        <input type="hidden" id="RequestId" name="request_id" value="{{ $data['waiter_request']->id }}">
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label">@lang('requests.reason_for_reject')</label>
                            <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('order.close')</button>
                    <button type="button" class="btn btn-primary" onclick="rejectRequest()">@lang('order.submit')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Add any custom scripts here -->
    <script>
        function rejectRequest() {
            const RequestId = $('#RequestId').val();
            const reason = $('#rejectReason').val().trim(); // Trim spaces

            if (!reason) {
                Swal.fire(
                    '@lang('order.error')',
                    '@lang('order.reason_required')',
                    'error'
                );
                return;
            }

            $.ajax({
                url: '{{ route('reject.waiter.request') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    request_id: RequestId,
                    reason: reason
                },
                success: function(response) {
                    Swal.fire(
                        '@lang('order.success')',
                        response.message || '@lang('requests.rejectedrequest')',
                        'success'
                    );
                    $('#rejectRequest').modal('hide');
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

        // Prevent spaces-only input in textarea
        $('#rejectReason').on('input', function() {
            let value = $(this).val();
            if (!value.trim()) {
                $(this).val('');
            }
        });
    </script>
@endsection
