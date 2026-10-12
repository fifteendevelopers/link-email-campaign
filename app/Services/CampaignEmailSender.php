<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\CampaignMail;
use App\Models\EmailTracking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;

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
    ): EmailTracking {
        $tracking = EmailTracking::create([
            'tracking_id' => (string) Str::uuid(),
            'parent_subscriber_id' => $parentSubscriberId,
            'school_subscriber_id' => $schoolSubscriberId,
            'recipient_email' => $recipient,
            'subject' => $subject,
        ]);

        $mail = new CampaignMail(array_merge($data, [
            'title' => $subject,
            'template' => $template,
            'recipient' => $recipient,
        ]));

        $mail->withSymfonyMessage(static function (Email $message) use ($tracking): void {
            $message->getHeaders()->addTextHeader(
                'X-Mailgun-Variables',
                json_encode(['tracking_id' => $tracking->tracking_id], JSON_THROW_ON_ERROR),
            );
        });

        Mail::to($recipient)->send($mail);

        return $tracking;
    }
}
