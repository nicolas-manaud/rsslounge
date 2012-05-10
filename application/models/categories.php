<?PHP

/**
 * Model for accessing the categories
 *
 * @package    application_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_models_categories extends application_models_base {
   
    
    /**
     * set up the table name
     *
     * @return void
     */
    protected function _setupTableName() {
        $this->_name = Zend_Registry::get('config')->resources->db->prefix . 'categories';
        parent::_setupTableName();
    }
    
    
    /**
     * set up metadata as other reference table objects
     * and dependend table objects
     *
     * @return void
     */
    protected function _setupMetadata() {
        $this->_dependentTables = array(
            'application_models_feeds'
            );
        parent::_setupMetadata();
    }
    
    
    /**
     * returns max position
     *
     * @return int max position
     */
    public function maxPosition() {
        $p = Zend_Registry::get('config')->resources->db->prefix;
        return $this->getAdapter()->fetchOne('SELECT MAX(position) FROM '.$p.'categories');
    }
    
    
    /**
     * returns amount of feeds in this category
     *
     * @return int amount of feeds in this category
     * @param int|Zend_Db_Table_Row $category current category model or category id
     */
    public function feeds($category) {
        if(is_numeric($category))
            $id = $category;
        else
            $id = $category->id;
    
        $p = Zend_Registry::get('config')->resources->db->prefix;
        $sql = 'SELECT count(*) 
            FROM '.$p.'feeds 
            WHERE '.$p.'feeds.category='.$id;
        
        return $this->getAdapter()->fetchOne($sql);
    }
    
    
    /**
     * set all categories with given data
     *
     * @return array|bool error messages as array or true on success
     * @param array $categories new categories as array
     */
    public function setCategories($categories) {
    
        // validate given categories
        $validationResult = $this->validate($categories);
        if($validationResult!==true)
            return $validationResult; // error found
        
        // delete all categories
        parent::delete('id!=0');
        
        // insert all categories
        foreach($categories as $cat)
            parent::insert($cat);
        
        // remove feeds from deleted categories
        $db = Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter();
        $p = Zend_Registry::get('config')->resources->db->prefix;
        $db->query("UPDATE " . $p . "feeds 
                    SET category=-1 
                    WHERE category NOT IN (
                        SELECT id FROM " . $p . "categories
                    )");
        
        // return success
        return true;
    }
    
    
    /**
     * fix the positions of all feeds in this category
     * feed positions of 0 1 3 4 5 will become 0 1 2 3 4
     *
     * @return void
     * @param Zend_Db_Table_Row $category category model
     */
    public function fixPositions($category) {
        $position = 0;
        
        foreach($category->findDependentRowset('application_models_feeds', null, $this->select()->order('position ASC')) as $feed) {
            $feed->position = $position++;
            $feed->save();
        }
    }
    
    
    /**
     * checks whether the category uncategorized exists
     * if not insert it
     *
     * @return void
     */
    public function checkUncategorized() {
        $res = $this->fetchAll( 
            $this->select()
                 ->from($this, array('amount' => 'Count(*)'))
                 ->where('id=-1')
        );
        
        if($res[0]['amount']==0) {
            $this->insert(array(
                'id'         => -1,
                'name'       => Zend_Registry::get('language')->translate('uncategorized'),
                'position'   => -1
            ));
        }
    }
    
    
    /**
     * validates all given categories
     *
     * @return array|bool true on success, an array with messages on error
     * @param array $categories array of new categories
     */
    protected function validate($categories) {
        // message array
        $messages = array();
        
        // flag
        $errors = false;
        
        // validator object
        $validator = $this->getValidator();
        
        // check all given categories
        for($i = 0; $i < count($categories); $i++) {
            
            // set data
            $validator->setData($categories[$i]);
            
            // insert empty error message entry for this category
            $messages[$i] = array();
            
            // validate
            if($validator->isValid()===false) {
                
                // mark that errors was found
                $errors = true;
                
                // save error messages
                foreach($validator->getMessages() as $field => $fieldArray)
                    foreach($fieldArray as $message)
                        $messages[$i][] = $message; // append error message
            }
        }
        
        // return true or array with errormessages
        return $errors ? $messages : true;
    }
    
    
    /**
     * returns an validator for a single category
     *
     * @return Zend_Filter_Input validator
     */
    protected function getValidator() {
         // define filter
        $filterTrim = new Zend_Filter_StringTrim();
        $filterUtf8 = new application_filter_utf8();
        
        $filter = array( 
            'id'          => $filterTrim,
            'name'        => array($filterTrim, $filterUtf8),
            'position'    => $filterTrim
        );
        
        
        // define validators
        $validatorId = new application_validate_categoryid();
        $validatorId->setMessage(Zend_Registry::get('language')->translate("category doesn't exists"), application_validate_categoryid::NOT_EXISTS);
        
        $validatorNotEmpty = new Zend_Validate_NotEmpty();
        $validatorNotEmpty->setMessage(Zend_Registry::get('language')->translate("Value is required and can't be empty"), Zend_Validate_NotEmpty::IS_EMPTY);
            
        $validatorAlnum = new Zend_Validate_Alnum(true);
        $validatorAlnum->setMessage(Zend_Registry::get('language')->translate('Only alphanummeric values allowed'), Zend_Validate_Alnum::NOT_ALNUM);
        $validatorAlnum->setMessage(Zend_Registry::get('language')->translate("Value is required and can't be empty"), Zend_Validate_Alnum::STRING_EMPTY);
        
        $validatorNum = new Zend_Validate_Digits(false);
        $validatorNum->setMessage(Zend_Registry::get('language')->translate('Only digits allowed'), Zend_Validate_Digits::NOT_DIGITS);
        $validatorNum->setMessage(Zend_Registry::get('language')->translate("Value is required and can't be empty"), Zend_Validate_Digits::STRING_EMPTY);
        
        $validators = array(
            'id'           => array(
                            $validatorId, 
                            Zend_Filter_Input::ALLOW_EMPTY => true, 
                            Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                            ),
            'name'         => array(
                            $validatorNotEmpty, 
                            Zend_Filter_Input::ALLOW_EMPTY => false, 
                            Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED
                            ),
            'position'     => array( 
                            $validatorNum,
                            Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                            )
        );
        
        
        // create validation main object
        $validator = new Zend_Filter_Input(
            $filter,
            $validators,
            array(),
            array(
                Zend_Filter_Input::NOT_EMPTY_MESSAGE => Zend_Registry::get('language')->translate("Value is required and can't be empty"),
                Zend_Filter_Input::BREAK_CHAIN => false
            )
        );
        
        
        // return filter input object
        return $validator;
    }
}


?>