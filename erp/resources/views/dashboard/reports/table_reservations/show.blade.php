@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('reservations.Show_table_reservations')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('reports.table_reservations.list') }}">@lang('reservations.table_reservations')</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('reservations.Show_table_reservations')</li>
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
                        <div class="card-header">
                            <div class="card-title">@lang('reservations.Show_table_reservations')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.TableID')</label>
                                    <p class="form-text">{{ $reservation['table_id'] ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.reservation_number')</label>
                                    <p class="form-text">
                                        {{ $reservation['reservation_number'] ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Order_Number')</label>
                                    <p class="form-text">
                                        {{ $reservation['order_number'] ?? __('reservations.NotAvailable') }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.floor_partition_id')</label>
                                    <p class="form-text">
                                        {{ app()->getLocale() == 'en' ? $reservation['floor_partition']['name_en'] ?? __('reservations.NotAvailable') : $reservation['floor_partition']['name_ar'] ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Floor_Partition_Image')</label>
                                    <div>
                                    <img src="{{ asset($reservation['floor_partition']['image']) }}" alt="Floor Partition" style="max-width: 50%; max-height: 60px;">

                                    </div>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Client')</label>
                                    <p class="form-text">{{ $reservation['name'] ?? __('reservations.NotAvailable') }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Phone')</label>
                                    <p class="form-text">{{ $reservation['phone'] ?? __('reservations.NotAvailable') }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Branch')</label>
                                    <p class="form-text">
                                        {{ app()->getLocale() == 'en' ? $reservation['branch']['name_en'] ?? __('reservations.NotAvailable') : $reservation['branch']['name_ar'] ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.date')</label>
                                    <p class="form-text">
                                        {{ $reservation['date']->format('Y-m-d') ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Time From')</label>
                                    <p class="form-text">
                                        {{ isset($reservation['time_from']) ? \Carbon\Carbon::parse($reservation['time_from'])->format('h:i:s A') : __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Time To')</label>
                                    <p class="form-text">
                                        {{ isset($reservation['time_to']) ? \Carbon\Carbon::parse($reservation['time_to'])->format('h:i:s A') : __('reservations.NotAvailable') }}
                                    </p>
                                </div>


                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Confirmed Date')</label>
                                    <p class="form-text">
                                        {{ $reservation['confirmed_date'] ? \Carbon\Carbon::parse($reservation['confirmed_date'])->format('Y-m-d') : __('reservations.NotAvailable') }}
                                    </p>

                                </div>
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Confirmed by')</label>
                                    <p class="form-text">
                                        {{ $reservation['confirmed_by'] ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Confirmed Time')</label>
                                    <p class="form-text">
                                        {{ isset($reservation['confirmed_time']) ? \Carbon\Carbon::parse($reservation['confirmed_time'])->format('h:i:s A') : __('reservations.NotAvailable') }}
                                    </p>
                                </div>


                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Reservation Type')</label>
                                    <p class="form-text">
                                        {{ isset($reservation['reservation_type']) ? ($reservation['reservation_type'] == 'with' ? __('reservations.WithMeal') : __('reservations.WithoutMeal')) : __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Adults')</label>
                                    <p class="form-text">{{ $reservation['adult'] ?? __('reservations.NotAvailable') }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Kids')</label>
                                    <p class="form-text">{{ $reservation['kids'] ?? __('reservations.NotAvailable') }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.men')</label>
                                    <p class="form-text">{{ $reservation['men'] ?? __('reservations.NotAvailable') }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.women')</label>
                                    <p class="form-text">{{ $reservation['women'] ?? __('reservations.NotAvailable') }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Personal Type')</label>

                                    <p class="form-text">
                                        {{ isset($reservation['personal_type']) ? ($reservation['personal_type'] == 'family' ? __('reservations.family') : __('reservations.person')) : __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.paid')</label>
                                    <p class="form-text">
                                        {{ $reservation['table_reservation_transaction']['paid'] ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div> 

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.refund')</label>
                                    <p class="form-text">
                                        {{ $reservation['table_reservation_transaction']['refund'] ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.cancellation_reason')</label>
                                    <p class="form-text">
                                        {{ $reservation['cancellation_reason'] ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div>

                                {{-- <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.reason')</label>
                                    <p class="form-text">
                                        @if (isset($reservation['table_reservation_transaction']))
                                            {{ $reservation['table_reservation_transaction']['reason'] ?? __('reservations.NotAvailable') }}
                                        @else
                                            {{ __('reservations.NotAvailable') }}
                                        @endif
                                    </p>
                                </div> --}}

                                {{-- <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.payment_gateway_status')</label>
                                    <p class="form-text">
                                        {{ $reservation['table_reservation_transaction']['payment_gateway_status'] ?? __('reservations.NotAvailable') }}
                                    </p>
                                </div> --}}

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Status')</label>
                                    <p class="form-text">
                                        @if (isset($reservation['status']))
                                            @if ($reservation['status'] == 'confirm')
                                                @lang('reservations.Confirmed')
                                            @else
                                                @lang('reservations.cancelled')
                                            @endif
                                        @endif
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.Confirmed')</label>
                                    <p class="form-text">
                                        @if (isset($reservation['confirmed']))
                                            @if ($reservation['confirmed'] == 1)
                                                @lang('reservations.Pending')
                                            @elseif($reservation['confirmed'] == 2)
                                                @lang('reservations.Confirmed')
                                            @else
                                                @lang('reservations.Rejected')
                                            @endif
                                        @else
                                            @lang('reservations.NotAvailable')
                                        @endif
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.payment_status')</label>
                                    <p class="form-text">
                                        @if (isset($reservation['table_reservation_transaction']['payment_status']))
                                            @if ($reservation['table_reservation_transaction']['payment_status'] == 'paid')
                                                @lang('reservations.paid')
                                            @elseif($reservation['table_reservation_transaction']['payment_status'] == 'unpaid')
                                                @lang('reservations.unpaid')
                                            @elseif($reservation['table_reservation_transaction']['payment_status'] == 'part')
                                                @lang('reservations.part')
                                            @else
                                                @lang('reservations.payment_failed')
                                            @endif
                                        @else
                                            @lang('reservations.NotAvailable')
                                        @endif
                                    </p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('reservations.payment_method')</label>
                                    <p class="form-text">
                                        @if (isset($reservation['table_reservation_transaction']['payment_method']))
                                            @if ($reservation['table_reservation_transaction']['payment_method'] == 'cash')
                                                @lang('reservations.cash')
                                            @elseif($reservation['table_reservation_transaction']['payment_method'] == 'credit_card')
                                                @lang('reservations.credit_card')
                                            @else
                                                @lang('reservations.online')
                                            @endif
                                        @else
                                            @lang('reservations.NotAvailable')
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer border-top-0">
                            <div class="btn-list float-end">
                                <button type="button" class="d-inline-flex btn btn-primary btn-wave print-button"
                                    onclick="printFormattedReport({{ json_encode($reservation) }})">
                                    <i class="ri-printer-line me-1 align-middle"></i>@lang('reservations.print')
                                </button>

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
@endsection
<script>
    function printFormattedReport(reservation) {
        if (!reservation) {
            alert("No reservation data available.");
            return;
        }

        // Create a new window for printing
        const printWindow = window.open('', '_blank');

        // Language detection
        const lang = document.documentElement.lang || 'en';

        // Translations
        const translations = {
            en: {
                reportTitle: "Table Reservation Report",
                tableID: "Table ID",
                reservationNumber: "Reservation Number",
                orderNumber: "Order Number",
                floor_partition_id: "Floor Partition",
                floorPartitionImage: "Floor Partition Image",
                client: "Client",
                phone: "Phone",
                branch: "Branch",
                date: "Date",
                timeFrom: "Time From",
                timeTo: "Time To",
                confirmedDate: "Confirmed Date",
                confirmedBy: "Confirmed by",
                confirmedTime: "Confirmed Time",
                reservationType: "Reservation Type",
                withMeal: "With Meal",
                withoutMeal: "Without Meal",
                adults: "Adults",
                kids: "Kids",
                men: "Men",
                women: "Women",
                personalType: "Personal Type",
                family: "Family",
                person: "Person",
                refund: "Refund",
                cancellationReason: "Cancellation Reason",
                status: "Status",
                confirmed: "Confirmed",
                pending: "Pending",
                confirmedStatus: "Confirmed",
                rejected: "Rejected",
                paymentStatus: "Payment Status",
                paid: "Paid",
                unpaid: "Unpaid",
                part: "Part",
                paymentFailed: "Payment Failed",
                paymentMethod: "Payment Method",
                cash: "Cash",
                creditCard: "Credit Card",
                online: "Online",
                notAvailable: "Not Available",
                cancelled: "Cancelled"
            },
            ar: {
                reportTitle: "تقرير حجز الطاولات",
                tableID: "رقم الطاولة",
                reservationNumber: "رقم الحجز",
                orderNumber: "رقم الطلب",
                floor_partition_id: "منطقة الدور",
                floorPartitionImage: "صورة منطقة الدور",
                client: "العميل",
                phone: "الهاتف",
                branch: "الفرع",
                date: "التاريخ",
                timeFrom: "وقت البدء",
                timeTo: "وقت الانتهاء",
                confirmedDate: "تاريخ التأكيد",
                confirmedBy: "تم التأكيد بواسطة",
                confirmedTime: "وقت التأكيد",
                reservationType: "نوع الحجز",
                withMeal: "مع وجبة",
                withoutMeal: "بدون وجبة",
                adults: "البالغون",
                kids: "الأطفال",
                men: "الرجال",
                women: "النساء",
                personalType: "نوع الشخص",
                family: "عائلة",
                person: "فرد",
                refund: "المبلغ المسترد",
                cancellationReason: "سبب الإلغاء",
                status: "الحالة",
                confirmed: "تم التأكيد",
                pending: "قيد الانتظار",
                confirmedStatus: "تم التأكيد",
                rejected: "مرفوض",
                paymentStatus: "حالة الدفع",
                paid: "مدفوع",
                unpaid: "غير مدفوع",
                part: "جزئي",
                paymentFailed: "فشل الدفع",
                paymentMethod: "طريقة الدفع",
                cash: "نقداً",
                creditCard: "بطاقة ائتمان",
                online: "عبر الإنترنت",
                notAvailable: "غير متاح",
                cancelled: "ملغى"
            }
        };

        const t = translations[lang];

        // Format date/time functions
        const formatDate = (dateStr) => {
            if (!dateStr) return t.notAvailable;
            const date = new Date(dateStr);
            return date.toISOString().split('T')[0];
        };

        const formatTime = (timeStr) => {
            if (!timeStr) return t.notAvailable;
            const time = new Date(timeStr);
            return time.toLocaleTimeString();
        };

        // Generate the HTML content
        const content = `
<!DOCTYPE html>
<html lang="${lang}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${t.reportTitle}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: ${lang === 'ar' ? 'rtl' : 'ltr'};
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .details-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .detail-item {
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .detail-value {
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .image-container {
            max-width: 100px;
            max-height: 60px;
            margin-top: 5px;
        }
        .image-container img {
            max-width: 100%;
            max-height: 60px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #666;
        }
        @media print {
            body {
                font-size: 12pt;
            }
            .details-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>${t.reportTitle}</h1>
    </div>
    
    <div class="details-container">
        <!-- Column 1 -->
        <div>
            <div class="detail-item">
                <div class="detail-label">${t.tableID}</div>
                <div class="detail-value">${reservation.table_id || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.reservationNumber}</div>
                <div class="detail-value">${reservation.reservation_number || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.orderNumber}</div>
                <div class="detail-value">${reservation.order_number || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.floor_partition_id}</div>
                <div class="detail-value">
                    ${reservation.floor_partition ? 
                        (lang === 'en' ? reservation.floor_partition.name_en : reservation.floor_partition.name_ar) : 
                        t.notAvailable}
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.floorPartitionImage}</div>
                <div class="detail-value">
                    ${reservation.floor_partition && reservation.floor_partition.image ? 
                        `<div class="image-container"><img src="${reservation.floor_partition.image}" alt="Floor Partition"></div>` : 
                        t.notAvailable}
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.client}</div>
                <div class="detail-value">${reservation.name || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.phone}</div>
                <div class="detail-value">${reservation.phone || t.notAvailable}</div>
            </div>
        </div>
        
        <!-- Column 2 -->
        <div>
            <div class="detail-item">
                <div class="detail-label">${t.branch}</div>
                <div class="detail-value">
                    ${reservation.branch ? 
                        (lang === 'en' ? reservation.branch.name_en : reservation.branch.name_ar) : 
                        t.notAvailable}
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.date}</div>
                <div class="detail-value">${formatDate(reservation.date)}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.timeFrom}</div>
                <div class="detail-value">${formatTime(reservation.time_from)}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.timeTo}</div>
                <div class="detail-value">${formatTime(reservation.time_to)}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.confirmedDate}</div>
                <div class="detail-value">${formatDate(reservation.confirmed_date)}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.confirmedBy}</div>
                <div class="detail-value">${reservation.confirmed_by || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.confirmedTime}</div>
                <div class="detail-value">${formatTime(reservation.confirmed_time)}</div>
            </div>
        </div>
        
        <!-- Column 3 -->
        <div>
            <div class="detail-item">
                <div class="detail-label">${t.reservationType}</div>
                <div class="detail-value">
                    ${reservation.reservation_type ? 
                        (reservation.reservation_type === 'with' ? t.withMeal : t.withoutMeal) : 
                        t.notAvailable}
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.adults}</div>
                <div class="detail-value">${reservation.adult || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.kids}</div>
                <div class="detail-value">${reservation.kids || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.men}</div>
                <div class="detail-value">${reservation.men || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.women}</div>
                <div class="detail-value">${reservation.women || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.personalType}</div>
                <div class="detail-value">
                    ${reservation.personal_type ? 
                        (reservation.personal_type === 'family' ? t.family : t.person) : 
                        t.notAvailable}
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.refund}</div>
                <div class="detail-value">
                    ${reservation.table_reservation_transaction ? 
                        (reservation.table_reservation_transaction.refund || t.notAvailable) : 
                        t.notAvailable}
                </div>
            </div>
        </div>
        
        <!-- Column 4 -->
        <div>
            <div class="detail-item">
                <div class="detail-label">${t.cancellationReason}</div>
                <div class="detail-value">${reservation.cancellation_reason || t.notAvailable}</div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.status}</div>
                <div class="detail-value">
                    ${reservation.status === 'confirm' ? t.confirmedStatus : t.cancelled}
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.confirmed}</div>
                <div class="detail-value">
                    ${reservation.confirmed == 1 ? t.pending : 
                     reservation.confirmed == 2 ? t.confirmedStatus : 
                     t.rejected}
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.paymentStatus}</div>
                <div class="detail-value">
                    ${reservation.table_reservation_transaction ? 
                        (reservation.table_reservation_transaction.payment_status === 'paid' ? t.paid : 
                         reservation.table_reservation_transaction.payment_status === 'unpaid' ? t.unpaid : 
                         reservation.table_reservation_transaction.payment_status === 'part' ? t.part : 
                         t.paymentFailed) : 
                     t.notAvailable}
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-label">${t.paymentMethod}</div>
                <div class="detail-value">
                    ${reservation.table_reservation_transaction ? 
                        (reservation.table_reservation_transaction.payment_method === 'cash' ? t.cash : 
                         reservation.table_reservation_transaction.payment_method === 'credit_card' ? t.creditCard : 
                         t.online) : 
                     t.notAvailable}
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>${lang === 'en' ? 'Printed on' : 'تم الطباعة في'} ${new Date().toLocaleString()}</p>
    </div>
</body>
</html>
    `;

        // Write the content to the new window
        printWindow.document.open();
        printWindow.document.write(content);
        printWindow.document.close();

        // Wait for the content to load before printing
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
                // printWindow.close(); // Uncomment if you want to close after printing
            }, 500);
        };
    }
</script>
