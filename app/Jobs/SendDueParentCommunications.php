<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ParentSubscriber;
use App\Services\CampaignEmailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDueParentCommunications implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function handle(CampaignEmailSender $emailSender): void
    {
        $timezone = (string) config('services.link_email_campaign.comms_timezone', 'Europe/London');
        $dueDate = today($timezone)->toDateString();

        foreach ($this->communications() as $communication) {
            $dateColumn = $communication['date'];
            $sentColumn = $communication['sent'];

            ParentSubscriber::query()
                ->where('delivery_status', 'active')
                ->whereNotNull($dateColumn)
                ->whereDate($dateColumn, '<=', $dueDate)
                ->whereNull($sentColumn)
                ->orderBy('id')
                ->chunkById(100, function ($subscribers) use (
                    $communication,
                    $emailSender,
                    $sentColumn,
                ): void {
                    foreach ($subscribers as $subscriber) {
                        $data = $this->templateData($subscriber);

                        $emailSender->send(
                            recipient: $subscriber->email_address,
                            subject: $communication['subject']($data),
                            template: $communication['template'],
                            data: $data,
                            parentSubscriberId: $subscriber->id,
                        );

                        $subscriber->forceFill([$sentColumn => now()])->save();
                    }
                });
        }
    }

    /**
     * @return array<int, array{
     *     date: string,
     *     sent: string,
     *     template: string,
     *     subject: callable(array<string, mixed>): string
     * }>
     */
    private function communications(): array
    {
        return [
            [
                'date' => 'journey_confirmation_start_date',
                'sent' => 'journey_confirmation_sent_at',
                'template' => 'emails.parent_comms.consent_confirmation.confirmation',
                'subject' => static fn (array $data): string => "We've received {$data['childName']}'s Bikeability consent form",
            ],
            [
                'date' => 'cycle_and_helment_check_comms_date',
                'sent' => 'cycle_and_helment_check_sent_at',
                'template' => 'emails.parent_comms.pre_course.how_to_get_ready_for_bikeability',
                'subject' => static fn (array $data): string => 'How to get ready for Bikeability',
            ],
            [
                'date' => 'pre_course_faq_comms_date',
                'sent' => 'pre_course_faq_sent_at',
                'template' => 'emails.parent_comms.pre_course.faq',
                'subject' => static fn (array $data): string => 'Frequently Asked Questions, answered',
            ],
            [
                'date' => 'outcomes_journey_start_date',
                'sent' => 'outcomes_journey_sent_at',
                'template' => 'emails.parent_comms.post_course.rider_report',
                'subject' => static fn (array $data): string => "{$data['childName']} did it!",
            ],
            [
                'date' => 'outcome_survey_comms_date',
                'sent' => 'outcome_survey_sent_at',
                'template' => 'emails.parent_comms.post_course.parent_survey',
                'subject' => static fn (array $data): string => 'Please share your views',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templateData(ParentSubscriber $subscriber): array
    {
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
}
