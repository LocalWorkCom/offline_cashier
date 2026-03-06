@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('client.show')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('client.index') }}">@lang('client.clients')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('client.show')</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- APP-CONTENT START -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start:: row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('client.show')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.name')</label>
                                    <p class="form-text">{{ $client->name }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.email')</label>
                                    <p class="form-text">{{ $client->email }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.country')</label>
                                    <p class="form-text">{{ $client->country_id ? $client->country?->name_site : '-----' }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.phone')</label>
                                    <p class="form-text">{{ $client->phone }}</p>
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.dob')</label>
                                    <p class="form-text">
                                        {{ $client->birth_date ?? __('client.none') }}
                                    </p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.address')</label>
                                    <p class="form-text">
                                        {{$client->addresses->isNotEmpty() ? $client->addresses->first()->address : __('client.none') }}
                                    </p>
                                </div>
                                @if ($client->addresses->isNotEmpty())
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.city')</label>
                                    <p class="form-text">{{ $client->addresses->first()->city }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.state')</label>
                                    <p class="form-text">{{ $client->addresses->first()->state }}</p>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.postalCode')</label>
                                    <p class="form-text">{{ $client->addresses->first()->postal_code }}</p>
                                </div>
                            @endif
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.is_active')</label>
                                    <p class="form-text">
                                        {{ $client->is_active == 1 ? __('client.yes') : __('client.no') }}
                                    </p>
                                </div>
                                {{-- <div class="col-xl-4 col-lg-6 col-md-6 col-sm-8">
                                    <label class="form-label">@lang('client.img')</label>
                                    @if ($client->clientDetails && $client->clientDetails->image)
                                        <div class="mb-3">
                                            <img src="{{ asset($client->clientDetails->image) }}" alt="Client Image"
                                                width="100" height="100">
                                        </div>
                                    @else
                                        <p class="form-text">@lang('client.none')</p>
                                    @endif
                                </div> --}}
                            </div>
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

    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
