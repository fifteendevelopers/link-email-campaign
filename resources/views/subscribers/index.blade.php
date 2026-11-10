@extends('subscribers.layout')

@section('title', $title)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $title }}</h1>
            <p class="text-muted mb-0">Update a send date, then send every email that is due today or earlier.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="btn-group" role="group" aria-label="Subscriber lists">
                <a
                    href="{{ route('subscribers.parents') }}"
                    @class(['btn', 'btn-primary' => $audience === 'parents', 'btn-outline-primary' => $audience !== 'parents'])
                >Parent subscribers</a>
                <a
                    href="{{ route('subscribers.schools') }}"
                    @class(['btn', 'btn-primary' => $audience === 'schools', 'btn-outline-primary' => $audience !== 'schools'])
                >School subscribers</a>
            </div>
            <button type="button" class="btn btn-success" id="send-due">
                Send due emails
            </button>
        </div>
    </div>

    <div id="status-alert" class="alert d-none" role="status"></div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div class="btn-group" role="group" aria-label="Delivery status" id="status-filter">
            <button type="button" class="btn btn-primary" data-status="">All</button>
            @foreach ($statuses as $value => $label)
                <button type="button" class="btn btn-outline-primary" data-status="{{ $value }}">{{ $label }}</button>
            @endforeach
        </div>
        <input
            id="subscriber-search"
            type="search"
            class="form-control"
            style="max-width: 32rem;"
            placeholder="Search email, delivery ID, consent ID, or child name"
            aria-label="Search email, delivery ID, consent ID, or child name"
            autocomplete="off"
        >
    </div>

    <div id="subscriber-rows" aria-live="polite">
        <div class="text-muted">Loading subscribers…</div>
    </div>

    <div class="modal fade" id="confirm-send-modal" tabindex="-1" aria-labelledby="confirm-send-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
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
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="email-modal-title">Sent email</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="email-modal-message" class="alert alert-warning d-none"></div>
                    <dl class="row mb-3" id="email-meta">
                        <dt class="col-sm-2">Sent</dt>
                        <dd class="col-sm-10" id="email-date"></dd>
                        <dt class="col-sm-2">From</dt>
                        <dd class="col-sm-10" id="email-from"></dd>
                        <dt class="col-sm-2">To</dt>
                        <dd class="col-sm-10" id="email-to"></dd>
                        <dt class="col-sm-2">Subject</dt>
                        <dd class="col-sm-10" id="email-subject"></dd>
                    </dl>
                    <iframe id="email-frame" class="w-100 border rounded" style="height: 70vh;" title="Sent email preview" sandbox=""></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const audience = @json($audience);
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

        $('#subscriber-rows').on('click', '.js-open-date', function () {
            const input = $(this).closest('.input-group').find('.js-send-date').get(0);

            if (input && input._flatpickr) {
                input._flatpickr.open();
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

        $('#subscriber-rows').on('click', '.js-send-one', function () {
            const $button = $(this);
            pendingSend = {
                button: $button,
                subscriberId: $button.data('subscriber'),
                sentColumn: $button.data('sent-column'),
            };

            $('#confirm-send-message').text(
                'Send the ' + $button.data('label') + ' email to ' + $button.data('email') + ' now?'
            );
            confirmSendModal.show();
        });

        $('#confirm-send').on('click', function () {
            if (!pendingSend) {
                return;
            }

            const send = pendingSend;
            const $confirm = $(this);
            pendingSend = null;
            send.button.prop('disabled', true);
            $confirm.prop('disabled', true);

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
                    $confirm.prop('disabled', false);
                });
        });

        $('#confirm-send-modal').on('hidden.bs.modal', function () {
            pendingSend = null;
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

        $('#send-due').on('click', function () {
            if (!window.confirm('Send every due ' + @json($noun) + ' email now? Emails that have already been sent are skipped.')) {
                return;
            }

            const $button = $(this);
            const originalText = $button.text();
            $button.prop('disabled', true).text('Sending…');

            $.post(@json(route('subscribers.send-due', $audience)))
                .done((response) => {
                    showStatus(response.message, 'success');
                })
                .fail((xhr) => {
                    const message = xhr.responseJSON?.message || 'The send could not be completed.';
                    showStatus(message, 'danger');
                })
                .always(() => {
                    $button.prop('disabled', false).text(originalText);
                    loadRows(rowsUrl);
                });
        });

        loadRows(filteredRowsUrl());
    </script>
@endpush
