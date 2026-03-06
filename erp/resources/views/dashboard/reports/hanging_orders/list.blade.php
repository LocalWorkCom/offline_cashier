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
        <h4 class="fw-medium mb-0">@lang('complaints.HangingOrdersDeliveries')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href=""
                            onclick="window.location.href='{{ route('hanging-orders.list') }}'">@lang('complaints.HangingOrdersDeliveries')</a></li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-4 -->
            <!-- Start:: row-4 -->
            <div class="row mt-5 mb-3">
                <!-- Filters Section -->
                <div class="col-md-3">
                    <label for="branch-filter">@lang('complaints.Branch')</label>
                    <select id="branch-filter" class="form-select">
                        <option value="all">@lang('complaints.AllBranches')</option>
                        <!-- Dynamically fill in branch options -->
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date-from">@lang('complaints.DateFrom')</label>
                    <input type="date" id="date-from" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="date-to">@lang('complaints.DateTo')</label>
                    <input type="date" id="date-to" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="type-filter">@lang('complaints.Type')</label>
                    <select id="type-filter" class="form-select">
                        <option value="all">@lang('complaints.AllTypes')</option>
                        <option value="CallCenter">@lang('complaints.CallCenter')</option>
                        <option value="Takeaway">@lang('complaints.Takeaway')</option>
                        <option value="Delivery">@lang('complaints.DeliveryOrder')</option>
                        <option value="Online">@lang('complaints.Online')</option>
                        <option value="dine-in">@lang('complaints.DineIn')</option>
                        <option value="reservation-table">@lang('complaints.ReservationTable')</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status-filter">@lang('complaints.Status')</label>
                    <select id="status-filter" class="form-select">
                        <option value="all">@lang('complaints.AllStatuses')</option>
                        <option value="done">@lang('complaints.Solved')</option>
                        <option value="hold">@lang('complaints.hold')</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="manage-filter">@lang('complaints.ManagedBy')</label>
                    <select id="manage-filter" class="form-select">
                        <option value="all">@lang('complaints.All')</option>
                        <option value="admin">@lang('complaints.Admin')</option>
                        <option value="customer_service">@lang('complaints.CallCenter')</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="order-search">@lang('complaints.OrderNum')</label>
                    <input type="text" id="order-search" class="form-control" placeholder="@lang('complaints.SearchOrderNum')">
                </div>

                <div class="col-md-3">
                    <label for="invoice-search">@lang('complaints.InvoiceNum')</label>
                    <input type="text" id="invoice-search" class="form-control" placeholder="@lang('complaints.SearchInvoiceNum')">
                </div>

                <div class="col-md-3">
                    <label for="delivery-name-search">@lang('complaints.Delivery')</label>
                    <input type="text" id="delivery-name-search" class="form-control" placeholder="@lang('complaints.SearchDeliveryName')">
                </div>

                <div class="col-md-3">
                    <label for="delivery-phone-search">@lang('complaints.DeliveryPhone')</label>
                    <input type="text" id="delivery-phone-search" class="form-control" placeholder="@lang('complaints.SearchDeliveryPhone')">
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header"
                            style="
                        display: flex;
                        justify-content: space-between;">
                            <div class="card-title">@lang('complaints.HangingOrdersDeliveries')</div>

                            {{--                            @if (auth('admin')->user()->hasPermissionTo('create returns', 'admin')) --}}

                            {{--                            <button type="button" class="btn btn-primary label-btn" --}}
                            {{--                                onclick="window.location.href='{{ route('hanging-order.create') }}'"> --}}
                            {{--                                <i class="fe fe-plus label-btn-icon me-2"></i> --}}
                            {{--                                @lang('complaints.AddHangingOrderDelivery') --}}
                            {{--                            </button> --}}
                            {{--                            @endif --}}

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
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('category.ID')</th>
                                        <th scope="col">@lang('complaints.Client')</th>
                                        <th scope="col">@lang('complaints.ClientPhone')</th>
                                        <th scope="col">@lang('complaints.Delivery')</th>
                                        <th scope="col">@lang('complaints.DeliveryPhone')</th>
                                        <th scope="col">@lang('complaints.Branch')</th>
                                        <th scope="col">@lang('complaints.OrderNum')</th>
                                        <th scope="col">@lang('complaints.InvoiceNum')</th>
                                        <th scope="col">@lang('complaints.Status')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rowNumber = 1; @endphp
                                    @foreach ($complaints as $complaint)
                                        <tr data-branch="{{ $complaint->order?->branch_id }}"
                                            data-order-type="{{ $complaint->order?->type }}"
                                            data-status="{{ $complaint->status }}" data-manage="{{ $complaint->manage }}"
                                            data-date="{{ $complaint->created_at }}">
                                            
                                            <td>{{ $complaint->id }}</td>
                                            {{--                                        <td>{{ $rowNumber++  }}</td> --}}
                                            {{--                                        <td> --}}
                                            {{--                                            <span class="badge {{ $complaint->active ? 'bg-success' : 'bg-danger' }}"> --}}
                                            {{--                                                {{ $complaint->active ? __('term.Active') : __('term.Inactive') }} --}}
                                            {{--                                            </span> --}}
                                            {{--                                        </td> --}}
                                            <td>{{ $complaint->client_name }}</td>
                                            <td>{{ $complaint->client_phone }}</td>
                                            <td>{{ $complaint->delivery_name }}</td>
                                            <td>{{ $complaint->delivery_phone }}</td>
                                            <td>{{ $complaint->branch }}</td>
                                            <td>{{ $complaint->order_num }}</td>
                                            <td>{{ $complaint->order?->invoice_number }}</td>
                                            <td>
                                                @if ($complaint->status == 'done')
                                                    @lang('complaints.Solved')
                                                @else
                                                    @lang('complaints.hold')
                                                @endif
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('detail report_hanging_orders', 'admin'))
                                                    <a href="{{ route('reports.hanging-order.show', $complaint->id) }}"
                                                        class="btn btn-info-light btn-wave">@lang('category.show') <i
                                                            class="ri-eye-line"></i></a>
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

        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- DATA-TABLES CDN -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

    <!-- INTERNAL DATADABLES JS -->
    <script>
        $(document).ready(function() {
            const table = $('#file-export').DataTable({
                paging: true,
                ordering: true,
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
            });

            function filterTable() {
                const selectedBranch = $('#branch-filter').val();
                const selectedType = $('#type-filter').val();
                const selectedStatus = $('#status-filter').val();
                const selectedManage = $('#manage-filter').val();
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();
                const orderSearch = $('#order-search').val().toLowerCase();
                const invoiceSearch = $('#invoice-search').val().toLowerCase();
                const deliveryNameSearch = $('#delivery-name-search').val().toLowerCase();
                const deliveryPhoneSearch = $('#delivery-phone-search').val().toLowerCase();

                $('#file-export tbody tr').each(function() {
                    const rowBranch = $(this).data('branch');
                    const rowStatus = $(this).data('status');
                    const rowManage = $(this).data('manage');
                    const rowType = $(this).data('order-type');
                    const rowDate = $(this).data('date');

                    // Get text from columns
                    const rowOrderNum = $(this).find('td:eq(6)').text().toLowerCase();
                    const rowInvoiceNum = $(this).find('td:eq(7)').text().toLowerCase();
                    const rowDeliveryName = $(this).find('td:eq(3)').text().toLowerCase();
                    const rowDeliveryPhone = $(this).find('td:eq(4)').text().toLowerCase();

                    const branchMatch = selectedBranch === 'all' || rowBranch == selectedBranch;
                    const typeMatch = selectedType === 'all' || rowType === selectedType;
                    const statusMatch = selectedStatus === 'all' || rowStatus === selectedStatus;
                    const manageMatch = selectedManage === 'all' || rowManage === selectedManage;
                    const dateMatch = (!dateFrom || rowDate >= dateFrom) && (!dateTo || rowDate <= dateTo);
                    const orderMatch = !orderSearch || rowOrderNum.includes(orderSearch);
                    const invoiceMatch = !invoiceSearch || rowInvoiceNum.includes(invoiceSearch);
                    const deliveryNameMatch = !deliveryNameSearch || rowDeliveryName.includes(
                        deliveryNameSearch);
                    const deliveryPhoneMatch = !deliveryPhoneSearch || rowDeliveryPhone.includes(
                        deliveryPhoneSearch);

                    if (branchMatch && typeMatch && statusMatch && manageMatch && dateMatch &&
                        orderMatch && invoiceMatch && deliveryNameMatch && deliveryPhoneMatch) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            // Add all filter inputs to the event listeners
            $('#branch-filter, #type-filter, #status-filter, #manage-filter, #date-from, #date-to, #order-search, #invoice-search, #delivery-name-search, #delivery-phone-search')
                .on('change keyup', function() {
                    filterTable();
                });

            filterTable();
        });
    </script>
@endsection
