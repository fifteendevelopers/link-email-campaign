<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\CampaignMail;
use App\Models\Activity_log;
use App\Models\EmailTracking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;

class MailgunWebhookController extends Controller
{
    public function sendConsentConfirmationEmail(): JsonResponse
    {
        $recipient = 'mohatma@fifteen.co.uk';
        $subject = 'Consent Confirmation';

        $tracking = EmailTracking::create([
            'tracking_id' => (string) Str::uuid(),
            'recipient_email' => $recipient,
            'subject' => $subject,
        ]);

        $mail = new CampaignMail([
            'title' => $subject,
            'template' => 'emails.parent_comms.consent_confirmation.confirmation',
            'recipient' => $recipient,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

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
            'tracking_id' => $tracking->tracking_id,
        ]);
    }

    /**
     * Handle all Mailgun event webhooks.
     *
     * Configure in Mailgun: Sending -> Webhooks -> add the URL
     * https://your-domain/mailgun/webhook for each event type:
     * accepted, rejected, delivered, temporary_fail, permanent_fail,
     * opened, clicked, unsubscribed and complained.
     */
    public function webhook(Request $request): JsonResponse
    {
        if (! $this->verifySignature($request->input('signature', []))) {
            Activity_log::log($request->ip(), Activity_log::PROCESS_ERROR, 'MailgunWebhookController->webhook', 'Invalid Mailgun webhook signature');

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        // Mailgun sends the payload under 'event-data', but test webhooks
        // (and some newer payloads) use the camelCase 'eventData' key.
        $eventData = $request->input('event-data', $request->input('eventData', []));
        $event = $eventData['event'] ?? '';
        $trackingId = $eventData['user-variables']['tracking_id'] ?? null;

        if ($trackingId === null) {
            // Not one of our tracked emails - acknowledge so Mailgun stops retrying.
            return response()->json(['message' => 'Ignored']);
        }

        $tracking = EmailTracking::where('tracking_id', $trackingId)->first();
        if ($tracking === null) {
            Activity_log::log($request->ip(), Activity_log::PROCESS_ERROR, 'MailgunWebhookController->webhook', "Unknown Mailgun tracking id '{$trackingId}' for event '{$event}'");

            return response()->json(['message' => 'Unknown tracking id']);
        }

        $eventTime = isset($eventData['timestamp'])
            ? Carbon::createFromTimestamp((int) $eventData['timestamp'])
            : now();

        match ($event) {
            'accepted' => $tracking->recordAccepted($eventTime),
            'rejected' => $tracking->recordRejected($eventTime, $this->extractRejectReason($eventData)),
            'delivered' => $tracking->recordDelivered($eventTime),
            'opened' => $tracking->recordOpen($eventTime),
            'clicked' => $tracking->recordClick($eventTime, $eventData['url'] ?? null),
            'temporary_fail' => $tracking->recordTemporaryFailure($eventTime, $this->extractFailureReason($eventData)),
            'permanent_fail' => $tracking->recordPermanentFailure($eventTime, $this->extractFailureReason($eventData)),
            'complained' => $tracking->recordComplaint($eventTime),
            'unsubscribed' => $tracking->recordUnsubscribe($eventTime),
            default => Activity_log::log($request->ip(), Activity_log::PROCESS_INFO, 'MailgunWebhookController->webhook', "Unhandled Mailgun event '{$event}' for tracking id '{$trackingId}'"),
        };

        return response()->json(['message' => 'Ok']);
    }

    /**
     * Verify the webhook came from Mailgun using the signing key.
     *
     * @see https://documentation.mailgun.com/docs/mailgun/user-manual/tracking-messages/#securing-webhooks
     */
    private function verifySignature(array $signature): bool
    {
        $signingKey = config('services.mailgun.webhook_signing_key');
        if (empty($signingKey)) {
            return false;
        }

        $timestamp = $signature['timestamp'] ?? '';
        $token = $signature['token'] ?? '';
        $expected = $signature['signature'] ?? '';

        if ($timestamp === '' || $token === '' || $expected === '') {
            return false;
        }

        // Reject stale requests to prevent replay attacks.
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $computed = hash_hmac('sha256', $timestamp.$token, $signingKey);

        return hash_equals($computed, $expected);
    }

    private function extractFailureReason(array $eventData): ?string
    {
        $deliveryStatus = $eventData['delivery-status'] ?? [];

        return $deliveryStatus['description']
            ?? $deliveryStatus['message']
            ?? $eventData['reason']
            ?? null;
    }

    private function extractRejectReason(array $eventData): ?string
    {
        return $eventData['reject']['reason'] ?? $eventData['reason'] ?? null;
    }
}
