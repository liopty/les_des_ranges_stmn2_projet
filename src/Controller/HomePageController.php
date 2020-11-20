<?php
namespace App\Controller;
use App\Model\AdherentDAO;
use App\Model\DB;
use App\Model\EmpruntDAO;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomePageController extends AbstractController
{


    /**
     * @Route("/", name="app_homaepage")
     */
    public function accueil()
    {
        $TopEmprunt = EmpruntDAO::GetTopEmprunt(4);

        $tbody = "";
        if ($TopEmprunt != null) {
            foreach ($TopEmprunt as $e) {
                // traitement de string
                $datas =  str_replace(["(",")",'"'], "", $e['topjeuxempruntes']);
                $nom = substr($datas, 0,strrpos($datas, ','));
                $endDatas = explode(",", $datas);
                $endDatas= $endDatas[sizeof($endDatas)-1];
                $nb_emprunt =  str_replace(',', "", $endDatas);

                $tbody .= "<tr>"
                    . "<td scope='row'>$nom</td>"
                    . "<td scope='row'>$nb_emprunt</td>"
                    . "</tr>";
            }
        }
        else{
            $tbody .="<p>Aucun Jeu emprunte :( </p>";
        }

        return $this->render('homepage.html.twig', [
            "current_menu" => "Accueil",
            "tbody" => $tbody,
        ]);
    }


}
