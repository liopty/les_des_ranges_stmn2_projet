<?php


namespace App\Model;


use http\Exception;
use PDO;
use PDOException;

class DB
{
    private static $instance = null;
    ///////////// Paramètres de connexions avec DOCKER ///////////////////////////////////
    // Paramètres de connexion à la base de données
    private static $dbhost = "db"; // adresse du serveur : 'db', cf docker-compose.yml
    private static $dbport = 5432; // port du serveur
    private static $dbuname = "postgres"; // login
    private static $dbpass = "admin"; // mot de passe
    private static $dbname = "les_des_ranges"; // nom de la base de données

    private static $bddStructure = [
        "adherent"=> ["uuidAdherent","nom","prenom","date_naissance","mail","date_premiere_cotisation","date_derniere_cotisation","telephone","type_adhesion","personnes_rattachees","autre","date_creation","date_modification"],
    ];

    ////////////////////////////////////////////////////////////////////////////
    public static function getInstance()
    {
        if (self::$instance == null) {
            try {
                self::$instance = new PDO("pgsql:host=" . self::$dbhost . ";dbname=" . self::$dbname, self::$dbuname, self::$dbpass); // config CHEZ VOUS
                //self::$instance = new PDO("pgsql:user=VOTRE_LOGIN;dbname=VOTRE_LOGIN"); // => À LA FAC
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // report des erreurs.
            } catch (Exception $e) {
                die('Error : ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function findAll( $table = "error", $orderby = "uuid ASC")
    {
        $table = strtolower($table);

        if(!array_key_exists($table,DB::$bddStructure)) return null;

        $req = DB::getInstance()->prepare("SELECT * FROM " . $table . " ORDER BY :orderby ;");
        try {
            $req->execute(array(
                'orderby' => $orderby
            ));
            $res = $req->fetchAll();
            return $res;
        } catch (PDOException $erreur) {
            return null;
            //DEBUG return "Erreur " . $erreur->getMessage();
        }
    }

    public static function insert($table, $val = []){
        $bddStruct = DB::$bddStructure;
        $table = strtolower($table);
        $paramlength = count($val);
        if(!array_key_exists($table,$bddStruct) || $paramlength != count($bddStruct[$table])) return null;

        try {

            $attr = "";
            $param = "";
            for ($i = 0; $i < $paramlength; $i++){
                $attr .= $bddStruct[$table][$i].",";
                $param .= "?,";
            }
            $attr = substr($attr, 0, -1);
            $param = substr($param, 0, -1);
            $req = DB::getInstance()->prepare("INSERT INTO ".$table." (".$attr.") VALUES (".$param.");");
            for ($i = 0; $i < $paramlength; $i++){
                $req->bindParam($i+1,$val[$i]);
            }
            $req->execute();

            return "Success";

        }catch (PDOException $erreur) {
            return null;
            //DEBUG return "Erreur " . $erreur->getMessage();
        }

    }

    public static function disconnect()
    {
        self::$instance = null;
    }
}
