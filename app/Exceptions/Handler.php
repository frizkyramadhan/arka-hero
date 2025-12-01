<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            if ($request->expectsJson()) {
                // For API requests, return JSON response
                return response()->json([
                    'responseMessage' => 'You do not have the required authorization.',
                    'responseStatus'  => 403,
                ]);
            }

            // For web requests, redirect with flash message for SweetAlert2
            return redirect()
                ->back()
                ->with('toast_error', 'You do not have permission to access this page')
                ->with('alert_type', 'error')
                ->with('alert_title', 'Access Denied')
                ->with('alert_message', 'You do not have the required permissions to perform this action.');
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found'
                ], 404);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found'
                ], 404);
            }
        });

        // Handle CSRF Token Mismatch (419 error)
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'CSRF token mismatch. Please refresh the page and try again.'
                ], 419);
            }

            // For web requests, redirect back to login with error message
            return redirect()
                ->route('login')
                ->with('toast_error', 'Your session has expired. Please try logging in again.')
                ->with('alert_type', 'error')
                ->with('alert_title', 'Session Expired');
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                $status = 500;
                $message = 'Server error';

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                    $message = $e->getMessage();
                } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $status = 401;
                    $message = 'Unauthenticated';
                } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    $status = 403;
                    $message = 'Unauthorized';
                }

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'errors' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null
                ], $status);
            }
        });
    }
}
