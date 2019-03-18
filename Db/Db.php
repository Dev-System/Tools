<?php

/**
 * Class Db | /core/Db/Db.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.3 (14/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Db;

use PDO;
use Easyd\Pagination\Pagination;
use Easyd\SendMail\SendMail;
use Easyd\Message\Message;

/**
 * Classe de connexion à la base de données
 */
class Db {

    protected $utilisateur;
    private static $instance = NULL;
    private $server;
    private $login;
    private $password;
    private $base;
    private $user;
    private $link;
    private $start;

    public function __construct(string $server = DB_SERVEUR, string $login = DB_USER, string $password = DB_PASSWORD, string $base = DB_BASE) {

        $this->server = $server;
        $this->login = $login;
        $this->password = $password;
        $this->base = $base;
        $this->user = !empty($_SESSION['admin']['log']) ?? 'Unknown';
        $this->start = $this->microtimeFloat();

        $this->connectBdd();
    }

    public function __destruct() {

        if (mysqli_ping($this->link)) {
            $this->closeBdd();
        }
    }

    /**
     * Crée et retourne l'objet SPDO
     *
     * @access public
     * @static
     * @param void
     * @return SPDO $instance
     */
    public static function getInstance() {

        if (is_null(self::$instance)) {
            self::$instance = new Db();
        }

        return self::$instance;
    }

