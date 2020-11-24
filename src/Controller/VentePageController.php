<?php


namespace App\Controller;


use App\Model\AdherentDAO;
use App\Model\ConsommablesDAO;
use App\Model\VenteDAO;
use DateTime;
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
        $error = $req->query->get("error");
        if ($req->request->get('venteRequestType') !== null) {
            $data = $req->request;
            switch ($data->get('venteRequestType')) {
                case "new":

                    $uuid = uniqid();
                    $uuidAdherent = ($data->get('uuidAdherent') === null) ? "" : $data->get('uuidAdherent');
                    $date_creation = new DateTime('now');
                    $date_modification = new DateTime('now');

                    $nbventes = ($data->get('ventenbprod') === null) ? 0 : (int)$data->get('ventenbprod');

                    $produits = [];
                    for($i = 0; $i <= $nbventes;$i++){
                        $uuidPRoduit = ($data->get('produit'.$i) === null) ? "" : $data->get('produit'.$i);
                        $qte = ($data->get('quantite'.$i) === null) ? 0 : (int)$data->get('quantite'.$i);
                        if($uuidPRoduit != "" && $qte > 0) $produits[$uuidPRoduit] = $qte;
                    }


                    $val = [
                        "uuidVente" => $uuid,
                        "uuidAdherent" => $uuidAdherent,
                        "prix_total" => 0.00,
                        "date_creation" => $date_creation->format("Y-m-d h:i:s"),
                        "date_modification" => $date_modification->format("Y-m-d h:i:s"),
                        "produits"=> $produits
                    ];
                    $error = VenteDAO::insert($val);

                    return $this->redirectToRoute("app_ventes", ["error" => $error]);
                default:
                    break;
            }
        }

        $ventes = VenteDAO::findAll();
        $tbody = "";
        if ($ventes != null) {
            foreach ($ventes as $v) {
                $uuidVente = $v['uuidvente'];
                $uuidAdherent = $v['uuidadherent'];
                $prix_total = $v['prix_total'];
                $date_creation = ($v['date_creation'] !== null) ? DateTime::createFromFormat('Y-m-d h:i:s', $v['date_creation'])->format('d-m-Y') : "";
                $adherent_nom = $v['anom'];
                $adherent_prenom = $v['aprenom'];

                $adherent = $uuidAdherent . "-" . $adherent_nom . " " . $adherent_prenom;
                $infosUrl = $req->getScheme() . '://' . $req->getHttpHost() . '/vente/'.$uuidVente;


                $tbody .= "<tr>"
                    . "<td>$date_creation</td>"
                    . "<td>$adherent</td>"
                    . "<td>$prix_total</td>"
                    . "<td>"
                    . '<button class="btn btn-secondary btn-sm openInfosVenteForm"
                                 data-toggle="modal" data-target="#infosVenteForm"
                                 data-url="' . $infosUrl . '">
                              <i class="fas fa-eye"></i>
                           </button> '
                    . "</td>"
                    . "</tr>";

            }
        }

        $adherents = AdherentDAO::getAdherentNomPrenomUuid();
        $produits = ConsommablesDAO::getConsommablesLabelUuid();


        return $this->render('ventes.html.twig', [
            "current_menu" => "Ventes",
            "tbody" => $tbody,
            "adherents" => $adherents,
            "produits" => $produits,
            "error" => $error
        ]);

    }


    /**
     * @Route("/vente/{uuidVente}", name="app_ventes_infos")
     * @param Request $req
     * @param String $uuidVente
     * @return Response
     */
    public function getVenteDetail(Request $req, String $uuidVente)
    {
        $res = "";

        //on s'assure que la fonction est lancée par l'envoi du formulaire
        $token = $req->request->get('token');
        if ($token !== null && $token == "appRequest") {
            $data = $req->request;
            $infos = VenteDAO::getProduits($uuidVente);
            if ($infos != null) {
                $tbody = "";
                $prix_total = 0;
                foreach ($infos as $i) {
                    $label = $i["label"];
                    $qte = $i["qte"];
                    $pu = $i["prix_unitaire"];
                    $prix_total = $i["prix_total"];
                    $tbody .= "<tr><td>$label</td><td>$qte</td><td>".$pu." €</td></tr>";
                }
                $tbody .= "<tr><td></td><td></td><td>Total: ".$prix_total." €</td></tr>";

                $res = '<table class="table table-striped w-auto" id="infosVentesTable">
                            <thead>
                            <tr>
                                <th class="th-lg">Produit</th>
                                <th class="th-lg">Quantité</th>
                                <th class="th-lg">Prix unitaire</th>
                            </tr>
                            </thead>
                            <tbody>
                            '.$tbody.'
                            </tbody>

                        </table>';
            }else $res = "Aucun produit dans la vente :(";




        } else $res = "Error";

        return new Response($res);
    }

}