<?php

/**
 * Class PhpError | /core/PhpError/PhpError.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.3 (16/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\PhpError;

use Easyd\SendMail\SendMail;

/**
 * Classe de gestion des erreurs
 */
class PhpError {

    private $message;
    private $nbError;
    private $auth;

    public function __construct($auth) {
        
        $this->auth = $auth;

        $this->message = '';
        $this->nbError = 0;

        set_error_handler(array($this, "myErrorHandler"));
    }

    public function __destruct() {

        if (!empty($this->message)) {

            $this->sendError();
        }
    }

    public function myErrorHandler($errno, $errstr, $errfile, $errline) {

        if (!(error_reporting() & $errno)) {
            // Ce code d'erreur n'est pas inclus dans error_reporting()
            return;
        }

        switch ($errno) {

            case E_USER_ERROR:
                $Type = 'Erreur fatale1';
                break;

            case E_USER_WARNING:
                $Type = 'Alerte1';
                break;

            case E_USER_NOTICE:
                $Type = 'Notice1';
                break;

            case E_ERROR:
                $Type = 'Erreur fatale';
                break;

            case E_WARNING:
                $Type = 'Alerte';
                break;

            case E_NOTICE:
                $Type = 'Notice';
                break;

            default:
                $Type = 'Type d\'erreur inconnu';
                break;
        }

        $this->message .= '<tr><td><b>Date : </b>' . date('d/m/Y à H:i') . '</td></tr>
                           <tr><td><b>Type : </b>' . $Type . ' (' . $errno . ')</td></tr>
                           <tr><td><b>Erreur : </b>' . $errstr . '</td></tr>
                           <tr><td><b>Fichier : </b>' . $errfile . '</td></tr>
                           <tr><td><b>Ligne : </b>' . $errline . '</td></tr>
                           <tr><td><b>Url : </b>' . $_SERVER['REQUEST_URI'] . '</td></tr>
                           <tr><td><b>Utilisateur : </b>' . $this->auth->getPseudo() . '</td></tr>
                           <tr><td>&nbsp;</td></tr>';

        $this->nbError++;

        if ($this->nbError >= 20) {

            $this->sendError();
            exit();
        }

        return true;
    }

    private function sendError() {

        /** Si "SuperAdmin" affichage à l'écran en plus de l'email */
        if ($this->auth->getLevel() >= 6) {
            echo '<table>' . $this->message . '</table>';
        }

        if (OPT_RAPPORT_EMAIL) {

            $mail = new SendMail();

            $mail->Subject = 'Erreur PHP';
            $mail->Body = '<html><body><table>' . $this->message . '</table></body></html>';
            $mail->destinataire = OPT_RAPPORT_EMAIL;

            $mail->send();
        }
    }

}
