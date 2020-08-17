<?php

namespace App\Exception;

use Symfony\Component\Form\FormInterface;

/**
 * Exception thrown when a form validation fails.
 * An "errors" property is added to the ApiException and holds a (recursive) Map of the validation errors of each form field.
 */
class FormValidationException extends ValidationException
{
    public function __construct(FormInterface $form, string $detail = null)
    {
        parent::__construct($this->getErrorsFromForm($form), $detail);
    }

    /**
     * Return the validation error map extracted from a FormInterface instance.
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
