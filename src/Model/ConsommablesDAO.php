<?php


namespace App\Model;


use PDOException;

class ConsommablesDAO extends DB
{
    public static function findAll($orderby = "label ASC", $table = "consommables"){
        return parent::findAll("consommables",$orderby);
    }

    public static function insert( $val = [], $table = "consommables"){
        return parent::insert("consommables",$val);
    }

    public static function update( $val = [], $id = [], $table = "consommables"){
        return parent::update("consommables",$val,$id);
    }

    public static function delete($param = [], $table = "consommables"){
        return parent::delete("consommables",$param);
    }

    public static function getConsommablesLabelUuid(){
        $table = "consommables";

        if (!array_key_exists($table, DB::getBddStructure())) return null;


        $req = DB::getInstance()->prepare("SELECT uuidConsommables, label FROM " . $table . " ORDER BY label ;");
        try {
            $req->execute();
            return  $req->fetchAll();;
        } catch (PDOException $erreur) {
            //return null;
            return "Erreur " . $erreur->getMessage();
        }
    }
}