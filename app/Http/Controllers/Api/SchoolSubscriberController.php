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
use Illuminate\Validation\ValidationException;

class SchoolSubscriberController extends Controller
{
    use SendsCampaignEmail;

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['sometimes', 'email', 'max:255'],
            'email_address' => ['sometimes', 'email', 'max:255'],
            'delivery_id' => ['sometimes', 'integer', 'min:1'],
        ]);

        $emailAddress = $validated['email_address'] ?? $validated['email'] ?? null;

        $subscribers = SchoolSubscriber::query()
            ->when(
                $emailAddress,
                fn ($query, string $email) => $query->where('email_address', strtolower($email)),
            )
            ->when(
                $validated['delivery_id'] ?? null,
                fn ($query, int $deliveryId) => $query->where('delivery_id', $deliveryId),
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
            'delivery_id' => ['sometimes', 'integer', 'min:1'],
        ]);

        $query = SchoolSubscriber::query()
            ->where('email_address', strtolower($validated['email']))
            ->when(
                $validated['delivery_id'] ?? null,
                fn ($builder, int $deliveryId) => $builder->where('delivery_id', $deliveryId),
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
        $deliveryStatus = $request->deliveryStatus();

        if ($deliveryStatus === null) {
            throw ValidationException::withMessages([
                'CustomFields' => 'A deliveryStatus custom field is required.',
            ]);
        }

        $emailAddress = $request->validated('email_address');
        $updatedCount = SchoolSubscriber::query()
            ->where('email_address', $emailAddress)
            ->when(
                $request->validated('delivery_id'),
                fn ($query, $deliveryId) => $query->where('delivery_id', $deliveryId),
            )
            ->update(['delivery_status' => $deliveryStatus]);

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
        $identity = [
            'email_address' => $attributes['email_address'],
        ];

        if (isset($attributes['delivery_id'])) {
            $identity['delivery_id'] = $attributes['delivery_id'];
        }

        return $identity;
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
     * Clear a sent marker when its communication date changes so the new
     * date can send again.
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

        foreach ($communicationColumns as $date => $sentAt) {
            if (! array_key_exists($date, $attributes)) {
                continue;
            }

            $storedDate = $subscriber->getAttribute($date)?->toDateString();

            if ($storedDate !== $attributes[$date]) {
                $attributes[$sentAt] = null;
            }
        }

        return $attributes;
    }
}
