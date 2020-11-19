<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JeuxPageController extends AbstractController
{
    /**
     * @Route("/jeux", name="app_jeux")
     * @param Request $req
     * @return Response
     */
    public function jeux(Request $req)
    {
        return $this->render('jeux.html.twig', [
            "current_menu" => "Jeux",
            "tbody" => ""
        ]);
    }
}