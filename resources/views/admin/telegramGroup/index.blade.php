@extends('layouts.master')

@section('title', 'Telegram Groups')

@section('button')
    @can('create_telegram_group')
        <a href="{{ route('admin.telegram-groups.create') }}">
            <button class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> Add Telegram Group</button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.telegramGroup.common.breadcrumb', ['title' => 'Telegram Groups'])

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Telegram Group Filters</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{ route('admin.telegram-groups.index') }}" method="get">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <input type="text" placeholder="Search by group name" name="name" value="{{ $filterParameters['name'] }}" class="form-control">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="action_key">
                            <option value="">All Actions</option>
                            @foreach($actionOptions as $key => $label)
                                <option value="{{ $key }}" {{ $filterParameters['action_key'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select" name="is_active">
                            <option value="">All Status</option>
                            <option value="1" {{ (string) $filterParameters['is_active'] === '1' ? 'selected' : '' }}>{{ __('index.active') }}</option>
                            <option value="0" {{ (string) $filterParameters['is_active'] === '0' ? 'selected' : '' }}>{{ __('index.inactive') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select" name="per_page">
                            <option value="10" {{ (string) $filterParameters['per_page'] === '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ (string) $filterParameters['per_page'] === '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ (string) $filterParameters['per_page'] === '50' ? 'selected' : '' }}>50</option>
                            <option value="all" {{ (string) $filterParameters['per_page'] === 'all' ? 'selected' : '' }}>All</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 d-flex">
                        <button type="submit" class="btn btn-success me-2 mb-4">{{ __('index.filter') }}</button>
                        <a class="btn btn-primary mb-4" href="{{ route('admin.telegram-groups.index') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Telegram Groups</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
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
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['edit_telegram_group','delete_telegram_group','test_telegram_group'])
                                <th class="text-center">{{ __('index.action') }}</th>
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
                                </td>
                                <td>
                                    @php $actionKeys = $telegramGroup->action_keys ?: [$telegramGroup->action_key]; @endphp
                                    @foreach(array_filter($actionKeys) as $actionKey)
                                        <span class="badge bg-info mb-1">{{ $actionOptions[$actionKey] ?? $actionKey }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @php $eventKeys = $telegramGroup->event_keys ?: [$telegramGroup->action_key]; @endphp
                                    @foreach($eventKeys as $eventKey)
                                        <span class="badge bg-secondary mb-1">{{ $actionOptions[$eventKey] ?? \App\Models\TelegramGroup::eventOptions()[$eventKey] ?? $eventKey }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $telegramGroup->branch_name ?: ($telegramGroup->branch?->name ?? 'All') }}</td>
                                <td>{{ $telegramGroup->department_name ?: ($telegramGroup->department?->dept_name ?? 'All') }}</td>
                                <td class="text-center">{{ $telegramGroup->send_for_all ? 'Yes' : 'No' }}</td>
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
                                                <li class="me-2">
                                                    <a class="btn btn-sm btn-outline-primary sendTelegramTest"
                                                       data-href="{{ route('admin.telegram-groups.test', $telegramGroup->id) }}"
                                                       href="javascript:void(0)">
                                                        <i class="link-icon" data-feather="send"></i> Send Test
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('edit_telegram_group')
                                                <li class="me-2">
                                                    <a href="{{ route('admin.telegram-groups.edit', $telegramGroup->id) }}">
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

        <div class="dataTables_paginate">
            {{ $telegramGroups->appends($_GET)->links() }}
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.toggleStatus').change(function (event) {
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

            $('.deleteTelegramGroup').click(function (event) {
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

            $('.sendTelegramTest').click(function (event) {
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
        });
    </script>
@endsection
