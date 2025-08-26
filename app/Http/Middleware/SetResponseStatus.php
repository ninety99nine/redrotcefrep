<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SetResponseStatus
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if the response is a JsonResponse and has a 201 status code
        if ($response instanceof JsonResponse && $response->getStatusCode() === 201) {

            // Modify the status code to 200
            $response->setStatusCode(200);

        }

        return $response;
    }
}
