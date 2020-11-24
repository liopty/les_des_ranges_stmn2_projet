<?php


namespace App\Model;


class AdherentDAO extends DB
{
    public static function findAll($orderby = "nom ASC",$table = "adherent"){
        return parent::findAll("adherent",$orderby);
    }

    public static function insert( $val = [],$table = "adherent"){
        return parent::insert("adherent",$val);
    }

    public static function update( $val = [], $id = [],$table = "adherent"){
        return parent::update("adherent",$val,$id);
    }

    public static function delete($param = [],$table = "adherent"){
        return parent::delete("adherent",$param);
    }

    public static function getAdherentNomPrenomUuid(){
        $table = "adherent";

        if (!array_key_exists($table, DB::getBddStructure())) return null;


        $req = DB::getInstance()->prepare("SELECT uuidAdherent, nom, prenom FROM " . $table . " ORDER BY nom ;");
        try {
            $req->execute();
            return  $req->fetchAll();;
        } catch (PDOException $erreur) {
            //return null;
            return "Erreur " . $erreur->getMessage();
        }
    }
}