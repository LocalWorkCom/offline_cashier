@extends('layouts.master')
@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">Invoice Details</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Invoice</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Invoice Details</li>
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
                    <div class="card custom-card printable-area">
                        <div class="card-header d-md-flex d-block">
                            <div class="h5 mb-0 d-sm-flex d-block align-items-center">
                                <div>
                                    <img src="{{ asset('build/assets/images/brand-logos/toggle-logo.png') }}"
                                        alt="">
                                </div>
                                <div class="ms-sm-2 ms-0 mt-sm-0 mt-2">
                                    <div class="h6 fw-semibold mb-0">PURCHASE INVOICE : <span
                                            class="text-primary">#{{ $purchase->id }}</span></div>
                                </div>
                            </div>
                            <div class="ms-auto mt-md-0 mt-2">
                                <button class="btn btn-secondary me-1" onclick="printInvoice({{ $purchase->id }})">Print<i
                                        class="ri-printer-line ms-1 align-middle d-inline-flex"></i></button>
                                <button class="btn btn-primary">Save As PDF<i
                                        class="ri-file-pdf-line ms-1 align-middle d-inline-flex"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Your content inside the printable area -->
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                            <p class="text-muted mb-2">Billing From :</p>
                                            <p class="fw-bold mb-1">
                                                {{ $purchase->vendor->name_en . ' | ' . $purchase->vendor->name_ar }}
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ $purchase->vendor->address_en . ' | ' . $purchase->vendor->address_ar }}
                                            </p>
                                            <p class="mb-1 text-muted">{{ $purchase->vendor->email }}</p>
                                            <p class="mb-1 text-muted">{{ $purchase->vendor->phone }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <p class="fw-semibold text-muted mb-1">Invoice ID :</p>
                                    <p class="fs-15 mb-1">{{ $purchase->invoice_number }}</p>
                                </div>
                                <div class="col-xl-3">
                                    <p class="fw-semibold text-muted mb-1">Date Issued :</p>
                                    <p class="fs-15 mb-1">
                                        {{ \Carbon\Carbon::parse($purchase->created_at)->format('d,M Y') }} -
                                        <span
                                            class="text-muted fs-12">{{ \Carbon\Carbon::parse($purchase->created_at)->format('h:i A') }}</span>
                                    </p>
                                </div>
                                <div class="col-xl-3">
                                    <p class="fw-semibold text-muted mb-1">Due Amount :</p>
                                    <p class="fs-16 mb-1 fw-semibold">
                                        ${{ $purchase->purchaseInvoicesDetails->sum('total_price') }}
                                    </p>
                                </div>
                                <div class="col-xl-12">
                                    <div class="table-responsive">
                                        <table class="table nowrap text-nowrap border mt-4">
                                            <thead>
                                                <tr>
                                                    <th>Product NAME</th>
                                                    <th>QUANTITY</th>
                                                    <th>PRICE PER UNIT</th>
                                                    <th>TOTAL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($purchase->purchaseInvoicesDetails as $detail)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">
                                                                {{ $detail->product->name_ar . ' | ' . $detail->product->name_en }}
                                                            </div>
                                                        </td>
                                                        <td class="product-quantity-container">
                                                            {{ $detail->quantity }}
                                                        </td>
                                                        <td>${{ $detail->price }}</td>
                                                        <td>${{ $detail->total_price }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <td colspan="3">
                                                        <table class="table table-sm text-nowrap mb-0 table-borderless">
                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row">
                                                                        <p class="mb-0 fs-14">Total :</p>
                                                                    </th>
                                                                    <td>
                                                                        <p class="mb-0 fw-semibold fs-16 text-success">
                                                                            ${{ $purchase->purchaseInvoicesDetails->sum('total_price') }}
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-success">Download <i
                                    class="ri-download-2-line ms-1 align-middle"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <!--End::row-1 -->
        </div>
    </div>
    <!-- END APP CONTENT -->
@endsection
<script>
    function printInvoice(id) {
        const url = `{{ route('purchase.print', ':id') }}`.replace(':id', id);
        const printWindow = window.open(url, '_blank');
    }
</script>
