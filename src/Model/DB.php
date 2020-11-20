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
        "adherent" => ["uuidAdherent", "nom", "prenom", "date_naissance", "mail", "date_premiere_cotisation", "date_derniere_cotisation", "telephone", "type_adhesion", "personnes_rattachees", "autre", "date_creation", "date_modification"],
        "emprunt" => ["uuidEmprunt", "uuidJeux", "uuidAdherent", "date_emprunt", "date_retourprevu", "date_retour", "date_creation", "date_modification"],
        "jeux" => ["uuidJeux","nom","code","categorie","etat","description","isDisponible","date_achat","date_creation","date_modification"],
        "consommables" => ["uuidConsommables","label","prix_unitaire","qte","date_creation","date_modification"],
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

    public static function findAll($table = "error", $orderby = "uuid ASC")
    {
        $table = strtolower($table);

        if (!array_key_exists($table, DB::$bddStructure)) return null;

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

    public static function insert($table, $val = [])
    {
        $bddStruct = DB::$bddStructure;
        $table = strtolower($table);
        if (!array_key_exists($table, $bddStruct)) return null;

        try {
            $paramlength = count($bddStruct[$table]);

            $attr = "";
            $param = "";
            $orderedValArray = [];

            for ($i = 0; $i < $paramlength; $i++) {
                if (array_key_exists($bddStruct[$table][$i], $val)) {
                    $attr .= $bddStruct[$table][$i] . ",";
                    $param .= "?,";
                    $orderedValArray[] = $val[$bddStruct[$table][$i]];
                }
            }
            $nbParam = count($orderedValArray);


            if ($nbParam > 0) {
                $attr = substr($attr, 0, -1);
                $param = substr($param, 0, -1);

                $req = DB::getInstance()->prepare("INSERT INTO " . $table . " (" . $attr . ") VALUES (" . $param . ");");
                for ($i = 0; $i < $nbParam; $i++) {
                    $req->bindParam($i + 1, $orderedValArray[$i]);
                }
                return $req->execute();

            }

            return null;

        } catch (PDOException $erreur) {
            return null;
            //DEBUG return "Erreur " . $erreur->getMessage();
        }
    }

    public static function update($table, $val = [], $id = [])
    {
        $bddStruct = DB::$bddStructure;
        $table = strtolower($table);
        if (!array_key_exists($table, $bddStruct)) return null;

        try {
            $paramlength = count($bddStruct[$table]);

            $attr = "";
            $orderedValArray = [];

            $idAttr = "";
            $orderedIdArray = [];

            for ($i = 0; $i < $paramlength; $i++) {
                if (array_key_exists($bddStruct[$table][$i], $val)) {
                    $attr .= $bddStruct[$table][$i] . " = ?,";
                    $orderedValArray[] = $val[$bddStruct[$table][$i]];
                }
                if (array_key_exists($bddStruct[$table][$i], $id)) {
                    $idAttr .= $bddStruct[$table][$i] . " = ? AND ";
                    $orderedIdArray[] = $id[$bddStruct[$table][$i]];
                }
            }

            $nbParam = count($orderedValArray);
            $nbid = count($orderedIdArray);

            if ($nbParam > 0 && $nbid > 0) {
                $attr = substr($attr, 0, -1);
                $idAttr = substr($idAttr, 0, -5);
                $req = DB::getInstance()->prepare("UPDATE " . $table . " SET " . $attr . " WHERE " . $idAttr . ";");
                $j = 0;
                for ($i = 0; $i < $nbParam; $i++) {
                    $j++;
                    $req->bindParam($j, $orderedValArray[$i]);
                }
                for ($i = 0; $i < $nbid; $i++) {
                    $j++;
                    $req->bindParam($j, $orderedIdArray[$i]);
                }
                return $req->execute();

            }
            return null;


        } catch (PDOException $erreur) {
            //return null;
            return "Erreur " . $erreur->getMessage();
        }
    }

    public static function delete($table, $param = [])
    {
        $bddStruct = DB::$bddStructure;
        $table = strtolower($table);
        if (!array_key_exists($table, $bddStruct)) return null;

        try {
            $paramAttr = "";
            $orderedParamArray = [];

            foreach ($param as $key => $val){
                if (in_array($key,$bddStruct[$table])) {
                    $paramAttr .= $key . " = ? AND ";
                    $orderedParamArray[] = $val;
                }
            }

            $nbParam = count($orderedParamArray);
            if ($nbParam > 0 ) {
                $paramAttr = substr($paramAttr, 0, -5);
                $req = DB::getInstance()->prepare("DELETE FROM " . $table . " WHERE " . $paramAttr . ";");

                for ($i = 0; $i < $nbParam; $i++) {
                    $req->bindParam($i+1, $orderedParamArray[$i]);
                }
                return $req->execute();

            }
            return null;

        } catch (PDOException $erreur) {
            //return null;
            return "Erreur " . $erreur->getMessage();
        }
    }

    //---------------------EMPRUNT-----------------------------------------
    public static function GetTopEmprunt($nbTop)
    {
         try {
                $req = DB::getInstance()->prepare("SELECT topJeuxEmpruntes(?);");
                $req->bindParam( 1, $nbTop);
                $req->execute();
                return $req->fetchAll();
         }
         catch (PDOException $erreur) {
            //return null;
             return "Erreur dans la requette GetTopEmprunt" . $erreur->getMessage();
        }
    }

    public static function disconnect()
    {
        self::$instance = null;
    }
}
