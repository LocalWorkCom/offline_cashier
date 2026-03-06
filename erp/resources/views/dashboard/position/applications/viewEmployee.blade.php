<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form</title>

    {{-- Bootstrap CSS (Optional) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb mb-4">
            <h4 class="fw-medium mb-0">Application Form  @lang('applications.createApplication'): {{ $positions->name_en }}</h4>
            <div class="ms-sm-1 ms-0">
                {{-- <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Application Form</li>
                    </ol>
                </nav> --}}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Application for  @lang('applications.createApplication') {{ $positions->name_en }}</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('application.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="position_id" value="{{ $positions->id }}">

                    <div class="row" id="questionsList">
                        @foreach($questions as $index => $question)
                            <div class="col-xl-12 border p-3 rounded mb-3">
                                <div class="question-item">
                                    <label class="fw-bold mb-2">{{ $question['question'] }}
                                        @if($question['required'])
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    @if($question['type'] == 'text')
                                        <input type="text" name="answers[{{ $index }}][value]"
                                               class="form-control"
                                               @if($question['required']) required @endif>
                                        <input type="hidden" name="answers[{{ $index }}][question]" value="{{ $question['question'] }}">
                                        <input type="hidden" name="answers[{{ $index }}][type]" value="text">

                                    @elseif($question['type'] == 'textarea')
                                        <textarea name="answers[{{ $index }}][value]"
                                                  class="form-control"
                                                  @if($question['required']) required @endif></textarea>
                                        <input type="hidden" name="answers[{{ $index }}][question]" value="{{ $question['question'] }}">
                                        <input type="hidden" name="answers[{{ $index }}][type]" value="textarea">

                                    @elseif(in_array($question['type'], ['radio', 'select']))
                                        <select name="answers[{{ $index }}][value]"
                                                class="form-select"
                                                @if($question['required']) required @endif>
                                            <option value="">Select an option  @lang('applications.createApplication')</option>
                                            @foreach(explode(',', $question['options']) as $option)
                                                <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="answers[{{ $index }}][question]" value="{{ $question['question'] }}">
                                        <input type="hidden" name="answers[{{ $index }}][type]" value="{{ $question['type'] }}">

                                    @elseif($question['type'] == 'checkbox')
                                        @foreach(explode(',', $question['options']) as $optionIndex => $option)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="answers[{{ $index }}][value][{{ $optionIndex }}]"
                                                       value="{{ trim($option) }}"
                                                       id="option_{{ $index }}_{{ $optionIndex }}">
                                                <label class="form-check-label" for="option_{{ $index }}_{{ $optionIndex }}">
                                                    {{ trim($option) }}
                                                </label>
                                            </div>
                                        @endforeach
                                        <input type="hidden" name="answers[{{ $index }}][question]" value="{{ $question['question'] }}">
                                        <input type="hidden" name="answers[{{ $index }}][type]" value="checkbox">

                                    @elseif($question['type'] == 'file')
                                        <input type="file" name="answers[{{ $index }}][value]"
                                               class="form-control"
                                               @if($question['required']) required @endif>
                                        <input type="hidden" name="answers[{{ $index }}][question]" value="{{ $question['question'] }}">
                                        <input type="hidden" name="answers[{{ $index }}][type]" value="file">
                                        <small class="text-muted">Accepted: PDF, DOC, DOCX, JPG, PNG (Max: 5MB)  @lang('applications.createApplication') </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Submit Application  @lang('applications.createApplication')</button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel  @lang('applications.createApplication')</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- JS Validation --}}
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = document.querySelectorAll('[required]');

            requiredFields.forEach(field => {
                if (!field.value) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields.');
            }
        });
    </script>

    {{-- Bootstrap JS (Optional) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
