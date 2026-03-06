@extends('website.layouts.master')

@section('content')
    {{-- <section class="forget-pass">
        <div class="body-images">
            <img src="./SiteAssets/images/meat-bg.png" class="position-absolute meat ">

            <img src="./SiteAssets/images/spoon-bg.png" class="position-absolute spoon ">
        </div>
    </section> --}}

    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('header.home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('header.coupons')</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="coupons">
        <div class="container py-2">
            <h4 class="fw-bold">@lang('header.coupons')</h4>
            <ul class="nav nav-pills nav-justified px-0 pt-3 mb-5" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="{{ route('show.coupons', ['type' => 'active']) }}"
                        class="nav-link {{ $status == 'active' ? 'active' : '' }}">
                        <h5 class="fw-bold mb-0">@lang('header.active')</h5>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('show.coupons', ['type' => 'used']) }}"
                        class="nav-link {{ $status == 'used' ? 'active' : '' }}">
                        <h5 class="fw-bold mb-0">@lang('header.used')</h5>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('show.coupons', ['type' => 'inactive']) }}"
                        class="nav-link {{ $status == 'inactive' ? 'active' : '' }}">
                        <h5 class="fw-bold mb-0">@lang('header.inactive')</h5>
                    </a>
                </li>
            </ul>

            @forelse ($coupons as $coupon)
                <div class="card p-3 mb-3 {{ $status == 'used' || $status == 'inactive' ? 'disabled' : '' }}">
                    <div class="card-body d-flex justify-content-between align-items-end">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-gift main-color fa-lg"></i>
                            <div class="d-inline-block mx-3">
                                <p class="card-title fw-bold d-inline-block">
                                    @lang('header.discount')
                                    <span class="main-color">{{ $coupon->coupon->value }}
                                        {{ $coupon->coupon->type == 'percentage' ? '%' : Auth::user()->country->currency_symbol ?? 'جنيه' }}
                                    </span>
                                    @lang('header.for first order')
                                </p>
                                <p class="card-text text-muted">
                                    {{ $coupon->coupon->end_date
                                        ? __('header.active_to') . 
                                            \Carbon\Carbon::parse($coupon->coupon->end_date)
                                                ->locale(app()->getLocale())
                                                ->translatedFormat('d M Y h:i A')
                                        : __('header.unknown') }}                                    
                                    {{-- {{ $coupon->coupon->end_date ? 'سارية حتى ' . \Carbon\Carbon::parse($coupon->coupon->end_date)->locale('ar')->translatedFormat('d M Y h:i A') : 'غير محددة' }} --}}

                                </p>
                            </div>
                        </div>
                        {{-- @if ($status == 'active') --}}
                        <div class="copy-coupon" onclick="copyCoupon('{{ $coupon->coupon->code }}')">
                            <i class="fas fa-copy main-color"></i>
                            <a class="main-color">
                                @lang('header.copy_coupon')
                            </a>
                        </div>
                        {{-- @else
                            <p class="text-muted">هذا الكوبون {{ $status == 'used' ? 'استخدم' : 'منته' }}.</p>
                        @endif --}}
                    </div>
                </div>
            @empty
                <p class="text-center">
                    @lang('header.no_coupons')
                    {{ $status == 'active' ? 'سارية' : ($status == 'used' ? __('header.used') : __('header.inactive')) }}
                </p>
            @endforelse


        </div>
    </section>
@endsection
{{-- <script>
    function copyCoupon(couponCode) {
        // Copy the coupon code to the clipboard
        navigator.clipboard.writeText(couponCode).then(() => {
            // Show a success message
            alert('تم نسخ الكوبون');
        }).catch(err => {
            // Handle any error
            console.error('Failed to copy coupon:', err);
        });
    }
</script> --}}
<script>
    function copyCoupon(couponCode) {
        // Copy the coupon code to the clipboard
        navigator.clipboard.writeText(couponCode).then(() => {
            // Show a SweetAlert success message
            Swal.fire({
                title: '@lang('header.coupon copy success')',
                text: `@lang('header.code copy success'): ${couponCode}`,
                icon: 'success',
                confirmButtonText: '@lang('header.ok')'
            });
        }).catch(err => {
            // Handle any error with SweetAlert
            Swal.fire({
                title: '@lang('header.wrong')',
                text: '@lang('header.fail to copy coupon so retry again')',
                icon: 'error',
                confirmButtonText: '@lang('header.ok')'
            });
            console.error('Failed to copy coupon:', err);
        });
    }
</script>
