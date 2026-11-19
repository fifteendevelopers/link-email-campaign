const emailLogPage = JSON.parse(document.getElementById('email-log-page-config').textContent);
const rowsBase = emailLogPage.rowsBase;
let rowsUrl = rowsBase;
let searchTerm = '';

const filteredRowsUrl = () => {
    const url = new URL(rowsBase, window.location.origin);

    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    }

    return url.pathname + url.search;
};

const loadRows = (url) => {
    const $rows = $('#email-log-rows');
    $rows.css('opacity', '0.55');

    $.get(url)
        .done((html) => {
            $rows.html(html).css('opacity', '1');
            rowsUrl = url;
            const summary = $rows.children().first().attr('data-summary');

            if (summary) {
                $('#email-log-summary').text(summary);
            }
        })
        .fail(() => {
            $rows.css('opacity', '1');
            showStatus('The email logs could not be loaded.', 'danger');
        });
};

let searchTimer = null;
$('#email-log-search').on('input', function () {
    const value = $(this).val();
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
        searchTerm = String(value).trim();
        loadRows(filteredRowsUrl());
    }, 300);
});

$('#email-log-rows').on('click', '.pagination a', function (event) {
    event.preventDefault();
    const href = $(this).attr('href');

    if (!href) {
        return;
    }

    loadRows(href);
});

loadRows(filteredRowsUrl());
