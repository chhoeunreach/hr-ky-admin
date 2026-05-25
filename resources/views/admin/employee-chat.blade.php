@extends('layouts.master')

@section('title', 'Employee Chat')

@section('action', 'Employee Chat')

@section('nav-head', 'Employee Chat')

@section('main-content')
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="mb-1">Employee Chat</h4>
                            <p class="text-muted mb-0">Access the employee chat workspace from the admin panel.</p>
                        </div>
                        @if(!empty($chatUrl))
                            <a href="{{ $chatUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                Open Chat
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(!empty($chatUrl))
                            <div class="alert alert-info">
                                Chat is configured and ready. Use the button above if you prefer opening it in a separate tab.
                            </div>
                            <div class="ratio ratio-16x9 border rounded overflow-hidden">
                                <iframe src="{{ $chatUrl }}" title="Employee Chat" class="w-100 h-100 border-0"></iframe>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                Chat menu is ready, but no chat URL is configured yet.
                                Add <strong>`MOBILE_APP_URL`</strong> to your <strong>`.env`</strong> file to launch the chat workspace here.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
