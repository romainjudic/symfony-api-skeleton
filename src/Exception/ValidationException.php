<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Base exception thrown when a validation process fails (form, request parameter, etc...).
 * 
 * Adds an "errors" property that holds information about what caused the validation error.
 */
class ValidationException extends ApiException
{
    public function __construct($errors, string $detail = null, \Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, self::TYPE_VALIDATION_ERROR, $detail, $previous);

        $this->set('errors', $errors);
    }
}
