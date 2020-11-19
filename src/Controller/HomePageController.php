<?php
namespace App\Controller;
use App\Model\DB;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomePageController extends AbstractController
{


    /**
     * @Route("/", name="app_homaepage")
     */
    public function accueil()
    {

        return $this->redirectToRoute("app_adherents");

    }


}
