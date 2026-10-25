@php use App\Models\Asset; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title', __('index.assets'))

@section('action', __('index.lists'))

@section('button')
    @can('asset_assign_list')
        <a class="btn btn-outline-primary" data-open-asset-modal href="{{ route('admin.assets.activity') }}">{{ __('index.asset_activity_history') }}</a>
    @endcan
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
                <h6 class="card-title mb-0">
                    <button type="button" class="assets-filter-toggle d-flex align-items-center justify-content-between w-100 bg-transparent border-0 p-0 text-start text-reset collapsed"
                            data-bs-toggle="collapse" data-bs-target="#assetsFilterPanel" aria-expanded="false" aria-controls="assetsFilterPanel">
                        <span>{{ __('index.assets_filter') }}</span>
                        <i data-feather="chevron-down" aria-hidden="true"></i>
                    </button>
                </h6>
            </div>
            <div class="collapse" id="assetsFilterPanel">
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
        </div>


        <div class="card support-main asset-list-card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.asset_lists') }}</h6>
            </div>
            <div class="card-body">
                @include('admin.assetManagement.assetDetail.common.toolkit')
                <div class="table-responsive assets-table-scroll">
                    @include('admin.assetManagement.assetDetail.common.table')
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
                    $('#assetDetail .assetTitle').text(data.name);
                    $('.name').text(data.name);
                    $('.type').text(data.assetType);
                    $('.asset_code').text(data.asset_code);
                    $('.asset_serial_no').text(data.asset_serial_no);
                    $('#assetDetail .phone_numbers').text(data.phone_numbers || '—');
                    ['icloud', 'asset_password_status', 'screen_time_status', 'set_by_name'].forEach(field => {
                        $('#assetDetail .' + field).text(data[field] || '—');
                    });
                    $('.is_working').text(data.is_working);
                    $('.purchased_date').text(data.purchased_date);
                    $('.is_available').text(data.is_available);
                    $('.note').text(data.note);
                    if (daysUsed > 0) {
                        $('.used_for').text(daysUsed+ ' days').parent().show();
                    } else {
                        $('.used_for').parent().hide();
                    }

                    const gallery = document.querySelector('#assetDetail .asset-images');
                    window.AssetGallery.render(gallery, data.image_urls || (data.image ? [data.image] : []), data.name);

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