    public function ping() {

        try {
            $this->link->query('SELECT 1');
        } catch (PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Connection au serveur
     */
    public function connectBdd() {

        try {

            $this->link = new PDO('mysql:host=' . $this->server . ';dbname=' . $this->base, $this->login, $this->password);
            $this->link->exec("set names utf8");
        } catch (PDOException $e) {

            $this->sqlError($e->getMessage(), '', 1);
        }
    }

    public function getLink() {
        return $this->link;
    }

    /**
     * Calcul temps chargement
     *
     * @return float
     */
    private function microtimeFloat() {

        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
     * Renvoi le temps de chargement
     *
     * @return string
     */
    public function tmpChargement() {

        $temps = round($this->microtimeFloat() - $this->start, 3);
        return 'Page générée en ' . $temps . ' secondes';
    }

    /**
     * Deconnexion du serveur
     */
    public function closeBdd() {

        $this->link = null;
    }

    /**
     * Controle inclusion de requetes
     *
     * @param string $string
     * @param booleen $html
     * @return string
     */
    public function controle($string, $option = '') {

        // Suppression des espaces inutile
        $string = trim($string);

        // Echappement des caractères speciaux
        $this->link->quote($string);

        // Vérification et encodage des données en UTF-8
        $string = mb_detect_encoding($string, 'UTF-8', true) ? $string : utf8_encode($string);

        // Si option est un tableau
        if (is_array($option)) {

            // Suppression du code html
            if (in_array('html', $option)) {
                $string = strip_tags(nl2br($string));
            }

            // Ajoute des doubles quotes si valeur différent de "NULL"
            if ((($string == 'NULL') || (!$string)) && in_array('NULL', $option)) {
                $string = 'NULL';
            } else {
                $string = '"' . $string . '"';
            }
        } else {

            // Suppression du code html
            if (!$option) {
                $string = strip_tags(nl2br($string));
            }
        }

        return $string;
    }

    /**
     * Appel le controle d'inclusion de requetes
     *
     * @param string $string
     * @param booleen $html
     * @return string
     */
    public static function cSQL($string, bool $html = false) {

        return self::getInstance()->controle($string, $html);
    }

    /**
     * Requette sql object
     *
     * @param string $sql
     * @return string|null
     */
    public function reqObject(string $sql = ''): ?string {

        $stmt = $this->link->prepare($sql);

        if ($stmt->execute()) {

            /** Renvoi la valeur de la première colonne trouvé */
            return $stmt->fetchColumn(0);
        } else {

            /** Renvoi le message d'erreur */
            $this->sqlError($stmt->errorInfo()[2], $sql, 0);
        }

        return null;
    }

    /**
     * Requette sql sur 1 ligne
     *
     * @param string $sql
     * @return array|null
     */
    public function reqArrayS(string $sql = '') {

        /** Préparation de la requête */
        $stmt = $this->link->prepare($sql);

        if ($stmt->execute()) {

            /** Renvoi la valeur de la première colonne trouvé */
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch();
            }
        } else {

            /** Renvoi le message d'erreur */
            $this->sqlError($stmt->errorInfo()[2], $sql, 0);
        }

        return null;
    }

    /**
     * Requette sql tableau multiple
     *
     * @param string $sql
     * @return array|null
     */
    public function reqArrayM(string $sql = ''): ?array {

        /** Préparation de la requête */
        $stmt = $this->link->prepare($sql);

        if ($stmt->execute()) {

            /** Renvoi la liste des enregistrement trouvés */
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll();
            }
        } else {

            /** Renvoi le message d'erreur */
            $this->sqlError($stmt->errorInfo()[2], $sql, 0);
        }

        return null;
    }

    /**
     * Requette sql tableau multiple en liste multi-page
     *
     * @param string $sql
     * @param int $nbByPages
     * @return array
     */
    public function reqMultiPage($sql, $nbByPages = 0) {

        $Pagination = new Pagination();

        $Pagination->nbByPages = $nbByPages ? $nbByPages : $Pagination->nbByPages;
        $Pagination->adresse = preg_replace(array('/\?limite(.*)/', '/\&limite(.*)/'), '', $_SERVER['REQUEST_URI']);
        $Pagination->limite = (!empty($_GET['limite'])) ? intval($this->controle($_GET['limite'])) : 0;
        $Pagination->limite = (!empty($_POST['page'])) ? (intval(($this->controle($_POST['page'])) - 1) * $Pagination->nbByPages) : $Pagination->limite;

        $liste = $this->reqArrayM($sql . ' LIMIT ' . $Pagination->limite . ',' . $Pagination->nbByPages);

        if (!$liste && $Pagination->limite) {

            $Pagination->limite = 0;
            $liste = $this->reqArrayM($sql . ' LIMIT ' . $Pagination->limite . ',' . $Pagination->nbByPages);
        }

        $Pagination->nbTotal = $this->reqCount($sql);
        $this->nbTotal = $Pagination->nbTotal;
        $this->BarreNavigation = $Pagination->BarreNavigation();
        $this->limite = $Pagination->limite;

        return $liste;
    }

    /**
     * Requette sql compte le nombre d'entrée
     *
     * @param string $sql
     * @return int|null
     */
    public function reqCount(string $sql = ''): ?int {

        return count($this->reqArrayM($sql));
    }

    /**
     * Requette sql supprime une entrée
     *
     * @param string $sql
     * @param string $table
     * @return int
     */
    public function delSql(string $params = null, string $table = null): ?int {

        /** Construction de la requête */
        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $params;

        /** Préparation de la requête */
        $stmt = $this->link->prepare($sql);

        if ($stmt->execute()) {

            /** Optimisation de la table */
            $this->link->query('OPTIMIZE TABLE ' . $table);

            /** Retourne le nombre de lignes affectées */
            return $stmt->rowCount();
        } else {

            /** Renvoi le message d'erreur */
            $this->sqlError($stmt->errorInfo()[2], $sql, 0);
        }

        return null;
    }

    /**
     * Requette sql insert une entrée
     *
     * @param string $sql
     * @return int
     */
    public function newSql(string $sql = ''): ?int {

        /** Préparation de la requête */
        $stmt = $this->link->prepare($sql);

        if ($stmt->execute()) {

            /** Retourne le dernier ID créé */
            return $this->link->lastInsertId();
        } else {

            /** Renvoi le message d'erreur */
            $this->sqlError($stmt->errorInfo()[2], $sql, 0);
        }

        return null;
    }

    /**
     *  Requette sql update une entrée
     *
     * @param string $sql
     * @param int $message
     * @param string $table
     * @param string $user
     * @param int $id
     * @return int|null
     */
    public function upSql(string $sql = '', int $message = 0, string $table = '', string $user = '', int $id = 0): ?int {

        /** Préparation de la requête */
        $stmt = $this->link->prepare($sql);

        if ($stmt->execute()) {

            $nbRowUpdate = $stmt->rowCount();

            if ($nbRowUpdate >= 1) {

                if ($message == 1) {
                    Message::getInstance()->add('success', 'Enregistrement réussi');
                }

                /** Enregistrement de la date et du user de la modification */
                if ($table && $user && $id) {

                    $sql = 'UPDATE ' . $table . ' SET '
                            . 'date_upd=NOW(), '
                            . 'user_upd="' . $this->controle($user) . '" '
                            . 'WHERE id="' . $this->controle($id) . '" ';

                    $stmt = $this->link->prepare($sql);
                    $stmt->execute();
                }
            } else {
                if ($message == 1) {
                    Message::getInstance()->add('alerte', 'Aucune modification');
                }
            }

            return $nbRowUpdate;
        } else {

            if ($message == 1) {

                Message::getInstance()->add('alerte', 'Problème lors de l\'enregistrement');
            }

            /** Renvoi le message d'erreur */
            $this->sqlError($stmt->errorInfo()[2], $sql, 0);
        }

        return null;
    }

    public function reqFetchObject(string $sql = null, string $className = null) {

        /** Préparation de la requête */
        $stmt = $this->link->prepare($sql);

        if ($stmt->execute()) {

            /** Retourne le dernier ID créé */
            return $stmt->fetchObject($className);
        } else {

            /** Renvoi le message d'erreur */
            $this->sqlError($stmt->errorInfo()[2], $sql, 0);
        }

        return null;
    }

    public function reqFetchAllObject(string $sql = null, string $className = null) {

        /** Préparation de la requête */
        $stmt = $this->link->prepare($sql);

        if ($stmt->execute()) {

            /** Retourne le dernier ID créé */
            return $stmt->fetchAll(PDO::FETCH_CLASS, $className);
        } else {

            /** Renvoi le message d'erreur */
            $this->sqlError($stmt->errorInfo()[2], $sql, 0);
        }

        return null;
    }

    /**
     * Requette sql Basique
     *
     * @param string $sql
     * @return string
     */
    public function cmdQuery(string $sql = ''): bool {

        /** Préparation de la requête */
        $stmt = $this->link->prepare($sql);

        if ($stmt->execute()) {

            return true;
        } else {

            /** Renvoi le message d'erreur */
            $this->sqlError($stmt->errorInfo()[2], $sql, 0);
        }

        return false;
    }

    /**
     * Formate une date au format sql
     *
     * @param date $date
     * @return string
     */
    public static function dateFormatSql($date) {

        $dateSql = NULL;

        $dateTab = explode('/', $date);

        $jour = (!empty($dateTab[0])) ? $dateTab[0] : '00';
        $mois = (!empty($dateTab[1])) ? $dateTab[1] : '00';
        $annee = (!empty($dateTab[2])) ? $dateTab[2] : '0000';

        if (checkdate($mois, $jour, $annee) != false) {
            $dateSql = $annee . '-' . $mois . '-' . $jour;
        }

        return $dateSql;
    }

    /**
     * Import SQL d'une date avec option "NULL"
     *
     * @param date $date
     * @return string
     */
    public static function dateImportSql($date) {

        $dateSql = 'NULL';

        $dateTab = explode('-', $date);

        $jour = (!empty($dateTab[2])) ? substr($dateTab[2], 0, 2) : '00';
        $mois = (!empty($dateTab[1])) ? $dateTab[1] : '00';
        $annee = (!empty($dateTab[0])) ? $dateTab[0] : '0000';

        if (checkdate($mois, $jour, $annee) != false) {
            $dateSql = '"' . $date . '"';
        }

        return $dateSql;
    }

    /**
     * Traitement des erreurs
     *
     * @param string $error
     * @param string $sql
     * @param booleen $error
     * @return string
     */
    private function sqlError($error, $sql, $verrou = 0) {

        $message = '<table><tr><td>&nbsp;</td></tr>'
                . '<tr><td><b>Date : </b>' . date('d/m/Y à H:i') . '</td></tr>'
                . '<tr><td><b>Utilisateur : </b>' . $this->utilisateur . '</td></tr>'
                . '<tr><td>&nbsp;</td></tr>'
                . '<tr><td><b>Erreur : </b>' . $error . '</td></tr>'
                . '<tr><td><b>Requete : </b>' . $sql . '</td></tr>'
                . '<tr><td>&nbsp;</td></tr>'
                . '<tr><td><b>Script : </b>' . DOC_ROOT . $_SERVER['PHP_SELF'] . '</td></tr>'
                . '<tr><td>&nbsp;</td></tr>'
                . '<tr><td><b>Url : </b>' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '</td></tr></table>';

        $this->destinataire = OPT_RAPPORT_EMAIL;

        if ($this->destinataire) {

            $mail = new SendMail();

            $mail->Subject = 'Erreur SQL';
            $mail->Body = '<html><head></head><body>' . $message . '</body></html>';
            $mail->destinataire = $this->destinataire;

            $mail->send();
        }

        if ($verrou) {

            echo "Site en maintenance...";
            exit();
        }
    }

}
