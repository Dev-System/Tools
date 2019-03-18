<?php

/**
 * Class Tools | /core/Tools/Tools.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.1 (11/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Tools;

/**
 * Classe fournissant quelques outils
 */
class Tools {

    public static $errorMessage;
    public static $alerteMessage;
    public static $confMessage;
    public static $infosMessage;

    /**
     * Verification de la validité d'un email
     * 
     * @param string $email
     * @return bool
     */
    public static function checkEmail(string $email): bool {

        $syntaxe = '#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#';

        if (preg_match($syntaxe, $email)) {

            list($user, $domaine) = preg_split("/@/", $email, 2);
            if (checkdnsrr($domaine, "MX")) {
                return true;
            }
        }
        return false;
    }

    /**
     * Formate un numéro de téléphone
     *
     * @param string $chaine
     * @return string
     */
    public static function formatTel(string $chaine): string {

        $chaine = trim($chaine);
        $chaine = str_replace("\t", " ", $chaine);
        $chaine = str_replace(" ", "", $chaine);
        $chaine = preg_replace("/[ ]+/", " ", $chaine);

        return $chaine;
    }

}
