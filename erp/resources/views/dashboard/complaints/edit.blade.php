@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('complaints.EditComplaint')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('complaints.list') }}">@lang('complaints.Complaints')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('complaints.EditComplaint')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">@lang('complaints.EditComplaint')</div>
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
                            <form method="POST" action="{{ route('complaint.update', $complaint->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <div class="col-xl-12">
                                        <p class="mb-2 text-muted">@lang('complaints.Status')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="status" value="pending"
                                                   class="form-check-input" {{ $complaint->status == 'pending'? 'checked' : '' }}
                                                   required>
                                            <label class="form-check-label">@lang('complaints.Pending')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="status" value="inprogress"
                                                   class="form-check-input" {{ $complaint->status == 'inprogress' ? 'checked' : '' }}
                                                   required>
                                            <label class="form-check-label">@lang('complaints.InProgress')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="status" value="solved"
                                                   class="form-check-input" {{ $complaint->status == 'solved' ? 'checked' : '' }}
                                                   required>
                                            <label class="form-check-label">@lang('complaints.Solved')</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('complaints.Client')</label>
                                        <p class="form-text">{{ $complaint->client->name ?? '' }}</p>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('complaints.Phone')</label>
                                        <p class="form-text">{{ $complaint->client->phone ?? '' }}</p>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('complaints.OrderNum')</label>
                                        <p class="form-text">{{ $complaint->order->order_number ?? '' }}</p>
                                    </div>

                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                        <label class="form-label">@lang('complaints.Branch')</label>
                                        <p class="form-text">{{ app()->getLocale() == 'en' ? $complaint->order->branch->name_en : $complaint->order->branch->name_ar }}</p>
                                    </div>

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label class="form-label">@lang('complaints.Complaint')</label>
                                        <p class="form-text">{{$complaint->complain}}</p>
                                    </div>

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label class="form-label">@lang('complaints.Comment')</label>
                                        <p class="form-text">{{$complaint->comment ?? ''}}</p>
                                    </div>

                                    <!-- Submit Button -->
                                    <center>
                                        <div class="col-xl-4">
                                            <button type="submit" class="btn btn-primary form-control">@lang('category.save')</button>
                                        </div>
                                    </center>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQUERY CDN -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <!-- SELECT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Custom JS -->
    @vite('resources/assets/js/validation.js')
@endsection
