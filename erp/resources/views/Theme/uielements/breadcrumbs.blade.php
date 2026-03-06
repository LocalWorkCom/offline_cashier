@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                        <h4 class="fw-medium mb-0">Breadcrumbs</h4>
                        <div class="ms-sm-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Ui Elements</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Breadcrumbs</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP-CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!--ROW-START-->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Example
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb">
                                                    <li class="breadcrumb-item active" aria-current="page">Home</li>
                                                </ol>
                                            </nav>

                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb">
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                                                    <li class="breadcrumb-item active" aria-current="page">Library</li>
                                                </ol>
                                            </nav>

                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb mb-0">
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Library</a></li>
                                                    <li class="breadcrumb-item active" aria-current="page">Data</li>
                                                </ol>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Example1
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb breadcrumb-example1">
                                                    <li class="breadcrumb-item active" aria-current="page">Home</li>
                                                </ol>
                                            </nav>

                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb breadcrumb-example1">
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                                                    <li class="breadcrumb-item active" aria-current="page">Library</li>
                                                </ol>
                                            </nav>

                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb breadcrumb-example1 mb-0">
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Library</a></li>
                                                    <li class="breadcrumb-item active" aria-current="page">Data</li>
                                                </ol>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Dividers
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav style="--bs-breadcrumb-divider: '~';" aria-label="breadcrumb">
                                                <ol class="breadcrumb mb-0">
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                                                    <li class="breadcrumb-item active" aria-current="page">Library</li>
                                                </ol>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Embedded SVG icon
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);"
                                                aria-label="breadcrumb">
                                                <ol class="breadcrumb mb-0">
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                                                    <li class="breadcrumb-item active embedded-breadcrumb" aria-current="page">Library</li>
                                                </ol>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Breadcrumb Style-1
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb breadcrumb-style1 mb-0">
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Library</a></li>
                                                    <li class="breadcrumb-item active" aria-current="page">Data</li>
                                                </ol>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Breadcrumb Style-2
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="breadcrumb">
                                                <ol class="breadcrumb breadcrumb-style2 mb-0">
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home-2 me-1 fs-15 d-inline-flex"></i>Home</a></li>
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-apps me-1 fs-15 d-inline-flex"></i>About</a></li>
                                                    <li class="breadcrumb-item active" aria-current="page">Services</li>
                                                </ol>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Background colors
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav style="--bs-breadcrumb-divider: '';" aria-label="breadcrumb">
                                                <ol class="breadcrumb mb-0">
                                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                                                    <li class="breadcrumb-item active" aria-current="page">Library</li>
                                                </ol>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--ROW-END-->

                        </div>
                    </div>
                    <!-- APP-CONTENT CLOSE -->

@endsection

@section('scripts')
  

@endsection