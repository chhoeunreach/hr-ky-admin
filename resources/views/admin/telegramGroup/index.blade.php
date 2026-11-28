@extends('layouts.master')

@section('title', __('index.telegram_channel_or_group_lists'))

@section('button')
    @can('create_telegram_group')
        <a href="{{ route('admin.telegram-groups.create') }}" data-open-telegram-group-modal data-telegram-group-modal-title="{{ __('index.add_telegram_group') }}">
            <button class="btn btn-sm btn-primary">
                <i class="link-icon" data-feather="plus"></i> {{ __('index.add_telegram_group') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.telegramGroup.common.breadcrumb', ['title' => __('index.telegram_channel_or_group_lists')])
        @include('admin.telegramGroup.common.nav-tabs')

        @php
            $hasTelegramFilters = collect($filterParameters ?? [])
                ->except(['search', 'per_page', 'page'])
                ->filter(function ($value) {
                    if (is_array($value)) {
                        return count(array_filter($value)) > 0;
                    }
                    return $value !== null && $value !== '';
                })
                ->isNotEmpty();
        @endphp
        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'telegramGroupFilterCollapse', 'directoryExpanded' => $hasTelegramFilters])
            <div id="telegramGroupFilterCollapse" class="collapse{{ $hasTelegramFilters ? ' show' : '' }}">
                <form class="forms-sample directory-filter-form" action="{{ route('admin.telegram-groups.index') }}" id="telegramGroupFilterForm" method="get">
                    <input type="hidden" id="search" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-md-6">
                            <label for="name" class="form-label">{{ __('index.channel_or_group_name') }}</label>
                            <input type="text" id="name" name="name" placeholder="{{ __('index.channel_or_group_name') }}"
                                   value="{{ $filterParameters['name'] ?? '' }}" class="form-control">
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="action_key" class="form-label">{{ __('index.action') }}</label>
                            <select class="form-control" id="action_key" name="action_key">
                                <option value="">{{ __('index.all_actions') }}</option>
                                @foreach($actionOptions as $key => $label)
                                    <option value="{{ $key }}" {{ ($filterParameters['action_key'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="is_active" class="form-label">{{ __('index.status') }}</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="">{{ __('index.all') }} {{ __('index.status') }}</option>
                                <option value="1" {{ (string)($filterParameters['is_active'] ?? '') === '1' ? 'selected' : '' }}>{{ __('index.active') }}</option>
                                <option value="0" {{ (string)($filterParameters['is_active'] ?? '') === '0' ? 'selected' : '' }}>{{ __('index.inactive') }}</option>
                            </select>
                        </div>
                    </div>

                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.telegram-groups.index')])
                </form>
            </div>
        </div>

        <div id="telegramGroupListSection">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
                @include('admin.section.directory-toolbar', [
                    'directoryTable' => 'telegramGroupTable',
                    'directoryForm' => 'telegramGroupFilterForm',
                    'directorySearch' => 'telegramGroupListSearch',
                    'directoryRecords' => $telegramGroups,
                    'directoryTitle' => __('index.telegram_channel_or_group_lists'),
                    'directoryIcon' => 'send',
                    'directoryPermission' => 'create_telegram_group',
                    'directoryAddUrl' => route('admin.telegram-groups.create'),
                    'directoryAddTelegramGroupModal' => true,
                    'directoryAddModalTitle' => __('index.add_telegram_group'),
                    'directoryAddLabel' => __('index.add_telegram_group'),
                    'directoryDefaultSize' => '10',
                    'directoryCurrentSize' => (string) ($filterParameters['per_page'] ?? '10'),
                    'directoryPageSizes' => [10, 25, 50, 100, 'all'],
                    'directorySearchKey' => 'search',
                    'directoryLive' => false,
                ])
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="telegramGroupTable" class="table mb-0 align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('index.channel_or_group_name') }}</th>
                                <th>{{ __('index.telegram_chat_id') }}</th>
                                <th>{{ __('index.topic_id') }}</th>
                                <th>{{ __('index.direct_telegram_link') }}</th>
                                <th class="text-center">{{ __('index.assigned_rules') }}</th>
                                <th class="text-center">{{ __('index.status') }}</th>
                                @canany(['edit_telegram_group','delete_telegram_group','test_telegram_group'])
                                    <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                                @endcanany
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($telegramGroups as $key => $telegramGroup)
                                <tr id="telegram-group-row-{{ $telegramGroup->id }}" data-id="{{ $telegramGroup->id }}">
                                    <td>{{ (($telegramGroups->currentPage() - 1) * $telegramGroups->perPage()) + (++$key) }}</td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">{{ $telegramGroup->name }}</div>
                                        @if($telegramGroup->description)
                                            <div class="small text-muted">{{ Str::limit($telegramGroup->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @php $chatIds = $telegramGroup->chat_ids ?: [$telegramGroup->chat_id]; @endphp
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach(array_filter($chatIds) as $chatId)
                                                <code class="px-2 py-0.5 bg-light border rounded text-dark font-monospace" style="font-size: 11px;">{{ $chatId }}</code>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        @if($telegramGroup->topic_id)
                                            @php $topicUrl = $telegramGroup->getTelegramTopicUrl(); @endphp
                                            @if($topicUrl)
                                                <a href="{{ $topicUrl }}" target="_blank" rel="noopener noreferrer" class="badge bg-secondary text-white text-decoration-none d-inline-flex align-items-center gap-1" title="Open Topic in Telegram">
                                                    <i data-feather="hash" style="width: 11px; height: 11px;"></i> Topic: {{ $telegramGroup->topic_id }}
                                                </a>
                                            @else
                                                <span class="badge bg-secondary">Topic: #{{ $telegramGroup->topic_id }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $directWebUrl = $telegramGroup->getTelegramUrl();
                                            $directAppUrl = $telegramGroup->getTelegramAppUrl();
                                        @endphp
                                        @if($directWebUrl || $telegramGroup->chat_id)
                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                <button type="button"
                                                        class="btn btn-xs btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-2 py-1 openTelegramChatModalBtn"
                                                        style="border-radius: 6px; font-size: 11px; background: linear-gradient(135deg, #2AABEE, #229ED9); border: none;"
                                                        data-chat-name="{{ $telegramGroup->name }}"
                                                        data-chat-id="{{ $telegramGroup->chat_id }}"
                                                        data-topic-id="{{ $telegramGroup->topic_id ?? '' }}"
                                                        data-invite-link="{{ $telegramGroup->invite_link ?? '' }}"
                                                        data-web-url="{{ $directWebUrl ?? '' }}"
                                                        data-app-url="{{ $directAppUrl ?? '' }}"
                                                        data-test-url="{{ route('admin.telegram-groups.test', $telegramGroup->id) }}"
                                                        data-group-id="{{ $telegramGroup->id }}"
                                                        data-is-active="{{ $telegramGroup->is_active ? '1' : '0' }}"
                                                        onclick="window.openTelegramChatModal && window.openTelegramChatModal(this, event); return false;"
                                                        title="{{ __('index.open_chat') }}: {{ $telegramGroup->name }}">
                                                    <i data-feather="message-circle" style="width: 12px; height: 12px;"></i> {{ __('index.open_chat') }}
                                                </button>
                                                @if($directAppUrl)
                                                    <a href="{{ $directAppUrl }}" class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1 px-1.5 py-1" style="border-radius: 6px; font-size: 11px;" title="{{ __('index.telegram_app') }} (tg://)">
                                                        <i data-feather="send" style="width: 11px; height: 11px;"></i> {{ __('index.telegram_app') }}
                                                    </a>
                                                @endif
                                            </div>
                                            @if($telegramGroup->invite_link)
                                                <div class="mt-1 font-monospace text-truncate text-muted" style="max-width: 200px; font-size: 10px;" title="{{ $telegramGroup->invite_link }}">
                                                    {{ $telegramGroup->invite_link }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted small">{{ __('index.no_direct_link') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php $rCount = $rulesCountByChannel[$telegramGroup->id] ?? 0; @endphp
                                        @if($rCount > 0)
                                            <a href="{{ route('admin.telegram-rules.index') }}" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-decoration-none px-2 py-1 rounded-pill" title="{{ __('index.assigned_rules') }}">
                                                <i data-feather="bell" style="width: 11px; height: 11px;" class="me-1"></i> {{ $rCount }} {{ __('index.rules') }}
                                            </a>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">0 {{ __('index.rules') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can('toggle_telegram_group_status')
                                            <label class="switch">
                                                <input class="toggleStatus"
                                                       data-href="{{ route('admin.telegram-groups.toggle-status', $telegramGroup->id) }}"
                                                       href="{{ route('admin.telegram-groups.toggle-status', $telegramGroup->id) }}"
                                                       onchange="window.toggleTelegramGroupStatus && window.toggleTelegramGroupStatus(this, event);"
                                                       type="checkbox" {{ $telegramGroup->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        @else
                                            <span class="badge {{ $telegramGroup->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $telegramGroup->is_active ? __('index.active') : __('index.inactive') }}
                                            </span>
                                        @endcan
                                    </td>
                                    @canany(['edit_telegram_group','delete_telegram_group','test_telegram_group'])
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                @can('test_telegram_group')
                                                    <div>
                                                        <a href="javascript:void(0)"
                                                           class="btn btn-xs btn-outline-primary sendTelegramTest"
                                                           data-href="{{ route('admin.telegram-groups.test', $telegramGroup->id) }}"
                                                           onclick="window.sendTelegramGroupTest && window.sendTelegramGroupTest(this, event); return false;"
                                                           title="{{ __('index.send_test') }}">
                                                            <i class="link-icon" data-feather="send" style="width: 13px; height: 13px; pointer-events: none;"></i> {{ __('index.send_test') }}
                                                        </a>
                                                        @if($telegramGroup->last_tested_at)
                                                            <div class="mt-1" style="font-size: 10px;">
                                                                 <span class="badge {{ $telegramGroup->last_test_status === 'success' ? 'bg-success' : 'bg-danger' }}"
                                                                       title="{{ __('index.tested') }}: {{ $telegramGroup->last_tested_at }}">
                                                                    {{ $telegramGroup->last_test_status === 'success' ? __('index.tested') : __('index.failed') }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endcan
                                                @can('edit_telegram_group')
                                                    <a href="{{ route('admin.telegram-groups.edit', $telegramGroup->id) }}"
                                                       class="btn btn-xs btn-outline-info"
                                                       data-open-telegram-group-modal
                                                       data-telegram-group-modal-title="{{ __('index.edit') }}: {{ $telegramGroup->name }}"
                                                       title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit" style="width: 13px; height: 13px; pointer-events: none;"></i>
                                                    </a>
                                                @endcan
                                                @can('delete_telegram_group')
                                                    <a href="javascript:void(0)"
                                                       class="btn btn-xs btn-outline-danger deleteTelegramGroup"
                                                       data-href="{{ route('admin.telegram-groups.delete', $telegramGroup->id) }}"
                                                       onclick="window.deleteTelegramGroupItem && window.deleteTelegramGroupItem(this, event); return false;"
                                                       title="{{ __('index.delete') }}">
                                                        <i class="link-icon" data-feather="trash-2" style="width: 13px; height: 13px; pointer-events: none;"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    @endcanany
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

            <div id="telegramGroupPaginationArea" class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-4">
                <div class="text-muted small">
                    @if(($filterParameters['per_page'] ?? '') === 'all')
                        {{ __('index.showing') }} <strong>{{ __('index.all') }}</strong> ({{ number_format($telegramGroups->total()) }} {{ __('index.entries') }})
                    @elseif($telegramGroups->total() > 0)
                        {{ __('index.showing') }} {{ number_format($telegramGroups->firstItem() ?? 1) }} - {{ number_format($telegramGroups->lastItem() ?? $telegramGroups->total()) }} {{ __('index.of') }} {{ number_format($telegramGroups->total()) }} {{ __('index.entries') }}
                    @else
                        {{ __('index.no_records_found') }}
                    @endif
                </div>
                <div class="dataTables_paginate">
                    {{ $telegramGroups->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        @include('admin.telegramGroup.common.form-modal')
        @include('admin.telegramGroup.common.chat-modal')
    </section>
@endsection

@section('scripts')
    <script>
        (function () {
            const I18N = {
                confirmTitle: @json(__('index.dialog_delete_title') ?: __('index.delete')),
                confirmDeleteGroup: @json(__('index.confirm_delete_telegram_group')),
                btnDelete: @json(__('index.delete_confirm_button') ?: 'លុប'),
                btnDeny: @json(__('index.deny_button') ?: 'បដិសេធ'),
                btnYes: @json(__('index.yes')),
                btnNo: @json(__('index.no')),
                btnOk: @json(__('index.ok')),
                sendTestTitle: @json(__('index.send_test')),
                confirmTestGroup: @json(__('index.confirm_test_telegram_group')),
                statusTitle: @json(__('index.status')),
                confirmStatusChange: @json(__('index.are_you_sure_change_status')),
                statusChanged: @json(__('message.status_changed')),
                groupDeleted: @json(__('index.telegram_group_deleted')),
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
            window.askTelegramGroupConfirm = function (title, message, isDestructive, confirmLabel, cancelLabel) {
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

            // Direct Global Delete Handler (Zero-reload AJAX with row removal)
            window.deleteTelegramGroupItem = function (el, event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                const btn = $(el);
                if (btn.data('is-deleting')) return false;

                const href = btn.attr('data-href') || btn.data('href');
                const row = btn.closest('tr');
                if (!href) return false;

                window.askTelegramGroupConfirm(
                    I18N.confirmTitle,
                    I18N.confirmDeleteGroup,
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
                            showToast('success', data.message || I18N.groupDeleted);
                            row.css('background-color', '#fee2e2').fadeOut(350, function () {
                                row.remove();
                                const countEl = document.querySelector('[data-directory-count="telegramGroupTable"]');
                                if (countEl) {
                                    const cur = parseInt(countEl.textContent.replace(/,/g, ''), 10) || 0;
                                    countEl.textContent = Math.max(0, cur - 1);
                                }
                                if ($('#telegramGroupTable tbody tr').length === 0) {
                                    if (typeof window.refreshTelegramGroupBlock === 'function') {
                                        window.refreshTelegramGroupBlock();
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

            // Direct Global Send Test Handler (Zero-reload AJAX with badge update)
            window.sendTelegramGroupTest = function (el, event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                const btn = $(el);
                if (btn.data('is-testing')) return false;

                const href = btn.attr('data-href') || btn.data('href');
                const row = btn.closest('tr');
                const groupId = row.data('id');
                if (!href) return false;

                window.askTelegramGroupConfirm(I18N.sendTestTitle, I18N.confirmTestGroup, false).then(confirmed => {
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
                        if (data.status && typeof window.refreshTelegramGroupBlock === 'function') {
                            window.refreshTelegramGroupBlock(groupId);
                        }
                    })
                    .catch(err => {
                        showToast('error', 'Error sending test message.');
                    })
                    .finally(() => {
                        btn.data('is-testing', false).removeClass('disabled').css('pointer-events', '');
                    });
                });

                return false;
            };

            // Direct Global Toggle Status Handler
            window.toggleTelegramGroupStatus = function (checkboxEl, event) {
                const checkbox = $(checkboxEl);
                const isChecked = checkbox.prop('checked');
                const href = checkbox.attr('data-href') || checkbox.attr('href');
                if (!href) return;

                window.askTelegramGroupConfirm(I18N.statusTitle, I18N.confirmStatusChange, false).then(confirmed => {
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

            // In-place partial refresh of the Channels / Groups block (No full page reload!)
            window.refreshTelegramGroupBlock = function (highlightId) {
                const listSection = document.getElementById('telegramGroupListSection');
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
                    const newSection = doc.getElementById('telegramGroupListSection');
                    if (newSection) {
                        listSection.innerHTML = newSection.innerHTML;
                        listSection.style.opacity = '1';

                        if (typeof feather !== 'undefined') feather.replace();

                        const perPage = $('#per_page');
                        if (perPage.length && !perPage.hasClass('select2-hidden-accessible')) {
                            perPage.select2({ minimumResultsForSearch: Infinity });
                        }

                        if (highlightId) {
                            const updatedRow = document.getElementById('telegram-group-row-' + highlightId);
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
            $("#action_key").select2({ width: '100%' });
            $("#is_active").select2({ width: '100%' });
            $("#per_page").select2({ minimumResultsForSearch: Infinity });

            $('#telegramGroupFilterCollapse').on('show.bs.collapse', function () {
                $('.directory-filter-toggle[data-bs-target="#telegramGroupFilterCollapse"]').attr('aria-expanded', 'true');
            }).on('hide.bs.collapse', function () {
                $('.directory-filter-toggle[data-bs-target="#telegramGroupFilterCollapse"]').attr('aria-expanded', 'false');
            });

            // Delegated click listeners (Fallback)
            $(document).on('click', '.deleteTelegramGroup', function (event) {
                window.deleteTelegramGroupItem(this, event);
            });
            $(document).on('click', '.sendTelegramTest', function (event) {
                window.sendTelegramGroupTest(this, event);
            });
            $(document).on('change', '.toggleStatus', function (event) {
                window.toggleTelegramGroupStatus(this, event);
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
                    if (data.status && groupId && typeof window.refreshTelegramGroupBlock === 'function') {
                        window.refreshTelegramGroupBlock(groupId);
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

            const perPage = $('#per_page');
            if (perPage.length && !perPage.hasClass('select2-hidden-accessible')) {
                perPage.select2({ minimumResultsForSearch: Infinity });
            }

            document.addEventListener('telegram-group:refresh', function () {
                if (typeof window.refreshTelegramGroupBlock === 'function') {
                    window.refreshTelegramGroupBlock();
                } else {
                    window.location.reload();
                }
            });
        });
    </script>
    <script src="{{ asset('assets/js/telegram-group-action-modal.js') }}?v={{ filemtime(public_path('assets/js/telegram-group-action-modal.js')) }}"></script>
@endsection
