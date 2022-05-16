<?php

namespace Petzsch\LaravelBtcpay\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyWebhookSignature
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param Closure                  $next
     *
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        try {
            // Verify signature
        } catch (Exception $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        return $next($request);
    }
}
