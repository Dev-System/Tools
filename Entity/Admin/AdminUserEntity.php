<?php

/**
 * Class AdminUserEntity | /Core/Entity/AdminUserEntity.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (14 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Entity\Admin;

use Easyd\Message\Message;

/**
 * Classe de l'entité admin_acces
 */
class AdminUserEntity {

    private $id;
    private $pseudo;
    private $nom;
    private $prenom;
    private $email;
    private $passwd;
    private $last_passwd_gen;
    private $niveau;
    private $last_connect;
    private $nb_connect;
    private $date_add;
    private $user_add;
    private $date_upd;
    private $user_upd;
    private $active;

    public function getId() {
        return $this->id;
    }

    public function getPseudo() {
        return $this->pseudo;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPasswd() {
        return $this->passwd;
    }

    public function getLast_passwd_gen() {
        return $this->last_passwd_gen;
    }

    public function getNiveau() {
        return $this->niveau;
    }

    public function getLast_connect() {
        return $this->last_connect;
    }

    public function getNb_connect() {
        return $this->nb_connect;
    }

    public function getDate_add() {
        return $this->date_add;
    }

    public function getUser_add() {
        return $this->user_add;
    }

    public function getDate_upd() {
        return $this->date_upd;
    }

    public function getUser_upd() {
        return $this->user_upd;
    }

    public function getActive() {
        return $this->active;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setPseudo($pseudo) {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function setNom($nom) {
        if (!$nom) {
            Message::getInstance()->add(
                    'danger',
                    'Le champ "Nom" est obligatoire'
            );
        } else {
            $this->nom = $nom;
        }
        return $this;
    }

    public function setPrenom($prenom) {
        if (!$prenom) {
            Message::getInstance()->add(
                    'danger',
                    'Le champ "Prénom" est obligatoire'
            );
        } else {
            $this->prenom = $prenom;
        }
        return $this;
    }

    public function setEmail($email) {
        if (!$email) {
            Message::getInstance()->add(
                    'danger',
                    'Le champ "Email" est obligatoire'
            );
        } else {
            $this->email = $email;
        }
        return $this;
    }

    public function setPasswd($passwd) {

        if (strlen($passwd) <= 7) {
            Message::getInstance()->add(
                    'danger',
                    'Le champ "Mot de passe" doit contenir 8 caractères minimum'
            );
        } else {
            $this->passwd = $passwd;
        }
        return $this;
    }

    public function setLast_passwd_gen($last_passwd_gen) {
        $this->last_passwd_gen = $last_passwd_gen;
        return $this;
    }

    public function setNiveau($niveau) {
        if (!$niveau) {
            Message::getInstance()->add(
                    'danger',
                    'Le champ "Niveau" est obligatoire'
            );
        } else {
            $this->niveau = $niveau;
        }
        return $this;
    }

    public function setLast_connect($last_connect) {
        $this->last_connect = $last_connect;
        return $this;
    }

    public function setNb_connect($nb_connect) {
        $this->nb_connect = $nb_connect;
        return $this;
    }

    public function setDate_add($date_add) {
        $this->date_add = $date_add;
        return $this;
    }

    public function setUser_add($user_add) {
        $this->user_add = $user_add;
        return $this;
    }

    public function setDate_upd($date_upd) {
        $this->date_upd = $date_upd;
        return $this;
    }

    public function setUser_upd($user_upd) {
        $this->user_upd = $user_upd;
        return $this;
    }

    public function setActive($active) {
        $this->active = $active;
        return $this;
    }

}
