<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided

                return RespondWithBadRequest($lang, 4);
            }
        });
    }


    function render($request, Throwable $exception)
    {
        // Explicitly check for HttpException and access getStatusCode()
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            // Handle different status codes
            switch ($statusCode) {
                case 401:
                    return response()->view('dashboard.error.401', [], 401);
                case 404:
                    return response()->view('dashboard.error.404', [], 404);
                case 403:
                    return response()->view('dashboard.error.403', [], 403);
                case 422:
                    return response()->view('dashboard.error.422', [], 422);
                case 500:
                    return response()->view('dashboard.error.500', [], 500);
                default:
                    return response()->view('dashboard.error.404', [], $statusCode);
            }
        }

        // For non-HTTP exceptions, use the parent render method
        return parent::render($request, $exception);
    }
}
