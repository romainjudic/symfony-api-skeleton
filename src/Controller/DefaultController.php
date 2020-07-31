<?php

namespace App\Controller;

use DateTime;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends BaseController
{
    /**
     * @Route("/default", name="default")
     */
    public function index()
    {
        return $this->createApiResponse([
            'date' => new DateTime(),
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DefaultController.php',
        ]);
    }
}
