<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception de base levée quand un processus de validation échoué (formulaire, paramètre de requête, ...).
 *
 * Ajoute un objet "errors" à l'objet qui contient toutes les infos nécessaires pour tracer la source de l'erreur de
 * validation. Par exemple, une Map associant à chaque champ d'un formulaire les messages d'erreur associés.
 */
class ValidationException extends ApiException
{
    public function __construct($errors, string $detail = null, \Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, self::TYPE_VALIDATION_ERROR, $detail, $previous);

        $this->set('errors', $errors);
    }
}
