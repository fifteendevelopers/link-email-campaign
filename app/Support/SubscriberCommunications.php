<?php

declare(strict_types=1);

namespace App\Support;

use App\Jobs\SendDueParentCommunications;
use App\Jobs\SendDueSchoolCommunications;
use App\Models\ParentSubscriber;
use App\Models\SchoolSubscriber;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

final class SubscriberCommunications
{
    /**
     * @return array{
     *     label: string,
     *     noun: string,
     *     model: class-string<ParentSubscriber|SchoolSubscriber>,
     *     job: class-string<SendDueParentCommunications|SendDueSchoolCommunications>,
     *     subscriberKey: string,
     *     communications: list<array{
     *         date: string,
     *         sent: string,
     *         label: string,
     *         template: string,
     *         subject: callable(array<string, mixed>): string,
     *         levels?: list<array{key: string, label: string, date: string, sent: string}>
     *     }>
     * }
     */
    public static function forAudience(string $audience): array
    {
        return match ($audience) {
            'parents' => [
                'label' => 'Parent subscribers',
                'noun' => 'parent',
                'model' => ParentSubscriber::class,
                'job' => SendDueParentCommunications::class,
                'subscriberKey' => 'parent_subscriber_id',
                'communications' => [
                    [
                        'date' => 'journey_confirmation_start_date',
                        'sent' => 'journey_confirmation_sent_at',
                        'label' => 'Consent confirmation',
                        'template' => 'emails.parent_comms.consent_confirmation.confirmation',
                        'subject' => static fn (array $data): string => "We've received {$data['childName']}'s Bikeability consent form",
                    ],
                    [
                        'date' => 'cycle_and_helmet_check_comms_date',
                        'sent' => 'cycle_and_helmet_check_sent_at',
                        'label' => 'Cycle and helmet check',
                        'template' => 'emails.parent_comms.pre_course.how_to_get_ready_for_bikeability',
                        'subject' => static fn (array $data): string => 'How to get ready for Bikeability',
                    ],
                    [
                        'date' => 'pre_course_faq_comms_date',
                        'sent' => 'pre_course_faq_sent_at',
                        'label' => 'Pre-course FAQ',
                        'template' => 'emails.parent_comms.pre_course.faq',
                        'subject' => static fn (array $data): string => 'Frequently Asked Questions, answered',
                    ],
                    [
                        'date' => 'outcomes_journey_start_date',
                        'sent' => 'outcomes_journey_sent_at',
                        'label' => 'Rider report',
                        'template' => 'emails.parent_comms.post_course.rider_report',
                        'subject' => static fn (array $data): string => "{$data['childName']} did it!",
                        'levels' => [
                            [
                                'key' => '1',
                                'label' => 'Level 1',
                                'date' => 'outcomes_journey_level_1_start_date',
                                'sent' => 'outcomes_journey_level_1_sent_at',
                            ],
                            [
                                'key' => '2',
                                'label' => 'Level 2',
                                'date' => 'outcomes_journey_level_2_start_date',
                                'sent' => 'outcomes_journey_level_2_sent_at',
                            ],
                        ],
                    ],
                    [
                        'date' => 'outcome_survey_comms_date',
                        'sent' => 'outcome_survey_sent_at',
                        'label' => 'Parent survey',
                        'template' => 'emails.parent_comms.post_course.parent_survey',
                        'subject' => static fn (array $data): string => 'Please share your views',
                    ],
                ],
            ],
            'schools' => [
                'label' => 'School subscribers',
                'noun' => 'school',
                'model' => SchoolSubscriber::class,
                'job' => SendDueSchoolCommunications::class,
                'subscriberKey' => 'school_subscriber_id',
                'communications' => [
                    [
                        'date' => 'journey_confirmation_start_date',
                        'sent' => 'journey_confirmation_sent_at',
                        'label' => 'Booking confirmation',
                        'template' => 'emails.school_comms.booking_confirmation.booking_confirmation',
                        'subject' => static fn (array $data): string => "Booking confirmation: Bikeability cycle training at {$data['establishmentName']}",
                    ],
                    [
                        'date' => 'pre_course_consent_check_comms_date',
                        'sent' => 'pre_course_consent_check_sent_at',
                        'label' => 'Consent check',
                        'template' => 'emails.school_comms.pre_course.consent_check',
                        'subject' => static fn (array $data): string => "Upcoming Bikeability - action required! Complete consent checks and finalise numbers for {$data['establishmentName']}",
                    ],
                    [
                        'date' => 'outstanding_consent_check_comms_date',
                        'sent' => 'outstanding_consent_check_sent_at',
                        'label' => 'Outstanding consent check',
                        'template' => 'emails.school_comms.pre_course.consent_check_outstanding',
                        'subject' => static fn (array $data): string => "Check your outstanding Bikeability consents for {$data['establishmentName']}",
                    ],
                    [
                        'date' => 'final_consent_reminder_comms_date',
                        'sent' => 'final_consent_reminder_sent_at',
                        'label' => 'Final consent reminder',
                        'template' => 'emails.school_comms.pre_course.consent_check_final_reminder',
                        'subject' => static fn (array $data): string => "Final reminder: Are you ready for Bikeability at {$data['establishmentName']}?",
                    ],
                ],
            ],
            default => throw new InvalidArgumentException('Unknown subscriber list.'),
        };
    }

