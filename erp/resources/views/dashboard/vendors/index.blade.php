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
        <h4 class="fw-medium mb-0">@lang('vendor.vendors')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('vendor.vendors')</li>
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
                                @lang('vendor.vendors')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create vendors', 'admin'))
                                <a href="{{ route('vendor.create') }}" type="button" class="btn btn-primary label-btn">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('vendor.addVendor')
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('vendor.ID')</th>
                                        <th scope="col">@lang('vendor.name')</th>
                                        <th scope="col">@lang('vendor.contactPerson')</th>
                                        <th scope="col">@lang('vendor.phone')</th>
                                        <th scope="col">@lang('vendor.email')</th>
                                        <th scope="col">@lang('vendor.address')</th>
                                        <th scope="col">@lang('vendor.country')</th>
                                        <th scope="col">@lang('vendor.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vendors as $vendor)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $vendor->name }}</td>
                                            <td>{{ $vendor->contact_person }}</td>
                                            <td>{{ $vendor->phone }}</td>
                                            <td>{{ $vendor->email }}</td>
                                            <td>
                                                {{ $vendor->address_en }}<br>
                                                {{ $vendor->address_ar }}
                                            </td>
                                            <td>{{ $vendor->country->name_ar . ' | ' . $vendor->country->name_en }}</td>

                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view vendors', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="{{ route('vendor.show', $vendor->id) }}"
                                                        class="btn btn-info-light btn-wave show-vendor">
                                                        @lang('vendor.show') <i class="ri-eye-line"></i>
                                                    </a>
                                              @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update vendors', 'admin'))
                                                    <!-- Edit Button -->
                                                    <a href="{{ route('vendor.edit', $vendor->id) }}"
                                                        class="btn btn-orange-light btn-wave">
                                                        @lang('vendor.edit') <i class="ri-edit-line"></i>
                                                    </a>
                                              @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete vendors', 'admin'))
                                                    <!-- Delete Button -->
                                                    <form class="d-inline" id="delete-form-{{ $vendor->id }}"
                                                        action="{{ route('vendor.delete', $vendor->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item({{ $vendor->id }})"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('vendor.delete') <i class="ri-delete-bin-line"></i>
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
            title: "@lang('vendor.warning')",
            text: "@lang('vendor.deleteMsg')",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "@lang('vendor.yesDelete')",
            cancelButtonText: "@lang('vendor.cancelDelete')",
            confirmButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('delete-form-' + id);
                form.submit();
            }
        });
    }
</script>
