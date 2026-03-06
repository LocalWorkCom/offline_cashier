@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endsection

@section('content')
    <!-- Page Header -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('hotel.Hotels')</h4>
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">@lang('sidebar.Main')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('hotel.Hotels')</li>
            </ol>
        </nav>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Hotels Table -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">@lang('hotel.Hotels')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create hotels', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#createModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>@lang('hotel.AddHotel')
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <!-- Success/Error Messages -->
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
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif

                            <!-- Hotels DataTable -->
                            <table class="table table-bordered text-nowrap" style="width:100%" id="hotels-table">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('hotel.ID')</th>
                                        <th scope="col">@lang('hotel.ArabicName')</th>
                                        <th scope="col">@lang('hotel.EnglishName')</th>
                                        <th scope="col">@lang('hotel.ArabicBranch')</th>
                                        <th scope="col">@lang('hotel.EnglishBranch')</th>
                                        <th scope="col">@lang('hotel.Status')</th>
                                        <th scope="col">@lang('hotel.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($hotels as $hotel)
                                        <tr>
                                            <td>{{ $hotel->id }}</td>
                                            <td>{{ $hotel->name_ar }}</td>
                                            <td>{{ $hotel->name_en }}</td>
                                            <td>{{ $hotel->branch_name_ar ?? 'N/A' }}</td>
                                            <td>{{ $hotel->branch_name_en ?? 'N/A' }}</td>
                                            <td>{{ $hotel->status ? __('hotel.StatusActive') : __('hotel.StatusInactive') }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view hotels', 'admin'))
                                                    <a href="javascript:void(0);" class="btn btn-info-light btn-wave show-hotel-btn"
                                                        data-id="{{ $hotel->id }}"
                                                        data-name-ar="{{ $hotel->name_ar }}"
                                                        data-name-en="{{ $hotel->name_en }}"
                                                        data-branch-ar="{{ $hotel->branch_name_ar }}"
                                                        data-branch-en="{{ $hotel->branch_name_en }}"
                                                        data-phone="{{ $hotel->phone_number }}"
                                                        data-note="{{ $hotel->note }}"
                                                        data-building-number="{{ $hotel->building_number }}"
                                                        data-status="{{ $hotel->status }}"
                                                        data-address-ar="{{ $hotel->address_ar }}"
                                                        data-address-en="{{ $hotel->address_en }}"
                                                        data-bs-toggle="modal" data-bs-target="#showModal">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('update hotels', 'admin'))
                                                    <button type="button" class="btn btn-orange-light btn-wave edit-hotel-btn"
                                                        data-id="{{ $hotel->id }}"
                                                        data-name-ar="{{ $hotel->name_ar }}"
                                                        data-name-en="{{ $hotel->name_en }}"
                                                        data-branch-ar="{{ $hotel->branch_name_ar }}"
                                                        data-branch-en="{{ $hotel->branch_name_en }}"
                                                        data-phone="{{ $hotel->phone_number }}"
                                                        data-note="{{ $hotel->note }}"
                                                        data-building-number="{{ $hotel->building_number }}"
                                                        data-status="{{ $hotel->status }}"
                                                        data-address-ar="{{ $hotel->address_ar }}"
                                                        data-address-en="{{ $hotel->address_en }}"
                                                        data-country-id="{{ $hotel->country_id }}"
                                                        data-city-id="{{ $hotel->city_id }}"
                                                        data-area-id="{{ $hotel->area_id }}"
                                                        data-route="{{ route('hotel.update', ':id') }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('delete hotels', 'admin'))
                                                    <form class="d-inline" id="delete-form-{{ $hotel->id }}"
                                                        action="{{ route('hotel.delete', $hotel->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        @if ($hotel->status)
                                                            <button type="button" onclick="deleteItem('{{ $hotel->id }}')"
                                                                class="btn btn-danger-light btn-wave">
                                                                @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        @endif
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
        </div>
    </div>

    <!-- Create Hotel Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('hotel.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title" id="createModalLabel">@lang('hotel.AddHotel')</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <!-- Arabic Name -->
                            <div class="col-xl-6">
                                <label for="name_ar" class="form-label">@lang('hotel.ArabicName')</label>
                                <input type="text" class="form-control" id="name_ar" name="name_ar" placeholder="@lang('hotel.ArabicName')" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                            </div>
                            <!-- English Name -->
                            <div class="col-xl-6">
                                <label for="name_en" class="form-label">@lang('hotel.EnglishName')</label>
                                <input type="text" class="form-control" id="name_en" name="name_en" placeholder="@lang('hotel.EnglishName')" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                            </div>
                            <!-- Arabic Address -->
                            <div class="col-xl-6">
                                <label for="address_ar" class="form-label">@lang('hotel.ArabicAddress')</label>
                                <input type="text" class="form-control" id="address_ar" name="address_ar" placeholder="@lang('hotel.ArabicAddress')" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterArabicAddress')</div>
                            </div>
                            <!-- English Address -->
                            <div class="col-xl-6">
                                <label for="address_en" class="form-label">@lang('hotel.EnglishAddress')</label>
                                <input type="text" class="form-control" id="address_en" name="address_en" placeholder="@lang('hotel.EnglishAddress')" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterEnglishAddress')</div>
                            </div>
                            <!-- Country -->
                            <div class="col-xl-4">
                                <label for="country_id" class="form-label">@lang('branch.Country')</label>
                                <select class="form-control select2" id="country_id" name="country_id" required>
                                    <option value="" disabled selected>@lang('branch.SelectCountry')</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name_ar }} | {{ $country->name_en }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">@lang('validation.EnterCountry')</div>
                            </div>
                            <!-- City -->
                            <div class="col-xl-4">
                                <label for="city_id" class="form-label">@lang('country.ChooseCity')</label>
                                <select class="form-control select2" id="city_id" name="city_id" required disabled>
                                    <option value="" disabled selected>@lang('country.ChooseCity')</option>
                                </select>
                                <div class="invalid-feedback">@lang('validation.EnterCity')</div>
                            </div>
                            <!-- Region -->
                            <div class="col-xl-4">
                                <label for="region_id" class="form-label">@lang('country.ChooseRegion')</label>
                                <select class="form-control select2" id="region_id" name="area_id" required disabled>
                                    <option value="" disabled selected>@lang('country.ChooseRegion')</option>
                                </select>
                                <div class="invalid-feedback">@lang('validation.EnterState')</div>
                            </div>
                            <!-- Arabic Branch -->
                            <div class="col-xl-6">
                                <label for="branch_ar" class="form-label">@lang('hotel.ArabicBranch')</label>
                                <input type="text" class="form-control" id="branch_ar" name="branch_name_ar" placeholder="@lang('hotel.ArabicBranch')">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterArabicBranch')</div>
                            </div>
                            <!-- English Branch -->
                            <div class="col-xl-6">
                                <label for="branch_en" class="form-label">@lang('hotel.EnglishBranch')</label>
                                <input type="text" class="form-control" id="branch_en" name="branch_name_en" placeholder="@lang('hotel.EnglishBranch')">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterEnglishBranch')</div>
                            </div>
                            <!-- Phone Number -->
                            <div class="col-xl-6">
                                <label for="phone" class="form-label">@lang('hotel.Phone')</label>
                                <input type="text" class="form-control" id="phone" name="phone_number" placeholder="@lang('hotel.Phone')" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterPhone')</div>
                            </div>
                            <!-- Note -->
                            <div class="col-xl-6">
                                <label for="note" class="form-label">@lang('hotel.Note')</label>
                                <input type="text" class="form-control" id="note" name="note" placeholder="@lang('hotel.Note')">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterNote')</div>
                            </div>
                            <!-- Building Number -->
                            <div class="col-xl-6">
                                <label for="building_number" class="form-label">@lang('hotel.BuildingNumber')</label>
                                <input type="text" class="form-control" id="building_number" name="building_number" placeholder="@lang('hotel.BuildingNumber')" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterBuildingNumber')</div>
                            </div>
                            <!-- Status -->
                            <div class="col-xl-4">
                                <label for="status" class="form-label">@lang('hotel.Status')</label>
                                <select class="form-control select2" id="status" name="status" required>
                                    <option value="" disabled selected>@lang('hotel.SelectStatus')</option>
                                    <option value="1">@lang('hotel.StatusActive')</option>
                                    <option value="0">@lang('hotel.StatusInactive')</option>
                                </select>
                                <div class="invalid-feedback">@lang('validation.EnterStatus')</div>
                            </div>
                            <!-- Shipping Cost -->
                            <!-- <div class="col-xl-6">
                                <label for="shipping_cost" class="form-label">@lang('hotel.ShippingCost')</label>
                                <input type="text" class="form-control" id="shipping_cost" name="shiping_cost" placeholder="@lang('hotel.ShippingCost')" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterShippingCost')</div>
                            </div> -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('modal.close')</button>
                        <button type="submit" class="btn btn-outline-primary">@lang('modal.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Hotel Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-hotel-form" action="" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title" id="editModalLabel">@lang('hotel.EditHotel')</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-4">
                            <!-- Arabic Name -->
                            <div class="col-xl-6">
                                <label for="edit-name-ar" class="form-label">@lang('hotel.ArabicName')</label>
                                <input type="text" class="form-control" id="edit-name-ar" name="name_ar" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                            </div>
                            <!-- English Name -->
                            <div class="col-xl-6">
                                <label for="edit-name-en" class="form-label">@lang('hotel.EnglishName')</label>
                                <input type="text" class="form-control" id="edit-name-en" name="name_en" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                            </div>
                            <!-- Arabic Address -->
                            <div class="col-xl-6">
                                <label for="edit-address-ar" class="form-label">@lang('hotel.ArabicAddress')</label>
                                <input type="text" class="form-control" id="edit-address-ar" name="address_ar" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterArabicAddress')</div>
                            </div>
                            <!-- English Address -->
                            <div class="col-xl-6">
                                <label for="edit-address-en" class="form-label">@lang('hotel.EnglishAddress')</label>
                                <input type="text" class="form-control" id="edit-address-en" name="address_en" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterEnglishAddress')</div>
                            </div>
                            <!-- Country -->
                            <div class="col-xl-4">
                                <label for="edit-country-id" class="form-label">@lang('branch.Country')</label>
                                <select class="form-control select2" id="edit-country-id" name="country_id" required>
                                    <option value="" disabled selected>@lang('branch.SelectCountry')</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name_ar }} | {{ $country->name_en }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">@lang('validation.EnterCountry')</div>
                            </div>
                            <!-- City -->
                            <div class="col-xl-4">
                                <label for="edit-city-id" class="form-label">@lang('country.ChooseCity')</label>
                                <select class="form-control select2" id="edit-city-id" name="city_id" required disabled>
                                    <option value="" disabled selected>@lang('country.ChooseCity')</option>
                                </select>
                                <div class="invalid-feedback">@lang('validation.EnterCity')</div>
                            </div>
                            <!-- Region -->
                            <div class="col-xl-4">
                                <label for="edit-region-id" class="form-label">@lang('country.ChooseRegion')</label>
                                <select class="form-control select2" id="edit-region-id" name="area_id" required disabled>
                                    <option value="" disabled selected>@lang('country.ChooseRegion')</option>
                                </select>
                                <div class="invalid-feedback">@lang('validation.EnterState')</div>
                            </div>
                            <!-- Arabic Branch -->
                            <div class="col-xl-6">
                                <label for="edit-branch-ar" class="form-label">@lang('hotel.ArabicBranch')</label>
                                <input type="text" class="form-control" id="edit-branch-ar" name="branch_name_ar">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterArabicBranch')</div>
                            </div>
                            <!-- English Branch -->
                            <div class="col-xl-6">
                                <label for="edit-branch-en" class="form-label">@lang('hotel.EnglishBranch')</label>
                                <input type="text" class="form-control" id="edit-branch-en" name="branch_name_en">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterEnglishBranch')</div>
                            </div>
                            <!-- Phone Number -->
                            <div class="col-xl-6">
                                <label for="edit-phone" class="form-label">@lang('hotel.Phone')</label>
                                <input type="text" class="form-control" id="edit-phone" name="phone_number" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterPhone')</div>
                            </div>
                            <!-- Note -->
                            <div class="col-xl-6">
                                <label for="edit-note" class="form-label">@lang('hotel.Note')</label>
                                <input type="text" class="form-control" id="edit-note" name="note">
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterNote')</div>
                            </div>
                            <!-- Building Number -->
                            <div class="col-xl-6">
                                <label for="edit-building-number" class="form-label">@lang('hotel.BuildingNumber')</label>
                                <input type="text" class="form-control" id="edit-building-number" name="building_number" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterBuildingNumber')</div>
                            </div>
                            <!-- Status -->
                            <div class="col-xl-4">
                                <label for="edit-status" class="form-label">@lang('hotel.Status')</label>
                                <select class="form-control select2" id="edit-status" name="status" required>
                                    <option value="" disabled selected>@lang('hotel.SelectStatus')</option>
                                    <option value="1">@lang('hotel.StatusActive')</option>
                                    <option value="0">@lang('hotel.StatusInactive')</option>
                                </select>
                                <div class="invalid-feedback">@lang('validation.EnterStatus')</div>
                            </div>
                            <!-- Shipping Cost -->
                            <!-- <div class="col-xl-6">
                                <label for="edit-shipping-cost" class="form-label">@lang('hotel.ShippingCost')</label>
                                <input type="text" class="form-control" id="edit-shipping-cost" name="shiping_cost" required>
                                <div class="valid-feedback">@lang('validation.Correct')</div>
                                <div class="invalid-feedback">@lang('validation.EnterShippingCost')</div>
                            </div> -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('modal.close')</button>
                        <button type="submit" class="btn btn-outline-primary">@lang('modal.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Show Hotel Modal -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="showModalLabel">@lang('hotel.ShowHotel')</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-4">
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.ArabicName')</label>
                            <p id="show-name-ar" class="form-control-static"></p>
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.EnglishName')</label>
                            <p id="show-name-en" class="form-control-static"></p>
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.ArabicBranch')</label>
                            <p id="show-branch-ar" class="form-control-static"></p>
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.EnglishBranch')</label>
                            <p id="show-branch-en" class="form-control-static"></p>
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.Phone')</label>
                            <p id="show-phone" class="form-control-static"></p>
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.Note')</label>
                            <p id="show-note" class="form-control-static"></p>
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.BuildingNumber')</label>
                            <p id="show-building-number" class="form-control-static"></p>
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.Status')</label>
                            <p id="show-status" class="form-control-static"></p>
                        </div>
                        <!-- <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.ShippingCost')</label>
                            <p id="show-shipping-cost" class="form-control-static"></p>
                        </div> -->
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.ArabicAddress')</label>
                            <p id="show-address-ar" class="form-control-static"></p>
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label">@lang('hotel.EnglishAddress')</label>
                            <p id="show-address-en" class="form-control-static"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('modal.close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <!-- Internal Scripts -->
    @vite('resources/assets/js/datatables.js')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize DataTable
            $('#hotels-table').DataTable({
                responsive: true,
                buttons: ['copy', 'excel', 'pdf', 'print']
            });

            // Edit Modal Handling
            const editButtons = document.querySelectorAll('.edit-hotel-btn');
            const editForm = document.getElementById('edit-hotel-form');
            const editNameAr = document.getElementById('edit-name-ar');
            const editNameEn = document.getElementById('edit-name-en');
            const editBranchAr = document.getElementById('edit-branch-ar');
            const editBranchEn = document.getElementById('edit-branch-en');
            const editPhone = document.getElementById('edit-phone');
            const editNote = document.getElementById('edit-note');
            const editBuildingNumber = document.getElementById('edit-building-number');
            const editStatus = document.getElementById('edit-status');
            // const editShippingCost = document.getElementById('edit-shipping-cost');
            const editAddressAr = document.getElementById('edit-address-ar');
            const editAddressEn = document.getElementById('edit-address-en');
            const editCountrySelect = document.getElementById('edit-country-id');
            const editCitySelect = document.getElementById('edit-city-id');
            const editRegionSelect = document.getElementById('edit-region-id');

            editButtons.forEach(button => {
                button.addEventListener('click', async function () {
                    const hotelId = this.getAttribute('data-id');
                    const routeTemplate = this.getAttribute('data-route');
                    editForm.action = routeTemplate.replace(':id', hotelId);

                    // Populate form fields
                    editNameAr.value = this.getAttribute('data-name-ar');
                    editNameEn.value = this.getAttribute('data-name-en');
                    editBranchAr.value = this.getAttribute('data-branch-ar');
                    editBranchEn.value = this.getAttribute('data-branch-en');
                    editPhone.value = this.getAttribute('data-phone');
                    editNote.value = this.getAttribute('data-note');
                    editBuildingNumber.value = this.getAttribute('data-building-number');
                    editStatus.value = this.getAttribute('data-status');
                    // editShippingCost.value = this.getAttribute('data-shipping-cost');
                    editAddressAr.value = this.getAttribute('data-address-ar');
                    editAddressEn.value = this.getAttribute('data-address-en');
                    const countryId = this.getAttribute('data-country-id');
                    const cityId = this.getAttribute('data-city-id');
                    const areaId = this.getAttribute('data-area-id');

                    // Populate country
                    editCountrySelect.value = countryId || '';
                    if (countryId) {
                        await fetchCities(countryId, editCitySelect, cityId);
                        if (cityId) {
                            await fetchRegions(cityId, editRegionSelect, areaId);
                        }
                    }
                });
            });

            // Show Modal Handling
            const showButtons = document.querySelectorAll('.show-hotel-btn');
            const showNameAr = document.getElementById('show-name-ar');
            const showNameEn = document.getElementById('show-name-en');
            const showBranchAr = document.getElementById('show-branch-ar');
            const showBranchEn = document.getElementById('show-branch-en');
            const showPhone = document.getElementById('show-phone');
            const showNote = document.getElementById('show-note');
            const showBuildingNumber = document.getElementById('show-building-number');
            const showStatus = document.getElementById('show-status');
            // const showShippingCost = document.getElementById('show-shipping-cost');
            const showAddressAr = document.getElementById('show-address-ar');
            const showAddressEn = document.getElementById('show-address-en');

            showButtons.forEach(button => {
                button.addEventListener('click', function () {
                    showNameAr.textContent = this.getAttribute('data-name-ar');
                    showNameEn.textContent = this.getAttribute('data-name-en');
                    showBranchAr.textContent = this.getAttribute('data-branch-ar');
                    showBranchEn.textContent = this.getAttribute('data-branch-en');
                    showPhone.textContent = this.getAttribute('data-phone');
                    showNote.textContent = this.getAttribute('data-note');
                    showBuildingNumber.textContent = this.getAttribute('data-building-number');
                    showStatus.textContent = this.getAttribute('data-status') == '1' ? '@lang('hotel.StatusActive')' : '@lang('hotel.StatusInactive')';
                    // showShippingCost.textContent = this.getAttribute('data-shipping-cost');
                    showAddressAr.textContent = this.getAttribute('data-address-ar');
                    showAddressEn.textContent = this.getAttribute('data-address-en');
                });
            });

            // Create Modal: Country, City, Region Handling
            const createCountrySelect = document.getElementById('country_id');
            const createCitySelect = document.getElementById('city_id');
            const createRegionSelect = document.getElementById('region_id');

            createCountrySelect.addEventListener('change', function () {
                const countryId = this.value;
                fetchCities(countryId, createCitySelect);
            });

            createCitySelect.addEventListener('change', function () {
                const cityId = this.value;
                fetchRegions(cityId, createRegionSelect);
            });

            // Edit Modal: Country, City, Region Handling
            editCountrySelect.addEventListener('change', function () {
                const countryId = this.value;
                fetchCities(countryId, editCitySelect);
            });

            editCitySelect.addEventListener('change', function () {
                const cityId = this.value;
                fetchRegions(cityId, editRegionSelect);
            });

            // Fetch Cities
            async function fetchCities(countryId, citySelect, selectedCityId = null) {
                if (!countryId) {
                    citySelect.innerHTML = '<option value="" disabled selected>@lang('country.ChooseCity')</option>';
                    citySelect.disabled = true;
                    fetchRegions(null, editRegionSelect || createRegionSelect);
                    return;
                }

                try {
                    const response = await fetch(`/dashboard/city/hotel/${countryId}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await response.json();
                    citySelect.innerHTML = '<option value="" disabled selected>@lang('country.ChooseCity')</option>';
                    data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = `${city.name_ar} | ${city.name_en}`;
                        if (selectedCityId && city.id == selectedCityId) {
                            option.selected = true;
                        }
                        citySelect.appendChild(option);
                    });
                    citySelect.disabled = false;
                } catch (error) {
                    console.error('Error fetching cities:', error);
                    citySelect.innerHTML = '<option value="" disabled selected>@lang('country.ChooseCity')</option>';
                    citySelect.disabled = true;
                }
            }

            // Fetch Regions
            async function fetchRegions(cityId, regionSelect, selectedAreaId = null) {
                if (!cityId) {
                    regionSelect.innerHTML = '<option value="" disabled selected>@lang('country.ChooseRegion')</option>';
                    regionSelect.disabled = true;
                    return;
                }

                try {
                    const response = await fetch(`/dashboard/region/hotel/${cityId}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await response.json();
                    regionSelect.innerHTML = '<option value="" disabled selected>@lang('country.ChooseRegion')</option>';
                    data.forEach(region => {
                        const option = document.createElement('option');
                        option.value = region.id;
                        option.textContent = `${region.name_ar} | ${region.name_en}`;
                        if (selectedAreaId && region.id == selectedAreaId) {
                            option.selected = true;
                        }
                        regionSelect.appendChild(option);
                    });
                    regionSelect.disabled = false;
                } catch (error) {
                    console.error('Error fetching regions:', error);
                    regionSelect.innerHTML = '<option value="" disabled selected>@lang('country.ChooseRegion')</option>';
                    regionSelect.disabled = true;
                }
            }

            // Delete Confirmation
            window.deleteItem = function (id) {
                Swal.fire({
                    title: @json(__('validation.Alert')),
                    text: @json(__('validation.DeleteConfirm')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: @json(__('validation.Delete')),
                    cancelButtonText: @json(__('validation.Cancel')),
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-form-${id}`).submit();
                    }
                });
            };
        });
    </script>
@endsection