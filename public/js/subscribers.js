const subscriberPage = JSON.parse(document.getElementById('subscriber-page-config').textContent);
const noun = subscriberPage.noun;
const subscribersBase = subscriberPage.subscribersBase;
const rowsBase = subscriberPage.rowsBase;
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
    pendingSend = null;
    confirmSendModal.hide();
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
    document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());

    const $toast = showStatus('Email is sending...', 'warning', { persistent: true });

    if (send.bulk) {
        const $button = send.button;
        const $label = $button.find('.js-send-due-label');
        const originalText = $label.text();
        $button.prop('disabled', true);
        $label.text('Sending…');

        $.post(subscriberPage.sendDueUrl)
            .done((response) => {
                updateToast($toast, response.message, 'success');
            })
            .fail((xhr) => {
                const message = xhr.responseJSON?.message || 'The send could not be completed.';
                updateToast($toast, message, 'danger');
            })
            .always(() => {
                $button.prop('disabled', false);
                $label.text(originalText);
                loadRows(rowsUrl);
            });

        return;
    }

    send.button.prop('disabled', true);

    $.post(subscribersBase + '/' + send.subscriberId + '/emails/' + send.sentColumn)
        .done((response) => {
            updateToast($toast, response.message, 'success');
            loadRows(rowsUrl);
        })
        .fail((xhr) => {
            const message = xhr.responseJSON?.message || 'The email could not be sent.';
            updateToast($toast, message, 'danger');
            send.button.prop('disabled', false);
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
