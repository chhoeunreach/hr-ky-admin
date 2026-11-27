@extends('layouts.master')

@section('title', 'Telegram Notification Rules')

@section('button')
    @can('create_telegram_group')
        <form action="{{ route('admin.telegram-rules.sync-default') }}" method="POST" class="d-inline me-1">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary" title="បង្កើតវិធានការដើមស្វ័យប្រវត្តពី Chat IDs ដែលមានស្រាប់">
                <i class="link-icon" data-feather="refresh-cw"></i> ទាញទិន្នន័យដើមពី Chat IDs
            </button>
        </form>
        <a href="{{ route('admin.telegram-rules.create') }}">
            <button class="btn btn-sm btn-primary">
                <i class="link-icon" data-feather="plus"></i> {{ __('index.create_rule') ?? 'បង្កើតវិធានការថ្មី' }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.telegramRule.common.breadcrumb', ['title' => 'Event Notification Rules'])

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
                    <input type="hidden" id="rule_per_page_hidden" name="per_page" value="{{ $filters['per_page'] ?? '10' }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-4 col-md-6">
                            <label for="event_key" class="form-label">{{ __('index.event') ?? 'ព្រឹត្តិការណ៍ (Event)' }}</label>
                            <select class="form-control select2-basic" id="event_key" name="event_key">
                                <option value="">-- គ្រប់ព្រឹត្តិការណ៍ (All Events) --</option>
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
                                <option value="1" {{ (string)($filterParameters['is_active'] ?? '') === '1' ? 'selected' : '' }}>{{ __('index.active') }} (Active)</option>
                                <option value="0" {{ (string)($filterParameters['is_active'] ?? '') === '0' ? 'selected' : '' }}>{{ __('index.inactive') }} (Inactive)</option>
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
                    'directoryTitle' => 'Event Notification Rules',
                    'directoryIcon' => 'bell',
                    'directoryPermission' => 'create_telegram_group',
                    'directoryAddUrl' => route('admin.telegram-rules.create'),
                    'directoryDefaultSize' => '10',
                    'directoryCurrentSize' => (string) ($filterParameters['per_page'] ?? '10'),
                    'directoryPageSizes' => [10, 25, 50, 100, 'all'],
                    'directorySearchKey' => 'search',
                    'directoryLive' => true,
                ])
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="telegramRuleTable" class="table mb-0 align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('index.rule_name') ?? 'ឈ្មោះវិធានការ (Rule Name)' }}</th>
                                <th>{{ __('index.event') ?? 'ព្រឹត្តិការណ៍ (Event)' }}</th>
                                <th>{{ __('index.branch_scope') ?? 'វិសាលភាព (Scope)' }}</th>
                                <th>{{ __('index.target_channels') ?? 'Channels ទទួលសារ' }}</th>
                                <th class="text-center">{{ __('index.options') ?? 'ជម្រើស' }}</th>
                                <th class="text-center">{{ __('index.status') ?? 'ស្ថានភាព' }}</th>
                                @canany(['edit_telegram_group','delete_telegram_group','test_telegram_group'])
                                    <th class="text-center" data-directory-actions>{{ __('index.action') ?? 'សកម្មភាព' }}</th>
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
                                                        +{{ count($ruleEventKeys) - 2 }} ផ្សេងទៀត
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
                                            <span class="badge bg-info">🌐 គ្រប់សាខា (All)</span>
                                        @else
                                            <span class="badge bg-warning text-dark">🏢 សាខាជាក់លាក់ (Specific)</span>
                                            @if(!empty($rule->branch_ids))
                                                <div class="small text-muted mt-1">{{ count($rule->branch_ids) }} សាខា</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $channelIds = (array) ($rule->channel_ids ?? []);
                                            $validChannels = array_filter(array_map(fn($id) => $channelsById[$id] ?? null, $channelIds));
                                        @endphp
                                        @if(empty($channelIds))
                                            <span class="text-danger small"><i data-feather="alert-triangle"></i> គ្មាន Channel</span>
                                        @elseif(empty($validChannels))
                                            <span class="text-danger small"><i data-feather="alert-triangle"></i> Channel ត្រូវបានលុប</span>
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
                                                <span class="badge bg-secondary mb-1">+{{ count($validChannels) - 2 }} ផ្សេងទៀត</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $rule->send_location ? 'bg-success' : 'bg-secondary' }}" title="Send Location Map">
                                            📍 Map
                                        </span>
                                        <span class="badge {{ $rule->send_selfie ? 'bg-success' : 'bg-secondary' }}" title="Send Selfie Photo">
                                            📷 Photo
                                        </span>
                                        @if($rule->is_silent)
                                            <span class="badge bg-dark" title="Silent Alert">🔕 Silent</span>
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
                                                {{ $rule->is_active ? 'Active' : 'Inactive' }}
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
                                                       title="ធ្វើតេស្តផ្ញើសារសាកល្បងទៅ Channels">
                                                        <i class="link-icon" data-feather="send" style="width: 14px; height: 14px;"></i> Test
                                                    </a>
                                                    @if($rule->last_test_at)
                                                        <div class="mt-1" style="font-size: 10px;">
                                                             <span class="badge {{ $rule->last_test_status === 'success' ? 'bg-success' : 'bg-danger' }}"
                                                                   title="Last tested: {{ $rule->last_test_at }}">
                                                                {{ $rule->last_test_status === 'success' ? 'Tested' : 'Failed' }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endcan
                                            @can('edit_telegram_group')
                                                <a href="{{ route('admin.telegram-rules.edit', $rule->id) }}"
                                                   class="btn btn-xs btn-outline-info"
                                                   data-open-telegram-rule-modal
                                                   data-telegram-rule-modal-title="{{ __('index.edit') }} វិធានការ: {{ $rule->name }}"
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
                                        <div>មិនទាន់មានវិធានការបញ្ជូនសារនៅឡើយទេ។ (No notification rules found).</div>
                                        @can('create_telegram_group')
                                            <div class="mt-3">
                                                <form action="{{ route('admin.telegram-rules.sync-default') }}" method="POST" class="d-inline me-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        <i class="link-icon" data-feather="download-cloud"></i> បង្កើតវិធានការដើមពី Chat IDs ដែលមានស្រាប់
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.telegram-rules.create') }}" class="btn btn-sm btn-primary">
                                                    <i class="link-icon" data-feather="plus"></i> បង្កើតវិធានការដំបូង
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
                    @if(($filters['per_page'] ?? '') === 'all')
                        {{ __('index.showing') }} <strong>{{ __('index.all') }}</strong> ({{ number_format($rules->total()) }} {{ __('index.entries') }})
                    @elseif($rules->total() > 0)
                        {{ __('index.showing') }} {{ number_format($rules->firstItem() ?? 1) }} - {{ number_format($rules->lastItem() ?? $rules->total()) }} {{ __('index.of') }} {{ number_format($rules->total()) }} {{ __('index.entries') }}
                    @else
                        {{ __('index.no_records_found') }}
                    @endif
                </div>
                <div class="dataTables_paginate">
                    {{ $rules->links() }}
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

            let ruleSearchTimer = null;
            let ruleListController = null;
            let ruleListRequestId = 0;

            const replaceTelegramRuleResults = (doc) => {
                const currentTableBody = document.querySelector('#telegramRuleTable tbody');
                const nextTableBody = doc.querySelector('#telegramRuleTable tbody');
                const currentPaginationArea = document.querySelector('#telegramRulePaginationArea');
                const nextPaginationArea = doc.querySelector('#telegramRulePaginationArea');

                if (!currentTableBody || !nextTableBody) {
                    return false;
                }

                currentTableBody.innerHTML = nextTableBody.innerHTML;

                const count = document.querySelector('[data-directory-count="telegramRuleTable"]');
                const nextCount = doc.querySelector('[data-directory-count="telegramRuleTable"]');
                if (count && nextCount) count.textContent = nextCount.textContent;

                if (currentPaginationArea && nextPaginationArea) {
                    currentPaginationArea.innerHTML = nextPaginationArea.innerHTML;
                } else {
                    const currentPagination = document.querySelector('#telegramRuleListSection .dataTables_paginate');
                    const nextPagination = doc.querySelector('#telegramRuleListSection .dataTables_paginate');
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

            const refreshTelegramRuleList = (options = {}) => {
                const form = document.getElementById('telegramRuleFilterForm');
                const listSection = document.getElementById('telegramRuleListSection');
                const tableBody = document.querySelector('#telegramRuleTable tbody');

                if (!form || !listSection) {
                    return;
                }

                const params = new URLSearchParams(new FormData(form));

                // Explicitly sync per_page from the directory-toolbar select
                const perPageSelect = document.getElementById('per_page');
                const perPageVal = perPageSelect ? perPageSelect.value : $('#per_page').val();
                if (perPageVal) {
                    params.set('per_page', perPageVal);
                    const hiddenRulePerPage = document.getElementById('rule_per_page_hidden');
                    if (hiddenRulePerPage) hiddenRulePerPage.value = perPageVal;
                }

                // Explicitly sync search from the directory search input
                const searchVal = document.getElementById('telegramRuleListSearch')?.value;
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
                const requestId = ++ruleListRequestId;

                if (ruleListController) {
                    ruleListController.abort();
                }

                ruleListController = new AbortController();

                if (tableBody) {
                    tableBody.style.opacity = '0.6';
                }

                fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: ruleListController.signal
                })
                    .then((response) => {
                        if (!response.ok) throw new Error('Fetch failed with status ' + response.status);
                        return response.text();
                    })
                    .then((html) => {
                        if (requestId !== ruleListRequestId) {
                            return;
                        }

                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        if (!replaceTelegramRuleResults(doc)) {
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
                        if (requestId === ruleListRequestId) {
                            if (tableBody) {
                                tableBody.style.opacity = '1';
                            }
                            ruleListController = null;
                        }
                    });
            };

            window.refreshTelegramRuleList = refreshTelegramRuleList;

            $(document).on('submit', '#telegramRuleFilterForm', function (event) {
                event.preventDefault();
                refreshTelegramRuleList({ resetPage: true });
            });

            $(document).on('change', '#per_page', function () {
                refreshTelegramRuleList({ resetPage: true });
            });

            $(document).on('input', '#telegramRuleListSearch', function () {
                const searchInput = document.getElementById('search');
                if (searchInput) {
                    searchInput.value = this.value;
                }

                clearTimeout(ruleSearchTimer);
                ruleSearchTimer = setTimeout(() => {
                    refreshTelegramRuleList({ resetPage: true });
                }, 300);
            });

            $(document).on('click', '#telegramRuleListSection .pagination a', function (event) {
                event.preventDefault();

                const tableBody = document.querySelector('#telegramRuleTable tbody');
                const requestUrl = this.href;
                const requestId = ++ruleListRequestId;

                if (ruleListController) {
                    ruleListController.abort();
                }

                ruleListController = new AbortController();

                if (tableBody) {
                    tableBody.style.opacity = '0.6';
                }

                fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: ruleListController.signal
                })
                    .then((response) => response.text())
                    .then((html) => {
                        if (requestId !== ruleListRequestId) {
                            return;
                        }

                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        if (!replaceTelegramRuleResults(doc)) {
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
                        if (requestId === ruleListRequestId) {
                            if (tableBody) {
                                tableBody.style.opacity = '1';
                            }
                            ruleListController = null;
                        }
                    });
            });

            // Toggle Rule Status (Zero-reload AJAX)
            $(document).on('change', '.toggleRuleStatus', function (e) {
                e.preventDefault();
                let checkbox = $(this);
                let href = checkbox.data('href');
                let isChecked = checkbox.prop('checked');

                Swal.fire({
                    title: 'ប្ដូរស្ថានភាពវិធានការ?',
                    text: 'តើអ្នកចង់' + (isChecked ? 'បើកដំណើរការ (Active)' : 'បិទដំណើរការ (Inactive)') + ' វិធានការនេះឬ?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'បាទ/ចាស',
                    cancelButtonText: 'បោះបង់'
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
                            showToast('success', data.message || 'ស្ថានភាពត្រូវបានផ្លាស់ប្តូរដោយជោគជ័យ។');
                        })
                        .catch(err => {
                            checkbox.prop('checked', !isChecked);
                            showToast('error', 'មានបញ្ហាក្នុងការផ្លាស់ប្តូរស្ថានភាព។');
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
                    title: 'លុបវិធានការនេះ?',
                    text: 'តើអ្នកប្រាកដជាចង់លុបវិធានការបញ្ជូនសារនេះចេញមែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានឡើយ។',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'បាទ/ចាស លុបចេញ',
                    cancelButtonText: 'បោះបង់'
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
                            showToast(data.status ? 'success' : 'error', data.message || 'វិធានការត្រូវបានលុបចេញដោយជោគជ័យ។');
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
                            showToast('error', 'មានបញ្ហាក្នុងការលុបវិធានការ។');
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
                    title: 'ផ្ញើសារសាកល្បង (Test)',
                    text: 'តើអ្នកចង់ផ្ញើសារ Test ទៅកាន់ Telegram Channels នៃវិធានការនេះមែនទេ?',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'ផ្ញើសារ Test',
                    cancelButtonText: 'បោះបង់'
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
                            showToast(data.status ? 'success' : 'error', data.message || 'បានផ្ញើសារ Test រួចរាល់។');
                            if (data.status) {
                                refreshTelegramRuleList();
                            }
                        })
                        .catch(err => {
                            showToast('error', 'មានបញ្ហាក្នុងការផ្ញើសារ Test');
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