    /**
     * Level keys that should receive outcome emails for this course.
     * A combined Level 1 and Level 2 course receives both.
     *
     * @return list<'1'|'2'>
     */
    public static function includedLevels(?string $courseLevel): array
    {
        $normalized = strtolower(trim((string) preg_replace('/\s+/', ' ', (string) $courseLevel)));

        if (in_array($normalized, ['level 1 & 2 combined', 'level 1 and 2 combined'], true)) {
            return ['1', '2'];
        }

        if (str_contains($normalized, 'level 2') && ! str_contains($normalized, 'level 1')) {
            return ['2'];
        }

        return ['1'];
    }

    public static function isCombinedCourse(?string $courseLevel): bool
    {
        return self::includedLevels($courseLevel) === ['1', '2'];
    }

    /**
     * One send definition per email. Combined courses send a Level 1 and a
     * Level 2 rider report. Every other course uses the single rider report.
     *
     * @return list<array{
     *     date: string,
     *     sent: string,
     *     label: string,
     *     template: string,
     *     subject: callable(array<string, mixed>): string,
     *     level?: string,
     *     forCombined?: bool
     * }>
     */
    public static function sendable(string $audience): array
    {
        $sends = [];

        foreach (self::forAudience($audience)['communications'] as $communication) {
            $levels = $communication['levels'] ?? null;

            if ($levels === null) {
                $sends[] = $communication;

                continue;
            }

            $sends[] = [
                'date' => $communication['date'],
                'sent' => $communication['sent'],
                'label' => $communication['label'],
                'template' => $communication['template'],
                'subject' => $communication['subject'],
                'forCombined' => false,
            ];

            foreach ($levels as $level) {
                $sends[] = [
                    'date' => $level['date'],
                    'sent' => $level['sent'],
                    'label' => $communication['label'].' '.$level['label'],
                    'template' => $communication['template'],
                    'subject' => $communication['subject'],
                    'level' => $level['key'],
                    'forCombined' => true,
                ];
            }
        }

        return $sends;
    }

    /**
     * Controls to show in one subscriber-list cell.
     *
     * @param  array{date: string, sent: string, label: string, levels?: list<array{key: string, label: string, date: string, sent: string}>}  $communication
     * @return list<array{date: string, sent: string, label: string, levelLabel: string|null}>
     */
    public static function tracksFor(ParentSubscriber|SchoolSubscriber $subscriber, array $communication): array
    {
        $levels = $communication['levels'] ?? null;
        $singleTrack = [[
            'date' => $communication['date'],
            'sent' => $communication['sent'],
            'label' => $communication['label'],
            'levelLabel' => null,
        ]];

        if ($levels === null || ! $subscriber instanceof ParentSubscriber || ! self::isCombinedCourse($subscriber->course_level)) {
            return $singleTrack;
        }

        $included = self::includedLevels($subscriber->course_level);
        $showLevelLabels = count($included) > 1;
        $tracks = [];

        foreach ($levels as $level) {
            if (! in_array($level['key'], $included, true)) {
                continue;
            }

            $tracks[] = [
                'date' => $level['date'],
                'sent' => $level['sent'],
                'label' => $showLevelLabels
                    ? $communication['label'].' '.$level['label']
                    : $communication['label'],
                'levelLabel' => $showLevelLabels ? $level['label'] : null,
            ];
        }

        return $tracks;
    }

