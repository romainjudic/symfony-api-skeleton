<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticationException extends ApiException
{
    public function __construct(string $detail = null, Throwable $previous = null, $code = 0)
    {
        parent::__construct(
            Response::HTTP_UNAUTHORIZED,
            self::TYPE_AUTHENTICATION_FAILED,
            $detail,
            $previous,
            $code
        );
    }
}
