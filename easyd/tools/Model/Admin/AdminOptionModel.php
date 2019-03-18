<?php

/**
 * Class AdminOptionModel | /lib/class/AdminOptionModel.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (15 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Model\Admin;

use Easyd\Db\Db;

/**
 * Descriptif de la classe
 */
class AdminOptionModel extends AdminModel {

    private static $tableAdminOptions = 'admin_options';
    private static $tableAdminMenu = 'admin_menu';
    public $fileOpt = DOC_ROOT . DS . SYS_REP . 'userfiles/Param/Options.php';
    public $themeColorCss = DOC_ROOT . DS . SYS_REP . 'views/admin/core/css/themeColor.css';
    private $htaccessPath = DOC_ROOT . DS . SYS_REP . SYS_ADMIN . '.htaccess';
    private $htpasswdPath = DOC_ROOT . DS . SYS_REP . SYS_ADMIN . '.htpasswd';

    /**
     * Renvoi la liste des options de la table "admin_options"
     * 
     * @return array
     */
    public function getOptions(): array {

        $options = [];

        $sql = 'SELECT * '
                . 'FROM ' . self::$tableAdminOptions;
        $rows = Db::getInstance()->reqArrayM($sql);

        foreach ($rows as $row) {
            $options[$row['nom']] = $row['value'];
        }

        return $options;
    }

    /**
     * Renvoi la liste des éléments du menu Top
     * 
     * @return array
     */
    public function getTopMenu(): ?array {

        $sql = 'SELECT * '
                . 'FROM ' . self::$tableAdminMenu . ' '
                . 'ORDER BY ordre_aff ASC';

        return Db::getInstance()->reqArrayM($sql);
    }

    /**
     * Active/Désactive l'utilisation forcée du SSL
     * 
     * @param bool $activate
     * @return bool
     */
    public function updateAdminHttps(bool $activate): bool {

        $newOpt = ['OPT_ADMIN_HTTPS' => $activate];

        if ($this->updateOption($newOpt)) {
            $this->generateAdminHtaccess();
        }
    }

    /**
     * Active/Désactive la sécurité par htpasswd
     * 
     * @param bool $activate
     * @param string $login
     * @param string $password
     */
    public function updateAdminHtpasswd(bool $activate = null, string $login, string $password) {

        $newOpt = [
            'OPT_ADMIN_HTPASSWD' => $activate,
            'OPT_ADMIN_HTPASSWD_LOGIN' => $login,
            'OPT_ADMIN_HTPASSWD_PASSWORD' => $password,
        ];

        if ($this->updateOption($newOpt)) {
            $this->generateAdminHtaccess();
        }
    }

    /**
     * Active/Désactive le changement de password après X jours
     * 
     * @param bool $activate
     * @param int $day
     * @return bool
     */
    public function updateMp(bool $activate = null, int $day): bool {

        if ($activate && !$day) {
            $this->message->add('danger', 'Nombre de jour incorrect');
            return false;
        }

        $newOpt = [
            'OPT_CHANGE_MP' => $activate,
            'OPT_CHANGE_MP_DAY' => $day,
        ];

        $this->updateOption($newOpt);

        return true;
    }

    /**
     * Définit la page d'accueil de l'admin
     * 
     * @param string $page
     * @param int $activateMenu
     * @param string $pathMenu
     * @return bool
     */
    public function updateHomePage(string $page, int $activateMenu, string $pathMenu): bool {

        //if (!file_get_contents(SYS_DOMAINE . SYS_PATHDIR . $page)) {
        //    $this->message->add('danger', 'La page ' . $page . ' est introuvable');
        //    return false;
        //}

        $newOpt = [
            'OPT_PAGE_ACCUEIL' => $page,
            'OPT_MENU1_ACCUEIL' => $activateMenu,
            'OPT_MENU2_ACCUEIL' => $pathMenu
        ];

        $this->updateOption($newOpt);

        return true;
    }

