@extends('layouts.master')

@section('title',__('index.trainer'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.trainers.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.trainingManagement.trainer.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    @if($trainerDetail->trainer_type == \App\Enum\TrainerTypeEnum::internal->value)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="name" class="form-label">{{ __('index.trainer_type') }} </label>
                            <input type="text" class="form-control"
                                   id="name"
                                   value="{{$trainerDetail->trainer_type}}"
                                   readonly >
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="type" class="form-label">{{ __('index.branch') }}</label>
                            <input type="text"
                                   class="form-control"
                                   id="name"
                                   value="{{$trainerDetail->branch?->name}}"
                                   readonly >
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.department') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail?->department?->dept_name}}"
                                   readonly >
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.name') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail->employee?->name}}"
                                   readonly >
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.email') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail->employee?->email}}"
                                   readonly >
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.phone') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail->employee?->phone}}"
                                   readonly >
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.address') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail->employee?->address}}"
                                   readonly >
                        </div>
                    @else
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="name" class="form-label">{{ __('index.trainer_type') }} </label>
                            <input type="text" class="form-control"
                                   id="name"
                                   value="{{$trainerDetail->trainer_type}}"
                                   readonly >
                        </div>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.name') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail->name}}"
                                   readonly >
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.email') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail->email}}"
                                   readonly >
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.phone') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail->contact_number}}"
                                   readonly >
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.address') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail->address}}"
                                   readonly >
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <label for="awardCode" class="form-label">{{ __('index.expertise') }}</label>
                            <input type="text" class="form-control"
                                   id="awardCode"
                                   value="{{$trainerDetail->expertise}}"
                                   readonly >
                        </div>
                    @endif


                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.trainingManagement.trainer.common.scripts', [
        'internal' => \App\Enum\TrainerTypeEnum::internal->value,
        'external' => \App\Enum\TrainerTypeEnum::external->value
    ])
@endsection

