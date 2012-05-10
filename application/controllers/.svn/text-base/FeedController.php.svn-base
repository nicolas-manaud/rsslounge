<?php

/**
 * Controller for handling all category related tasks like
 * add, remove, edit or reorder the feeds
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class FeedController extends Zend_Controller_Action {

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
        
        // suppress rendering
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
    }
    
    
    /**
     * save new order of feeds (sortable)
     *
     * @return void
     */
    public function sortAction() {
        // get new category
        $category = $this->getRequest()->getParam('cat');
        if(strlen($category)>4)
            $category = substr($category,4);
        
        // load category
        $categoriesModel = new application_models_categories();
        $category = $categoriesModel->find($category);
        
        // no category found: abort
        if($category->count()==0)
            return;
        
        // get feeds in new order
        $feeds = $this->getRequest()->getParam('feed');
        
        // save new order
        $feedsModel = new application_models_feeds();
        $categories = $feedsModel->sort($category->current(), $feeds);
        
        // send new unread items
        $unread = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter')->unreadItemsCategories();
        $this->_helper->json($unread);
    }
    
    
    /**
     * show add dialog
     *
     * @return void
     */
    public function addAction() {
        // use add and edit template
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(false);
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setRender('addedit');
        
        // prepare source and category list
        $this->prepare();
        
        // set predefined feed url
        $this->view->newfeed = $this->getRequest()->getParam('newfeed','');
    }
    
    
    /**
     * show edit dialog
     *
     * @return void
     */
    public function editAction() {
        // use add and edit template
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(false);
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setRender('addedit');
        
        // set edit mode
        $this->view->edit = true;
        
        // prepare source and category list
        $this->prepare();
        
        // check whether given feed exist and load it
        $feedModel = new application_models_feeds();
        $feeds = $feedModel->find($this->getRequest()->getParam('id'));
        
        if($feeds->count()==0) {
            echo($this->view->translate('No feed with given ID found'));
            
            // suppress view rendering
            Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
            
            return;
        }
        
        $this->view->feed = $feeds->current();
    }
    
    
    /**
     * save feed
     *
     * @return void
     */
    public function saveAction() {
        // get data
        $data = $this->getRequest()->getPost();
        
        // get feed model for db access
        $feedModel = new application_models_feeds();
        
        if(strlen(trim($data['id']))==0)
            $new = true;
        else
            $new = false;
        
        // validate and write/add feed
        if($new)
            $result = $feedModel->add($data);
        else
            $result = $feedModel->edit($data);
        
        // error
        if(is_array($result)) {
            $this->_helper->json( array(
                'success' => false,
                'errors'  => $result
            ) );
        
        // success
        } else {
            // get new feed
            $newFeed = $feedModel->find($result)->current();
            
            // update items of new feed
            if($new) {
                $updater = Zend_Controller_Action_HelperBroker::getStaticHelper('updater');
                $updater->feed($newFeed);
            
            // delete old icon (on edit feed)
            } else {
                $feedModel->deleteIcon($newFeed);
            }
            
            // save new icon
            $feedModel->saveIcon($newFeed);
            
            // set new priorities
            $newSettings = $this->resetPriorities();
            
            // disable icon caching if user added a feed
            Zend_Registry::get('config')->cache->iconcaching=0;
            
            // renew iconcache
            Zend_Controller_Action_HelperBroker::getStaticHelper('icon')->resetIconImage();
            
            // build result
            $result = array(
                'success'    => true,
                
                // new feed data
                'feed'       => array(
                                'id'          => $newFeed->id,
                                'category'    => $newFeed->category,
                                'position'    => $newFeed->position,
                                'html'        => $this->view->partial(
                                                    'feed/feed.'.Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix(), 
                                                    $newFeed->toArray()
                                                )
                                ),
                
                // new count value for unread items on affected categories
                'categories' => Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter')->unreadItemsCategories(),
                
                // count all items
                'all'        => Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter')->allItems(),
                
                // new settings (priority)
                'settings'   => $newSettings,
                
                // count feeds
                'feeds'      => $feedModel->count(Zend_Registry::get('session')->currentPriorityStart, 
                                                  Zend_Registry::get('session')->currentPriorityEnd,
                                                  Zend_Registry::get('session')->view)
            );
            
            $this->_helper->json($result);
        }
        
    }
    
    
    /**
     * delete feed
     *
     * @return void
     */
    public function deleteAction() {
        // get id
        $id = $this->getRequest()->getParam('id');
        
        // delete feed
        $feedModel = new application_models_feeds();
        $result = $feedModel->remove($id);
        
        // return unread items or error
        $return = array();
                
        if($result===true) {
            // renew iconcache
            Zend_Controller_Action_HelperBroker::getStaticHelper('icon')->resetIconImage();
        
            // count unread items per category
            $return['categories'] = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter')->unreadItemsCategories();
            
            // count all feeds
            $return['feeds'] = $feedModel->count(Zend_Registry::get('session')->currentPriorityStart, 
                                                 Zend_Registry::get('session')->currentPriorityEnd,
                                                 Zend_Registry::get('session')->view);
            
            // count all items
            $return['all'] = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter')->allItems();
            
            // new settings (priority)
            $return['settings'] = $this->resetPriorities();
        
        } else 
            $return['error'] = $result;
        
        // send result
        $this->_helper->json($return);
    }
    
    
    /**
     * set view template params for showing all available sources
     * in add/edit dialog. prepares categories
     *
     * @return void
     */
    protected function prepare() {
        
        // load available plugins
        $plugins = Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader')->getPlugins();
        
        // order plugins by category
        $cat = array();
        foreach($plugins as $plugin => $obj)
            $cat[$obj->category][$plugin] = $obj;
        
        ksort($cat);
        $this->view->sources = $cat;
        
        // load categories
        $categories = new application_models_categories();
        $cats = $categories->fetchAll($categories->select()->order('position ASC'));
        
        $this->view->categories = array();
        foreach($cats as $cat)
            $this->view->categories[$cat->id] = $cat->name;
        
    }
    
    
    /**
     * reset priorities depending on the current
     * max and min priorities of all feeds
     *
     * @return array new settings
     */
    protected function resetPriorities() {
        // set min and max priority
        $feedModel = new application_models_feeds();
        $min = $feedModel->minPriority();
        $max = $feedModel->maxPriority();
        $newSettings = array(
                'priorityStart'  => $min,
                'priorityEnd'    => $max
        );
        
        // reset current priority if necessary 
        if(Zend_Registry::get('session')->currentPriorityStart < $min)
            $newSettings['currentPriorityStart'] = $min;
        if(Zend_Registry::get('session')->currentPriorityEnd > $max)
            $newSettings['currentPriorityEnd'] = $max;
        if(Zend_Registry::get('session')->currentPriorityEnd < $min)
            $newSettings['currentPriorityEnd'] = $min;
        
        // save new settings
        $settings = new application_models_settings();
        $settings->set($newSettings);
        
        return $newSettings;
    }
    
}

