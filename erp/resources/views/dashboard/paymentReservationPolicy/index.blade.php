@extends('layouts.master')

@section('styles')
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('policies.updatereservisionpolicy')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="">@lang('policies.reservisionpolicy')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('policies.updatereservisionpolicy')</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- APP-CONTENT START -->
    <div class="main-content app-content ">
        <div class="container-fluid ">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                @lang('policies.updatereservisionpolicy')
                            </div>
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
                            <form method="POST" action="{{ route('payment_reservation_policy.update') }}"
                                class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label for="paymentAr" class="form-label"> @lang('policies.paymentAr')
                                        </label>
                                        <textarea type="text" name="payment_ar" id="myeditorinstance_ar" class="form-control"
                                            placeholder="@lang('policies.paymentAr')" required>{{ $data->payment_ar }}</textarea>
                                        <div class="invalid-feedback">@lang('validation.paymentAr')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label for="paymentEn" class="form-label">@lang('policies.paymentEn')</label>
                                        <textarea type="text" name="payment_en" id="myeditorinstance_en" class="form-control"
                                            placeholder="@lang('policies.paymentEn')" required>{{ $data->payment_en }}</textarea>
                                        <div class="invalid-feedback">@lang('validation.paymentEn')</div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label for="reservationAr" class="form-label"> @lang('policies.reservationAr')
                                        </label>
                                        <textarea type="text" name="reservation_ar" id="myeditorinstance_ar" class="form-control"
                                            placeholder="@lang('policies.reservationAr')" required>{{ $data->reservation_ar }}</textarea>
                                        <div class="invalid-feedback">@lang('validation.reservationAr')</div>
                                    </div>

                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                        <label for="reservationEn" class="form-label">@lang('policies.reservationEn')</label>
                                        <textarea type="text" name="reservation_en" id="myeditorinstance_en" class="form-control"
                                            placeholder="@lang('policies.reservationEn')" required>{{ $data->reservation_en }}</textarea>
                                        <div class="invalid-feedback">@lang('validation.reservationEn')</div>
                                    </div>

                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary " id="input-submit"
                                                value="@lang('category.save')">
                                        </div>
                                    </center>


                                </div>
                            </form>
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

    <!-- FORM VALIDATION JS -->
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    <script src="https://cdn.ckeditor.com/4.25.0-lts/standard/ckeditor.js"></script>
    <script src="https://cdn.tiny.cloud/1/j0eude2g3rvtwte1o6z42lqr0uoeox80bsimaoaka7zp1scf/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
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
