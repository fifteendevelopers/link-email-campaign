<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailLogController extends Controller
{
    public function index(): View
    {
        return view('email-logs.index');
    }

    public function show(EmailLog $emailLog): Response
    {
        $body = trim((string) $emailLog->body);

        if ($body === '') {
            $body = '<!doctype html><html lang="en"><body><p>No stored copy of this email was found.</p></body></html>';
        }

        return response($body, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function rows(Request $request): View
    {
        $search = strtolower(trim($request->string('search')->toString()));

        $logs = EmailLog::query()
            ->leftJoin('parent_subscribers as parents', 'parents.id', '=', 'email_log.parent_subscriber_id')
            ->leftJoin('school_subscribers as schools', 'schools.id', '=', 'email_log.school_subscriber_id')
            ->select([
                'email_log.id',
                'email_log.date',
                'email_log.from',
                'email_log.to',
                'email_log.subject',
                'parents.consent_id as consent_id',
                'parents.course_level as course_level',
                'parents.outcome_survey_course_level as course_survey_level',
            ])
            ->selectRaw('COALESCE(parents.delivery_id, schools.delivery_id) as delivery_id')
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';

                $query->where(function ($query) use ($like): void {
                    $query->where('email_log.to', 'like', $like)
                        ->orWhere('parents.email_address', 'like', $like)
                        ->orWhere('schools.email_address', 'like', $like)
                        ->orWhere('parents.consent_id', 'like', $like)
                        ->orWhere('parents.delivery_id', 'like', $like)
                        ->orWhere('schools.delivery_id', 'like', $like)
                        ->orWhere('parents.course_level', 'like', $like)
                        ->orWhere('parents.outcome_survey_course_level', 'like', $like);
                });
            })
            ->orderByDesc('email_log.date')
            ->orderByDesc('email_log.id')
            ->paginate(10)
            ->withQueryString();

        return view('email-logs.partials.table', [
            'logs' => $logs,
        ]);
    }
}
