@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/4.25.0-lts/standard/ckeditor.js"></script>

@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('term.AddReturn')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('returns.list') }}">@lang('term.Returns')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('term.AddReturn')</li>
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
                            <div class="card-title">@lang('term.AddReturn')</div>
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
                            <form method="POST" action="{{ route('return.store') }}" class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <div class="col-xl-12">
                                        <p class="mb-2 text-muted">@lang('term.active')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="active" value="1" class="form-check-input" checked required>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="active" value="0" class="form-check-input" required>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <label for="code" class="form-label">@lang('term.ArabicName')</label>
                                        <input type="text" name="name_ar" id="code" class="form-control" placeholder="@lang('term.ArabicName')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="value" class="form-label">@lang('term.EnglishName')</label>
                                        <input type="text" name="name_en" id="value" class="form-control" placeholder="@lang('term.EnglishName')" required>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="code" class="form-label">@lang('term.ArabicDescription')</label>
                                        <textarea type="text" name="description_ar" id="myeditorinstance_ar" class="form-control" placeholder="@lang('term.ArabicDescription')" required></textarea>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicDesc')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="value" class="form-label">@lang('term.EnglishDescription')</label>
                                            <textarea type="text" name="description_en" id="myeditorinstance_en" class="form-control" placeholder="@lang('term.EnglishDescription')" required></textarea>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishDesc')</div>
                                    </div>
                                </div>

                                    <!-- Submit Button -->
                                    <center>
                                        <div class="col-xl-4 mt-3">
                                            <button type="submit" class="btn btn-primary form-control">@lang('category.save')</button>
                                        </div>
                                    </center>
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

    <script src="https://cdn.tiny.cloud/1/j0eude2g3rvtwte1o6z42lqr0uoeox80bsimaoaka7zp1scf/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea#myeditorinstance_ar', // Replace this CSS selector to match the placeholder element for TinyMCE
            plugins: 'code table lists',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
        });
        tinymce.init({
            selector: 'textarea#myeditorinstance_en', // Replace this CSS selector to match the placeholder element for TinyMCE
            plugins: 'code table lists',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
        });
    </script>
@endsection



