@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('complaints.ShowComplaint')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('complaints.list') }}">@lang('complaints.Complaints')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('complaints.ShowComplaint')</li>
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
                            <div class="card-title">@lang('complaints.ShowComplaint')</div>
                        </div>
                        <div class="card-body">
                            <div class="row gy-4">
                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Client')</label>
                                    <p class="form-text">{{ $complaint['client']['name'] ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Phone')</label>
                                    <p class="form-text">{{ $complaint['client']['phone'] ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Rate')</label>
                                    <p class="form-text">{{ $complaint['rate'] ?? 0 }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.OrderNum')</label>
                                    <p class="form-text">{{ $complaint['order']['order_number'] ?? '' }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Branch')</label>
                                    <p class="form-text">{{ app()->getLocale() == 'en' 
        ? $complaint['order']['branch']['name_en'] 
        : $complaint['order']['branch']['name_ar'] }}</p>
                                </div>

                                <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                    <label class="form-label">@lang('complaints.Status')</label>
                                    <p class="form-text">@if($complaint['status'] == 'solved')
                                            @lang('complaints.Solved')
                                        @elseif($complaint['status'] == 'inprogress')
                                            @lang('complaints.InProgress')
                                        @else
                                            @lang('complaints.Pending')
                                        @endif</p>
                                </div>

                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('complaints.Complaint')</label>
                                        <p class="form-text">{{$complaint['complain']}}</p>
                                </div>

                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                    <label class="form-label">@lang('complaints.Comment')</label>
                                        <p class="form-text">{{$complaint['comment'] ?? ''}}</p>
                                </div>

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
