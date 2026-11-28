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
                                <tr id="telegram-rule-row-{{ $rule->id }}" data-id="{{ $rule->id }}">
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
                                        @if(!empty($rule->department_ids))
                                            @php
                                                $ruleDeptIds = (array) $rule->department_ids;
                                                $ruleDeptNames = isset($departmentsById)
                                                    ? array_filter(array_map(fn($did) => $departmentsById[$did]->dept_name ?? null, $ruleDeptIds))
                                                    : [];
                                            @endphp
                                            <div class="mt-1">
                                                <span class="badge bg-light text-dark border" style="font-size: 10px;" title="{{ implode(', ', $ruleDeptNames) ?: (count($ruleDeptIds) . ' ' . __('index.departments')) }}">
                                                    📁 {{ count($ruleDeptIds) }} {{ __('index.departments') }}
                                                </span>
                                            </div>
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
                                                @php
                                                    $chUrl = $ch->getTelegramUrl();
                                                    $chAppUrl = $ch->getTelegramAppUrl();
                                                @endphp
                                                <button type="button"
                                                        class="badge bg-light text-primary border text-decoration-none mb-1 d-inline-flex align-items-center gap-1 openTelegramChatModalBtn"
                                                        style="cursor: pointer;"
                                                        data-chat-name="{{ $ch->name }}"
                                                        data-chat-id="{{ $ch->chat_id }}"
                                                        data-topic-id="{{ $ch->topic_id ?? '' }}"
                                                        data-invite-link="{{ $ch->invite_link ?? '' }}"
                                                        data-web-url="{{ $chUrl ?? '' }}"
                                                        data-app-url="{{ $chAppUrl ?? '' }}"
                                                        data-test-url="{{ route('admin.telegram-groups.test', $ch->id) }}"
                                                        data-group-id="{{ $ch->id }}"
                                                        data-is-active="{{ $ch->is_active ? '1' : '0' }}"
                                                        onclick="window.openTelegramChatModal && window.openTelegramChatModal(this, event); return false;"
                                                        title="{{ __('index.open_chat') }}: {{ $ch->name }}">
                                                    <i data-feather="message-circle" style="width: 10px; height: 10px;"></i> {{ $ch->name }}
                                                    @if($ch->topic_id)
                                                        <small class="text-muted">#{{ $ch->topic_id }}</small>
                                                    @endif
                                                </button>
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
                                                       onchange="window.toggleTelegramRuleStatus && window.toggleTelegramRuleStatus(this, event);"
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
                                                       onclick="window.sendTelegramRuleTest && window.sendTelegramRuleTest(this, event); return false;"
                                                       title="{{ __('index.send_test') }}">
                                                        <i class="link-icon" data-feather="send" style="width: 14px; height: 14px; pointer-events: none;"></i> {{ __('index.send_test') }}
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
                                                    <i class="link-icon" data-feather="edit" style="width: 14px; height: 14px; pointer-events: none;"></i>
                                                </a>
                                            @endcan
                                            @can('delete_telegram_group')
                                                <a href="javascript:void(0)"
                                                   data-href="{{ route('admin.telegram-rules.delete', $rule->id) }}"
                                                   class="btn btn-xs btn-outline-danger btnDeleteRule"
                                                   onclick="window.deleteTelegramRuleItem && window.deleteTelegramRuleItem(this, event); return false;"
                                                   title="{{ __('index.delete') }}">
                                                    <i class="link-icon" data-feather="trash-2" style="width: 14px; height: 14px; pointer-events: none;"></i>
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
        @include('admin.telegramGroup.common.chat-modal')
    </section>
@endsection

