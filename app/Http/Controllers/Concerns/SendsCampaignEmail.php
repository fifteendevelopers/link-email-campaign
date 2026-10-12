<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Services\CampaignEmailSender;
use Illuminate\Http\JsonResponse;

trait SendsCampaignEmail
{
    /**
     * Send a campaign email with Mailgun tracking attached.
     *
     * @param  array<string, string>  $data
     */
    private function send(string $recipient, string $subject, string $template, array $data): JsonResponse
    {
        $tracking = app(CampaignEmailSender::class)->send(
            recipient: $recipient,
            subject: $subject,
            template: $template,
            data: $data,
        );

        return response()->json([
            'message' => 'Email sent successfully.',
            'recipient' => $recipient,
            'subject' => $subject,
            'tracking_id' => $tracking->tracking_id,
        ]);
    }
}
