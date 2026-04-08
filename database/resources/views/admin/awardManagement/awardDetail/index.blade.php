@extends('layouts.master')

@section('title',__('index.award'))

@section('action',__('index.lists'))

@section('button')

    <div class="float-end">

        @can('create_award')
            <a href="{{ route('admin.awards.create')}}">
                <button class="btn btn-primary">
                    <i class="link-icon" data-feather="plus"></i>{{ __('index.add_award') }}
                </button>
            </a>
        @endcan
        @can('award_type_list')
            <a href="{{ route('admin.award-types.index')}}">
                <button class="btn btn-primary">
                    <i class="link-icon" data-feather="list"></i>{{ __('index.award_types') }}
                </button>
            </a>
        @endcan
    </div>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.awardManagement.awardDetail.common.breadcrumb')

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.award_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee') }}</th>
                            <th>{{ __('index.award') }}</th>
                            <th>{{ __('index.gift_item') }}</th>
                            <th>{{ __('index.awarded_date') }}</th>
                            @canany(['show_award','delete_award','update_award'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($awardLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{ $value->employee?->name }}</td>
                                    <td>{{ $value->type?->title }}</td>
                                    <td>{{ $value->gift_item }}</td>
                                    <td>
{{--                                        @if($value->award_base == \App\Enum\AwardBaseEnum::yearly->value)--}}
{{--                                            {{ date('Y', strtotime($value->awarded_date)) }}--}}
{{--                                        @elseif($value->award_base == \App\Enum\AwardBaseEnum::monthly->value)--}}
{{--                                            {{ date('F Y', strtotime($value->awarded_date)) }}--}}
{{--                                        @else--}}
                                            {{ \App\Helpers\AppHelper::formatDateForView($value->awarded_date) }}
{{--                                        @endif--}}
                                    </td>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_award')
                                                <li class="me-2">
                                                    <a href="{{route('admin.awards.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_award')
                                                <li class="me-2">
                                                    <a href="{{route('admin.awards.show',$value->id)}}" title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_award')
                                                <li>
                                                    <a class="delete"
                                                       data-title="{{$value->name}} Award Detail"
                                                       data-href="{{route('admin.awards.delete',$value->id)}}"
                                                       title="{{ __('index.delete') }}">
                                                        <i class="link-icon"  data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            @endcan
                                          </ul>
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

        <div class="dataTables_paginate mt-3">
            {{$awardLists->appends($_GET)->links()}}
        </div>
    </section>

@endsection

@section('scripts')
    @include('admin.awardManagement.awardDetail.common.scripts')
@endsection

