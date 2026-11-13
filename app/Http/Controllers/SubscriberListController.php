<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDeliveryStatusRequest;
use App\Http\Requests\UpdateSendDateRequest;
use App\Models\EmailLog;
use App\Models\ParentSubscriber;
use App\Models\SchoolSubscriber;
use App\Services\CampaignEmailSender;
use App\Support\SubscriberCommunications;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SubscriberListController extends Controller
{
    public function parents(): View
    {
        return $this->page('parents');
    }

    public function schools(): View
    {
        return $this->page('schools');
    }

    public function rows(Request $request, string $audience): View
    {
        $definition = SubscriberCommunications::forAudience($audience);
        $model = $definition['model'];
        $statuses = SubscriberCommunications::statuses($audience);
        $status = $request->string('status')->toString();
        $search = strtolower(trim($request->string('search')->toString() ?: $request->string('email')->toString()));

        $subscribers = $model::query()
            ->when(
                $search === '' && array_key_exists($status, $statuses),
                fn ($query) => $query->where('delivery_status', $status),
            )
            ->when($search !== '', function ($query) use ($search, $audience): void {
                $like = '%'.addcslashes($search, '%_\\').'%';

                $query->where(function ($query) use ($like, $audience): void {
                    $query->where('email_address', 'like', $like)
                        ->orWhere('delivery_id', 'like', $like);

                    if ($audience === 'parents') {
                        $query->orWhere('consent_id', 'like', $like)
                            ->orWhere('child_name', 'like', $like);
                    }
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('subscribers.partials.table', [
            'audience' => $audience,
            'subscribers' => $subscribers,
            'communications' => $definition['communications'],
            'statuses' => $statuses,
        ]);
    }

    public function updateStatus(UpdateDeliveryStatusRequest $request, string $audience, int $subscriber): JsonResponse
    {
        $subscriberModel = $this->findSubscriber($audience, $subscriber);
        $status = $request->string('status')->toString();

        $subscriberModel->forceFill([
            'delivery_status' => $status,
        ])->save();

        return response()->json([
            'message' => 'Delivery status updated.',
            'status' => $status,
        ]);
    }

    public function updateSendDate(UpdateSendDateRequest $request, string $audience, int $subscriber): JsonResponse
    {
        $subscriberModel = $this->findSubscriber($audience, $subscriber);
        $column = $request->string('column')->toString();
        $value = $request->input('value');

        $subscriberModel->forceFill([
            $column => $value,
        ])->save();

        return response()->json([
            'message' => 'Send date updated.',
            'column' => $column,
            'value' => $subscriberModel->getAttribute($column)?->toDateString(),
        ]);
    }

    public function sendDue(string $audience): JsonResponse
    {
        $definition = SubscriberCommunications::forAudience($audience);

        try {
            $definition['job']::dispatchSync();
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'Some '.$definition['noun'].' emails could not be sent.',
                'error' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Due '.$definition['noun'].' emails have been sent.',
        ]);
    }

    public function sendOne(CampaignEmailSender $emailSender, string $audience, int $subscriberId, string $sentColumn): JsonResponse
    {
        $definition = SubscriberCommunications::forAudience($audience);
        $communication = SubscriberCommunications::findBySentColumn($audience, $sentColumn);

        if ($communication === null) {
            return response()->json([
                'message' => 'That email could not be found.',
            ], 404);
        }

        $subscriber = $this->findSubscriber($audience, $subscriberId);
        $deliveryStatus = strtolower(trim((string) $subscriber->delivery_status));

        if (in_array($deliveryStatus, ['deleted', 'consent removed'], true)) {
            return response()->json([
                'message' => 'Emails are not sent when the delivery status is '.$deliveryStatus.'.',
            ], 422);
        }

        if ($subscriber->getAttribute($sentColumn) !== null) {
            return response()->json([
                'message' => 'This email has already been sent.',
            ], 422);
        }

        if (blank($subscriber->email_address)) {
            return response()->json([
                'message' => 'This subscriber has no email address.',
            ], 422);
        }

        try {
            $data = SubscriberCommunications::templateData($subscriber);
            $subject = $communication['subject']($data);
            $emailSender->send(
                recipient: $subscriber->email_address,
                subject: $subject,
                template: $communication['template'],
                data: $data,
                parentSubscriberId: $audience === 'parents' ? $subscriber->id : null,
                schoolSubscriberId: $audience === 'schools' ? $subscriber->id : null,
                sentColumn: $sentColumn,
            );
            $subscriber->forceFill([$sentColumn => now()])->save();
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'The '.$communication['label'].' email could not be sent.',
                'error' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => $communication['label'].' email sent.',
            'sent_at' => SubscriberCommunications::formatSentAt($subscriber->getAttribute($sentColumn)),
        ]);
    }

    public function email(string $audience, int $subscriber, string $sentColumn): JsonResponse
    {
        $definition = SubscriberCommunications::forAudience($audience);

        if (! in_array($sentColumn, SubscriberCommunications::sentColumns($audience), true)) {
            return response()->json([
                'message' => 'That email could not be found.',
            ], 404);
        }

        $this->findSubscriber($audience, $subscriber);

        $log = EmailLog::query()
            ->where($definition['subscriberKey'], $subscriber)
            ->where('sent_column', $sentColumn)
            ->orderByDesc('id')
            ->first();

        if ($log === null) {
            return response()->json([
                'message' => 'No stored copy of this email was found.',
            ], 404);
        }

        return response()->json([
            'id' => $log->id,
            'date' => SubscriberCommunications::formatSentAt($log->date),
            'from' => $log->from,
            'to' => $log->to,
            'cc' => $log->cc,
            'bcc' => $log->bcc,
            'subject' => $log->subject,
            'body' => $log->body,
        ]);
    }

    private function page(string $audience): View
    {
        $definition = SubscriberCommunications::forAudience($audience);

        return view('subscribers.index', [
            'audience' => $audience,
            'title' => $definition['label'],
            'noun' => $definition['noun'],
            'statuses' => SubscriberCommunications::statuses($audience),
            'communicationCount' => count($definition['communications']),
        ]);
    }

    private function findSubscriber(string $audience, int $subscriber): ParentSubscriber|SchoolSubscriber
    {
        $model = SubscriberCommunications::forAudience($audience)['model'];

        return $model::query()->findOrFail($subscriber);
    }
}
