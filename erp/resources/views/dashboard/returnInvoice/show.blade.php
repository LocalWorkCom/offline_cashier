@extends('layouts.master')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link {
        margin-top: 50px;
        margin-right: 10px;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
        }

        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('einvoice.show')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="window.location.href='{{ route('return-invoice.index') }}'">
                            @lang('sidebar.returnInvoice')
                        </a>
                    </li>
                </ol>
                
            </nav>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                <h4>@lang('einvoice.invoice_details') - {{ $invoice->invoice_num }}</h4>
                            </div>
                            @if($invoice->parent_id)
                                <div>
                                    <a href="{{ route('invoice.show', $invoice->parent_id) }}" 
                                       class="btn btn-primary-light btn-wave">
                                        <i class="ri-file-text-line"></i> @lang('einvoice.view_original_invoice')
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="card-body">
                            <!-- Basic Invoice Information -->
                            <div class="invoice-details-card">
                                <div class="invoice-details-header">
                                    <h5>@lang('einvoice.basic_info')</h5>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('einvoice.invoice_num')</label>
                                        <p class="form-text">{{ $invoice->invoice_num }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('einvoice.status')</label>
                                        <p class="form-text">@lang('einvoice.' . $invoice->status)</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('einvoice.date')</label>
                                        <p class="form-text">{{ $invoice->date }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('einvoice.time')</label>
                                        <p class="form-text">{{ $invoice->time }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Information -->
                            <div class="invoice-details-card">
                                <div class="invoice-details-header">
                                    <h5>@lang('einvoice.financial_info')</h5>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('einvoice.total_before_tax')</label>
                                        <p class="form-text">{{ $invoice->total_before_tax }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('einvoice.tax') (@lang('einvoice.percentage')
                                            {{ $invoice->tax_percentage }}%)</label>
                                        <p class="form-text">{{ $invoice->tax }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('einvoice.service_fees') (@lang('einvoice.percentage')
                                            {{ $invoice->service_percentage }}%)</label>
                                        <p class="form-text">{{ $invoice->service_fees }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('einvoice.total_after_tax')</label>
                                        <p class="form-text">{{ $invoice->total_after_tax }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('einvoice.delivery_fees')</label>
                                        <p class="form-text">{{ $invoice->delivery_fees }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('einvoice.coupon_value')</label>
                                        <p class="form-text">{{ $invoice->coupon_value }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Information -->
                            <div class="invoice-details-card">
                                <div class="invoice-details-header">
                                    <h5>@lang('einvoice.order_info')</h5>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('einvoice.order_number')</label>
                                        <p class="form-text">{{ $invoice->orders->order_number }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('einvoice.order_status')</label>
                                        <p class="form-text">@lang('einvoice.' . $invoice->orders->status)</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('einvoice.order_type')</label>
                                        <p class="form-text">@lang('einvoice.' . $invoice->orders->type)</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('einvoice.branch')</label>
                                        <p class="form-text">{{ $invoice->orders->branch->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <div class="invoice-details-card">
                                <div class="invoice-details-header">
                                    <h5>@lang('einvoice.invoice_items')</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>@lang('einvoice.item_type')</th>
                                                <th>@lang('einvoice.item_name')</th>
                                                <th>@lang('einvoice.quantity')</th>
                                                <th>@lang('einvoice.price')</th>
                                                <th>@lang('einvoice.tax')</th>
                                                <th>@lang('einvoice.service_fees')</th>
                                                <th>@lang('einvoice.total')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoice->invoiceDetails as $detail)
                                                <tr>
                                                    <td>@lang('einvoice.' . $detail->type)</td>
                                                    <td>
                                                        @if ($detail->type == 'dish')
                                                            @php
                                                                $orderDetail = $invoice->orders->orderDetails->firstWhere(
                                                                    'id',
                                                                    $detail->details_id,
                                                                );
                                                            @endphp
                                                            {{ $orderDetail->dish->name ?? 'N/A' }}
                                                        @else
                                                            @php
                                                                $addon = $invoice->orders->orderAddons->firstWhere(
                                                                    'id',
                                                                    $detail->details_id,
                                                                );
                                                            @endphp
                                                            {{ $addon->addons->name ?? 'N/A' }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $detail->quantity }}</td>
                                                    <td>{{ $detail->total_before_tax / $detail->quantity }}</td>
                                                    <td>{{ $detail->tax }}</td>
                                                    <td>{{ $detail->service_fees }}</td>
                                                    <td>{{ $detail->total_after_tax }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <th colspan="6" class="text-end">@lang('einvoice.subtotal')</th>
                                                <th>{{ $invoice->total_before_tax }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="6" class="text-end">@lang('einvoice.tax')
                                                    ({{ $invoice->tax_percentage }}%)</th>
                                                <th>{{ $invoice->tax }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="6" class="text-end">@lang('einvoice.service_fees')
                                                    ({{ $invoice->service_percentage }}%)</th>
                                                <th>{{ $invoice->service_fees }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="6" class="text-end">@lang('einvoice.total')</th>
                                                <th>{{ $invoice->total_after_tax }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="invoice-details-card">
                                <div class="invoice-details-header">
                                    <h5>@lang('einvoice.additional_info')</h5>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">@lang('einvoice.note')</label>
                                        <p class="form-text">{{ $invoice->note ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Initialize any necessary scripts here
        });
    </script>
@endsection
