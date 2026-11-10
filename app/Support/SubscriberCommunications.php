<?php

declare(strict_types=1);

namespace App\Support;

use App\Jobs\SendDueParentCommunications;
use App\Jobs\SendDueSchoolCommunications;
use App\Models\ParentSubscriber;
use App\Models\SchoolSubscriber;
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
     *     communications: list<array{date: string, sent: string, label: string, template: string, subject: callable(array<string, mixed>): string}>
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
                        'date' => 'cycle_and_helment_check_comms_date',
                        'sent' => 'cycle_and_helment_check_sent_at',
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
     * @return list<string>
     */
    public static function dateColumns(string $audience): array
    {
        return array_column(self::forAudience($audience)['communications'], 'date');
    }

    /**
     * @return list<string>
     */
    public static function sentColumns(string $audience): array
    {
        return array_column(self::forAudience($audience)['communications'], 'sent');
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
        foreach (self::forAudience($audience)['communications'] as $communication) {
            if ($communication['sent'] === $sentColumn) {
                return $communication;
            }
        }

        return null;
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
