<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\SendsCampaignEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\ParentSubscriberRequest;
use App\Http\Requests\SendParentEmailRequest;
use App\Models\ParentSubscriber;
use App\Support\SubscriberCommunications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ParentSubscriberController extends Controller
{
    use SendsCampaignEmail;

    public function show(Request $request): JsonResponse
    {
        /* return response()->json([
            'message' => 'Parent subscriber API is working.',
            'data' => $request->all(),
        ]); */

        $validated = $request->validate([
            'email' => ['sometimes', 'email', 'max:255'],
            'email_address' => ['sometimes', 'email', 'max:255'],
        ]);

        $emailAddress = $validated['email_address'] ?? $validated['email'] ?? null;

        $subscribers = ParentSubscriber::query()
            ->when(
                $emailAddress,
                fn ($query, string $email) => $query->where('email_address', strtolower($email)),
            )
            ->first();

        if (! $subscribers) {
            return response()->json([
                'message' => 'Parent subscriber not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($subscribers);
    }

    public function upsert(ParentSubscriberRequest $request): JsonResponse
    {
        return $this->persistSubscriber($request);
    }

    public function update(
        ParentSubscriberRequest $request,
        ParentSubscriber $parentSubscriber,
    ): JsonResponse {
        $parentSubscriber->update($this->attributesForSave(
            $request->subscriberAttributes(),
            $parentSubscriber,
        ));

        return response()->json([
            'message' => 'Parent subscriber updated successfully.',
            'data' => $parentSubscriber->fresh(),
        ]);
    }

    public function destroy(ParentSubscriber $parentSubscriber): Response
    {
        $parentSubscriber->delete();

        return response()->noContent();
    }

    public function destroyByEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'delivery_id' => ['sometimes', 'integer', 'min:1'],
        ]);

        $query = ParentSubscriber::query()
            ->where('email_address', strtolower($validated['email']))
            ->when(
                $validated['delivery_id'] ?? null,
                fn ($builder, int $deliveryId) => $builder->where('delivery_id', $deliveryId),
            );

        $deletedCount = $query->delete();

        if ($deletedCount === 0) {
            return response()->json(['message' => 'Parent subscriber not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Parent subscriber deleted successfully.',
            'deleted_count' => $deletedCount,
        ]);
    }

    public function sendConsentConfirmation(SendParentEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "We've received {$data['childName']}'s Bikeability consent form",
            template: 'emails.parent_comms.consent_confirmation.confirmation',
            data: $data,
        );
    }

    public function joinBikeClub(ParentSubscriberRequest $request): JsonResponse
    {
        return $this->persistSubscriber($request);
    }

    public function sendCourseCompletion(ParentSubscriberRequest $request): JsonResponse
    {
        return $this->persistSubscriber($request);
    }

    public function deliveryDeleted(ParentSubscriberRequest $request): JsonResponse
    {
        return $this->updateDeliveryStatuses($request);
    }

    public function consentRemoved(ParentSubscriberRequest $request): JsonResponse
    {
        return $this->updateDeliveryStatuses($request);
    }

    public function sendPreCourseHowToGetReady(SendParentEmailRequest $request): JsonResponse
    {
        return $this->send(
            recipient: $request->validated('recipient'),
            subject: 'How to get ready for Bikeability',
            template: 'emails.parent_comms.pre_course.how_to_get_ready_for_bikeability',
            data: $request->templateData(),
        );
    }

    public function sendPreCourseFaq(SendParentEmailRequest $request): JsonResponse
    {
        return $this->send(
            recipient: $request->validated('recipient'),
            subject: 'Frequently Asked Questions, answered',
            template: 'emails.parent_comms.pre_course.faq',
            data: $request->templateData(),
        );
    }

    public function sendPreCourseReminder(SendParentEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Reminder: {$data['childName']} is booked for Bikeability cycle training soon",
            template: 'emails.parent_comms.pre_course.reminder',
            data: $data,
        );
    }

    public function sendPostCourseRiderReport(SendParentEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();
        $data['outcomesURL'] ??= config('link.club.base_url', 'https://club.bikeability.org.uk');

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "{$data['childName']} did it!",
            template: 'emails.parent_comms.post_course.rider_report',
            data: $data,
        );
    }

    public function sendPostCourseParentSurvey(SendParentEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();
        $data['surveyLinkUrl'] ??= config('ridersurvey.base_url', '#');

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: 'Please share your views',
            template: 'emails.parent_comms.post_course.parent_survey',
            data: $data,
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function subscriberIdentity(array $attributes): array
    {
        if (isset($attributes['consent_id'], $attributes['email_address'])) {
            return [
                'consent_id' => $attributes['consent_id'],
                'email_address' => $attributes['email_address'],
            ];
        }

        if (isset($attributes['consent_id'])) {
            return ['consent_id' => $attributes['consent_id']];
        }

        return ['email_address' => $attributes['email_address']];
    }

    private function persistSubscriber(ParentSubscriberRequest $request): JsonResponse
    {
        $attributes = $request->subscriberAttributes();
        $identity = $this->subscriberIdentity($attributes);
        $existingSubscriber = ParentSubscriber::query()->where($identity)->first();

        $subscriber = ParentSubscriber::query()->updateOrCreate(
            $identity,
            $this->attributesForSave($attributes, $existingSubscriber),
        );

        return response()->json([
            'message' => $subscriber->wasRecentlyCreated
                ? 'Parent subscriber created successfully.'
                : 'Parent subscriber updated successfully.',
            'data' => $subscriber->fresh(),
        ], $subscriber->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    /**
     * Store each rider-report start date, derive the survey date, then clear sent markers whose dates changed.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function attributesForSave(array $attributes, ?ParentSubscriber $subscriber): array
    {
        $courseLevel = array_key_exists('course_level', $attributes)
            ? (is_scalar($attributes['course_level']) ? (string) $attributes['course_level'] : null)
            : $subscriber?->course_level;

        $attributes = $this->fillMissingRiderStartDates($attributes, $subscriber, $courseLevel);

        return $this->resetChangedCommunicationDates(
            SubscriberCommunications::scheduleOutcomeDates($attributes, $courseLevel, $subscriber),
            $subscriber,
        );
    }

    /**
     * When a course becomes combined after one rider-report date was stored,
     * copy that date onto the level that does not have one yet.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function fillMissingRiderStartDates(
        array $attributes,
        ?ParentSubscriber $subscriber,
        ?string $courseLevel,
    ): array {
        if (
            $subscriber === null
            || ! array_key_exists('course_level', $attributes)
            || array_key_exists('outcomes_journey_start_date', $attributes)
            || array_key_exists('outcomes_journey_level_1_start_date', $attributes)
            || array_key_exists('outcomes_journey_level_2_start_date', $attributes)
            || ! SubscriberCommunications::isCombinedCourse($courseLevel)
        ) {
            return $this->fillSingleRiderStartDate($attributes, $subscriber, $courseLevel);
        }

        $knownDate = $subscriber->outcomes_journey_start_date?->toDateString();

        foreach (['1', '2'] as $level) {
            $storedDate = $subscriber->getAttribute(SubscriberCommunications::riderStartColumn($level));

            if ($storedDate !== null) {
                $knownDate = $storedDate->toDateString();
                break;
            }
        }

        if ($knownDate === null) {
            return $attributes;
        }

        foreach (['1', '2'] as $level) {
            $column = SubscriberCommunications::riderStartColumn($level);

            if (array_key_exists($column, $attributes) || $subscriber->getAttribute($column) !== null) {
                continue;
            }

            $attributes[$column] = $knownDate;
        }

        return $attributes;
    }

    /**
     * When a course stops being combined, keep its rider report on the single date column.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function fillSingleRiderStartDate(
        array $attributes,
        ?ParentSubscriber $subscriber,
        ?string $courseLevel,
    ): array {
        if (
            $subscriber === null
            || ! array_key_exists('course_level', $attributes)
            || SubscriberCommunications::isCombinedCourse($courseLevel)
            || array_key_exists('outcomes_journey_start_date', $attributes)
            || $subscriber->outcomes_journey_start_date !== null
        ) {
            return $attributes;
        }

        foreach (SubscriberCommunications::includedLevels($courseLevel) as $level) {
            $storedDate = $subscriber->getAttribute(SubscriberCommunications::riderStartColumn($level));

            if ($storedDate === null) {
                continue;
            }

            $attributes['outcomes_journey_start_date'] = $storedDate->toDateString();
            break;
        }

        return $attributes;
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
        ?ParentSubscriber $subscriber,
    ): array {
        if ($subscriber === null) {
            return $attributes;
        }

        $communicationColumns = [
            'journey_confirmation_start_date' => ['journey_confirmation_sent_at'],
            'cycle_and_helmet_check_comms_date' => ['cycle_and_helmet_check_sent_at'],
            'pre_course_faq_comms_date' => ['pre_course_faq_sent_at'],
            'outcomes_journey_start_date' => ['outcomes_journey_sent_at'],
            'outcomes_journey_level_1_start_date' => ['outcomes_journey_level_1_sent_at'],
            'outcomes_journey_level_2_start_date' => ['outcomes_journey_level_2_sent_at'],
            'outcome_survey_comms_date' => ['outcome_survey_sent_at'],
        ];

        foreach ($communicationColumns as $date => $sentColumns) {
            if (! array_key_exists($date, $attributes)) {
                continue;
            }

            $storedDate = $subscriber->getAttribute($date)?->toDateString();

            if ($storedDate === $attributes[$date]) {
                continue;
            }

            foreach ($sentColumns as $sentAt) {
                $attributes[$sentAt] = null;
            }
        }

        return $attributes;
    }

    private function updateDeliveryStatuses(ParentSubscriberRequest $request): JsonResponse
    {
        $deliveryStatus = $request->deliveryStatus();

        if ($deliveryStatus === null) {
            throw ValidationException::withMessages([
                'CustomFields' => 'A deliveryStatus custom field is required.',
            ]);
        }

        $emailAddress = $request->validated('email_address');
        $updatedCount = ParentSubscriber::query()
            ->where('email_address', $emailAddress)
            ->when(
                $request->validated('consent_id'),
                fn ($query, $consentId) => $query->where('consent_id', $consentId),
            )
            ->when(
                $request->validated('delivery_id'),
                fn ($query, $deliveryId) => $query->where('delivery_id', $deliveryId),
            )
            ->update(['delivery_status' => $deliveryStatus]);

        if ($updatedCount === 0) {
            return response()->json([
                'message' => 'Parent subscriber delivery not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Parent subscriber delivery status updated successfully.',
            'updated_count' => $updatedCount,
        ]);
    }
}
