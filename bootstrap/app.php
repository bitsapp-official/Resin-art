<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckUserNotBlocked::class);
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            'webhook/stripe',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                // Gracefully handle session expiration across web & admin forms:
                // seamlessly redirect back with input preserved and a polite, non-technical notice
                // instead of crashing to the raw white 419 Page Expired screen.
                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation', '_token'))
                    ->with('status', 'For your security, please confirm your details to continue.');
            }
        });
    })->create();

