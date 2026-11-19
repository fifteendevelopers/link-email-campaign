@extends('subscribers.layout')

@section('title', $title)

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
            <p class="eyebrow">Email journey</p>
            <h1>{{ $title }}</h1>
            <p class="lede">
                @if ($audience === 'parents')
                    Families on the consent and course journey. Set a send date, then send every email that is due today or earlier.
                @else
                    School contacts on the booking and consent journey. Set a send date, then send every email that is due today or earlier.
                @endif
            </p>
        </div>
        <div class="page-actions">
            <a href="{{ route('email-logs.index') }}" class="btn-logs">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                </svg>
                Email logs
            </a>
            <nav class="audience-switch" aria-label="Subscriber lists">
                <a
                    href="{{ route('subscribers.parents') }}"
                    @class(['audience-link', 'is-current' => $audience === 'parents'])
                    @if ($audience === 'parents') aria-current="page" @endif
                >Parent subscribers</a>
                <a
                    href="{{ route('subscribers.schools') }}"
                    @class(['audience-link', 'is-current' => $audience === 'schools'])
                    @if ($audience === 'schools') aria-current="page" @endif
                >School subscribers</a>
            </nav>
            <button type="button" class="btn-send" id="send-due">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-14.999Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.471 1.178Z"/>
                </svg>
                <span class="js-send-due-label">Send due emails</span>
            </button>
        </div>
    </div>

    <div class="filter-bar">
        <div role="group" aria-label="Delivery status" id="status-filter">
            <button type="button" class="btn btn-primary" data-status="">All</button>
            @foreach ($statuses as $value => $label)
                <button type="button" class="btn btn-outline-primary" data-status="{{ $value }}">{{ $label }}</button>
            @endforeach
        </div>
        <label class="search-field">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            <input
                id="subscriber-search"
                type="search"
                class="form-control"
                placeholder="Search email, delivery ID, consent ID, or child name"
                aria-label="Search email, delivery ID, consent ID, or child name"
                autocomplete="off"
            >
        </label>
    </div>

    <div id="subscriber-rows" aria-live="polite">
        <div class="loading-state">
            <span class="loading-mark" aria-hidden="true"><span class="spinner"></span></span>
            <p>Loading subscribers…</p>
        </div>
    </div>

    <div class="modal fade" id="confirm-send-modal" tabindex="-1" aria-labelledby="confirm-send-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content app-modal">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="confirm-send-title">Send this email?</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="confirm-send-message"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-send">Send now</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="email-modal" tabindex="-1" aria-labelledby="email-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content app-modal">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="email-modal-title">Sent email</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="email-modal-message" class="alert alert-warning d-none"></div>
                    <dl class="email-meta" id="email-meta">
                        <div>
                            <dt>Sent</dt>
                            <dd id="email-date"></dd>
                        </div>
                        <div>
                            <dt>From</dt>
                            <dd id="email-from"></dd>
                        </div>
                        <div>
                            <dt>To</dt>
                            <dd id="email-to"></dd>
                        </div>
                        <div>
                            <dt>Subject</dt>
                            <dd id="email-subject"></dd>
                        </div>
                    </dl>
                    <iframe id="email-frame" class="w-100" title="Sent email preview" sandbox=""></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @php
        $subscriberPage = [
            'noun' => $noun,
            'subscribersBase' => url('/subscribers/'.$audience),
            'rowsBase' => route('subscribers.rows', $audience),
            'sendDueUrl' => route('subscribers.send-due', $audience),
        ];
    @endphp
    <script type="application/json" id="subscriber-page-config">@json($subscriberPage)</script>
    <script src="{{ asset('js/subscribers.js') }}"></script>
@endpush
