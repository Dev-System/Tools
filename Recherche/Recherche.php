<?php

/**
 * Class Recherche | /lib/class/Recherche.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (03/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Recherche;

use Easyd\Db\Db;

/**
 * Classe de recherche, associée aux méthodes getListe()
 */
class Recherche {

    public $Param = [];
    public $nom;
    public $table;
    public $criteres;
    private $requete;

    function RechSql($nom = '', $table = '') {

        $this->nom = $nom;
        $this->table = $table;
        $this->criteres = (!empty($_SESSION[$this->nom])) ? $_SESSION[$this->nom] : array();
        $this->requete = '';
    }

    public function Criteres() {

        $_SESSION[$this->nom] = $this->criteres;

        return $this->criteres;
    }

    public function FormatRech($valeur) {

        $valeur = trim($valeur);
        $valeur = str_replace("\’", "%", $valeur);
        $valeur = str_replace(' ', '%', $valeur);
        $valeur = str_replace(array('ò', 'ó', 'ô', 'õ', 'ö'), 'o', $valeur);
        $valeur = str_replace(array('é', 'è', 'ê', 'ë'), 'e', $valeur);
        $valeur = str_replace(array('à', 'á', 'â', 'ã', 'ä'), 'a', $valeur);
        $valeur = str_replace(array('ù', 'ú', 'û', 'ü'), 'u', $valeur);
        $valeur = str_replace(array('ý', 'ÿ'), 'y', $valeur);
        $valeur = str_replace(array('ì', 'í', 'î', 'ï'), 'i', $valeur);
        $valeur = str_replace('ç', 'c', $valeur);
        $valeur = str_replace('ñ', 'n', $valeur);
        $valeur = str_replace('____', '%', $valeur);
        $valeur = str_replace('___', '%', $valeur);
        $valeur = str_replace('__', '%', $valeur);
        $valeur = str_replace('_', '%', $valeur);

        return $valeur;
    }

    // Requete de recherche
    public function RequeteSql() {

        if ($this->criteres) {

            $nbCrit = count($this->Param);

            for ($i = 0; $i < $nbCrit; $i++) {

                $what = Db::cSQL($this->Param[$i][0]);
                $where = Db::cSQL($this->Param[$i][1]);
                $exact = $this->Param[$i][2];
                $type = $this->Param[$i][3];
                $table = (!empty($this->Param[$i][4])) ? $this->Param[$i][4] : $this->table;

                if ($what && $where) {

                    $where = $table . '.' . $where;

                    if ($type == 'date') {
                        $what = $this->DateFormatSql($what);
                    }

                    if ($type == 'dateAnnee') {
                        $what = $what . '01-01';
                    }

                    if ($type == 'boolean') {
                        if ($what == 'true') {
                            $what = 1;
                        } else {
                            $what = 0;
                        }
                    }

                    if ($exact == '=') {
                        $this->requete .= 'AND ' . $where . '="' . $what . '" ';
                    } else if ($exact == 'x%') {
                        $this->requete .= 'AND ' . $where . ' LIKE "' . $what . '%" ';
                    } else if ($exact == '%x') {
                        $this->requete .= 'AND ' . $where . ' LIKE "%' . $what . '" ';
                    } else if ($exact == '<') {
                        $this->requete .= 'AND ' . $where . '<"' . $what . '" ';
                    } else if ($exact == '>') {
                        $this->requete .= 'AND ' . $where . '>"' . $what . '" ';
                    } else if ($exact == '<=') {
                        $this->requete .= 'AND ' . $where . '<="' . $what . '" ';
                    } else if ($exact == '>=') {
                        $this->requete .= 'AND ' . $where . '>="' . $what . '" ';
                    } else {
                        $this->requete .= 'AND ' . $where . ' LIKE "%' . $this->FormatRech($what) . '%" ';
                    }
                }
            }
        }

        return $this->requete;
    }

    // Efface la recherche
    public function Efface() {

        unset($_SESSION[$this->nom]);
        unset($this->criteres);
    }

}
