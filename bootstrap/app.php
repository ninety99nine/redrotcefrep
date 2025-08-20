<?php

use Illuminate\Http\Request;
use App\Http\Middleware\SetUssdUser;
use Illuminate\Foundation\Application;
use App\Http\Middleware\StorePermission;
use App\Http\Middleware\RecordStoreVisit;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'set.ussd.user' => SetUssdUser::class,
            'store.permission' => StorePermission::class,
            'record.store.visit' => RecordStoreVisit::class
        ]);

        $middleware->prependToPriorityList(
            before: AuthenticatesRequests::class,
            prepend: SetUssdUser::class,
        );

        //  Global registration to execute for every api route
        $middleware->api(prepend: [
            SetUssdUser::class
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {

            if ($request->is('api/*')) {

                // Try to detect if it's from a missing model
                if (str_contains($e->getMessage(), 'No query results for model')) {

                    if (preg_match('/model \[App\\\\Models\\\\(.+?)\]/', $e->getMessage(), $matches)) {

                        $modelName = $matches[1] ?? 'Resource';

                        return response()->json([
                            'message' => "{$modelName} not found"
                        ], 404);

                    }
                }

                // Otherwise, fallback to generic not found
                return response()->json([
                    'message' => "Resource not found"
                ], 404);

            }

        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'You are not authorized to perform this action'
                ], 403);
            }
        });

    })->create();
