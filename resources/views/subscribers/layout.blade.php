<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ config('app.name', 'Email campaign') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --faint: #94a3b8;
            --line: #e6ebf2;
            --line-strong: #d7e0ea;
            --surface: #f3f6fb;
            --card: #ffffff;
            --accent: #0f766e;
            --accent-deep: #115e59;
            --accent-soft: #f0fdfa;
            --shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 16px 40px rgba(15, 23, 42, 0.06);
            --radius: 18px;
        }

        body.app-body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            font-family: "DM Sans", "Segoe UI", sans-serif;
            background:
                radial-gradient(1100px 420px at 0% -10%, rgba(15, 118, 110, 0.09), transparent 55%),
                radial-gradient(900px 360px at 100% 0%, rgba(14, 116, 144, 0.07), transparent 50%),
                var(--surface);
        }

        .app-shell {
            width: min(1680px, calc(100% - 2.5rem));
            margin: 0 auto;
            padding: 1.5rem 0 3rem;
        }

        .app-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.9rem;
            background: linear-gradient(160deg, #14b8a6, var(--accent-deep));
            color: #fff;
            box-shadow: 0 10px 20px rgba(15, 118, 110, 0.25);
            flex: none;
        }

        .brand-name {
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .brand-sub {
            margin-top: 0.15rem;
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .page-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: end;
            gap: 1.25rem 1.5rem;
            margin-bottom: 1.15rem;
        }

        .eyebrow {
            margin: 0 0 0.35rem;
            color: var(--accent);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .page-head h1 {
            margin: 0;
            font-size: clamp(1.7rem, 2vw, 2.15rem);
            font-weight: 700;
            letter-spacing: -0.04em;
            line-height: 1.1;
        }

        .lede {
            max-width: 40rem;
            margin: 0.55rem 0 0;
            color: var(--muted);
            font-size: 0.98rem;
            line-height: 1.5;
        }

        .page-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
        }

        .journey-stat {
            display: inline-flex;
            align-items: baseline;
            gap: 0.35rem;
            padding: 0.55rem 0.8rem;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.86);
        }

        .journey-stat strong {
            font-size: 0.95rem;
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .journey-stat span {
            color: var(--muted);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .audience-switch {
            display: inline-flex;
            padding: 0.25rem;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .audience-link {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            color: #475569;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1;
            padding: 0.72rem 0.95rem;
            text-decoration: none;
        }

        .audience-link:hover {
            color: var(--ink);
            background: #f8fafc;
        }

        .audience-link.is-current {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 8px 16px rgba(15, 118, 110, 0.22);
        }

        .audience-link:focus-visible,
        .btn-send:focus-visible,
        #status-filter .btn:focus-visible,
        .send-chip:focus-visible,
        .sent-chip:focus-visible,
        .date-open:focus-visible,
        .search-field:focus-within {
            outline: none;
            box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.16);
        }

        .btn-send {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 0;
            border-radius: 999px;
            background: var(--ink);
            color: #fff;
            font-weight: 700;
            line-height: 1;
            padding: 0.78rem 1.05rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
        }

        .btn-send:hover {
            background: #1e293b;
            color: #fff;
        }

        .btn-send:disabled {
            opacity: 0.65;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: rgba(255, 255, 255, 0.92);
            box-shadow: var(--shadow);
        }

        #status-filter {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 0.2rem;
            padding: 0.22rem;
            border-radius: 999px;
            background: #eef2f6;
        }

        #status-filter .btn {
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #475569;
            font-size: 0.84rem;
            font-weight: 700;
            padding: 0.48rem 0.9rem;
            box-shadow: none;
        }

        #status-filter .btn:hover {
            background: rgba(255, 255, 255, 0.75);
            color: var(--ink);
        }

        #status-filter .btn.btn-primary {
            background: var(--ink);
            color: #fff;
        }

        #status-filter .btn.btn-outline-primary {
            background: transparent;
            color: #475569;
        }

        .search-field {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            width: min(100%, 32rem);
            margin-left: auto;
            padding: 0.15rem 0.9rem;
            border: 1px solid var(--line-strong);
            border-radius: 999px;
            background: #f8fafc;
            color: var(--faint);
        }

        .search-field .form-control {
            border: 0;
            background: transparent;
            color: var(--ink);
            font-weight: 500;
            padding-left: 0;
            box-shadow: none;
        }

        .search-field .form-control::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        #status-alert.alert {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 1080;
            width: min(24rem, calc(100vw - 2rem));
            margin: 0;
            border: 0;
            border-radius: 16px;
            font-weight: 600;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.16);
        }

        #status-alert.alert-success {
            background: #ecfdf5;
            color: #065f46;
        }

        #status-alert.alert-danger {
            background: #fff1f2;
            color: #9f1239;
        }

        #status-alert.alert-warning {
            background: #fffbeb;
            color: #92400e;
        }

        #subscriber-rows {
            transition: opacity 0.15s ease;
        }

        .loading-state,
        .empty-state {
            display: grid;
            justify-items: center;
            gap: 0.45rem;
            padding: 3.5rem 1.5rem;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #fff;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .empty-mark,
        .loading-mark {
            display: grid;
            place-items: center;
            width: 3rem;
            height: 3rem;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent-deep);
        }

        .empty-title,
        .loading-state p,
        .empty-state p {
            margin: 0;
        }

        .empty-title {
            font-weight: 700;
        }

        .empty-copy {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .spinner {
            width: 1.1rem;
            height: 1.1rem;
            border: 2px solid rgba(15, 118, 110, 0.2);
            border-top-color: var(--accent);
            border-radius: 999px;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .table-shell {
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #fff;
            box-shadow: var(--shadow);
        }

        .table-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1.05rem;
            border-bottom: 1px solid #eef2f7;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .table-scroll {
            max-height: calc(100vh - 17.5rem);
            overflow: auto;
        }

        .subscriber-table {
            --sticky-subscriber-width: 0px;
            width: max-content;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .subscriber-table thead th {
            position: sticky;
            top: 0;
            z-index: 4;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--line);
            background: #f8fafc;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            vertical-align: bottom;
            white-space: nowrap;
        }

        .subscriber-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #eef2f7;
            background: #fff;
            vertical-align: top;
        }

        .subscriber-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .subscriber-table tbody tr:hover td {
            background: #f8fafc;
        }

        .subscriber-table .sticky-start,
        .subscriber-table .sticky-course {
            position: sticky;
            z-index: 2;
        }

        .subscriber-table thead .sticky-start,
        .subscriber-table thead .sticky-course {
            z-index: 6;
            background: #f8fafc;
        }

        .subscriber-table .sticky-start {
            left: 0;
            width: 18.5rem;
            min-width: 18.5rem;
            max-width: 18.5rem;
        }

        .subscriber-table .sticky-course {
            left: var(--sticky-subscriber-width);
            width: 13.5rem;
            min-width: 13.5rem;
            max-width: 13.5rem;
        }

        .subscriber-table .sticky-edge {
            box-shadow: 8px 0 16px -14px rgba(15, 23, 42, 0.55);
        }

        .comm-index {
            display: block;
            margin-bottom: 0.2rem;
            color: var(--accent);
            letter-spacing: 0.08em;
        }

        .subscriber-card {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            min-width: 0;
        }

        .subscriber-identity {
            min-width: 0;
        }

        .avatar {
            display: grid;
            place-items: center;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 999px;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            flex: none;
        }

        .subscriber-email {
            overflow: hidden;
            color: var(--ink);
            font-weight: 700;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .subscriber-name,
        .subscriber-place {
            color: var(--muted);
            font-size: 0.8rem;
            line-height: 1.35;
        }

        .subscriber-place {
            color: #334155;
            font-weight: 600;
        }

        .meta-list {
            display: grid;
            gap: 0.35rem;
            margin: 0.85rem 0 0;
        }

        .course-cell .meta-list {
            margin-top: 0;
        }

        .meta-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
            color: var(--ink);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .meta-label {
            flex: none;
            color: var(--faint);
            font-weight: 700;
        }

        .sent-meter {
            margin-top: 0.9rem;
        }

        .sent-meter-track {
            height: 0.35rem;
            overflow: hidden;
            border-radius: 999px;
            background: #e2e8f0;
        }

        .sent-meter-fill {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #14b8a6, var(--accent));
        }

        .sent-meter-label {
            margin-top: 0.35rem;
            color: var(--muted);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .status-field {
            margin-top: 0.85rem;
        }

        .field-label {
            display: block;
            margin-bottom: 0.32rem;
            color: var(--faint);
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .status-field .js-delivery-status {
            width: 100%;
            max-width: 12rem;
            border: 1px solid var(--line-strong);
            border-radius: 999px;
            background-color: #fff;
            color: var(--ink);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.38rem 2rem 0.38rem 0.8rem;
        }

        .js-delivery-status.status-active {
            color: #fff;
            background-color: #047857;
            border-color: #065f46;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        }

        .js-delivery-status.status-removed {
            color: #fff;
            background-color: #be123c;
            border-color: #9f1239;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        }

        .js-delivery-status option.status-active {
            color: #fff;
            background-color: #047857;
        }

        .js-delivery-status option.status-removed {
            color: #fff;
            background-color: #be123c;
        }

        .comm-panel {
            display: flex;
            align-items: flex-end;
            gap: 0.75rem;
            min-width: 17.75rem;
            padding: 0.7rem;
            border: 1px solid #e8eef5;
            border-radius: 14px;
            background: #f8fafc;
        }

        .date-field {
            width: 10.75rem;
            overflow: hidden;
            border: 1px solid var(--line-strong);
            border-radius: 12px;
            background: #fff;
            flex-wrap: nowrap;
        }

        .date-field .form-control {
            border: 0;
            background: #fff;
            color: var(--ink);
            font-size: 0.82rem;
            font-weight: 600;
            box-shadow: none;
        }

        .date-open {
            display: grid;
            place-items: center;
            width: 2.15rem;
            border: 0;
            border-left: 1px solid #eef2f6;
            background: #fff;
            color: #64748b;
        }

        .date-open:hover,
        .send-chip:hover {
            background: var(--accent);
            color: #fff;
        }

        .sent-row {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            min-height: 2.05rem;
        }

        .sent-chip,
        .unsent-pill,
        .send-chip {
            border: 0;
            line-height: 1;
        }

        .sent-chip,
        .unsent-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.48rem 0.7rem;
            white-space: nowrap;
        }

        .sent-chip {
            background: #d1fae5;
            color: #065f46;
        }

        .sent-chip:hover {
            background: #a7f3d0;
            color: #064e3b;
        }

        .unsent-pill {
            background: #e2e8f0;
            color: #475569;
        }

        .send-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.05rem;
            height: 2.05rem;
            border: 1px solid #99f6e4;
            border-radius: 999px;
            background: #fff;
            color: var(--accent-deep);
        }

        .send-chip:disabled,
        .sent-chip:disabled {
            opacity: 0.6;
        }

        .pager {
            display: flex;
            justify-content: flex-end;
            padding: 0.85rem 1rem 1rem;
        }

        .pager .pagination {
            gap: 0.25rem;
            margin: 0;
        }

        .pager .page-link {
            min-width: 2.3rem;
            border: 0;
            border-radius: 10px;
            color: #334155;
            font-weight: 700;
            text-align: center;
        }

        .pager .page-link:hover {
            background: #f1f5f9;
            color: var(--ink);
        }

        .pager .page-item.active .page-link {
            background: var(--ink);
            color: #fff;
        }

        .pager .page-item.disabled .page-link {
            background: transparent;
            color: #cbd5e1;
        }

        .app-modal {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.22);
        }

        .app-modal .modal-header,
        .app-modal .modal-footer {
            padding: 1.15rem 1.35rem;
            border-color: #eef2f7;
        }

        .app-modal .modal-title {
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .app-modal .modal-body {
            padding: 1.2rem 1.35rem 1.35rem;
            color: #334155;
        }

        .app-modal .btn {
            border-radius: 999px;
            font-weight: 700;
            padding: 0.5rem 0.95rem;
        }

        .app-modal .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
        }

        .app-modal .btn-primary:hover {
            background: var(--accent-deep);
            border-color: var(--accent-deep);
        }

        .email-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin: 0 0 1rem;
        }

        .email-meta div {
            padding: 0.75rem 0.85rem;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #f8fafc;
        }

        .email-meta dt {
            margin-bottom: 0.15rem;
            color: var(--faint);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .email-meta dd {
            margin: 0;
            color: var(--ink);
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        #email-frame {
            height: 68vh;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
        }

        .flatpickr-calendar {
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.16);
            font-family: "DM Sans", "Segoe UI", sans-serif;
        }

        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background: var(--accent);
            border-color: var(--accent);
        }

        .flatpickr-months .flatpickr-month,
        .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-current-month input.cur-year {
            font-weight: 700;
        }

        @media (max-width: 1120px) {
            .page-head {
                grid-template-columns: 1fr;
            }

            .page-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 860px) {
            .app-shell {
                width: min(100% - 1.25rem, 1680px);
            }

            .app-bar,
            .page-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .page-head {
                grid-template-columns: 1fr;
            }

            .search-field {
                margin-left: 0;
            }

            .email-meta {
                grid-template-columns: 1fr;
            }

            .table-scroll {
                max-height: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .spinner,
            #subscriber-rows {
                animation: none;
                transition: none;
            }
        }
    </style>
</head>
<body class="app-body">
    <main class="app-shell">
        @yield('content')
    </main>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    @stack('scripts')
</body>
</html>
