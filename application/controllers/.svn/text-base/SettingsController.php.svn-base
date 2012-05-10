<?php

/**
 * Controller for change the settings
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class SettingsController extends Zend_Controller_Action {

    /**
     * Initialize controller (set language object, base etc.)
     *
     * @return void
     */
    public function init() {
        // initialize view
        $view = $this->initView();
        
        // set translate object
        $view->translate()->setTranslator(Zend_Registry::get('language'));
    }

    
    /**
     * Show edit dialog
     *
     * @return void
     */
    public function indexAction() {
        // load available languages
        $this->view->languages = array();
        foreach(Zend_Registry::get('language')->getList() as $lang)
            $this->view->languages[$lang] = utf8_encode(html_entity_decode(Zend_Registry::get('language')->translate($lang)));
            
        // load username
        $user = new application_models_users();
        $username = $user->getUsername();
        if($username!==false)
            $this->view->username = $username;
    }
    
    
    /**
     * Save new settings
     *
     * @return void
     */
    public function saveAction() {
        // suppress view rendering
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
        
        // save username password
        $activateLogin = $this->getRequest()->getParam('activate_login');
        $username = $this->getRequest()->getParam('username',false);
        $password = $this->getRequest()->getParam('password',false);
        $passwordAgain = $this->getRequest()->getParam('password_again',false);
        
        $result = array();
        
        // deactivate login
        if($activateLogin=='0') {
            $this->removeLogin();
            
        // activate login
        } else {
            // any data changed?
            $user = new application_models_users();
            if($username!=$user->getUsername() || strlen($password)!=0 ) {
                if($password!=$passwordAgain)
                    $result = array('password_again' => Zend_Registry::get('language')->translate('given passwords not equal'));
                else if(strlen(trim($password))!=0 && strlen(trim($username))==0)
                    $result = array('username' => Zend_Registry::get('language')->translate('if you set a password you must set an username') );
                else 
                    $this->saveLogin($username, $password);
            }
        }
        
        $newSettings = $this->getRequest()->getPost();
        
        // save new settings
        if(count($result)==0) {
            $settingsModel = new application_models_settings();
            $result = $settingsModel->save($newSettings);
        }
        
        // delete cached js files (for language settings)
        $target = Zend_Registry::get('config')->pub->path . 'javascript/' . Zend_Registry::get('config')->cache->minifiedjsfile;
        if(file_exists($target));
            unlink($target);
        
        // return result (errors or success)
        $this->_helper->json($result);
    }
    

    /**
     * save login
     *
     * @return void
     */
    private function saveLogin($username, $password) {
    
        // for demo application
        if(Zend_Registry::get('config')->demomode=="1")
            return;
        
        $user = new application_models_users();
        $user->setUser($username, $password);
    }


    /**
     * remove login
     *
     * @return void
     */
    private function removeLogin() {
        $user = new application_models_users();
        $user->purge();
    }    
}

