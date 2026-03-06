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
        <h4 class="fw-medium mb-0">@lang('offer.OfferDetails')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('dashboard.home')}}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('offers.list') }}" onclick="window.location.href='{{ route('offers.list') }}'">@lang('offer.Offers')</a></li>
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
                                @lang('offer.OfferDetails')</div>
                                @if (auth('admin')->user()->hasPermissionTo('create offerDetails', 'admin'))

                            <a href="{{ route('offerDetail.create', $id) }}" type="button" class="btn btn-primary label-btn">
                                <i class="fe fe-plus label-btn-icon me-2"></i>
                                @lang('offer.AddOfferDetails')
                            </a>
                            @endif

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
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('category.ID')</th>
                                        <th scope="col">@lang('offer.Type')</th>
                                        <th scope="col">@lang('offer.TypeName')</th>
                                        <th scope="col">@lang('offer.Count')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rowNumber = 1; @endphp
                                    @foreach ($offerDetails as $detail)
                                        <tr>
                                            {{-- @dd( $detail) --}}
{{--                                            <td>{{ $detail->id }}</td>--}}
                                            <td>{{ $rowNumber++  }}</td>
                                            <td>
                                                @if ($detail->offer_type == 'dishes')
                                                    {{ __('offer.Dish') ?? __('category.none') }}  <!-- Fetch name from dishes table -->
                                                @elseif ($detail->offer_type == 'addons')
                                                    {{ __('offer.Addon') ?? __('category.none') }}  <!-- Fetch name from addons table -->
                                                @elseif ($detail->offer_type == 'products')
                                                    {{ __('offer.Product') ?? __('category.none') }}  <!-- Fetch name from products table -->
                                                @else
                                                    {{ __('category.none') }}  <!-- Default message if no type matches -->
                                                @endif
                                            </td>
                                            <td>
                                                @if ($detail->offer_type == 'dishes')
                                                    {{ app()->getLocale() == 'en' ? $detail->dish->name_en :  $detail->dish->name_ar }}  <!-- Fetch name from dishes table -->
                                                @elseif ($detail->offer_type == 'addons')
                                                    {{ app()->getLocale() == 'en' ? $detail->dish->name_en :  $detail->dish->name_ar}}  <!-- Fetch name from addons table -->
                                                @elseif ($detail->offer_type == 'products')
                                                    {{ app()->getLocale() == 'en' ? $detail->dish->name_en :  $detail->dish->name_ar }}  <!-- Fetch name from products table -->
                                                @else
                                                    {{ __('category.none') }}  <!-- Default message if no type matches -->
                                                @endif
                                            </td>
                                            <td>{{ $detail->count }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('update offerDetails', 'admin'))

                                                <!-- Edit Button -->
                                                <a href="{{ route('offerDetail.edit', $detail->id) }}" class="btn btn-orange-light btn-wave">
                                                    @lang('category.edit') <i class="ri-edit-line"></i>
                                                </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete offerDetails', 'admin'))

                                                <!-- Delete Button -->
                                                <form class="d-inline" id="delete-form-{{ $detail->id }}" action="{{ route('offerDetail.delete', $detail->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" onclick="delete_item({{ $detail->id }})" class="btn btn-danger-light btn-wave">
                                                        @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
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

