<?php


namespace App\Controller;


use App\Model\AdherentDAO;
use App\Model\EmpruntDAO;
use App\Model\JeuxDAO;
use DateTime;
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
        $error = $req->query->get("error");
        if ($req->request->get('empruntRequestType') !== null) {
            $data = $req->request;
            switch ($data->get('empruntRequestType')) {
                case "new":

                    $uuid = uniqid();
                    $uuidJeu = ($data->get('uuidJeu') === null) ? "" : $data->get('uuidJeu');
                    $uuidAdherent = ($data->get('uuidAdherent') === null) ? "" : $data->get('uuidAdherent');
                    $date_emprunt = ($data->get('date_emprunt') === null) ? "" : $data->get('date_emprunt');
                    $date_retourprevu = ($data->get('date_retourprevu') === null) ? "" : $data->get('date_retourprevu');
                    $date_creation = new DateTime('now');
                    $date_modification = new DateTime('now');

                    $val = [
                        "uuidEmprunt" => $uuid,
                        "uuidJeux" => $uuidJeu,
                        "uuidAdherent" => $uuidAdherent,
                        "date_emprunt" => $date_emprunt,
                        "date_retourprevu" => $date_retourprevu,
                        "date_creation" => $date_creation->format("Y-m-d h:i:s"),
                        "date_modification" => $date_modification->format("Y-m-d h:i:s")
                    ];
                    $error = EmpruntDAO::insert($val);

                    return $this->redirectToRoute("app_emprunts", ["error"=>$error]);
                case "update":
                    $uuid = ($data->get('uuidEmprunt') === null) ? "" : $data->get('uuidEmprunt');
                    $uuidJeu = ($data->get('uuidJeu') === null) ? "" : $data->get('uuidJeu');
                    $uuidAdherent = ($data->get('uuidAdherent') === null) ? "" : $data->get('uuidAdherent');
                    $date_emprunt = ($data->get('date_emprunt') === null) ? "" : $data->get('date_emprunt');
                    $date_retourprevu = ($data->get('date_retourprevu') === null) ? "" : $data->get('date_retourprevu');
                    $date_modification = new DateTime('now');

                    $val = [
                        "uuidJeux" => $uuidJeu,
                        "uuidAdherent" => $uuidAdherent,
                        "date_emprunt" => $date_emprunt,
                        "date_retourprevu" => $date_retourprevu,
                        "date_modification" => $date_modification->format("Y-m-d h:i:s")
                    ];

                    $idTab = [
                        "uuidEmprunt" => $uuid
                    ];
                    $error = EmpruntDAO::update($val, $idTab);

                    return $this->redirectToRoute("app_emprunts", ["error"=>$error]);
                case "delete":
                    $id = ($data->get('uuidEmprunt') === null) ? "" : $data->get('uuidEmprunt');
                    $param = [
                        "uuidEmprunt" => $id
                    ];
                    $error = EmpruntDAO::delete($param);

                    return $this->redirectToRoute("app_emprunts", ["error"=>$error]);
                case "rendre":
                    $uuid = ($data->get('uuidEmprunt') === null) ? "" : $data->get('uuidEmprunt');

                    $date_retour = ($data->get('date_retour') === null) ? "" : $data->get('date_retour');
                    $date_modification = new DateTime('now');

                    $val = [

                        "date_retour" => $date_retour,
                        "date_modification" => $date_modification->format("Y-m-d h:i:s")
                    ];

                    $idTab = [
                        "uuidEmprunt" => $uuid
                    ];
                    $error = EmpruntDAO::update($val, $idTab);

                    return $this->redirectToRoute("app_emprunts", ["error"=>$error]);
                default:
                    break;
            }
        }

        $emprunts = EmpruntDAO::findAll();
        $tbody = "";
        if ($emprunts != null) {
            foreach ($emprunts as $e) {
                $uuidEmprunt = $e['uuidemprunt'];
                $uuidAdherent = $e['uuidadherent'];
                $uuidjeux = $e['uuidjeux'];
                $date_emprunt = ($e['date_emprunt'] !== null) ? DateTime::createFromFormat('Y-m-d', $e['date_emprunt'])->format('d-m-Y') : "";
                $date_retourprevu = ($e['date_retourprevu'] !== null) ? DateTime::createFromFormat('Y-m-d', $e['date_retourprevu'])->format('d-m-Y') : "";
                $date_retour = ($e['date_retour'] !== null) ? DateTime::createFromFormat('Y-m-d', $e['date_retour'])->format('d-m-Y') : null;
                $adherent_nom = $e['anom'];
                $adherent_prenom = $e['aprenom'];
                $jeu_nom = $e['jnom'];

                $adherent = $uuidAdherent."-".$adherent_nom." ".$adherent_prenom;
                $jeu = $uuidjeux."-".$jeu_nom;

                $isRendu = "toHideEncours";
                $isEncours = false;
                if ($date_retour == null){
                    $date_retour = "";
                    $isEncours = true;
                    $isRendu = "";
                }

                $retard = ((DateTime::createFromFormat('Y-m-d', $e['date_retourprevu']) < new DateTime('now')) && $isEncours)?"empruntRetard":"";


                $tbody .= "<tr class='$retard $isRendu' hidden>"
                    . "<td>$adherent</td>"
                    . "<td>$jeu</td>"
                    . "<td>$date_emprunt</td>"
                    . "<td>$date_retourprevu</td>"
                    . "<td class='toHideEncours' hidden>$date_retour</td>"
                    . "<td>";
                    if($isEncours){
                        $tbody .=       '<button class="btn btn-secondary btn-sm openEditEmpruntForm"
                                 data-toggle="modal" data-target="#neworupdateempruntForm"
                                 data-id_adherent="' . $uuidAdherent . '"
                                 data-id_jeu="' . $uuidjeux . '"
                                 data-date_emprunt="' . $date_emprunt . '"
                                 data-date_retourprevu="' . $date_retourprevu . '"
                                 data-id_emprunt="' . $uuidEmprunt . '">
                              <i class="fas fa-cog"></i>
                           </button> '
                            .'<button class="btn btn-secondary btn-sm openRendreEmpruntForm"
                            data-toggle="modal" data-target="#empruntFormRendre"
                            data-id_emprunt="' . $uuidEmprunt . '">
                          <i class="fas fa-exchange-alt"></i>
                       </button>'
                            .' <button class="btn btn-danger btn-sm openDeleteEmpruntsForm"
                            data-toggle="modal" data-target="#empruntFormDelete"
                            data-id_emprunt="' . $uuidEmprunt . '">
                          <i class="fas fa-trash-alt"></i>
                       </button>';

                    }
                    $tbody .= "</td>"
                    . "</tr>";

            }
        }

        $adherents = AdherentDAO::getAdherentNomPrenomUuid();
        $jeux = JeuxDAO::getJeuxNomUuid();

        return $this->render('emprunts.html.twig', [
            "current_menu" => "Emprunts",
            "tbody" => $tbody,
            "adherents" => $adherents,
            "jeux"=>$jeux,
            "error"=> $error
        ]);
    }
}