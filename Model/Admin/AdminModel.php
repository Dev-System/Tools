<?php

/**
 * Class AdminModel | /core/Model/AdminModel.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.2 (13/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Model\Admin;

use Easyd\Model\Model;
use Easyd\Auth\Admin\AdminAuth;

/**
 * Classe de référence pour la création / modification de projets
 */
class AdminModel extends Model {

    public $auth;

    public function __construct() {

        parent::__construct();

        $this->auth = AdminAuth::getInstance();
    }

    /**
     * Renvoi les informations d'un enregistrement d'une table
     * 
     * @param int $id
     * @param string $entityClass
     * @return ObjectEntity
     */
    public function loadEntity(int $id, string $entityClass) {

        $sql = 'SELECT * '
                . 'FROM ' . $this->tableDefault . ' '
                . 'WHERE id=:id';

        $sth = $this->pdo->prepare($sql);
        $sth->bindParam(':id', $id, $this->pdo::PARAM_INT);
        $sth->execute();

        return $sth->fetchObject($entityClass);
    }

}
