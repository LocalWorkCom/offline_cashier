@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                        <h4 class="fw-medium mb-0">Images &amp; Figures</h4>
                        <div class="ms-sm-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Ui Elements</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Images &amp; Figures</li>
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
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Responsive image
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-title mb-3">Use <code> .img-fluid </code>class to the img tag to get responsive image.</p>
                                            <div class="text-center">
                                                <img src="{{asset('build/assets/images/media/media-48.jpg')}}" class="img-fluid" alt="...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Image With Radius
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-title mb-3">Use <code>.rounded</code> class along with <code>.img-fluid</code> to get border radius.</p>
                                            <div class="text-center">
                                                <img src="{{asset('build/assets/images/media/media-49.jpg')}}" class="img-fluid rounded" alt="...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Rounded Image
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-title mb-3">Use <code>.rounded-pill</code> class to <code>img</code> tag to get rounded image.</p>
                                            <div class="text-center">
                                                <img src="{{asset('build/assets/images/media/media-50.jpg')}}" class="img-fluid rounded-pill" alt="...">
                                            </div>
                                        </div>
                                </div>
                                </div>
                            </div>
                            <!-- End:: row-1 -->

                            <!-- Start:: row-2 -->
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Image Left Align</div>
                                        </div>
                                        <div class="card-body">
                                            <img class="rounded float-start" src="{{asset('build/assets/images/media/media-53.jpg')}}" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Image Center Align</div>
                                        </div>
                                        <div class="card-body">
                                            <img class="rounded mx-auto d-block" src="{{asset('build/assets/images/media/media-55.jpg')}}" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">Image Right Align</div>
                                        </div>
                                        <div class="card-body">
                                            <img class="rounded float-end" src="{{asset('build/assets/images/media/media-54.jpg')}}" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Figures
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <figure class="figure">
                                                <img class="bd-placeholder-img figure-img img-fluid rounded card-img" src="{{asset('build/assets/images/media/media-56.jpg')}}" alt="...">
                                                <figcaption class="figure-caption">A caption for the above image.
                                                </figcaption>
                                            </figure>
                                            <figure class="figure float-end">
                                                <img class="bd-placeholder-img figure-img img-fluid rounded card-img" src="{{asset('build/assets/images/media/media-57.jpg')}}" alt="...">
                                                <figcaption class="figure-caption text-end">A caption for the above image.
                                                </figcaption>
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Image Thumbnail
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-title mb-3">Use <code> .img-thumbnail </code>to give an image a rounded 1px border.</p>
                                            <div class="text-center">
                                                <img src="{{asset('build/assets/images/media/media-51.jpg')}}" class="img-thumbnail" alt="...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Rounded Thumbnail
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-title mb-3">Use <code> .rounded-pill </code>along with <code> .img-thummbnail </code> to get radius.</p>
                                            <div class="text-center">
                                                <img src="{{asset('build/assets/images/media/media-52.jpg')}}" class="img-thumbnail rounded-pill" alt="...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-2 -->
                        </div>
                    </div>
                    <!-- APP-CONTENT CLOSE -->
            
@endsection

@section('scripts')
  

@endsection