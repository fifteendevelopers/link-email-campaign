<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\SendsCampaignEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolSubscriberRequest;
use App\Http\Requests\SendSchoolBookingEmailRequest;
use App\Http\Requests\SendSchoolEmailRequest;
use App\Models\SchoolSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SchoolSubscriberController extends Controller
{
    use SendsCampaignEmail;

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['sometimes', 'email', 'max:255'],
            'email_address' => ['sometimes', 'email', 'max:255'],
        ]);

        $emailAddress = $validated['email_address'] ?? $validated['email'] ?? null;

        $subscribers = SchoolSubscriber::query()
            ->when(
                $emailAddress,
                fn ($query, string $email) => $query->where('email_address', strtolower($email)),
            )
            ->first();

        if (! $subscribers) {
            return response()->json([
                'message' => 'School subscriber not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($subscribers);
    }

    public function upsert(SchoolSubscriberRequest $request): JsonResponse
    {
        return $this->persistSubscriber($request);
    }

    public function update(
        SchoolSubscriberRequest $request,
        SchoolSubscriber $schoolSubscriber,
    ): JsonResponse {
        $schoolSubscriber->update(
            $this->resetChangedCommunicationDates(
                $request->subscriberAttributes(),
                $schoolSubscriber,
            ),
        );

        return response()->json([
            'message' => 'School subscriber updated successfully.',
            'data' => $schoolSubscriber->fresh(),
        ]);
    }

    public function destroy(SchoolSubscriber $schoolSubscriber): Response
    {
        $schoolSubscriber->delete();

        return response()->noContent();
    }

    public function destroyByEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'delivery_id' => ['sometimes', 'string', 'max:255'],
        ]);

        $query = SchoolSubscriber::query()
            ->where('email_address', strtolower($validated['email']))
            ->when(
                $validated['delivery_id'] ?? null,
                fn ($builder, string $deliveryId) => $builder->where(
                    fn ($deliveryQuery) => $deliveryQuery
                        ->where('delivery_id_1', $deliveryId)
                        ->orWhere('delivery_id_2', $deliveryId)
                        ->orWhere('delivery_id_3', $deliveryId),
                ),
            );

        $deletedCount = $query->delete();

        if ($deletedCount === 0) {
            return response()->json(['message' => 'School subscriber not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'School subscriber deleted successfully.',
            'deleted_count' => $deletedCount,
        ]);
    }

    public function sendConsentCheck(SendSchoolEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Upcoming Bikeability - action required! Complete consent checks and finalise numbers for {$data['establishmentName']}",
            template: 'emails.school_comms.pre_course.consent_check',
            data: $data,
        );
    }

    public function sendConsentCheckReminder(SendSchoolEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Reminder: complete consent checks and finalise numbers for Bikeability at {$data['establishmentName']}",
            template: 'emails.school_comms.pre_course.consent_check_reminder',
            data: $data,
        );
    }

    public function sendConsentCheckOutstanding(SendSchoolEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Check your outstanding Bikeability consents for {$data['establishmentName']}",
            template: 'emails.school_comms.pre_course.consent_check_outstanding',
            data: $data,
        );
    }

    public function sendConsentCheckFinalReminder(SendSchoolEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Final reminder: Are you ready for Bikeability at {$data['establishmentName']}?",
            template: 'emails.school_comms.pre_course.consent_check_final_reminder',
            data: $data,
        );
    }

    public function sendBookingConfirmation(SendSchoolBookingEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Booking confirmation: Bikeability cycle training at {$data['establishmentName']}",
            template: 'emails.school_comms.booking_confirmation.booking_confirmation',
            data: $data,
        );
    }

    public function sendBookingConfirmationReminder(SendSchoolBookingEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Reminder: Booking confirmation for Bikeability cycle training at {$data['establishmentName']}",
            template: 'emails.school_comms.booking_confirmation.booking_confirmation_reminder',
            data: $data,
        );
    }

    public function sendBookingCompleted(SchoolSubscriberRequest $request): JsonResponse
    {
        return $this->persistSubscriber($request);
    }

    public function deliveryDeleted(SchoolSubscriberRequest $request): JsonResponse
    {
        $statusUpdates = $request->deliveryStatusUpdates();

        if ($statusUpdates === []) {
            throw ValidationException::withMessages([
                'CustomFields' => 'At least one deliveryStatus{segment} custom field is required.',
            ]);
        }

        $emailAddress = $request->validated('email_address');
        $updatedCount = DB::transaction(function () use ($emailAddress, $statusUpdates): int {
            $attributes = [];

            foreach ($statusUpdates as $segment => $deliveryStatus) {
                $attributes["delivery_status_{$segment}"] = $deliveryStatus;
            }

            return SchoolSubscriber::query()
                ->where('email_address', $emailAddress)
                ->update($attributes);
        });

        if ($updatedCount === 0) {
            return response()->json([
                'message' => 'School subscriber delivery not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'School subscriber delivery status updated successfully.',
            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function subscriberIdentity(array $attributes): array
    {
        if (isset($attributes['consent_id'])) {
            return ['consent_id' => $attributes['consent_id']];
        }

        return ['email_address' => $attributes['email_address']];
    }

    private function persistSubscriber(SchoolSubscriberRequest $request): JsonResponse
    {
        $attributes = $request->subscriberAttributes();
        $identity = $this->subscriberIdentity($attributes);
        $existingSubscriber = SchoolSubscriber::query()->where($identity)->first();

        $subscriber = SchoolSubscriber::query()->updateOrCreate(
            $identity,
            $this->resetChangedCommunicationDates($attributes, $existingSubscriber),
        );

        return response()->json([
            'message' => $subscriber->wasRecentlyCreated
                ? 'School subscriber created successfully.'
                : 'School subscriber updated successfully.',
            'data' => $subscriber->fresh(),
        ], $subscriber->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    /**
     * Clear delivery markers when a communication date changes so a reused
     * segment can send its new sequence.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function resetChangedCommunicationDates(
        array $attributes,
        ?SchoolSubscriber $subscriber,
    ): array {
        if ($subscriber === null) {
            return $attributes;
        }

        $communicationColumns = [
            'journey_confirmation_start_date' => 'journey_confirmation_sent_at',
            'pre_course_consent_check_comms_date' => 'pre_course_consent_check_sent_at',
            'outstanding_consent_check_comms_date' => 'outstanding_consent_check_sent_at',
            'final_consent_reminder_comms_date' => 'final_consent_reminder_sent_at',
        ];

        foreach (range(1, 3) as $segment) {
            foreach ($communicationColumns as $date => $sentAt) {
                $dateColumn = "{$date}_{$segment}";

                if (! array_key_exists($dateColumn, $attributes)) {
                    continue;
                }

                $storedDate = $subscriber->getAttribute($dateColumn)?->toDateString();

                if ($storedDate !== $attributes[$dateColumn]) {
                    $attributes["{$sentAt}_{$segment}"] = null;
                }
            }
        }

        return $attributes;
    }
}
