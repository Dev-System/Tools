<?php

/**
 * Class AdminOptionController | /Core/Controller/Admin/AdminOptionController.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (15 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Controller\Admin;

use Easyd\Model\Admin\AdminOptionModel;

/**
 * Descriptif de la classe
 */
class AdminOptionController extends AdminController {

    protected $model;

    public function __construct() {
        parent::__construct();

        $this->model = new AdminOptionModel();
    }

    public function index() {

        /** Activation du certificat SSL */
        if ($this->submit == 'activateSslCertificate') {

            $optAdminHttps = filter_input(INPUT_POST, 'OPT_ADMIN_HTTPS', FILTER_VALIDATE_BOOLEAN);
            $this->model->updateAdminHttps($optAdminHttps);
        }

        /** Activation du htpasswd */
        if ($this->submit == 'updateAdminHtpasswd') {

            $activate = filter_input(INPUT_POST, 'OPT_ADMIN_HTPASSWD', FILTER_VALIDATE_BOOLEAN);
            $login = filter_input(INPUT_POST, 'OPT_ADMIN_HTPASSWD_LOGIN');
            $password = filter_input(INPUT_POST, 'OPT_ADMIN_HTPASSWD_PASSWORD');
            $this->model->updateAdminHtpasswd($activate, $login, $password);
        }

        /** Activation du changment de password */
        if ($this->submit == 'updateMp') {

            $activate = filter_input(INPUT_POST, 'OPT_CHANGE_MP', FILTER_VALIDATE_BOOLEAN);
            $day = filter_input(INPUT_POST, 'OPT_CHANGE_MP_DAY', FILTER_VALIDATE_INT);
            $this->model->updateMp($activate, $day);
        }

        /** Modification de la homePage */
        if ($this->submit == 'updateHomePage') {

            $page = filter_input(INPUT_POST, 'OPT_PAGE_ACCUEIL');
            $activateMenu = filter_input(INPUT_POST, 'OPT_MENU1_ACCUEIL');
            $pathMenu = filter_input(INPUT_POST, 'OPT_MENU2_ACCUEIL');
            $this->model->updateHomePage($page, $activateMenu, $pathMenu);
        }

        /** Modification du TopMenu */
        if ($this->submit == 'updateTopMenu') {

            $topMenu = filter_input(INPUT_POST, 'topMenu', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $this->model->updateTopMenu($topMenu);
        }

        /** Couleur du thème */
        if ($this->submit == 'updateThemeColor') {

            $optThemeColor = filter_input(INPUT_POST, 'OPT_THEME_COLOR');
            $this->model->updateThemeColor($optThemeColor);
        }

        $this->smarty->assign('options', $this->model->getOptions());
        $this->smarty->assign('menuTop', $this->model->getTopMenu());

        $this->render('core/option/list.tpl');
    }

}
