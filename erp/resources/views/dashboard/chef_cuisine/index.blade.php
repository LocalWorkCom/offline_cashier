@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('cuisines.chefCategory')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    @if (auth('admin')->user()->hasPermissionTo('view chef-cuisine', 'admin'))
                        <li class="breadcrumb-item active" aria-current="page">
                            <a href="javascript:void(0);" onclick="window.location.href='{{ route('chefCuisine.index') }}'">
                                @lang('cuisines.chefCategory')
                            </a>
                        </li>
                    @endif
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title">@lang('cuisines.chefCategory')</div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#assignCuisineModal">
                                @lang('cuisines.Assign Cuisine Categories')
                            </button>
                        </div>

                        <div class="card-body">
                            @if (session('message'))
                                <div class="alert alert-solid-info alert-dismissible fade show">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endif


                            <table id="assignedCuisinesTable" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>@lang('cuisines.ID')</th>
                                        <th>@lang('cuisines.Chef Name')</th>
                                        <th>@lang('cuisines.Cuisine Category')</th>
                                        <th>@lang('cuisines.Dishes')</th>
                                        <th>@lang('employee.code')</th>
                                        <th>@lang('employee.national_id')</th>
                                        <th>@lang('employee.branch')</th>
                                        <th>@lang('cuisines.country')</th>
                                        <th>@lang('employee.phone')</th>
                                        <th>@lang('cuisines.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($assignedCuisines as $key => $assignment)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                @if ($assignment->chef)
                                                    {{ $assignment->chef->first_name }} - {{ $assignment->chef->last_name }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if (
                                                    $assignment->cuisineCategory &&
                                                        $assignment->cuisineCategory->dish_category &&
                                                        $assignment->cuisineCategory->cuisine)
                                                    {{ app()->getLocale() == 'en' ? $assignment->cuisineCategory->dish_category->name_en : $assignment->cuisineCategory->dish_category->name_ar }}
                                                    -
                                                    {{ app()->getLocale() == 'en' ? $assignment->cuisineCategory->cuisine->name_en : $assignment->cuisineCategory->cuisine->name_ar }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if ($assignment->dishes && is_array($assignment->dishes))
                                                    @if (in_array(-1, $assignment->dishes))
                                                        <span class="badge bg-success">@lang('cuisines.All')</span>
                                                    @else
                                                        @foreach ($assignment->dishes as $dishId)
                                                            @php
                                                                $dish = \App\Models\Dish::find($dishId);
                                                                if ($dish) {
                                                                    $dishName =
                                                                        app()->getLocale() == 'en'
                                                                            ? $dish->name_en ?? trans('brand.none')
                                                                            : $dish->name_ar ?? trans('brand.none');
                                                                } else {
                                                                    $dishName = trans('cuisines.deleted_dish');
                                                                }
                                                            @endphp
                                                            <span class="badge bg-primary">{{ $dishName }}</span>
                                                        @endforeach
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">@lang('cuisines.no_dishes')</span>
                                                @endif
                                            </td>
                                            <td>{{ $assignment->chef ? $assignment->chef->employee_code : 'N/A' }}</td>
                                            <td>{{ $assignment->chef ? $assignment->chef->national_id : 'N/A' }}</td>
                                            <td>
                                                @if ($assignment->chef)
                                                    {{ app()->getLocale() == 'en' ? optional($assignment->chef->branch)->name_en : optional($assignment->chef->branch)->name_ar }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $assignment->chef ? $assignment->chef->country_code : 'N/A' }}</td>
                                            <td>{{ $assignment->chef ? $assignment->chef->phone_number : 'N/A' }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('delete chef-cuisine', 'admin'))
                                                    <form action="{{ route('chefCuisine.destroy', $assignment->id) }}"
                                                        method="POST" id="delete-form-{{ $assignment->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item({{ $assignment->id }})"
                                                            class="btn btn-danger"> @lang('cuisines.Unassign')
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Modal -->
                            @if (auth('admin')->user()->hasPermissionTo('create chef-cuisine', 'admin'))
                                <div class="modal fade" id="assignCuisineModal" tabindex="-1"
                                    aria-labelledby="assignCuisineModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="assignCuisineModalLabel">@lang('cuisines.Assign Cuisine Categories')</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="assignCuisineForm">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label for="modal_employee_id"
                                                            class="form-label">@lang('cuisines.Choose Chef')</label>
                                                        <select name="employee_id" id="modal_employee_id"
                                                            class="form-control">
                                                            <option value="">@lang('cuisines.Choose Chef')</option>
                                                            @foreach ($chefs as $chef)
                                                                <option value="{{ $chef->id }}">
                                                                    {{ $chef->first_name }} -
                                                                    {{ $chef->last_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.Choose Chef')
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">@lang('cuisines.Select Cuisine Categories & Dishes')</label>
                                                        <div id="category-dishes-container">
                                                            <!-- Will be populated dynamically -->
                                                        </div>
                                                        <button type="button" id="add-more-categories"
                                                            class="btn btn-sm btn-secondary mt-2">
                                                            @lang('cuisines.Add More Categories')
                                                        </button>
                                                    </div>

                                                    <button type="submit"
                                                        class="btn btn-primary">@lang('cuisines.Assign')</button>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div> <!-- End Card Body -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize form
            initAssignCuisineForm();

            function initAssignCuisineForm() {
                // Add first category row when the modal is opened for the first time
                // or after it's reset.
                if ($('#category-dishes-container').children().length === 0) {
                    addCategoryRow();
                }

                // Add more categories when button clicked
                $('#add-more-categories').off('click').on('click', function() {
                    addCategoryRow();
                });

                function addCategoryRow() {
                    const rowId = Date.now();
                    const rowHtml = `
                <div class="category-dish-row mb-3 border p-3" data-row-id="${rowId}">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">@extends('layouts.master')</label>
                            <select name="categories[${rowId}][category_id]" class="form-control category-select">
                            <option value="">@lang('cuisines.chefCategory')</option> {{-- Changed from chefCategory for clarity --}}
                                @foreach ($cuisineCategories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ app()->getLocale() == 'en' ? $category->dish_category->name_en : $category->dish_category->name_ar }}
                                        -
                                        {{ app()->getLocale() == 'en' ? $category->cuisine->name_en : $category->cuisine->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback category-error">
                            @lang('sidebar.Main') {{-- Specific error for category --}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-sm btn-danger float-end remove-row mt-4"
                                data-row-id="${rowId}">
                                @lang('cuisines.chefCategory')
                            </button>
                        </div>
                    </div>
                    <div class="dishes-container mt-2" id="dishes-${rowId}" style="display: none;">
                        <div class="form-check mb-2">
                            <input class="form-check-input select-all-dishes" type="checkbox"
                                    id="select-all-${rowId}" data-row-id="${rowId}">
                            <label class="form-check-label fw-bold" for="select-all-${rowId}">
                                @lang('cuisines.chefCategory')
                            </label>
                            <input type="hidden" name="categories[${rowId}][dishes][]" value="-1" disabled>
                        </div>
                        <div class="dishes-checkboxes" id="dishes-checkboxes-${rowId}"></div>
                        
                    </div>
                </div>`;

                    $('#category-dishes-container').append(rowHtml);
                }

                // Remove category row
                $(document).on('click', '.remove-row', function() {
                    const rowId = $(this).data('row-id');
                    $(`.category-dish-row[data-row-id="${rowId}"]`).remove();
                    if ($('#category-dishes-container').children().length === 0) {
                        addCategoryRow();
                    }
                });

                // When category is selected, load its dishes
                // When category is selected, load its dishes
                $(document).on('change', '.category-select', function() {
                    const rowId = $(this).closest('.category-dish-row').data('row-id');
                    const cuisineCategoryId = $(this).val();
                    const dishesContainer = $(`#dishes-checkboxes-${rowId}`);
                    const selectAllCheckboxContainer = $(
                        `#dishes-${rowId} .form-check:has(.select-all-dishes)`
                    ); // Get the parent container of "Select All"
                    const selectAllCheckbox = $(`#select-all-${rowId}`);
                    const selectAllHiddenInput = $(`#select-all-${rowId}`).siblings('input[type="hidden"]');


                    // Reset state
                    $(this).removeClass('is-invalid');
                    $(`#dishes-${rowId} .dishes-error`).hide();
                    selectAllCheckbox.prop('checked', false);
                    selectAllHiddenInput.prop('disabled', true);
                    selectAllCheckboxContainer.hide(); // Hide "Select All" by default


                    if (!cuisineCategoryId) {
                        $(`#dishes-${rowId}`).hide();
                        dishesContainer.empty();
                        return;
                    }

                    dishesContainer.html('<p>Loading dishes...</p>');
                    $(`#dishes-${rowId}`).show();

                    $.ajax({
                        url: "{{ route('dishes.by-cuisine-category', '') }}/" + cuisineCategoryId,
                        method: 'GET',
                        success: function(data) {
                            dishesContainer.empty();
                            const currentLocale = "{{ app()->getLocale() }}";

                            if (data.error) {
                                dishesContainer.html(
                                    `<p class="text-danger">${data.error}</p>`);
                                selectAllCheckboxContainer.hide(); // Hide "Select All" on error
                                return;
                            }

                            if (data.length > 0) {
                                // Show "Select All" only if there are dishes
                                selectAllCheckboxContainer.show();
                                data.forEach(dish => {
                                    const dishName = (currentLocale === 'en') ? dish
                                        .name_en : dish.name_ar;
                                    dishesContainer.append(`
                                <div class="form-check">
                                    <input class="form-check-input dish-checkbox" type="checkbox"
                                            name="categories[${rowId}][dishes][]"
                                            value="${dish.id}" id="dish-${rowId}-${dish.id}">
                                    <label class="form-check-label" for="dish-${rowId}-${dish.id}">
                                        ${dishName}
                                    </label>
                                </div>
                            `);
                                });
                            } else {
                                dishesContainer.html(
                                    '<p class="text-muted">@lang('cuisines.Assign Cuisine Categories')</p>');
                                selectAllCheckboxContainer
                                    .hide(); // Hide "Select All" if no dishes
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = '@lang('cuisines.ID')'; // More descriptive error
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            }
                            dishesContainer.html(`<p class="text-danger">${errorMsg}</p>`);
                            selectAllCheckboxContainer
                                .hide(); // Hide "Select All" on AJAX error
                        }
                    });
                });

                // Handle "Select All" checkbox
                $(document).on('change', '.select-all-dishes', function() {
                    const rowId = $(this).data('row-id');
                    const isChecked = $(this).is(':checked');
                    const dishesContainer = $(`#dishes-checkboxes-${rowId}`);
                    const hiddenInput = $(this).siblings('input[type="hidden"]');

                    // Toggle the hidden input that stores the -1 value
                    hiddenInput.prop('disabled', !isChecked);

                    // Toggle all individual dish checkboxes
                    dishesContainer.find('.dish-checkbox').prop('checked', false);
                    dishesContainer.find('.dish-checkbox').prop('disabled', isChecked);
                });

                // Form submission
                $('#assignCuisineForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    const $form = $(this);
                    const $submitButton = $form.find('button[type="submit"]');
                    const $modal = $('#assignCuisineModal');

                    if ($submitButton.prop('disabled')) {
                        return false;
                    }

                    if (!validateForm()) {
                        $submitButton.prop('disabled', false);
                        return false;
                    }

                    $submitButton.prop('disabled', true);

                    // In your form submission handler:
                    const formData = {
                        employee_id: $('#modal_employee_id').val(),
                        categories: []
                    };

                    $('.category-dish-row').each(function() {
                        const rowId = $(this).data('row-id');
                        const cuisineCategoryId = $(this).find('.category-select').val();

                        if (cuisineCategoryId) {
                            const dishes = [];
                            const selectAllChecked = $(this).find('.select-all-dishes').is(
                                ':checked');

                            if (selectAllChecked) {
                                // Don't push -1, just leave dishes array empty to indicate "all"
                                formData.categories.push({
                                    category_id: cuisineCategoryId,
                                    dishes: [] // Empty array means "all dishes"
                                });
                            } else {
                                $(this).find('.dish-checkbox:checked').each(function() {
                                    dishes.push($(this).val());
                                });
                                formData.categories.push({
                                    category_id: cuisineCategoryId,
                                    dishes: dishes
                                });
                            }
                        }
                    });

                    $.ajax({
                        url: "{{ route('chefCuisine.store') }}",
                        type: "POST",
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                $modal.modal('hide');
                                Swal.fire({
                                    title: "{{ __('validation.Success') }}",
                                    text: response.message,
                                    icon: "success",
                                    timer: 1500,
                                    showConfirmButton: true
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire("{{ __('validation.Error') }}", response.message,
                                    "error");
                                $submitButton.prop('disabled', false);
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = "{{ __('validation.Something went wrong.') }}";
                            if (xhr.responseJSON?.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON?.errors) {
                                errorMessage = Object.values(xhr.responseJSON.errors).flat()
                                    .join("\n");
                            }
                            Swal.fire("{{ __('validation.Error') }}", errorMessage, "error");
                            $submitButton.prop('disabled', false);
                        }
                    });
                });

                function validateForm() {
                    let isValid = true;
                    $('#modal_employee_id').removeClass('is-invalid');
                    $('.category-select').removeClass('is-invalid');
                    $('.dishes-error').hide();

                    // Validate chef selection
                    if ($('#modal_employee_id').val() === '') {
                        $('#modal_employee_id').addClass('is-invalid');
                        Swal.fire({
                            title: "{{ __('validation.Error') }}",
                            text: "@lang('cuisines.Chef Name')",
                            icon: "error"
                        });
                        return false;
                    }

                    // Validate at least one cuisine category is selected
                    const selectedCategories = $('.category-select').filter(function() {
                        return $(this).val() !== '';
                    });

                    if (selectedCategories.length === 0) {
                        Swal.fire({
                            title: "{{ __('validation.Error') }}",
                            text: "@lang('cuisines.Cuisine Category')",
                            icon: "error"
                        });
                        return false;
                    }

                    // Validate dishes for each selected category
                    let allCategoriesValid = true;
                    selectedCategories.each(function() {
                        const rowId = $(this).closest('.category-dish-row').data('row-id');
                        const cuisineCategoryId = $(this).val();
                        const dishesContainer = $(`#dishes-${rowId}`);
                        const dishesErrorElement = $(`#dishes-${rowId} .dishes-error`);
                        const selectAllChecked = $(`#select-all-${rowId}`).is(':checked');
                        const anyDishChecked = $(`#dishes-checkboxes-${rowId} .dish-checkbox:checked`)
                            .length > 0;

                        if (!cuisineCategoryId) {
                            $(this).addClass('is-invalid');
                            allCategoriesValid = false;
                        } else {
                            $(this).removeClass('is-invalid');
                            if (!selectAllChecked && !anyDishChecked && dishesContainer.is(':visible')) {
                                dishesErrorElement.show();
                                allCategoriesValid = false;
                                Swal.fire({
                                    title: "{{ __('validation.Error') }}",
                                    text: "@lang('cuisines.Dishes')",
                                    icon: "error"
                                });
                            } else {
                                dishesErrorElement.hide();
                            }
                        }
                    });

                    return allCategoriesValid;
                }
            }

            // Reset modal when closed
            $('#assignCuisineModal').on('hidden.bs.modal', function() {
                $(this).find('form').trigger('reset');
                $('#category-dishes-container').empty();
                initAssignCuisineForm();
            });

            // Get available cuisines when chef changes (This part seems unrelated to the main modal logic,
            // as the categories are already populated in the modal. Keeping it as is, but noting its potential
            // redundancy if modal_cuisine_category_id is not used in the modal.)
            $('#modal_employee_id').change(function() {
                let chefId = $(this).val();
                if (chefId) {
                    // This part might be for another select element not shown in the modal structure provided.
                    // If it's intended to filter the categories in the modal, it would require more complex logic
                    // to update the dynamically added category selects.
                    $.ajax({
                        url: '/get-available-cuisines/' + chefId,
                        method: 'GET',
                        success: function(data) {
                            let categorySelect = $(
                                '#modal_cuisine_category_id'
                            ); // This ID is not in the dynamic rows
                            if (categorySelect.length) { // Check if element exists
                                categorySelect.empty(); // Clear previous options

                                if (data.length > 0) {
                                    data.forEach(category => {
                                        categorySelect.append(new Option(category.name,
                                            category.id));
                                    });
                                } else {
                                    categorySelect.append(new Option(
                                        "{{ __('validation.No available categories') }}",
                                        ""));
                                }
                            }
                        }
                    });
                }
            });
        });

        function delete_item(id) {
            Swal.fire({
                title: "{{ __('validation.Are you sure?') }}",
                text: "{{ __('validation.You won\'t be able to revert this!') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "{{ __('validation.Yes, delete it!') }}",
                cancelButtonText: "{{ __('validation.Cancel') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('chefCuisine.destroy', ':id') }}".replace(':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire("{{ __('validation.Deleted!') }}",
                                    "{{ __('validation.Cuisine unassigned successfully!') }}",
                                    "success");
                                location.reload();
                            } else {
                                Swal.fire("{{ __('validation.Error!') }}", response.message, "error");
                            }
                        },
                        error: function(xhr) {
                            Swal.fire("{{ __('validation.Error!') }}", xhr.responseJSON?.message ||
                                "{{ __('validation.Something went wrong.') }}", "error");
                        }
                    });
                }
            });
        }
    </script>
@endsection
