@extends('layouts.master')

@section('title', 'Telegram Groups')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.telegramGroup.common.breadcrumb', ['title' => 'Telegram Groups'])
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
                    <input type="hidden" id="per_page_hidden" name="per_page" value="{{ $filterParameters['per_page'] ?? '10' }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-md-6">
                            <label for="name" class="form-label">Group Name</label>
                            <input type="text" id="name" name="name" placeholder="Search by group name"
                                   value="{{ $filterParameters['name'] ?? '' }}" class="form-control">
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="action_key" class="form-label">Action</label>
                            <select class="form-control" id="action_key" name="action_key">
                                <option value="">All Actions</option>
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
                    'directoryTitle' => 'Telegram Groups',
                    'directoryIcon' => 'send',
                    'directoryPermission' => 'create_telegram_group',
                    'directoryAddUrl' => route('admin.telegram-groups.create'),
                    'directoryDefaultSize' => '10',
                    'directoryCurrentSize' => (string) ($filterParameters['per_page'] ?? '10'),
                    'directoryPageSizes' => [10, 25, 50, 100, 'all'],
                    'directorySearchKey' => 'search',
                    'directoryLive' => true,
                ])
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="telegramGroupTable" class="table mb-0 align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Channel / Group Name</th>
                                <th>Telegram Chat ID</th>
                                <th>Topic ID</th>
                                <th>Direct Telegram Link</th>
                                <th class="text-center">Assigned Rules</th>
                                <th class="text-center">{{ __('index.status') }}</th>
                                @canany(['edit_telegram_group','delete_telegram_group','test_telegram_group'])
                                    <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                                @endcanany
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($telegramGroups as $key => $telegramGroup)
                                <tr>
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
                                        @if($directWebUrl)
                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                <a href="{{ $directWebUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-2 py-1" style="border-radius: 6px; font-size: 11px;" title="Open Telegram Web / Direct Link: {{ $directWebUrl }}">
                                                    <i data-feather="external-link" style="width: 12px; height: 12px;"></i> Open Chat
                                                </a>
                                                @if($directAppUrl)
                                                    <a href="{{ $directAppUrl }}" class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1 px-1.5 py-1" style="border-radius: 6px; font-size: 11px;" title="Open in Telegram Desktop / Mobile App (tg://)">
                                                        <i data-feather="send" style="width: 11px; height: 11px;"></i> App
                                                    </a>
                                                @endif
                                            </div>
                                            @if($telegramGroup->invite_link)
                                                <div class="mt-1 font-monospace text-truncate text-muted" style="max-width: 200px; font-size: 10px;" title="{{ $telegramGroup->invite_link }}">
                                                    {{ $telegramGroup->invite_link }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted small">No direct link</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php $rCount = $rulesCountByChannel[$telegramGroup->id] ?? 0; @endphp
                                        @if($rCount > 0)
                                            <a href="{{ route('admin.telegram-rules.index') }}" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-decoration-none px-2 py-1 rounded-pill" title="View notification rules targeting this channel">
                                                <i data-feather="bell" style="width: 11px; height: 11px;" class="me-1"></i> {{ $rCount }} {{ Str::plural('Rule', $rCount) }}
                                            </a>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">0 Rules</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can('toggle_telegram_group_status')
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{ route('admin.telegram-groups.toggle-status', $telegramGroup->id) }}"
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
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                @can('test_telegram_group')
                                                    <li class="me-2 text-center">
                                                        <a class="btn btn-sm btn-outline-primary sendTelegramTest"
                                                           data-href="{{ route('admin.telegram-groups.test', $telegramGroup->id) }}"
                                                           href="javascript:void(0)">
                                                            <i class="link-icon" data-feather="send"></i> Send Test
                                                        </a>
                                                        @if($telegramGroup->last_tested_at)
                                                            <div class="mt-1">
                                                                <span class="badge {{ $telegramGroup->last_test_status === 'success' ? 'bg-success' : 'bg-danger' }}"
                                                                      title="Last tested: {{ $telegramGroup->last_tested_at }}">
                                                                    {{ $telegramGroup->last_test_status === 'success' ? 'Tested' : 'Failed' }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </li>
                                                @endcan
                                                @can('edit_telegram_group')
                                                    <li class="me-2">
                                                        <a href="{{ route('admin.telegram-groups.edit', $telegramGroup->id) }}"
                                                           data-open-telegram-group-modal
                                                           data-telegram-group-modal-title="{{ __('index.edit') }}: {{ $telegramGroup->name }}"
                                                           title="{{ __('index.edit') }}">
                                                            <i class="link-icon" data-feather="edit"></i>
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('delete_telegram_group')
                                                    <li>
                                                        <a class="deleteTelegramGroup" data-href="{{ route('admin.telegram-groups.delete', $telegramGroup->id) }}">
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
                    {{ $telegramGroups->links() }}
                </div>
            </div>
        </div>

        @include('admin.telegramGroup.common.form-modal')
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $("#action_key").select2({ width: '100%' });
            $("#is_active").select2({ width: '100%' });
            $("#per_page").select2({ minimumResultsForSearch: Infinity });

            $('#telegramGroupFilterCollapse').on('show.bs.collapse', function () {
                $('.directory-filter-toggle[data-bs-target="#telegramGroupFilterCollapse"]').attr('aria-expanded', 'true');
            }).on('hide.bs.collapse', function () {
                $('.directory-filter-toggle[data-bs-target="#telegramGroupFilterCollapse"]').attr('aria-expanded', 'false');
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
                    Toast.fire({
                        icon: icon || 'success',
                        title: title
                    });
                }
            };

            $(document).on('change', '.toggleStatus', function (event) {
                event.preventDefault();
                const checkbox = $(this);
                const isChecked = checkbox.prop('checked');
                const href = checkbox.attr('href') || checkbox.data('href');

                Swal.fire({
                    title: '{{ __('index.are_you_sure_change_status') }}',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
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
                            refreshTelegramGroupList();
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

            $(document).on('click', '.deleteTelegramGroup', function (event) {
                event.preventDefault();
                const href = $(this).data('href');
                Swal.fire({
                    title: 'Are you sure you want to delete this Telegram group?',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
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
                            showToast(data.status ? 'success' : 'error', data.message || 'Deleted successfully.');
                            refreshTelegramGroupList();
                        })
                        .catch(err => {
                            showToast('error', 'Failed to delete Telegram group.');
                        });
                    }
                });
            });

            $(document).on('click', '.sendTelegramTest', function (event) {
                event.preventDefault();
                const btn = $(this);
                const href = btn.data('href');
                Swal.fire({
                    title: 'Send test message to this Telegram group?',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
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
                            showToast(data.status ? 'success' : 'error', data.message || (data.status ? 'Test sent successfully.' : 'Test failed.'));
                            refreshTelegramGroupList();
                        })
                        .catch(err => {
                            showToast('error', 'Error sending test message.');
                        })
                        .finally(() => {
                            btn.removeClass('disabled');
                        });
                    }
                });
            });

            let telegramSearchTimer = null;
            let telegramListController = null;
            let telegramListRequestId = 0;

            const initTelegramListControls = () => {
                const perPage = $('#per_page');

                if (perPage.length && !perPage.hasClass('select2-hidden-accessible')) {
                    perPage.select2({ minimumResultsForSearch: Infinity });
                }
            };

            const replaceTelegramGroupResults = (doc) => {
                const currentTableBody = document.querySelector('#telegramGroupTable tbody');
                const nextTableBody = doc.querySelector('#telegramGroupTable tbody');
                const currentPaginationArea = document.querySelector('#telegramGroupPaginationArea');
                const nextPaginationArea = doc.querySelector('#telegramGroupPaginationArea');

                if (!currentTableBody || !nextTableBody) {
                    return false;
                }

                currentTableBody.innerHTML = nextTableBody.innerHTML;

                const count = document.querySelector('[data-directory-count="telegramGroupTable"]');
                const nextCount = doc.querySelector('[data-directory-count="telegramGroupTable"]');
                if (count && nextCount) count.textContent = nextCount.textContent;

                if (currentPaginationArea && nextPaginationArea) {
                    currentPaginationArea.innerHTML = nextPaginationArea.innerHTML;
                } else {
                    const currentPagination = document.querySelector('#telegramGroupListSection .dataTables_paginate');
                    const nextPagination = doc.querySelector('#telegramGroupListSection .dataTables_paginate');
                    if (currentPagination) {
                        currentPagination.innerHTML = nextPagination ? nextPagination.innerHTML : '';
                    }
                }

                document.dispatchEvent(new CustomEvent('directory:updated'));

                if (window.feather) {
                    feather.replace();
                }

                return true;
            };

            const refreshTelegramGroupList = (options = {}) => {
                const form = document.getElementById('telegramGroupFilterForm');
                const listSection = document.getElementById('telegramGroupListSection');
                const tableBody = document.querySelector('#telegramGroupTable tbody');

                if (!form || !listSection) {
                    return;
                }

                const params = new URLSearchParams(new FormData(form));

                // Explicitly sync per_page from the directory-toolbar select
                const perPageSelect = document.getElementById('per_page');
                const perPageVal = perPageSelect ? perPageSelect.value : $('#per_page').val();
                if (perPageVal) {
                    params.delete('per_page');
                    params.set('per_page', perPageVal);
                    const hiddenPerPage = document.getElementById('per_page_hidden');
                    if (hiddenPerPage) hiddenPerPage.value = perPageVal;
                }

                // Explicitly sync search from the directory search input
                const searchVal = document.getElementById('telegramGroupListSearch')?.value;
                if (searchVal !== undefined && searchVal !== null && searchVal !== '') {
                    params.set('search', searchVal);
                    const hiddenSearch = document.getElementById('search');
                    if (hiddenSearch) hiddenSearch.value = searchVal;
                }

                // Reset page to 1 when changing page size or filters
                if (options.resetPage || perPageVal === 'all') {
                    params.delete('page');
                    params.set('page', '1');
                }

                const requestUrl = `${form.action}?${params.toString()}`;
                const requestId = ++telegramListRequestId;

                if (telegramListController) {
                    telegramListController.abort();
                }

                telegramListController = new AbortController();

                if (tableBody) {
                    tableBody.style.opacity = '0.6';
                }

                fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: telegramListController.signal
                })
                    .then((response) => {
                        if (!response.ok) throw new Error('Fetch failed with status ' + response.status);
                        return response.text();
                    })
                    .then((html) => {
                        if (requestId !== telegramListRequestId) {
                            return;
                        }

                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        if (!replaceTelegramGroupResults(doc)) {
                            window.location.href = requestUrl;
                            return;
                        }

                        window.history.replaceState({}, '', requestUrl);
                    })
                    .catch((error) => {
                        if (error.name === 'AbortError') {
                            return;
                        }
                        console.warn('Block refresh failed, falling back to page reload:', error);
                        window.location.href = requestUrl;
                    })
                    .finally(() => {
                        if (requestId === telegramListRequestId) {
                            if (tableBody) {
                                tableBody.style.opacity = '1';
                            }
                            telegramListController = null;
                        }
                    });
            };

            window.refreshTelegramGroupList = refreshTelegramGroupList;
            document.addEventListener('telegram-group:refresh', refreshTelegramGroupList);

            $(document).on('submit', '#telegramGroupFilterForm', function (event) {
                event.preventDefault();
                refreshTelegramGroupList({ resetPage: true });
            });

            $(document).on('change select2:select', '#per_page', function () {
                refreshTelegramGroupList({ resetPage: true });
            });

            $(document).on('input', '#telegramGroupListSearch', function () {
                const searchInput = document.getElementById('search');
                if (searchInput) {
                    searchInput.value = this.value;
                }

                clearTimeout(telegramSearchTimer);
                telegramSearchTimer = setTimeout(() => {
                    refreshTelegramGroupList({ resetPage: true });
                }, 300);
            });

            $(document).on('click', '#telegramGroupListSection .pagination a', function (event) {
                event.preventDefault();

                const tableBody = document.querySelector('#telegramGroupTable tbody');
                const requestUrl = this.href;
                const requestId = ++telegramListRequestId;

                if (telegramListController) {
                    telegramListController.abort();
                }

                telegramListController = new AbortController();

                if (tableBody) {
                    tableBody.style.opacity = '0.6';
                }

                fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: telegramListController.signal
                })
                    .then((response) => response.text())
                    .then((html) => {
                        if (requestId !== telegramListRequestId) {
                            return;
                        }

                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        if (!replaceTelegramGroupResults(doc)) {
                            console.warn('Pagination replacement failed');
                            return;
                        }

                        window.history.replaceState({}, '', requestUrl);
                    })
                    .catch((error) => {
                        if (error.name === 'AbortError') {
                            return;
                        }
                        console.warn('Pagination fetch failed silently:', error);
                    })
                    .finally(() => {
                        if (requestId === telegramListRequestId) {
                            if (tableBody) {
                                tableBody.style.opacity = '1';
                            }
                            telegramListController = null;
                        }
                    });
            });

            initTelegramListControls();
        });
    </script>
    <script src="{{ asset('assets/js/telegram-group-action-modal.js') }}?v={{ filemtime(public_path('assets/js/telegram-group-action-modal.js')) }}"></script>
@endsection
