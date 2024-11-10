<?php

namespace App\Exceptions;

use Throwable;
use Symfony\Component\Mailer\Exception\TransportException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e) {
		if ($e instanceof TransportException) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => 'SMTP Configuration is incorrect !']);
			} else {
				return redirect()->route('login')->with('error', 'SMTP Configuration is incorrect !');
			}
		}

		return parent::render($request, $e);
	}
}
