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
        <h4 class="fw-medium mb-0">@lang('leave_type.LeaveType')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item"><a href="#">@lang('leave_type.Leaves')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('leave_type.LeaveType')</li>
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
                                @lang('leave_type.LeaveType')</div>
                            @if (auth('admin')->user()->hasPermissionTo('create leave_types', 'admin'))
                                <button type="button" class="btn btn-primary label-btn" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                    <i class="fe fe-plus label-btn-icon me-2"></i>
                                    @lang('leave_type.AddLeaveType')
                                </button>
                            @endif

                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('leave-type.store') }}" method="POST"
                                            class="needs-validation" novalidate>
                                            @csrf
                                            @if ($errors->any())
                                                @foreach ($errors->all() as $error)
                                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                                        {{ $error }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                            aria-label="Close">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endif
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="exampleModalLabel1">@lang('leave_type.AddLeaveType')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('leave_type.ArabicName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('leave_type.ArabicName')" name="name_ar" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterArabicName')
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="input-placeholder"
                                                            class="form-label">@lang('leave_type.EnglishName')</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="@lang('leave_type.EnglishName')" name="name_en" required>
                                                        <div class="valid-feedback">
                                                            @lang('validation.Correct')
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            @lang('validation.EnterEnglishName')
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">@lang('modal.close')</button>
                                                <button type="submit"
                                                    class="btn btn-outline-primary">@lang('modal.save')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form id="edit-floor-form" action="" method="POST" class="needs-validation"
                                            novalidate>
                                            @csrf
                                            @method('PUT')
                                            @if ($errors->any())
                                                @foreach ($errors->all() as $error)
                                                    <div class="alert alert-solid-danger alert-dismissible fade show">
                                                        {{ $error }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                            aria-label="Close">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endif
                                            <div class="modal-header">
                                                <h6 class="modal-title" id="editModalLabel">@lang('leave_type.EditFloor')</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row gy-4">
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-ar"
                                                            class="form-label">@lang('leave_type.ArabicName')</label>
                                                        <input type="text" id="edit-name-ar" class="form-control"
                                                            name="name_ar" required>
                                                    </div>
                                                    <div class="col-xl-6 col-lg-8 col-md-8 col-sm-12">
                                                        <label for="edit-name-en"
                                                            class="form-label">@lang('leave_type.EnglishName')</label>
                                                        <input type="text" id="edit-name-en" class="form-control"
                                                            name="name_en" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">@lang('modal.close')</button>
                                                <button type="submit"
                                                    class="btn btn-outline-primary">@lang('modal.save')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="showModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="showModalLabel">@lang('leave_type.ShowFloor')</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row gy-4">
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('leave_type.ArabicName')</label>
                                                    <p id="show-name-ar" class="form-control-static"></p>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label class="form-label">@lang('leave_type.EnglishName')</label>
                                                    <p id="show-name-en" class="form-control-static"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">@lang('modal.close')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                            <table id="file-export" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('leave_type.ID')</th>
                                        <th scope="col">@lang('leave_type.ArabicName')</th>
                                        <th scope="col">@lang('leave_type.EnglishName')</th>
                                        <th scope="col">@lang('category.Actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($LeaveType as $k_leave_type => $leave_type)
                                        <tr>
                                            <td>{{ ++$k_leave_type }}</td>
                                            <td>{{ $leave_type->name_ar }}</td>
                                            <td>{{ $leave_type->name_en }}</td>
                                            <td>
                                                @if (auth('admin')->user()->hasPermissionTo('view leave_types', 'admin'))
                                                    <!-- Show Button -->
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-info-light btn-wave show-floor-btn"
                                                        data-id="{{ $leave_type->id }}" data-bs-toggle="modal"
                                                        data-bs-target="#showModal">
                                                        @lang('category.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('update leave_types', 'admin'))
                                                    <!-- Edit Button -->
                                                    <button type="button"
                                                        class="btn btn-orange-light btn-wave edit-floor-btn"
                                                        data-id="{{ $leave_type->id }}" data-bs-toggle="modal"
                                                        data-bs-target="#editModal">
                                                        @lang('category.edit') <i class="ri-edit-line"></i>
                                                    </button>
                                                @endif
                                                @if (auth('admin')->user()->hasPermissionTo('delete leave_types', 'admin'))
                                                    <form class="d-inline" id="delete-form-{{ $leave_type->id }}"
                                                        action="{{ route('leave-type.delete', $leave_type->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="delete_item('{{ $leave_type->id }}')"
                                                            class="btn btn-danger-light btn-wave">
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

    <!-- INTERNAL DATADABLES JS -->
    @vite('resources/assets/js/datatables.js')
    @vite('resources/assets/js/validation.js')
    @vite('resources/assets/js/choices.js')
    @vite('resources/assets/js/modal.js')
@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.edit-floor-btn').on('click', function() {
            var leaveTypeId = this.getAttribute('data-id');
            var get_url = "{{ route('leave-type.show', 'id') }}";
            var edit_url = "{{ route('leave-type.update', 'id') }}";
            get_url = get_url.replace('id', leaveTypeId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    // Populate the modal with the data
                    $('#edit-name-ar').val(data.name_ar);
                    $('#edit-name-en').val(data.name_en);

                    edit_url = edit_url.replace('id', leaveTypeId);

                    $('#edit-floor-form').attr('action', edit_url);

                    // Show the modal
                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });

        $('.show-floor-btn').on('click', function() {
            var leaveTypeId = this.getAttribute('data-id');
            var get_url = "{{ route('leave-type.show', 'id') }}";
            get_url = get_url.replace('id', leaveTypeId);

            // AJAX request to fetch user details
            $.ajax({
                url: get_url,
                type: 'GET',
                success: function(data) {
                    console.log(data);

                    // Populate the modal with the data
                    $('#show-name-ar').text(data.name_ar);
                    $('#show-name-en').text(data.name_en);

                    // Show the modal
                    $('#showModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });
    });

    function confirmDelete() {
        return confirm("@lang('validation.DeleteConfirm')");
    }

    function delete_item(id) {
        Swal.fire({
            title: 'تنبيه',
            text: 'هل انت متاكد من انك تريد ان تحذف هذا الفرع',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم, احذف',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('delete-form-' + id);
                form.submit();
            }
        });
    }
</script>
