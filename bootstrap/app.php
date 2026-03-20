<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\CustomException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApiRequest = static fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen(function (Request $request) use ($isApiRequest): bool {
            return $isApiRequest($request);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            $errors = $exception->errors();
            $firstMessage = collect($errors)->flatten()->first();

            return response()->json([
                'success' => false,
                'message' => $firstMessage ?: 'Error de validacion.',
                'data' => null,
                'errors' => $errors,
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'data' => null,
                'errors' => null,
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'No autorizado para esta accion.',
                'data' => null,
                'errors' => null,
            ], 403);
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'No autorizado para esta accion.',
                'data' => null,
                'errors' => null,
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado.',
                'data' => null,
                'errors' => null,
            ], 404);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado.',
                'data' => null,
                'errors' => null,
            ], 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Metodo HTTP no permitido para este endpoint.',
                'data' => null,
                'errors' => null,
            ], 405);
        });

        $exceptions->render(function (CustomException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return $exception->render($request);
        });

        $exceptions->render(function (HttpException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            $statusCode = $exception->getStatusCode();

            $fallbackMessages = [
                400 => 'Solicitud invalida.',
                401 => 'No autenticado.',
                403 => 'No autorizado para esta accion.',
                404 => 'Recurso no encontrado.',
                405 => 'Metodo HTTP no permitido para este endpoint.',
                409 => 'Conflicto de estado.',
                422 => 'Error de validacion.',
            ];

            $message = $fallbackMessages[$statusCode] ?? 'Error en la solicitud.';

            if ($statusCode === 409 && $exception->getMessage() !== '') {
                $message = $exception->getMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => null,
            ], $statusCode);
        });

        $exceptions->render(function (QueryException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error al procesar la solicitud.',
                'data' => null,
                'errors' => null,
            ], 500);
        });

        $exceptions->render(function (\Throwable $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor.',
                'data' => null,
                'errors' => null,
            ], 500);
        });
    })->create();
