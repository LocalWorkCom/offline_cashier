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
        <h4 class="fw-medium mb-0">@lang('client.clients')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('client.clients')</li>
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
                                @lang('client.clients')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create client_details', 'admin'))
                                <a href="{{ route('client.create') }}" type="button" class="btn btn-primary label-btn">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('client.addClient')
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
                                        <th scope="col">@lang('client.ID')</th>
                                        <th scope="col">@lang('client.name')</th>
                                        <th scope="col">@lang('client.email')</th>
                                        <th scope="col">@lang('client.country')</th>
                                        <th scope="col">@lang('client.phone')</th>
                                        <th scope="col">@lang('client.dob')</th>
                                        {{-- <th scope="col">@lang('client.img')</th> --}}
                                        <th scope="col">@lang('client.is_active')</th>
                                        <th scope="col">@lang('client.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->country_id ? $user->country?->name_site : '---' }}
                                            </td>
                                            <td>{{ $user->phone }}</td>
                                            <td>{{ $user->birth_date }}</td>
                                            {{-- <td>
                                                @if ($user->clientDetails && $user->clientDetails->image)
                                                    <img src="{{ asset($user->clientDetails->image) }}" alt="img"
                                                        width="100" height="100">
                                                @else
                                                    ---
                                                @endif
                                            </td> --}}
                                            <td>
                                                @if ($user->is_active == 1)
                                                    @lang('client.yes')
                                                @else
                                                    @lang('client.no')
                                                @endif
                                            </td>

                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view client_details', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="{{ route('client.show', $user->id) }}"
                                                        class="btn btn-info-light btn-wave show-client">
                                                        @lang('client.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('update client_details', 'admin'))
                                                    <!-- Edit Button -->
                                                    <a href="{{ route('client.edit', $user->id) }}"
                                                        class="btn btn-orange-light btn-wave">
                                                        @lang('client.edit') <i class="ri-edit-line"></i>
                                                    </a>
                                                @endif

                                                @if (auth('admin')->user()->hasPermissionTo('delete client_details', 'admin'))
                                                    <!-- Delete Button -->
                                                    <form class="d-inline" id="delete-form-{{ $user->id }}"
                                                        action="{{ route('client.delete', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item({{ $user->id }})"
                                                            class="btn btn-danger-light btn-wave">
                                                            @lang('client.delete') <i class="ri-delete-bin-line"></i>
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
            title: "@lang('client.warning')",
            text: "@lang('client.deleteMsg')",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "@lang('client.yesDelete')",
            cancelButtonText: "@lang('client.cancelDelete')",
            confirmButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('delete-form-' + id);
                form.submit();
            }
        });
    }
</script>
