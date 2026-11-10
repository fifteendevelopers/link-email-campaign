<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ParentSubscriber;
use App\Services\CampaignEmailSender;
use App\Support\SubscriberCommunications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendDueParentCommunications implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function handle(CampaignEmailSender $emailSender): void
    {
        $timezone = (string) config('services.link_email_campaign.comms_timezone', 'Europe/London');
        $dueDate = today($timezone)->toDateString();
        $failures = [];

        $this->log('info', 'Parent communication job started.', [
            'timezone' => $timezone,
            'due_date' => $dueDate,
        ]);

        foreach ($this->communications() as $communication) {
            $dateColumn = $communication['date'];
            $sentColumn = $communication['sent'];

            $this->logSelection($dateColumn, $sentColumn, $communication['template'], $dueDate);

            ParentSubscriber::query()
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
                            'parent_subscriber_id' => $subscriber->id,
                            'email_address' => $subscriber->email_address,
                            'date_column' => $dateColumn,
                            'comms_date' => $subscriber->getAttribute($dateColumn)?->toDateString(),
                            'template' => $communication['template'],
                            'due_date' => $dueDate,
                        ];

                        $this->log('info', 'Sending parent communication.', $context);

                        try {
                            $data = SubscriberCommunications::templateData($subscriber);
                            $subject = $communication['subject']($data);
                            $tracking = $emailSender->send(
                                recipient: $subscriber->email_address,
                                subject: $subject,
                                template: $communication['template'],
                                data: $data,
                                parentSubscriberId: $subscriber->id,
                                sentColumn: $sentColumn,
                            );

                            $sentAt = now();
                            $subscriber->forceFill([$sentColumn => $sentAt])->save();

                            $this->log('info', 'Parent communication sent.', [
                                ...$context,
                                'subject' => $subject,
                                'tracking_id' => $tracking->tracking_id,
                                'sent_at' => $sentAt->toDateTimeString(),
                            ]);
                        } catch (Throwable $exception) {
                            $failures[] = $exception;

                            $this->log('error', 'Parent communication failed.', [
                                ...$context,
                                'exception' => $exception::class,
                                'error' => $exception->getMessage(),
                            ]);
                        }
                    }
                });
        }

        $this->log($failures === [] ? 'info' : 'error', 'Parent communication job finished.', [
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
        return SubscriberCommunications::forAudience('parents')['communications'];
    }

    private function logSelection(string $dateColumn, string $sentColumn, string $template, string $dueDate): void
    {
        $selectedIds = [];

        ParentSubscriber::query()
            ->whereNotNull($dateColumn)
            ->when(
                $this->ignoresSentColumn($sentColumn),
                fn ($query) => $query->whereNull($sentColumn),
            )
            ->orderBy('id')
            ->each(function (ParentSubscriber $subscriber) use ($dateColumn, $sentColumn, $template, $dueDate, &$selectedIds): void {
                if ($this->alreadySent($subscriber, $sentColumn)) {
                    return;
                }

                $reason = $this->skipReason($subscriber, $dateColumn, $sentColumn, $dueDate);

                if ($reason === null) {
                    $selectedIds[] = $subscriber->id;

                    return;
                }

                $this->log('info', 'Parent subscriber not selected.', [
                    'parent_subscriber_id' => $subscriber->id,
                    'email_address' => $subscriber->email_address,
                    'date_column' => $dateColumn,
                    'comms_date' => $subscriber->getAttribute($dateColumn)?->toDateString(),
                    'delivery_status' => $subscriber->delivery_status,
                    'sent_at' => $subscriber->getAttribute($sentColumn)?->toDateTimeString(),
                    'template' => $template,
                    'reason' => $reason,
                ]);
            });

        $this->log('info', 'Selected parent subscribers.', [
            'date_column' => $dateColumn,
            'sent_column' => $sentColumn,
            'template' => $template,
            'due_date' => $dueDate,
            'selected_ids' => $selectedIds,
            'selected_count' => count($selectedIds),
        ]);
    }

    private function skipReason(
        ParentSubscriber $subscriber,
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

    private function alreadySent(ParentSubscriber $subscriber, string $sentColumn): bool
    {
        return $this->ignoresSentColumn($sentColumn)
            && $subscriber->getAttribute($sentColumn) !== null;
    }

    private function ignoresSentColumn(string $sentColumn): bool
    {
        return in_array($sentColumn, [
            'journey_confirmation_sent_at',
            'cycle_and_helment_check_sent_at',
            'pre_course_faq_sent_at',
            'outcomes_journey_sent_at',
            'outcome_survey_sent_at',
        ], true);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel('parent_subscription')->log($level, $message, $context);
    }
}
