<?php

/**
 * Class AdminUserModel | /Core/Model/AdminUserModel.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (14/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Model\Admin;

use Easyd\Recherche\Recherche;
use Easyd\Entity\Admin\AdminUserEntity;

/**
 * Descriptif de la classe
 */
class AdminUserModel extends AdminModel {

    protected $tableDefault = 'admin_acces';
    public static $tableAdminAcces = 'admin_acces';
    public static $tableAdminAccesHistorique = 'admin_acces_historique';
    public static $tableAdminAccesNiveau = 'admin_acces_niveau';
    public $sessionRech = 'RechercheFiche';

    public function save(AdminUserEntity $user) {

        /** Contrôle des données */
        if (!$this->message->getError()) {
            if ($this->checkExisteChamps('email', $user)) {
                $this->message->add('danger', 'Cet "Email" est déjà utilisé.');
            }
        }
        if (!$this->message->getError()) {
            if ($this->checkExisteChamps('nom', $user) && $this->checkExisteChamps('prenom', $user)) {
                $this->message->add('danger', 'Ce "Nom" et ce "Prénom" sont déjà utilisés.');
            }
        }

        /** Génération du pseudo si vide */
        if (!$this->message->getError()) {
            if (!$user->getPseudo()) {
                $user->setPseudo($this->generatePseudo($user));
            }
        }

        /** Sauvegarde de l'objet */
        if (!$this->message->getError()) {

            if ($user->getId()) {
                $this->update($user);
            } else {
                $this->add($user);
            }
        }
    }

    /**
     * Ajoute un enregistrement
     */
    private function add(AdminUserEntity $user) {

        if (!$this->message->getError()) {

            $sql = 'INSERT INTO ' . self::$tableAdminAcces . ' SET '
                    . 'pseudo="' . $this->db::cSQL($user->getPseudo()) . '", '
                    . 'nom="' . $this->db::cSQL($user->getNom()) . '", '
                    . 'prenom="' . $this->db::cSQL($user->getPrenom()) . '", '
                    . 'email="' . $this->db::cSQL($user->getEmail()) . '", '
                    . 'passwd="' . $this->db::cSQL($user->getPasswd()) . '", '
                    . 'last_passwd_gen=NOW(), '
                    . 'niveau="' . $this->db::cSQL($user->getNiveau()) . '", '
                    . 'date_add=NOW(), '
                    . 'user_add="' . $this->db::cSQL($this->auth->getPseudo()) . '"';

            $newId = $this->db->newSql($sql);

            if ($newId) {

                $message = '<b>' . $user->getNom() . '</b> enregistré - '
                        . '<a href = "' . self::$chemin . '?a=edit&id=' . $newId . '" '
                        . 'class = "btn btn-outline-secondary">voir</a>';

                $this->message->add('success', $message);
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                $this->message->add('danger', 'Problème lors de l\'enregistrement');
            }
        }
    }

    /**
     * Modifier les paramètres d'un enregistrement
     * 
     * @param AdminUserEntity $user
     * @return boolean
     */
    private function update(AdminUserEntity $user) {

        /** Enregitrement des données */
        $sql = 'UPDATE ' . self::$tableAdminAcces . ' SET '
                . 'nom="' . $this->db::cSQL($user->getNom()) . '", '
                . 'prenom="' . $this->db::cSQL($user->getPrenom()) . '", '
                . 'email="' . $this->db::cSQL($user->getEmail()) . '", '
                . 'passwd="' . $this->db::cSQL($user->getPasswd()) . '", '
                . 'niveau="' . $this->db::cSQL($user->getNiveau()) . '", '
                . 'active="' . $this->db::cSQL($user->getActive()) . '" '
                . 'WHERE id="' . $this->db::cSQL($user->getId()) . '" ';

        $this->db->upSql($sql, 1, self::$tableAdminAcces, $this->auth->getPseudo(), $user->getId());

        return true;
    }

