@extends('layouts.custom-master')

@section('styles')
@endsection

@section('content')
@section('errorbody')

    <body>
    @endsection

    <div class="page error-bg" id="particles-js">
        <!-- Start::error-page -->
        <div class="error-page  ">
            <div class="container">
                <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
                    <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                        <div class="my-5 d-flex justify-content-center">
                            <a href="{{ url('dashboard.home') }}">
                                <img src="{{ asset('SiteAssets/images/logo.png') }}" alt="logo"
                                    class="desktop-logo">
                                <img src="{{ asset('SiteAssets/images/logo.png') }}"
                                    alt="logo" class="desktop-dark">
                            </a>
                        </div>
                        <div class="card custom-card rectangle2">
                            <div class="card-body p-5 rectangle3">
                                <p class="h4 fw-semibold mb-2 text-center">@lang('auth.forgetPass')</p>
                                <p class="mb-4 text-muted op-7 fw-normal text-center">@lang('auth.welcome')</p>
                                <form method="POST" action="{{ route('dashboard.password.email') }}" class="needs-validation">
                                    @csrf
                                    <div class="row gy-3">
                                        <!-- Email Input -->
                                        <div class="col-xl-12">
                                            <label for="signin-username" class="form-label text-default">@lang('auth.email')</label>
                                            <input type="text" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                                   id="signin-username" name="email" placeholder="email" value="{{ old('email') }}">
                                            <div class="invalid-feedback">
                                                @error('email')
                                                {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Submit Button -->
                                        <div class="col-xl-12 d-grid mt-2">
                                            <button type="submit" class="btn btn-lg btn-primary">@lang('auth.sendmail')</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End::error-page -->
    </div>
@endsection

@section('scripts')
    <!-- SHOW PASSWORD JS -->
    <script src="{{ asset('build/assets/show-password.js') }}"></script>
@endsection
