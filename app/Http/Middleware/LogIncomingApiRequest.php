<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class LogIncomingApiRequest
{
    private const STARTED_AT_ATTRIBUTE = 'api_log_started_at';

    private const REDACTED = '[redacted]';

    private const MAX_RESPONSE_LENGTH = 20000;

    /**
     * @var list<string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'secret',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'api_key',
        'api_secret',
        'php_auth_pw',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set(self::STARTED_AT_ATTRIBUTE, hrtime(true));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $startedAt = $request->attributes->get(self::STARTED_AT_ATTRIBUTE);

            ApiLog::query()->create([
                'method' => $request->method(),
                'path' => $request->path(),
                'route_name' => $request->route()?->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'query' => $this->nullableArray($this->redact($request->query())),
                'payload' => $this->nullableArray($this->redact($this->payload($request))),
                'status_code' => $response->getStatusCode(),
                'response' => $this->responseBody($response),
                'duration_ms' => is_int($startedAt)
                    ? (int) ((hrtime(true) - $startedAt) / 1_000_000)
                    : null,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        if ($request->isJson()) {
            $payload = $request->json()->all();

            return is_array($payload) ? $payload : [];
        }

        return $request->request->all();
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function redact(array $values): array
    {
        $redacted = [];

        foreach ($values as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $redacted[$key] = self::REDACTED;

                continue;
            }

            $redacted[$key] = is_array($value) ? $this->redact($value) : $value;
        }

        return $redacted;
    }

    private function isSensitiveKey(string $key): bool
    {
        return in_array(strtolower($key), self::SENSITIVE_KEYS, true);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>|null
     */
    private function nullableArray(array $values): ?array
    {
        return $values === [] ? null : $values;
    }

    private function responseBody(Response $response): ?string
    {
        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return null;
        }

        if (strlen($content) <= self::MAX_RESPONSE_LENGTH) {
            return $content;
        }

        return substr($content, 0, self::MAX_RESPONSE_LENGTH).'…';
    }
}
