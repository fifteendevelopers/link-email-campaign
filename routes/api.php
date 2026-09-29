<?php

use App\Http\Controllers\ParentEmailController;
use App\Http\Controllers\SchoolEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Parent Communication Emails
|--------------------------------------------------------------------------
|
| Each endpoint accepts a JSON payload, renders the matching Blade email
| template with the supplied data and sends it via Mailgun with open /
| click tracking attached.
|
| Payload:
| {
|     "recipient": "parent@example.com",
|     "child_name": "John Doe",
|     "parent_name": "Jane Doe",
|     "establishment_name": "Anytown Primary School",
|     "provider_name": "Anytown Cycling Club",
|     "provider_public_email_address": "info@anytowncyclingclub.org",
|     "outcomes_url": "https://club.bikeability.org.uk",   (optional, rider report)
|     "survey_link_url": "https://survey.example.com"      (optional, parent survey)
| }
|
*/

Route::prefix('emails/parent')->name('api.emails.parent.')->group(function (): void {
    Route::post('consent-confirmation', [ParentEmailController::class, 'sendConsentConfirmation'])
        ->name('consent-confirmation');

    Route::post('pre-course/how-to-get-ready', [ParentEmailController::class, 'sendPreCourseHowToGetReady'])
        ->name('pre-course.how-to-get-ready');

    Route::post('pre-course/faq', [ParentEmailController::class, 'sendPreCourseFaq'])
        ->name('pre-course.faq');

    Route::post('pre-course/reminder', [ParentEmailController::class, 'sendPreCourseReminder'])
        ->name('pre-course.reminder');

    Route::post('post-course/rider-report', [ParentEmailController::class, 'sendPostCourseRiderReport'])
        ->name('post-course.rider-report');

    Route::post('post-course/parent-survey', [ParentEmailController::class, 'sendPostCourseParentSurvey'])
        ->name('post-course.parent-survey');
});

/*
|--------------------------------------------------------------------------
| School Communication Emails
|--------------------------------------------------------------------------
|
| Payload:
| {
|     "recipient": "office@anytownprimary.sch.uk",
|     "establishment_name": "Anytown Primary School",
|     "provider_name": "Anytown Cycling Club",
|     "provider_contact_email": "info@anytowncyclingclub.org",
|     "booking_link": "https://link.bikeability.org.uk/booking/123",
|     "first_name": "Jane"    (required for booking confirmation emails)
| }
|
*/

Route::prefix('emails/school')->name('api.emails.school.')->group(function (): void {
    Route::post('pre-course/consent-check', [SchoolEmailController::class, 'sendConsentCheck'])
        ->name('pre-course.consent-check');

    Route::post('pre-course/consent-check-reminder', [SchoolEmailController::class, 'sendConsentCheckReminder'])
        ->name('pre-course.consent-check-reminder');

    Route::post('pre-course/consent-check-outstanding', [SchoolEmailController::class, 'sendConsentCheckOutstanding'])
        ->name('pre-course.consent-check-outstanding');

    Route::post('pre-course/consent-check-final-reminder', [SchoolEmailController::class, 'sendConsentCheckFinalReminder'])
        ->name('pre-course.consent-check-final-reminder');

    Route::post('post-course/booking-confirmation', [SchoolEmailController::class, 'sendBookingConfirmation'])
        ->name('post-course.booking-confirmation');

    Route::post('post-course/booking-confirmation-reminder', [SchoolEmailController::class, 'sendBookingConfirmationReminder'])
        ->name('post-course.booking-confirmation-reminder');
});
