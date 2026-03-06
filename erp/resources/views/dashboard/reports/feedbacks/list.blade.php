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
        <h4 class="fw-medium mb-0">@lang('complaints.Complaints')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="" onclick="window.location.href='{{ route('reports.feedbacks.list') }}'">@lang('complaints.Complaints')</a></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-4 -->
            <div class="row mt-5 mb-3">
                <!-- Filters Section -->
                <div class="col-md-3">
                    <label for="branch-filter">@lang('complaints.Branch')</label>
                    <select id="branch-filter" class="form-select">
                        <option value="all">@lang('complaints.AllBranches')</option>
                        <!-- Dynamically fill in branch options -->
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ app()->getLocale() == 'en' ? $branch->name_en : $branch->name_ar }}</option>
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
                    <label for="rating-filter">@lang('complaints.AverageRating')</label>
                    <select id="rating-filter" class="form-select">
                        <option value="all">@lang('complaints.AllRatings')</option>
                        <option value="1">@lang('complaints.1Star')</option>
                        <option value="2">@lang('complaints.2Stars')</option>
                        <option value="3">@lang('complaints.3Stars')</option>
                        <option value="4">@lang('complaints.4Stars')</option>
                        <option value="5">@lang('complaints.5Stars')</option>
                    </select>
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
                        <option value="solved">@lang('complaints.Solved')</option>
                        <option value="inprogress">@lang('complaints.InProgress')</option>
                        <option value="pending">@lang('complaints.Pending')</option>
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
                    <span> @lang('complaints.Total') @lang('complaints.Pending'): {{$pendingCount}}</span><br>
                    <span> @lang('complaints.Total') @lang('complaints.InProgress'): {{$inprogressCount}}</span><br>
                    <span> @lang('complaints.Total') @lang('complaints.Solved'): {{$solvedCount}}</span>
                </div>

                <div class="col-md-3">
                    <label for="order-filter">@lang('complaints.OrderNum')</label>
                    <input type="text" id="order-filter" class="form-control" placeholder="@lang('complaints.OrderNum')">
                </div>

                <div class="col-md-3">
                    <label for="invoice-filter">@lang('complaints.InvoiceNum')</label>
                    <input type="text" id="invoice-filter" class="form-control" placeholder="@lang('complaints.InvoiceNum')">
                </div>

                <div class="col-md-3">
                    <label for="phone-filter">@lang('complaints.Phone')</label>
                    <input type="text" id="phone-filter" class="form-control" placeholder="@lang('complaints.Phone')">
                </div>

                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button id="reset-filters" class="btn btn-danger w-100">
                        <i class="ri-refresh-line"></i> @lang('complaints.ResetFilters')
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">@lang('complaints.Complaints')</div>
                        </div>
                        <div id="table-container" class="card-body">
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
                                    <th scope="col">@lang('complaints.Phone')</th>
                                    <th scope="col">@lang('complaints.Branch')</th>
                                    <th scope="col">@lang('complaints.Rate')</th>
                                    <th scope="col">@lang('complaints.OrderNum')</th>
                                    <th scope="col">@lang('complaints.InvoiceNum')</th>
                                    <th scope="col">@lang('complaints.Status')</th>
                                    <th scope="col">@lang('complaints.Date')</th>
                                    <th scope="col">@lang('category.Actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($complaints != null)
                                    @foreach ($complaints as $complaint)
                                        <tr data-branch="{{ $complaint['branch']['id'] ?? '' }}"
                                            data-rate="{{ $complaint['rate'] ?? 0 }}"
                                            data-order-type="{{ $complaint['order_type'] ?? '' }}"
                                            data-status="{{ $complaint['status'] ?? '' }}"
                                            data-manage="{{ $complaint['manage'] ?? '' }}"
                                            data-date="{{ $complaint['created_at'] }}">
                                            
                                            <td>{{ $complaint['id'] }}</td>
                                            <td>{{ $complaint['name'] }}</td>
                                            <td>{{ $complaint['phone'] }}</td>
                                            <td>
                                                {{ app()->getLocale() == 'en' 
                                                    ? ($complaint['branch']['name_en'] ?? '') 
                                                    : ($complaint['branch']['name_ar'] ?? '') }}
                                            </td>
                                            <td>{{ $complaint['rate'] ?? 0 }}</td>
                                            <td>{{ $complaint['order_num'] ?? '' }}</td>
                                            <td>{{ $complaint['invoice_number'] ?? '' }}</td>
                                            <td>
                                                @if($complaint['status'] == 'solved')
                                                    @lang('complaints.Solved')
                                                @elseif($complaint['status'] == 'inprogress')
                                                    @lang('complaints.InProgress')
                                                @else
                                                    @lang('complaints.Pending')
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($complaint['created_at'])->format('Y-m-d') }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view complaints', 'admin'))
                                                    <a href="{{ route('reports.feedbacks.show', $complaint['id']) }}" class="btn btn-info-light btn-wave">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>

                                    @endforeach
                                @endif
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
                const selectedRating = $('#rating-filter').val();
                const selectedType = $('#type-filter').val();
                const selectedStatus = $('#status-filter').val();
                const selectedManage = $('#manage-filter').val();
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();
                const orderNum = $('#order-filter').val().toLowerCase();
                const phoneNum = $('#phone-filter').val().toLowerCase();
                const invoiceNum = $('#invoice-filter').val().toLowerCase();

                $('#file-export tbody tr').each(function() {
                    const rowBranch = $(this).data('branch');
                    const rowRate = $(this).data('rate');
                    const rowStatus = $(this).data('status');
                    const rowManage = $(this).data('manage');
                    const rowType = $(this).data('order-type');
                    const rowDate = $(this).data('date');
                    const rowOrderNum = $(this).find('td:eq(5)').text().toLowerCase();
                    const rowPhone = $(this).find('td:eq(2)').text().toLowerCase();
                    const rowInvoiceNum = $(this).find('td:eq(6)').text().toLowerCase();

                    const branchMatch = selectedBranch === 'all' || rowBranch == selectedBranch;
                    const ratingMatch = selectedRating === 'all' || rowRate == selectedRating;
                    const typeMatch = selectedType === 'all' || rowType === selectedType;
                    const statusMatch = selectedStatus === 'all' || rowStatus === selectedStatus;
                    const manageMatch = selectedManage === 'all' || rowManage === selectedManage;
                    const dateMatch = (!dateFrom || rowDate >= dateFrom) && (!dateTo || rowDate <= dateTo);
                    const orderMatch = !orderNum || rowOrderNum.includes(orderNum);
                    const phoneMatch = !phoneNum || rowPhone.includes(phoneNum);
                    const invoiceMatch = !invoiceNum || rowInvoiceNum.includes(invoiceNum);

                    if (branchMatch && ratingMatch && typeMatch && statusMatch && manageMatch &&
                        dateMatch && orderMatch && phoneMatch && invoiceMatch) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            // Add all filter inputs to the event listeners
            $('#branch-filter, #rating-filter, #type-filter, #status-filter, #manage-filter, #date-from, #date-to').on('change', filterTable);
            $('#order-filter, #phone-filter, #invoice-filter').on('keyup', filterTable);

            // Reset all filters
            $('#reset-filters').on('click', function() {
                $('#branch-filter, #rating-filter, #type-filter, #status-filter, #manage-filter').val('all');
                $('#date-from, #date-to, #order-filter, #phone-filter, #invoice-filter').val('');
                filterTable();
            });

            // Initial filter
            filterTable();
        });
    </script>
@endsection
