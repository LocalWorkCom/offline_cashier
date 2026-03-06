@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('order.orders')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('order.orders')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-4 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header"
                            style="
                        display: flex;
                        justify-content: space-between;">
                            <div class="card-title">@lang('order.orders')</div>
                            {{-- <button type="button" class="btn btn-primary label-btn"
                                onclick="window.location.href='{{ route('order.add') }}'">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('order.AddOrder')
                            </button> --}}


                        </div>
                        <div class="card-body">
                            @if (session('message'))
                                <div class="alert alert-solid-info alert-dismissible fade show">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endif
                            <table id="orders-table" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('order.ID')</th>
                                        <th scope="col">@lang('order.invoice_num')</th>
                                        <th scope="col">@lang('order.date')</th>
                                        <th scope="col">@lang('order.type')</th>
                                        <th scope="col">@lang('order.branch')</th>
                                        <th scope="col">@lang('order.currency_symbol')</th>
                                        <th scope="col">@lang('order.client')</th>
                                        <th scope="col">@lang('order.total_price')</th>
                                        <th scope="col">@lang('order.status')</th>
                                        <th scope="col">@lang('order.status_paid')</th>
                                        <th scope="col">@lang('order.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->invoice_number }}</td>
                                            <td>{{ $order->date }}</td>
                                            <td>
                                                <span class="badge bg-primary-transparent"> @lang('order.' . strtolower($order->type))</span>
                                            </td>
                                            <td>
                                                {{ $order->branch ? (app()->getLocale() == 'en' ? $order->branch->name_en : $order->branch->name_ar) : 'Null' }}
                                            </td>
                                            </td>
                                            <td>{{ $order->branch->country->currency_symbol ?? '-' }}
                                            </td>
                                            <td>{{ $order->responsible_person }}</td>
                                            {{-- <td>{{ $order->client->name }}</td> --}}
                                            <td>
                                                {{-- @if (optional($order->branch)->tax_apply == 1) --}}
                                                {{-- @if (optional($order->branch)->tax_application == 1) --}}
                                                {{-- {{ $order->total_price_befor_tax }}
                                                    @else --}}
                                                {{ $order->total_price_after_tax }}
                                                {{-- @endif
                                                @else
                                                    {{ $order->total_price_after_tax }}
                                                @endif --}}
                                            </td>
                                            <td>
                                                <span class="badge bg-warning-transparent">
                                                    @if ($order->latestTracking)
                                                        @lang('order.' . strtolower($order->latestTracking->order_status))
                                                    @else
                                                        @if ($order->status == 'packing')
                                                            @lang('order.readyForPickup')
                                                        @else
                                                            @lang('order.' . strtolower($order->status))
                                                        @endif
                                                    @endif
                                                </span>
                                            </td>


                                            <td>
                                                <span class="badge bg-primary-transparent"> @lang('order.' . strtolower($order->transaction ? $order->transaction->payment_status : ''))</span>
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view orders', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="{{ route('order.show', $order->id) }}"
                                                        class="btn btn-info-light btn-wave show-order">
                                                        @lang('order.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('view purchase_invoices', 'admin'))
                                                    <a href="{{ route('order.invoice', $order->id) }}"
                                                        class="btn btn-info-light btn-wave show-order">
                                                        @lang('order.print') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('change status orders', 'admin'))
                                                    @if ($order->last_status != 'completed' && $order->last_status != 'cancelled')
                                                        <div class="btn-group" role="group">
                                                            <button id="btnGroupVerticalDrop4" type="button"
                                                                class="btn btn-primary dropdown-toggle"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                @lang('order.change')

                                                            </button>
                                                            <ul class="dropdown-menu"
                                                                aria-labelledby="btnGroupVerticalDrop4">
                                                                @foreach ($order['next_status'] as $status)
                                                                    <li>
                                                                        <a onclick="ChangeOrder('{{ $order->id }}', '{{ $status }}', event)"
                                                                            data-status="{{ $order['status'] }}"
                                                                            {{-- pass current status here --}} class="dropdown-item">
                                                                            @lang('order.' . $status)
                                                                            <i class="ri-{{ $status }}-line"></i>
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>

                                                        </div>
                                                    @endif
                                                @endif


                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-4 -->
            <!-- Add this modal at the end of your Blade file, before the closing </body> tag -->

            <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelOrderModalLabel">@lang('order.cancel_order')</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="cancelOrderForm">
                                @csrf
                                <input type="hidden" id="cancelOrderId" name="order_id">
                                @if (isset($order))
                                    <input type="hidden" id="orderStatus_{{ $order->id }}"
                                        value="{{ $order->status }}">
                                @endif
                                <div class="mb-3">
                                    <label for="reasonSelect" class="form-label">@lang('order.select_reason')</label>
                                    <select class="form-select" id="reasonSelect" name="reason_id">
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
                            <button type="button" class="btn btn-primary" onclick="submitCancelOrder()">
                                @lang('order.submit')
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <!-- Existing CDN scripts -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables Initialization -->
    <script>
        $(document).ready(function() {
            $('#orders-table').DataTable({
                order: [
                    [2, 'desc']
                ], // Sort by the 3rd column (index 2, the date column)

                paging: true,
                info: true,
                responsive: true,
                dom: 'Bfrtip',
               language: {
                    info: '@lang("order.showing") _START_ @lang("order.to") _END_ @lang("order.of") _TOTAL_ @lang("order.entries")',
                    paginate: {
                        previous: '@lang("order.previous")',
                        next: '@lang("order.next")'
                    }
                }
            });
        });
    </script>

    <!-- Your existing scripts -->
    <script>
        function confirmDelete() {
            return confirm("@lang('validation.DeleteConfirm')");
        }

        function ChangeOrder(orderId, status, event) {
            const clickedElement = event.currentTarget;
            const currentStatus = $(clickedElement).data('status');

            if (status === 'cancelled') {
                $.ajax({
                    url: '{{ route('check.payment.status', '') }}/' + orderId,
                    type: 'GET',
                    success: function(response) {
                         // if (response.payment_status === 'paid') {
                    //     Swal.fire(
                    //         '@lang('order.error')',
                    //         '@lang('order.cannot_cancel_paid_order')',
                    //         'error'
                    //     );
                    //     return;
                    // }

                    // Proceed with cancellation if unpaid
                        if (currentStatus === 'inprogress') {
                            Swal.fire({
                                title: '@lang('order.warning')',
                                text: '@lang('order.dishes_preparing')',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: '@lang('order.yes_continue')',
                                cancelButtonText: '@lang('order.no_cancel')'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#cancelOrderId').val(orderId);
                                    $('#cancelOrderModal').modal('show');
                                }
                            });
                        } else {
                            $('#cancelOrderId').val(orderId);
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
            } else {
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
                                Swal.fire('@lang('order.error')', errorMessage, 'error');
                            }
                        });
                    }
                });
            }
        }

        function submitCancelOrder() {
            const orderId = $('#cancelOrderId').val();
            const selectedReasonId = $('#reasonSelect').val();
            const writtenReason = $('#cancelReason').val().trim();

            if (!selectedReasonId) {
                Swal.fire(
                    '@lang('order.error')',
                    '@lang('order.reason_required')',
                    'error'
                );
                return;
            }

            $.ajax({
                url: '{{ route('order.change') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: orderId,
                    status: 'cancelled',
                    reason_id: selectedReasonId,
                    reason: writtenReason
                },
                success: function(response) {
                    Swal.fire(
                        '@lang('order.success')',
                        response.message || '@lang('order.status_updated')',
                        'success'
                    );
                    $('#cancelOrderModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'An error occurred';
                    Swal.fire('@lang('order.error')', errorMessage, 'error');
                }
            });
        }

        $('#cancelReason').on('input', function() {
            let value = $(this).val();
            if (!value.trim()) {
                $(this).val('');
            }
        });
    </script>
@endsection
