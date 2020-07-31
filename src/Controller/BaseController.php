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
 * Contrôleur dont doivent étendre tous les contrôleurs de l'API afin d'avoir
 * toutes les méthodes utiles pour traiter les requêtes courantes.
 */
abstract class BaseController extends AbstractController
{
    const IN_QUERY = 'query';

    /** @var Request */
    protected $request;

    /**
     * Injection par setter, appelée automatiquement à l'instanciation.
     *
     * Permet aux classes filles d'avoir un constructeur plus simple.
     * 
     * @required
     * @param RequestStack
     */
    public function setBaseDependencies(RequestStack $requestStack): void {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Retourne le contenu (body) de la requête en cours.
     *
     * @return array|null Un array ou null si aucun body n'est précisé
     */
    protected function getRequestData(): ?array
    {
        return json_decode($this->request->getContent(), true);
    }

    /**
     * Crée et retourne une réponse API en JSON pour les données passées.
     * 
     * @param mixed $data Les données à envoyer dans la réponse JSON
     * @param int $status Le statut HTTP de la réponse
     * @param array $serializationContext Un éventuel contexte de sérialisation pour contrôler le processus de sérialisation
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
     * Retourne l'utlisateur authentifié avec le bon typage.
     * 
     * @return User
     */
    protected function getAppUser(): User
    {
        $user = $this->getUser();
        if (!($user instanceof User)) {
            throw new \LogicException("Impossible d'accéder à un utilisateur de type App\Entity\User dans une zone non sécurisée.");
        }

        return $user;
    }

    /**
     * Soumet les données du body de la requête à un formulaire Symfony. Gère la validation.
     * 
     * @param FormInterface $form         Le formulaire à utiliser
     * @param bool|null $clearMissing     Indique si les champs non soumis doivent être vidés (null) -
     *                                    Par défaut true pour tous les verbes autres que PATCH
     * @param array|null $preExistingData Données à utiliser, si les données ont déjà été extraites du body par exemple
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

        // Si un champ est manquant, il est vidé (null) sauf avec le verbe PATCH
        // Si une valeur $clearMissing est passée, ce comportement peut être surchargé
        $actuallyClearMissing = null !== $clearMissing ? $clearMissing : ($this->request->getMethod() != 'PATCH');
        $form->submit($data, $actuallyClearMissing);

        if (!$form->isValid()) {
            throw new FormValidationException($form);
        }
    }
}
