<?php

/**
 * Class AdminIndexModel | /lib/class/AdminIndexModel.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (15/03/2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Model\Admin;

/**
 * Descriptif de la classe
 */
class AdminIndexModel extends AdminModel {

    public static $tableAdminMenu = 'admin_menu';

    public function getTopMenu(): array {

        $menu = [];
        $i = 1;

        /** Lien vers la page d'accueil */
        $menu[] = [
            0 => SYS_PATHDIR,
            1 => '',
            2 => '<i class="fas fa-home"></i><span>Accueil</span>',
            3 => 'Ma' . $i++,
            4 => '',
            5 => ''
        ];

        $links = $this->getListTopMenu();

        if ($links) {

            foreach ($links as $link) {

                $menu[] = [
                    0 => SYS_PATHDIR . $link['default_page'],
                    1 => SYS_PATHDIR . $link['lien_menu'],
                    2 => $link['ancre'],
                    3 => 'Ma' . $i++,
                    4 => $link['class_menu'],
                    5 => $link['target']
                ];
            }
            
        }

        /** Lien vers l'administration */
        $menu[] = [
            0 => SYS_PATHDIR . 'administration/user/',
            1 => SYS_PATHDIR . '?a=showLeftMenu',
            2 => '<i class="fas fa-cogs"></i><span>Administration</span>',
            3 => 'Ma' . $i++,
            4 => '',
            5 => 'pages'
        ];

        /** Lien de purge du cache */
        if ($this->auth->getLevel() >= 6) {

            $menu[] = [
                0 => SYS_PATHDIR . '?a=emptyCache',
                1 => '',
                2 => '<i class="fas fa-eraser"></i><span>Vider le cache</span>',
                3 => 'Ma' . $i++,
                4 => '',
                5 => ''
            ];
        }

        /** Lien de deconnexion */
        $menu[] = [
            0 => SYS_PATHDIR . '?a=logout',
            1 => '',
            2 => '<i class="fas fa-sign-out-alt"></i><span>Quitter</span>',
            3 => 'Ma' . $i++,
            4 => '',
            5 => ''
        ];

        return $menu;
    }

    /** Renvoi la liste des éléments modifiables du menu top */
    private function getListTopMenu() {

        $sql = 'SELECT * '
                . 'FROM ' . self::$tableAdminMenu . ' '
                . 'ORDER BY ordre_aff ASC';

        return $this->db->reqArrayM($sql);
    }

}
