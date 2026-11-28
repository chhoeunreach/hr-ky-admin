@extends('layouts.master')

@section('title', __('index.telegram_notification_rules'))

@section('button')
    @can('create_telegram_group')
        <form action="{{ route('admin.telegram-rules.sync-default') }}" method="POST" class="d-inline me-1">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('index.sync_default_rules_hint') }}">
                <i class="link-icon" data-feather="refresh-cw"></i> {{ __('index.sync_default_rules') }}
            </button>
        </form>
        <a href="{{ route('admin.telegram-rules.create') }}" data-open-telegram-rule-modal data-telegram-rule-modal-title="{{ __('index.add_telegram_rule') }}">
            <button class="btn btn-sm btn-primary">
                <i class="link-icon" data-feather="plus"></i> {{ __('index.add_telegram_rule') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.telegramRule.common.breadcrumb', ['title' => __('index.telegram_notification_rules')])

        {{-- Nav Tabs --}}
        @include('admin.telegramGroup.common.nav-tabs')

        @php
            $hasRuleFilters = collect($filterParameters ?? [])
                ->except(['search', 'per_page', 'page'])
                ->filter(function ($value) {
                    if (is_array($value)) {
                        return count(array_filter($value)) > 0;
                    }
                    return $value !== null && $value !== '';
                })
                ->isNotEmpty();
        @endphp

        {{-- Directory Filter Panel --}}
        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'telegramRuleFilterCollapse', 'directoryExpanded' => $hasRuleFilters])
            <div id="telegramRuleFilterCollapse" class="collapse{{ $hasRuleFilters ? ' show' : '' }}">
                <form class="forms-sample directory-filter-form" action="{{ route('admin.telegram-rules.index') }}" id="telegramRuleFilterForm" method="get">
                    <input type="hidden" id="search" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-4 col-md-6">
                            <label for="event_key" class="form-label">{{ __('index.event') }}</label>
                            <select class="form-control select2-basic" id="event_key" name="event_key">
                                <option value="">-- {{ __('index.all_events') }} --</option>
                                @foreach($categorizedEvents as $catKey => $catData)
                                    <optgroup label="{{ $catData['label'] }}">
                                        @foreach($catData['events'] as $evtKey => $evtLabel)
                                            <option value="{{ $evtKey }}" {{ ($filterParameters['event_key'] ?? '') === $evtKey ? 'selected' : '' }}>
                                                {{ $evtLabel }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="is_active" class="form-label">{{ __('index.status') }}</label>
                            <select class="form-control select2-basic" id="is_active" name="is_active">
                                <option value="">{{ __('index.all') }} {{ __('index.status') }}</option>
                                <option value="1" {{ (string)($filterParameters['is_active'] ?? '') === '1' ? 'selected' : '' }}>{{ __('index.active') }}</option>
                                <option value="0" {{ (string)($filterParameters['is_active'] ?? '') === '0' ? 'selected' : '' }}>{{ __('index.inactive') }}</option>
                            </select>
                        </div>
                    </div>

                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.telegram-rules.index')])
                </form>
            </div>
        </div>

        {{-- Rules Table Card with Directory Toolbar --}}
        <div id="telegramRuleListSection">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
                @include('admin.section.directory-toolbar', [
                    'directoryTable' => 'telegramRuleTable',
                    'directoryForm' => 'telegramRuleFilterForm',
                    'directorySearch' => 'telegramRuleListSearch',
                    'directoryRecords' => $rules,
                    'directoryTitle' => __('index.telegram_notification_rules'),
                    'directoryIcon' => 'bell',
                    'directoryPermission' => 'create_telegram_group',
                    'directoryAddUrl' => route('admin.telegram-rules.create'),
                    'directoryAddTelegramRuleModal' => true,
                    'directoryAddModalTitle' => __('index.add_telegram_rule'),
                    'directoryAddLabel' => __('index.add_telegram_rule'),
                    'directoryDefaultSize' => '10',
                    'directoryCurrentSize' => (string) ($filterParameters['per_page'] ?? '10'),
                    'directoryPageSizes' => [10, 25, 50, 100, 'all'],
                    'directorySearchKey' => 'search',
                    'directoryLive' => false,
                ])
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="telegramRuleTable" class="table mb-0 align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('index.rule_name') }}</th>
                                <th>{{ __('index.event') }}</th>
                                <th>{{ __('index.branch_scope') }}</th>
                                <th>{{ __('index.target_channels') }}</th>
                                <th class="text-center">{{ __('index.delivery_options') }}</th>
                                <th class="text-center">{{ __('index.status') }}</th>
                                @canany(['edit_telegram_group','delete_telegram_group','test_telegram_group'])
                                    <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                                @endcanany
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($rules as $key => $rule)
                                <tr>
                                    <td>{{ $rules->firstItem() + $key }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $rule->name }}</div>
                                        @if($rule->description)
                                            <div class="small text-muted">{{ Str::limit($rule->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $ruleEventKeys = $rule->event_keys_list;
                                        @endphp
                                        @if(count($ruleEventKeys) > 1)
                                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                                @foreach(array_slice($ruleEventKeys, 0, 2) as $ek)
                                                    <span class="badge bg-primary" style="font-size: 11px;">
                                                        {{ $eventOptions[$ek] ?? $ek }}
                                                    </span>
                                                @endforeach
                                                @if(count($ruleEventKeys) > 2)
                                                    <span class="badge bg-secondary" title="{{ implode(', ', array_map(fn($k) => $eventOptions[$k] ?? $k, array_slice($ruleEventKeys, 2))) }}" style="font-size: 11px;">
                                                        +{{ count($ruleEventKeys) - 2 }} {{ __('index.more') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="small text-muted font-monospace mt-1" style="font-size: 10px;">
                                                <code>{{ implode(', ', array_slice($ruleEventKeys, 0, 2)) }}@if(count($ruleEventKeys) > 2)...@endif</code>
                                            </div>
                                        @else
                                            <span class="badge bg-primary mb-1">
                                                {{ $rule->event_label }}
                                            </span>
                                            <div class="small text-muted font-monospace" style="font-size: 11px;">
                                                <code>{{ $rule->event_key }}</code>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rule->branch_scope === 'all')
                                            <span class="badge bg-info">🌐 {{ __('index.all_branches') }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">🏢 {{ __('index.specific_branches') }}</span>
                                            @if(!empty($rule->branch_ids))
                                                <div class="small text-muted mt-1">{{ count($rule->branch_ids) }} {{ __('index.branches') }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $channelIds = (array) ($rule->channel_ids ?? []);
                                            $validChannels = array_filter(array_map(fn($id) => $channelsById[$id] ?? null, $channelIds));
                                        @endphp
                                        @if(empty($channelIds))
                                            <span class="text-danger small"><i data-feather="alert-triangle"></i> {{ __('index.no_channel') }}</span>
                                        @elseif(empty($validChannels))
                                            <span class="text-danger small"><i data-feather="alert-triangle"></i> {{ __('index.channel_deleted') }}</span>
                                        @else
                                            @foreach(array_slice($validChannels, 0, 2) as $ch)
                                                @php $chUrl = $ch->getTelegramUrl(); @endphp
                                                @if($chUrl)
                                                    <a href="{{ $chUrl }}" target="_blank" rel="noopener noreferrer" class="badge bg-light text-primary border text-decoration-none mb-1 d-inline-flex align-items-center gap-1" title="Open {{ $ch->name }} in Telegram: {{ $chUrl }}">
                                                        <i data-feather="external-link" style="width: 10px; height: 10px;"></i> {{ $ch->name }}
                                                        @if($ch->topic_id)
                                                            <small class="text-muted">#{{ $ch->topic_id }}</small>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="badge bg-light text-dark border mb-1">
                                                        {{ $ch->name }}
                                                        @if($ch->topic_id)
                                                            <small class="text-primary">#{{ $ch->topic_id }}</small>
                                                        @endif
                                                    </span>
                                                @endif
                                            @endforeach
                                            @if(count($validChannels) > 2)
                                                <span class="badge bg-secondary mb-1">+{{ count($validChannels) - 2 }} {{ __('index.more') }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $rule->send_location ? 'bg-success' : 'bg-secondary' }}" title="{{ __('index.send_location_pin') }}">
                                            📍 {{ __('index.map') }}
                                        </span>
                                        <span class="badge {{ $rule->send_selfie ? 'bg-success' : 'bg-secondary' }}" title="{{ __('index.send_photo_selfie') }}">
                                            📷 {{ __('index.photo') }}
                                        </span>
                                        @if($rule->is_silent)
                                            <span class="badge bg-dark" title="{{ __('index.silent_alert') }}">🔕 {{ __('index.silent_alert') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can('edit_telegram_group')
                                            <label class="switch mb-0">
                                                <input class="toggleRuleStatus"
                                                       data-href="{{ route('admin.telegram-rules.toggle-status', $rule->id) }}"
                                                       type="checkbox" {{ $rule->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        @else
                                            <span class="badge {{ $rule->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $rule->is_active ? __('index.active') : __('index.inactive') }}
                                            </span>
                                        @endcan
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center gap-1">
                                            @can('test_telegram_group')
                                                <div>
                                                    <a href="javascript:void(0)"
                                                       data-href="{{ route('admin.telegram-rules.test', $rule->id) }}"
                                                       class="btn btn-xs btn-outline-primary btnTestRule"
                                                       title="{{ __('index.send_test') }}">
                                                        <i class="link-icon" data-feather="send" style="width: 14px; height: 14px;"></i> {{ __('index.send_test') }}
                                                    </a>
                                                    @if($rule->last_test_at)
                                                        <div class="mt-1" style="font-size: 10px;">
                                                             <span class="badge {{ $rule->last_test_status === 'success' ? 'bg-success' : 'bg-danger' }}"
                                                                   title="{{ __('index.tested') }}: {{ $rule->last_test_at }}">
                                                                {{ $rule->last_test_status === 'success' ? __('index.tested') : __('index.failed') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endcan
                                            @can('edit_telegram_group')
                                                <a href="{{ route('admin.telegram-rules.edit', $rule->id) }}"
                                                   class="btn btn-xs btn-outline-info"
                                                   data-open-telegram-rule-modal
                                                   data-telegram-rule-modal-title="{{ __('index.edit') }}: {{ $rule->name }}"
                                                   title="{{ __('index.edit') }}">
                                                    <i class="link-icon" data-feather="edit" style="width: 14px; height: 14px;"></i>
                                                </a>
                                            @endcan
                                            @can('delete_telegram_group')
                                                <a href="javascript:void(0)"
                                                   data-href="{{ route('admin.telegram-rules.delete', $rule->id) }}"
                                                   class="btn btn-xs btn-outline-danger btnDeleteRule"
                                                   title="{{ __('index.delete') }}">
                                                    <i class="link-icon" data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <div class="mb-2"><i data-feather="inbox" style="width: 36px; height: 36px; stroke-width: 1.5;"></i></div>
                                        <div>{{ __('index.no_rules_found') }}</div>
                                        @can('create_telegram_group')
                                            <div class="mt-3">
                                                <form action="{{ route('admin.telegram-rules.sync-default') }}" method="POST" class="d-inline me-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('index.sync_default_rules_hint') }}">
                                                        <i class="link-icon" data-feather="download-cloud"></i> {{ __('index.sync_default_rules') }}
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.telegram-rules.create') }}" class="btn btn-sm btn-primary">
                                                    <i class="link-icon" data-feather="plus"></i> {{ __('index.create_first_rule') }}
                                                </a>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="telegramRulePaginationArea" class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-4">
                <div class="text-muted small">
                    @if(($filterParameters['per_page'] ?? '') === 'all')
                        {{ __('index.showing') }} <strong>{{ __('index.all') }}</strong> ({{ number_format($rules->total()) }} {{ __('index.entries') }})
                    @elseif($rules->total() > 0)
                        {{ __('index.showing') }} {{ number_format($rules->firstItem() ?? 1) }} - {{ number_format($rules->lastItem() ?? $rules->total()) }} {{ __('index.of') }} {{ number_format($rules->total()) }} {{ __('index.entries') }}
                    @else
                        {{ __('index.no_records_found') }}
                    @endif
                </div>
                <div class="dataTables_paginate">
                    {{ $rules->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        @include('admin.telegramRule.common.form-modal')
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            if ($(".select2-basic").length) {
                $(".select2-basic").select2({ width: '100%' });
            }

            const perPageRule = $('#per_page');
            if (perPageRule.length && !perPageRule.hasClass('select2-hidden-accessible')) {
                perPageRule.select2({ minimumResultsForSearch: Infinity });
            }

            $('#telegramRuleFilterCollapse').on('show.bs.collapse', function () {
                $('.directory-filter-toggle[data-bs-target="#telegramRuleFilterCollapse"]').attr('aria-expanded', 'true');
            }).on('hide.bs.collapse', function () {
                $('.directory-filter-toggle[data-bs-target="#telegramRuleFilterCollapse"]').attr('aria-expanded', 'false');
            });

            const showToast = (icon, title) => {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: icon, title: title });
                } else {
                    alert(title);
                }
            };

            document.addEventListener('telegram-rule:refresh', function () {
                window.location.reload();
            });

            // Toggle Rule Status (Zero-reload AJAX)
            $(document).on('change', '.toggleRuleStatus', function (e) {
                e.preventDefault();
                let checkbox = $(this);
                let href = checkbox.data('href');
                let isChecked = checkbox.prop('checked');

                Swal.fire({
                    title: '{{ __('index.confirm_change_rule_status') }}',
                    text: '{{ __('index.are_you_sure_change_status') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('index.yes') }}',
                    cancelButtonText: '{{ __('index.no') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Status change failed');
                            return response.json();
                        })
                        .then(data => {
                            showToast('success', data.message || '{{ __('message.status_changed') }}');
                        })
                        .catch(err => {
                            checkbox.prop('checked', !isChecked);
                            showToast('error', 'Error changing status.');
                        });
                    } else {
                        checkbox.prop('checked', !isChecked);
                    }
                });
            });

            // Delete Rule (Zero-reload AJAX)
            $(document).on('click', '.btnDeleteRule', function (e) {
                e.preventDefault();
                let href = $(this).data('href');
                let row = $(this).closest('tr');

                Swal.fire({
                    title: '{{ __('index.delete') }}',
                    text: '{{ __('index.confirm_delete_telegram_rule') }}',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: '{{ __('index.yes') }}',
                    cancelButtonText: '{{ __('index.no') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            showToast(data.status ? 'success' : 'error', data.message || '{{ __('index.telegram_rule_deleted') }}');
                            if (data.status) {
                                row.fadeOut(300, function () {
                                    row.remove();
                                    const count = document.querySelector('[data-directory-count="telegramRuleTable"]');
                                    if (count) {
                                        const cur = parseInt(count.textContent.replace(/,/g, ''), 10) || 0;
                                        count.textContent = Math.max(0, cur - 1);
                                    }
                                });
                            }
                        })
                        .catch(err => {
                            showToast('error', 'Failed to delete rule.');
                        });
                    }
                });
            });

            // Send Test Message (Zero-reload AJAX)
            $(document).on('click', '.btnTestRule', function (e) {
                e.preventDefault();
                let btn = $(this);
                let href = btn.data('href');

                Swal.fire({
                    title: '{{ __('index.send_test') }}',
                    text: '{{ __('index.confirm_test_telegram_rule') }}',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('index.yes') }}',
                    cancelButtonText: '{{ __('index.no') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.addClass('disabled');
                        fetch(href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            showToast(data.status ? 'success' : 'error', data.message || '{{ __('index.telegram_test_sent') }}');
                            if (data.status) {
                                setTimeout(() => window.location.reload(), 1000);
                            }
                        })
                        .catch(err => {
                            showToast('error', '{{ __('index.telegram_test_failed') }}');
                        })
                        .finally(() => {
                            btn.removeClass('disabled');
                        });
                    }
                });
            });
        });
    </script>
    <script src="{{ asset('assets/js/telegram-rule-action-modal.js') }}?v={{ filemtime(public_path('assets/js/telegram-rule-action-modal.js')) }}"></script>
@endsection
