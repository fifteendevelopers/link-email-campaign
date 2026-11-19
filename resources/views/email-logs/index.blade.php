@extends('subscribers.layout')

@section('title', 'Email logs')

@section('content')
    <header class="app-bar">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                </svg>
            </span>
            <div>
                <div class="brand-name">{{ str_replace(['-', '_'], ' ', config('app.name', 'Email campaign')) }}</div>
                <div class="brand-sub">Subscriber campaigns</div>
            </div>
        </div>
    </header>

    <div class="page-head">
        <div>
            <p class="eyebrow">Delivery history</p>
            <h1>Email logs</h1>
            <p class="lede">Every email sent from the parent and school journeys.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('subscribers.parents') }}" class="btn-subscribers">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                </svg>
                Subscriber Lists
            </a>
        </div>
    </div>

    <div class="filter-bar">
        <label class="search-field">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            <input
                id="email-log-search"
                type="search"
                class="form-control"
                placeholder="Search email, delivery ID, consent ID, or course level"
                aria-label="Search email, delivery ID, consent ID, or course level"
                autocomplete="off"
            >
        </label>
    </div>

    <div class="table-shell">
        <div class="table-meta">
            <span id="email-log-summary">Email logs</span>
        </div>
        <div id="email-log-rows" aria-live="polite">
            <div class="loading-state">
                <span class="loading-mark" aria-hidden="true"><span class="spinner"></span></span>
                <p>Loading email logs…</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @php
        $emailLogPage = [
            'rowsBase' => route('email-logs.rows'),
        ];
    @endphp
    <script type="application/json" id="email-log-page-config">@json($emailLogPage)</script>
    <script src="{{ asset('js/email-logs.js') }}"></script>
@endpush
