<?php

use App\Http\Controllers\MailgunWebhookController;
use App\Models\EmailTracking;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/mailgun/webhook', [MailgunWebhookController::class, 'webhook'])
    ->name('mailgun.webhook');

Route::get('test', function () {
    /* Mail::raw('This is a test email sent through Mailgun SMTP.', function (Message $message): void {
            $message
                ->to('mohatma@fifteen.co.uk')
                ->subject('Mailgun SMTP test');
        });

        return response()->json([
            'message' => 'Test email sent successfully.',
            'recipient' => 'mohatma@fifteen.co.uk',
        ]); */

    $subject = 'Mailgun SMTP test';

    $tracking = EmailTracking::create([
        'tracking_id' => (string) Str::uuid(),
        'recipient_email' => 'mohatma@fifteen.co.uk',
        'subject' => $subject,
    ]);

    // Open/click tracking only works with HTML emails: Mailgun injects a
    // tracking pixel into the HTML body and rewrites <a> links. Plain-text
    // emails (Mail::raw) can never trigger 'opened' or 'clicked' events.
    $htmlBody = <<<'HTML'
            <html>
            <body>
                <p>This is a test email sent through Mailgun SMTP.</p>
                <p><a href="https://bikeability-campaign.fifteen.dev">Click here to test click tracking</a></p>
            </body>
            </html>
            HTML;

    Mail::html($htmlBody, function (Message $message) use ($subject, $tracking): void {
        $message
            ->to('mohatma@fifteen.co.uk')
            ->subject($subject);

        // Mailgun exposes this header back to us as 'user-variables'
        // on every webhook event, which is how webhook() finds the
        // matching EmailTracking record.
        $message->getHeaders()->addTextHeader(
            'X-Mailgun-Variables',
            json_encode(['tracking_id' => $tracking->tracking_id], JSON_THROW_ON_ERROR)
        );
    });

    return response()->json([
        'message' => 'Test email sent successfully.',
        'recipient' => 'mohatma@fifteen.co.uk',
        'tracking_id' => $tracking->tracking_id,
    ]);
});

/* Pre Course Emails */
Route::get('/consent-confirmation-email', [MailgunWebhookController::class, 'sendConsentConfirmationEmail'])
    ->name('emails.consent-confirmation.send');

Route::get('/join-bike-club-email', function () {
    return view('emails.parent_comms.consent_confirmation.bike_club');
});

Route::get('/pre-course-email', function () {
    return view('emails.parent_comms.pre_course.how_to_get_ready_for_bikeability');
});

Route::get('/pre-course-faq-email', function () {
    return view('emails.parent_comms.pre_course.faq');
});

Route::get('/pre-course-reminder-email', function () {
    return view('emails.parent_comms.pre_course.reminder');
});

/* Post Course Emails */
Route::get('/post-course-rider-report-email', function () {
    return view('emails.parent_comms.post_course.rider_report');
});

Route::get('/post-course-parent-survey-email', function () {
    return view('emails.parent_comms.post_course.parent_survey');
});

/* School Pre Course Emails */
Route::get('/school-pre-course-consent-check-email', function () {
    return view('emails.school_comms.pre_course.consent_check');
});

Route::get('/school-pre-course-consent-check-reminder-email', function () {
    return view('emails.school_comms.pre_course.consent_check_reminder');
});

Route::get('/school-pre-course-consent-check-outstanding-email', function () {
    return view('emails.school_comms.pre_course.consent_check_outstanding');
});

Route::get('/school-pre-course-consent-check-final-reminder-email', function () {
    return view('emails.school_comms.pre_course.consent_check_final_reminder');
});

/* School Post Course Emails */
Route::get('/school-post-course-booking-confirmation-email', function () {
    return view('emails.school_comms.booking_confirmation.booking_confirmation');
});

Route::get('/school-post-course-booking-confirmation-reminder-email', function () {
    return view('emails.school_comms.booking_confirmation.booking_confirmation_reminder');
});
