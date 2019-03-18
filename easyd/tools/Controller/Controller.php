<?php

/**
 * Class Controller | /core/Controller/Controller.php
 *
 * @package     EasyD - Framework - v6
 * @subpackage  Core
 * @author      Stéphane Ramé <stephane.rame@dev-system.com>
 * @version     v.1.0.0 (13 mars 2019)
 * @copyright   Copyright (c) 2019, Dev-System
 */

namespace Easyd\Controller;

use Detection\MobileDetect;
use Easyd\Message\Message;

/**
 * Classe parent des controllers
 */
class Controller {

    public $smarty;
    protected $deviceType;
    public $message;

    public function __construct() {

        $this->message = Message::getInstance();
        
        /** Device Type */
        $detect = new MobileDetect();
        $this->deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'computer');
        
        /** Smarty */
        $this->smarty = new \Smarty();

        $this->smarty->setTemplateDir(DOC_ROOT . SYS_REP . 'views');
        $this->smarty->setCompileDir(DOC_ROOT . SYS_REP . 'cache/smarty/templates_c');
        $this->smarty->setCacheDir(DOC_ROOT . SYS_REP . 'cache/smarty/cache');
        $this->smarty->error_reporting = 1;
        $this->smarty->debugging = false;
        $this->smarty->caching = false;

        $this->smarty->assign('deviceType', $this->deviceType);
    }

}
