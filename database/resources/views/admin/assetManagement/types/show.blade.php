@extends('layouts.master')

@section('title',__('index.asset_type'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-end">
        <a href="{{route('admin.asset-types.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.assetManagement.types.common.breadcrumb')

        <div class="card support-main">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('index.asset_name')}} </th>
                            <th>{{__('index.purchased_date')}}</th>
                            <th class="text-center">{{__('index.is_working')}}</th>
                            <th class="text-center">{{__('index.is_available')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($assetTypeDetail->assets as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td><a href="{{route('admin.assets.show',$value->id)}}">
                                        {{ucfirst($value->name)}}</a>
                                </td>
                                <td>
                                    {{\App\Helpers\AppHelper::formatDateForView($value->purchased_date)}}
                                </td>
                                <td class="text-center">
                                     <span class="btn btn-sm btn-secondary" >
                                         {{ucfirst($value->is_working)}}
                                     </span>
                                </td>
                                <td class="text-center">
                                     <span class="btn btn-sm btn-{{$value->is_available ? 'success' : 'danger'}}" >
                                         {{ isset($value->is_available) && $value->is_available == 1  ? __('index.yes'):__('index.no')}}
                                     </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection


