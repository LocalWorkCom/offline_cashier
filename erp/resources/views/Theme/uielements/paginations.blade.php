@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                        <h4 class="fw-medium mb-0">Paginations</h4>
                        <div class="ms-sm-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Ui Elements</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Paginations</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP-CONTENT START -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start:: row-1 -->
                            <div class="row">
                                <div class="col-xxl-3 col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Basic Pagination
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination mb-0">
                                                    <li class="page-item disabled"><a class="page-link" href="javascript:void(0);">Previous</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">Next</a></li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Pagination With Icons 
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination mb-0">
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);" aria-label="Previous">
                                                            <span aria-hidden="true"><i class="bi bi-caret-left"></i></span>
                                                        </a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);" aria-label="Next">
                                                            <span aria-hidden="true"><i class="bi bi-caret-right"></i></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Pagination Sizing
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-wrap justify-content-between gap-2">
                                            <nav class="mb-3 mb-sm-0" aria-label="...">
                                                <ul class="pagination pagination-sm mb-0">
                                                    <li class="page-item active" aria-current="page">
                                                        <span class="page-link">1</span>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                                                </ul>
                                            </nav>
                                            <nav aria-label="...">
                                                <ul class="pagination mb-0">
                                                    <li class="page-item active" aria-current="page">
                                                        <span class="page-link">1</span>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                                                </ul>
                                            </nav>
                                            <nav aria-label="...">
                                                <ul class="pagination pagination-lg mb-0">
                                                    <li class="page-item active" aria-current="page">
                                                        <span class="page-link">1</span>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-1 -->

                            <!-- Start:: row-2  -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Center & Right Aligned Pagination
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination justify-content-center">
                                                    <li class="page-item disabled">
                                                        <a class="page-link">Previous</a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination justify-content-end mb-0">
                                                    <li class="page-item disabled">
                                                        <a class="page-link">Previous</a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Active and disabled states
                                            </div>  
                                        </div>
                                        <div class="card-body d-flex flex-wrap">
                                            <nav aria-label="..." class="me-3">
                                                <ul class="pagination">
                                                    <li class="page-item disabled">
                                                        <a class="page-link">Previous</a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item active" aria-current="page">
                                                        <a class="page-link" href="javascript:void(0);">2</a>
                                                    </li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                            <nav aria-label="...">
                                                <ul class="pagination">
                                                    <li class="page-item disabled">
                                                        <span class="page-link">Previous</span>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item active" aria-current="page">
                                                        <span class="page-link">2</span>
                                                    </li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-2  -->

                            <!-- Start:: row-3 -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Pagination Style-1
                                            </div>  
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="Page navigation" class="pagination-style-1">
                                                <ul class="pagination mb-0">
                                                    <li class="page-item disabled">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            <i class="ri-arrow-left-s-line align-middle"></i>
                                                        </a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item active"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            <i class="bi bi-three-dots"></i>
                                                        </a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">21</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            <i class="ri-arrow-right-s-line align-middle"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Pagination Style-2
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="Page navigation" class="pagination-style-2">
                                                <ul class="pagination mb-0 flex-wrap">
                                                    <li class="page-item disabled">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            Prev
                                                        </a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            <i class="bi bi-three-dots"></i>
                                                        </a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">17</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link text-primary" href="javascript:void(0);">
                                                            next
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Pagination Style-3
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="Page navigation" class="pagination-style-3">
                                                <ul class="pagination mb-0 flex-wrap">
                                                    <li class="page-item disabled">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            Prev
                                                        </a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            <i class="bi bi-three-dots"></i>
                                                        </a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">16</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link text-primary" href="javascript:void(0);">
                                                            next
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Pagination Style-4
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <nav aria-label="Page navigation" class="pagination-style-4">
                                                <ul class="pagination mb-0 flex-wrap">
                                                    <li class="page-item disabled">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            Prev
                                                        </a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link" href="javascript:void(0);">
                                                            <i class="bi bi-three-dots"></i>
                                                        </a>
                                                    </li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">16</a></li>
                                                    <li class="page-item"><a class="page-link" href="javascript:void(0);">17</a></li>
                                                    <li class="page-item">
                                                        <a class="page-link text-primary" href="javascript:void(0);">
                                                            next
                                                        </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-3 -->

                        </div>
                    </div>
                    <!-- APP-CONTENT CLOSE -->

@endsection

@section('scripts')
  

@endsection