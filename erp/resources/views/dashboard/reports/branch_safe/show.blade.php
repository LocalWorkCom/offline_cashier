@extends('layouts.master')

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('branch_safe.title')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.branch_safe.index') }}">@lang('branch_safe.title')</a></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('branch_safe.branch_safe_info')</div>
                            <button type="button" class="btn btn-primary" onclick="printFormattedReport()">
                                <i class="ri-printer-line me-1"></i>@lang('order.print')
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Branch Safe Details -->
                            <div class="mb-5">
                                <h5 class="mb-3">@lang('branch_safe.branch_safe_info')</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>@lang('branch_safe.cash_amount'):</strong> {{ $response['query'][0]->cash_amount }}</p>
                                        <p><strong>@lang('branch_safe.visa_amount'):</strong> {{ $response['query'][0]->visa_amount }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>@lang('branch_safe.deficit_cash'):</strong> {{ $response['query'][0]->deficit_cash }}</p>
                                        <p><strong>@lang('branch_safe.deficit_visa'):</strong> {{ $response['query'][0]->deficit_visa }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>@lang('branch_safe.reason'):</strong> {{ $response['query'][0]->reason ?? __('branch_safe.noReason') }}</p>
                                        <p><strong>@lang('branch_safe.timeStamp'):</strong> {{ $response['query'][0]->created_at ?? __('branch_safe.timeStamp') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Branch Details -->
                            <div class="mb-5">
                                <h5 class="mb-3">@lang('branch_safe.branch_info')</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>@lang('branch_safe.name'):</strong> {{ app()->getLocale() == 'en' ? $response['query'][0]->branch->name_en : $response['query'][0]->branch->name_ar }}</p>
                                        <p><strong>@lang('branch_safe.address'):</strong> {{ app()->getLocale() == 'en' ? $response['query'][0]->branch->address_en : $response['query'][0]->branch->address_ar }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>@lang('branch_safe.phone'):</strong> {{ $response['query'][0]->branch->phone }}</p>
                                        <p><strong>@lang('branch_safe.email'):</strong> {{ $response['query'][0]->branch->email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Employee (Cashier) Details -->
                            <div class="mb-5">
                                <h5 class="mb-3">@lang('branch_safe.cashier_info')</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>@lang('branch_safe.name'):</strong> {{ $response['query'][0]->employee->first_name }} {{ $response['query'][0]->employee->last_name }}</p>
                                        <p><strong>@lang('branch_safe.employee_code'):</strong> {{ $response['query'][0]->employee->employee_code }}</p>
                                        <p><strong>@lang('branch_safe.machine'):</strong> {{ $response['query'][0]->machine->name }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>@lang('branch_safe.email'):</strong> {{ $response['query'][0]->employee->email }}</p>
                                        <p><strong>@lang('branch_safe.phone'):</strong> {{ $response['query'][0]->employee->country_code }} {{ $response['query'][0]->employee->phone_number }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Opening Balances -->
                            <div class="mb-4">
                                <h5 class="mb-3">@lang('branch_safe.opening_balances')</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="balances-table">
                                        <thead>
                                            <tr>
                                                <th>@lang('branch_safe.id')</th>
                                                <th>@lang('branch_safe.date')</th>
                                                <th>@lang('branch_safe.time')</th>
                                                <th>@lang('branch_safe.open_cash')</th>
                                                <th>@lang('branch_safe.open_visa')</th>
                                                <th>@lang('branch_safe.close_cash')</th>
                                                <th>@lang('branch_safe.close_visa')</th>
                                                <th>@lang('branch_safe.real_cash')</th>
                                                <th>@lang('branch_safe.real_visa')</th>
                                                <th>@lang('branch_safe.deficit_cash')</th>
                                                <th>@lang('branch_safe.deficit_visa')</th>
                                                <th>@lang('branch_safe.deficit_cash_close')</th>
                                                <th>@lang('branch_safe.deficit_visa_close')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($response['balances'] as $balance)
                                            <tr>
                                                <td>{{ $balance->id }}</td>
                                                <td>{{ $balance->date }}</td>
                                                <td>{{ $balance->time }}</td>
                                                <td>{{ $balance->open_cash }}</td>
                                                <td>{{ $balance->open_visa }}</td>
                                                <td>{{ $balance->close_cash }}</td>
                                                <td>{{ $balance->close_visa }}</td>
                                                <td>{{ $balance->real_cash }}</td>
                                                <td>{{ $balance->real_visa }}</td>
                                                <td>{{ $balance->deficit_cash }}</td>
                                                <td>{{ $balance->deficit_visa }}</td>
                                                <td>{{ $balance->deficit_cash_close }}</td>
                                                <td>{{ $balance->deficit_visa_close }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
    <script>
        function printFormattedReport() {
            // Get the HTML of the balances table
            const balancesTable = document.getElementById('balances-table').outerHTML;
            
            // Create the print content with translations
            const content = `
                <!DOCTYPE html>
                <html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                <head>
                    <title>@lang('branch_safe.report_title')</title>
                    <style>
                        body { 
                            font-family: Arial, sans-serif; 
                            margin: 20px;
                            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
                        }
                        h1 { 
                            color: #333; 
                            border-bottom: 1px solid #ddd; 
                            padding-bottom: 10px;
                            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
                        }
                        h2 { 
                            color: #555; 
                            margin-top: 20px;
                            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
                        }
                        .section { 
                            margin-bottom: 30px;
                        }
                        table { 
                            width: 100%; 
                            border-collapse: collapse; 
                            margin-top: 10px;
                        }
                        th, td { 
                            border: 1px solid #ddd; 
                            padding: 8px;
                            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
                        }
                        th { 
                            background-color: #f2f2f2;
                        }
                        .row { 
                            display: flex; 
                            margin-bottom: 15px;
                            flex-direction: {{ app()->getLocale() == 'ar' ? 'row-reverse' : 'row' }};
                        }
                        .col { 
                            flex: 1; 
                            padding: 0 10px;
                        }
                        @media print {
                            body { 
                                margin: 10mm;
                            }
                            .no-print { 
                                display: none; 
                            }
                        }
                    </style>
                </head>
                <body>
                    <h1>@lang('branch_safe.report_title')</h1>
                    
                    <div class="section">
                        <h2>@lang('branch_safe.branch_safe_info')</h2>
                        <div class="row">
                            <div class="col">
                                <p><strong>@lang('branch_safe.cash_amount'):</strong> {{ $response['query'][0]->cash_amount }}</p>
                                <p><strong>@lang('branch_safe.visa_amount'):</strong> {{ $response['query'][0]->visa_amount }}</p>
                            </div>
                            <div class="col">
                                <p><strong>@lang('branch_safe.deficit_cash'):</strong> {{ $response['query'][0]->deficit_cash }}</p>
                                <p><strong>@lang('branch_safe.deficit_visa'):</strong> {{ $response['query'][0]->deficit_visa }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <h2>@lang('branch_safe.branch_info')</h2>
                        <div class="row">
                            <div class="col">
                                <p><strong>@lang('branch_safe.name'):</strong> {{ app()->getLocale() == 'en' ? $response['query'][0]->branch->name_en : $response['query'][0]->branch->name_ar }}</p>
                                <p><strong>@lang('branch_safe.address'):</strong> {{ app()->getLocale() == 'en' ? $response['query'][0]->branch->address_en : $response['query'][0]->branch->address_ar }}</p>
                            </div>
                            <div class="col">
                                <p><strong>@lang('branch_safe.phone'):</strong> {{ $response['query'][0]->branch->phone }}</p>
                                <p><strong>@lang('branch_safe.email'):</strong> {{ $response['query'][0]->branch->email }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <h2>@lang('branch_safe.cashier_info')</h2>
                        <div class="row">
                            <div class="col">
                                <p><strong>@lang('branch_safe.name'):</strong> {{ $response['query'][0]->employee->first_name }} {{ $response['query'][0]->employee->last_name }}</p>
                                <p><strong>@lang('branch_safe.employee_code'):</strong> {{ $response['query'][0]->employee->employee_code }}</p>
                                <p><strong>@lang('branch_safe.machine'):</strong> {{ $response['query'][0]->machine->name }}</p>
                            </div>
                            <div class="col">
                                <p><strong>@lang('branch_safe.email'):</strong> {{ $response['query'][0]->employee->email }}</p>
                                <p><strong>@lang('branch_safe.phone'):</strong> {{ $response['query'][0]->employee->country_code }} {{ $response['query'][0]->employee->phone_number }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <h2>@lang('branch_safe.opening_balances')</h2>
                        ${balancesTable}
                    </div>
                    
                    <script>
                        window.onload = function() {
                            setTimeout(function() {
                                window.print();
                                window.close();
                            }, 200);
                        };
                    <\/script>
                </body>
                </html>
            `;

            const printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write(content);
            printWindow.document.close();
        }
    </script>
@endsection