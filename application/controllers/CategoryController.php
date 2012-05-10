<?php

/**
 * Controller for handling all category related tasks
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class CategoryController extends Zend_Controller_Action {

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
        // load categories
        $categories = new application_models_categories();
        $this->view->categories = $categories->fetchAll($categories->select()->order('position ASC'));
    }
    
    
    /**
     * saves the given categories
     *
     * @return void
     */
    public function saveAction() {
        // get new categories
        $newcategories = $this->getRequest()->getParam('categories');
        
        // validate parameter
        if(!is_array($newcategories))
            $this->_helper->json( array( 
                        'error' => Zend_Registry::get('language')->translate('no data given') 
                    ) );
        
        // prepare categories for insertion and update
        $newcategories = array_chunk($newcategories, 2);
        $categorieList = array();
        $position = 0;
        foreach($newcategories as $cat) {
            $newCat = array( 
                'name'     => $cat[1],
                'position' => $position++
            );
                
            // parse id (z.B. cat_3 => 3)
            if(strlen(trim($cat[0]))>=4)
                $newCat['id'] = substr(trim($cat[0]),4);
                 
            $categorieList[] = $newCat;
        }
        
        // insert and update
        $categories = new application_models_categories();
        $result = $categories->setCategories($categorieList);
        
        $this->_helper->json($result);
    }

    
    /**
     * saves open categories
     *
     * @return void
     */
    public function openAction() {
        $settingsModel = new application_models_settings();
        $settingsModel->save($this->getRequest()->getParams());
        $this->_helper->json(true);
    }
}

