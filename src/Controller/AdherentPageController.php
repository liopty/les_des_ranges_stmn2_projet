<?php


namespace App\Controller;


use App\Model\AdherentDAO;
use App\Model\DB;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdherentPageController extends AbstractController
{

    /**
     * @Route("/adherents", name="app_adherents")
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adherents(Request $req)
    {


        if ($req->request->get('adherentRequestType') !== null) {
            $data = $req->request;
            switch ($data->get('adherentRequestType')) {
                case "new":
                    $uuid = uniqid();
                    $nom = ($data->get('nom') === null) ? "" : $data->get('nom');
                    $prenom = ($data->get('prenom') === null) ? "" : $data->get('prenom');
                    $date_naissance = ($data->get('datenaissance') !== null && $data->get('datenaissance') != "") ? $data->get('datenaissance') : null;
                    $telephone = ($data->get('telephone') === null) ? "" : $data->get('telephone');
                    $mail = ($data->get('mail') === null) ? "" : $data->get('mail');
                    $date_premiere_cotisation = ($data->get('dateprecoti') !== null && $data->get('dateprecoti') != "") ? $data->get('dateprecoti') : null;
                    $date_derniere_cotisation = ($data->get('datedercoti') !== null && $data->get('datedercoti') != "") ? $data->get('datedercoti') : $date_premiere_cotisation;
                    $type_adhesion = ($data->get('adhesion') === null) ? "" : $data->get('adhesion');
                    $personnes_rattachees = ($type_adhesion !== "Familiale" && $data->get('personnesrattachees') === null) ? "" : $data->get('personnesrattachees');

                    $autre = ($data->get('autre') === null) ? "" : $data->get('autre');
                    $date_creation = new DateTime('now');
                    $date_modification = new DateTime('now');

                    $val = [
                        "uuidAdherent" => $uuid,
                        "nom" => $nom,
                        "prenom" => $prenom,
                        "date_naissance" => $date_naissance,
                        "mail" => $mail,
                        "date_premiere_cotisation" => $date_premiere_cotisation,
                        "date_derniere_cotisation" => $date_derniere_cotisation,
                        "telephone" => $telephone,
                        "type_adhesion" => $type_adhesion,
                        "personnes_rattachees" => $personnes_rattachees,
                        "autre" => $autre,
                        "date_creation" => $date_creation->format("Y-m-d h:i:s"),
                        "date_modification" => $date_modification->format("Y-m-d h:i:s")
                    ];
                    AdherentDAO::insert($val);

                    return $this->redirectToRoute("app_adherents");
                case "update":
                    $id = ($data->get('idAdherent') === null) ? "" : $data->get('idAdherent');
                    $nom = ($data->get('nom') === null) ? "" : $data->get('nom');
                    $prenom = ($data->get('prenom') === null) ? "" : $data->get('prenom');
                    $date_naissance = ($data->get('datenaissance') !== null && $data->get('datenaissance') != "") ? $data->get('datenaissance') : null;
                    $telephone = ($data->get('telephone') === null) ? "" : $data->get('telephone');
                    $mail = ($data->get('mail') === null) ? "" : $data->get('mail');
                    $date_premiere_cotisation = ($data->get('dateprecoti') !== null && $data->get('dateprecoti') != "") ? $data->get('dateprecoti') : null;
                    $date_derniere_cotisation = ($data->get('datedercoti') !== null && $data->get('datedercoti') != "") ? $data->get('datedercoti') : $date_premiere_cotisation;
                    $type_adhesion = ($data->get('adhesion') === null) ? "" : $data->get('adhesion');
                    $personnes_rattachees = ($type_adhesion !== "Familiale" && $data->get('personnesrattachees') === null) ? "" : $data->get('personnesrattachees');

                    $autre = ($data->get('autre') === null) ? "" : $data->get('autre');
                    $date_modification = new DateTime('now');

                    $val = [
                        "nom" => $nom,
                        "prenom" => $prenom,
                        "date_naissance" => $date_naissance,
                        "mail" => $mail,
                        "date_premiere_cotisation" => $date_premiere_cotisation,
                        "date_derniere_cotisation" => $date_derniere_cotisation,
                        "telephone" => $telephone,
                        "type_adhesion" => $type_adhesion,
                        "personnes_rattachees" => $personnes_rattachees,
                        "autre" => $autre,
                        "date_modification" => $date_modification->format("Y-m-d h:i:s")
                    ];

                    $idTab = [
                        "uuidAdherent" => $id
                    ];
                    AdherentDAO::update($val, $idTab);


                    return $this->redirectToRoute("app_adherents");
                case "delete":
                    $id = ($data->get('idAdherent') === null) ? "" : $data->get('idAdherent');
                    $param = [
                        "uuidAdherent" => $id
                    ];
                    AdherentDAO::delete($param);

                    return $this->redirectToRoute("app_adherents");
                default:
                    break;
            }
        }


        $adherents = AdherentDAO::findAll("adherent");
        $tbody = "";
        if ($adherents != null) {
            foreach ($adherents as $a) {
                $uuidadherent = $a['uuidadherent'];
                $nom = $a['nom'];
                $prenom = $a['prenom'];
                $date_naissance = ($a['date_naissance'] !== null) ? DateTime::createFromFormat('Y-m-d', $a['date_naissance'])->format('d-m-Y') : "";
                $mail = $a['mail'];
                $date_premiere_cotisation = ($a['date_premiere_cotisation'] !== null) ? DateTime::createFromFormat('Y-m-d', $a['date_premiere_cotisation'])->format('d-m-Y') : "";
                $date_derniere_cotisation = ($a['date_derniere_cotisation'] !== null) ? DateTime::createFromFormat('Y-m-d', $a['date_derniere_cotisation'])->format('d-m-Y') : "";
                $telephone = $a['telephone'];
                $type_adhesion = $a['type_adhesion'];
                $personnes_rattachees = $a['personnes_rattachees'];
                $autre = $a['autre'];

                $tbody .= "<tr>"
                    . "<td>$nom</td>"
                    . "<td>$prenom</td>"
                    . "<td>$date_naissance</td>"
                    . "<td>$mail</td>"
                    . "<td>$date_premiere_cotisation</td>"
                    . "<td>$date_derniere_cotisation</td>"
                    . "<td>$telephone</td>"
                    . "<td>$type_adhesion</td>"
                    . "<td>$personnes_rattachees</td>"
                    . "<td>$autre</td>"
                    . "<td>"
                    . '<button class="btn btn-secondary btn-sm openEditAdherentForm"
                             data-toggle="modal" data-target="#neworupdateadherentForm"
                             data-prenom="' . $prenom . '"
                             data-nom="' . $nom . '"
                             data-date_naissance="' . $date_naissance . '"
                             data-mail="' . $mail . '"
                             data-date_premiere_cotisation="' . $date_premiere_cotisation . '"
                             data-date_derniere_cotisation="' . $date_derniere_cotisation . '"
                             data-telephone="' . $telephone . '"
                             data-type_adhesion="' . $type_adhesion . '"
                             data-personnes_rattachees="' . $personnes_rattachees . '"
                             data-autre="' .$autre . '"
                             data-id_adherent="' . $uuidadherent . '">
                          <i class="fas fa-cog"></i>
                       </button>
                       <button class="btn btn-danger btn-sm openDeleteAdherentForm"
                            data-toggle="modal" data-target="#adherentFormDelete"
                            data-id_adherent="' . $uuidadherent . '">
                          <i class="fas fa-trash-alt"></i>
                       </button>'
                    . "</td>"
                    . "</tr>";

            }
        }


        return $this->render('adherents.html.twig', [
            "current_menu" => "Adherents",
            "tbody" => $tbody
        ]);
    }

}