    /**
     * Combined courses keep a rider-report date for each level. Every other course
     * keeps one rider-report date. The parent survey is two days after the latest date.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function scheduleOutcomeDates(
        array $attributes,
        ?string $courseLevel,
        ?ParentSubscriber $subscriber = null,
    ): array {
        $combined = self::isCombinedCourse($courseLevel);
        $startWasUpdated = array_key_exists('outcomes_journey_start_date', $attributes)
            || self::hasRiderStartDate($attributes);

        if ($combined && array_key_exists('outcomes_journey_start_date', $attributes)) {
            foreach (self::includedLevels($courseLevel) as $level) {
                $column = self::riderStartColumn($level);

                if (! array_key_exists($column, $attributes)) {
                    $attributes[$column] = $attributes['outcomes_journey_start_date'];
                }
            }
        }

        if ($combined && $startWasUpdated) {
            $attributes['outcomes_journey_start_date'] = null;
        }

        if (! $combined && array_key_exists('outcomes_journey_start_date', $attributes)) {
            $attributes['outcomes_journey_level_1_start_date'] = null;
            $attributes['outcomes_journey_level_2_start_date'] = null;
        }

        if (! $startWasUpdated) {
            return $attributes;
        }

        $latest = $combined
            ? self::latestRiderStartDate($attributes, $subscriber, ['1', '2'])
            : (array_key_exists('outcomes_journey_start_date', $attributes)
                ? $attributes['outcomes_journey_start_date']
                : $subscriber?->outcomes_journey_start_date?->toDateString());

        $attributes['outcome_survey_comms_date'] = self::surveyDate($latest);

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $levels
     */
    private static function latestRiderStartDate(array $attributes, ?ParentSubscriber $subscriber, array $levels): ?string
    {
        $latest = null;

        foreach ($levels as $level) {
            $column = self::riderStartColumn($level);
            $value = array_key_exists($column, $attributes)
                ? $attributes[$column]
                : $subscriber?->getAttribute($column)?->toDateString();

            if ($value === null || $value === '') {
                continue;
            }

            if ($latest === null || $value > $latest) {
                $latest = $value;
            }
        }

        return $latest;
    }

    public static function riderStartColumn(string $level): string
    {
        return 'outcomes_journey_level_'.$level.'_start_date';
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private static function hasRiderStartDate(array $attributes): bool
    {
        return array_key_exists('outcomes_journey_level_1_start_date', $attributes)
            || array_key_exists('outcomes_journey_level_2_start_date', $attributes);
    }

    /**
     * @return list<string>
     */
    public static function dateColumns(string $audience): array
    {
        return array_values(array_unique(array_column(self::sendable($audience), 'date')));
    }

    /**
     * @return list<string>
     */
    public static function sentColumns(string $audience): array
    {
        return array_values(array_unique(array_column(self::sendable($audience), 'sent')));
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(string $audience): array
    {
        $statuses = [
            'active' => 'Active',
            'deleted' => 'Deleted',
        ];

        if ($audience === 'parents') {
            $statuses['consent removed'] = 'Consent removed';
        }

        return $statuses;
    }

    /**
     * @return array{date: string, sent: string, label: string, template: string, subject: callable(array<string, mixed>): string}|null
     */
    public static function findBySentColumn(string $audience, string $sentColumn): ?array
    {
        foreach (self::sendable($audience) as $communication) {
            if ($communication['sent'] === $sentColumn) {
                return $communication;
            }
        }

        return null;
    }

    private static function surveyDate(mixed $startDate): ?string
    {
        if ($startDate === null || $startDate === '') {
            return null;
        }

        return CarbonImmutable::parse((string) $startDate)->addDays(2)->toDateString();
    }

    /**
     * @return array<string, mixed>
     */
    public static function templateData(ParentSubscriber|SchoolSubscriber $subscriber): array
    {
        if ($subscriber instanceof ParentSubscriber) {
            return [
                'childName' => $subscriber->child_name,
                'parentName' => $subscriber->parent_name,
                'establishmentName' => $subscriber->establishment_name,
                'providerName' => $subscriber->provider_name,
                'providerPublicEmailAddress' => $subscriber->provider_public_email_address,
                'outcomesURL' => $subscriber->bikeability_club_link
                    ?: config('link.club.base_url', 'https://club.bikeability.org.uk'),
                'surveyLinkUrl' => $subscriber->survey_link
                    ?: config('ridersurvey.base_url', '#'),
            ];
        }

        return [
            'firstName' => $subscriber->first_name,
            'establishmentName' => $subscriber->establishment_name,
            'providerName' => $subscriber->provider_name,
            'providerContactEmail' => $subscriber->provider_contact_email_address,
            'bookingLink' => $subscriber->booking_link,
        ];
    }

    public static function formatSentAt(mixed $sentAt): ?string
    {
        if (! $sentAt instanceof CarbonInterface) {
            return null;
        }

        $timezone = (string) config('services.link_email_campaign.comms_timezone', 'Europe/London');

        return $sentAt->copy()->timezone($timezone)->format('d/m/Y, H:i');
    }
}
