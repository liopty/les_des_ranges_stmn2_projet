<?php


namespace App\Model;


class ConsommablesDAO
{
    public static function findAll($orderby = "uuid ASC"){
        return DB::findAll("consommables",$orderby);
    }

    public static function insert( $val = []){
        return DB::insert("consommables",$val);
    }

    public static function update( $val = [], $id = []){
        return DB::update("consommables",$val,$id);
    }

    public static function delete($param = []){
        return DB::delete("consommables",$param);
    }
}