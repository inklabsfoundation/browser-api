<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        bug_report('```'."\n# ".$e->getMessage()."\n---\n".$e->getTraceAsString()."\n".'```');
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof HttpException) {
            return response()->json(['error'=>$e->getMessage()], $e->getStatusCode());
        }
        if ($e instanceof ModelNotFoundException) {
            return response()->json(['error'=>'No query results.'], 404);
        }
        if ($e instanceof AuthorizationException) {
            return response()->json(['error'=>'Credentials were not correct.'], 401);
        }
        if($e instanceof \ErrorException){
            return response()->json(['error'=>'Server is down.'], 502);
        }

        return parent::render($request, $e);
    }
}
