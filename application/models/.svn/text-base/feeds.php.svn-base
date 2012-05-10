<?PHP

/**
 * Model for accessing and edit the feeds
 *
 * @package    application_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_models_feeds extends application_models_base {
    

    /**
     * set up the table name
     *
     * @return void
     */
    protected function _setupTableName() {
        $this->_name = Zend_Registry::get('config')->resources->db->prefix . 'feeds';
        parent::_setupTableName();
    }
    
    
    /**
     * set up metadata as other reference table objects
     * and dependend table objects
     *
     * @return void
     */
    protected function _setupMetadata() {
        $this->_referenceMap = array(
            'categories' => array(
                        'columns'       => 'category',
                        'refTableClass' => 'application_models_categories',
                        'refColumn'     => 'id'
                        )
        );
        parent::_setupMetadata();
    }
    
    
    /**
     * set new feed order
     *
     * @return array all affected categories
     * @param Zend_Db_Table_Row $cat new category
     * @param array $feeds all ids of the feeds
     */
    public function sort($cat, $feeds) {
        if(!is_array($feeds))
            $feeds = array($feeds);
        
        // positioncounter
        $position = 0;
        
        // save old categories for correcting positions
        $oldCategories = array();
        
        // reorder feeds
        foreach($feeds as $feed) {
            $c = $this->find($feed)->current();
            if($cat->id!=$c->category)
                $oldCategories[] = $c->category;
            $c->category = $cat->id;
            $c->position = $position++;
            $c->save();
        }
        
        // fix positions of old categories
        $categoryModel = new application_models_categories();
        foreach(array_unique($oldCategories) as $current)
            $categoryModel->fixPositions($categoryModel->find($current)->current());
        
        $oldCategories[] = $cat->id;
        return $oldCategories;
    }
    
    
    /**
     * returns amount of feeds
     *
     * @return int minimum priority
     * @param int $startPriority the minimal priority
     * @param int $endPriority the maximal priority
     * @param string $view the current view mode
     */
    public function count($startPriority, $endPriority, $view = 'both') {
        $p = Zend_Registry::get('config')->resources->db->prefix;
        $select = $this->getAdapter()->select()->from($p.'feeds', 'Count(*)')
                                         ->where('priority>=?', $startPriority)
                                         ->where('priority<=?', $endPriority);
        if($view=='multimedia')
            $select->where('multimedia=1');
        elseif($view=='messages')
            $select->where('multimedia=0');
        return $this->getAdapter()->fetchOne($select);
    }
    
    
    /**
     * returns minimum priority of all feeds
     *
     * @return int minimum priority
     */
    public function minPriority() {
        $p = Zend_Registry::get('config')->resources->db->prefix;
        return $this->getAdapter()->fetchOne('SELECT MIN(priority) FROM '.$p.'feeds');
    }
    
    
    /**
     * returns maximum priority of all feeds
     *
     * @return int maximum priority
     */
    public function maxPriority() {
        $p = Zend_Registry::get('config')->resources->db->prefix;
        return $this->getAdapter()->fetchOne('SELECT MAX(priority) FROM '.$p.'feeds');
    }
    
    
    /**
     * add new feed
     *
     * @return array|int id of new feed, or error messages
     * @param array $data the post data array
     */
    public function add($data) {
        // validate data
        $input = $this->validate($data);
        if(is_array($input))
            return $input;
        
        $categoryModel = new application_models_categories();
        
        // save new feed
        $id = $this->insert(
            array_merge(
                $input->getEscaped(),
                array(
                    'icon'        =>  Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader')->getPlugin($input->getEscaped('source'))->icon,
                    'position'    =>  $categoryModel->feeds($input->getEscaped('category')), // get new position
                    'multimedia'  =>  Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader')->getPlugin($input->getEscaped('source'))->multimedia, // get multimedia or not
                    'htmlurl'     =>  ''
                ) 
            )
        );
        
        return $id;
    }
    
    
    /**
     * edit feed
     *
     * @return array|int id of edited feed, or error messages
     * @param array $data the post data array
     */
    public function edit($data) {
        // validate data
        $input = $this->validate($data, true);
        if(is_array($input))
            return $input;
        
        $categoryModel = new application_models_categories();
        $id = $input->getEscaped('id');
        $feed = $this->find($id)->current();
        
        // delete old items on source type change
        if($input->getEscaped('source')!=$feed->source) {
            $itemsModel = new application_models_items();
            $itemsModel->delete('feed='.$id);
        }
        
        // save new feed
        $this->update(
            array_merge(
                $input->getEscaped(),
                array(
                    'icon'          => Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader')->getPlugin($input->getEscaped('source'))->icon,
                    'multimedia'    => Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader')->getPlugin($input->getEscaped('source'))->multimedia // get multimedia or not
                )
            ),
            'id='.$id
        );
        
        return $id;
        
    }
    
    
    /**
     * removes a feed
     *
     * @return array|bool true or error messages
     * @param int id of the feed
     */
    public function remove($id) {
        // get feed and category
        $feed = $this->find($id);
        if($feed->count()==0)
            return Zend_Registry::get('language')->translate("feed doesn't exists");
        
        $feed = $feed->current();
        $category = $feed->category;
        
        // delete all items
        $itemsModel = new application_models_items();
        $itemsModel->delete('feed='.$feed->id);
        
        // delete icon
        $this->deleteIcon($feed);
        
        // delete messages
        $messagesModel = new application_models_messages();
        $messagesModel->delete('feed='.$feed->id);
        
        // delete feed
        $this->delete('id='.$feed->id);
        
        // reorder feeds in parent category
        if($category!=0) {
            $categoryModel = new application_models_categories();
            $categoryModel->fixPositions($categoryModel->find($category)->current());
        }
        
        // success
        return true;
    }
    
    
    /**
     * saves the new icon
     *
     * @return void
     * @param Zend_Db_Table_Row $feed the current feed
     */
    public function saveIcon($feed) {
        $icon = false;
        $iconLoader = Zend_Controller_Action_HelperBroker::getStaticHelper('icon');
        
        // use favicon url (if given)
        if(strlen(trim($feed->favicon))!=0) {
            if(@file_get_contents($feed->favicon)!==false)
                $icon = $iconLoader->loadIconFile($feed->favicon, Zend_Registry::get('config')->favicons->path);
        }
        
        // try url of the htmlurl rss feed
        if($icon===false && strlen($feed->htmlurl)>0)
            $icon = $iconLoader->load($feed->htmlurl,Zend_Registry::get('config')->favicons->path);
        
        // use datasource icon
        if($icon===false)
            $icon = Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader')->getPlugin($feed->source)->icon;
        
        // save icon url
        $feed->icon = $icon;
        $feed->dirtyicon = 0;
        $feed->save();
    }
    

    /**
     * deletes the icon file
     *
     * @return void
     * @param Zend_Db_Table_Row $feed the current feed
     */
    public function deleteIcon($feed) {
        // only delete if no other feed uses this icon
        $res = $this->fetchAll( 
            $this->select()
                 ->from($this, array('amount' => 'Count(*)'))
                 ->where('icon=?', $feed->icon)
        );
        if($res[0]['amount']==1) {
            @unlink(Zend_Registry::get('config')->favicons->path . $feed->icon); // fails on plugin feeds
        }
    }
        
     
    /**
     * validates feed input
     *
     * @return Zend_Filter_Input|array validator or error message array
     * @param array $data for validating
     * @param int $validateid (optional) indicates whether id has to be validated
     */
    protected function validate($data, $validateId = false) {
        // define filter
        $filterTrim = new Zend_Filter_StringTrim();

        $filter = array( 
            'name'          => $filterTrim,
            'url'           => $filterTrim,
            'category'      => $filterTrim,
            'priority'      => $filterTrim,
            'favicon'       => $filterTrim,
            'filter'        => $filterTrim,
            'source'        => $filterTrim
        );
        
        if(!isset($data['source']))
            $data['source'] = '';
        
        // define validators
        $validatorNotEmpty = new Zend_Validate_NotEmpty();
        $validatorNotEmpty->setMessage(Zend_Registry::get('language')->translate("Value is required and can't be empty"), Zend_Validate_NotEmpty::IS_EMPTY);
        
        $validatorCategoryId = new application_validate_categoryid();
        $validatorCategoryId->setMessage(Zend_Registry::get('language')->translate("category doesn't exists"), application_validate_categoryid::NOT_EXISTS);
        
        $validatorSource = new application_validate_source();
        $validatorSource->setMessage(Zend_Registry::get('language')->translate("source doesn't exists"), application_validate_source::NOT_EXISTS);
        
        $validatorNum = new Zend_Validate_Int(Zend_Registry::get('session')->language);
        $validatorNum->setLocale(Zend_Registry::get('session')->language);
        $validatorNum->setMessage(Zend_Registry::get('language')->translate('Only digits allowed'), Zend_Validate_Int::NOT_INT);
        $validatorNum->setMessage(Zend_Registry::get('language')->translate('Only digits allowed'), Zend_Validate_Int::INVALID);
        
        $validatorDuplicateFeed = new application_validate_duplicatefeed($data['source'],$validateId ? $data['id'] : false);
        $validatorDuplicateFeed->setMessage(Zend_Registry::get('language')->translate("feed already exists"), application_validate_duplicatefeed::ALREADY_EXISTS);
        
        $validators = array(
            'name'         => array(
                                $validatorNotEmpty,
                                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED
                                ),
            'url'          => array(
                                $validatorDuplicateFeed,
                                Zend_Filter_Input::ALLOW_EMPTY => true,
                                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                ),
            'category'     => array( 
                                $validatorNum,
                                $validatorCategoryId,
                                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                ),
            'priority'     => array( 
                                $validatorNum,
                                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED
                                ),
            'favicon'      => array( 
                                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                                Zend_Filter_Input::ALLOW_EMPTY => true
                                ),
            'filter'       => array( 
                                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                                Zend_Filter_Input::ALLOW_EMPTY => true
                                ),
            'source'       => array( 
                                $validatorNotEmpty,
                                $validatorSource,
                                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                                )
        );
        
        
        // read from source whether url is optional or not
        if($validatorSource->isValid($data['source'])) {
            $plugin = Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader')->getPlugin($data['source']);
            if(!$plugin->sourceOptional && $plugin->source!==false) {
                $validators['url'] = array(
                    $validatorNotEmpty,
                    $validatorDuplicateFeed,
                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED
                );
            }
        }
        
        
        // insert filter and validate rules for id
        if($validateId!==false) {
            $validatorFeedId = new application_validate_feedid();
            $validatorFeedId->setMessage(Zend_Registry::get('language')->translate("feed doesn't exists"), application_validate_feedid::NOT_EXISTS);
        
            $filter['id'] = $filterTrim;
            $validators['id'] = array(
                                    $validatorNum, 
                                    $validatorFeedId,
                                    Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED
                                );
        }
        
        
        // create validation main object
        $validator = new Zend_Filter_Input(
            $filter,
            $validators,
            $data,
            array(
                Zend_Filter_Input::NOT_EMPTY_MESSAGE => Zend_Registry::get('language')->translate("Value is required and can't be empty"),
                Zend_Filter_Input::BREAK_CHAIN => false
            )
        );
        
        
        // return filter input object
        return parent::validate($validator);
    }
}


?>