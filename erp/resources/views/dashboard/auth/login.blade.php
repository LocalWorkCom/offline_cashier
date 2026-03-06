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
                            <a href="{{ route('home') }}">
                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/logo.png') }}" alt="logo" class="desktop-logo" width="300px">
                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/logo.png') }}" alt="logo" width="300px"
                                    class="desktop-dark">
                            </a>
                        </div>

                        <div class="card custom-card rectangle2">
                            <div class="card-body p-5 rectangle3">


                                <p class="h4 fw-semibold mb-2 text-center">@lang('auth.signin')</p>
                                <p class="mb-4 text-muted op-7 fw-normal text-center">@lang('auth.welcome') </p>
                                <form method="POST" action="{{ route('dashboard.submitlogin') }}">
                                    @csrf
                                    <div class="row gy-3">
                                        <div class="col-xl-12">
                                            <label for="signin-username" class="form-label text-default">@lang('auth.email_or_phone')</label>
                                            <input type="text" class="form-control form-control-lg @error('user_login') is-invalid @enderror" name="email_or_phone" value="{{ old('email_or_phone') }}">
                                            @error('email_or_phone')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-xl-12">
                                            <label for="signin-password" class="form-label text-default">@lang('auth.passplace')</label>
                                            <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password">
                                            @error('password')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-xl-12 d-flex justify-content-between">
                                            <button type="submit" class="btn btn-primary text-center w-100 btn-lg">@lang('auth.login')</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="d-flex">
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="{{ route('set-locale', 'en') }}">
                                        <span class="avatar avatar-xs lh-1 me-2">
                                            <img src="{{ asset('build/assets/images/flags/us_flag.png') }}"
                                                alt="img">
                                        </span>
                                        @lang('auth.English')
                                    </a>

                                    <a class="dropdown-item d-flex align-items-center"
                                        href="{{ route('set-locale', 'ar') }}">
                                        <span class="avatar avatar-xs lh-1 me-2">
                                            <img src="{{ asset('build/assets/images/flags/ar_flag.png') }}"
                                                alt="img">
                                        </span>
                                        @lang('auth.Arabic')
                                    </a>
                                </div>
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
    <script>
        function togglePasswordVisibility(inputId, button) {
            var passwordField = document.getElementById(inputId);
            var icon = button.querySelector('i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            }
        }
    </script>
@endsection
