@extends('layouts.master')

@section('title',__('index.trainer'))

@section('action',__('index.lists'))

@section('button')
    @can('create_trainer')
        <a href="{{ route('admin.trainers.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_trainer') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.trainingManagement.trainer.common.breadcrumb')

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.trainer_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.trainer_type') }}</th>
                            <th>{{ __('index.name') }}</th>
                            <th>{{ __('index.email') }}</th>
                            <th>{{ __('index.phone') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['show_trainer','delete_trainer','update_trainer'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($trainerLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                     <td>{{ ucfirst($value->trainer_type) }}</td>
                                     <td>{{ $value->trainer_type == \App\Enum\TrainerTypeEnum::internal->value ? $value->employee?->name : $value->name }}</td>
                                     <td>{{ $value->trainer_type == \App\Enum\TrainerTypeEnum::internal->value ? $value->employee?->email : $value->email }}</td>
                                     <td>{{ $value->trainer_type == \App\Enum\TrainerTypeEnum::internal->value ? $value->employee?->phone : $value->contact_number }}</td>
                                     <td class="text-center">
                                         <label class="switch">
                                             <input class="toggleStatus" href="{{route('admin.trainers.toggle-status',$value->id)}}"
                                                    type="checkbox" {{($value->status) == 1 ?'checked':''}}>
                                             <span class="slider round"></span>
                                         </label>
                                     </td>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_trainer')
                                                <li class="me-2">
                                                    <a href="{{route('admin.trainers.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_trainer')
                                                <li class="me-2">
                                                    <a href="{{route('admin.trainers.show',$value->id)}}" title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_trainer')
                                                <li>
                                                    <a class="delete"
                                                       data-title="{{$value->name}} Award Detail"
                                                       data-href="{{route('admin.trainers.delete',$value->id)}}"
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
            {{$trainerLists->appends($_GET)->links()}}
        </div>
    </section>

@endsection

@section('scripts')
    @include('admin.trainingManagement.trainer.common.scripts', [
        'internal' => \App\Enum\TrainerTypeEnum::internal->value,
        'external' => \App\Enum\TrainerTypeEnum::external->value
    ])
@endsection

