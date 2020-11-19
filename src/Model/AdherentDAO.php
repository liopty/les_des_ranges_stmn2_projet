<?php


namespace App\Model;


class AdherentDAO
{
    public static function findAll($orderby = "uuid ASC"){
        return DB::findAll("adherent",$orderby);
    }

    public static function insert( $val = []){
        DB::insert("adherent",$val);
    }
}