@extends('website.layouts.master')

@section('content')
    <section class="forget-pass">
        <div class="body-images">
            <img src="./SiteAssets/images/meat-bg.png" class="position-absolute meat ">

            <img src="./SiteAssets/images/spoon-bg.png" class="position-absolute spoon ">
        </div>
    </section>

    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"> @lang('header.home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> @lang('header.questions')
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="policy">
        <div class="container py-2">
            <div class="row ">
                <div class="col-12">
                    <h4 class="fw-bold ">
                        @lang('header.questions')
                    </h4>
                </div>
                <div class="col-12">
                    @foreach ($faqs as $faq)
                        <div class="card mt-4 p-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title fw-bold">
                                    {{ html_entity_decode(strip_tags($faq['question'])) }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">
                                    {{ html_entity_decode(strip_tags($faq['answer'])) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