    /**
     * Enregistrement du TopMenu
     * 
     * @param array $topMenu
     */
    public function updateTopMenu(?array $links) {

        /** Suppression des éléments du menu */
        $this->db->delSql('ordre_aff>="0"', self::$tableAdminMenu);

        /** Enregistrement des nouveaux éléments du menu */
        $i = 0;
        foreach ($links as $link) {

            if ($link['default_page']) {

                $lienMenu = $link['default_page'] . '?a=showLeftMenu';
                $classMenu = '';
                $targetMenu = 'pages';

                $cheminParamMenu = $link['default_page'] . 'inc/param_menu.php';

                if (file_exists(DOC_ROOT . SYS_PATHDIR . $cheminParamMenu)) {
                    include DOC_ROOT . SYS_PATHDIR . $cheminParamMenu;
                }

                $sql = 'INSERT INTO ' . self::$tableAdminMenu . ' SET '
                        . 'default_page = :default_page,'
                        . 'lien_menu = :lien_menu,'
                        . 'ancre = :ancre,'
                        . 'class_menu = :class_menu,'
                        . 'target = :target,'
                        . 'ordre_aff = :ordre_aff';
                
                $ordre_aff = $i++;
                $sth = $this->pdo->prepare($sql);
                $sth->bindParam(':default_page', $link['default_page'], $this->pdo::PARAM_STR);
                $sth->bindParam(':lien_menu', $lienMenu);
                $sth->bindParam(':ancre', $ancreMenuTop);
                $sth->bindParam(':class_menu', $classMenu);
                $sth->bindParam(':target', $targetMenu);
                $sth->bindParam(':ordre_aff', $ordre_aff);
                $sth->execute();
            }
        }

        if (!$this->message->getError()) {
            $this->message->add('success', 'Enregistrement réussi');
        }
    }

    /**
     * Met à jour la couleur du thème
     * 
     * @param string $color
     * @return bool
     */
    public function updateThemeColor(string $color): bool {

        if (!preg_match('/^[a-z0-9]{6,6}$/i', $color)) {
            $this->message->add('danger', '"<b>' . $color . '</b>" code couleur incorrecte !');
            return false;
        }

        $newOpt = ['OPT_THEME_COLOR' => $color];

        if ($this->updateOption($newOpt)) {

            $css = file_get_contents($this->themeColorCss);
            $newCss = preg_replace('/\: #[a-z0-9]{6,6}/i', ': #' . $color, $css);

            /** Réécriture du fichier css */
            $cssFile = fopen($this->themeColorCss, 'w+');
            if (fputs($cssFile, $newCss)) {
                fclose($cssFile);
                return true;
            } else {
                $this->message->add('error', 'Impossible d\'écrire dans le fichier ' . $cssFile);
            }
        }

        return false;
    }

    /**
     * Enregistre un tableau d'options
     * 
     * @param array $options
     * @return bool
     */
    private function updateOption(array $options): bool {

        foreach ($options as $key => $value) {

            $sql = 'INSERT INTO ' . self::$tableAdminOptions . ' SET '
                    . 'nom = :key, '
                    . 'value = :value '
                    . 'ON DUPLICATE KEY UPDATE '
                    . 'nom = :key, '
                    . 'value = :value';

            $sth = $this->pdo->prepare($sql);
            $sth->bindParam(':key', $key, $this->pdo::PARAM_STR);
            $sth->bindParam(':value', $value, $this->pdo::PARAM_STR);
            $sth->execute();
        }

        $this->majOptionsFile();

        if ($this->message->getError()) {
            $this->message->add('error', 'Problème lors de l\'enregistrement');
            return false;
        }

        $this->message->add('success', 'Enregistrement réussi');
        return true;
    }

    /**
     * MaJ du fichier options.php
     */
    private function majOptionsFile() {

        $newOptions = '<?php' . "\n";
        $newOptions .= 'define(\'OPT_ADMIN\', \'' . SYS_ADMIN . '\') ;' . "\n";

        $options = $this->getOptions();

        foreach ($options as $key => $value) {
            $newOptions .= 'define(\'' . $key . '\', \'' . $value . '\') ;' . "\n";
        }

        $fp = fopen($this->fileOpt, 'w+');
        if (fputs($fp, $newOptions)) {
            fclose($fp);
        }
    }

