<?php

/**
 * Class AdminVersionModel | /Core/Model/AdminVersionModel.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  admin
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (15 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Model\Admin;

/**
 * Descriptif de la classe
 */
class AdminVersionModel extends AdminModel {

    private $moduleDir = DOC_ROOT . DS . SYS_REP;

    public function getListOfModules() {

        $modules = [];

        $d = dir($this->moduleDir);
        $content = [];

        while ($entry = $d->read()) {
            if ($entry != "." && $entry != "..") {
                $content[] = $entry;
            }
        }
        $d->close();

        sort($content);

        foreach ($content as $element) {

            if (is_dir($this->moduleDir . $element)) {

                $version = NULL;
                $fichierParam = $this->moduleDir . $element . '/inc/version.php';

                if (file_exists($fichierParam)) {

                    require $fichierParam;

                    if ($version) {
                        $modules[] = $version;
                    }
                }
            }
        }

        return $modules;

    }

}
