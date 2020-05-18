<?php

namespace App\Exceptions;

use App\Http\Utils\ApiUtil;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (ApiUtil::checkUrlIsApi($request)) {
            return $this->handleApiException($request, $exception);
        } else {
            return $this->handleWebException($request, $exception);
        }


    }

    private function handleWebException($request, Exception $exception)
    {

        if ($exception instanceof WebServiceException) {
            return redirect()->back()->withErrors($exception->getValidator())->withInput();
        }

        if ($exception instanceof WebServiceErroredException) {
            return redirect()->back()->with('error', $exception->getExplanation());
        }
        return parent::render($request, $exception);
    }


    private function handleApiException($request, Exception $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof \App\Exceptions\ApiServiceException) {
            return $exception->getApiResponse();
        }
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return parent::render($request, $exception);
    }

}
