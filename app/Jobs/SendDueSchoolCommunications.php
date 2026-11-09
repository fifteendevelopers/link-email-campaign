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
use Illuminate\Support\Facades\Log;
use Throwable;

class SendDueSchoolCommunications implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function handle(CampaignEmailSender $emailSender): void
    {
        $timezone = (string) config('services.link_email_campaign.comms_timezone', 'Europe/London');
        $dueDate = today($timezone)->toDateString();
        $failures = [];

        $this->log('info', 'School communication job started.', [
            'timezone' => $timezone,
            'due_date' => $dueDate,
        ]);

        foreach ($this->communications() as $communication) {
            $dateColumn = $communication['date'];
            $sentColumn = $communication['sent'];

            $this->logSelection($dateColumn, $sentColumn, $communication['template'], $dueDate);

            SchoolSubscriber::query()
                ->where('delivery_status', 'active')
                ->whereNotNull($dateColumn)
                ->whereDate($dateColumn, '<=', $dueDate)
                ->whereNull($sentColumn)
                ->orderBy('id')
                ->chunkById(100, function ($subscribers) use (
                    $communication,
                    $emailSender,
                    $dateColumn,
                    $sentColumn,
                    $dueDate,
                    &$failures,
                ): void {
                    foreach ($subscribers as $subscriber) {
                        if ($this->alreadySent($subscriber, $sentColumn)) {
                            continue;
                        }

                        $context = [
                            'school_subscriber_id' => $subscriber->id,
                            'email_address' => $subscriber->email_address,
                            'date_column' => $dateColumn,
                            'comms_date' => $subscriber->getAttribute($dateColumn)?->toDateString(),
                            'template' => $communication['template'],
                            'due_date' => $dueDate,
                        ];

                        $this->log('info', 'Sending school communication.', $context);

                        try {
                            $data = $this->templateData($subscriber);
                            $subject = $communication['subject']($data);
                            $tracking = $emailSender->send(
                                recipient: $subscriber->email_address,
                                subject: $subject,
                                template: $communication['template'],
                                data: $data,
                                schoolSubscriberId: $subscriber->id,
                            );

                            $sentAt = now();
                            $subscriber->forceFill([$sentColumn => $sentAt])->save();

                            $this->log('info', 'School communication sent.', [
                                ...$context,
                                'subject' => $subject,
                                'tracking_id' => $tracking->tracking_id,
                                'sent_at' => $sentAt->toDateTimeString(),
                            ]);
                        } catch (Throwable $exception) {
                            $failures[] = $exception;

                            $this->log('error', 'School communication failed.', [
                                ...$context,
                                'exception' => $exception::class,
                                'error' => $exception->getMessage(),
                            ]);
                        }
                    }
                });
        }

        $this->log($failures === [] ? 'info' : 'error', 'School communication job finished.', [
            'due_date' => $dueDate,
            'failure_count' => count($failures),
        ]);

        if ($failures !== []) {
            throw $failures[0];
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
    private function templateData(SchoolSubscriber $subscriber): array
    {
        return [
            'firstName' => $subscriber->first_name,
            'establishmentName' => $subscriber->establishment_name,
            'providerName' => $subscriber->provider_name,
            'providerContactEmail' => $subscriber->provider_contact_email_address,
            'bookingLink' => $subscriber->booking_link,
        ];
    }

    private function logSelection(string $dateColumn, string $sentColumn, string $template, string $dueDate): void
    {
        $selectedIds = [];

        SchoolSubscriber::query()
            ->whereNotNull($dateColumn)
            ->when(
                $this->ignoresSentColumn($sentColumn),
                fn ($query) => $query->whereNull($sentColumn),
            )
            ->orderBy('id')
            ->each(function (SchoolSubscriber $subscriber) use ($dateColumn, $sentColumn, $template, $dueDate, &$selectedIds): void {
                if ($this->alreadySent($subscriber, $sentColumn)) {
                    return;
                }

                $reason = $this->skipReason($subscriber, $dateColumn, $sentColumn, $dueDate);

                if ($reason === null) {
                    $selectedIds[] = $subscriber->id;

                    return;
                }

                $this->log('info', 'School subscriber not selected.', [
                    'school_subscriber_id' => $subscriber->id,
                    'email_address' => $subscriber->email_address,
                    'date_column' => $dateColumn,
                    'comms_date' => $subscriber->getAttribute($dateColumn)?->toDateString(),
                    'delivery_status' => $subscriber->delivery_status,
                    'sent_at' => $subscriber->getAttribute($sentColumn)?->toDateTimeString(),
                    'template' => $template,
                    'reason' => $reason,
                ]);
            });

        $this->log('info', 'Selected school subscribers.', [
            'date_column' => $dateColumn,
            'sent_column' => $sentColumn,
            'template' => $template,
            'due_date' => $dueDate,
            'selected_ids' => $selectedIds,
            'selected_count' => count($selectedIds),
        ]);
    }

    private function skipReason(
        SchoolSubscriber $subscriber,
        string $dateColumn,
        string $sentColumn,
        string $dueDate,
    ): ?string {
        if ($subscriber->delivery_status !== 'active') {
            return 'delivery status is '.$subscriber->delivery_status;
        }

        $commsDate = $subscriber->getAttribute($dateColumn)?->toDateString();

        if ($commsDate > $dueDate) {
            return 'comms date is after the due date';
        }

        if (! $this->ignoresSentColumn($sentColumn) && $subscriber->getAttribute($sentColumn) !== null) {
            return 'communication has already been sent';
        }

        return null;
    }

    private function alreadySent(SchoolSubscriber $subscriber, string $sentColumn): bool
    {
        return $this->ignoresSentColumn($sentColumn)
            && $subscriber->getAttribute($sentColumn) !== null;
    }

    private function ignoresSentColumn(string $sentColumn): bool
    {
        return in_array($sentColumn, [
            'journey_confirmation_sent_at',
            'pre_course_consent_check_sent_at',
            'outstanding_consent_check_sent_at',
            'final_consent_reminder_sent_at',
        ], true);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel('school_subscription')->log($level, $message, $context);
    }
}
