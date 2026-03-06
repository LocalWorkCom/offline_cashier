@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/4.25.0-lts/standard/ckeditor.js"></script>

@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('cash_payment_setting.AddCashPaymentSetting')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('cashPaymentSettings.list') }}">@lang('cash_payment_setting.CashPaymentSettings')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('cash_payment_setting.AddCashPaymentSetting')</li>
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
                            <div class="card-title">@lang('cash_payment_setting.AddCashPaymentSetting')</div>
                        </div>
                        <div class="card-body">
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
                            <form method="POST" action="{{ route('cashPaymentSetting.store') }}" class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-xl-6">
                                        <p class="mb-2 text-muted">@lang('cash_payment_setting.EnforceLimit')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="enforce_limit" value="1" class="form-check-input" checked required>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="enforce_limit" value="0" class="form-check-input" required>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <p class="mb-2 text-muted">@lang('cash_payment_setting.Branch')</p>
                                        <select name="branch_id" class="form-control" required>
                                            <option value="" disabled @if(!$isSuperAdmin) hidden @endif>
                                                @lang('cash_payment_setting.ChooseBranch')
                                            </option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                        @if(!$isSuperAdmin && $branch->id == $managerBranchId)
                                                            selected
                                                        @elseif($isSuperAdmin)
                                                            {{-- No selection restriction for superAdmin --}}
                                                        @endif
                                                        @if(!$isSuperAdmin && $branch->id != $managerBranchId)
                                                            disabled
                                                    @endif>
                                                    {{ $branch->name_ar . " | " . $branch->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterBranch')</div>
                                    </div>


                                    <div class="col-xl-6">
                                        <label for="code" class="form-label">@lang('cash_payment_setting.MinCash')</label>
                                        <input type="number" name="min_cash" id="code" class="form-control" placeholder="@lang('cash_payment_setting.MinCash')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterMinCash')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="value" class="form-label">@lang('cash_payment_setting.MaxCash')</label>
                                        <input type="number" name="max_cash" id="value" class="form-control" placeholder="@lang('cash_payment_setting.MaxCash')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterMaxCash')</div>
                                    </div>
                                </div>

                                    <!-- Submit Button -->
                                    <center>
                                        <div class="col-xl-4 mt-3">
                                            <button type="submit" class="btn btn-primary form-control">@lang('category.save')</button>
                                        </div>
                                    </center>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Custom JS -->
    @vite('resources/assets/js/validation.js')
@endsection



