<?php

/**
 * Class Auth | /core/Auth/Admin/AdminAuth.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (16 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Auth\Admin;

use Easyd\Auth\Auth;

/**
 * Class d'authentification
 */
class AdminAuth extends Auth {

    protected $sessionName = 'admin';
    protected $tableUser = 'admin_acces';
    protected $tableUserHistory = 'admin_acces_historique';

    public function __construct() {

        if (!empty($_SESSION[$this->sessionName]['authId'])) {
            $this->loadAuth($_SESSION[$this->sessionName]['authId']);
        }
    }

}
