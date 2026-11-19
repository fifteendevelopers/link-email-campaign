const dismissToast = ($toast) => {
    if (!$toast || !$toast.length || $toast.hasClass('is-leaving')) {
        return;
    }

    window.clearTimeout($toast.data('timer'));
    $toast.addClass('is-leaving');
    window.setTimeout(() => $toast.remove(), 180);
};

const armToast = ($toast) => {
    window.clearTimeout($toast.data('timer'));
    $toast.data('timer', window.setTimeout(() => dismissToast($toast), 2000));
};

const showStatus = (message, tone, options) => {
    const settings = options || {};
    const $toast = $(`
        <div class="app-toast" role="status">
            <p class="toast-message"></p>
            <button type="button" class="toast-close" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </div>
    `);

    $toast.addClass('is-' + (tone || 'success'));
    $toast.find('.toast-message').text(message);
    $('#toast-stack').append($toast);

    if (!settings.persistent) {
        armToast($toast);
    }

    return $toast;
};

const updateToast = ($toast, message, tone) => {
    if (!$toast || !$toast.length || !$.contains(document, $toast.get(0))) {
        return showStatus(message, tone);
    }

    $toast.removeClass('is-success is-danger is-warning is-leaving').addClass('is-' + tone);
    $toast.find('.toast-message').text(message);
    armToast($toast);

    return $toast;
};

$('#toast-stack').on('click', '.toast-close', function () {
    dismissToast($(this).closest('.app-toast'));
});
