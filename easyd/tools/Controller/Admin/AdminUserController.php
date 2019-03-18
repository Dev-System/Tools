<?php

/**
 * Class AdminUserController | /lib/class/AdminUserController.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (14/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Controller\Admin;

use Easyd\Model\Admin\AdminUserModel;
use Easyd\Entity\Admin\AdminUserEntity;

/**
 * Descriptif de la classe
 */
class AdminUserController extends AdminController {

    protected $model;
    public static $chemin = '/' . SYS_REP . SYS_ADMIN . 'administration/user/';
    public $txt = [
        'listTitle' => 'Liste des utilisateurs',
        'editTitle' => 'Modifier un utilisateur',
        'addTitle' => 'Ajouter un utilisateur',
        'confirmDelete' => 'Etes-vous sûr(e) de vouloir supprimer cet utilisateur ?',
        'addButton' => 'Ajouter un utilisateur'
    ];

    public function __construct() {
        parent::__construct();

        $this->model = new AdminUserModel();
    }

    public function index() {

        /** Affichage de toutes les fiches */
        $this->model->getList($this->submit);

        $this->smarty->assign('module', $this);

        $this->smarty->assign('liste', $this->model->list);
        $this->smarty->assign('barre_nav2', $this->model->barre_nav2);
        $this->smarty->assign('nbtotal', $this->model->nbtotal);
        $this->smarty->assign('recherche', $this->model->recherche);

        $this->smarty->assign('authLevel', $this->auth->getLevel());

        $this->render('core/user/list.tpl');
    }

    /**
     * Affiche une vue d'édition
     */
    public function edit() {

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        $user = $this->model->loadEntity($id, 'Easyd\Entity\Admin\AdminUserEntity');

        /** Test les droits de l'utilisateur */
        $authRight = $this->model->getRightOfModification($user);

        if ($user) {

            if ($this->submit == 'save' && $authRight) {

                $user->setNom(filter_input(INPUT_POST, 'nom'))
                        ->setPrenom(filter_input(INPUT_POST, 'prenom'))
                        ->setEmail(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL))
                        ->setNiveau(filter_input(INPUT_POST, 'niveau', FILTER_VALIDATE_INT))
                        ->setActive(filter_input(INPUT_POST, 'active', FILTER_VALIDATE_BOOLEAN));

                $this->model->save($user);
            }
            $this->smarty->assign('user', $user);
            $this->smarty->assign('module', $this);
            $this->smarty->assign('levels', $this->model->getListOfLevelsForSelect());

            if ($authRight) {
                $this->render('core/user/edit.tpl');
            } else {
                $this->message->add(
                        'warning',
                        'Vous n\'avez pas les droits nécessaire à la modification de cette fiche'
                );
                $this->render('core/user/view.tpl');
            }
        } else {
            $this->message->add('danger', 'Utilisateur introuvalbe');
            header('Location:' . self::$chemin);
            exit();
        }
    }

    /**
     * Affiche une vue d'ajout
     */
    public function add() {

        $user = new AdminUserEntity();

        /** Si l'utilisateur est de niveau 1, on le redirige vers la liste */
        if ($this->auth->getLevel() < 4) {
            $this->message->add(
                    'warning',
                    'Vous n\'avez pas les droits nécessaire à la création d\'un utilisateur'
            );
            header('Location:' . self::$chemin);
            exit();
        }

        if ($this->submit == 'save') {

            $password = $this->passwordEncoding(filter_input(INPUT_POST, 'mp1'));
            $passwordConfirmation = $this->passwordEncoding(filter_input(INPUT_POST, 'mp2'));

            $user->setNom(filter_input(INPUT_POST, 'nom'))
                    ->setPrenom(filter_input(INPUT_POST, 'prenom'))
                    ->setEmail(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL))
                    ->setNiveau(filter_input(INPUT_POST, 'niveau', FILTER_VALIDATE_INT))
                    ->setPasswd($password)
                    ->setActive(filter_input(INPUT_POST, 'active', FILTER_VALIDATE_BOOLEAN));

            /** Check password */
            $this->model->checkPasswordConfirmation($user, $passwordConfirmation);

            $this->model->save($user);
        }
        $this->smarty->assign('user', $user);
        $this->smarty->assign('module', $this);
        $this->smarty->assign('levels', $this->model->getListOfLevelsForSelect());

        $this->render('core/user/add.tpl');
    }

    /**
     * Affiche la vue de modification du password
     */
    public function changePassword() {

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        $user = $this->model->loadEntity($id, 'Easyd\Entity\Admin\AdminUserEntity');

        if ($user) {

            if ($this->submit == 'save') {

                $password = $this->passwordEncoding(filter_input(INPUT_POST, 'mp1'));
                $passwordConfirmation = $this->passwordEncoding(filter_input(INPUT_POST, 'mp2'));

                $user->setPasswd($password);

                /** Check password */
                $this->model->checkPasswordConfirmation($user, $passwordConfirmation);

                $this->model->save($user);
            }
            $this->smarty->assign('user', $user);
            $this->smarty->assign('module', $this);

            $this->render('core/user/changePassword.tpl');
        } else {
            $this->model->message->add('danger', 'Utilisateur introuvalbe');
            header('Location:' . self::$chemin);
            exit();
        }
    }

    /**
     * Renvoi un mot de passe encodé (MD5)
     * 
     * @param string $password
     * @return string
     */
    private function passwordEncoding(string $password): string {

        return md5($password);
    }

}
