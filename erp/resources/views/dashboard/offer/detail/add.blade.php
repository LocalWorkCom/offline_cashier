@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('offer.AddOfferDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('offers.list') }}">@lang('offer.Offers')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('offer.AddOfferDetails')</li>
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
                            <div class="card-title">
                                @lang('offer.AddOfferDetails')
                            </div>
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
                            <form method="POST" action="{{ route('offerDetail.store') }}" class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <input hidden="" class="form-check-input" type="text" name="offer_id" value="{{$id}}">

                                    <!-- Offer Type -->
{{--                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">--}}
{{--                                        <label class="form-label">@lang('offer.OfferType')</label>--}}
{{--                                        <select class="form-control" name="offer_type" id="offer_type" required>--}}
{{--                                            <option value="dishes" disabled selected >@lang('offer.Dish')</option>--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
                                    <input type="hidden" name="offer_type" value="dishes">

                                    <!-- Type ID -->
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12" id="type_id_section">
                                        <label class="form-label">@lang('offer.TypeId')</label>
                                        <select class="form-control" name="type_id" id="type_id" required>
                                            <!-- Dynamic options will go here -->
                                        </select>
                                    </div>

                                    <!-- Count -->
                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                        <label class="form-label">@lang('offer.Count')</label>
                                        <input type="number" class="form-control" name="count" value="{{ old('count') }}" required>
                                        <div class="invalid-feedback">
                                            @lang('validation.EnterCount')
                                        </div>
                                    </div>

                                    <center>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <input type="submit" class="form-control btn btn-primary" id="input-submit" value="@lang('category.save')">
                                        </div>
                                    </center>
                                </div>
                            </form>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const element = document.querySelector('.choices');
            if (element) {
                new Choices(element);
            }
        });

        {{--$(document).ready(function () {--}}
        {{--    let dishes = @json($dishes);--}}
        {{--    let addons = @json($addons);--}}
        {{--    let products = @json($products);--}}

        {{--    function loadOptions(offerType) {--}}
        {{--        let typeIdSelect = $('#type_id');--}}
        {{--        typeIdSelect.empty();--}}

        {{--        let options = [];--}}
        {{--        if (offerType == 'dishes') {--}}
        {{--            options = dishes;--}}
        {{--        } else if (offerType == 'addons') {--}}
        {{--            options = addons;--}}
        {{--        } else if (offerType == 'products') {--}}
        {{--            options = products;--}}
        {{--        }--}}

        {{--        --}}{{--typeIdSelect.append('<option value="" disabled selected>@lang('offer.SelectType')</option>');--}}

        {{--        options.forEach(function (option) {--}}
        {{--            typeIdSelect.append('<option value="' + option.id + '">' + option.name + '</option>');--}}
        {{--        });--}}
        {{--    }--}}

        {{--    $('#offer_type').change(function () {--}}
        {{--        let offerType = $(this).val();--}}
        {{--        loadOptions(offerType);--}}
        {{--    });--}}

        {{--    $('#offer_type').change();--}}
        {{--});--}}

        $(document).ready(function () {
            let dishes = @json($dishes);
            let addons = @json($addons);
            let products = @json($products);

            function loadOptions(offerType) {
                let typeIdSelect = $('#type_id');
                typeIdSelect.empty();

                let options = [];
                if (offerType === 'dishes') {
                    options = dishes;
                } else if (offerType === 'addons') {
                    options = addons;
                } else if (offerType === 'products') {
                    options = products;
                }

                typeIdSelect.append('<option value="" disabled selected>@lang('offer.SelectType')</option>');

                options.forEach(function (option) {
                    // console.log(option)
                    typeIdSelect.append('<option value="' + option.id + '">' + option.name_site + '</option>');
                });
            }

            // Load options for the default offer type (dishes)
            loadOptions('dishes');
        });

    </script>
@endsection
