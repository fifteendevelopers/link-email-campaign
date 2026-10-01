<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateCampaignApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUsername = config('services.link_email_campaign.username');
        $expectedSecret = config('services.link_email_campaign.secret');

        if (! is_string($expectedUsername) || ! is_string($expectedSecret) || $expectedSecret === '') {
            return $this->unauthenticatedResponse();
        }

        $credentialsAreValid = hash_equals($expectedUsername, (string) $request->getUser())
            && hash_equals($expectedSecret, (string) $request->getPassword());

        if (! $credentialsAreValid) {
            return $this->unauthenticatedResponse();
        }

        return $next($request);
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        return new JsonResponse(
            data: ['message' => 'Unauthenticated.'],
            status: Response::HTTP_UNAUTHORIZED,
            headers: ['WWW-Authenticate' => 'Basic realm="Campaign API"'],
        );
    }
}
