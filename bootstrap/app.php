<?php

use App\Exceptions\ApiException;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException as PermissionUnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(__DIR__.'/../routes/admin.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.permission' => \App\Http\Middleware\EnsureAdminPermission::class,
        ]);

        // Laravel's default Authenticate middleware redirects guests to a
        // route named "login", which doesn't exist in this app (the admin
        // panel's login route is "admin.login"). Without this override,
        // any unauthenticated request that doesn't send an
        // "Accept: application/json" header (e.g. plain curl, or a browser
        // hitting an API route) throws RouteNotFoundException => 500,
        // instead of the intended 401 JSON response.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*')
            ? null
            : route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ApiException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error($e->getMessage(), null, $e->status());
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Validation failed.', $e->errors(), 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Unauthenticated.', null, 401);
            }
        });

        $exceptions->render(function (PermissionUnauthorizedException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Forbidden.', null, 403);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Forbidden.', null, 403);
            }
        });

        // Note: Eloquent's ModelNotFoundException (thrown by failed route
        // model binding, e.g. PUT /api/admin/users/{user}) never reaches a
        // renderer registered for its own class — Laravel's exception
        // handler converts it to a NotFoundHttpException, carrying the raw
        // "No query results for model [...]" message, before any render()
        // callback runs. So 404s are normalized to a safe message below,
        // keyed on status code instead of exception type.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*') || $e->getStatusCode() < 400) {
                return null;
            }

            $message = $e->getStatusCode() === 404
                ? 'Resource not found.'
                : ($e->getMessage() ?: 'Request failed.');

            return ApiResponse::error($message, null, $e->getStatusCode());
        });
    })->create();
