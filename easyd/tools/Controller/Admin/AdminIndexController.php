<?php

/**
 * Class AdminIndexController
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (13 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Controller\Admin;

use Easyd\Model\Admin\AdminIndexModel;

/**
 * Classe d'index du backoofice
 */
class AdminIndexController extends AdminController {

    protected $model;

    public function __construct() {
        parent::__construct();

        $this->model = new AdminIndexModel();
    }

    public function index() {

        /** Page d'accueil */
        $default = [
            'page' => OPT_PAGE_ACCUEIL,
            'menu1' => 'Ma' . OPT_MENU1_ACCUEIL,
            'menu2' => OPT_MENU2_ACCUEIL
        ];

        /** Sécurité du compte */
        /*
          if (!Comptes::checkChangePasswordDay($_SESSION['admin']['idAcces'])) {
          $default['page'] = 'default/administration/comptes/compte_modif_mp.php?id=' . $_SESSION['admin']['idAcces'];
          $default['menu1'] = 'Ma99';
          $default['menu2'] = 'default/administration/menu.php';
          }
         * 
         */

        $this->smarty->assign('defaut', $default);

        $this->smarty->assign('menuTop', $this->model->getTopMenu());

        $this->render('core/index.tpl');
    }

    /**
     * Renvoi le menu de gauche de l'espace "Administration"
     */
    public function showLeftMenu() {

        $menu = [];
        $i=0;

        /**
         * $menu[] = [
         *     0 => Lien,
         *     1 => Ancre,
         *     2 => Id,
         *     3 => class,
         *     4 => target
         * ];
         */
        $menu[] = [
            0 => SYS_PATHDIR . '/administration/user/',
            1 => 'Les comptes',
            2 => '',
            3 => 'hover default',
            4 => 'pages'
        ];

        if ($this->auth->getLevel() >= 6) {

            $menu[] = [
                0 => SYS_PATHDIR . '/administration/backup/',
                1 => 'Les sauvegardes',
                2 => '',
                3 => '',
                4 => 'pages'
            ];

            $menu[] = [
                0 => SYS_PATHDIR . '/administration/version/',
                1 => 'Les Versions',
                2 => '',
                3 => '',
                4 => 'pages'
            ];
        }

        $menu[] = [
            0 => SYS_PATHDIR . '/administration/aide/',
            1 => 'Les aides',
            2 => '',
            3 => '',
            4 => 'pages'
        ];

        if ($this->auth->getLevel() >= 6) {

            $menu[] = [
                0 => SYS_PATHDIR . '/administration/option/',
                1 => 'Options',
                2 => '',
                3 => '',
                4 => 'pages'
            ];
        }

        $this->smarty->assign('menu', $menu);
        $this->smarty->assign('menuTitre', 'Administration');

        $this->render('core/menu.tpl');
    }

    public function logout() {

        session_unset();
        session_destroy();

        header('Location:' . SYS_PATHDIR);
        exit();
    }

    /**
     * Suppression des caches de la partie "Admin"
     */
    public function emptyCache() {

        $cachePath = DOC_ROOT . DS . SYS_REP . 'cache/smarty/';

        $directory = [
            $cachePath . 'templates_c/admin/',
            $cachePath . 'cache/admin/'
        ];

        foreach ($directory as $dir) {

            $d = dir($dir);

            while ($entry = $d->read()) {

                if ($entry != "." && $entry != ".." && $entry != 'ServeurExplore') {
                    if (unlink($d->path . $entry)) {
                        $this->message->add('success', 'Suppression de ' . $d->path . $entry);
                    } else {
                        $this->message->add('danger', 'Problème lors de la suppression de ' . $d->path . $entry);
                    }
                }
            }

            $d->close();
        }

        header('Location:' . SYS_PATHDIR);
        exit();
    }

}
