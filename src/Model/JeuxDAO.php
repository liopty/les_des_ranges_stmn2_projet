<?php


namespace App\Model;


use PDOException;

class JeuxDAO extends DB
{
    public static $categories = [
        "3-6ans" => '3_6ans',
        "6-8ans" => '6_8ans',
        "Famille" => 'Famille',
        "Amateur" => 'Amateur',
        "Expert" => 'Expert',
        "Solo ou à 2" => 'Solo_ou_a_2',
        "Ambiance" => 'Ambiance'

    ];

    public static $etats = [
        "Neuf" => 'Neuf',
        "Très bon" => 'Tres_bon',
        "Bon" => 'Bon',
        "Passable" => 'Passable',
        "Médiocre" => 'Mediocre',
        "Incomplet" => 'Incomplet',
        "Autre" => 'Autre'
    ];


    public static function findAll($orderby = "nom ASC", $table = "jeux"){
        return DB::findAll("jeux",$orderby);
    }

    public static function insert( $val = [], $table = "jeux"){
        return DB::insert("jeux",$val);
    }

    public static function update( $val = [], $id = [], $table = "jeux"){
        return DB::update("jeux",$val,$id);
    }

    public static function delete($param = [], $table = "jeux"){
        return DB::delete("jeux",$param);
    }

    public static function getJeuxNomUuid(){
        $table = "jeux";

        if (!array_key_exists($table, DB::getBddStructure())) return null;

        $req = DB::getInstance()->prepare("SELECT uuidJeux, nom FROM " . $table . " ORDER BY nom;");
        try {
            $req->execute();
            return  $req->fetchAll();;
        } catch (PDOException $erreur) {
            //return null;
            return "Erreur " . $erreur->getMessage();
        }
    }
}