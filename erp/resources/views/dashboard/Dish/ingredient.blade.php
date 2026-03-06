@extends('layouts.master')

@section('styles')
    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .table th {
            background-color: #f1f1f1;
        }

        .alert-dismissible .btn-close {
            background-color: transparent;
        }

        .group-title {
            margin-top: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('dishes.DishIngredient')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.dishes.index') }}">@lang('dishes.Dishes')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('dishes.DishIngredient')</li>
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
                            <div class="card-title">@lang('dishes.DishIngredient')</div>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                        {{ $error }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endforeach
                            @endif

                            <form method="POST" action="{{ route('dashboard.dishes.ingredient.save') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="dish_id" value="{{ $dishId }}" id="dish_id">

                                <div id="recipe-groups">
                                    @foreach ($groupedData['groups'] as $groupIndex => $group)
                                        <div class="recipe-group">
                                            <div class="group-title">
                                                <label>@lang('dishes.RecipeGroupTitle')</label>
                                                <input type="text" name="groups[{{ $groupIndex }}][title]"
                                                    class="form-control" value="{{ $group['title'] }}" required>
                                            </div>

                                            <table class="table table-bordered mt-3">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('dishes.Recipe')</th>
                                                        <th>@lang('dishes.Actions')</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dish-recipes-table-{{ $groupIndex }}">
                                                    @foreach ($group['recipes'] as $recipeIndex => $recipe)
                                                        <tr>
                                                            <td>
                                                                <input type="text"
                                                                    name="groups[{{ $groupIndex }}][recipes][{{ $recipeIndex }}]"
                                                                    class="form-control" value="{{ $recipe }}"
                                                                    required>
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                            <button type="button" class="btn btn-success btn-sm add-recipe"
                                                data-group-id="{{ $groupIndex }}">@lang('dishes.AddRecipe')</button>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" id="add-recipe-group"
                                    class="btn btn-primary mt-3">@lang('dishes.AddRecipeGroup')</button>

                                <div id="note-groups">
                                    @foreach ($groupedData['notes'] as $noteIndex => $note)
                                        <div class="note-group">
                                            <div class="group-title">
                                                <label>@lang('dishes.NoteGroupTitle')</label>
                                                <input type="text" name="notes[{{ $noteIndex }}][title]"
                                                    class="form-control" value="{{ $note['title'] }}" required>
                                            </div>

                                            <table class="table table-bordered mt-3">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('dishes.Note')</th>
                                                        <th>@lang('dishes.Actions')</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dish-notes-table-{{ $noteIndex }}">
                                                    @foreach ($note['notes'] as $noteSubIndex => $noteText)
                                                        <tr>
                                                            <td>
                                                                <textarea name="notes[{{ $noteIndex }}][notes][{{ $noteSubIndex }}]" class="form-control" required>{{ $noteText }}</textarea>
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm remove-note">@lang('dishes.Remove')</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                            <button type="button" class="btn btn-success btn-sm add-note"
                                                data-group-id="{{ $noteIndex }}">@lang('dishes.AddNote')</button>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" id="add-note-group"
                                    class="btn btn-primary mt-3">@lang('dishes.AddNoteGroup')</button>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary w-100">@lang('dishes.Save')</button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            let recipeGroupIndex = {{ count($groupedData['groups']) }};
            let noteGroupIndex = {{ count($groupedData['notes']) }};

            // Add Recipe
            $(document).on('click', '.add-recipe', function() {
                const groupId = $(this).data('group-id');
                const recipeIndex = $(`#dish-recipes-table-${groupId} tr`).length;
                $(`#dish-recipes-table-${groupId}`).append(`
            <tr>
                <td><input type="text" name="groups[${groupId}][recipes][${recipeIndex}]" class="form-control" required></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
            </tr>
        `);
            });

            // Add Recipe Group
            $('#add-recipe-group').on('click', function() {
                const groupId = recipeGroupIndex++;
                $('#recipe-groups').append(`
            <div class="recipe-group" id="recipe-group-${groupId}">
                <div class="group-title">
                    <label>@lang('dishes.RecipeGroupTitle')</label>
                    <input type="text" name="groups[${groupId}][title]" class="form-control" required>
                </div>
                <table class="table table-bordered mt-3">
                    <thead><tr><th>@lang('dishes.Recipe')</th><th>@lang('dishes.Actions')</th></tr></thead>
                    <tbody id="dish-recipes-table-${groupId}">
                        <tr>
                            <td><input type="text" name="groups[${groupId}][recipes][0]" class="form-control" required></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">@lang('dishes.Remove')</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-success btn-sm add-recipe" data-group-id="${groupId}">@lang('dishes.AddRecipe')</button>
            </div>
        `);
            });

            // Add Note
            $(document).on('click', '.add-note', function() {
                const groupId = $(this).data('group-id');
                const noteIndex = $(`#dish-notes-table-${groupId} tr`).length;
                $(`#dish-notes-table-${groupId}`).append(`
            <tr>
                <td><textarea name="notes[${groupId}][notes][${noteIndex}]" class="form-control" required></textarea></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-note">@lang('dishes.Remove')</button></td>
            </tr>
        `);
            });

            // Add Note Group
            $('#add-note-group').on('click', function() {
                const groupId = noteGroupIndex++;
                $('#note-groups').append(`
            <div class="note-group" id="note-group-${groupId}">
                <div class="group-title">
                    <label>@lang('dishes.NoteGroupTitle')</label>
                    <input type="text" name="notes[${groupId}][title]" class="form-control" required>
                </div>
                <table class="table table-bordered mt-3">
                    <thead><tr><th>@lang('dishes.Note')</th><th>@lang('dishes.Actions')</th></tr></thead>
                    <tbody id="dish-notes-table-${groupId}">
                        <tr>
                            <td><textarea name="notes[${groupId}][notes][0]" class="form-control" required></textarea></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-note">@lang('dishes.Remove')</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-success btn-sm add-note" data-group-id="${groupId}">@lang('dishes.AddNote')</button>
            </div>
        `);
            });

            // Remove Row (for both recipes and notes)
            $(document).on('click', '.remove-row', function() {
                let group = $(this).closest('.recipe-group');
                $(this).closest('tr').remove();

                // If no more rows exist in the group, remove the entire group
                if (group.find('tbody tr').length === 0) {
                    group.remove();
                }
            });

            $(document).on('click', '.remove-note', function() {
                let group = $(this).closest('.note-group');
                $(this).closest('tr').remove();

                // If no more rows exist in the group, remove the entire group
                if (group.find('tbody tr').length === 0) {
                    group.remove();
                }
            });
        });
    </script>
@endsection
