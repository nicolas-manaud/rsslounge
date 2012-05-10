<?php

/**
 * Controller for listing and editing items (messages and images)
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class ItemController extends Zend_Controller_Action {

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
        
        // no automatic view rendering
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
    }
    
    
    /**
     * shows a list of items
     * will be called on every change of settings and
     * returns the messages and images
     *
     * @return void
     */
    public function listAction() {
        $isJson = $this->getRequest()->getParam('json', false);
    
        // load current settings
        $settingsModel = new application_models_settings();
        $currentSettings = array();
        foreach($settingsModel->fetchAll() as $setting)
            $currentSettings[$setting->name] = $setting->value;
    
        // read settings
        $settings = $this->getRequest()->getPost();
        $settings = array_merge($currentSettings,$settings);
        
        // get list template vars
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('list');
        try {
            $listHelper->readItems($settings);
            $listHelper->setTemplateVars($this->view);
        } catch(Exception $e) {
            $this->_helper->json(array( 'error' => $e->getMessage()));
        }
        
        // save settings
        if(Zend_Registry::get('config')->demomode!="1" && Zend_Registry::get('session')->authenticated===true) {
            $settingsModel->save( array( 
                    'currentPriorityStart'   => $settings['currentPriorityStart'],
                    'currentPriorityEnd'     => $settings['currentPriorityEnd'],
                    'view'                   => $settings['view'],
                    'selected'               => $settings['selected'],
                    'unread'                 => $settings['unread'],
                    'starred'                => $settings['starred'],
                    'sort'                   => $settings['sort']
            ) );
        }
        
        // get unread items
        $itemCounter = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter');
        
        // return items
        $feedModel = new application_models_feeds();
        $result = array( 
            'html'           => utf8_encode(utf8_decode($this->view->render('item/list.'.Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix()))),
            'categories'     => $itemCounter->unreadItemsCategories($settings),
            'feeds'          => $itemCounter->unreadItemsFeeds($settings),
            'starred'        => $itemCounter->starredItems($settings),
            'all'            => $itemCounter->allItems($settings),
            'countfeeds'     => $feedModel->count(Zend_Registry::get('session')->currentPriorityStart, 
                                                  Zend_Registry::get('session')->currentPriorityEnd,
                                                  Zend_Registry::get('session')->view)
        );
        
        if($isJson==true) {
            unset($result['html']);
            $result['messages'] = $listHelper->getMessages();
            $result['moremessages'] = $listHelper->hasMoreMessages();
            $result['multimedia'] = $listHelper->getMultimedia();
            $result['moremultimedia'] = $listHelper->hasMoreMultimedia();
        }
        
        $this->_helper->json($result);
    }
    
    
    /**
     * list more items
     * will be executed on clicking on more
     *
     * @return void
     */
    public function listmoreAction() {
        // load current settings
        $settingsModel = new application_models_settings();
        $currentSettings = array();
        foreach($settingsModel->fetchAll() as $setting)
            $currentSettings[$setting->name] = $setting->value;
    
        // read settings
        $settings = $this->getRequest()->getParams();
        $settings = array_merge($currentSettings,$settings);
        
        // get items
        $result = $this->listItems($settings);
        
        // show items
        $this->_helper->json($result);
    }
    
    
    /**
     * mark as read
     * sends a new image or message to client
     * if only unread items filter set
     *
     * @return void
     */
    public function markAction() {
        // get item
        $item = $this->getItem();
        if($item===false)
            return;
        
        // toggle item mark
        if($item->unread==1)
            $item->unread = 0;
        else
            $item->unread = 1;
        $item->save();
        
        // get next item only unread items visible? 
        $itemCounter = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter');
        $settings = $this->getRequest()->getParams();
        $result = array();
        
        if($settings['unread'] == 1) {
            // load next item (if item exists)
            $settings['offset'] = $this->getRequest()->getParam('items');
            $settings['itemsperpage'] = 1;
            $result = $this->listItems($settings);
        }
        
        // return unread items
        $result['categories'] = $itemCounter->unreadItemsCategories();
        $result['feeds'] = $itemCounter->unreadItemsFeeds();
        $result['starred'] = $itemCounter->starredItems();
        
        $this->_helper->json($result);
    }
    
    
    /**
     * mark all as read
     *
     * @return void
     */
    public function markallAction() {
        $items = $this->getRequest()->getParam('items');
        if(!is_array($items))
            $items = array();
            
        // mark items as read
        $itemModel = new application_models_items();
        $itemModel->markAsRead($items);
        unset($items);
        
        // count items of all categories
        $itemCounter = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter');
        $items = $itemCounter->unreadItemsCategories();
        
        // no more unread items available?
        if(count($items)==1)
            $this->_helper->json(array(
                'success'    => true,
                'next'       => '0'
            ));
        
        // get current selected cat or feed
        $selected = Zend_Registry::get('session')->selected;
        if(strlen($selected)==0)
            $selected = 'cat_0';
            
        // category selected?
        if(strlen($selected)>4 && substr($selected,0,4)=='cat_') {
            $current = substr($selected,4);
            $type = 'cat_';
        }
        
        // feed selected?
        if(strlen($selected)>5 && substr($selected,0,5)=='feed_') {
            $items = $itemCounter->unreadItemsFeeds();
            $current = substr($selected,5);
            $type = 'feed_';
        }
        
        // other unread items found and current has no more unread items
        if(!array_key_exists($current, $items) && count($items)>0) {
            $keys = array_keys($items);
            $this->_helper->json(array(
                'success'    => true,
                'next'       => $type.$keys[0]
            ));
        
        // current has more unread items
        } else
            $this->_helper->json(array(
                'success'  => true,
                'next'     => $selected
            ));
    }
    
    
    /**
     * mark as starred
     *
     * @return void
     */
    public function starAction() {
        // get item
        $item = $this->getItem();
        if($item===false)
            return;
        
        // toggle item mark
        if($item->starred==1)
            $item->starred = 0;
        else
            $item->starred = 1;
        $item->save();
        
        // return starred items
        $itemCounter = Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter');
        $result['starred'] = $itemCounter->starredItems();
        
        $this->_helper->json($result);
    }
    
    
    /**
     * unstarr all
     *
     * @return void
     */
    public function unstarrallAction() {
        $items = $this->getRequest()->getParam('items');
        if(!is_array($items))
            $this->_helper->json(true);
        $items = array_unique($items);
        
        // mark items as unstarred
        $itemModel = new application_models_items();
        foreach($items as $id) {
            $item = $itemModel->find($id);
            if($item->count()>0) {
                $item->current()->starred = 0;
                $item->current()->save();
            }
        }
        
        $this->_helper->json(true);
    }
    
    
    /**
     * list more items
     *
     * @return array with items
     * @param array $settings current settings
     */
    public function listItems($settings) {
        $isJson = $this->getRequest()->getParam('json', false);
        
        // get items
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('list');
        try {
            $listHelper->readItems($settings);
        } catch(Exception $e) {
            return array( 'error' => $e->getMessage());
        }
        
        // create html for message and return as json
        $return = array();
        if($settings['offset']>0 && $settings['view']=='messages') {
        
            if($isJson==true) {
                $return['messages'] = $listHelper->getMessages();
                $return['more'] = $listHelper->hasMoreMessages();
            } else {
                // render message items
                $return['messages'] = $this->view->partialLoop(
                                        'item/message-item.'.Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix(),
                                        $listHelper->getMessages()
                                    );
                                    
                // indicates whether more items available or not
                $return['more']    = $listHelper->hasMoreMessages();
            }
        }
        
        // create html for multimedia and return as json
        if($settings['offset']>0 && $settings['view']=='multimedia') {
            if($isJson==true) {
                $return['multimedia'] = $listHelper->getMultimedia();
                $return['more'] = $listHelper->hasMoreMultimedia();
            } else {
                // render multimedia items
                $return['multimedia'] = $this->view->partialLoop(
                                        'item/multimedia-item.'.Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix(),
                                        $listHelper->getMultimedia()
                                    );
                                    
                // indicates whether more items available or not                    
                $return['more'] = $listHelper->hasMoreMultimedia();
           }
        }
        
        return $return;
    }
    
    
    /**
     * validates given item id and returns item row object
     *
     * @return Zend_Db_Table_Row|bool false on error or item row object
     */
    protected function getItem() {
        // get id
        $id = $this->getRequest()->getParam('id');
        
        // search and validate given id
        $itemModel = new application_models_items();
        $item = $itemModel->find($id);
        if($item->count()==0)
            $this->_helper->json(array(
                'error'     => $this->view->translate('invalid item id')
            ));
        else
            return $item->current();
    }
}

