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

        {{-- Filter Panel --}}
        <div class="card mb-3">
            <div class="card-body py-3">
                <form action="{{ route('admin.telegram-rules.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4 col-sm-6">
                        <input type="text"
                               name="keyword"
                               class="form-control"
                               placeholder="ស្វែងរកតាមឈ្មោះវិធានការ..."
                               value="{{ request('keyword') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <select name="event_key" class="form-select select2-basic">
                            <option value="">-- គ្រប់ព្រឹត្តិការណ៍ (All Events) --</option>
                            @foreach($categorizedEvents as $catKey => $catData)
                                <optgroup label="{{ $catData['label'] }}">
                                    @foreach($catData['events'] as $evtKey => $evtLabel)
                                        <option value="{{ $evtKey }}" {{ request('event_key') === $evtKey ? 'selected' : '' }}>
                                            {{ $evtLabel }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <select name="is_active" class="form-select select2-basic">
                            <option value="">-- ស្ថានភាព --</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>សកម្ម (Active)</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>អសកម្ម (Inactive)</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-8 d-flex">
                        <button type="submit" class="btn btn-primary me-2 flex-grow-1">
                            <i class="link-icon" data-feather="filter"></i> {{ __('index.filter') }}
                        </button>
                        <a href="{{ route('admin.telegram-rules.index') }}" class="btn btn-outline-secondary">
                            <i class="link-icon" data-feather="refresh-cw"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Rules Table Card --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('index.rule_name') ?? 'ឈ្មោះវិធានការ (Rule Name)' }}</th>
                                <th>{{ __('index.event') ?? 'ព្រឹត្តិការណ៍ (Event)' }}</th>
                                <th>{{ __('index.branch_scope') ?? 'វិសាលភាព (Scope)' }}</th>
                                <th>{{ __('index.target_channels') ?? 'Channels ទទួលសារ' }}</th>
                                <th class="text-center">{{ __('index.options') ?? 'ជម្រើស' }}</th>
                                <th class="text-center">{{ __('index.status') ?? 'ស្ថានភាព' }}</th>
                                <th class="text-center">{{ __('index.action') ?? 'សកម្មភាព' }}</th>
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
                                        <span class="badge bg-primary mb-1">
                                            {{ $rule->event_label }}
                                        </span>
                                        <div class="small text-muted font-monospace" style="font-size: 11px;">
                                            <code>{{ $rule->event_key }}</code>
                                        </div>
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
                                        @endphp
                                        @if(empty($channelIds))
                                            <span class="text-danger small"><i data-feather="alert-triangle"></i> គ្មាន Channel</span>
                                        @else
                                            @foreach(array_slice($channelIds, 0, 2) as $chId)
                                                @php $ch = $channelsById[$chId] ?? null; @endphp
                                                @if($ch)
                                                    <span class="badge bg-light text-dark border mb-1">
                                                        {{ $ch->name }}
                                                        @if($ch->topic_id)
                                                            <small class="text-primary">#{{ $ch->topic_id }}</small>
                                                        @endif
                                                    </span>
                                                @endif
                                            @endforeach
                                            @if(count($channelIds) > 2)
                                                <span class="badge bg-secondary mb-1">+{{ count($channelIds) - 2 }} ផ្សេងទៀត</span>
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
                                            <label class="switch">
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

                <div class="mt-3">
                    {{ $rules->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            if ($(".select2-basic").length) {
                $(".select2-basic").select2({ width: '100%' });
            }

            // Test Rule
            $(document).on('click', '.btnTestRule', function (e) {
                e.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: 'ធ្វើតេស្តវិធានការនេះ?',
                    text: 'ប្រព័ន្ធនឹងផ្ញើសារសាកល្បងទៅកាន់ Channels ទាំងអស់ដែលបានភ្ជាប់ក្នុងវិធានការនេះ។',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'បាទ/ចាស ផ្ញើតេស្ត',
                    cancelButtonText: 'បោះបង់'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });

            // Toggle Rule Status
            $(document).on('change', '.toggleRuleStatus', function (e) {
                e.preventDefault();
                let href = $(this).data('href');
                let checkbox = $(this);
                let isChecked = checkbox.prop('checked');

                Swal.fire({
                    title: 'ផ្លាស់ប្តូរស្ថានភាពវិធានការ?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'យល់ព្រម',
                    cancelButtonText: 'បោះបង់'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    } else {
                        checkbox.prop('checked', !isChecked);
                    }
                });
            });

            // Delete Rule
            $(document).on('click', '.btnDeleteRule', function (e) {
                e.preventDefault();
                let href = $(this).data('href');

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
                        window.location.href = href;
                    }
                });
            });
        });
    </script>
@endsection