    /**
     * Génère la liste des éléments
     * 
     * @param string $submit
     */
    public function getList() {

        $submit = filter_input(INPUT_POST, 'submit');

        /** Formulaire de recherche */
        $Rech = new Recherche();
        $Rech->RechSql($this->sessionRech, $this->tableDefault);

        @$Rech->Param[] = array($Rech->criteres['what'], $Rech->criteres['where'], $Rech->criteres['exact'], 'texte');
        @$Rech->Param[] = array($Rech->criteres['active'], 'active', '=', 'boolean');

        if ($submit) {

            if ($submit == 'recherche') {
                $Rech->criteres['what'] = filter_input(INPUT_POST, 'what');
                $Rech->criteres['where'] = filter_input(INPUT_POST, 'where');
                $Rech->criteres['exact'] = filter_input(INPUT_POST, 'exact');
                $Rech->criteres['active'] = filter_input(INPUT_POST, 'active');

                $Rech->Criteres();
            }

            if ($submit == 'reset') {
                $Rech->Efface();
            }

            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }

        /** Liste à afficher */
        $sql = 'SELECT ' . $this->tableDefault . '.*, '
                . self::$tableAdminAccesNiveau . '.nom AS niveauNom '
                . 'FROM ' . $this->tableDefault . ' '
                . 'LEFT JOIN ' . self::$tableAdminAccesNiveau . ' ON ' . $this->tableDefault . '.niveau=' . self::$tableAdminAccesNiveau . '.niveau '
                . 'WHERE ' . $this->tableDefault . '.id!="" '
                . $Rech->RequeteSql()
                . 'ORDER BY ' . $this->tableDefault . '.nom ASC';

        $this->list = $this->db->reqMultiPage($sql);
        $this->barre_nav2 = $this->db->BarreNavigation;
        $this->nbtotal = $this->db->nbTotal;
        $this->recherche = $_SESSION[$this->sessionRech] ?? null;
    }

    /**
     * Renvoi la liste des niveaux d'accès, pour les <select>
     * 
     * @return array
     */
    public function getListOfLevelsForSelect(): array {

        $sql = 'SELECT niveau AS id, nom '
                . 'FROM ' . self::$tableAdminAccesNiveau . ' '
                . 'ORDER BY niveau';

        return $this->db->reqArrayM($sql);
    }

    public function generatePseudo(AdminUserEntity $user) {

        $prenom = $user->getPrenom();
        $nom = $user->getNom();
        $pseudo = ucfirst($prenom[0]) . '.' . ucfirst(strtolower($nom));

        /** Liste des pseudos similaire */
        $sql = 'SELECT pseudo '
                . 'FROM ' . self::$tableAdminAcces . ' '
                . 'WHERE pseudo LIKE :pseudo';

        $sth = $this->pdo->prepare($sql);
        $term = "$pseudo%";
        $sth->bindParam(':pseudo', $term, $this->pdo::PARAM_STR);
        $sth->execute();
        $pseudoList = $sth->fetchAll();

        if ($pseudoList) {

            $list = array_column($pseudoList, 'pseudo');

            if (in_array($pseudo, $list)) {

                $num = 2;

                while (in_array($pseudo . $num, $list)) {
                    $num++;
                }

                $pseudo .= ' ' . $num;
            }
        }

        return $pseudo;
    }

    /**
     * Contrôle la confirmation du password
     * 
     * @param AdminUserEntity $user
     * @param string $passwordConfirmation
     */
    public function checkPasswordConfirmation(AdminUserEntity $user, string $passwordConfirmation) {

        if ($user->getPasswd() != $passwordConfirmation) {
            $this->message->add(
                    'danger',
                    'La confirmation du mot de passe est incorrect'
            );
        }
    }

    /**
     * Renvoi les droits de modifications d'un user
     * 
     * @param AdminUserEntity $user
     * @return boolean
     */
    public function getRightOfModification(AdminUserEntity $user) {

        /** Si l'utilisateur à un niveau superAdmin */
        if ($this->auth->getLevel() == 6) {
            return true;
        }

        /** Si l'utilisateur souhaite modifier sa propre fiche */
        if ($this->auth->getId() == $user->getId()) {
            return true;
        }

        /** Si l'utilisateur à un niveau supérieur à $user */
        if ($this->auth->getLevel() > $user->getNiveau()) {
            return true;
        }

        return false;
    }

}
