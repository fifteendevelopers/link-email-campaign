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
            <div class="journey-stat">
                <strong>{{ $communicationCount }}</strong>
                <span>emails in this journey</span>
            </div>
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

    <div id="status-alert" class="alert d-none" role="status"></div>

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
    <script>
        const audience = @json($audience);
        const noun = @json($noun);
        const subscribersBase = @json(url('/subscribers/'.$audience));
        const rowsBase = @json(route('subscribers.rows', $audience));
        let rowsUrl = rowsBase;
        let statusFilter = '';
        let searchTerm = '';

        const filteredRowsUrl = () => {
            const url = new URL(rowsBase, window.location.origin);

            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else if (statusFilter) {
                url.searchParams.set('status', statusFilter);
            }

            return url.pathname + url.search;
        };

        const markStatusFilter = () => {
            $('#status-filter button').each(function () {
                const status = $(this).attr('data-status') ?? '';
                const selected = status === statusFilter;

                $(this)
                    .toggleClass('btn-primary', selected)
                    .toggleClass('btn-outline-primary', !selected)
                    .toggleClass('active', selected);
            });
        };

        const paintStatusSelect = ($select) => {
            const value = String($select.val() || '');

            $select
                .toggleClass('status-active', value === 'active')
                .toggleClass('status-removed', value === 'deleted' || value === 'consent removed');
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });

        const showStatus = (message, tone) => {
            $('#status-alert')
                .removeClass('d-none alert-success alert-danger alert-warning')
                .addClass('alert-' + tone)
                .text(message);
        };

        const pinStickyColumns = () => {
            const table = document.querySelector('#subscriber-rows .subscriber-table');

            if (!table) {
                return;
            }

            const header = table.querySelector('thead .sticky-start');

            if (!header) {
                return;
            }

            table.style.setProperty('--sticky-subscriber-width', `${header.offsetWidth}px`);
        };

        const loadRows = (url) => {
            const $rows = $('#subscriber-rows');
            $rows.css('opacity', '0.55');

            $.get(url)
                .done((html) => {
                    $rows.html(html).css('opacity', '1');
                    rowsUrl = url;
                    initSendDates();
                    $rows.find('.js-delivery-status').each(function () {
                        paintStatusSelect($(this));
                    });
                    pinStickyColumns();
                })
                .fail(() => {
                    $rows.css('opacity', '1');
                    showStatus('The subscriber list could not be loaded.', 'danger');
                });
        };

        $('#status-filter').on('click', 'button', function () {
            statusFilter = $(this).attr('data-status') || '';
            markStatusFilter();
            loadRows(filteredRowsUrl());
        });

        let searchTimer = null;
        $('#subscriber-search').on('input', function () {
            const value = $(this).val();
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => {
                searchTerm = String(value).trim();
                loadRows(filteredRowsUrl());
            }, 300);
        });

        $('#subscriber-rows').on('change', '.js-delivery-status', function () {
            const $select = $(this);
            const previous = $select.data('current');

            paintStatusSelect($select);
            $select.prop('disabled', true);

            $.ajax({
                url: subscribersBase + '/' + $select.data('subscriber') + '/status',
                method: 'PATCH',
                data: { status: $select.val() },
            }).done((response) => {
                showStatus(response.message, 'success');
                loadRows(rowsUrl);
            }).fail((xhr) => {
                $select.val(previous);
                paintStatusSelect($select);
                const message = xhr.responseJSON?.message || 'The delivery status could not be saved.';
                showStatus(message, 'danger');
            }).always(() => {
                $select.prop('disabled', false);
            });
        });

        $('#subscriber-rows').on('click', '.pagination a', function (event) {
            event.preventDefault();
            const href = $(this).attr('href');

            if (!href) {
                return;
            }

            loadRows(href);
        });

        const initSendDates = () => {
            document.querySelectorAll('.js-send-date').forEach((input) => {
                if (input._flatpickr) {
                    return;
                }

                flatpickr(input, {
                    dateFormat: 'd/m/Y',
                    allowInput: false,
                    clickOpens: true,
                    disableMobile: true,
                });
            });
        };

        $('#subscriber-rows').on('mousedown click', '.js-open-date', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (event.type !== 'click') {
                return;
            }

            const input = $(this).closest('.input-group').find('.js-send-date').get(0);

            if (input && input._flatpickr) {
                input._flatpickr.toggle();
            }
        });

        $('#subscriber-rows').on('keydown paste', '.js-send-date', function (event) {
            event.preventDefault();
        });

        $('#subscriber-rows').on('change', '.js-send-date', function () {
            const $input = $(this);
            const subscriberId = $input.data('subscriber');

            $input.prop('disabled', true);

            $.ajax({
                url: subscribersBase + '/' + subscriberId + '/send-date',
                method: 'PATCH',
                data: {
                    column: $input.data('column'),
                    value: $input.val(),
                },
            }).done((response) => {
                showStatus(response.message, 'success');
            }).fail((xhr) => {
                const message = xhr.responseJSON?.message || 'The send date could not be saved.';
                showStatus(message, 'danger');
            }).always(() => {
                $input.prop('disabled', false);
            });
        });

        let pendingSend = null;
        const confirmSendModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirm-send-modal'));

        const openConfirm = ({ title, message, confirmLabel, payload }) => {
            pendingSend = payload;
            $('#confirm-send-title').text(title);
            $('#confirm-send-message').text(message);
            $('#confirm-send').text(confirmLabel);
            confirmSendModal.show();
        };

        $('#subscriber-rows').on('click', '.js-send-one', function () {
            const $button = $(this);

            openConfirm({
                title: 'Send this email?',
                message: 'Send the ' + $button.data('label') + ' email to ' + $button.data('email') + ' now?',
                confirmLabel: 'Send now',
                payload: {
                    bulk: false,
                    button: $button,
                    subscriberId: $button.data('subscriber'),
                    sentColumn: $button.data('sent-column'),
                },
            });
        });

        $('#send-due').on('click', function () {
            openConfirm({
                title: 'Send due emails?',
                message: 'Send every due ' + noun + ' email now? Emails that have already been sent are skipped.',
                confirmLabel: 'Send due emails',
                payload: {
                    bulk: true,
                    button: $(this),
                },
            });
        });

        $('#confirm-send').on('click', function () {
            if (!pendingSend) {
                return;
            }

            const send = pendingSend;
            const $confirm = $(this);
            pendingSend = null;
            $confirm.prop('disabled', true).text('Sending…');

            if (send.bulk) {
                const $button = send.button;
                const $label = $button.find('.js-send-due-label');
                const originalText = $label.text();
                $button.prop('disabled', true);
                $label.text('Sending…');

                $.post(@json(route('subscribers.send-due', $audience)))
                    .done((response) => {
                        confirmSendModal.hide();
                        showStatus(response.message, 'success');
                    })
                    .fail((xhr) => {
                        const message = xhr.responseJSON?.message || 'The send could not be completed.';
                        confirmSendModal.hide();
                        showStatus(message, 'danger');
                    })
                    .always(() => {
                        $button.prop('disabled', false);
                        $label.text(originalText);
                        $confirm.prop('disabled', false).text('Send now');
                        loadRows(rowsUrl);
                    });

                return;
            }

            send.button.prop('disabled', true);

            $.post(subscribersBase + '/' + send.subscriberId + '/emails/' + send.sentColumn)
                .done((response) => {
                    confirmSendModal.hide();
                    showStatus(response.message, 'success');
                    loadRows(rowsUrl);
                })
                .fail((xhr) => {
                    const message = xhr.responseJSON?.message || 'The email could not be sent.';
                    confirmSendModal.hide();
                    showStatus(message, 'danger');
                    send.button.prop('disabled', false);
                })
                .always(() => {
                    $confirm.prop('disabled', false).text('Send now');
                });
        });

        $('#confirm-send-modal').on('hidden.bs.modal', function () {
            pendingSend = null;
            $('#confirm-send').prop('disabled', false).text('Send now');
            $('#confirm-send-title').text('Send this email?');
        });

        $('#subscriber-rows').on('click', '.js-view-email', function () {
            const $button = $(this);
            const subscriberId = $button.data('subscriber');
            const sentColumn = $button.data('sent-column');
            const modalElement = document.getElementById('email-modal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            const frame = document.getElementById('email-frame');

            $('#email-modal-title').text($button.data('label') + ' email');
            $('#email-modal-message').addClass('d-none').text('');
            $('#email-meta').removeClass('d-none');
            $('#email-date, #email-from, #email-to, #email-subject').text('Loading…');
            frame.srcdoc = '';
            modal.show();

            $.get(subscribersBase + '/' + subscriberId + '/emails/' + sentColumn)
                .done((email) => {
                    $('#email-date').text(email.date || '');
                    $('#email-from').text(email.from || '');
                    $('#email-to').text(email.to || '');
                    $('#email-subject').text(email.subject || '');
                    frame.srcdoc = email.body || '';
                })
                .fail((xhr) => {
                    const message = xhr.responseJSON?.message || 'The email could not be loaded.';
                    $('#email-meta').addClass('d-none');
                    $('#email-modal-message').removeClass('d-none').text(message);
                });
        });

        $(window).on('resize', pinStickyColumns);

        loadRows(filteredRowsUrl());
    </script>
@endpush
