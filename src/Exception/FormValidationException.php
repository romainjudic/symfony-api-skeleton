<?php

namespace App\Exception;

use Symfony\Component\Form\FormInterface;

/**
 * Exception levée quand la validation d'un formulaire échoue.
 * L'objet "errors" ajouté à la réponse contient une Map (récursive) des erreurs associées à chaque champ du formulaire.
 */
class FormValidationException extends ValidationException
{
    public function __construct(FormInterface $form, string $detail = null)
    {
        parent::__construct($this->getErrorsFromForm($form), $detail);
    }

    /**
     * Retourne la Map des erreurs extraites de l'objet de formulaire FormInterface.
     * @param FormInterface $form Le formulaire à parcourir
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];
        if (!is_null($form->getErrors())) {
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
        }
        
        if (!is_null($form->all())) {
            foreach ($form->all() as $childForm) {
                if ($childForm instanceof FormInterface && $childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
