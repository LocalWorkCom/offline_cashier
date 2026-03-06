@extends('layouts.master')

@section('content')
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('dishes.DishDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.dishes.index') }}">@lang('dishes.Dishes')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('dishes.DishDetails')</li>
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
                            <div class="card-title">@lang('dishes.DishDetails')</div>
                        </div>
                        <div class="card-body">
                            <!-- Basic Dish Information -->
                            <h5 class="mb-3">@lang('dishes.BasicInfo')</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>@lang('dishes.NameArabic')</th>
                                    <td>{{ $dish->name_ar }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('dishes.NameEnglish')</th>
                                    <td>{{ $dish->name_en }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('dishes.DescriptionArabic')</th>
                                    <td>{{ $dish->description_ar }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('dishes.DescriptionEnglish')</th>
                                    <td>{{ $dish->description_en }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('recipes.time')</th>
                                    <td>{{ $dish->time }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('dishes.time_cancelation')</th>
                                    <td>{{ $dish->time_cancelation }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('dishes.Category')</th>
                                    <td>{{ $dish->dishCategory ? $dish->dishCategory->name_ar . ' | ' . $dish->dishCategory->name_en : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('dishes.Cuisine')</th>
                                    <td>{{ $dish->cuisine ? $dish->cuisine->name_ar . ' | ' . $dish->cuisine->name_en : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('dishes.Price')</th>
                                    <td>{{ $dish->price ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('dishes.Image')</th>
                                    <td>
                                        @if ($dish->image)
                                            <img src="{{ asset($dish->image) }}" alt="Dish Image" width="100">
                                        @else
                                            @lang('dishes.NoImage')
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            @if ($dish->branchMenus)
                                <h5 class="mt-4">@lang('dishes.AllBranches')</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>@lang('dishes.Branch')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dish->branchMenus as $barnch_menu)
                                            <tr>
                                                {{-- <td>{{ $barnch_menu->branches ? $barnch_menu->branches->name_ar . " | " . $barnch_menu->branches->name_en : '-' }}</td> --}}

                                                <td>
                                                    {{ $barnch_menu->branches ? $barnch_menu->branches->name_ar . ' | ' . $barnch_menu->branches->name_en : '-' }}
                                                </td>

                                                {{-- <td>{{$barnch_menu->branches ? $barnch_menu->branches->name_site : __('dishes.NoData')}}</td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <!-- Dish Sizes and Recipes -->
                            @if ($dish->has_sizes)
                                <h5 class="mt-4">@lang('dishes.DishSizes')</h5>
                                @foreach ($dish->sizes as $size)
                                    <div class="mt-3">
                                        <h6>@lang('dishes.Size'): {{ $size->size_name_en }} ({{ $size->size_name_ar }})</h6>
                                        <p>@lang('dishes.Price'): {{ $size->price }}</p>
                                        <p>@lang('dishes.DefaultSize'):
                                            {{ $size->default_size ? __('dishes.Yes') : __('dishes.No') }}</p>
                                        @if ($size->details->isNotEmpty())
                                            <h6>@lang('dishes.RecipesForSize')</h6>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('dishes.Recipe')</th>
                                                        <th>@lang('dishes.Quantity')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($size->details as $detail)
                                                        <tr>
                                                            <td>{{ $detail->recipe?->name_ar . ' | ' . $detail->recipe?->name_en }}
                                                            </td>

                                                            {{-- <td>{{ $detail->recipe ? $detail->recipe->name_site : __('dishes.RecipeNotFound') }}</td> --}}
                                                            <td>{{ $detail->quantity }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif

                                    </div>
                                @endforeach
                            @endif

                            <!-- Dish Recipes Without Sizes -->
                            @if (!$dish->has_sizes && $dish->details->isNotEmpty())
                                <h5 class="mt-4">@lang('dishes.DishRecipes')</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>@lang('dishes.Recipe')</th>
                                            <th>@lang('dishes.Quantity')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dish->details as $detail)
                                            <tr>
                                                {{-- <td>{{ $detail->recipe->name_ar . ' | ' . $detail->recipe->name_en }}</td> --}}

                                                {{-- <td>{{ $detail->recipe->name }}</td> --}}
                                               <td>{{ ($detail->recipe?->name_ar ?? '') . ' | ' . ($detail->recipe?->name_en ?? '') }}</td>
                                                <td>{{ $detail->quantity }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <!-- Dish Addons and Categories -->
                            @if ($dish->has_addon)
                                <h5 class="mt-4">@lang('dishes.DishAddons')</h5>
                                @foreach ($dish->dishAddonsDetails->groupBy('addon_category_id') as $addonCategoryId => $addons)
                                    @php
                                        $addonCategory = $addons->first()->category ?? null;
                                    @endphp
                                    <div class="mt-3">
                                        @if ($addonCategory)
                                            <h6>@lang('dishes.AddonCategory'): {{ $addonCategory->name_en }}</h6>
                                            <p>@lang('dishes.MinAddons'): {{ $addons->first()->min_addons }}</p>
                                            <p>@lang('dishes.MaxAddons'): {{ $addons->first()->max_addons }}</p>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('dishes.Addon')</th>
                                                        <th>@lang('dishes.Quantity')</th>
                                                        <th>@lang('dishes.Price')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($addons as $addon)
                                                        <tr>
                                                            <td>{{ $addon->addons ? $addon->addons->name_site : __('dishes.NoAddon') }}
                                                            </td>
                                                            <td>{{ $addon->quantity }}</td>
                                                            <td>{{ $addon->price }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p>@lang('dishes.NoAddonsForCategory')</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif


                            <!-- Back Button -->
                            <div class="mt-4">
                                <a href="{{ route('dashboard.dishes.index') }}"
                                    class="btn btn-secondary">@lang('dishes.Back')</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
