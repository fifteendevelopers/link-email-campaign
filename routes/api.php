<?php

use App\Http\Controllers\Api\ParentSubscriberController;
use App\Http\Controllers\Api\SchoolSubscriberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1', 'campaign.api'])->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Parent Subscriber
    |--------------------------------------------------------------------------
    */

    Route::prefix('subscribers/parents')->name('api.subscribers.parents.')->group(function (): void {
        Route::get('/', [ParentSubscriberController::class, 'show'])->name('show');
        Route::post('/', [ParentSubscriberController::class, 'upsert'])->name('upsert');
        Route::delete('/', [ParentSubscriberController::class, 'destroyByEmail'])->name('destroy-by-email');
        // Route::get('{parentSubscriber}', [ParentSubscriberController::class, 'show'])->name('show');
        Route::patch('{parentSubscriber}', [ParentSubscriberController::class, 'update'])->name('update');
        Route::delete('{parentSubscriber}', [ParentSubscriberController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | School Subscriber
    |--------------------------------------------------------------------------
    */

    Route::prefix('subscribers/schools')->name('api.subscribers.schools.')->group(function (): void {
        Route::get('/', [SchoolSubscriberController::class, 'show'])->name('show');
        Route::post('/', [SchoolSubscriberController::class, 'upsert'])->name('upsert');
        Route::delete('/', [SchoolSubscriberController::class, 'destroyByEmail'])->name('destroy-by-email');
        // Route::get('{schoolSubscriber}', [SchoolSubscriberController::class, 'show'])->name('show');
        Route::patch('{schoolSubscriber}', [SchoolSubscriberController::class, 'update'])->name('update');
        Route::delete('{schoolSubscriber}', [SchoolSubscriberController::class, 'destroy'])->name('destroy');
    });

});

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
    Route::post('consent-confirmation', [ParentSubscriberController::class, 'sendConsentConfirmation'])
        ->name('consent-confirmation');

    Route::post('join-bike-club', [ParentSubscriberController::class, 'joinBikeClub'])
        ->name('join-bike-club');

    Route::post('course-completion', [ParentSubscriberController::class, 'sendCourseCompletion'])
        ->name('course-completion');

    Route::post('delivery-deleted', [ParentSubscriberController::class, 'deliveryDeleted'])
        ->name('delivery-deleted-notice');

    Route::post('consent-removed', [ParentSubscriberController::class, 'consentRemoved'])
        ->name('consent-removed');

    /*
    * @TODO: This routes are not necessary as we will be setting the dates for the emails automatically

    Route::post('pre-course/how-to-get-ready', [ParentSubscriberController::class, 'sendPreCourseHowToGetReady'])
        ->name('pre-course.how-to-get-ready');

    Route::post('pre-course/faq', [ParentSubscriberController::class, 'sendPreCourseFaq'])
        ->name('pre-course.faq');

    Route::post('pre-course/reminder', [ParentSubscriberController::class, 'sendPreCourseReminder'])
        ->name('pre-course.reminder');

    Route::post('post-course/rider-report', [ParentSubscriberController::class, 'sendPostCourseRiderReport'])
        ->name('post-course.rider-report');

    Route::post('post-course/parent-survey', [ParentSubscriberController::class, 'sendPostCourseParentSurvey'])
        ->name('post-course.parent-survey'); */
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
    Route::post('booking-completed', [SchoolSubscriberController::class, 'sendBookingCompleted'])
        ->name('booking-completed');

    Route::post('delivery-deleted', [SchoolSubscriberController::class, 'deliveryDeleted'])
        ->name('delivery-deleted');

    /* Route::post('pre-course/consent-check-reminder', [SchoolSubscriberController::class, 'sendConsentCheckReminder'])
        ->name('pre-course.consent-check-reminder');

    Route::post('pre-course/consent-check-outstanding', [SchoolSubscriberController::class, 'sendConsentCheckOutstanding'])
        ->name('pre-course.consent-check-outstanding');

    Route::post('pre-course/consent-check-final-reminder', [SchoolSubscriberController::class, 'sendConsentCheckFinalReminder'])
        ->name('pre-course.consent-check-final-reminder');

    Route::post('post-course/booking-confirmation', [SchoolSubscriberController::class, 'sendBookingConfirmation'])
        ->name('post-course.booking-confirmation');

    Route::post('post-course/booking-confirmation-reminder', [SchoolSubscriberController::class, 'sendBookingConfirmationReminder'])
        ->name('post-course.booking-confirmation-reminder'); */
});
