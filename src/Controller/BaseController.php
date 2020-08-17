<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\ApiException;
use App\Exception\FormValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Parent controller for all API controllers.
 * Provides utility methods for API purposes.
 * 
 * All other controllers must inherit this class.
 */
abstract class BaseController extends AbstractController
{
    const IN_QUERY = 'query';

    /** @var Request */
    protected $request;

    /**
     * Setter injection. Automatically called when instanciated.
     * 
     * @required
     * @param RequestStack
     */
    public function setBaseDependencies(RequestStack $requestStack): void {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Return the current request's body.
     *
     * @return array|null An array or NULL if no body is specified
     */
    protected function getRequestData(): ?array
    {
        return json_decode($this->request->getContent(), true);
    }

    /**
     * Create and return an JSON response for the given data.
     * 
     * @param mixed $data Data to send in the JSON response
     * @param int $status HTTP status code for the response
     * @param array $serializationContext Optional serialization context to controle the serialization process
     * @return Response
     */
    protected function createApiResponse($data, $status = 200, $serializationContext = []): Response
    {
        if (!empty($data) || is_array($data)) {
            return $this->json($data, $status, [], $serializationContext);
        }

        return new Response('', $status);
    }

    /**
     * Return the authenticatd user with the correct type.
     * 
     * @return User
     */
    protected function getAppUser(): User
    {
        $user = $this->getUser();
        if (!($user instanceof User)) {
            $userClass = User::class;
            throw new \LogicException("Cannot access a user of type $userClass outside of the main firewal.");
        }

        return $user;
    }

    /**
     * Submit the request body to a Symfony form and handle validation.
     * 
     * @param FormInterface $form         The form to use
     * @param bool|null $clearMissing     Indicate whether the missing fields should be clear from the object (NULL) -
    *                                     Defaults to true for all HTTP verbs but PATCH
     * @param array|null $preExistingData Optional data that should be used instead of the request body
     */
    protected function processForm(FormInterface $form, ?bool $clearMissing = null, ?array $preExistingData = null): void
    {
        $data = $preExistingData;
        if (null === $data) {
            $data = $this->getRequestData();
        }
        if (null === $data) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, ApiException::TYPE_INVALID_REQUEST_FORMAT);
        }

        // Clear missing fields (NULL), unless the HTTP verb is PATCH
        // This behavior can be overriden by spending a $clearMissing manually
        $actuallyClearMissing = null !== $clearMissing ? $clearMissing : ($this->request->getMethod() != 'PATCH');
        $form->submit($data, $actuallyClearMissing);

        if (!$form->isValid()) {
            throw new FormValidationException($form);
        }
    }
}
