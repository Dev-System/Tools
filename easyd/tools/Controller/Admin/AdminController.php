<?php

/**
 * Class AdminController | /core/Controller/Admin/AdminController.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (13 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Controller\Admin;

use Easyd\Controller\Controller;
use Easyd\Auth\Admin\AdminAuth;
use Easyd\Message\Message;

/**
 * Descriptif de la classe
 */
class AdminController extends Controller {

    public $submit;
    public $auth;

    public function __construct() {

        parent::__construct();
        
        $this->auth = AdminAuth::getInstance();

        $this->submit = filter_input(INPUT_POST, 'submit', FILTER_SANITIZE_STRING);

        $this->smarty->setTemplateDir(DOC_ROOT . DS . SYS_REP . 'views/admin');
        $this->smarty->setCompileDir(DOC_ROOT . DS . SYS_REP . 'cache/smarty/templates_c/admin');
        $this->smarty->setCacheDir(DOC_ROOT . DS . SYS_REP . 'cache/smarty/cache/admin');
        $this->smarty->error_reporting = 1;
        $this->smarty->debugging = false;
        $this->smarty->caching = false;

        $this->smarty->assign('rep', SYS_REP);
        $this->smarty->assign('admin', SYS_ADMIN);
        $this->smarty->assign('pathDir', SYS_PATHDIR);
        $this->smarty->assign('nom', SYS_NOM);
        $this->smarty->assign('version', SYS_VERSION);

        /** Si l'utilisateur n'est pas identifié, on le renvoi vers la page "login" */
        if (!$this->auth->isLogged()) {

            if ($this->submit == 'authentification') {

                $login = filter_input(INPUT_POST, 'login');
                $password = filter_input(INPUT_POST, 'mdp');

                $this->auth->login($login, $password);
            }

            $this->render('core/login.tpl');
            exit();
        }
        
        $this->smarty->assign('user', $this->auth->getPseudo());
        $this->smarty->assign('niv', $this->auth->getLevel());
    }

    /**
     * Renvoi la vue demandée
     * 
     * @param string $view
     */
    public function render(string $view) {

        $this->smarty->registerClass("Form", "Easyd\FormBootstrap\FormBootstrap");
        $this->smarty->display($view);
    }

}
