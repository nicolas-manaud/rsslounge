<?PHP

/**
 * Model for accessing and edit the settings
 * This Model also save the settings in session
 *
 * @package    application_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_models_settings extends application_models_base {
    
    /**
     * the primary key of the table
     *
     * @var string
     */
    protected $_primary = array('name');
    
    
    /**
     * activate autoincrement
     *
     * @var boolean
     */
    protected $_sequence = false;
    

    /**
     * set up the table name
     *
     * @return void
     */
    protected function _setupTableName() {
        $this->_name = Zend_Registry::get('config')->resources->db->prefix . 'settings';
        parent::_setupTableName();
    }
    
    
    /**
     * get setting value
     *
     * @return value of given setting
     * @param string $name key of setting
     */
    public function get($name) {
        $result = $this->fetchAll(
                            $this->select()
                                 ->where('name=?',$name)
                );
        if($result->count()!=0)
            return $result->current()->value;
        else
            return false;
    }
    
    
    
    /**
     * set new setting without validating it
     *
     * @return array given data
     * @param array $data the new data as key => value array
     */
    public function set($data) {
        foreach($data as $key => $value)
            $this->write($key, $value);
        return $data;
    }
    
    
    /**
     * save new settings
     *
     * @return array|bool error messages as array or true
     * @param array $data the post data array
     */
    public function save($data) {
        // validate data
        $input = $this->validate($data);
        if(is_array($input))
            return $input;
        
        // save new settings
        foreach($input->getEscaped() as $key => $value)
            $this->write($key, $value);
        
        // success
        return true;
    }
     
     
    /**
     * returns an validator for settings params or error array
     *
     * @return Zend_Filter_Input|array on success data on error message array
     * @param array $data current data for validation
     */
    public function validate($data) {
         // define filter
        $filterTrim = new Zend_Filter_StringTrim();

        $filter = array( 
            'deleteItems'           =>    $filterTrim,
            'imagesPosition'        =>    $filterTrim,
            'imagesHeight'          =>    $filterTrim,
            'language'              =>    $filterTrim,
            'refresh'               =>    $filterTrim,
            'lastrefresh'           =>    $filterTrim,
            'view'                  =>    $filterTrim,
            'offset'                =>    $filterTrim,
            'itemsperpage'          =>    $filterTrim,
            'selected'              =>    $filterTrim,
            'dateFilter'            =>    $filterTrim,
            'dateStart'             =>    $filterTrim,
            'dateEnd'               =>    $filterTrim,
            'search'                =>    $filterTrim,
            'unread'                =>    $filterTrim,
            'starred'               =>    $filterTrim,
            'currentPriorityStart'  =>    $filterTrim,
            'currentPriorityEnd'    =>    $filterTrim,
            'saveOpenCategories'    =>    $filterTrim,
            'openCategories'        =>    $filterTrim,
            'firstUnread'           =>    $filterTrim,
            'newWindow'             =>    $filterTrim,
            'public'                =>    $filterTrim,
            'anonymizer'            =>    $filterTrim,
            'sort'                  =>    $filterTrim,
            'openitems'             =>    $filterTrim,
            'iconcache'             =>    $filterTrim
        );
        
        
        // define validators
        $validatorType = new Zend_Validate_InArray( array( "both", "multimedia", "messages" ) );
        $validatorType->setMessage(Zend_Registry::get('language')->translate('Only both, multimedia, message allowed'), Zend_Validate_InArray::NOT_IN_ARRAY);
        
        $validatorNotEmpty = new Zend_Validate_NotEmpty();
        $validatorNotEmpty->setMessage(Zend_Registry::get('language')->translate("Value is required and can't be empty"), Zend_Validate_NotEmpty::IS_EMPTY);
        
        $validatorNum = new Zend_Validate_Int(Zend_Registry::get('session')->language);
        $validatorNum->setLocale(Zend_Registry::get('session')->language);
        $validatorNum->setMessage(Zend_Registry::get('language')->translate('Only digits allowed'), Zend_Validate_Int::NOT_INT);
        
        $validatorInArray = new Zend_Validate_InArray( array( "top", "bottom" ) );
        $validatorInArray->setMessage(Zend_Registry::get('language')->translate('Only top or bottom allowed'), Zend_Validate_InArray::NOT_IN_ARRAY);
        
        $validatorLanguage = new Zend_Validate_InArray( Zend_Registry::get('language')->getList() );
        $validatorLanguage->setMessage(Zend_Registry::get('language')->translate('Language is not available'), Zend_Validate_InArray::NOT_IN_ARRAY);
        
        $validatorDate = new Zend_Validate_Date();
        $validatorDate->setMessage(Zend_Registry::get('language')->translate('No valid date given'), Zend_Validate_Date::INVALID);
        $validatorDate->setMessage(Zend_Registry::get('language')->translate('No valid date given'), Zend_Validate_Date::FALSEFORMAT);
        
        $validatorBiggerThanZero = new Zend_Validate_GreaterThan(0);
        $validatorBiggerThanZero->setMessage(Zend_Registry::get('language')->translate('Value must be bigger than 0'), Zend_Validate_GreaterThan::NOT_GREATER);
        
        $validatorBetweenDays = new Zend_Validate_Between(0,2000);
        $validatorBetweenDays->setMessage(Zend_Registry::get('language')->translate('Please choose a value between 0 and 2000 days'), Zend_Validate_Between::NOT_BETWEEN);
        
        $validatorBetweenItemsperpage = new Zend_Validate_Between(0,200);
        $validatorBetweenItemsperpage->setMessage(Zend_Registry::get('language')->translate('Please choose a value between 0 and 200 items per page'), Zend_Validate_Between::NOT_BETWEEN);
        
        $validatorSort = new Zend_Validate_InArray( array( "date", "dateasc", "priority", "priorityasc") );
        $validatorSort->setMessage(Zend_Registry::get('language')->translate('Only date or rating allowed'), Zend_Validate_InArray::NOT_IN_ARRAY);
        
        $validators = array(
            'deleteItems'           => array(
                                        $validatorNum,
                                        $validatorBetweenDays,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'imagesPosition'        => array( 
                                        $validatorInArray,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'language'              => array( 
                                        $validatorLanguage,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'refresh'               => array(
                                        $validatorNum, 
                                        $validatorBiggerThanZero,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'lastrefresh'           => array(
                                        $validatorNum, 
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'view'                  => array(
                                        $validatorType,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'offset'                => array(
                                        $validatorNum,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'itemsperpage'          => array(
                                        $validatorNum,
                                        $validatorBetweenItemsperpage,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'selected'              => array( 
                                        Zend_Filter_Input::ALLOW_EMPTY => true ,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'dateFilter'            => array(
                                        $validatorNum,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'search'                => array( 
                                        Zend_Filter_Input::ALLOW_EMPTY => true,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'unread'                => array(
                                        $validatorNum,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'starred'               => array(
                                        $validatorNum,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'currentPriorityStart'  => array(
                                        $validatorNum,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'currentPriorityEnd'    => array(
                                        $validatorNum,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'saveOpenCategories'    => array(
                                        $validatorNum,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'openCategories'        => array(
                                        Zend_Filter_Input::ALLOW_EMPTY => true,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'firstUnread'           => array(
                                        Zend_Filter_Input::ALLOW_EMPTY => true,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'newWindow'             => array(
                                        Zend_Filter_Input::ALLOW_EMPTY => true,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'public'                => array(
                                        Zend_Filter_Input::ALLOW_EMPTY => true,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'anonymizer'            => array(
                                        Zend_Filter_Input::ALLOW_EMPTY => true,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'sort'                  => array(
                                        Zend_Filter_Input::ALLOW_EMPTY => true,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL,
                                        $validatorSort
                                        ),
            'openitems'             => array(
                                        Zend_Filter_Input::ALLOW_EMPTY => true,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        ),
            'iconcache'             => array(
                                        Zend_Filter_Input::ALLOW_EMPTY => true,
                                        Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
                                        )
        );
        
        
        // optional check date
        if(isset($data['dateFilter']) && $data['dateFilter']==1) {
            $validators['dateStart'] = $validatorDate;
            $validators['dateEnd'] = $validatorDate;
        } else {
            $validators['dateStart'] = array( Zend_Filter_Input::ALLOW_EMPTY => true );
            $validators['dateEnd'] = array( Zend_Filter_Input::ALLOW_EMPTY => true );
            $data['dateStart'] = '';
            $data['dateEnd'] = '';
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
    
    
    /**
     * write back new setting
     *
     * @return void
     * @param mixed $key
     * @param mixed $value
     */
    public function write($key, $value) {
        // save new value in database
        if($this->fetchAll($this->select()->where("name=?",$key))->count()==0) {
            $this->insert(array(
                        'name'  => $key,
                        'value' => $value
                        ));
        } else {
            $this->update( array(
                        'value' => $value
                       ),
                       $this->getAdapter()->quoteInto('name=?',$key));
        }
        
        // save new value in session
        Zend_Registry::get('session')->__set(
            $key,
            $value
        );
    }
}


?>