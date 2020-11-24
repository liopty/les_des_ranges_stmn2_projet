<?php


namespace App\Model;


use PDOException;

class EmpruntDAO extends DB
{


    public static function findAll( $table="emprunt",$orderby = "date_retourprevu ASC"){
        $table = strtolower("emprunt");

        if (!array_key_exists($table, DB::getBddStructure())) return null;


        $req = DB::getInstance()->prepare("SELECT e.uuidEmprunt, e.uuidAdherent, e.uuidJeux, e.date_emprunt, e.date_retourprevu, e.date_retour, a.nom as anom, j.nom as jnom, a.prenom as aprenom FROM emprunt e, adherent a, jeux j WHERE e.uuidAdherent = a.uuidAdherent AND e.uuidJeux = j.uuidJeux ORDER BY :orderby");
        try {
            $req->execute(array(
                'orderby' => $orderby
            ));
            return  $req->fetchAll();
        } catch (PDOException $erreur) {
            return "Erreur " . $erreur->getMessage();
        }
    }

    public static function insert($val = [],$table="emprunt"){
        return parent::insert("emprunt",$val);
    }

    public static function update($val = [], $id = [],$table="emprunt"){
        return parent::update("emprunt",$val,$id);
    }

    public static function delete($param = [], $table="emprunt"){
        return parent::delete("emprunt",$param);
    }

    public static function GetTopEmprunt($nbTop)
    {
        return parent::GetTopEmprunt($nbTop);
    }
}