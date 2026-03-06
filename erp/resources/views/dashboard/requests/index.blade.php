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
    <h4 class="fw-medium mb-0">@lang('requests.waiterRequests')</h4>
    <div class="ms-sm-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard.home') }}">
                        @lang('sidebar.Main')
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    @lang('requests.waiterRequests')
                </li>
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
                    <div class="card-header" style="display: flex; justify-content: space-between;">
                        <div class="card-title">@lang('requests.waiterRequests')</div>
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
                        <table class="table table-bordered text-nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">@lang('requests.type')</th>
                                    <th scope="col">@lang('requests.waitername')</th>
                                    <th scope="col">@lang('requests.table')</th>
                                    <th scope="col">@lang('requests.status')</th>
                                    <th scope="col">@lang('requests.reason')</th>
                                    <th scope="col">@lang('category.Actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $request)
                                <tr>
                                    <!-- Type Column -->
                                    <!-- Type Column -->
                                    <td>
                                        @if ($request->type == 0)
                                        @lang('requests.split')
                                        @elseif ($request->type == 1)
                                        @lang('requests.merge')
                                        @else
                                        @lang('requests.print')
                                        @endif
                                    </td>

                                    <!-- Waiter Name Column -->
                                    <td>
                                        {{ $request->employee->first_name . ' ' . $request->employee->last_name ?? 'N/A' }}
                                    </td>

                                    <!-- Table Name Column -->
                                    <td>
                                        {{ $request->tables->name ?? 'N/A' }}
                                    </td>
                                    <!-- Status Column -->
                                    <td>
                                        @if ($request->status == 0)
                                        <span class="badge bg-warning">@lang('requests.pending')</span>
                                        @elseif ($request->status == 1)
                                        <span class="badge bg-success">@lang('requests.approved')</span>
                                        @elseif ($request->status == 2)
                                        <span class="badge bg-danger">@lang('requests.rejected')</span>
                                        @endif
                                    </td>

                                    <!-- Reason Column -->
                                    <td>
                                        {{ $request->status == 2 ? $request->reason ?? 'N/A' : 'N/A' }}
                                    </td>
                                    <!-- Actions Column -->

                                    <td>
                                        <!-- Show Button -->
                                        <a href="{{ route('waiter.request.show', ['id' => $request->id]) }}"
                                            class="btn btn-info-light btn-wave show-category">
                                            @lang('category.show') <i class="ri-eye-line"></i>
                                        </a>
                                        @if ($request->status == 1)
                                        <!-- print invoice Button -->
                                        <button type="button" class="btn btn-info-light btn-wave print-btn"
                                            data-order-id="{{$request->new_order_id 
        ? (int) $request->new_order_id 
        : (int) (is_array($request->order_ids) 
            ? $request->order_ids[0] 
            : json_decode($request->order_ids, true)[0] ?? 0)}}">
                                            @lang('order.print') <i class="ri-printer-line"></i>
                                        </button>

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
        <div id="print-area" style="display: none;"></div>

    </div>
</div>
@endsection

@section('scripts')
<!-- JQUERY CDN -->
<script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

<!-- DATA-TABLES CDN -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
    $(document).on('click', '.print-btn', function() {
        var orderId = $(this).data('order-id');

        $.ajax({
            url: "{{ route('request.print.ajax') }}",
            method: 'GET',
            data: {
                order_id: orderId
            },
            success: function(response) {
                // Create a temporary container
                var $printContainer = $('<div>').css({
                    'position': 'fixed',
                    'width': '100%',
                    'height': '100%',
                    'top': '0',
                    'left': '0',
                    'z-index': '9999',
                    'background': 'white',
                    'padding': '20px',
                    'overflow': 'auto'
                }).html(response.html);

                // Add to body
                $('body').append($printContainer);

                // Wait for content to render
                setTimeout(function() {
                    // Print the document
                    window.print();

                    // Remove the container after printing
                    setTimeout(function() {
                        $printContainer.remove();
                    }, 500);
                }, 500);
            },
            error: function() {
                alert('Failed to load order details for printing.');
            }
        });
    });
</script>

@endsection