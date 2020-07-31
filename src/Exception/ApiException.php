<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Exception de base pour représenter une erreur API.
 * Permet ensuite de fournir une réponse détaillée qui suit un format simplifié de JSON:API.
 *
 * Une telle erreur contient :
 * - "status" : le statut HTTP lié à l'erreur
 * - "type" : un type qui catégorise l'erreur
 * - "title" : description textuelle du type d'erreur (pas spécifique à CETTE erreur mais à ce type d'erreur)
 * - "detail" : si besoin description plus détaillée de cette occurence de l'erreur.
 *
 * D'autres champs peuvent s'ajouter au besoin
 * (exemple : un champs "errors" qui contient la liste des erreurs de validation).
 *
 * Les types et titres associés sont créés en tant que constantes dans cette classe pour centraliser leur localisation.
 */
class ApiException extends HttpException
{
    const TYPE_DEFAULT = 'about:blank';
    const TYPE_ERROR = 'error';
    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_INVALID_REQUEST_FORMAT = 'invalid_request_format';
    const TYPE_AUTHENTICATION_FAILED = 'authentication_failed';

    /** Map des titres associés aux différents types d'erreurs */
    private static $titles = [
        self::TYPE_ERROR => 'Une erreur est survenue',
        self::TYPE_VALIDATION_ERROR => 'La validation a échoué',
        self::TYPE_INVALID_REQUEST_FORMAT => 'Format de requête invalide',
        self::TYPE_AUTHENTICATION_FAILED => "L'authentification a échoué",
    ];

    /** Tableau des infos supplémentaires à inclure dans la réponse correspondant à cette exception */
    private $extraData = [];

    private $type;

    private $title;

    /**
     * {@inheritDoc}
     * @param int $statusCode Statut HTTP correspondant à l'erreur
     * @param string $type Type de l'erreur - Doit être une des constantes définies dans cette classe - Le titre est
     *                     complété automatiquement
     * @param string $detail Message décrivant cette erreur spécifique
     */
    public function __construct(
        int $statusCode = 500,
        $type = null,
        string $detail = null,
        Throwable $previous = null,
        $code = 0
    ) {
        if (null === $type) {
            $this->type = self::TYPE_DEFAULT;
            $this->title = isset(Response::$statusTexts[$statusCode])
                ? Response::$statusTexts[$statusCode]
                : 'Statut HTTP inconnu';
        } else {
            $this->type = $type;
            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException("Aucun titre prévu pour le type d'erreur \"{$type}\"");
            }
            $this->title = self::$titles[$type];
        }


        if (null !== $detail) {
            $this->set('detail', $detail);
        }

        parent::__construct($statusCode, $this->title, $previous, [], $code);
    }

    /**
     * Ajoute des infos supplémentaires à l'objet d'erreur par clé/valeur.
     * @param string $name Le nom de l'info à ajouter
     * @param mixed  $value Sa valeur - Tout type sérialisable en JSON nativement
     * @return self
     */
    public function set(string $name, $value): self
    {
        $this->extraData[$name] = $value;
        return $this;
    }
    
    /**
     * Renvoie les infos supplémentaires de l'objet d'erreur.
     * @param string $name Le nom de l'info à retrouver
     * @return mixed
     */
    public function get(string $name)
    {
        if (isset($this->extraData[$name])) {
            return $this->extraData[$name];
        }
        return null;
    }
    
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Retourne la représentation sous forme d'array de cette exception, utilisée pour construire la réponse retournée
     * à l'utilisateur.
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            $this->extraData,
            [
                'status' => $this->getStatusCode(),
                'title' => $this->getMessage(),
                'type' => $this->type,
            ]
        );
    }
}
