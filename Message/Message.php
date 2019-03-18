<?php

/**
 * Class Message | /core/Message/Message.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.2 (13/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Message;

/**
 * Classe de gestion des messages système
 */
class Message {

    private $danger = [];
    private $warning = [];
    private $success = [];
    private $info = [];
    private $sessionName = 'Message';
    private static $instance = NULL;

    public function __construct() {

        if (!empty($_SESSION[$this->sessionName]['danger'])) {
            $this->danger = $_SESSION[$this->sessionName]['danger'];
        }
        if (!empty($_SESSION[$this->sessionName]['warning'])) {
            $this->warning = $_SESSION[$this->sessionName]['warning'];
        }
        if (!empty($_SESSION[$this->sessionName]['success'])) {
            $this->success = $_SESSION[$this->sessionName]['success'];
        }
        if (!empty($_SESSION[$this->sessionName]['info'])) {
            $this->info = $_SESSION[$this->sessionName]['info'];
        }

        unset($_SESSION[$this->sessionName]);
    }

    public function __destruct() {
        if ($this->danger) {
            $_SESSION[$this->sessionName]['danger'] = $this->danger;
        }
        if ($this->warning) {
            $_SESSION[$this->sessionName]['warning'] = $this->warning;
        }
        if ($this->success) {
            $_SESSION[$this->sessionName]['success'] = $this->success;
        }
        if ($this->info) {
            $_SESSION[$this->sessionName]['info'] = $this->info;
        }
    }

    /**
     * Crée et retourne l'objet Message
     *
     * @return Message $instance
     */
    public static function getInstance() {

        if (is_null(self::$instance)) {
            self::$instance = new Message();
        }

        return self::$instance;
    }

    /**
     * Enregistre un message en fonction du type
     *
     * @param string $message
     * @param string $type
     */
    public function add(string $type, string $message) {

        if ($message && ($type == 'danger' || $type == 'error')) {
            $this->danger[] = $message;
        }

        if ($message && ($type == 'warning' || $type == 'alerte')) {
            $this->warning[] = $message;
        }

        if ($message && ($type == 'success' || $type == 'conf')) {
            $this->success[] = $message;
        }

        if ($message && ($type == 'info' || $type == 'infos')) {
            $this->info[] = $message;
        }
    }

    /**
     * Indique si une ou plusieurs erreurs existe
     *
     * @return booleen
     */
    public function getError() {

        if ($this->danger) {
            return true;
        }
        return false;
    }

    /**
     * Renvoi le message formaté
     * 
     * @param string $texte
     * @param string $type
     * @return string
     */
    private function getMessageHtml($texte, $type) {

        if ($type == 'danger') {
            $picto = '<i class="fas fa-exclamation-triangle fa-2x"></i> ';
        } else if ($type == 'warning') {
            $picto = '<i class="fas fa-exclamation-circle fa-2x"></i> ';
        } else if ($type == 'success') {
            $picto = '<i class="fas fa-check fa-2x"></i> ';
        } else if ($type == 'info') {
            $picto = '<i class="fas fa-info-circle fa-2x"></i> ';
        }

        $messages = implode('<br>', $texte);

        $html = '<div class="alert alert-' . $type . ' alert-dismissible fade show row mx-0 px-0" role="alert">'
                . '<div class="col-auto align-self-center">' . $picto . '</div><div class="col-auto align-self-center">' . $messages . '</div>'
                . '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
                . '<span aria-hidden="true">&times;</span>'
                . '</button>'
                . '</div>';

        return $html;
    }

    /**
     * Renvoi le(s) message(s) enregistré(s)
     *
     * @param string $type
     * @return string
     */
    public function displayMessage($type = 'all') {

        $messages = NULL;

        /** Messages type "danger" */
        if (($type == 'danger') || ($type == 'all')) {

            if ($this->danger) {
                $messages .= $this->getMessageHtml($this->danger, 'danger');
            }
        }

        /** Messages type "alerte" */
        if (($type == 'warning') || ($type == 'all')) {

            if ($this->warning) {
                $messages .= $this->getMessageHtml($this->warning, 'warning');
            }
        }

        /** Messages type "conf" */
        if (($type == 'success') || ($type == 'all')) {

            if ($this->success) {
                $messages .= $this->getMessageHtml($this->success, 'success');
            }
        }

        /** Messages type "infos" */
        if (($type == 'info') || ($type == 'all')) {

            if ($this->info) {
                $messages .= $this->getMessageHtml($this->info, 'info');
            }
        }

        $this->destructAllMessages();

        $messages .= '<div class="load" style="display: none" id="load"><div class="barreAction">'
                . '<i class="fas fa-circle-notch fa-spin"></i><br />Opération en cours, merci de patienter...</div></div>';

        return $messages;
    }

    /**
     * Renvoi le(s) message(s) enregistré(s)
     *
     * @param string $type
     * @return string
     */
    public function getMessageTxt(string $type = null): string {

        if (!$type) {
            return 'Aucun type défini';
        }

        $messages = '';

        /** Messages type "danger" */
        if (($type == 'danger') || ($type == 'error')) {

            if ($this->danger) {
                $messages = implode('<br />', $this->danger);
            }
        }

        /** Messages type "alerte" */
        if ($type == 'warning') {

            if ($this->warning) {
                $messages = implode('<br />', $this->warning);
            }
        }

        /** Messages type "conf" */
        if (($type == 'success') || ($type == 'conf')) {

            if ($this->success) {
                $messages = implode('<br />', $this->success);
            }
        }

        /** Messages type "infos" */
        if ($type == 'info') {

            if ($this->info) {
                $messages = implode('<br />', $this->info);
            }
        }

        return $messages;
    }

    /**
     * Destruction d'un message
     * 
     * @param string $type
     */
    public function deleteMessage(string $type = 'all') {

        if ($type == 'all') {
            $this->destructAllMessages();
        } else {
            if (($type == 'danger') || ($type == 'error')) {
                $this->danger = NULL;
            }
            if ($type == 'warning') {
                $this->warning = NULL;
            }
            if (($type == 'success') || ($type == 'conf')) {
                $this->success = NULL;
            }
            if ($type == 'info') {
                $this->info = NULL;
            }
        }
    }

    /**
     * Destruction de tous les messages
     */
    private function destructAllMessages() {
        $this->danger = NULL;
        $this->warning = NULL;
        $this->success = NULL;
        $this->info = NULL;
    }

}
