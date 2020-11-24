<?php


namespace App\Controller;


use App\Model\AdherentDAO;
use App\Model\ConsommablesDAO;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConsommablesPageController extends AbstractController
{
    /**
     * @Route("/stocks", name="app_stocks")
     * @param Request $req
     * @return Response
     */
    public function stocks(Request $req)
    {

        $error = $req->query->get("error");
        if ($req->request->get('consoRequestType') !== null) {
            $data = $req->request;
            switch ($data->get('consoRequestType')) {
                case "new":
                    $uuid = uniqid();
                    $label= ($data->get('label') === null) ? "" : $data->get('label');
                    $prix_unitaire = ($data->get('prix_unitaire') === null) ? "" : $data->get('prix_unitaire');
                    $qte = ($data->get('qte') === null) ? "" : $data->get('qte');
                    $date_creation = new DateTime('now');
                    $date_modification = new DateTime('now');

                    $val = [
                        "uuidConsommables" => $uuid,
                        "label" => $label,
                        "prix_unitaire" => $prix_unitaire,
                        "qte" => $qte,
                        "date_creation" => $date_creation->format("Y-m-d h:i:s"),
                        "date_modification" => $date_modification->format("Y-m-d h:i:s")
                    ];
                    $error = ConsommablesDAO::insert($val);

                    return $this->redirectToRoute("app_stocks", ["error"=>$error]);
                case "update":
                    $uuid = ($data->get('uuidConsommables') === null) ? "" : $data->get('uuidConsommables');
                    $label = ($data->get('label') === null) ? "" : $data->get('label');
                    $prix_unitaire = ($data->get('prix_unitaire') === null) ? "" : $data->get('prix_unitaire');
                    $qte = ($data->get('qte') === null) ? "" : $data->get('qte');
                    $date_creation = ($data->get('date_creation') === null) ? "" : $data->get('date_creation');
                    $date_modification = new DateTime('now');

                    $val = [
                        "label" => $label,
                        "prix_unitaire" => $prix_unitaire,
                        "qte" => $qte,
                        "date_modification" => $date_modification->format("Y-m-d h:i:s")
                    ];

                    $idTab = [
                        "uuidConsommables" => $uuid
                    ];
                    $error = ConsommablesDAO::update($val, $idTab);

                    return $this->redirectToRoute("app_stocks", ["error"=>$error]);
                case "delete":
                    $id = ($data->get('uuidConsommables') === null) ? "" : $data->get('uuidConsommables');
                    $param = [
                        "uuidConsommables" => $id
                    ];
                    $error = ConsommablesDAO::delete($param);

                    return $this->redirectToRoute("app_stocks", ["error"=>$error]);
                default:
                    break;
            }
        }

        $consommables = ConsommablesDAO::findAll();
        $tbody = "";
        if ($consommables != null) {
            foreach ($consommables as $c) {
                $uuidConsommables = $c['uuidconsommables'];
                $label = $c['label'];
                $prix_unitaire = $c['prix_unitaire'];
                $qte = $c['qte'];

                $tbody .= "<tr>"
                    . "<td>$label</td>"
                    . "<td>".$prix_unitaire." €</td>"
                    . "<td>$qte</td>"
                    . "<td>"
                        . '<button class="btn btn-secondary btn-sm openEditConsommablesForm"
                                 data-toggle="modal" data-target="#neworupdateconsommablesForm"
                                 data-label="' . $label . '"
                                 data-prix_unitaire="' . $prix_unitaire . '"
                                 data-qte="' . $qte . '"
                                 data-id_consommable="' . $uuidConsommables . '">
                              <i class="fas fa-cog"></i>
                           </button>'
                 .'   <button class="btn btn-danger btn-sm openDeleteConsommablesForm"
                            data-toggle="modal" data-target="#ConsommablesFormDelete"
                            data-id_consommable="' . $uuidConsommables . '">
                          <i class="fas fa-trash-alt"></i>
                       </button>'
                    . "</td>"
                    . "</tr>";

            }
        }

        return $this->render('stocks.html.twig', [
            "current_menu" => "Stocks",
            "tbody" => $tbody,
            "error" => $error
        ]);
    }
}