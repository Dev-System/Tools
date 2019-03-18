<?php

/**
 * Class AdminBackupModel | /lib/class/AdminBackupModel.php
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
class AdminBackupModel extends AdminModel {

    private $saveDir = DOC_ROOT . '/' . SYS_REP . 'userfiles/BackUpSql/';
    private $saveDirTemp = DOC_ROOT . '/' . SYS_REP . 'userfiles/BackUpSql/tmp/';

    public function __construct() {
        parent::__construct();

        $this->checkExistPath($this->saveDir);
        $this->checkExistPath($this->saveDirTemp);
    }

    /**
     * Affiche les droits de l'utilisateur sur la BDD
     */
    public function getUserGrant() {

        $this->message->add('infos', '<b>Liste des droits utilisateur sur la base de données :</b>');

        $pdo = Db::getInstance()->getLink();

        /** Show Grants */
        $sql = 'SHOW GRANTS FOR CURRENT_USER';
        $query = $pdo->query($sql);
        while ($droit = $query->fetchAll($pdo::FETCH_COLUMN)) {
            $this->message->add('infos', '- ' . $droit[0]);
        }

        /** Test si privilège SELECT */
        if (!$pdo->query('SELECT * FROM admin_acces LIMIT 1')) {
            $this->message->add('danger', 'Le privilège "<b>SELECT</b>" est nécessaire à l\'exécution de la sauvegarde');
        }

        /** Test si privilège LOCK TABLES */
        if (!$pdo->query('LOCK TABLE admin_acces READ')) {
            $this->message->add('danger', 'Le privilège "<b>LOCK TABLES</b>" est nécessaire à l\'exécution de la sauvegarde');
        } else {
            $pdo->query('UNLOCK TABLE');
        }
    }

    public function generateBackup() {

        $dateSave = date('Y-m-d_H-i');
        $newBackUp = 'BackUpSql_' . $dateSave . '.zip';

        $pdo = Db::getInstance()->getLink();

        /** Vide le dossier tmp */
        $files = array_diff(scandir($this->saveDirTemp), ['.', '..']);
        foreach ($files as $file) {
            (is_dir($this->saveDirTemp . '/' . $file)) ? '' : unlink($this->saveDirTemp . '/' . $file);
        }

        /** Liste des tables à sauvegarder */
        $query = $pdo->query('SHOW TABLES');
        $tables = $query->fetchAll($pdo::FETCH_COLUMN);

        foreach ($tables as $table) {

            system('/usr/bin/mysqldump --host=' . DB_SERVEUR . ' --user=' . DB_USER . ' --password=' . DB_PASSWORD . ' ' . DB_BASE . ' --tables ' . $table . ' > ' . $this->saveDirTemp . $table . '.sql', $retval);

            if ($retval !== false) {
                if (!file_exists($this->saveDirTemp . $table . '.sql')) {
                    $this->message->add('danger', '- ' . $table . ' : manquant !' . $retval);
                } else {
                    $this->message->add('success', '- ' . $table . ' : ok');
                }
            } else {
                $this->message->add('danger', '- ' . $table . ' : error 2 !' . $retval);
            }
        }

        // Compression des tables.sql
        $zip = new \ZipArchive();

        if ($zip->open($this->saveDir . $newBackUp, \ZIPARCHIVE::CREATE) !== TRUE) {
            $this->message->add('danger', 'Echec lors de la création de l\'archive : ' . $this->saveDirTemp . $newBackUp);
        }

        if (!$this->message->getError()) {

            $zip->setArchiveComment('Fichiers zipper le ' . date("d-m-Y H\hi"));

            $files = array_diff(scandir($this->saveDirTemp), ['.', '..']);
            foreach ($files as $file) {
                (is_dir($this->saveDirTemp . '/' . $file)) ? '' : $zip->addFile($this->saveDirTemp . '/' . $file, $file);
            }
        }
    }

    /**
     * Restaure un backup
     * !!! Méthode à redéfinir !!!
     * 
     * @param string $backupFileName
     * @return boolean
     */
    public function restoreBackup(string $backupFileName) {

        /////////////////////////////////////
        $this->message->add('warning', 'La méthode de restauration est à redéfinir !');
        return null;
        /////////////////////////////////////

        if (!$backupFileName) {
            return false;
        }

        /** Sauvegarde à restaurer */
        $backupFile = $this->saveDir . $backupFileName;

        /** Vide le dossier tmp */
        $files = array_diff(scandir($this->tmpDir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir($this->tmpDir . '/' . $file)) ? '' : unlink($this->tmpDir . '/' . $file);
        }

        $zip = new \ZipArchive();

        if ($zip->open($backupFile) === TRUE) {

            $zip->extractTo($this->tmpDir);
            $zip->close();
        } else {
            Message::getInstance()->add('danger', "Impossible de décompresser {$backupFile}");
        }

        if ($dossier = opendir($this->tmpDir)) {

            while (($tableData = readdir($dossier)) !== false) {

                if ($tableData != '.' && $tableData != '..' && $tableData != 'index.php') {

                    $tableName = str_replace(".sql", "", $tableData);

                    system('/usr/bin/mysql --host=' . DB_SERVEUR . ' --user=' . DB_USER . ' --password=' . DB_PASSWORD . ' ' . DB_BASE . ' < ' . $this->tmpDir . $tableData, $retval);

                    if ($retval === FALSE) {
                        Message::getInstance()->add('danger', "- {$tableName} - error !");
                    } else {
                        Message::getInstance()->add('success', "- {$tableName} - ok");
                    }
                }
            }

            closedir($dossier);
        }

        return true;
    }

    /**
     * Supprime un backup
     * 
     * @param string $deleteFileName
     * @return boolean
     */
    public function deleteBackup(string $backupFileName) {

        if (!$backupFileName) {
            return false;
        }

        /** Sauvegarde à supprimer */
        $backupFile = $this->saveDir . $backupFileName;

        if (!unlink($backupFile)) {
            $this->message->add('danger', "Impossible de supprimer la sauvegarde : {$backupFileName}");
            return false;
        }

        $this->message->add('success', 'Sauvegarde supprimée');
        return true;
    }

    /**
     * Renvoi la liste des sauvegardes
     * 
     * @return string
     */
    public function getListOfBackups() {

        $listeFichier = [];
        $i = 0;

        $d = dir($this->saveDir);

        while ($fichier = $d->read()) {

            if ($fichier != '.' && $fichier != '..' && $fichier != 'index.php' && $fichier != 'tmp' && $fichier != '.htaccess') {

                $infos['date'] = date("d/m/Y à H:i:s", filemtime($this->saveDir . $fichier));

                $listeFichier[$i]['nom'] = $fichier;
                $listeFichier[$i]['size'] = $this->formatTheSize(filesize($this->saveDir . $fichier));
                $listeFichier[$i]['lien'] = '/' . SYS_REP . 'userfiles/BackUpSql/' . $fichier;
                $listeFichier[$i]['date'] = 'Effectuée le ' . $infos['date'];

                $i++;
            }
        }

        $d->close();

        rsort($listeFichier);

        return $listeFichier;
    }

    /**
     * Formate la taille d'un fichier
     *
     * @param int $taille
     * @return string
     */
    private function formatTheSize($taille) {

        $unites = ['o', 'ko', 'Mo', 'Go'];
        $unite = '';

        for ($u = count($unites); $u >= 0; $u--) {
            if (isset($unites[$u]) && $taille >= 1024 * pow(1024, $u - 1)) {
                $taille = $taille / pow(1024, $u);
                $unite = $unites[$u];
                break;
            }
        }

        if ($u > 0) {
            return number_format($taille, 0) . ' ' . $unite;
        } else {
            return $taille . ' ' . $unite;
        }
    }

    /**
     * Contrôle si un chemin existe
     * S'il n'existe pas, on tente de le créer
     * 
     * @param string $path
     */
    private function checkExistPath(string $path) {

        if ($path && !file_exists($path)) {

            if (!mkdir($path, 0755, true)) {
                $this->message->add('danger', 'Impossible de créer le dossier : ' . $path);
            } else {
                $this->message->add('success', 'Création du dossier : ' . $path);
            }
        }
    }

}
