<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Consommables extends AbstractController
{
    /**
     * @Route("/stocks", name="app_stocks")
     * @param Request $req
     * @return Response
     */
    public function stocks(Request $req)
    {
        return $this->render('stocks.html.twig', [
            "current_menu" => "Stocks",
            "tbody" => ""
        ]);
    }
}