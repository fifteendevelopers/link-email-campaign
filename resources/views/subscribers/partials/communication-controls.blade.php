@if ($showDate)
    <div class="comm-date">
        <label class="field-label" for="date-{{ $subscriber->id }}-{{ $track['date'] }}">Send date</label>
        <div class="input-group input-group-sm date-field">
            <input
                id="date-{{ $subscriber->id }}-{{ $track['date'] }}"
                type="text"
                readonly
                placeholder="dd/mm/yyyy"
                class="form-control js-send-date"
                value="{{ $subscriber->getAttribute($track['date'])?->format('d/m/Y') }}"
                data-subscriber="{{ $subscriber->id }}"
                data-column="{{ $track['date'] }}"
                aria-label="{{ $track['label'] }} send date"
                title="Choose a date"
            >
            <button
                type="button"
                class="date-open js-open-date"
                aria-label="Open {{ $track['label'] }} calendar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                </svg>
            </button>
        </div>
    </div>
@endif
@if ($showSent)
    @php
        $sentAt = $subscriber->getAttribute($track['sent']);
        $sentLabel = \App\Support\SubscriberCommunications::formatSentAt($sentAt);
    @endphp
    <div class="comm-sent">
        <div class="field-label">Sent</div>
        <div class="sent-row">
            @if ($sentLabel)
                <button
                    type="button"
                    class="sent-chip js-view-email"
                    data-subscriber="{{ $subscriber->id }}"
                    data-sent-column="{{ $track['sent'] }}"
                    data-label="{{ $track['label'] }}"
                    aria-label="View {{ $track['label'] }} email sent {{ $sentLabel }}"
                >
                    <span aria-hidden="true">✓</span>
                    {{ $sentLabel }}
                </button>
            @else
                <span class="unsent-pill" title="Not sent" aria-label="{{ $track['label'] }} has not been sent">Not sent</span>
                @if (! in_array($deliveryStatus, ['deleted', 'consent removed'], true))
                    <button
                        type="button"
                        class="send-chip js-send-one"
                        data-subscriber="{{ $subscriber->id }}"
                        data-sent-column="{{ $track['sent'] }}"
                        data-label="{{ $track['label'] }}"
                        data-email="{{ $subscriber->email_address }}"
                        aria-label="Send {{ $track['label'] }} email"
                        title="Send now"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-14.999Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.471 1.178Z"/>
                        </svg>
                    </button>
                @endif
            @endif
        </div>
    </div>
@endif
