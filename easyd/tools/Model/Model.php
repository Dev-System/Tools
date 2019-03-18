<?php

/**
 * Class Model | /core/Model/Model.php
 *
 * @package     EsayD
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.2 (13/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Model;

use Easyd\Db\Db;
use Easyd\Message\Message;

/**
 * Classe principale des models
 */
class Model {

    protected $tableDefault;
    protected $db;
    protected $pdo;
    public static $chemin;
    public $message;

    public function __construct() {

        $this->db = Db::getInstance();
        $this->pdo = Db::getInstance()->getLink();
        $this->message = Message::getInstance();
    }

    /**
     * Contrôle si la valeur d'un champ existe déjà 
     * 
     * @param string $fieldName
     * @param Object $entity
     * @return boolean
     */
    public function checkExisteChamps(string $fieldName, $entity) {

        if ($fieldName && $entity) {

            $getValue = 'get' . ucfirst($fieldName);
            $sql = 'SELECT id '
                    . 'FROM ' . $this->tableDefault . ' '
                    . 'WHERE ' . $fieldName . '="' . $this->db::cSQL($entity->$getValue()) . '"';

            if ($entity->getId()) {
                $sql .= ' AND id!="' . $this->db::cSQL($entity->getId()) . '"';
            }

            return $this->db->reqCount($sql);
        }

        return false;
    }

}
