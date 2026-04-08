@extends('layouts.master')

@section('title', 'Clients')

@section('action', __('index.client_listing'))

@section('button')
    @can('create_client')
        <a href="{{ route('admin.clients.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_client') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.client.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.client_name') }}</th>
                            <th>{{ __('index.client_email') }}</th>
                            <th>{{ __('index.contact') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['show_client_detail','edit_client','delete_client'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($clientLists as $key => $value)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ ucfirst($value->name) }}</td>
                                <td>{{ $value->email }}</td>
                                <td>{{ $value->contact_no }}</td>

                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus"
                                               href="{{ route('admin.clients.toggle-status', $value->id) }}"
                                               type="checkbox" {{ $value->is_active == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['show_client_detail','edit_client','delete_client'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center align-items-center">
                                            @can('edit_client')
                                                <li class="me-2">
                                                    <a href="{{ route('admin.clients.edit', $value->id) }}"
                                                       title="{{ __('index.edit_client_detail') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_client_detail')
                                                <li class="me-2">
                                                    <a href="{{ route('admin.clients.show', $value->id) }}"
                                                       title="{{ __('index.show_client_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_client')
                                                <li>
                                                    <a class="deleteClientDetail"
                                                       data-href="{{ route('admin.clients.delete', $value->id) }}"
                                                       title="{{ __('index.delete_client_detail') }}">
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
    </section>
@endsection

@section('scripts')
    @include('admin.client.common.scripts')
@endsection
