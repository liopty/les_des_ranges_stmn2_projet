<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VentePageController extends AbstractController
{
    /**
     * @Route("/ventes", name="app_ventes")
     * @param Request $req
     * @return Response
     */
    public function ventes(Request $req)
    {
        return $this->render('ventes.html.twig', [
            "current_menu" => "Ventes",
            "tbody" => ""
        ]);
    }
}