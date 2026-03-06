@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('cash_payment_setting.EditCashPaymentSetting')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('cashPaymentSettings.list') }}">@lang('cash_payment_setting.CashPaymentSettings')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('cash_payment_setting.EditCashPaymentSetting')</li>
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
                            <div class="card-title">@lang('cash_payment_setting.EditCashPaymentSetting')</div>
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
                            <form method="POST" action="{{ route('cashPaymentSetting.update', $cash_payment_setting->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <div class="col-xl-6">
                                        <p class="mb-2 text-muted">@lang('cash_payment_setting.EnforceLimit')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="enforce_limit" value="1"
                                                   class="form-check-input" {{ $cash_payment_setting->enforce_limit ? 'checked' : '' }}
                                                   required>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="enforce_limit" value="0"
                                                   class="form-check-input" {{ !$cash_payment_setting->enforce_limit ? 'checked' : '' }}
                                                   required>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>

                                    <div class="col-xl-6">
                                        <p class="mb-2 text-muted">@lang('cash_payment_setting.Branch')</p>
                                        <select name="branch_id" class="js-example-basic-single form-control" required>
                                            <option value="" disabled>@lang('cash_payment_setting.ChooseBranch')</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                        {{ $branch->id == $cash_payment_setting->branch_id ? 'selected' : '' }}
                                                        @if(!$isSuperAdmin && $branch->id != $managerBranchId) disabled @endif>
                                                    {{ $branch->name_ar . ' | ' . $branch->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">@lang('validation.EnterBranch')</div>
                                    </div>

                                    <!-- Name Fields -->
                                    <div class="col-xl-6">
                                        <label for="min_cash" class="form-label">@lang('cash_payment_setting.MinCash')</label>
                                        <input type="number" name="min_cash" id="min_cash" class="form-control" value="{{ old('min_cash', $cash_payment_setting->min_cash) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterMinCash')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="max_cash" class="form-label">@lang('cash_payment_setting.MaxCash')</label>
                                        <input type="number" name="max_cash" id="max_cash" class="form-control" value="{{ old('max_cash', $cash_payment_setting->max_cash) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterMaxCash')</div>
                                    </div>

                                    <!-- Submit Button -->
                                    <center>
                                        <div class="col-xl-4">
                                            <button type="submit" class="btn btn-primary form-control">@lang('category.save')</button>
                                        </div>
                                    </center>
                                </div>
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

    <script src="https://cdn.tiny.cloud/1/j0eude2g3rvtwte1o6z42lqr0uoeox80bsimaoaka7zp1scf/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea#myeditorinstance_ar', // Replace this CSS selector to match the placeholder element for TinyMCE
            plugins: 'code table lists',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
        });
        tinymce.init({
            selector: 'textarea#myeditorinstance_en', // Replace this CSS selector to match the placeholder element for TinyMCE
            plugins: 'code table lists',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
        });
    </script>
@endsection
