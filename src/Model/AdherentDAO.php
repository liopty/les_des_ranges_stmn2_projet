<?php


namespace App\Model;


class AdherentDAO
{
    public static function findAll($orderby = "uuid ASC"){
        return DB::findAll("adherent",$orderby);
    }

    public static function insert( $val = []){
        return DB::insert("adherent",$val);
    }

    public static function update( $val = [], $id = []){
        return DB::update("adherent",$val,$id);
    }

    public static function delete($param = []){
        return DB::delete("adherent",$param);
    }
}