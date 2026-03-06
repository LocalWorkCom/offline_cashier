@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('report.Show_waiter_table_service')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('reports.waiter_table_service_report.list') }}">
                            @lang('report.Show_waiter_table_service')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @lang('report.Show_waiter_table_service')
                    </li>
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
                            <div class="card-title">@lang('report.Show_waiter_table_service')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <!-- Waiter ID -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('category.ID')</label>
                                    <p class="form-text">
                                        {{ $WaiterTableService['waiter_id'] ?? __('report.NotAvailable') }}
                                    </p>
                                </div>

                                <!-- Waiter Name -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.WaiterName')</label>
                                    <p class="form-text">
                                        {{ $WaiterTableService['first_name'] ?? '' }}
                                        {{ $WaiterTableService['last_name'] ?? '' }}
                                    </p>
                                </div>

                                <!-- Total Orders -->
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('report.TotalOrders')</label>
                                    <p class="form-text">
                                        {{ $WaiterTableService['total_orders'] ?? __('report.NotAvailable') }}
                                    </p>
                                </div>

                                <!-- Table Details -->
                                <div class="col-xl-12">
                                    <label class="form-label">@lang('report.TableDetails')</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('report.TableName')</th>
                                                    <th>@lang('report.TotalOrders')</th>
                                                    <th>@lang('report.AvgPrice')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($WaiterTableService['tables'] ?? [] as $table)
                                                    <tr>
                                                        <td>{{ app()->getLocale() === 'ar' ? $table['name_ar'] ?? '' : $table['name_en'] ?? '' }}
                                                        </td>
                                                        <td>{{ $table['order_count'] ?? 0 }}</td>
                                                        <td>{{ number_format($table['avg_total_price'] ?? 0, 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3">@lang('report.NoTablesFound')</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Modifications -->
                                <div class="col-xl-12">
                                    <label class="form-label">@lang('report.Modifications')</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('report.TableName')</th>
                                                    <th>@lang('report.TotalModifications')</th>
                                                    <th>@lang('report.ModificationDetails')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($WaiterTableService['tables'] ?? [] as $table)
                                                    <tr>
                                                        <td>{{ app()->getLocale() === 'ar' ? $table['name_ar'] ?? '' : $table['name_en'] ?? '' }}
                                                        </td>
                                                        <td>{{ array_sum(array_column($table['modifications'] ?? [], 'count')) }}
                                                        </td>
                                                        <td>
                                                            @forelse ($table['modifications'] ?? [] as $mod)
                                                                - {{ $mod['dish_name'] ?? '' }}
                                                                ({{ $mod['count'] ?? 0 }})
                                                                <br>
                                                            @empty
                                                                @lang('report.NoModifications')
                                                            @endforelse
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3">@lang('report.NoTablesFound')</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Cancellations -->
                                <div class="col-xl-12">
                                    <label class="form-label">@lang('report.Cancellations')</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('report.TableName')</th>
                                                    <th>@lang('report.TotalCancellations')</th>
                                                    <th>@lang('report.CancellationDetails')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($WaiterTableService['tables'] ?? [] as $table)
                                                    <tr>
                                                        <td>{{ app()->getLocale() === 'ar' ? $table['name_ar'] ?? '' : $table['name_en'] ?? '' }}
                                                        </td>
                                                        <td>{{ array_sum(array_column($table['cancellations'] ?? [], 'count')) }}
                                                        </td>
                                                        <td>
                                                            @forelse ($table['cancellations'] ?? [] as $cancel)
                                                                - {{ $cancel['dish_name'] ?? '' }}
                                                                ({{ $cancel['count'] ?? 0 }})
                                                                <br>
                                                            @empty
                                                                @lang('report.NoCancellations')
                                                            @endforelse
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3">@lang('report.NoTablesFound')</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Print Button -->
                        <div class="card-footer border-top-0">
                            <div class="btn-list float-end">
                                <button type="button" class="d-inline-flex btn btn-primary btn-wave print-button"
                                    onclick="printFormattedReport({{ json_encode($WaiterTableService) }})">
                                    <i class="ri-printer-line me-1 align-middle"></i>@lang('reservations.print')
                                </button>

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
@endsection
<script>
    function printFormattedReport(waiterTableService) {
        if (!waiterTableService) {
            alert("No waiter table service data available.");
            return;
        }

        // Create an iframe for printing
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0px';
        iframe.style.height = '0px';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;

        // Language detection
        const lang = document.documentElement.lang || 'en';

        // Translations
        const translations = {
            en: {
                reportTitle: "Waiter Table Service Report",
                waiterID: "Waiter ID",
                waiterName: "Waiter Name",
                totalOrders: "Total Orders",
                tableDetails: "Table Details",
                tableName: "Table Name",
                avgPrice: "Average Price",
                modifications: "Modifications",
                totalModifications: "Total Modifications",
                modificationDetails: "Modification Details",
                cancellations: "Cancellations",
                totalCancellations: "Total Cancellations",
                cancellationDetails: "Cancellation Details",
                notAvailable: "Not Available",
                noTablesFound: "No Tables Found",
                noModifications: "No Modifications",
                noCancellations: "No Cancellations"
            },
            ar: {
                reportTitle: "تقرير خدمة النادل للطاولات",
                waiterID: "معرف النادل",
                waiterName: "اسم النادل",
                totalOrders: "إجمالي الطلبات",
                tableDetails: "تفاصيل الطاولة",
                tableName: "اسم الطاولة",
                avgPrice: "متوسط السعر",
                modifications: "التعديلات",
                totalModifications: "إجمالي التعديلات",
                modificationDetails: "تفاصيل التعديل",
                cancellations: "الإلغاءات",
                totalCancellations: "إجمالي الإلغاءات",
                cancellationDetails: "تفاصيل الإلغاء",
                notAvailable: "غير متاح",
                noTablesFound: "لا توجد طاولات",
                noModifications: "لا توجد تعديلات",
                noCancellations: "لا توجد إلغاءات"
            }
        };

        const t = translations[lang];

        // Helper function to sum array values by key
        const arraySum = (arr, key) => {
            if (!arr) return 0;
            return arr.reduce((sum, item) => sum + (item[key] || 0), 0);
        };

        // Helper function to generate details list
        const generateDetails = (items) => {
            if (!items || items.length === 0) {
                return items === 'modifications' ? t.noModifications : t.noCancellations;
            }

            return items.map(item => `- ${item.dish_name || ''} (${item.count || 0})<br>`).join('');
        };

        // Helper function to generate table rows
        const generateTableRows = (items, type) => {
            if (!items || items.length === 0) {
                return `<tr><td colspan="3">${type === 'tables' ? t.noTablesFound : 
                        type === 'modifications' ? t.noModifications : t.noCancellations}</td></tr>`;
            }

            return items.map(table => `
                <tr>
                    <td>${lang === 'ar' ? (table.name_ar || '') : (table.name_en || '')}</td>
                    <td>${type === 'tables' ? (table.order_count || 0) : 
                        type === 'modifications' ? arraySum(table.modifications || [], 'count') : 
                        arraySum(table.cancellations || [], 'count')}</td>
                    <td>
                        ${type === 'tables' ? (table.avg_total_price ? Number(table.avg_total_price).toFixed(2) : '0.00') : 
                         generateDetails(type === 'modifications' ? (table.modifications || []) : (table.cancellations || []))}
                    </td>
                </tr>
            `).join('');
        };

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
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: ${lang === 'ar' ? 'right' : 'left'};
        }
        .info-table th {
            background-color: #f2f2f2;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: ${lang === 'ar' ? 'right' : 'left'};
        }
        .data-table th {
            background-color: #f2f2f2;
        }
        .section-title {
            margin-top: 30px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>${t.reportTitle}</h1>
    </div>
    
    <table class="info-table">
        <tr>
            <th>${t.waiterID}</th>
            <td>${waiterTableService.waiter_id || t.notAvailable}</td>
        </tr>
        <tr>
            <th>${t.waiterName}</th>
            <td>${waiterTableService.first_name || ''} ${waiterTableService.last_name || ''}</td>
        </tr>
        <tr>
            <th>${t.totalOrders}</th>
            <td>${waiterTableService.total_orders || t.notAvailable}</td>
        </tr>
    </table>

    <div class="section-title">${t.tableDetails}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>${t.tableName}</th>
                <th>${t.totalOrders}</th>
                <th>${t.avgPrice}</th>
            </tr>
        </thead>
        <tbody>
            ${generateTableRows(waiterTableService.tables, 'tables')}
        </tbody>
    </table>

    <div class="section-title">${t.modifications}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>${t.tableName}</th>
                <th>${t.totalModifications}</th>
                <th>${t.modificationDetails}</th>
            </tr>
        </thead>
        <tbody>
            ${generateTableRows(waiterTableService.tables, 'modifications')}
        </tbody>
    </table>

    <div class="section-title">${t.cancellations}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>${t.tableName}</th>
                <th>${t.totalCancellations}</th>
                <th>${t.cancellationDetails}</th>
            </tr>
        </thead>
        <tbody>
            ${generateTableRows(waiterTableService.tables, 'cancellations')}
        </tbody>
    </table>
</body>
</html>
`;

        doc.open();
        doc.write(content);
        doc.close();

        // Wait for iframe to load before printing
        iframe.onload = function() {
            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                document.body.removeChild(iframe);
            }, 300);
        };
    }
</script>
