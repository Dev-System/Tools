<?php

/**
 * Class SendMail | /core/SendMail/SendMail.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.3 (10/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\SendMail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Classe de gestion des envois d'email en PHP
 */
class SendMail extends PHPMailer {

    private $domaine;
    public $replyTo;
    public $destinataire;

    public function __construct() {

        parent::__construct();

        $this->domaine = preg_replace("/www\./", "", SYS_DOMAINE);
        $this->Sender = OPT_SMTP_EXPEDITEUR ? OPT_SMTP_EXPEDITEUR : 'noreply@' . $this->domaine;
        $this->From = OPT_SMTP_EXPEDITEUR ? OPT_SMTP_EXPEDITEUR : 'noreply@' . $this->domaine;
        $this->FromName = $this->domaine;
        $this->replyTo = OPT_SMTP_EXPEDITEUR ? OPT_SMTP_EXPEDITEUR : 'noreply@' . $this->domaine;

        $this->CharSet = 'UTF-8';
        $this->IsHTML(true);

        /** Utilisation d'un SMTP distant */
        if (OPT_SMTP == 1) {

            $this->IsSMTP();
            $this->SMTPAuth = true;
            $this->SMTPSecure = OPT_SMTP_SECURE;
            $this->Host = OPT_SMTP_HOST;
            $this->Port = OPT_SMTP_PORT;
            $this->Username = OPT_SMTP_USERNAME;
            $this->Password = OPT_SMTP_PASSWORD;
        }
    }

    public function __destruct() {

        parent::__destruct();
    }

    /**
     * Methode d'envoi de mail surchargée
     */
    public function send() {

        /** On ajoute l'adresse de réponse */
        $this->AddReplyTo($this->replyTo);

        /** On ajout les destinataires */
        $adresses = explode(',', $this->destinataire);
        foreach ($adresses as $adresse) {
            $this->AddAddress(trim($adresse));
        }

        return parent::Send();
    }

}
