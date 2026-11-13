@php
    use App\Support\SubscriberCommunications;

    $avatarPalette = ['#0f766e', '#0e7490', '#1d4ed8', '#6d28d9', '#be123c', '#b45309'];
@endphp

@if ($subscribers->isEmpty())
    <div class="empty-state">
        <div class="empty-mark" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
        </div>
        <p class="empty-title">No subscribers found.</p>
        <p class="empty-copy">Try another status, or search by email, delivery ID, consent ID, or child name.</p>
    </div>
@else
    <div class="table-shell">
        <div class="table-meta">
            <span>Showing {{ $subscribers->firstItem() }}–{{ $subscribers->lastItem() }} of {{ $subscribers->total() }}</span>
            <span>{{ $audience === 'parents' ? 'Parent journey' : 'School journey' }}</span>
        </div>
        <div class="table-scroll">
            <table class="subscriber-table">
                <thead>
                    <tr>
                        <th scope="col" @class(['sticky-start', 'sticky-edge' => $audience !== 'parents'])>Subscriber</th>
                        @if ($audience === 'parents')
                            <th scope="col" class="sticky-course sticky-edge">Delivery / Course</th>
                        @endif
                        @foreach ($communications as $communication)
                            <th scope="col">
                                <span class="comm-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                {{ $communication['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscribers as $subscriber)
                        @php
                            $deliveryStatus = strtolower(trim((string) $subscriber->delivery_status));
                            $contactName = $audience === 'parents'
                                ? ($subscriber->parent_name ?: 'Parent')
                                : ($subscriber->name ?: $subscriber->first_name ?: 'School contact');
                            $initials = '';

                            foreach (preg_split('/\s+/', trim((string) $contactName)) ?: [] as $namePart) {
                                if ($namePart === '') {
                                    continue;
                                }

                                $initials .= mb_strtoupper(mb_substr($namePart, 0, 1));

                                if (mb_strlen($initials) >= 2) {
                                    break;
                                }
                            }

                            $avatarColor = $avatarPalette[abs(crc32((string) $subscriber->email_address)) % count($avatarPalette)];
                            $sentCount = 0;

                            foreach ($communications as $communication) {
                                if ($subscriber->getAttribute($communication['sent']) !== null) {
                                    $sentCount++;
                                }
                            }

                            $communicationTotal = count($communications);
                            $sentPercent = $communicationTotal > 0 ? (int) round(($sentCount / $communicationTotal) * 100) : 0;
                        @endphp
                        <tr>
                            <td @class(['subscriber-cell', 'sticky-start', 'sticky-edge' => $audience !== 'parents'])>
                                <div class="subscriber-card">
                                    <span class="avatar" style="background-color: {{ $avatarColor }}" aria-hidden="true">{{ $initials }}</span>
                                    <div class="subscriber-identity">
                                        <div class="subscriber-email" title="{{ $subscriber->email_address }}">{{ $subscriber->email_address }}</div>
                                        <div class="subscriber-name">
                                            {{ $contactName }}
                                            @if ($audience === 'parents' && $subscriber->child_name)
                                                · {{ $subscriber->child_name }}
                                            @endif
                                        </div>
                                        @if ($subscriber->establishment_name)
                                            <div class="subscriber-place">{{ $subscriber->establishment_name }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="meta-list">
                                    @if ($audience !== 'parents')
                                        <div class="meta-row"><span class="meta-label">Delivery ID</span> {{ $subscriber->delivery_id ?: '—' }}</div>
                                    @endif
                                    <div class="meta-row"><span class="meta-label">Last updated</span> {{ SubscriberCommunications::formatSentAt($subscriber->updated_at) ?: '—' }}</div>
                                </div>
                                <div class="sent-meter" aria-label="{{ $sentCount }} of {{ $communicationTotal }} emails sent">
                                    <div class="sent-meter-track">
                                        <span class="sent-meter-fill" style="width: {{ $sentPercent }}%"></span>
                                    </div>
                                    <div class="sent-meter-label">{{ $sentCount }} of {{ $communicationTotal }} sent</div>
                                </div>
                                <div class="status-field">
                                    <label class="field-label" for="status-{{ $subscriber->id }}">Status</label>
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
                                </div>
                            </td>
                            @if ($audience === 'parents')
                                <td class="course-cell sticky-course sticky-edge">
                                    <div class="meta-list">
                                        <div class="meta-row"><span class="meta-label">Delivery ID</span> {{ $subscriber->delivery_id ?: '—' }}</div>
                                        <div class="meta-row"><span class="meta-label">Consent ID</span> {{ $subscriber->consent_id ?: '—' }}</div>
                                        <div class="meta-row"><span class="meta-label">Course Level</span> {{ $subscriber->course_level ?: '—' }}</div>
                                        <div class="meta-row"><span class="meta-label">Course Survey Level</span> {{ $subscriber->outcome_survey_course_level ?: '—' }}</div>
                                    </div>
                                </td>
                            @endif
                            @foreach ($communications as $communication)
                                <td>
                                    <div class="comm-panel">
                                        @include('subscribers.partials.communication-controls', [
                                            'track' => $communication,
                                            'showDate' => true,
                                            'showSent' => false,
                                        ])
                                        @include('subscribers.partials.communication-controls', [
                                            'track' => $communication,
                                            'showDate' => false,
                                            'showSent' => true,
                                        ])
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($subscribers->hasPages())
            <div class="pager">
                {{ $subscribers->links() }}
            </div>
        @endif
    </div>
@endif
