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
        <h4 class="fw-medium mb-0">@lang('currency.Currencies')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);"
                           onclick="window.location.href='{{ route('currencies.list') }}'">@lang('currency.Currencies')</a>
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
                            <div class="card-title">@lang('currency.Currencies')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create currencies', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('currency.AddCurrency')
                                </button>
                            @endif
                            <!-- Modal for Adding a Currency -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                 aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('currency.store') }}" method="POST" class="needs-validation"
                                              novalidate>
                                            @csrf
                                            @if ($errors->any())
                                                @foreach ($errors->all() as $error)
                                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                                        {{ $error }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                                aria-label="Close">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endif
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('currency.AddCurrency')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="currency-ar" class="form-label">@lang('currency.ArabicName')</label>
                                                        <input type="text" class="form-control"
                                                               placeholder="@lang('currency.ArabicName')" name="currency_ar" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterArabicName')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="currency-en" class="form-label">@lang('currency.EnglishName')</label>
                                                        <input type="text" class="form-control"
                                                               placeholder="@lang('currency.EnglishName')" name="currency_en" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="currency-symbol" class="form-label">@lang('currency.CurrencySymbol')</label>
                                                        <input type="text" class="form-control"
                                                               placeholder="@lang('currency.CurrencySymbol')" name="currency_symbol" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterCurrencySymbol')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="currency-code" class="form-label">@lang('currency.CurrencyCode')</label>
                                                        <input type="text" class="form-control"
                                                               placeholder="@lang('currency.CurrencyCode')" name="currency_code" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterCurrencyCode')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="country" class="form-label">@lang('currency.Country')</label>
                                                        <select name="country_id" class="form-select" required>
                                                            <option value="">@lang('currency.SelectCountry')</option>
                                                            @foreach($countries as $country)
                                                                <option value="{{ $country->id }}">
                                                                    {{ app()->getLocale() == 'en' ? $country->name_en : $country->name_ar }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.SelectCountry')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_default" id="is_default">
                                                            <label class="form-check-label" for="is_default">
                                                                @lang('currency.IsDefault')
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">@lang('modal.close')</button>
                                                <button type="submit"
                                                        class="btn btn-outline-primary">@lang('modal.save')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                 aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-currency-form" action="" method="POST"
                                              class="needs-validation" novalidate>
                                            @csrf
                                            @method('PUT')
                                            @if ($errors->any())
                                                @foreach ($errors->all() as $error)
                                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                                        {{ $error }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                                aria-label="Close">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endif
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="editModalLabel">@lang('currency.EditCurrency')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-currency-ar"
                                                               class="form-label">@lang('currency.ArabicName')</label>
                                                        <input type="text" id="edit-currency-ar" class="form-control"
                                                               name="currency_ar" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-currency-en"
                                                               class="form-label">@lang('currency.EnglishName')</label>
                                                        <input type="text" id="edit-currency-en" class="form-control"
                                                               name="currency_en" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-currency-symbol"
                                                               class="form-label">@lang('currency.CurrencySymbol')</label>
                                                        <input type="text" id="edit-currency-symbol" class="form-control"
                                                               name="currency_symbol" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterCurrencySymbol')</div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-currency-code"
                                                               class="form-label">@lang('currency.CurrencyCode')</label>
                                                        <input type="text" id="edit-currency-code" class="form-control"
                                                               name="currency_code" required>
                                                        <div class="valid-feedback">@lang('validation.Correct')</div>
                                                        <div class="invalid-feedback">@lang('validation.EnterCurrencyCode')</div>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <label for="edit-country" class="form-label">@lang('currency.Country')</label>
                                                        <select name="country_id" id="edit-country" class="form-select" required>
                                                            @foreach($countries as $country)
                                                                <option value="{{ $country->id }}">
                                                                    {{ app()->getLocale() == 'en' ? $country->name_en : $country->name_ar }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                   name="is_default" id="edit-is-default">
                                                            <label class="form-check-label" for="edit-is-default">
                                                                @lang('currency.IsDefault')
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">@lang('modal.close')</button>
                                                <button type="submit"
                                                        class="btn btn-outline-primary">@lang('modal.save')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                 aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('currency.ShowCurrency')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('currency.ArabicName')</label>
                                                    <p id="show-currency-ar" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('currency.EnglishName')</label>
                                                    <p id="show-currency-en" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('currency.CurrencySymbol')</label>
                                                    <p id="show-currency-symbol" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('currency.CurrencyCode')</label>
                                                    <p id="show-currency-code" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('currency.Country')</label>
                                                    <p id="show-country" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                    <label class="form-label">@lang('currency.IsDefault')</label>
                                                    <p id="show-is-default" class="form-control-static"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">@lang('modal.close')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                <tr>
                                    <th scope="col">@lang('category.ID')</th>
                                    <th scope="col">@lang('currency.ArabicName')</th>
                                    <th scope="col">@lang('currency.EnglishName')</th>
                                    <th scope="col">@lang('currency.CurrencySymbol')</th>
                                    <th scope="col">@lang('currency.CurrencyCode')</th>
                                    <th scope="col">@lang('currency.Country')</th>
                                    <th scope="col">@lang('currency.IsDefault')</th>
                                    <th scope="col">@lang('category.Actions')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $rowNumber = 1; @endphp
                                @foreach ($currencies as $currency)
                                    <tr>
                                        <td>{{ $rowNumber++ }}</td>
                                        <td>{{ $currency->currency_ar }}</td>
                                        <td>{{ $currency->currency_en }}</td>
                                        <td>{{ $currency->currency_symbol }}</td>
                                        <td>{{ $currency->currency_code }}</td>
                                        <td>{{ app()->getLocale() == 'en' ? $currency->country->name_en : $currency->country->name_ar }}</td>
                                        <td>
                                            @if($currency->is_default)
                                                <span class="badge bg-success">@lang('currency.Yes')</span>
                                            @else
                                                <span class="badge bg-secondary">@lang('currency.No')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (auth('admin')->user()->hasPermissionTo('view currencies', 'admin'))
                                                <!-- Show Button -->
                                                <a href="javascript:void(0);"
                                                   class="btn btn-info-light btn-wave show-currency-btn"
                                                   data-id="{{ $currency->id }}"
                                                   data-currency-ar="{{ $currency->currency_ar }}"
                                                   data-currency-en="{{ $currency->currency_en }}"
                                                   data-currency-symbol="{{ $currency->currency_symbol }}"
                                                   data-currency-code="{{ $currency->currency_code }}"
                                                   data-country="{{ app()->getLocale() == 'en' ? $currency->country->name_en : $currency->country->name_ar }}"
                                                   data-is-default="{{ $currency->is_default }}"
                                                   data-bs-toggle="modal" data-bs-target="#showModal">
                                                    @lang('category.show') <i class="ri-eye-line"></i>
                                                </a>
                                            @endif

                                            @if (auth('admin')->user()->hasPermissionTo('update currencies', 'admin'))
                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-orange-light btn-wave edit-currency-btn"
                                                        data-id="{{ $currency->id }}"
                                                        data-currency-ar="{{ $currency->currency_ar }}"
                                                        data-currency-en="{{ $currency->currency_en }}"
                                                        data-currency-symbol="{{ $currency->currency_symbol }}"
                                                        data-currency-code="{{ $currency->currency_code }}"
                                                        data-country-id="{{ $currency->country_id }}"
                                                        data-is-default="{{ $currency->is_default }}"
                                                        data-route="{{ route('currency.update', ':id') }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal">
                                                    @lang('category.edit') <i class="ri-edit-line"></i>
                                                </button>
                                            @endif

                                            @if (auth('admin')->user()->hasPermissionTo('delete currencies', 'admin'))
                                                <form class="d-inline" id="delete-form-{{ $currency->id }}"
                                                      action="{{ route('currency.delete', $currency->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" onclick="delete_item('{{ $currency->id }}')"
                                                            class="btn btn-danger-light btn-wave">
                                                        @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
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
    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Currency Modal
            const editButtons = document.querySelectorAll('.edit-currency-btn');
            const editForm = document.getElementById('edit-currency-form');
            const currencyArInput = document.getElementById('edit-currency-ar');
            const currencyEnInput = document.getElementById('edit-currency-en');
            const currencySymbolInput = document.getElementById('edit-currency-symbol');
            const currencyCodeInput = document.getElementById('edit-currency-code');
            const countrySelect = document.getElementById('edit-country');
            const isDefaultCheckbox = document.getElementById('edit-is-default');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currencyId = this.getAttribute('data-id');
                    const currencyAr = this.getAttribute('data-currency-ar');
                    const currencyEn = this.getAttribute('data-currency-en');
                    const currencySymbol = this.getAttribute('data-currency-symbol');
                    const currencyCode = this.getAttribute('data-currency-code');
                    const countryId = this.getAttribute('data-country-id');
                    const isDefault = this.getAttribute('data-is-default');
                    const routeTemplate = this.getAttribute('data-route');

                    editForm.action = routeTemplate.replace(':id', currencyId);
                    currencyArInput.value = currencyAr;
                    currencyEnInput.value = currencyEn;
                    currencySymbolInput.value = currencySymbol;
                    currencyCodeInput.value = currencyCode;
                    countrySelect.value = countryId;
                    isDefaultCheckbox.checked = isDefault === '1';
                });
            });

            // Show Currency Modal
            const showButtons = document.querySelectorAll('.show-currency-btn');
            const showCurrencyAr = document.getElementById('show-currency-ar');
            const showCurrencyEn = document.getElementById('show-currency-en');
            const showCurrencySymbol = document.getElementById('show-currency-symbol');
            const showCurrencyCode = document.getElementById('show-currency-code');
            const showCountry = document.getElementById('show-country');
            const showIsDefault = document.getElementById('show-is-default');

            showButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currencyAr = this.getAttribute('data-currency-ar');
                    const currencyEn = this.getAttribute('data-currency-en');
                    const currencySymbol = this.getAttribute('data-currency-symbol');
                    const currencyCode = this.getAttribute('data-currency-code');
                    const country = this.getAttribute('data-country');
                    const isDefault = this.getAttribute('data-is-default');

                    showCurrencyAr.textContent = currencyAr;
                    showCurrencyEn.textContent = currencyEn;
                    showCurrencySymbol.textContent = currencySymbol;
                    showCurrencyCode.textContent = currencyCode;
                    showCountry.textContent = country;
                    showIsDefault.textContent = isDefault === '1' ? '@lang('currency.Yes')' : '@lang('currency.No')';
                });
            });
        });

        function delete_item(id) {
            Swal.fire({
                title: @json(__('validation.Alert')),
                text: @json(__('validation.DeleteConfirm')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: @json(__('validation.Delete')),
                cancelButtonText: @json(__('validation.Cancel')),
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = document.getElementById('delete-form-' + id);
                    form.submit();
                }
            });
        }
    </script>
@endsection
