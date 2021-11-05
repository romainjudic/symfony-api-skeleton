<?php

namespace App\Controller;

use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class DefaultController extends BaseController
{
    /**
     * @Route("/default", name="default")
     * @IsGranted("ROLE_USER")
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
