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
                        <table id="telegramGroupTable" class="table mb-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Chat ID</th>
                                <th>Action</th>
                                <th>Events</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th class="text-center">All</th>
                                <th class="text-center">Map</th>
                                <th class="text-center">Selfie</th>
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
                                    <td>{{ $telegramGroup->name }}</td>
                                    <td>
                                        @php $chatIds = $telegramGroup->chat_ids ?: [$telegramGroup->chat_id]; @endphp
                                        @foreach(array_filter($chatIds) as $chatId)
                                            <span class="badge bg-light text-dark mb-1">{{ $chatId }}</span>
                                        @endforeach
                                        @if($telegramGroup->topic_id)
                                            <div>
                                                <span class="badge bg-secondary mb-1" title="Telegram Forum Topic / Message Thread ID">
                                                    Topic: {{ $telegramGroup->topic_id }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @php $actionKeys = array_values(array_filter($telegramGroup->action_keys ?: [$telegramGroup->action_key])); @endphp
                                        @if(count($actionKeys) > 3)
                                            @foreach(array_slice($actionKeys, 0, 2) as $actionKey)
                                                <span class="badge bg-info mb-1">{{ $actionOptions[$actionKey] ?? $actionKey }}</span>
                                            @endforeach
                                            <span class="badge bg-primary mb-1" title="{{ implode(', ', array_map(fn($k) => $actionOptions[$k] ?? $k, $actionKeys)) }}">
                                                +{{ count($actionKeys) - 2 }} more
                                            </span>
                                        @else
                                            @foreach($actionKeys as $actionKey)
                                                <span class="badge bg-info mb-1">{{ $actionOptions[$actionKey] ?? $actionKey }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $eventKeys = array_values(array_filter($telegramGroup->event_keys ?: (isset($telegramGroup->action_key) ? [$telegramGroup->action_key] : [])));
                                            $allEventOpts = \App\Models\TelegramGroup::eventOptions();
                                        @endphp
                                        @if(count($eventKeys) > 4)
                                            @foreach(array_slice($eventKeys, 0, 3) as $eventKey)
                                                <span class="badge bg-secondary mb-1">{{ $allEventOpts[$eventKey] ?? $eventKey }}</span>
                                            @endforeach
                                            <span class="badge bg-primary mb-1" title="{{ implode(', ', array_map(fn($k) => $allEventOpts[$k] ?? $k, $eventKeys)) }}">
                                                +{{ count($eventKeys) - 3 }} more
                                            </span>
                                        @else
                                            @foreach($eventKeys as $eventKey)
                                                <span class="badge bg-secondary mb-1">{{ $allEventOpts[$eventKey] ?? $eventKey }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{ $telegramGroup->branch_name ?: ($telegramGroup->branch?->name ?? 'All') }}</td>
                                    <td>{{ $telegramGroup->department_name ?: ($telegramGroup->department?->dept_name ?? 'All') }}</td>
                                    <td class="text-center">{{ $telegramGroup->send_for_all ? 'Yes' : 'No' }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ ($telegramGroup->send_location ?? true) ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ($telegramGroup->send_location ?? true) ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ ($telegramGroup->send_selfie ?? true) ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ($telegramGroup->send_selfie ?? true) ? 'Yes' : 'No' }}
                                        </span>
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

            <div class="dataTables_paginate mt-4">
                {{ $telegramGroups->appends($_GET)->links() }}
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

            $(document).on('change', '.toggleStatus', function (event) {
                event.preventDefault();
                var status = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: '{{ __('index.are_you_sure_change_status') }}',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    } else if (result.isDenied) {
                        (status === 0) ? $(this).prop('checked', true) : $(this).prop('checked', false);
                    }
                });
            });

            $(document).on('click', '.deleteTelegramGroup', function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: 'Are you sure you want to delete this Telegram group?',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });

            $(document).on('click', '.sendTelegramTest', function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: 'Send test message to this Telegram group?',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding: '10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
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
                const currentPagination = document.querySelector('#telegramGroupListSection .dataTables_paginate');
                const nextPagination = doc.querySelector('#telegramGroupListSection .dataTables_paginate');

                if (!currentTableBody || !nextTableBody) {
                    return false;
                }

                currentTableBody.innerHTML = nextTableBody.innerHTML;

                const count = document.querySelector('[data-directory-count="telegramGroupTable"]');
                const nextCount = doc.querySelector('[data-directory-count="telegramGroupTable"]');
                if (count && nextCount) count.textContent = nextCount.textContent;

                if (currentPagination && nextPagination) {
                    currentPagination.innerHTML = nextPagination.innerHTML;
                }

                document.dispatchEvent(new CustomEvent('directory:updated'));

                if (window.feather) {
                    feather.replace();
                }

                return true;
            };

            const refreshTelegramGroupList = () => {
                const form = document.getElementById('telegramGroupFilterForm');
                const listSection = document.getElementById('telegramGroupListSection');
                const tableBody = document.querySelector('#telegramGroupTable tbody');

                if (!form || !listSection) {
                    return;
                }

                const params = new URLSearchParams(new FormData(form));
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
                    .then((response) => response.text())
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

                        form.submit();
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
                refreshTelegramGroupList();
            });

            $(document).on('change', '#per_page', function () {
                refreshTelegramGroupList();
            });

            $(document).on('input', '#telegramGroupListSearch', function () {
                const searchInput = document.getElementById('search');
                if (searchInput) {
                    searchInput.value = this.value;
                }

                clearTimeout(telegramSearchTimer);
                telegramSearchTimer = setTimeout(() => {
                    refreshTelegramGroupList();
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
                            window.location.href = requestUrl;
                            return;
                        }

                        window.history.replaceState({}, '', requestUrl);
                    })
                    .catch((error) => {
                        if (error.name === 'AbortError') {
                            return;
                        }

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
            });

            initTelegramListControls();
        });
    </script>
    <script src="{{ asset('assets/js/telegram-group-action-modal.js') }}"></script>
@endsection
