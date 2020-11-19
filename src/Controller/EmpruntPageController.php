<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmpruntPageController extends AbstractController
{
    /**
     * @Route("/emprunts", name="app_emprunts")
     * @param Request $req
     * @return Response
     */
    public function emprunts(Request $req)
    {
        return $this->render('emprunts.html.twig', [
            "current_menu" => "Emprunts",
            "tbody" => ""
        ]);
    }
}