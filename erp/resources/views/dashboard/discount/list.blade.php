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
        <h4 class="fw-medium mb-0">@lang('discount.Discounts')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.home') }}">
                            @lang('sidebar.Main')
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <a href="javascript:void(0);"
                            onclick="window.location.href='{{ route('discounts.list') }}'">@lang('discount.Discounts')</a>
                    </li>
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
                            <div class="card-title">@lang('discount.Discounts')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create discounts', 'admin'))
                                <button type="button" class="btn btn-primary label-btn"
                                    onclick="window.location.href='{{ route('discount.create') }}'">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('discount.AddDiscount')
                                </button>
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

                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('category.ID')</th>
                                        <th scope="col">@lang('discount.NameAr')</th>
                                        <th scope="col">@lang('discount.NameEn')</th>
                                        <th scope="col">@lang('discount.Type')</th>
                                        <th scope="col">@lang('discount.Value')</th>
                                        <th scope="col">@lang('discount.StartDate')</th>
                                        <th scope="col">@lang('discount.EndDate')</th>
                                        <th scope="col">@lang('discount.IsActive')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($discounts as $discount)
                                        <tr>
                                            <td>{{ $discount->id }}</td>
                                            <td>{{ $discount->name_ar }}</td>
                                            <td>{{ $discount->name_en }}</td>
                                            <td>@lang('discount.' . ucfirst($discount->type))</td>
                                            <td>{{ $discount->value }}</td>
                                            <td>{{ $discount->start_date ? \Carbon\Carbon::parse($discount->start_date)->format('Y-m-d H:i') : '-' }}
                                            </td>
                                            <td>{{ $discount->end_date ? \Carbon\Carbon::parse($discount->end_date)->format('Y-m-d H:i') : '-' }}
                                            </td>

                                            <td>
                                                <span
                                                    class="badge {{ $discount->is_active ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $discount->is_active ? __('discount.Active') : __('discount.Inactive') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view discounts', 'admin'))
                                                    <a href="{{ route('discount.show', $discount->id) }}"
                                                        class="btn btn-info-light btn-wave">@lang('category.show') <i
                                                            class="ri-eye-line"></i></a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update discounts', 'admin'))
                                                    <a href="{{ route('discount.edit', $discount->id) }}"
                                                        class="btn btn-orange-light btn-wave">@lang('category.edit') <i
                                                            class="ri-edit-line"></i></a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete discounts', 'admin'))
                                                    <form class="d-inline" id="delete-form-{{ $discount->id }}"
                                                        action="{{ route('discount.delete', $discount->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item('{{ $discount->id }}')"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('category.delete') <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                @endif


                                                @if ($discount->is_active == 1)
                                                    @if (auth('admin')->user()->hasPermissionTo('view discount_dishes', 'admin'))
                                                        <a href="{{ url('dashboard/discounts/dish/list') }}/{{ $discount->id }}"
                                                            class="btn btn-outline-teal btn-wave">
                                                            @lang('discount.AddDishes') <i class="ri-add-box-line"></i>
                                                        </a>
                                                    @endif
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
    {{--    <!-- JQUERY CDN --> --}}
    {{--    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script> --}}

    {{--    <!-- DATA-TABLES CDN --> --}}
    {{--    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script> --}}
    {{--    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script> --}}
    {{--    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script> --}}
    {{--    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script> --}}
    {{--    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script> --}}
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script> --}}
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script> --}}
    {{--    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script> --}}
    {{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script> --}}

    {{--    <!-- INTERNAL DATADABLES JS --> --}}
    {{--    @vite('resources/assets/js/datatables.js') --}}
@endsection
<script>
    function confirmDelete() {
        return confirm("@lang('validation.DeleteConfirm')");
    }

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
