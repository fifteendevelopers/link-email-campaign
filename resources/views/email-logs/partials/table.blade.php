@php
    use App\Support\SubscriberCommunications;

    $summary = $logs->total() === 0
        ? 'No emails to show'
        : 'Showing '.$logs->firstItem().'–'.$logs->lastItem().' of '.$logs->total();
@endphp

<div data-summary="{{ $summary }}">
    @if ($logs->isEmpty())
        <div class="empty-state">
            <div class="empty-mark" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
            </div>
            <p class="empty-title">No emails found.</p>
            <p class="empty-copy">Try another email, consent ID, or delivery ID.</p>
        </div>
    @else
        <div class="table-scroll">
            <table class="subscriber-table log-table">
                <thead>
                    <tr>
                        <th scope="col">Subscriber</th>
                        <th scope="col">Date</th>
                        <th scope="col">From</th>
                        <th scope="col">To</th>
                        <th scope="col">Subject</th>
                        <th scope="col"><span class="visually-hidden">View</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="log-subscriber">
                                <div class="meta-list">
                                    <div class="meta-row"><span class="meta-label">Consent ID</span> {{ $log->consent_id ?: '—' }}</div>
                                    <div class="meta-row"><span class="meta-label">Delivery ID</span> {{ $log->delivery_id ?: '—' }}</div>
                                    <div class="meta-row"><span class="meta-label">Course Level</span> {{ $log->course_level ?: '—' }}</div>
                                    <div class="meta-row"><span class="meta-label">Course Survey Level</span> {{ $log->course_survey_level ?: '—' }}</div>
                                </div>
                            </td>
                            <td>{{ SubscriberCommunications::formatSentAt($log->date) ?: '—' }}</td>
                            <td>{{ $log->from ?: '—' }}</td>
                            <td>{{ $log->to ?: '—' }}</td>
                            <td class="log-subject">{{ $log->subject ?: '—' }}</td>
                            <td class="log-view-cell">
                                <a
                                    href="{{ route('email-logs.show', $log) }}"
                                    class="log-view"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="View email{{ $log->subject ? ' '.$log->subject : '' }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="pager">
                {{ $logs->links() }}
            </div>
        @endif
    @endif
</div>
