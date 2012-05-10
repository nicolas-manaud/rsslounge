<?php

/**
 * Plugin for authenticate the user
 *
 * @package    application_controllers
 * @subpackage plugins
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Plugin_Authentication extends Zend_Controller_Plugin_Abstract {

    /**
     * checks whether a user needs a login and is loggedin
     * otherwise redirect to login page
     *
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        // allways allow silent update (for easy cronjob)
        if($request->getControllerName()=='update' && $request->getActionName()=='silent')
            return;
        
        // show login
        if($request->getParam('login', false)!==false) {
            $request->setControllerName('index');
            $request->setActionName('login');
            return;
        }
        
        // logout?
        if($request->getParam('logout', false)!==false) {
            Zend_Registry::get('session')->__unset('authenticated');
            $request->setControllerName('index');
            $request->setActionName('login');
            return;
        }
        
        // disallow readonly mode if user has option public not set
        if( Zend_Registry::get('session')->authenticated==='readonly'
            && Zend_Registry::get('session')->public != 1)
                Zend_Registry::get('session')->authenticated=false;
    
        // check whether user loggedin or public access allowed
        if(    Zend_Registry::get('session')->authenticated!==true
            && Zend_Registry::get('session')->authenticated!=='readonly') {
        
            // no login required?
            $users = new application_models_users();
            if(!$users->getUsername()) {
                Zend_Registry::get('session')->authenticated = true;
                
            // public access allowed? start public mode
            } elseif(Zend_Registry::get('session')->public==1) {
                Zend_Registry::get('session')->authenticated='readonly';
                
            // unallowed access -> show login window
            } else {
                $request->setControllerName('index');
                $request->setActionName('login');
                return;
            }
        }
        
        
        // load default values 4 readonly mode
        if(Zend_Registry::get('session')->authenticated==='readonly') {
            $priorityStart = Zend_Registry::get('session')->priorityStart;
            $priorityEnd = Zend_Registry::get('session')->priorityEnd;
            
            // reset session with default config from config.ini
            Zend_Registry::get('bootstrap')->resetSession(false);
            
            // set priority slider
            Zend_Registry::get('session')->currentPriorityStart = $priorityStart;
            Zend_Registry::get('session')->currentPriorityEnd = $priorityEnd;
            Zend_Registry::get('session')->priorityStart = $priorityStart;
            Zend_Registry::get('session')->priorityEnd = $priorityEnd;
        }
        
        
        // don't allow any changings in readonly mode
        if(Zend_Registry::get('session')->authenticated!==true) {
            if( 
                $request->getControllerName()!='error'
                && $request->getControllerName()!='index'
                && $request->getControllerName()!='patch'
                && !($request->getControllerName()=='item' && $request->getActionName()=='list')
                && !($request->getControllerName()=='item' && $request->getActionName()=='listmore')
                && !($request->getControllerName()=='update' && $request->getActionName()=='silent')
            )
                die('access denied');
        }
    }

}