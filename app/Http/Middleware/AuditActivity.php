<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class AuditActivity
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user();

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            if ($actor instanceof User) {
                $statusCode = $exception instanceof HttpExceptionInterface
                    ? $exception->getStatusCode()
                    : match (true) {
                        $exception instanceof ValidationException => $exception->status,
                        $exception instanceof ModelNotFoundException => 404,
                        default => 500,
                    };

                $this->auditLogger->log($request, 'ACCOUNT_REQUEST', $actor, statusCode: $statusCode);
            }

            throw $exception;
        }

        if ($actor instanceof User) {
            $this->auditLogger->log($request, 'ACCOUNT_REQUEST', $actor, statusCode: $response->getStatusCode());
        }

        return $response;
    }
}
