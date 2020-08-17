<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Base exception to represent an API error.
 * Ensure the response has detailed information following a precise format (simplified JSON:API).
 *
 * Such an error contains:
 * - "status": the HTTP status for that error
 * - "type": a category that target this kind of errors globally
 * - "title": a descriptive title for the type
 * - "detail": optional detailed description of that specific instance of the error.
 * 
 * Other stuff can be added when needed. For example: an "error" field with a list of validation errors.
 * 
 * Types and titles are stored as constants in this class to keep them in ine place.
 */
class ApiException extends HttpException
{
    const TYPE_DEFAULT = 'about:blank';
    const TYPE_ERROR = 'error';
    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_INVALID_REQUEST_FORMAT = 'invalid_request_format';
    const TYPE_AUTHENTICATION_FAILED = 'authentication_failed';

    /** Map of the titles for every type of error */
    private static $titles = [
        self::TYPE_ERROR => 'An error occured',
        self::TYPE_VALIDATION_ERROR => 'Validation failed',
        self::TYPE_INVALID_REQUEST_FORMAT => 'Invalid request format',
        self::TYPE_AUTHENTICATION_FAILED => 'Authentication failed',
    ];

    /** Array of extra data that will be included in the response */
    private $extraData = [];

    private $type;

    private $title;

    /**
     * {@inheritDoc}
     * @param int $statusCode HTTP status code for that error
     * @param string $type Error type - Must be one of the constants defined in this class - The corresponding title will be picked automatically
     * @param string $detail Message that describes the error more specifically
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
                : 'Unknown HTTP status code';
        } else {
            $this->type = $type;
            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException("No title for that error type \"{$type}\"");
            }
            $this->title = self::$titles[$type];
        }


        if (null !== $detail) {
            $this->set('detail', $detail);
        }

        parent::__construct($statusCode, $this->title, $previous, [], $code);
    }

    /**
     * Add additional property to the error by key/value.
     * @param string $name Name/key of the additional property
     * @param mixed  $value Its value - Anything that may be serialized natively to JSON
     * @return self
     */
    public function set(string $name, $value): self
    {
        $this->extraData[$name] = $value;
        return $this;
    }
    
    /**
     * Return an extra property
     * @param string $name Name of the property
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
     * Return the JSON representation (array) of the exception that will be used in the response.
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
