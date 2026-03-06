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
                            <div class="card-title">@lang('applications.PositionsApplications')</div>
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

                            <form method="POST" action="{{ route('position.applications.store') }}"
                                class="needs-validation" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row gy-4">
                                    <!-- Job Title Section -->
                                    <div class="col-xl-12">
                                        <label>Job Title @lang('applications.Title'): {{ $position->getNameAttribute() }}</label>
                                        <input type="hidden" name="job_title" value="{{ $position->id }}">
                                    </div>
                                </div>

                                <!-- Question Type Selection -->
                                <div class="row mt-4">
                                    <div class="col-xl-4">
                                        <label for="questionType">Question Type @lang('applications.Question'):</label>
                                        <select id="questionType" class="form-select" required>
                                            <option value="text">Text Question @lang('applications.Text')</option>
                                            <option value="textarea">Textarea Question @lang('applications.Textarea')</option>
                                            <option value="radio">Choose @lang('applications.Choose')</option>
                                            <option value="checkbox">multi selected @lang('applications.selected')</option>
                                            <option value="select">Dropdown @lang('applications.Dropdown')</option>
                                            <option value="file">upload @lang('applications.upload')</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-primary form-control"
                                            onclick="addQuestion()">Add Question @lang('applications.AddQuestion')</button>
                                    </div>
                                </div>
                                <div class="row gy-4" id="questionsContainer">
                                    <!-- Job Title Section -->

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
    <script>
        function addQuestion() {
            const container = document.getElementById('questionsContainer');
            const selectedType = document.getElementById('questionType').value;

            let newElement = document.createElement('div');
            newElement.classList.add('mb-3');

            const uniqueId = 'q_' + Math.random().toString(36).substr(2, 9); // unique id for grouping

            if (['radio', 'checkbox', 'select'].includes(selectedType)) {
                // For types with options
                newElement.innerHTML = `
                    <div class="col-xl-12 border p-3 rounded">
                        <button type="button" class="btn btn-danger btn-sm m-r-0" onclick="this.parentElement.remove()">X</button>
                        <label>Question Content @lang('applications.ContentQuestion') (${capitalize(selectedType)}):</label>
                        <input type="text" class="form-control mb-2" name="questions[${uniqueId}][content]" placeholder="Enter your question @lang('applications.EnterQuestion')">

                        <div id="${uniqueId}_options" class="mt-2"></div>

                        <button type="button" class="btn btn-sm btn-success mt-2" onclick="addOption('${uniqueId}', '${selectedType}')">Add Option @lang('applications.AddOption')</button>

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="required_${uniqueId}" name="questions[${uniqueId}][required]">
                            <label class="form-check-label" for="required_${uniqueId}">Is Required?</label>
                        </div>

                        <input type="hidden" name="questions[${uniqueId}][type]" value="${selectedType}">
                    </div>
                `;
            } else {
                // For simple types
                newElement.innerHTML = `
                    <div class="col-xl-12 border p-3 rounded">
                        <button type="button" class="btn btn-danger btn-sm m-r-0" onclick="this.parentElement.remove()">X</button>
                        <label>@lang('applications.ContentQuestion') (${capitalize(selectedType)}):</label>
                        ${selectedType === 'textarea'
                            ? `<textarea class="form-control mb-2" name="questions[${uniqueId}][content]" placeholder="@lang('applications.EnterQuestion')"></textarea>`
                            : `
                                <input type="text" class="form-control mb-2" name="questions[${uniqueId}][content]" placeholder="@lang('applications.EnterQuestion')">`
                        }

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="required_${uniqueId}" name="questions[${uniqueId}][required]">
                            <label class="form-check-label" for="required_${uniqueId}">Is Required?</label>
                        </div>

                        <input type="hidden" name="questions[${uniqueId}][type]" value="${selectedType}">
                    </div>
                `;
            }

            container.appendChild(newElement);
        }

        function addOption(containerId, type) {
            const optionsContainer = document.getElementById(containerId + '_options');

            const optionElement = document.createElement('div');
            optionElement.classList.add('d-flex', 'align-items-center', 'mt-2');

            optionElement.innerHTML = `
                ${type !== 'select' ? `<input type="${type}" disabled>` : ''}
                <input type="text" class="form-control mx-2" placeholder="Option Text" name="questions[${containerId}][options][]">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">X</button>
            `;

            optionsContainer.appendChild(optionElement);
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    </script>

@endsection
