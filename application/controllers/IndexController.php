<?php

/**
 * Controller for showing the main window frame
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class IndexController extends Zend_Controller_Action {
    
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
        
        // set version (for footer)
        $view->version = Zend_Registry::get('version');
    }
    
    
    /**
     * show main window
     *
     * @return void
     */
    public function indexAction() {
        // prepare icon cache
        Zend_Controller_Action_HelperBroker::getStaticHelper('icon')->generateIconImage();
    
        // stop if ie is current browser
        $browser = @get_browser();
        if(isset($browser->browser) && get_browser()->browser == 'IE' && get_browser()->majorver == 6)
            $this->_redirect('index/ie');
        
        // update
        $this->checkDatabase();
        
        // load feedlist
        $this->feedlistData();
        
        // set unread if setting firstUnread is set and unread available
        if(Zend_Registry::get('session')->firstUnread==1 && $this->view->unread>0)
            Zend_Registry::get('session')->unread = 1;
        
        // convert session into array (for loading items)
        $settings = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter')->getSessionAsArray();
        
        // get list template vars
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('list');
        try {
            $listHelper->readItems($settings);
            $listHelper->setTemplateVars($this->view);
        } catch(Exception $e) {
            $this->view->messages = $e->getMessage();
        }
        
        // set new timeout for rss refresh in session settings
        Zend_Controller_Action_HelperBroker::getStaticHelper('updater')->timeout();
        
        // logout/login available?
        $users = new application_models_users();
        if($users->getUsername()!==false && Zend_Registry::get('session')->authenticated===true)
            $this->view->logout = true;
        elseif(Zend_Registry::get('session')->authenticated==='readonly')
            $this->view->login = true;
        
        // add new feed? Then show the dialog (for add feed bookmark)
        $this->view->newfeed = $this->getRequest()->getParam('url', '');
        
        // ipad detection
        $this->view->isIpad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
    }
    
    
    /**
     * login dialog
     *
     * @return void
     */
    public function loginAction() {
        $username = $this->getRequest()->getParam('username', false);
        $password = $this->getRequest()->getParam('password', false);
        $isJson = $this->getRequest()->getParam('json', false);
        
        // login
        $users = new application_models_users();
        if($username) {
            if($users->authenticate($username, $password)) {
                Zend_Registry::get('session')->authenticated=true;
                if($isJson==true)
                    $this->_helper->json(array('success' => true));
                
                $this->_redirect('');
            } else {
                $this->view->error = true;
                
                if($isJson==true)
                    $this->_helper->json(array('success' => false));
            }
        }
        
        if($this->getRequest()->getParam('logout', false)!==false)
            $this->view->logout = true;
            
        if($isJson==true)
            $this->_helper->json(array('success' => true));
    }
    
    
    /**
     * about dialog
     *
     * @return void
     */
    public function aboutAction() {
        
    }
    
    
    /**
     * ie error
     *
     * @return void
     */
    public function ieAction() {
        
    }
    
    
    /**
     * set feedlist data for the current view
     *
     * @return void
     */
    protected function feedlistData() {
        // check whether uncategorized exists or not (add it if not)
        $categoriesModel = new application_models_categories();
        $categoriesModel->checkUncategorized();
    
        // categories and feeds (target template param)
        $this->view->categories = array();
        
        // load unread items
        $itemCounter = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter');
        $unreadCategory = $itemCounter->unreadItemsCategories();
        $unreadFeed = $itemCounter->unreadItemsFeeds();
        
        // get open categories
        if(Zend_Registry::get('session')->saveOpenCategories==1)
            $open = explode(',',Zend_Registry::get('session')->openCategories);
        else
            $open = array();
        
        // load categories
        $categoriesDb = $categoriesModel->fetchAll( 
                            $categoriesModel->select()->order('position ASC') 
                        );
        
        $feedPositions = Zend_Controller_Action_HelperBroker::getStaticHelper('icon')->getFeedsIconPosition();
        
        // read feeds and count items of the loaded categories
        $feedsModel = new application_models_feeds();
        foreach($categoriesDb as $cat) {
            $newcat = $cat->toArray();
            
            // get feeds of cat
            $feedRowset = $cat->findDependentRowset('application_models_feeds', null, $feedsModel->select()->order('position ASC'));
            $newcat['feeds'] = array();
            foreach($feedRowset as $feed) {
                $newfeed = $feed->toArray();
                $newfeed['unread'] = isset($unreadFeed[$feed->id]) ? $unreadFeed[$feed->id] : 0;
                $newfeed['iconposition'] = $feedPositions[$feed->id];
                $newcat['feeds'][] = $newfeed;
            }
        
            // get unread items of cat
            $newcat['unread'] = isset($unreadCategory[$cat->id]) ? $unreadCategory[$cat->id] : 0;
            
            // set open (feeds visible) or not
            if(in_array($cat->id, $open))
                $newcat['open'] = true;
            else
                $newcat['open'] = false;
                
            // append category
            $this->view->categories[] = $newcat;
        }
        
        // count starred items
        $this->view->starred = $itemCounter->starredItems();
        
        // count unread items
        $this->view->unread = $unreadCategory[0];
        
        // count all items
        $this->view->all = $itemCounter->allItems();
        
        // count read items
        $this->view->read = $this->view->all - $this->view->unread;
        
        // count feeds
        $this->view->amountfeeds = $feedsModel->count(Zend_Registry::get('session')->currentPriorityStart, 
                                                      Zend_Registry::get('session')->currentPriorityEnd,
                                                      Zend_Registry::get('session')->view);
        
    }
    
    
    /**
     * returns current browser
     * taken from http://showif.com
     *
     * @return string browser
     */
    protected function getBrowser() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $ub = 'other';
        if(preg_match('/MSIE/i',$u_agent))
            $ub = 'ie';
        elseif(preg_match('/Firefox/i',$u_agent))
            $ub = 'firefox';
        elseif(preg_match('/Mozilla/i',$u_agent))
            $ub = 'firefox';
        elseif(preg_match('/Safari/i',$u_agent))
            $ub = 'safari';
        elseif(preg_match('/Chrome/i',$u_agent))
            $ub = 'chrome';
        elseif(preg_match('/Opera/i',$u_agent))
            $ub = 'opera';
        return $ub;
    }
    
    
    /**
     * checks whether database must be updated
     *
     * @return void
     */
    protected function checkDatabase() {
        $b = Zend_Registry::get('bootstrap');
        if($b->getCurrentVersion() < $b->getApplicationVersion())
            $this->_forward('index','patch');
    }

}

