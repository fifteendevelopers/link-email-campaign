@php
    use App\Support\SubscriberCommunications;
@endphp

@if ($subscribers->isEmpty())
    <div class="alert alert-info mb-0">No subscribers found.</div>
@else
    <p class="text-muted small mb-2">
        Showing {{ $subscribers->firstItem() }}–{{ $subscribers->lastItem() }} of {{ $subscribers->total() }}
    </p>
    <div class="table-responsive bg-white border rounded">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Subscriber</th>
                    @foreach ($communications as $communication)
                        <th>{{ $communication['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($subscribers as $subscriber)
                    @php
                        $deliveryStatus = strtolower(trim((string) $subscriber->delivery_status));
                    @endphp
                    <tr>
                        <td class="text-nowrap">
                            <div>{{ $subscriber->email_address }}</div>
                            @if ($audience === 'parents')
                                <div class="small text-muted">
                                    {{ $subscriber->parent_name ?: 'Parent' }}
                                    @if ($subscriber->child_name)
                                        · {{ $subscriber->child_name }}
                                    @endif
                                </div>
                            @else
                                <div class="small text-muted">{{ $subscriber->name ?: $subscriber->first_name ?: 'School contact' }}</div>
                            @endif
                            @if ($subscriber->establishment_name)
                                <div class="small">{{ $subscriber->establishment_name }}</div>
                            @endif
                            <div class="small">Delivery ID: {{ $subscriber->delivery_id ?: '—' }}</div>
                            @if ($audience === 'parents')
                                <div class="small">Consent ID: {{ $subscriber->consent_id ?: '—' }}</div>
                            @endif
                            <div class="small">Last updated: {{ SubscriberCommunications::formatSentAt($subscriber->updated_at) ?: '—' }}</div>
                            <label class="form-label small text-muted mb-1 mt-2" for="status-{{ $subscriber->id }}">Status</label>
                            <select
                                id="status-{{ $subscriber->id }}"
                                @class([
                                    'form-select form-select-sm js-delivery-status',
                                    'status-active' => $deliveryStatus === 'active',
                                    'status-removed' => in_array($deliveryStatus, ['deleted', 'consent removed'], true),
                                ])
                                data-subscriber="{{ $subscriber->id }}"
                                data-current="{{ $deliveryStatus }}"
                                aria-label="Delivery status for {{ $subscriber->email_address }}"
                            >
                                @foreach ($statuses as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @class([
                                            'status-active' => $value === 'active',
                                            'status-removed' => in_array($value, ['deleted', 'consent removed'], true),
                                        ])
                                        @selected($deliveryStatus === $value)
                                    >{{ $label }}</option>
                                @endforeach
                                @if (! array_key_exists($deliveryStatus, $statuses))
                                    <option value="{{ $deliveryStatus }}" selected>{{ ucfirst($deliveryStatus) }}</option>
                                @endif
                            </select>
                        </td>
                        @foreach ($communications as $communication)
                            @php
                                $sendDate = $subscriber->getAttribute($communication['date']);
                                $sentAt = $subscriber->getAttribute($communication['sent']);
                                $sentLabel = SubscriberCommunications::formatSentAt($sentAt);
                            @endphp
                            <td>
                                <div class="d-flex align-items-end gap-2">
                                    <div>
                                        <label class="form-label small text-muted mb-1" for="date-{{ $subscriber->id }}-{{ $communication['date'] }}">Send date</label>
                                        <div class="input-group input-group-sm" style="width: 11rem;">
                                            <input
                                                id="date-{{ $subscriber->id }}-{{ $communication['date'] }}"
                                                type="text"
                                                readonly
                                                placeholder="dd/mm/yyyy"
                                                class="form-control js-send-date"
                                                style="cursor: pointer; background-color: #fff;"
                                                value="{{ $sendDate?->format('d/m/Y') }}"
                                                data-subscriber="{{ $subscriber->id }}"
                                                data-column="{{ $communication['date'] }}"
                                                aria-label="{{ $communication['label'] }} send date"
                                                title="Choose a date"
                                            >
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary js-open-date"
                                                aria-label="Open {{ $communication['label'] }} calendar"
                                            >&#9662;</button>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-muted mb-1">Sent</div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($sentLabel)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-success text-nowrap js-view-email"
                                                    data-subscriber="{{ $subscriber->id }}"
                                                    data-sent-column="{{ $communication['sent'] }}"
                                                    data-label="{{ $communication['label'] }}"
                                                    aria-label="View {{ $communication['label'] }} email sent {{ $sentLabel }}"
                                                >
                                                    <span aria-hidden="true">✓</span>
                                                    {{ $sentLabel }}
                                                </button>
                                            @else
                                                <span class="text-danger fs-5 lh-1" title="Not sent" aria-label="{{ $communication['label'] }} has not been sent">✗</span>
                                                @if (! in_array($deliveryStatus, ['deleted', 'consent removed'], true))
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary js-send-one"
                                                        data-subscriber="{{ $subscriber->id }}"
                                                        data-sent-column="{{ $communication['sent'] }}"
                                                        data-label="{{ $communication['label'] }}"
                                                        data-email="{{ $subscriber->email_address }}"
                                                        aria-label="Send {{ $communication['label'] }} email"
                                                        title="Send now"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                                            <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-14.999Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.471 1.178Z"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($subscribers->hasPages())
        <div class="mt-3">
            {{ $subscribers->links() }}
        </div>
    @endif
@endif
