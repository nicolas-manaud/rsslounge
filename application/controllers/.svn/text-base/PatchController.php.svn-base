<?php

/**
 * Controller for update rsslounge
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class PatchController extends Zend_Controller_Action {

    /**
     * database update
     *
     * @return void
     */
    public function indexAction() {
    
        // update rsslounge
        if($this->getRequest()->getParam('execute', 0) == 1) {
            $b = Zend_Registry::get('bootstrap');
            $from = $b->getCurrentVersion();
            $to = $b->getApplicationVersion();
            
            for($i=$from+1; $i<=$to; $i++) {
                // execute update if available
                $updatefile = APPLICATION_PATH . '/../updates/update_' . $i . '.php';
                if(file_exists($updatefile))
                    require_once($updatefile);
                
                // set new version in database
                $b->updateCurrentVersion($i);
            }
            
            $this->_forward('index','index');
        }
    
    }

}