@extends('layouts.master')
@section('title',__('index.award_types'))
@section('action',__('index.lists'))
@section('button')
    @can('create_award_type')
        <a href="{{ route('admin.award-types.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_award_types') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.awardManagement.types.common.breadcrumb')
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.name') }}</th>
                            <th class="text-center">{{ __('index.award_distributed') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['update_award_type','delete_award_type'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                        @forelse($awardTypes as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{ucfirst($value->title)}}</td>
                                <td class="text-center">
                                    <a href="{{route('admin.award-types.show',$value->id)}}"> {{$value->awards_count}}</a>
                                </td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{route('admin.award-types.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->status) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('update_award_type')
                                            <li class="me-2">
                                                <a href="{{route('admin.award-types.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('delete_award_type')
                                            <li>
                                                <a class="delete"
                                                   data-href="{{route('admin.award-types.delete',$value->id)}}" title="{{ __('index.delete') }}">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </td>

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

@section('scripts')
    @include('admin.awardManagement.types.common.scripts')
@endsection






