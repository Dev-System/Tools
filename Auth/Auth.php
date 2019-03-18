<?php

/**
 * Class Auth | /core/Auth/Auth.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (13 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Auth;

use Easyd\Db\Db;
use Easyd\Message\Message;

/**
 * Class d'authentification
 */
abstract class Auth {

    protected $sessionName;
    protected $tableUser;
    protected $tableUserHistory;
    protected $authId;
    protected $pseudo = 'Unknow';
    protected $level;
    protected $active;
    private static $_instances = array();

    public static function getInstance() {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }

    /**
     * 
     * @param type $username
     * @param type $password
     * @return boolean
     */
    public function login(string $login, string $password) {

        if ($login && $password) {

            $pdo = Db::getInstance()->getLink();

            $sql = 'SELECT id '
                    . 'FROM ' . $this->tableUser . ' '
                    . 'WHERE passwd = :passwd '
                    . 'AND email = :email';

            $password = md5($password);

            $sth = $pdo->prepare($sql);
            $sth->bindParam(':passwd', $password, $pdo::PARAM_STR);
            $sth->bindParam(':email', $login, $pdo::PARAM_STR);
            $sth->execute();

            $authId = $sth->fetchColumn();

            if ($authId) {

                $this->loadAuth($authId);

                if ($this->active >= 1) {

                    $_SESSION[$this->sessionName]['authId'] = $this->authId;

                    $sql = 'UPDATE ' . $this->tableUser . ' SET '
                            . 'last_connect=NOW(), '
                            . 'nb_connect=nb_connect+1 '
                            . 'WHERE id="' . Db::cSQL($this->authId) . '"';

                    Db::getInstance()->upSql($sql, 0);

                    /** Enregistrement de l'historique de connexion */
                    $sql = 'INSERT INTO ' . $this->tableUserHistory . ' SET '
                            . 'id_acces="' . Db::cSQL($this->authId) . '",'
                            . 'ip="' . Db::cSQL($_SERVER['REMOTE_ADDR']) . '",'
                            . 'date=NOW()';

                    Db::getInstance()->newSql($sql);

                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit();
                } else {
                    Message::getInstance()->add('danger', 'Compte désactivé !');
                }
            } else {
                Message::getInstance()->add('danger', 'Identifiant et/ou Mot de passe incorrects !');
            }
        } else {
            Message::getInstance()->add('danger', 'Tous les champs sont obligatoires !');
        }
    }

    /**
     * Charge les paramètres de l'utilisateur à partir de son ID
     * 
     * @param int $id
     */
    protected function loadAuth(int $id) {

        $pdo = Db::getInstance()->getLink();

        $sql = 'SELECT id, pseudo, niveau AS level, active '
                . 'FROM ' . $this->tableUser . ' '
                . 'WHERE id = :id';

        $sth = $pdo->prepare($sql);
        $sth->bindParam(':id', $id, $pdo::PARAM_INT);
        $sth->execute();

        $user = $sth->fetchObject();

        $this->authId = $user->id;
        $this->pseudo = $user->pseudo;
        $this->level = $user->level;
        $this->active = $user->active;
    }

    /**
     * Contrôle si l'utilisateur courant est identifié
     * 
     * @return bool
     */
    public function isLogged(): bool {

        if ($this->authId) {

            return true;
        }
        return false;
    }

    public function getId() {
        return $this->authId;
    }

    public function getPseudo() {
        return $this->pseudo;
    }

    public function getLevel() {
        return $this->level;
    }

    public function getActive() {
        return $this->active;
    }

}