    /**
     * Génère le fichier Htaccess
     */
    private function generateAdminHtaccess() {

        /**
         * OPT_ADMIN_HTTPS
         * OPT_ADMIN_HTPASSWD
         * OPT_ADMIN_HTPASSWD_LOGIN
         * OPT_ADMIN_HTPASSWD_PASSWORD
         */
        /** Si fichier existe, on récupère le contenu hors balise */
        $specificBefore = $specificAfter = '';

        if (file_exists($this->htaccessPath)) {

            $content = file_get_contents($this->htaccessPath);

            if (preg_match('#^(.*)\# ~~start~~.*\# ~~end~~[^\n]*(.*)$#s', $content, $m)) {

                $specificBefore = $m[1];
                $specificAfter = $m[2];
            }
        }

        /** Si activation du htpassword */
        if (OPT_ADMIN_HTPASSWD == 1) {

            /** Si le fichier .htpasswd est inaccessible */
            if (!$write_hp = @fopen($this->htpasswdPath, 'w')) {

                $this->message->add('danger', 'Impossible d\'accéder ou de créer le fichier .htpasswd !');
            } else {

                fwrite($write_hp, OPT_ADMIN_HTPASSWD_LOGIN . ":" . \crypt(OPT_ADMIN_HTPASSWD_PASSWORD));
                fclose($write_hp);
            }
        }

        /** Si le fichier est inaccessible */
        if (!$write_fd = @fopen($this->htaccessPath, 'w')) {

            $this->message->add('danger', 'Impossible d\'accéder ou de créer le fichier .htaccess !');
        } else {

            if ($specificBefore) {
                fwrite($write_fd, trim($specificBefore) . "\n\n");
            }

            fwrite($write_fd, "# ~~start~~ Do not remove this comment, EasyD will keep automatically the code outside this comment when .htaccess will be generated again\n\n");

            /** Si activation du htpassword */
            if (OPT_ADMIN_HTPASSWD == 1) {

                fwrite($write_fd, "AuthUserFile " . $this->htpasswdPath . "\n");
                fwrite($write_fd, "AuthName \"Admin Access\"\n");
                fwrite($write_fd, "AuthType Basic\n");
                fwrite($write_fd, "Require valid-user\n\n");
            }

            fwrite($write_fd, "options -indexes\n");
            fwrite($write_fd, "RewriteEngine on\n");

            if (OPT_ADMIN_HTTPS == 1) {

                fwrite($write_fd, "RewriteCond %{HTTPS} off [OR]\n");
                fwrite($write_fd, "RewriteCond %{HTTP_HOST} !^" . quotemeta(SYS_DOMAINE) . "$ [NC]\n");
                fwrite($write_fd, "RewriteCond %{REQUEST_URI} " . SYS_REP . OPT_ADMIN . " [NC]\n");
                fwrite($write_fd, "RewriteRule ^(.*)$ https://" . SYS_DOMAINE . "/" . SYS_REP . OPT_ADMIN . "$1 [L,R=301]\n");
            } else {

                fwrite($write_fd, "RewriteCond %{HTTP_HOST} !^" . quotemeta(SYS_DOMAINE) . "$ [NC]\n");
                fwrite($write_fd, "RewriteRule ^(.*)$ http://" . SYS_DOMAINE . "/$1 [L,R=301]\n");
            }

            fwrite($write_fd, "RewriteCond %{REQUEST_FILENAME} !-d\n");
            fwrite($write_fd, "RewriteCond %{REQUEST_FILENAME} !-f\n");
            fwrite($write_fd, "RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]\n");

            fwrite($write_fd, "AddDefaultCharset UTF-8\n\n");

            fwrite($write_fd, "# ~~end~~ Do not remove this comment, EasyD will keep automatically the code outside this comment when .htaccess will be generated again");

            if ($specificAfter) {
                fwrite($write_fd, "\n\n" . trim($specificAfter));
            }

            fclose($write_fd);
        }
    }

}
