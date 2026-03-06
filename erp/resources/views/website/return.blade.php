@extends('website.layouts.master')

@section('content')
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('term.Home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('return') }}">@lang('term.Returns')</a></li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="contact-us">
        <div class="container py-2">
            <div class="row">
                <div class="col-12">
                    <div class="col-12">
                        <h4 class="fw-bold ">@lang('term.Returns')</h4>
                    </div>
                    <div class="col-12">
                        @foreach($returnsArray as $return)
                            <div class="card mt-4 p-4">
                                <div class="card-header bg-white border-bottom">
                                    <h5 class="card-title fw-bold">{{ $return['title'] }}</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">{!! $return['description'] !!}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
