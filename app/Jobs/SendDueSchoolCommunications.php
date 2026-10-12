<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SchoolSubscriber;
use App\Services\CampaignEmailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDueSchoolCommunications implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function handle(CampaignEmailSender $emailSender): void
    {
        $timezone = (string) config('services.link_email_campaign.comms_timezone', 'Europe/London');
        $dueDate = today($timezone)->toDateString();

        foreach ($this->communications() as $communication) {
            foreach (range(1, 3) as $segment) {
                $dateColumn = "{$communication['date']}_{$segment}";
                $sentColumn = "{$communication['sent']}_{$segment}";

                SchoolSubscriber::query()
                    ->where("delivery_status_{$segment}", 'active')
                    ->whereNotNull($dateColumn)
                    ->whereDate($dateColumn, '<=', $dueDate)
                    ->whereNull($sentColumn)
                    ->orderBy('id')
                    ->chunkById(100, function ($subscribers) use (
                        $communication,
                        $emailSender,
                        $segment,
                        $sentColumn,
                    ): void {
                        foreach ($subscribers as $subscriber) {
                            $data = $this->templateData($subscriber, $segment);

                            $emailSender->send(
                                recipient: $subscriber->email_address,
                                subject: $communication['subject']($data),
                                template: $communication['template'],
                                data: $data,
                                schoolSubscriberId: $subscriber->id,
                            );

                            $subscriber->forceFill([$sentColumn => now()])->save();
                        }
                    });
            }
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
                'template' => 'emails.school_comms.booking_confirmation.booking_confirmation',
                'subject' => static fn (array $data): string => "Booking confirmation: Bikeability cycle training at {$data['establishmentName']}",
            ],
            [
                'date' => 'pre_course_consent_check_comms_date',
                'sent' => 'pre_course_consent_check_sent_at',
                'template' => 'emails.school_comms.pre_course.consent_check',
                'subject' => static fn (array $data): string => "Upcoming Bikeability - action required! Complete consent checks and finalise numbers for {$data['establishmentName']}",
            ],
            [
                'date' => 'outstanding_consent_check_comms_date',
                'sent' => 'outstanding_consent_check_sent_at',
                'template' => 'emails.school_comms.pre_course.consent_check_outstanding',
                'subject' => static fn (array $data): string => "Check your outstanding Bikeability consents for {$data['establishmentName']}",
            ],
            [
                'date' => 'final_consent_reminder_comms_date',
                'sent' => 'final_consent_reminder_sent_at',
                'template' => 'emails.school_comms.pre_course.consent_check_final_reminder',
                'subject' => static fn (array $data): string => "Final reminder: Are you ready for Bikeability at {$data['establishmentName']}?",
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templateData(SchoolSubscriber $subscriber, int $segment): array
    {
        return [
            'firstName' => $subscriber->getAttribute("first_name_{$segment}"),
            'establishmentName' => $subscriber->getAttribute("establishment_name_{$segment}"),
            'providerName' => $subscriber->getAttribute("provider_name_{$segment}"),
            'providerContactEmail' => $subscriber->getAttribute("provider_contact_email_address_{$segment}"),
            'bookingLink' => $subscriber->getAttribute("booking_link_{$segment}"),
        ];
    }
}
