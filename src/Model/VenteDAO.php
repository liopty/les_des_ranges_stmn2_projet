<?php


namespace App\Model;


class VenteDAO extends DB
{
    public static function findAll( $table="vente",$orderby = "date_creation ASC"){
        $table = "vente";
        $table2 = "vente_consommables";

        if (!array_key_exists($table, DB::getBddStructure()) || !array_key_exists($table2, DB::getBddStructure())) return null;


        $req = DB::getInstance()->prepare("SELECT v.uuidVente as uuidvente, v.uuidAdherent as uuidadherent, v.prix_total as prix_total, v.date_creation as date_creation, a.nom as anom, a.prenom as aprenom FROM vente v, adherent a WHERE v.uuidAdherent = a.uuidAdherent ORDER BY :orderby");
        try {
            $req->execute(array(
                'orderby' => $orderby
            ));
            return  $req->fetchAll();
        } catch (PDOException $erreur) {
            return "Erreur " . $erreur->getMessage();
        }
    }

    public static function insert($val = [],$table="vente"){
        if(parent::insert("vente",$val)){
            if(!array_key_exists("produits", $val)) return null;
            $res = true;
            foreach ($val["produits"] as $consommable => $qte){
                $res = DB::insert("vente_consommables",[
                   "uuidVente" => $val["uuidVente"],
                   "uuidConsommables" => $consommable,
                   "qte" => $qte
                ]);
                if(!$res) return "Erreur lors de l'insertion d'une vente_consommable";
            }
            return $res;
        }


        return "Erreur lors de l'insertion de la vente";


    }

    public static function getProduits($uuidVente){

        $req = DB::getInstance()->prepare("SELECT c.label as label, vc.qte as qte, v.prix_total as prix_total, c.prix_unitaire as prix_unitaire FROM vente v, consommables c, vente_consommables vc WHERE v.uuidVente = vc.uuidVente AND vc.uuidConsommables = c.uuidConsommables AND v.uuidVente = :uuidVente ORDER BY c.label");
        try {
            $req->execute(array(
                'uuidVente'=>$uuidVente
            ));
            return  $req->fetchAll();
        } catch (PDOException $erreur) {
            return "Erreur " . $erreur->getMessage();
        }
    }

}