@extends('layouts.master')

@section('title',__('index.warning'))

@section('action',__('index.show_detail'))

@section('button')
    <div class="float-md-end">
        <a href="{{route('admin.warning.index')}}" >
            <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.warning.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <table class="table table-striped table-responsive">
                        <tbody>
                        <tr>
                            <th class="w-30">{{ __('index.subject') }}</th>
                            <td>
                                {{ $warningDetail->subject }}
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.branch') }}</th>
                            <td>{{ $warningDetail->branch?->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.department') }}</th>
                            <td>
                                <ul class="mb-0 ps-0 list-unstyled">
                                    @forelse($warningDetail->warningDepartment as $detail)
                                        <li>{{ $detail?->department?->dept_name }}</li>
                                    @empty
                                    @endforelse
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.employee_name') }}</th>
                            <td>
                                <ul class="mb-0 ps-0 list-unstyled">
                                    @forelse($warningDetail->warningEmployee as $detail)
                                        <li>{{ $detail?->employee?->name }}</li>
                                    @empty
                                    @endforelse
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.warning_date') }}</th>
                            <td>
                                {{
                                    \App\Helpers\AppHelper::formatDateForView($warningDetail->warning_date) }}
                            </td>
                        </tr>


                        <tr>
                            <th class="w-30">{{ __('index.message') }}</th>
                            <td>
                                {!! $warningDetail->message !!}
                            </td>
                        </tr>


                        <tr>
                            <th class="w-30">{{ __('index.created_by') }}</th>
                            <td>{{ $warningDetail->createdBy->name }}</td>
                        </tr>
                        <tr>
                            <th class="w-30">{{ __('index.updated_by') }}</th>
                            <td>{{ $warningDetail->updatedBy?->name }}</td>
                        </tr>

                        </tbody>
                    </table>

                    <h5 class="mt-4 mb-4"> {{ __('index.response_section') }}</h5>

                        @forelse($warningDetail->warningReply as $response)
                            <textare class="form-control mx-3" readonly>{!! $response->message !!}</textare>
                            <span class="mx-3 mb-4">Response By: {{ $response?->employee?->name }}</span>

                        @empty
                        @endforelse


                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.warning.common.scripts')
@endsection

