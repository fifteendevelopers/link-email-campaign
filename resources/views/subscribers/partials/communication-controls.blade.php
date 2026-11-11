@if ($showDate)
    <div>
        <label class="form-label small text-muted mb-1" for="date-{{ $subscriber->id }}-{{ $track['date'] }}">Send date</label>
        <div class="input-group input-group-sm" style="width: 11rem;">
            <input
                id="date-{{ $subscriber->id }}-{{ $track['date'] }}"
                type="text"
                readonly
                placeholder="dd/mm/yyyy"
                class="form-control js-send-date"
                style="cursor: pointer; background-color: #fff;"
                value="{{ $subscriber->getAttribute($track['date'])?->format('d/m/Y') }}"
                data-subscriber="{{ $subscriber->id }}"
                data-column="{{ $track['date'] }}"
                aria-label="{{ $track['label'] }} send date"
                title="Choose a date"
            >
            <button
                type="button"
                class="btn btn-outline-secondary js-open-date"
                aria-label="Open {{ $track['label'] }} calendar"
            >&#9662;</button>
        </div>
    </div>
@endif
@if ($showSent)
    @php
        $sentAt = $subscriber->getAttribute($track['sent']);
        $sentLabel = \App\Support\SubscriberCommunications::formatSentAt($sentAt);
        $caption = $sentCaption ?? ($track['levelLabel'] ?? 'Sent');
        $showLevelOnButton = $track['levelLabel'] && $caption === $track['levelLabel'];
    @endphp
    <div>
        <div @class(['small mb-1', 'fw-semibold' => $caption !== 'Sent', 'text-muted' => $caption === 'Sent'])>{{ $caption }}</div>
        <div class="d-flex align-items-center gap-2">
            @if ($sentLabel)
                <button
                    type="button"
                    class="btn btn-sm btn-success text-nowrap js-view-email"
                    data-subscriber="{{ $subscriber->id }}"
                    data-sent-column="{{ $track['sent'] }}"
                    data-label="{{ $track['label'] }}"
                    aria-label="View {{ $track['label'] }} email sent {{ $sentLabel }}"
                >
                    <span aria-hidden="true">✓</span>
                    {{ $sentLabel }}
                </button>
            @else
                <span class="text-danger fs-5 lh-1" title="Not sent" aria-label="{{ $track['label'] }} has not been sent">✗</span>
                @if (! in_array($deliveryStatus, ['deleted', 'consent removed'], true))
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary text-nowrap js-send-one"
                        data-subscriber="{{ $subscriber->id }}"
                        data-sent-column="{{ $track['sent'] }}"
                        data-label="{{ $track['label'] }}"
                        data-email="{{ $subscriber->email_address }}"
                        aria-label="Send {{ $track['label'] }} email"
                        title="Send now"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-14.999Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.471 1.178Z"/>
                        </svg>
                        @if ($showLevelOnButton)
                            {{ $track['levelLabel'] }}
                        @endif
                    </button>
                @endif
            @endif
        </div>
    </div>
@endif
