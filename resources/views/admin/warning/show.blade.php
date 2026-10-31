@extends('layouts.master')
@section('title', __('index.warning'))
@section('action', __('index.show_detail'))

@section('main-content')
    <section class="content">
        <div class="no-print">
            @include('admin.section.flash_message')
            @include('admin.warning.common.breadcrumb')
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.warning.index') }}"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</a>
                @can('update_warning')
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.warning.edit', $warningDetail->id) }}"><i class="link-icon" data-feather="edit"></i> {{ __('index.edit') }}</a>
                @endcan
                <button class="btn btn-primary btn-sm" type="button" onclick="window.print()"><i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}</button>
            </div>
        </div>
        @include('admin.warning.common.print-form')
        <div class="no-print mt-4">
            <h5>{{ __('index.response_section') }}</h5>
            @forelse($warningDetail->warningReply as $response)
                <div class="border-bottom py-3">
                    <strong>{{ $response->employee?->name ?: __('index.not_available') }}</strong>
                    <div class="warning-reply-message">{{ html_entity_decode(strip_tags($response->message ?? ''), ENT_QUOTES, 'UTF-8') }}</div>
                </div>
            @empty
                <p class="text-muted">{{ __('index.no_records_found') }}</p>
            @endforelse
        </div>
    </section>
@endsection

@section('scripts')
    @if(request()->boolean('print'))
        <script>
            window.addEventListener('load', async function () {
                if (document.fonts) await document.fonts.ready;
                window.print();
            });
        </script>
    @endif
@endsection
