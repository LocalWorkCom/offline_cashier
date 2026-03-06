@extends('layouts.master')

@section('content')
<div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
    <h4 class="fw-medium mb-0">@lang('position.Positions')</h4>
    <div class="ms-sm-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('position.Positions')</li>
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
                        <div class="card-title">Edit Questions for {{ $application->job_title }}</div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('position.applications.update', $application->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="questions-list row" id="questionsList">
                                @foreach ($questions as $index => $question)
                                    <div class="col-xl-12 border p-3 rounded mb-3 question-item" data-original-index="{{ $index }}">
                                        <button type="button" class="btn btn-danger btn-sm m-r-0 remove-question">X</button>
                                        <input type="hidden" name="job_title" value="{{ $application->id }}">

                                        <div class="mb-3">
                                            <label class="form-label">Question Content:</label>
                                            <input type="text" name="questions[{{ $index }}][content]"
                                                value="{{ old('questions.'.$index.'.content', $question['question']) }}"
                                                class="form-control">
                                                <input type="hidden" name="questions[{{ $index }}][is_new]" value="0">

                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Question Type:</label>
                                            <select name="questions[{{ $index }}][type]" class="form-select question-type"
                                                onchange="handleTypeChange(this, {{ $index }})">
                                                <option value="text" {{ $question['type'] == 'text' ? 'selected' : '' }}>Text</option>
                                                <option value="textarea" {{ $question['type'] == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                                <option value="radio" {{ $question['type'] == 'radio' ? 'selected' : '' }}>Choose</option>
                                                <option value="checkbox" {{ $question['type'] == 'checkbox' ? 'selected' : '' }}>Multi Selected</option>
                                                <option value="select" {{ $question['type'] == 'select' ? 'selected' : '' }}>Dropdown</option>
                                                <option value="file" {{ $question['type'] == 'file' ? 'selected' : '' }}>Upload</option>
                                            </select>
                                        </div>

                                        <div class="options-container mb-3" id="options_{{ $index }}">
                                            @if (in_array($question['type'], ['radio', 'checkbox', 'select']))
                                                @foreach (explode(',', $question['options']) as $optIndex => $opt)
                                                    <div class="d-flex align-items-center mt-2 option-row">
                                                        <input type="text" class="form-control mx-2"
                                                            name="questions[{{ $index }}][options][{{ $optIndex }}]"
                                                            value="{{ old('questions.'.$index.'.options.'.$optIndex, $opt) }}"
                                                            placeholder="Option Text">
                                                        <button type="button" class="btn btn-danger btn-sm remove-option">X</button>
                                                    </div>
                                                @endforeach
                                                <button type="button" class="btn btn-success btn-sm mt-2 add-option"
                                                    data-index="{{ $index }}">Add Option</button>
                                            @endif
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1"
                                                id="required_{{ $index }}"
                                                name="questions[{{ $index }}][required]"
                                                {{ old('questions.'.$index.'.required', !empty($question['required'])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="required_{{ $index }}">Is Required?</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="questionType" class="form-label">Add New Question Type:</label>
                                <select id="questionType" class="form-select">
                                    <option value="text">Text Question</option>
                                    <option value="textarea">Textarea Question</option>
                                    <option value="radio">Choose</option>
                                    <option value="checkbox">Multi Selected</option>
                                    <option value="select">Dropdown</option>
                                    <option value="file">Upload</option>
                                </select>
                            </div>

                            <button type="button" class="btn btn-primary" onclick="addNewQuestion()">Add New Question</button>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success">Update Questions</button>
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
<script>
    // Track the next available index for new questions
    let maxExistingIndex = {{ count($questions) > 0 ? max(array_keys($questions)) + 1 : 0 }};
    let nextNewIndex = maxExistingIndex;

    function addNewQuestion() {
        const selectedType = document.getElementById('questionType').value;
        const container = document.getElementById('questionsList');

        const newQuestionHtml = `
            <div class="col-xl-12 border p-3 rounded mb-3 question-item" data-new-question="true">
                <button type="button" class="btn btn-danger btn-sm m-r-0 remove-question">X</button>
                <input type="hidden" name="job_title" value="{{ $application->id }}">
                <input type="hidden" name="questions[${nextNewIndex}][is_new]" value="1">

                <div class="mb-3">
                    <label class="form-label">Question Content:</label>
                    <input type="text" name="questions[${nextNewIndex}][content]"
                        class="form-control" placeholder="Enter your question" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Question Type:</label>
                    <select name="questions[${nextNewIndex}][type]"
                        class="form-select question-type"
                        onchange="handleTypeChange(this, ${nextNewIndex})" required>
                        <option value="text" ${selectedType === 'text' ? 'selected' : ''}>Text</option>
                        <option value="textarea" ${selectedType === 'textarea' ? 'selected' : ''}>Textarea</option>
                        <option value="radio" ${selectedType === 'radio' ? 'selected' : ''}>Choose</option>
                        <option value="checkbox" ${selectedType === 'checkbox' ? 'selected' : ''}>Multi Selected</option>
                        <option value="select" ${selectedType === 'select' ? 'selected' : ''}>Dropdown</option>
                        <option value="file" ${selectedType === 'file' ? 'selected' : ''}>Upload</option>
                    </select>
                </div>

                <div class="options-container mb-3" id="options_${nextNewIndex}">
                    ${['radio', 'checkbox', 'select'].includes(selectedType) ?
                        `<button type="button" class="btn btn-success btn-sm mt-2 add-option" data-index="${nextNewIndex}">Add Option</button>` : ''
                    }
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1"
                        id="required_${nextNewIndex}"
                        name="questions[${nextNewIndex}][required]">
                    <label class="form-check-label" for="required_${nextNewIndex}">Is Required?</label>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', newQuestionHtml);
        nextNewIndex++;
    }

    // Event delegation for dynamic elements
    document.addEventListener('click', function(e) {
        // Handle remove question button
        if (e.target && e.target.classList.contains('remove-question')) {
            e.target.closest('.question-item').remove();
        }

        // Handle add option button
        if (e.target && e.target.classList.contains('add-option')) {
            const index = e.target.getAttribute('data-index');
            addOption(index);
        }

        // Handle remove option button
        if (e.target && e.target.classList.contains('remove-option')) {
            e.target.closest('.option-row').remove();
        }
    });

    function addOption(containerId) {
        const optionsContainer = document.getElementById('options_' + containerId);
        const optionCount = optionsContainer.querySelectorAll('.option-row').length;

        const optionHtml = `
            <div class="d-flex align-items-center mt-2 option-row">
                <input type="text" class="form-control mx-2"
                    name="questions[${containerId}][options][${optionCount}]"
                    placeholder="Option Text">
                <button type="button" class="btn btn-danger btn-sm remove-option">X</button>
            </div>
        `;

        // Insert before the add button
        const addButton = optionsContainer.querySelector('.add-option');
        if (addButton) {
            addButton.insertAdjacentHTML('beforebegin', optionHtml);
        } else {
            optionsContainer.insertAdjacentHTML('beforeend', optionHtml);
        }
    }

    function handleTypeChange(select, index) {
        const selectedType = select.value;
        const optionsDiv = document.getElementById('options_' + index);

        if (['radio', 'checkbox', 'select'].includes(selectedType)) {
            if (!optionsDiv.querySelector('.add-option')) {
                optionsDiv.innerHTML = `
                    <button type="button" class="btn btn-success btn-sm mt-2 add-option"
                        data-index="${index}">Add Option</button>
                `;
            }
        } else {
            optionsDiv.innerHTML = '';
        }
    }
</script>
@endsection
