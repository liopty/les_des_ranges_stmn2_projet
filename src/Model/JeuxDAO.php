<?php


namespace App\Model;


class JeuxDAO
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


    public static function findAll($orderby = "uuid ASC"){
        return DB::findAll("jeux",$orderby);
    }

    public static function insert( $val = []){
        return DB::insert("jeux",$val);
    }

    public static function update( $val = [], $id = []){
        return DB::update("jeux",$val,$id);
    }

    public static function delete($param = []){
        return DB::delete("jeux",$param);
    }
}