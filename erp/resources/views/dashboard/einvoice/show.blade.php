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
    <h4 class="fw-medium mb-0">@lang('permissions.einvoices')</h4>
    <div class="ms-sm-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('permissions.einvoices')</li>
            </ol>
        </nav>

    </div>
</div>
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Filter Buttons for Cashier Machines -->
        <div class="d-flex flex-wrap mb-3">
            @foreach ($cashierMachines as $machine)
            <a href="{{ route(
                        'dashboard.einvoices.show',
                        array_filter([
                            'cashier_machine_id' => $machine->id,
                            'from' => $fromDate,
                            'to' => $toDate,
                        ]),
                    ) }}"
                class="btn btn-wave text-white me-2 mb-2
                    {{ $cashierMachineId == $machine->id ? 'btn-primary' : 'btn-success' }}">
                {{ $machine->name_ar }} / {{ $machine->name_en }}
            </a>
            @endforeach
        </div>
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif



        <!-- Date Filter Form -->
        <form action="{{ route('dashboard.einvoices.show') }}" method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="date" name="from" value="{{ $fromDate }}" class="form-control"
                        placeholder="@lang('einvoice.from_date')">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to" value="{{ $toDate }}" class="form-control"
                        placeholder="@lang('einvoice.to_date')">
                </div>
                <div class="col-md-3">
                    <select name="payment_method" class="form-select">
                        <option value="">@lang('einvoice.select_payment')</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>
                            @lang('einvoice.payment_methods.cash')</option>
                        <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>
                            @lang('einvoice.payment_methods.credit')</option>
                        <option value="online" {{ request('payment_method') == 'online' ? 'selected' : '' }}>
                            @lang('einvoice.payment_methods.online')</option>
                        <option value="credit_with_delivery"
                            {{ request('payment_method') == 'credit_with_delivery' ? 'selected' : '' }}>
                            @lang('einvoice.payment_methods.credit_with_delivery')</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">@lang('einvoice.filter_by_date')</button>
                </div>
            </div>
        </form>


        <!-- Buttons Section -->
        <div class="d-flex flex-wrap gap-2 mb-3">

            <!-- Filter Button -->
            <form action="{{ route('dashboard.einvoices.filter') }}" method="POST" id="filterForm">
                @csrf
                <input type="hidden" name="cashier_machine_id" value="{{ $cashierMachineId }}">
                <button type="submit" class="btn btn-info-light btn-wave">@lang('einvoice.get_filter_invoice')</button>
            </form>

            <!-- Test Button -->
            <form action="{{ route('dashboard.einvoices.test') }}" method="POST" id="testForm">
                @csrf
                <input type="hidden" name="selected_invoices[]" id="selectedInvoicesTest">
                <input type="hidden" name="cashier_machine_id" value="{{ $cashierMachineId }}">
                <button type="submit" class="btn btn-info-light btn-wave">@lang('einvoice.test')</button>
            </form>

            <!-- Reset Button -->
            <form action="{{ route('dashboard.einvoices.reset') }}" method="POST" id="resetForm">
                @csrf
                <input type="hidden" name="cashier_machine_id" value="{{ $cashierMachineId }}">
                <input type="hidden" name="from" value="{{ $fromDate }}">
                <input type="hidden" name="to" value="{{ $toDate }}">
                <button type="submit" class="btn btn-info-light btn-wave">@lang('einvoice.reset')</button>
            </form>

            <!-- Upload Button -->
            <form action="{{ route('dashboard.einvoices.ereceipt.send') }}" method="POST" id="uploadForm">
                @csrf
                <input type="hidden" name="test" id="selectedInvoicesUpload">
                <button type="submit" class="btn btn-info-light btn-wave">@lang('einvoice.upload')</button>
            </form>
            @if (auth('admin')->user()->hasPermissionTo('view officer_assign_setting', 'admin'))

            <a href="{{ route('dashboard.einvoices.setting.list') }}" class="btn btn-primary label-btn">
                <i class="fe fe-pepole label-btn-icon me-2"></i>
                @lang('einvoice.addbalancesettings')
            </a>
            @endif
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <!-- Count Total Button -->
                <button id="countSelectedBtn" class="btn btn-warning me-2">
                    <i class="bi bi-list-check"></i> @lang('einvoice.count_selected')
                </button>
                <div id="selectedCountDisplay" class="alert alert-warning mb-0" style="display: none;">
                    <strong>@lang('einvoice.selected_count'):</strong> <span id="selectedCountValue">0</span>
                </div>
            </div>
            <!--  Total Button -->
            <div>
                <button id="calculateTotalBtn" class="btn btn-primary me-2">
                    <i class="bi bi-calculator"></i> @lang('einvoice.calculate_total')
                </button>
                <div id="totalAmountDisplay" class="alert alert-success mb-0 " style="display: none;">
                    <strong>@lang('einvoice.total_amount'):</strong> <span id="totalAmountValue">0.00</span>
                </div>
            </div>

            <!-- Show Cashier Settings Button -->
            <div>
                <button id="showCashierSettingsBtn" class="btn btn-info">
                    <i class="bi bi-gear"></i> @lang('einvoice.show_cashier_settings')
                </button>
                <div id="cashierSettingsDisplay" class="alert alert-info mb-0" style="display: none;">
                    <strong>@lang('einvoice.cashier_settings'):</strong>
                    <ul class="mb-0">
                        <li><strong>@lang('einvoice.min_balance'):</strong> <span id="minBalanceValue">0.00</span></li>
                        <li><strong>@lang('einvoice.max_balance'):</strong> <span id="maxBalanceValue">0.00</span></li>
                        <li><strong>@lang('einvoice.min_count'):</strong> <span id="minCountValue">0</span></li>
                        <li><strong>@lang('einvoice.max_count'):</strong> <span id="maxCountValue">0</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-body">
                        @if (session('message'))
                        <div class="alert alert-solid-info alert-dismissible fade show">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        @endif
                        {{-- @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                        {{ $error }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    @endforeach
                    @endif --}}
                    @if ($cashierMachineId)
                    @php
                    $selectedMachine = $cashierMachines->where('id', $cashierMachineId)->first();
                    @endphp
                    <h5 class="mb-3">
                        @lang('einvoice.showing_orders_for'): {{ $selectedMachine->name_ar }} / {{ $selectedMachine->name_en }}
                    </h5>
                    @else
                    <h5 class="mb-3">@lang('einvoice.showing_all_orders')</h5>
                    @endif

                    <table class="table table-bordered text-nowrap" id="invoicesTable" style="width:100%">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select_all"></th>
                                <th>@lang('einvoice.invoice_number')</th>
                                <th>@lang('einvoice.total_after_tax')</th>
                                <th>@lang('einvoice.order_number')</th>
                                <th>@lang('einvoice.payment_method')</th>
                                <th>@lang('einvoice.date')</th>
                                <th>@lang('einvoice.time')</th>

                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($filteredOrders))
                            @foreach ($filteredOrders as $item)
                            <tr>

                                <td><input type="checkbox" name="selected_invoices[]"
                                        value="{{ $item->id }}" checked></td>
                                <td>{{ $item->invoice->orders->invoice_num }}</td>
                                <td>{{ $item->invoice->orders->total_after_tax }}</td>
                                <td>{{ optional($item->invoice->orders)->order_number ?? 'N/A' }}</td>
                                <td>
                                    @php
                                    $method = optional($item->invoice->orders->transaction)->payment_method;

                                    $methods = [
                                    'cash' => [
                                    '💵',
                                    __('einvoice.payment_methods.cash'),
                                    'text-success',
                                    ],
                                    'credit' => [
                                    '💳',
                                    __('einvoice.payment_methods.credit'),
                                    'text-primary',
                                    ],
                                    'online' => [
                                    '🌐',
                                    __('einvoice.payment_methods.online'),
                                    'text-info',
                                    ],
                                    'credit_with_delivery' => [
                                    '🚚💳',
                                    __('einvoice.payment_methods.credit_with_delivery'),
                                    'text-warning',
                                    ],
                                    ];

                                    $icon = $methods[$method][0] ?? '❓';
                                    $label =
                                    $methods[$method][1] ??
                                    __('einvoice.payment_methods.unknown');
                                    $class = $methods[$method][2] ?? 'text-muted';
                                    @endphp

                                    <span class="{{ $class }}">
                                        {{ $icon }} {{ $label }}
                                    </span>
                                </td>
                                <td>{{ $item->invoice->date }}</td>
                                <td>{{ $item->invoice->time }}</td>
                            </tr>
                            @endforeach
                            @else
                            @foreach ($einvoices as $item)
                            <tr>

                                <td><input type="checkbox" name="selected_invoices[]"
                                        value="{{ $item->id }}" checked></td>
                                <td>{{ $item->invoice->invoice_num }}</td>
                                <td>{{ $item->invoice->total_after_tax }}</td>
                                <td>{{ optional($item->invoice->orders)->order_number ?? 'N/A' }}</td>
                                <td>
                                    @php
                                    $method = optional($item->invoice->orders->transaction)->payment_method;

                                    $methods = [
                                    'cash' => [
                                    '💵',
                                    __('einvoice.payment_methods.cash'),
                                    'text-success',
                                    ],
                                    'credit' => [
                                    '💳',
                                    __('einvoice.payment_methods.credit'),
                                    'text-primary',
                                    ],
                                    'online' => [
                                    '🌐',
                                    __('einvoice.payment_methods.online'),
                                    'text-info',
                                    ],
                                    'credit_with_delivery' => [
                                    '🚚💳',
                                    __('einvoice.payment_methods.credit_with_delivery'),
                                    'text-warning',
                                    ],
                                    ];

                                    $icon = $methods[$method][0] ?? '❓';
                                    $label =
                                    $methods[$method][1] ??
                                    __('einvoice.payment_methods.unknown');
                                    $class = $methods[$method][2] ?? 'text-muted';
                                    @endphp

                                    <span class="{{ $class }}">
                                        {{ $icon }} {{ $label }}
                                    </span>
                                </td>


                                <td>{{ $item->invoice->date }}</td>
                                <td>{{ $item->invoice->time }}</td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('select_all').addEventListener('click', function(event) {
            document.querySelectorAll('input[name="selected_invoices[]"]').forEach(
                checkbox => {
                    checkbox.checked = event.target.checked;
                });
        });

        function getSelectedInvoices() {
            return Array.from(document.querySelectorAll(
                    'input[name="selected_invoices[]"]:checked'))
                .map(checkbox => checkbox.value);
        }

        document.getElementById('testForm').addEventListener('submit', function(event) {
            let selectedInvoices = getSelectedInvoices();
            document.getElementById('selectedInvoicesTest').value = selectedInvoices.join(
                ',');
        });
        document.getElementById('uploadForm').addEventListener('submit', function(event) {

            let selectedInvoices = getSelectedInvoices();
            document.getElementById('selectedInvoicesUpload').value = selectedInvoices.join(
                ',');
        });

        // Count Selected Invoices Button Handler
        document.getElementById('countSelectedBtn').addEventListener('click', function() {
            const selectedCount = document.querySelectorAll('input[name="selected_invoices[]"]:checked')
                .length;

            const selectedCountDisplay = document.getElementById('selectedCountDisplay');
            const selectedCountValue = document.getElementById('selectedCountValue');
            selectedCountValue.textContent = selectedCount;
            selectedCountDisplay.style.display = 'block';
        });

        // Calculate Total Button Handler
        document.getElementById('calculateTotalBtn').addEventListener('click', function() {
            let totalAmount = 0;

            const selectedInvoices = document.querySelectorAll(
                'input[name="selected_invoices[]"]:checked');

            selectedInvoices.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const amount = parseFloat(row.querySelector('td:nth-child(3)').textContent);
                totalAmount += amount;
            });

            const totalAmountDisplay = document.getElementById('totalAmountDisplay');
            const totalAmountValue = document.getElementById('totalAmountValue');
            totalAmountValue.textContent = totalAmount.toFixed(2);
            totalAmountDisplay.style.display = 'block';
        });


        // Show Cashier Settings Button Handler
        document.getElementById('showCashierSettingsBtn').addEventListener('click', function() {
            const cashierMachineId = "{{ $cashierMachineId }}";

            fetch(`/dashboard/einvoices/cashier-settings/${cashierMachineId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cashierSettingsDisplay = document.getElementById(
                            'cashierSettingsDisplay');
                        const minBalanceValue = document.getElementById('minBalanceValue');
                        const maxBalanceValue = document.getElementById('maxBalanceValue');
                        const minCountValue = document.getElementById('minCountValue');
                        const maxCountValue = document.getElementById('maxCountValue');

                        minBalanceValue.textContent = data.settings.min_balance;
                        maxBalanceValue.textContent = data.settings.max_balance;
                        minCountValue.textContent = data.settings.min_count;
                        maxCountValue.textContent = data.settings.max_count;

                        cashierSettingsDisplay.style.display = 'block';
                    } else {
                        alert('Failed to fetch cashier settings.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching cashier settings.');
                });
        });
    });
</script>
@endsection