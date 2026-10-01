<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\SendsCampaignEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\ParentSubscriberRequest;
use App\Http\Requests\SendParentEmailRequest;
use App\Models\ParentSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
        $parentSubscriber->update($request->validated());

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
            'delivery_id' => ['sometimes', 'string', 'max:255'],
        ]);

        $query = ParentSubscriber::query()
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
            template: 'emails.parent_coms.consent_confirmation.confirmation',
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
            template: 'emails.parent_coms.pre_course.how_to_get_ready_for_bikeability',
            data: $request->templateData(),
        );
    }

    public function sendPreCourseFaq(SendParentEmailRequest $request): JsonResponse
    {
        return $this->send(
            recipient: $request->validated('recipient'),
            subject: 'Frequently Asked Questions, answered',
            template: 'emails.parent_coms.pre_course.faq',
            data: $request->templateData(),
        );
    }

    public function sendPreCourseReminder(SendParentEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Reminder: {$data['childName']} is booked for Bikeability cycle training soon",
            template: 'emails.parent_coms.pre_course.reminder',
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
            template: 'emails.parent_coms.post_course.rider_report',
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
            template: 'emails.parent_coms.post_course.parent_survey',
            data: $data,
        );
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

    private function persistSubscriber(ParentSubscriberRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $subscriber = ParentSubscriber::query()->updateOrCreate(
            $this->subscriberIdentity($attributes),
            $attributes,
        );

        return response()->json([
            'message' => $subscriber->wasRecentlyCreated
                ? 'Parent subscriber created successfully.'
                : 'Parent subscriber updated successfully.',
            'data' => $subscriber->fresh(),
        ], $subscriber->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    private function updateDeliveryStatuses(ParentSubscriberRequest $request): JsonResponse
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

            return ParentSubscriber::query()
                ->where('email_address', $emailAddress)
                ->update($attributes);
        });

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
