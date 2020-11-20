<?php


namespace App\Model;


class EmpruntDAO
{
    public static function findAll($orderby = "uuid ASC"){
        return DB::findAll("emprunt",$orderby);
    }

    public static function insert( $val = []){
        return DB::insert("emprunt",$val);
    }

    public static function update( $val = [], $id = []){
        return DB::update("emprunt",$val,$id);
    }

    public static function delete($param = []){
        return DB::delete("emprunt",$param);
    }

    public static function GetTopEmprunt($nbTop)
    {
        return DB::GetTopEmprunt($nbTop);
    }
}