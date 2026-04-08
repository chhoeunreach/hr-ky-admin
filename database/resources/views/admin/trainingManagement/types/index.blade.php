@extends('layouts.master')
@section('title',__('index.training_types'))
@section('action',__('index.lists'))
@section('button')
    @can('create_training_type')
        <button class="btn btn-primary" onclick="openModal()">
            <i class="link-icon" data-feather="plus"></i>{{ __('index.add_training_types') }}
        </button>
    @endcan
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.trainingManagement.types.common.breadcrumb')
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.name') }}</th>
                            <th class="text-center">{{ __('index.total_training') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['update_training_type','delete_training_type'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                        @forelse($trainingTypes as $key => $value)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{ucfirst($value->title)}}</td>
                                <td class="text-center">
                                    <a href="{{route('admin.training-types.show',$value->id)}}"> {{$value->trainings_count}}</a>
                                </td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{route('admin.training-types.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->status) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('update_training_type')
                                            <li class="me-2">
                                                <a href="#" title="{{ __('index.edit') }}"
                                                   onclick="openModal('{{ $value->id }}', '{{ $value->branch_id }}','{{ $value->title }}')">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('delete_training_type')
                                            <li>
                                                <a class="delete"
                                                   data-href="{{route('admin.training-types.delete',$value->id)}}" title="{{ __('index.delete') }}">
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

    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title float-start" id="formModalLabel">{{ __('index.add_training_types') }}</h5>
                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="trainingTypeForm" class="forms-sample" method="POST">
                        @csrf
                        <div id="method-field"></div>

                        <div class="row align-items-center">
                            <div class="col-12 mb-4">
                                <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option selected disabled>{{ __('index.select_branch') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ isset(auth()->user()->branch_id) && auth()->user()->branch_id == $branch->id ? 'selected' : '' }}>
                                            {{ ucfirst($branch->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="title" class="form-label">{{ __('index.title') }}<span style="color: red">*</span></label>
                                <input type="text" class="form-control" id="title"
                                       required
                                       name="title"
                                       autocomplete="off"
                                       placeholder="">
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i id="form-icon" class="link-icon" data-feather="plus"></i>
                                    <span id="form-submit-text">{{ __('index.create') }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('admin.trainingManagement.types.common.scripts')
@endsection






