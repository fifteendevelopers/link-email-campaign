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
                                @if ($subscriber->course_level)
                                    <div class="small">Course: {{ $subscriber->course_level }}</div>
                                @endif
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
                                $tracks = SubscriberCommunications::tracksFor($subscriber, $communication);
                                $sharedDate = count(array_unique(array_column($tracks, 'date'))) === 1;
                            @endphp
                            <td>
                                @if ($sharedDate && count($tracks) === 1)
                                    <div class="d-flex align-items-end gap-2">
                                        @include('subscribers.partials.communication-controls', [
                                            'track' => $tracks[0],
                                            'showDate' => true,
                                            'showSent' => false,
                                        ])
                                        @include('subscribers.partials.communication-controls', [
                                            'track' => $tracks[0],
                                            'showDate' => false,
                                            'showSent' => true,
                                        ])
                                    </div>
                                @elseif ($sharedDate)
                                    @php
                                        $dateTrack = $tracks[0];
                                        $dateTrack['label'] = $communication['label'];
                                    @endphp
                                    <div class="d-flex flex-column gap-2">
                                        @include('subscribers.partials.communication-controls', [
                                            'track' => $dateTrack,
                                            'showDate' => true,
                                            'showSent' => false,
                                        ])
                                        @foreach ($tracks as $track)
                                            @include('subscribers.partials.communication-controls', [
                                                'track' => $track,
                                                'showDate' => false,
                                                'showSent' => true,
                                                'sentCaption' => $track['levelLabel'],
                                            ])
                                        @endforeach
                                    </div>
                                @else
                                    <div class="d-flex flex-column gap-3">
                                        @foreach ($tracks as $track)
                                            <div>
                                                @if ($track['levelLabel'])
                                                    <div class="small fw-semibold mb-1">{{ $track['levelLabel'] }}</div>
                                                @endif
                                                <div class="d-flex align-items-end gap-2">
                                                    @include('subscribers.partials.communication-controls', [
                                                        'track' => $track,
                                                        'showDate' => true,
                                                        'showSent' => true,
                                                        'sentCaption' => 'Sent',
                                                    ])
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
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
