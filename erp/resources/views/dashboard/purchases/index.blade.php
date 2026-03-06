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
        <h4 class="fw-medium mb-0">@lang('purchase.purchases')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('purchase.purchases')</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">@lang( 'purchase.purchases')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create purchase_invoices', 'admin'))

                            <a href="{{ route('purchase.create') }}" type="button" class="btn btn-primary label-btn">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('purchase.addPurchase')
                            </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('purchase.ID')</th>
                                        <th scope="col">@lang('purchase.date')</th>
                                        <th scope="col">@lang('purchase.invoiceNumber')</th>
                                        <th scope="col">@lang('purchase.vendor')</th>
                                        <th scope="col">@lang('purchase.type')</th>
                                        <th scope="col">@lang('purchase.products')</th>
                                        <th scope="col">@lang('purchase.totalPrice')</th>
                                        <th scope="col">@lang('purchase.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchases as $purchase)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $purchase->Date }}</td>
                                            <td>{{ $purchase->invoice_number }}</td>
                                            <td>{{ $purchase->vendor->name_ar . ' | ' . $purchase->vendor->name_en }}</td>
                                            <td>
                                                {{ $purchase->type === 0
                                                    ? (app()->getLocale() === 'ar'
                                                        ? 'شراء'
                                                        : 'Purchase')
                                                    : (app()->getLocale() === 'ar'
                                                        ? 'استرجاع'
                                                        : 'Refund') }}
                                            </td>
                                            <td>
                                                <ul>
                                                    @foreach ($purchase->purchaseInvoicesDetails as $detail)
                                                        <li>
                                                            {{ $detail->product->name_ar . ' | ' . $detail->product->name_en }}
                                                            -
                                                            @lang('purchase.price'): {{ $detail->price }},
                                                            @lang('purchase.quantity'): {{ $detail->quantity }},
                                                            @lang('purchase.totalPrice'): {{ $detail->total_price }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                            <td>{{ $purchase->purchaseInvoicesDetails->sum('total_price') }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view purchase_invoices', 'admin'))

                                                <!-- Show Button -->
                                                <a href="{{ route('purchase.show', $purchase->id) }}"
                                                    class="btn btn-info-light btn-wave">
                                                    @lang('purchase.show') <i class="ri-eye-line"></i>
                                                </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update purchase_invoices', 'admin'))

                                                <!-- Edit Button -->
                                                <a href="{{ route('purchase.edit', $purchase->id) }}"
                                                    class="btn btn-orange-light btn-wave">
                                                    @lang('purchase.edit') <i class="ri-edit-line"></i>
                                                </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete purchase_invoices', 'admin'))

                                                <!-- Delete Button -->
                                                <form class="d-inline" id="delete-form-{{ $purchase->id }}"
                                                    action="{{ route('purchase.delete', $purchase->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" onclick="delete_item({{ $purchase->id }})"
                                                        class="btn btn-danger-light btn-wave">
                                                        @lang('purchase.delete') <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('print purchase_invoices', 'admin'))

                                                <!-- Print Button -->
                                                <a href="{{ route('purchase.showInvoice', $purchase->id) }}"
                                                    class="btn btn-success-light btn-wave">
                                                    @lang('purchase.print') <i class="bx bx-receipt"></i>
                                                </a>
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
            <!-- End:: row -->
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function delete_item(id) {
            Swal.fire({
                title: "@lang('purchase.warning')",
                text: "@lang('purchase.deleteMsg')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "@lang('purchase.yesDelete')",
                cancelButtonText: "@lang('purchase.cancelDelete')",
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = document.getElementById('delete-form-' + id);
                    form.submit();
                }
            });
        }
    </script>
@endsection