@section('scripts')
    <script>
        (function () {
            const I18N = {
                confirmTitle: @json(__('index.dialog_delete_title') ?: __('index.delete')),
                confirmDeleteRule: @json(__('index.confirm_delete_telegram_rule')),
                btnDelete: @json(__('index.delete_confirm_button') ?: 'លុប'),
                btnDeny: @json(__('index.deny_button') ?: 'បដិសេធ'),
                btnYes: @json(__('index.yes')),
                btnNo: @json(__('index.no')),
                btnOk: @json(__('index.ok')),
                sendTestTitle: @json(__('index.send_test')),
                confirmTestRule: @json(__('index.confirm_test_telegram_rule')),
                statusTitle: @json(__('index.status')),
                confirmStatusChange: @json(__('index.are_you_sure_change_status')),
                statusChanged: @json(__('message.status_changed')),
                ruleDeleted: @json(__('index.telegram_rule_deleted')),
                testSent: @json(__('index.telegram_test_sent')),
                testFailed: @json(__('index.telegram_test_failed')),
                requestFailed: @json(__('index.dialog_request_failed')),
                failedToDelete: @json(__('index.failed_to_delete') ?: 'មិនអាចលុបបានទេ'),
                active: @json(__('index.active')),
                inactive: @json(__('index.inactive')),
                copiedToClipboard: @json(__('index.copied_to_clipboard') ?: 'បានចម្លងរួចរាល់')
            };

            const showToast = function (icon, title) {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: icon || 'success',
                        title: title
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr[icon || 'success'](title);
                } else {
                    alert(title);
                }
            };

            // Multi-tier confirmation dialog
            window.askTelegramRuleConfirm = function (title, message, isDestructive, confirmLabel, cancelLabel) {
                const btnConfirm = confirmLabel || (isDestructive ? I18N.btnDelete : I18N.btnYes);
                const btnCancel = cancelLabel || (isDestructive ? I18N.btnDeny : I18N.btnNo);

                if (window.AppDialog && typeof window.AppDialog.confirm === 'function') {
                    return window.AppDialog.confirm(message, {
                        title: title,
                        icon: isDestructive ? 'warning' : 'question',
                        confirmButtonText: btnConfirm,
                        cancelButtonText: btnCancel
                    });
                }
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                    return Swal.fire({
                        title: title,
                        text: message,
                        icon: isDestructive ? 'warning' : 'info',
                        showCancelButton: true,
                        confirmButtonColor: isDestructive ? '#d33' : '#0b5fff',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: btnConfirm,
                        cancelButtonText: btnCancel,
                        allowOutsideClick: false
                    }).then(res => res.isConfirmed);
                }
                return Promise.resolve(window.confirm(message || title));
            };

            // Toggle Rule Status (Zero-reload AJAX)
            window.toggleTelegramRuleStatus = function (checkboxEl, event) {
                let checkbox = $(checkboxEl);
                let href = checkbox.data('href');
                let isChecked = checkbox.prop('checked');
                if (!href) return;

                window.askTelegramRuleConfirm(I18N.statusTitle, I18N.confirmStatusChange, false).then(confirmed => {
                    if (confirmed) {
                        fetch(href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Status change failed');
                            return response.json();
                        })
                        .then(data => {
                            showToast('success', data.message || I18N.statusChanged);
                        })
                        .catch(err => {
                            checkbox.prop('checked', !isChecked);
                            showToast('error', 'Error changing status.');
                        });
                    } else {
                        checkbox.prop('checked', !isChecked);
                    }
                });
            };

            // Delete Rule (Zero-reload AJAX with row removal)
            window.deleteTelegramRuleItem = function (el, event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                let btn = $(el);
                if (btn.data('is-deleting')) return false;

                let href = btn.data('href') || btn.attr('data-href');
                let row = btn.closest('tr');
                if (!href) return false;

                window.askTelegramRuleConfirm(
                    I18N.confirmTitle,
                    I18N.confirmDeleteRule,
                    true,
                    I18N.btnDelete,
                    I18N.btnDeny
                ).then(confirmed => {
                    if (!confirmed) return;

                    btn.data('is-deleting', true).addClass('disabled').css('pointer-events', 'none');

                    fetch(href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    .then(response => {
                        return response.json().then(data => {
                            if (!response.ok) {
                                throw new Error(data.message || I18N.requestFailed);
                            }
                            return data;
                        }).catch(err => {
                            if (!response.ok) {
                                throw new Error(err.message || ('Server error: ' + response.status));
                            }
                            throw err;
                        });
                    })
                    .then(data => {
                        if (data.status) {
                            showToast('success', data.message || I18N.ruleDeleted);
                            row.css('background-color', '#fee2e2').fadeOut(350, function () {
                                row.remove();
                                const count = document.querySelector('[data-directory-count="telegramRuleTable"]');
                                if (count) {
                                    const cur = parseInt(count.textContent.replace(/,/g, ''), 10) || 0;
                                    count.textContent = Math.max(0, cur - 1);
                                }
                                if ($('#telegramRuleTable tbody tr').length === 0) {
                                    if (typeof window.refreshTelegramRuleBlock === 'function') {
                                        window.refreshTelegramRuleBlock();
                                    }
                                }
                            });
                        } else {
                            btn.data('is-deleting', false).removeClass('disabled').css('pointer-events', '');
                            const reason = data.message || I18N.requestFailed;
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: I18N.failedToDelete,
                                    text: reason,
                                    confirmButtonText: I18N.btnOk
                                });
                            } else {
                                alert(I18N.failedToDelete + ': ' + reason);
                            }
                        }
                    })
                    .catch(err => {
                        btn.data('is-deleting', false).removeClass('disabled').css('pointer-events', '');
                        const errorMsg = err.message || I18N.requestFailed;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: I18N.failedToDelete,
                                text: errorMsg,
                                confirmButtonText: I18N.btnOk
                            });
                        } else {
                            alert(I18N.failedToDelete + ': ' + errorMsg);
                        }
                    });
                });

                return false;
            };

            // Send Test Message (Zero-reload AJAX)
            window.sendTelegramRuleTest = function (el, event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                let btn = $(el);
                if (btn.data('is-testing')) return false;

                let href = btn.data('href') || btn.attr('data-href');
                let row = btn.closest('tr');
                let ruleId = row.data('id');
                if (!href) return false;

                window.askTelegramRuleConfirm(I18N.sendTestTitle, I18N.confirmTestRule, false).then(confirmed => {
                    if (!confirmed) return;

                    btn.data('is-testing', true).addClass('disabled').css('pointer-events', 'none');

                    fetch(href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        showToast(data.status ? 'success' : 'error', data.message || (data.status ? I18N.testSent : I18N.testFailed));
                        if (data.status && typeof window.refreshTelegramRuleBlock === 'function') {
                            window.refreshTelegramRuleBlock(ruleId);
                        }
                    })
                    .catch(err => {
                        showToast('error', I18N.testFailed);
                    })
                    .finally(() => {
                        btn.data('is-testing', false).removeClass('disabled').css('pointer-events', '');
                    });
                });

                return false;
            };

            // In-place partial refresh of the Rules block (No full page reload!)
            window.refreshTelegramRuleBlock = function (highlightId) {
                const listSection = document.getElementById('telegramRuleListSection');
                if (!listSection) {
                    window.location.reload();
                    return;
                }
                listSection.style.transition = 'opacity 0.2s';
                listSection.style.opacity = '0.5';

                fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to refresh list');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newSection = doc.getElementById('telegramRuleListSection');
                    if (newSection) {
                        listSection.innerHTML = newSection.innerHTML;
                        listSection.style.opacity = '1';

                        if (typeof feather !== 'undefined') feather.replace();

                        const perPageRule = $('#per_page');
                        if (perPageRule.length && !perPageRule.hasClass('select2-hidden-accessible')) {
                            perPageRule.select2({ minimumResultsForSearch: Infinity });
                        }

                        if (highlightId) {
                            const updatedRow = document.getElementById('telegram-rule-row-' + highlightId);
                            if (updatedRow) {
                                updatedRow.classList.add('table-success');
                                updatedRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                setTimeout(() => {
                                    updatedRow.classList.remove('table-success');
                                }, 2500);
                            }
                        }
                    } else {
                        window.location.reload();
                    }
                })
                .catch(() => {
                    window.location.reload();
                });
            };

            // Copy helper with fallback
            window.copyTelegramText = function (text, successMsg) {
                if (!text) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function () {
                        showToast('success', successMsg || I18N.copiedToClipboard);
                    }).catch(function () {
                        window.prompt(successMsg || 'Copy:', text);
                    });
                } else {
                    window.prompt(successMsg || 'Copy:', text);
                }
            };

            // Open Telegram Chat Modal
            window.openTelegramChatModal = function (triggerEl, event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                const el = $(triggerEl);
                const chatName = el.attr('data-chat-name') || '';
                const chatId = el.attr('data-chat-id') || '';
                const topicId = el.attr('data-topic-id') || '';
                const inviteLink = el.attr('data-invite-link') || '';
                const webUrl = el.attr('data-web-url') || '';
                const appUrl = el.attr('data-app-url') || '';
                const testUrl = el.attr('data-test-url') || '';
                const groupId = el.attr('data-group-id') || '';
                const isActive = el.attr('data-is-active') === '1';

                $('#telegramChatModalTitle').text(chatName ? chatName : @json(__('index.telegram_channel_or_group')));
                $('#telegramChatModalName').text(chatName || '—');
                $('#telegramChatModalChatId').text(chatId || '—');

                const statusBadge = $('#telegramChatModalStatusBadge');
                if (isActive) {
                    statusBadge.removeClass('bg-danger').addClass('bg-success').text(I18N.active);
                } else {
                    statusBadge.removeClass('bg-success').addClass('bg-danger').text(I18N.inactive);
                }

                const topicBadge = $('#telegramChatModalTopicBadge');
                if (topicId) {
                    topicBadge.text('Topic #' + topicId).removeClass('d-none');
                } else {
                    topicBadge.addClass('d-none');
                }

                const inviteWrap = $('#telegramChatModalInviteWrap');
                const inviteLinkEl = $('#telegramChatModalInviteLink');
                if (inviteLink) {
                    inviteLinkEl.text(inviteLink).attr('href', inviteLink);
                    inviteWrap.removeClass('d-none');
                } else {
                    inviteWrap.addClass('d-none');
                }

                const openWebBtn = $('#telegramChatOpenWebBtn');
                if (webUrl) {
                    openWebBtn.attr('href', webUrl).removeClass('d-none');
                } else {
                    openWebBtn.addClass('d-none');
                }

                const openAppBtn = $('#telegramChatOpenAppBtn');
                if (appUrl) {
                    openAppBtn.attr('href', appUrl).removeClass('d-none');
                } else {
                    openAppBtn.addClass('d-none');
                }

                const testBtn = $('#telegramChatModalSendTestBtn');
                if (testUrl) {
                    testBtn.attr('data-test-url', testUrl).attr('data-group-id', groupId).removeClass('d-none');
                } else {
                    testBtn.addClass('d-none');
                }

                // Modal iFrame loading
                const iframe = document.getElementById('telegramChatModalIframe');
                const loader = document.getElementById('telegramChatIframeLoader');
                if (iframe) {
                    if (webUrl) {
                        if (loader) loader.style.display = 'block';
                        iframe.style.opacity = '0';
                        iframe.src = webUrl;
                        iframe.onload = function () {
                            if (loader) loader.style.display = 'none';
                            iframe.style.opacity = '1';
                        };
                    } else {
                        iframe.src = 'about:blank';
                        if (loader) loader.style.display = 'none';
                    }
                }

                const modalEl = document.getElementById('telegramChatModal');
                if (modalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modalInstance.show();
                    } else {
                        $(modalEl).modal('show');
                    }
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                }
            };
        })();

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

            // Delegated click listeners (Fallback)
            $(document).on('click', '.btnDeleteRule', function (e) {
                window.deleteTelegramRuleItem(this, e);
            });
            $(document).on('click', '.btnTestRule', function (e) {
                window.sendTelegramRuleTest(this, e);
            });
            $(document).on('change', '.toggleRuleStatus', function (e) {
                window.toggleTelegramRuleStatus(this, e);
            });

            // Delegated click listeners for Chat Modal
            $(document).on('click', '.openTelegramChatModalBtn', function (event) {
                window.openTelegramChatModal(this, event);
            });

            // Copy Chat ID in modal
            $(document).on('click', '#telegramChatModalCopyChatIdBtn', function () {
                const text = $('#telegramChatModalChatId').text().trim();
                if (text && text !== '—') {
                    window.copyTelegramText(text, I18N.copiedToClipboard);
                }
            });

            // Copy Invite Link in modal
            $(document).on('click', '#telegramChatModalCopyLinkBtn', function () {
                const text = $('#telegramChatModalInviteLink').text().trim();
                if (text) {
                    window.copyTelegramText(text, I18N.copiedToClipboard);
                }
            });

            // Send Test inside modal
            $(document).on('click', '#telegramChatModalSendTestBtn', function (event) {
                const btn = $(this);
                const testUrl = btn.attr('data-test-url');
                const groupId = btn.attr('data-group-id');
                if (!testUrl || btn.data('is-testing')) return;

                btn.data('is-testing', true).addClass('disabled').css('pointer-events', 'none');
                const originalHtml = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> ' + @json(__('index.sending') ?: 'កំពុងផ្ញើ...'));

                fetch(testUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    showToast(data.status ? 'success' : 'error', data.message || (data.status ? I18N.testSent : I18N.testFailed));
                    if (data.status && typeof window.refreshTelegramRuleBlock === 'function') {
                        window.refreshTelegramRuleBlock();
                    }
                })
                .catch(err => {
                    showToast('error', 'Error sending test message.');
                })
                .finally(() => {
                    btn.data('is-testing', false).removeClass('disabled').css('pointer-events', '').html(originalHtml);
                    if (typeof feather !== 'undefined') feather.replace();
                });
            });

            // Clear iframe on modal close to release resources
            $('#telegramChatModal').on('hidden.bs.modal', function () {
                const iframe = document.getElementById('telegramChatModalIframe');
                if (iframe) {
                    iframe.src = 'about:blank';
                }
                const loader = document.getElementById('telegramChatIframeLoader');
                if (loader) loader.style.display = 'block';
            });

            document.addEventListener('telegram-rule:refresh', function () {
                if (typeof window.refreshTelegramRuleBlock === 'function') {
                    window.refreshTelegramRuleBlock();
                } else {
                    window.location.reload();
                }
            });
        });
    </script>
    <script src="{{ asset('assets/js/telegram-rule-action-modal.js') }}?v={{ filemtime(public_path('assets/js/telegram-rule-action-modal.js')) }}"></script>
@endsection
