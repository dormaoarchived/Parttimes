<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        # 快捷查看错误信息
        # return parent::render($request, $exception);

        $back = $this->onAPIHandle($request, $exception);
        if ($back != null) {
            return response()->json(
                $back->toJson(), $back->getResponseCode()
            );
        }else {
            #var_dump(get_class($exception));
            return parent::render($request, $exception);
        }
    }

    public function onAPIHandle($request, Exception $exception){
        return $this->handleAPIException($exception);
    }

    /**
     * @param Exception $exception
     * @return APIException|null
     */
    public function handleAPIException(Exception $exception){
        if ($exception instanceof APIException){
            return $exception;
        }else if($exception instanceof AuthenticationException){
            return new APIException(1, $exception->getMessage(), [], 400, $exception);
        }else if($exception instanceof ThrottleRequestsException){
            return new APIException(1,$exception->getMessage(), [], $exception->getStatusCode(), $exception);
        }else if($exception instanceof NotFoundHttpException){
            return new APIException(1, 'not found', [], 404);
        }
        return new APIException(1, 'internal', [], 500, $exception);
    }
}
