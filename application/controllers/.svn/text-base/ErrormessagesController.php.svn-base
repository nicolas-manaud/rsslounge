<?php

/**
 * Controller for showing all errormessages of the latest update run
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class ErrormessagesController extends Zend_Controller_Action {

    /**
     * Initialize controller (set language object, base etc.)
     *
     * @return void
     */
    public function init() {
        // initialize view
        $view = $this->initView();
        
        // set language
        $view->language = Zend_Registry::get('language');
        
        // set translate object
        $view->translate()->setTranslator($view->language);
    }
    
    
    /**
     * show messages
     *
     * @return void
     */
    public function indexAction() {
        $offset = $this->getRequest()->getParam('offset',0);
    
        // load latest errormessages
        $messagesModel = new application_models_messages();
        $messages = $messagesModel->fetchAll(
                            $messagesModel->select()
                                          ->order('datetime DESC')
                                          ->limit( Zend_Registry::get('config')->errormessages->length, $offset )
                    );
        
        // load feedname
        $this->view->messages = array();
        foreach($messages as $message) {
            $msg = $message->toArray();
            $msg['feed'] = $message->findParentRow('application_models_feeds')->name;
            $this->view->messages[] = $msg;
        }
        
        $this->view->offset = $offset;
    }
    
}