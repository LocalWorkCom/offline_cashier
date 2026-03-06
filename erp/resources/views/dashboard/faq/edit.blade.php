@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('term.EditFAQ')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('faqs.list') }}">@lang('term.FAQs')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('term.EditFAQ')</li>
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
                            <div class="card-title">@lang('term.EditFAQ')</div>
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
                            <form method="POST" action="{{ route('faq.update', $faq->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row gy-4">
                                    <div class="col-xl-12">
                                        <p class="mb-2 text-muted">@lang('term.active')</p>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="active" value="1"
                                                   class="form-check-input" {{ $faq->active ? 'checked' : '' }}
                                                   required>
                                            <label class="form-check-label">@lang('category.yes')</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="active" value="0"
                                                   class="form-check-input" {{ !$faq->active ? 'checked' : '' }}
                                                   required>
                                            <label class="form-check-label">@lang('category.no')</label>
                                        </div>
                                    </div>
                                    <!-- Name Fields -->
                                    <div class="col-xl-6">
                                        <label for="name_ar" class="form-label">@lang('term.ArabicName')</label>
                                        <input type="text" name="name_ar" id="name_ar" class="form-control" value="{{ old('name_ar', $faq->name_ar) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterArabicName')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="name_en" class="form-label">@lang('term.EnglishName')</label>
                                        <input type="text" name="name_en" id="name_en" class="form-control" value="{{ old('name_en', $faq->name_en) }}" required>
                                        <div class="invalid-feedback">@lang('validation.EnterEnglishName')</div>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="question_ar" class="form-label">@lang('term.ArabicQuestion')</label>
                                        <textarea name="question_ar" id="myeditorinstance_ar" class="form-control">{{ old('question_ar', $faq->question_ar) }}</textarea>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="question_en" class="form-label">@lang('term.EnglishQuestion')</label>
                                        <textarea name="question_en" id="myeditorinstance_en" class="form-control">{{ old('question_en', $faq->question_en) }}</textarea>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="answer_ar" class="form-label">@lang('term.ArabicAnswer')</label>
                                        <textarea name="answer_ar" id="myeditorinstance_ar" class="form-control">{{ old('answer_ar', $faq->answer_ar) }}</textarea>
                                    </div>

                                    <div class="col-xl-6">
                                        <label for="answer_en" class="form-label">@lang('term.EnglishAnswer')</label>
                                        <textarea name="answer_en" id="myeditorinstance_en" class="form-control">{{ old('answer_en', $faq->answer_en) }}</textarea>
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
