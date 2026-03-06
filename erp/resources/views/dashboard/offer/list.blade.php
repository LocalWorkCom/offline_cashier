@extends('layouts.master')

@section('styles')
<!-- DATA-TABLES CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endsection

@section('content')
<!-- PAGE HEADER -->
<div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
    <h4 class="fw-medium mb-0">@lang('offer.Offers')</h4>
    <div class="ms-sm-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                <li class="breadcrumb-item active" aria-current="page"><a href=""
                        onclick="window.location.href='{{ route('offers.list') }}'">@lang('offer.Offers')</a></li>
            </ol>
        </nav>
    </div>
</div>
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Start:: row-4 -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header"
                        style="
                        display: flex;
                        justify-content: space-between;">
                        <div class="card-title">
                            @lang('offer.Offers')</div>

                        @if (auth('admin')->user()->hasPermissionTo('create offers', 'admin'))
                        <a href="{{ route('offer.create') }}" type="button" class="btn btn-primary label-btn">
                            <i class="fe fe-plus label-btn-icon me-2"></i>
                            @lang('offer.AddOffer')
                        </a>
                        @endif
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
                        @if (session('message'))
                        <div class="alert alert-solid-info alert-dismissible fade show">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        @endif
                        <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">@lang('category.ID')</th>
                                    <th scope="col">@lang('offer.ImageAr')</th>
                                    <th scope="col">@lang('offer.ImageEn')</th>
                                    <th scope="col">@lang('offer.ArabicName')</th>
                                    <th scope="col">@lang('offer.EnglishName')</th>
                                    <th scope="col">@lang('offer.BranchSelection')</th>
                                    <th scope="col">@lang('offer.DiscountType')</th>
                                    <th scope="col">@lang('offer.Discount')</th>
                                    <th scope="col">@lang('offer.Active')</th>
                                    <th scope="col">@lang('category.Actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rowNumber = 1; @endphp
                                @foreach ($offers as $offer)
                                <tr>
                                    {{-- @dd( $offer) --}}
                                    {{-- <td>{{ $offer->id }}</td> --}}
                                    <td>{{ $rowNumber++ }}</td>
                                    <td>
                                        @if ($offer->image_ar)
                                        <img src="{{ url($offer->image_ar) }}" alt="" width="100" height="100">
                                        @else
                                        ----
                                        @endif
                                    </td>
                                    <td>
                                        @if ($offer->image_en)
                                        <img src="{{ url($offer->image_en) }}" alt="" width="100" height="100">
                                        @else
                                        ----
                                        @endif
                                    </td>
                                    <td>{{ $offer->name_ar }}</td>
                                    <td>{{ $offer->name_en }}</td>
                                    <td>
                                        @if (app()->getLocale() == 'en')
                                        {{ $offer->branch_id == -1 ? 'All branches' : 'Specific' }}
                                        @else
                                        {{ $offer->branch_id == -1 ? 'كل الفروع' : 'محددة' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (app()->getLocale() == 'en')
                                        {{ $offer->discount_type == 'percentage' ? 'percentage' : 'fixed' }}
                                        @else
                                        {{ $offer->discount_type == 'percentage' ? 'نسبة مئوية' : 'نسبة ثابتة' }}
                                        @endif
                                    </td>
                                    <td>{{ $offer->discount_value }}</td>
                                    <td>
                                        <span class="badge {{ $offer->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $offer->is_active ? __('term.Active') : __('term.Inactive') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if (auth('admin')->user()->hasPermissionTo('view offers', 'admin'))

                                        <!-- Show Button -->
                                        <a href="{{ route('offer.show', $offer->id) }}"
                                            class="btn btn-info-light btn-wave show-category">
                                            @lang('category.show') <i class="ri-eye-line"></i>
                                        </a>
                                        @endif


                                        @if (!checkOfferUsed($offer->id))
                                        <!-- Show the Edit Button only if the offer has not been used -->
                                        @if (auth('admin')->user()->hasPermissionTo('update offers', 'admin'))

                                        <a href="{{ route('offer.edit', $offer->id) }}"
                                            class="btn btn-orange-light btn-wave">
                                            @lang('category.edit') <i class="ri-edit-line"></i>
                                        </a>
                                        @endif

                                        @if (auth('admin')->user()->hasPermissionTo('delete offers', 'admin'))

                                        <!-- Delete Button -->
                                        <form class="d-inline" id="delete-form-{{ $offer->id }}"
                                            action="{{ route('offer.delete', $offer->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="delete_item({{ $offer->id }})"
                                                class="btn btn-danger-light btn-wave">
                                                @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @else
                                        <!-- Optionally, display a message or a disabled button -->
                                        <button class="btn btn-orange-light btn-wave" disabled>
                                            @lang('offer.noEdit') @lang('offer.used')
                                        </button>
                                        <button class="btn btn-danger-light btn-wave" disabled>
                                            @lang('offer.noDelete') @lang('offer.used')
                                        </button>
                                        @endif

                                        @if (auth('admin')->user()->hasPermissionTo('view offerDetails', 'admin'))

                                        <a href="{{ route('offerDetails.list', $offer->id) }}"
                                            class="btn btn-outline-teal btn-wave">
                                            @lang('offer.Details') <i class="ri-add-box-line"></i>
                                        </a>
                                        @endif

                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End:: row-4 -->

    </div>
</div>
@endsection

@section('scripts')
<!-- JQUERY CDN -->
<script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>

<!-- DATA-TABLES CDN -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- INTERNAL DATADABLES JS -->
@vite('resources/assets/js/datatables.js')
@endsection

<script>
    function delete_item(id) {
        Swal.fire({
            title: @json(__('validation.Alert')),
            text: @json(__('validation.DeleteConfirm')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: @json(__('validation.Delete')),
            cancelButtonText: @json(__('validation.Cancel')),
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('delete-form-' + id);
                form.submit();
            }
        });
    }
</script>