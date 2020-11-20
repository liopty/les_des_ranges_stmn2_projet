<?php


namespace App\Controller;


use App\Model\JeuxDAO;
use DateTime;
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
        if ($req->request->get('jeuRequestType') !== null) {
            $data = $req->request;
            switch ($data->get('jeuRequestType')) {
                case "new":
                    $uuid = uniqid();
                    $nom = ($data->get('nom') === null) ? "" : $data->get('nom');
                    $code = $this->getCode();
                    $categorie = ($data->get('categorie') !== null && $data->get('categorie') != "") ? $data->get('categorie') : null;
                    $etat = ($data->get('etat') !== null && $data->get('etat') != "") ? $data->get('etat') : null;
                    $description = ($data->get('description') === null) ? "" : $data->get('description');
                    $isDisponible = ($data->get("disponible") === null ) ? 0 : 1;
                    $date_achat = ($data->get('dateachat') !== null && $data->get('dateachat') != "") ? $data->get('dateachat') : null;
                    $date_creation = new DateTime('now');
                    $date_modification = new DateTime('now');

                    $val = [
                        "uuidJeux" => $uuid,
                        "nom" => $nom,
                        "code" => $code,
                        "categorie" => $categorie,
                        "etat" => $etat,
                        "description" => $description,
                        "isDisponible" => $isDisponible,
                        "date_achat" => $date_achat,
                        "date_creation" => $date_creation->format("Y-m-d h:i:s"),
                        "date_modification" => $date_modification->format("Y-m-d h:i:s")
                    ];
                    JeuxDAO::insert($val);

                    return $this->redirectToRoute("app_jeux");
                case "update":

                    $id = ($data->get('idJeu') === null) ? "" : $data->get('idJeu');
                    $nom = ($data->get('nom') === null) ? "" : $data->get('nom');
                    $categorie = ($data->get('categorie') !== null && $data->get('categorie') != "") ? $data->get('categorie') : null;
                    $etat = ($data->get('etat') !== null && $data->get('etat') != "") ? $data->get('etat') : null;
                    $description = ($data->get('description') === null) ? "" : $data->get('description');
                    $isDisponible = ($data->get("disponible") === null ) ? 0 : 1;
                    $date_achat = ($data->get('dateachat') !== null && $data->get('dateachat') != "") ? $data->get('dateachat') : null;
                    $date_modification = new DateTime('now');

                    $val = [
                        "nom" => $nom,
                        "categorie" => $categorie,
                        "etat" => $etat,
                        "description" => $description,
                        "isDisponible" => $isDisponible,
                        "date_achat" => $date_achat,
                        "date_modification" => $date_modification->format("Y-m-d h:i:s")
                    ];

                    $idTab = [
                        "uuidJeux" => $id
                    ];
                    JeuxDAO::update($val, $idTab);


                    return $this->redirectToRoute("app_jeux");
                case "delete":
                    $id = ($data->get('idJeu') === null) ? "" : $data->get('idJeu');
                    $param = [
                        "uuidJeux" => $id
                    ];
                    JeuxDAO::delete($param);

                    return $this->redirectToRoute("app_jeux");
                default:
                    break;
            }
        }


        $jeux = JeuxDAO::findAll("jeux");
        $allCategories = JeuxDAO::$categories;
        $allEtats = JeuxDAO::$etats;

        $tbody = "";
        if ($jeux != null) {

            foreach ($jeux as $c) {
                $uuidJeux = $c['uuidjeux'];
                $nom = $c['nom'];
                $code = $c['code'];
                $categorie = $c['categorie'];
                $etat = $c['etat'];
                $description = $c['description'];
                $isDisponible = ($c['isdisponible'])?"Oui":"Non";
                $date_achat = ($c['date_achat'] !== null) ? DateTime::createFromFormat('Y-m-d', $c['date_achat'])->format('d-m-Y') : "";

                $categorieColor = $allCategories[$categorie];

                $tbody .= "<tr class='rowColorCateory$categorieColor'>"
                    . "<td>$nom</td>"
                    . "<td>$code</td>"
                    . "<td>$categorie</td>"
                    . "<td>$etat</td>"
                    . "<td>$description</td>"
                    . "<td>$isDisponible</td>"
                    . "<td>$date_achat</td>"
                    . "<td>"
                    . '<button class="btn btn-secondary btn-sm openEditJeuForm"
                             data-toggle="modal" data-target="#neworupdateJeuForm"
                             data-nom="' . $nom . '"
                             data-code="' . $code . '"
                             data-categorie="' . $categorie . '"
                             data-etat="' . $etat . '"
                             data-description="' . $description . '"
                             data-isdisponible="' . $isDisponible . '"
                             data-date_achat="' . $date_achat . '"
                             data-id_jeu="' . $uuidJeux . '">
                          <i class="fas fa-cog"></i>
                       </button>
                       <button class="btn btn-danger btn-sm openDeleteJeuForm"
                            data-toggle="modal" data-target="#jeuFormDelete"
                            data-id_jeu="' . $uuidJeux . '">
                          <i class="fas fa-trash-alt"></i>
                       </button>'
                    . "</td>"
                    . "</tr>";

            }
        }

        return $this->render('jeux.html.twig', [
            "current_menu" => "Jeux",
            "tbody" => $tbody,
            "categories" => $allCategories,
            "etats" => $allEtats
        ]);
    }






    function getCode($len = 6){
        $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $base = strlen($charset);
        $result = '';

        $now = explode(' ', microtime())[1];
        while ($now >= $base){
            $i = $now % $base;
            $result = $charset[$i] . $result;
            $now /= $base;
        }
        return substr($result, -$len+1);
    }
}