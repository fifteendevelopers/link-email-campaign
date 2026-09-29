<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Mail\CampaignMail;
use App\Models\EmailTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;

trait SendsCampaignEmail
{
    /**
     * Send a campaign email with Mailgun tracking attached.
     *
     * @param  array<string, string>  $data
     */
    private function send(string $recipient, string $subject, string $template, array $data): JsonResponse
    {
        $tracking = EmailTracking::create([
            'tracking_id' => (string) Str::uuid(),
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
                json_encode(['tracking_id' => $tracking->tracking_id], JSON_THROW_ON_ERROR)
            );
        });

        Mail::to($recipient)->send($mail);

        return response()->json([
            'message' => 'Email sent successfully.',
            'recipient' => $recipient,
            'subject' => $subject,
            'tracking_id' => $tracking->tracking_id,
        ]);
    }
}
