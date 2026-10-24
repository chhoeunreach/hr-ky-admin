@php use App\Models\Asset; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.assets'))

@section('action', __('index.lists'))

@section('button')
    @can('create_assets')
        <a data-open-asset-modal href="{{ route('admin.assets.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{  __('index.add_asset') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.assetManagement.assetDetail.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{  __('index.assets_filter') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{route('admin.assets.index')}}" method="get">
                <input type="hidden" name="q" value="{{ $filterParameters['q'] }}">
                <input type="hidden" name="per_page" value="{{ $filterParameters['per_page'] }}">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="direction" value="{{ request('direction') }}">

                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option selected disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id']  == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-3 col-md-6 mb-4">
                        <input type="text" placeholder="Asset name" id="asset" name="name"
                               value="{{$filterParameters['name']}}" class="form-control">
                    </div>

                    <div class="col-lg-3  col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="type_id" id="type">
                            <option
                                value="" {{!isset($filterParameters['type']) ? 'selected': ''}} >{{  __('index.all') }} </option>

                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="is_working" id="is_working">
                            <option
                                value="" {{!isset($filterParameters['is_working']) ? 'selected': ''}} > {{  __('index.all') }} </option>
                            @foreach(Asset::IS_WORKING as $value)
                                <option
                                    value="{{$value}}" {{ isset($filterParameters['is_working']) && $filterParameters['is_working'] == $value ? 'selected': '' }}>
                                    {{ucfirst($value)}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="is_available" id="is_available">
                            <option
                                value="" {{!isset($filterParameters['is_available']) ? 'selected': ''}} >{{  __('index.all') }}</option>
                            <option
                                value="1" {{isset($filterParameters['is_available']) && $filterParameters['is_available'] == 1 ? 'selected': ''}} >{{  __('index.yes_available') }}</option>
                            <option
                                value="0" {{isset($filterParameters['is_available']) && $filterParameters['is_available'] == 0 ? 'selected': ''}} >{{  __('index.notavailable') }}</option>
                        </select>
                    </div>


                    <div class="col-lg-3 col-md-6 mb-4">
                        <input type="date" value="{{$filterParameters['purchased_from']}}" name="purchased_from"
                               class="form-control">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <input type="date" value="{{$filterParameters['purchased_to']}}" name="purchased_to"
                               class="form-control">
                    </div>


                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit"
                                    class="btn btn-block btn-success me-2">{{  __('index.filter') }}</button>
                            <a href="{{route('admin.assets.index')}}"
                               class="btn btn-block btn-primary">{{  __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.asset_lists') }}</h6>
            </div>
            <div class="card-body">
                @include('admin.assetManagement.assetDetail.common.toolkit')
                <div class="table-responsive">
                    <table id="assetsTable" class="table">
                        <thead>
                        <tr>
                            <th data-asset-column="number" class="text-center" aria-sort="{{ request('sort') === 'number' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'number', 'label' => '#'])</th>
                            <th data-asset-column="employee" aria-sort="{{ request('sort') === 'employee' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'employee', 'label' => __('index.employee')])</th>
                            <th data-asset-column="name" aria-sort="{{ request('sort') === 'name' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'name', 'label' => __('index.name')])</th>
                            <th data-asset-column="phone_numbers" aria-sort="{{ request('sort') === 'phone_numbers' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'phone_numbers', 'label' => __('index.phone_number')])</th>
                            <th data-asset-column="asset_code" aria-sort="{{ request('sort') === 'asset_code' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'asset_code', 'label' => __('index.asset_code')])</th>
                            <th data-asset-column="asset_serial_no" aria-sort="{{ request('sort') === 'asset_serial_no' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'asset_serial_no', 'label' => __('index.asset_serial_number')])</th>
                            <th data-asset-column="description" aria-sort="{{ request('sort') === 'description' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'description', 'label' => __('index.description')])</th>
                            <th data-asset-column="type" class="text-center" aria-sort="{{ request('sort') === 'type' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'type', 'label' => __('index.type')])</th>
                            <th data-asset-column="is_working" class="text-center" aria-sort="{{ request('sort') === 'is_working' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'is_working', 'label' => __('index.is_working')])</th>
                            <th data-asset-column="is_available" class="text-center" aria-sort="{{ request('sort') === 'is_available' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'is_available', 'label' => __('index.is_available')])</th>
                            <th data-asset-column="receipt" class="text-center">{{ __('index.asset_receipt') }}</th>
                            @can('assign_asset')
                                <th data-asset-column="assigned_to" class="text-center" aria-sort="{{ request('sort') === 'assigned_to' ? (request('direction', 'asc') === 'desc' ? 'descending' : 'ascending') : 'none' }}">@include('admin.assetManagement.assetDetail.common.sort-heading', ['column' => 'assigned_to', 'label' => __('index.assign_to')])</th>
                            @endcan
                            @canany(['show_asset','edit_assets','delete_assets','asset_assign_list'])
                                <th data-asset-column="actions" class="text-center">{{  __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($assetLists as $key => $value)
                            <tr data-asset-row>
                                <td class="text-center">{{ $assetLists instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $assetLists->firstItem() + $loop->index : $loop->iteration }}</td>
                                <td>@include('admin.assetManagement.assetDetail.common.employee-profile', ['employee' => $value->latestAssignment && !$value->latestAssignment->returned_date ? $value->latestAssignment->user : null])</td>
                                <td>{{ucfirst($value->name)}}</td>
                                <td class="asset-phone-number">@include('admin.assetManagement.assetDetail.common.phone-number', ['phoneNumber' => $value->phone_numbers])</td>
                                <td>{{ $value->asset_code ?: __('index.not_available') }}</td>
                                <td>{{ $value->asset_serial_no ?: __('index.not_available') }}</td>
                                <td class="asset-description-cell">@include('admin.assetManagement.assetDetail.common.description', ['note' => $value->note])</td>
                                <td class="text-center">
                                    @if($value->type)
                                        <a data-open-asset-modal href="{{ route('admin.asset-types.show', $value->type_id) }}">{{ ucfirst($value->type->name) }}</a>
                                    @else
                                        {{ __('index.not_available') }}
                                    @endif
                                </td>

                                <td class="text-center">{{ucfirst($value->is_working)}}</td>

                                <td class="text-center">
                                    {{($value->is_available) == 1 ? __('index.yes'): __('index.no')}}
                                </td>
                                <td class="text-center">
                                    @if($value->latestAssignment && !$value->latestAssignment->returned_date)
                                        <a class="btn btn-outline-secondary btn-sm" data-open-asset-modal href="{{ route('admin.assets.receipt', $value->id) }}" title="{{ __('index.asset_print_receipt') }}">
                                            <i data-feather="printer"></i> {{ __('index.asset_receipt') }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @can('assign_asset')
                                    <td class="text-center">

                                        @if($value->latestAssignment && !$value->latestAssignment->returned_date)
                                            <span class="badge bg-secondary">{{ __('index.asset_already_assigned') }}</span>
                                            @can('asset_assign_list')
                                                <a class="d-block small mt-2" data-open-asset-modal href="{{ route('admin.asset-assignment.index', $value->id) }}">{{ __('index.assignment_history_return') }}</a>
                                            @endcan
                                        @elseif($value->is_working === 'yes' && $value->is_available == 1)
                                            <button type="button" class="assignAsset btn btn-sm btn-info"
                                               data-options-url="{{ route('admin.asset-assignment.options', $value->id) }}"
                                               data-id="{{ $value->id }}"
                                               data-branch-id="{{ $value->branch_id }}"
                                               data-href="{{ route('admin.asset-assignment.store', $value->id) }}">
                                                {{ __('index.assign_to_user') }}
                                            </button>
                                        @else
                                            <span class="text-muted small d-block">{{ $value->is_working !== 'yes' ? __('index.asset_not_working_assignment') : __('index.asset_unavailable_assignment') }}</span>
                                            @can('edit_assets')
                                                <a class="d-block small mt-2" data-open-asset-modal href="{{ route('admin.assets.edit', $value->id) }}">{{ __('index.asset_edit_availability') }}</a>
                                            @endcan
                                        @endif

                                    </td>
                                @endcan
                                @canany(['show_asset','edit_assets','delete_assets','asset_assign_list'])
                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('edit_assets')
                                            <li class="me-2">
                                                <a data-open-asset-modal href="{{route('admin.assets.edit',$value->id)}}"
                                                   title="{{  __('index.edit') }}">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('asset_assign_list')
                                            <li class="me-2">
                                                <a data-open-asset-modal href="{{route('admin.asset-assignment.index',$value->id)}}"
                                                   title="{{  __('index.assignment_history_return') }}">
                                                    <i class="link-icon" data-feather="list"></i>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('show_asset')
                                            <li class="me-2">
                                                <a href="javascript:void(0)" title="{{  __('index.asset_detail') }}"
                                                   onclick="showAssetDetails('{{ route('admin.assets.show',$value->id) }}')">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('delete_assets')
                                            <li>
                                                <a class="delete"
                                                   data-title="{{$value->name}} Asset Detail"
                                                   data-href="{{route('admin.assets.delete',$value->id)}}"
                                                   title="{{  __('index.delete') }}">
                                                    <i class="link-icon" data-feather="delete"></i>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13">
                                    <p class="text-center"><b>{{  __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="assets-table-footer">
            <span>{{ __('index.assets_total_results', ['count' => $assetLists instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $assetLists->total() : $assetLists->count()]) }}</span>
            @if($assetLists instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                {{ $assetLists->links() }}
            @endif
        </div>
    </section>

    @include('admin.assetManagement.assetDetail.common.assignment')
    @include('admin.assetManagement.assetDetail.show')
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/assets-toolkit.js') }}?v={{ filemtime(public_path('assets/js/assets-toolkit.js')) }}"></script>


    <script>

        function showAssetDetails(url) {
            $.get(url, function (response) {
                if (response && response.data) {
                    const data = response.data;

                    var daysUsed = data.used_for;
                    $('.assetTitle').html('Asset Detail');
                    $('.name').text(data.name);
                    $('.type').text(data.assetType);
                    $('.asset_code').text(data.asset_code);
                    $('.asset_serial_no').text(data.asset_serial_no);
                    $('.is_working').text(data.is_working);
                    $('.purchased_date').text(data.purchased_date);
                    $('.is_available').text(data.is_available);
                    $('.note').text(data.note);
                    if (daysUsed > 0) {
                        $('.used_for').text(daysUsed+ ' days');
                    } else {
                        $('.used_for').parent().hide();
                    }

                    if (data.image) {
                        $('.image').attr('src', data.image).show();
                    } else {
                        $('.image').parent().hide();
                    }

                    const modal = new bootstrap.Modal(document.getElementById('assetDetail'));
                    modal.show();
                }
            }).fail(function (xhr, status, error) {
                // Handle error
                AppDialog.alert('Unable to load the asset details. Please try again.', 'error');
                console.error('Error:', error);
            });
        }

    </script>
    @include('admin.assetManagement.assetDetail.common.scripts')
@endsection
