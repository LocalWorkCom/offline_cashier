@extends('website.layouts.master')

@section('content')

<main>
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('payment.Home') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> {{ __('payment.transactionDetails') }} </li>
                </ol>
            </nav>
        </div>

    </section>

    <section class="addresses">
        <div class="container pb-sm-5 pb-4">
            <div class="card p-5 w-50 text-center mx-auto mt-5">
                <div class=" d-flex justify-content-center">
                    <img src="@if($transaction_payment_status == 'paid') {{ asset('front/AlKout-Resturant/SiteAssets/images/donee.png') }} @else {{ asset('front/AlKout-Resturant/SiteAssets/images/ix_error-filled.png') }} @endif" alt="Logo" height="110">
                </div>
                <h2 class="my-4 fw-bold"> {{$transaction_payment_status == "paid" || $transaction_payment_status == "part" ? __('payment.transactionSuccessful') : __('payment.transactionFailed')}} </h2>
                <a href="{{ $transaction_payment_status == 'paid' ? route('orders.tracking') : route('home') }}" class="btn mt-3 w-100">  {{$transaction_payment_status == "paid" ? __('payment.goToOrderTracking') : __('payment.backToHome')}} </a>
            </div>
        </div>
    </section>
</main>

@include('website.cart.global')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        localStorage.removeItem('cart');
        updateCartCount();
    });
</script>

@endsection
