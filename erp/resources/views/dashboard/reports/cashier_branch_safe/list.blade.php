@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .badge-cash {
            background-color: #28a745;
        }

        .badge-visa {
            background-color: #17a2b8;
        }
    </style>
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('branch_safe.titlecahsierSafe')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('branch_safe.titlecahsierSafe')</li>
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
                        <div class="card-header" style="display: flex; justify-content: space-between;">
                            <div class="card-title">@lang('branch_safe.titlecahsierSafe')</div>
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
                            <div class="table-responsive">
                                <table id="branch-safe-table" class="table table-bordered text-nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>@lang('branch_safe.id')</th>
                                            <th>@lang('branch_safe.branch')</th>
                                            <th>@lang('branch_safe.cashier')</th>
                                            <th>@lang('branch_safe.created_at')</th>
                                            <th>@lang('branch_safe.actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($request['query'] as $branchSafe)
                                            <tr>
                                                <td>{{ $branchSafe->id }}</td>
                                                <td>
                                                    @if ($branchSafe->branch)
                                                        {{ app()->getLocale() == 'en' ? $branchSafe->branch->name_en : $branchSafe->branch->name_ar }}
                                                    @else
                                                        @lang('common.na')
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($branchSafe->employee)
                                                        {{ $branchSafe->employee->first_name }}
                                                        {{ $branchSafe->employee->last_name }}
                                                        <br>
                                                        <small>{{ $branchSafe->employee->email }}</small>
                                                    @else
                                                        @lang('common.na')
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $branchSafe->created_at }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('reports.cashier_branch-safe.show', $branchSafe->id) }}"
                                                        class="btn btn-sm btn-info-light btn-wave"
                                                        title="@lang('branch_safe.view_details')">
                                                        @lang('order.show') <i class="ri-eye-line"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-4 -->
        </div>
    </div>
@endsection

@section('scripts')
    <!-- REQUIRED DATA-TABLES SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    
    <!-- Add html2canvas and jsPDF libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#branch-safe-table').DataTable({
                dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="ri-file-excel-line"></i> Excel',
                        className: 'btn btn-success'
                    },
                    {
                        text: '<i class="ri-file-pdf-line"></i> PDF',
                        className: 'btn btn-danger',
                        action: function (e, dt, node, config) {
                            generatePDF();
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="ri-printer-line"></i> Print',
                        className: 'btn btn-dark'
                    }
                ],
                responsive: true,
                // REMOVED the language configuration to prevent Arabic translation
            });

            // Function to generate PDF
            function generatePDF() {
                // Create a temporary div to hold the table
                var pdfContent = document.createElement('div');
                pdfContent.style.width = '100%';
                
                // Clone the table and its styles
                var tableClone = $('#branch-safe-table').clone();
                tableClone.find('td, th').css('border', '1px solid #ddd');
                tableClone.find('tr').css('border', '1px solid #ddd');
                
                // Add title
                var title = document.createElement('h2');
                title.textContent = '@lang("branch_safe.title")';
                title.style.textAlign = 'center';
                title.style.marginBottom = '20px';
                pdfContent.appendChild(title);
                
                // Add date
                var date = document.createElement('p');
                date.textContent = 'Generated on: ' + new Date().toLocaleDateString();
                date.style.textAlign = 'right';
                date.style.marginBottom = '20px';
                pdfContent.appendChild(date);
                
                // Add the table
                pdfContent.appendChild(tableClone[0]);
                
                // Temporarily add to body
                document.body.appendChild(pdfContent);
                
                // Use html2canvas to capture the content
                html2canvas(pdfContent, {
                    scale: 2, // Higher quality
                    logging: false,
                    useCORS: true
                }).then(function(canvas) {
                    // Remove the temporary element
                    document.body.removeChild(pdfContent);
                    
                    // Create PDF
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');
                    const imgData = canvas.toDataURL('image/png');
                    const imgWidth = 210; // A4 width in mm
                    const pageHeight = 295; // A4 height in mm
                    const imgHeight = canvas.height * imgWidth / canvas.width;
                    
                    let heightLeft = imgHeight;
                    let position = 0;
                    
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                    
                    // Add new pages if content is longer than one page
                    while (heightLeft >= 0) {
                        position = heightLeft - imgHeight;
                        pdf.addPage();
                        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight;
                    }
                    
                    // Save the PDF
                    pdf.save('branch_safe_report.pdf');
                });
            }
        });
    </script>
@endsection
