<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\CampaignMail;
use App\Models\EmailLog;
use App\Models\EmailTracking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;
use Throwable;

class CampaignEmailSender
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function send(
        string $recipient,
        string $subject,
        string $template,
        array $data,
        ?int $parentSubscriberId = null,
        ?int $schoolSubscriberId = null,
        ?string $sentColumn = null,
    ): EmailTracking {
        $tracking = EmailTracking::create([
            'tracking_id' => (string) Str::uuid(),
            'parent_subscriber_id' => $parentSubscriberId,
            'school_subscriber_id' => $schoolSubscriberId,
            'recipient_email' => $recipient,
            'subject' => $subject,
        ]);

        $payload = array_merge($data, [
            'title' => $subject,
            'template' => $template,
            'recipient' => $recipient,
        ]);

        $mail = new CampaignMail($payload);

        $mail->withSymfonyMessage(static function (Email $message) use ($tracking): void {
            $message->getHeaders()->addTextHeader(
                'X-Mailgun-Variables',
                json_encode(['tracking_id' => $tracking->tracking_id], JSON_THROW_ON_ERROR),
            );
        });

        $html = view($template, ['data' => $payload])->render();

        Mail::to($recipient)->send($mail);

        $this->writeLog(
            recipient: $recipient,
            subject: $subject,
            html: $html,
            parentSubscriberId: $parentSubscriberId,
            schoolSubscriberId: $schoolSubscriberId,
            sentColumn: $sentColumn,
        );

        return $tracking;
    }

    private function writeLog(
        string $recipient,
        string $subject,
        string $html,
        ?int $parentSubscriberId,
        ?int $schoolSubscriberId,
        ?string $sentColumn,
    ): void {
        $from = $this->formattedFrom();

        try {
            EmailLog::query()->create([
                'date' => now(),
                'from' => $from,
                'to' => $recipient,
                'cc' => null,
                'bcc' => null,
                'subject' => $subject,
                'body' => $html,
                'headers' => "From: {$from}\r\nTo: {$recipient}\r\nSubject: {$subject}\r\n",
                'attachments' => null,
                'parent_subscriber_id' => $parentSubscriberId,
                'school_subscriber_id' => $schoolSubscriberId,
                'sent_column' => $sentColumn,
            ]);
        } catch (Throwable $exception) {
            Log::error('Campaign email was sent but could not be written to email_log.', [
                'recipient' => $recipient,
                'subject' => $subject,
                'sent_column' => $sentColumn,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function formattedFrom(): string
    {
        $address = (string) config('mail.from.address');
        $name = trim((string) config('mail.from.name'));

        if ($name === '') {
            return $address;
        }

        return $name.' <'.$address.'>';
    }
}
