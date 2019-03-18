<?php

/**
 * Class AdminVersionController | /Core/Controller/Admin/AdminVersionController.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (15 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Controller\Admin;

use Easyd\SendMail\SendMail;
use Detection\MobileDetect;
use PHPMailer\PHPMailer\PHPMailer;
use Easyd\Model\Admin\AdminVersionModel;

/**
 * Descriptif de la classe
 */
class AdminVersionController extends AdminController {

    protected $model;

    public function __construct() {
        parent::__construct();

        $this->model = new AdminVersionModel();
    }

    public function index() {

        $this->smarty->assign('modules', $this->model->getListOfModules());
        $this->smarty->assign('versionPhp', phpversion());
        $this->smarty->assign('versionMobileDetect', (new MobileDetect)::VERSION);
        $this->smarty->assign('versionPhpMailer', (new PHPMailer())::VERSION);

        $this->render('core/version/list.tpl');
    }

}
