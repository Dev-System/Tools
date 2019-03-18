<?php

/**
 * Class AdminAideController | /Core/Controller/Admin/AdminAideController.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (15 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Controller\Admin;

/**
 * Descriptif de la classe
 */
class AdminAideController extends AdminController {

    public function index() {

        $this->render('core/aide/list.tpl');
    }

}
