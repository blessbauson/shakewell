<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            /*'tokenChecker' => \App\Http\Middleware\TokenCheckerMiddleware::class,
            'permissionCheck' => \App\Http\Middleware\CheckPermissionsMiddleware::class,
            'checkLoginToken' => \App\Http\Middleware\CheckTokenLoginMiddleware::class,*/
            'client'    => \Laravel\Passport\Http\Middleware\CheckClientCredentials::class,
            'auth'      => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create()->loadEnvironmentFrom(realpath($_SERVER['DOCUMENT_ROOT'].'/.env'));
