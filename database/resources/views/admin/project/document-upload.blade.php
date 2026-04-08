@extends('layouts.master')

@section('title', __('index.project_document'))

@section('action', __('index.upload_documents'))

@section('button')
    <div class="float-end">
        <a href="{{ route('admin.projects.show', $projectId) }}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> @lang('index.back')</button>
        </a>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/imageuploadify.min.css') }}">
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.project.common.breadcrumb')

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card ">
                    <div class="card-header">
                        <h5 class="text-muted">@lang('index.upload_project_attachments')</h5>
                    </div>
                    <div class="card-body">
                        <form id="projectDocument" class="forms-sample" action="{{ route('admin.project-attachment.store') }}" enctype="multipart/form-data" method="POST">
                            @csrf
                            <div class="row">
                                <input type="hidden" value="{{ $projectId }}" readonly name="project_id" >
                                <div class="mb-3 col-12">
                                    <div>
                                        <input id="image-uploadify" type="file" name="attachments[]"
                                               accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xls,.txt,.zip" multiple />
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mb-3">
                                <button type="submit" class="btn btn-primary">@lang('index.submit')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/imageuploadify.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $("#image-uploadify").imageuploadify();
        });
    </script>

@endsection
