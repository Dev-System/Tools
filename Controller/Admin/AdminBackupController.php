<?php

/**
 * Class AdminBackupController | /Core/Controller/Admin/AdminBackupController.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (15 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Controller\Admin;

use Easyd\Model\Admin\AdminBackupModel;

/**
 * Descriptif de la classe
 */
class AdminBackupController extends AdminController {

    protected $model;
    public static $chemin = '/' . SYS_REP . SYS_ADMIN . 'administration/backup/';
    public $cheminTpl = 'matrice/tpl/';
    public $txt = [
        'listTitle' => 'Liste des sauvegardes',
        'editTitle' => '',
        'addTitle' => '',
        'confirmDelete' => 'Etes-vous sûr(e) de vouloir supprimer cette sauvegarde ?',
        'addButton' => 'Faire une nouvelle sauvegarde'
    ];

    public function __construct() {
        parent::__construct();

        $this->model = new AdminBackupModel();
    }

    public function index() {

        /** Affichage des droits utilisateur */
        if ($this->submit == 'userGrant') {
            $this->model->getUserGrant();
        }

        /** Nouveau backup */
        if ($this->submit == 'generate') {
            $this->model->generateBackup();
        }

        /** Restauration d'une sauvegarde */
        if ($this->submit == 'restore') {
            
            $backupFileName = filter_input(INPUT_POST, 'backupFileName');
            $this->model->restoreBackup($backupFileName);
        }

        /** Suppression d'une sauvegarde */
        if ($this->submit == 'delete') {

            $backupFileName = filter_input(INPUT_POST, 'backupFileName');
            $this->model->deleteBackup($backupFileName);
        }

        /** Affichage de toutes les sauvegarde */
        $this->smarty->assign('liste', $this->model->getListOfBackups());

        $this->smarty->assign('module', $this);

        $this->render('core/backup/list.tpl');
    }

}
