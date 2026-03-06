@extends('website.layouts.master')

@section('content')
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4 mt-3 ">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('term.Home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> <a href="{{ route('contactUs') }}">@lang('term.ContactUs')  </a></li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="contact-us">
        <div class="container py-2 pb-5">
            <div class="row">
                <div class="col-12">
                    <h4 class="fw-bold"> @lang('term.ContactUs')</h4>
                    <div class="d-flex justify-content-center align-items-center mb-1">
                        <img src="{{ asset('build/assets/images/media/contact-us.png') }}" alt="contactus">
                    </div>
                    <h5 class="text-center text-muted">
                        @lang('term.NeedContactUs')
                    </h5>
                    <div class="card mt-4 p-4">
                        <div class="card-header bg-white custom-border">
                            <h5 class="card-title fw-bold text-center">
                                @lang('term.ContactUsTrough')
                            </h5>
                        </div>

                        <div class="card-body">
                            @foreach ($branches as $branch)
                                <div class="location border-bottom">
                                    <h5 class="fw-bold my-3">
                                        <i class="fas fa-map-marker-alt main-color mx-2"></i>
                                        {{ $branch->{"name_" . app()->getLocale()} }}
                                    </h5>
                                    <p class="text-muted my-3">
                                        {{ $branch->{"address_" . app()->getLocale()} }}
                                    </p>
                                    <h5 class="text-muted fw-bold my-3">
                                        <i class="fas fa-phone main-color mx-2"></i>
                                        {{ $branch->phone }}
                                    </h5>
                                    @if ($branch->email)
                                        <h5 class="text-muted fw-bold my-3">
                                            <i class="fas fa-envelope main-color mx-2"></i>
                                            {{ $branch->email }}
                                        </h5>
                                    @endif
                                    @php
                                        $branchTimes = $branch->branchTimes()->where('is_active', 1)->get();
                                    @endphp
                                    @if ($branchTimes->isNotEmpty())
                                        <p class="text-muted my-3">
                                            <i class="fas fa-clock main-color mx-2"></i>
                                            {{ __('header.workingtimes') }}:
                                            @foreach ($branchTimes->groupBy('day') as $day => $times)
                                                @php
                                                    $dayName = [
                                                        0 => __('header.sunday'),
                                                        1 => __('header.monday'),
                                                        2 => __('header.tuesday'),
                                                        3 => __('header.wednesday'),
                                                        4 => __('header.thursday'),
                                                        5 => __('header.friday'),
                                                        6 => __('header.saturday'),
                                                    ][$day];

                                                    $timeRanges = $times
                                                        ->map(function ($time) {
                                                            $opening = formatTimeToArabic($time->opening_hour);
                                                            $closing = formatTimeToArabic($time->closing_hour);
                                                            return "{$opening} - {$closing}";
                                                        })
                                                        ->join(', ');
                                                @endphp
                                                <br>{{ $dayName }}: {{ $timeRanges }}
                                            @endforeach
                                        </p>
                                    @else
                                        <p class="text-muted my-3">
                                            <i class="fas fa-clock main-color mx-2"></i>
                                            @lang('header.no_hours_available')
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
