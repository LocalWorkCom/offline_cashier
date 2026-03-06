@extends('layouts.master')

@section('styles')

        <!-- FILEPOND CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond/filepond.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/dropzone/dropzone.css')}}">
      
@endsection

@section('content')

                    <!-- PAGE HEADER -->
                    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
                        <h4 class="fw-medium mb-0">File Uploads</h4>
                        <div class="ms-sm-1 ms-0">
                            <nav>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Form Elements</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">File Uploads</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- END PAGE HEADER -->

                    <!-- APP CONTENT -->
                    <div class="main-content app-content">
                        <div class="container-fluid">

                            <!-- Start:: row-1 -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Bootstrap File Input
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="formFile" class="form-label">Default file input example</label>
                                                <input class="form-control" type="file" id="formFile">
                                            </div>
                                            <div class="mb-3">
                                                <label for="formFileMultiple" class="form-label">Multiple files input
                                                    example</label>
                                                <input class="form-control" type="file" id="formFileMultiple" multiple="">
                                            </div>
                                            <div class="mb-3">
                                                <label for="formFileDisabled" class="form-label">Disabled file input
                                                    example</label>
                                                <input class="form-control" type="file" id="formFileDisabled" disabled="">
                                            </div>
                                            <div class="mb-3">
                                                <label for="formFileSm" class="form-label">Small file input example</label>
                                                <input class="form-control form-control-sm" id="formFileSm" type="file">
                                            </div>
                                            <div>
                                                <label for="formFileLg" class="form-label">Large file input example</label>
                                                <input class="form-control form-control-lg" id="formFileLg" type="file">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        Multiple Upload
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <input type="file" class="multiple-filepond" name="filepond" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="6">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="card custom-card">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        Single Upload
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <input type="file" class="single-fileupload" name="filepond" accept="image/png, image/jpeg, image/gif">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-1 -->

                            <!-- Start:: row-2 -->
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card custom-card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                Dropzone
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <form data-single="true" method="post" action="https://httpbin.org/post" class="dropzone"></form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-2 -->

                        </div>
                    </div>
                    <!-- END APP CONTENT -->

@endsection

@section('scripts')

        <!-- FILEPOND JS -->
        <script src="{{asset('build/assets/libs/filepond/filepond.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js')}}"></script>

        <!-- DROPZONE JS -->
        <script src="{{asset('build/assets/libs/dropzone/dropzone-min.js')}}"></script>

        <!-- FILEUPLOAD JS -->
        @vite('resources/assets/js/fileupload.js')
        

@endsection