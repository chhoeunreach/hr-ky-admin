@extends('layouts.master')

@section('title',__('index.asset'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.assets.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{__('index.back')}}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.assetManagement.assetDetail.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="name" class="form-label">{{__('index.name')}} </label>
                        <input type="text" class="form-control"
                               id="name"
                               value="{{$assetDetail->name}}"
                               readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="type" class="form-label">{{__('index.type')}}</label>
                        <input type="text"
                               class="form-control"
                               id="name"
                               value="{{$assetDetail->type->name}}"
                               readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="assetCode" class="form-label">{{__('index.asset_code')}}</label>
                        <input type="text" class="form-control"
                               id="assetCode"
                               value="{{$assetDetail?->asset_code}}"
                              readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="assetCode" class="form-label">{{__('index.asset_serial_number')}}</label>
                        <input type="text" class="form-control"
                               id="assetCode"
                               value="{{$assetDetail?->asset_serial_no}}"
                               readonly >
                    </div>


                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="is_working" class="form-label">{{__('index.is_working')}}</label>
                        <input type="text"
                               class="form-control"
                               value="{{ucfirst($assetDetail->is_working)}}"
                               readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="purchased_date" class="form-label">{{__('index.purchased_date')}}</label>
                        <input type="date"
                               class="form-control"
                               value="{{ ($assetDetail->purchased_date)}}"
                               readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="warranty_available" class="form-label">{{__('index.warranty_available')}}</label>
                        <input type="text"
                               class="form-control"
                               id="warranty_available"
                               value="{{$assetDetail->is_available ? __('index.yes') : __('index.no') }}"
                               readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="warranty_end_date" class="form-label">{{ __('index.warranty_end_date') }}</label>
                        <input type="text"
                               class="form-control"
                               id="warranty_end_date"
                               value="{{($assetDetail->warranty_end_date )}}"
                              readonly >
                    </div>

                    <div class="col-lg-4 col-md-6 mb-4">
                        <label for="is_available" class="form-label">{{__('index.is_available_for_employee')}}? </label>
                        <input type="text"
                               class="form-control"
                               id="is_available"
                               value="{{$assetDetail->is_available ? 'Yes' : 'No'}}"
                               readonly >
                    </div>

                    <div class="col-lg-6 mb-4">
                        <label for="assigned_to" class="form-label">{{__('index.asset_assigned_employee')}} </label>
                        <input type="text"
                               class="form-control"
                               id="assignTo"
                               value="{{ucfirst($assetDetail?->assignedTo?->name)}}"
                               readonly >
                    </div>

                    <div class="col-lg-6 mb-4">
                        <label for="assigned_date" class="form-label">{{__('index.assigned_date')}}</label>
                        <input type="text"
                               class="form-control"
                               id="assigned_date"
                               value="{{ ($assetDetail?->assigned_date)}}"
                               readonly >

                    </div>

                    <div class="col-lg-12 mb-4">
                        <label for="note" class="form-label">{{__('index.description')}}</label>
                        <div class="rounded p-3"  style="background-color: #e9ecef">
                            {!!  $assetDetail->note !!}
                        </div>
                    </div>

                    <div class="col-lg-12 mb-4">
                        <label for="image" class="form-label d-block ">{{__('index.asset_image')}} </label>
                        <img id="image-preview" class="rounded p-4"
                             src="{{  asset(\App\Models\Asset::UPLOAD_PATH.$assetDetail->image)}}"
                             style="object-fit: contain; background-color: #e9ecef"
                        >
                    </div>


                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.assetManagement.assetDetail.common.scripts')
@endsection

