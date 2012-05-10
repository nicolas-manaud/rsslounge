<?PHP

/**
 * Base model configures the basic settings for all models
 *
 * @package    application_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_models_base extends Zend_Db_Table {
    
    /**
     * the primary key of the table
     *
     * @var string
     */
    protected $_primary = 'id';
    
    
    /**
     * activate autoincrement
     *
     * @var boolean
     */
    protected $_sequence = true;
    
    
    /**
     * sets the database adapter
     *
     * @return void
     */
    protected function _setupDatabaseAdapter() {
        $this->_setAdapter(Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter());
        parent::_setupDatabaseAdapter();
    }
    
    
    /**
     * sets the metadata (in this case the database name)
     *
     * @return void
     */
    protected function _setupMetadata() {
        $this->_schema = Zend_Registry::get('config')->resources->db->params->dbname;
        parent::_setupMetadata();
    }
    
    
    /**
     * executes the validation and creates an error array or
     * returns the Zend_Filter_Input object
     *
     * @return Zend_Filter_Input|array
     * @param Zend_Filter_Input $input
     */
     protected function validate($input) {
         if(!$input->isValid()) {
             $errors = array();
             foreach($input->getMessages() as $field => $fieldArray)
                 foreach($fieldArray as $message)
                     $errors[$field] = $message;
             return $errors;
         } else
             return $input;
     }
     
     
     /**
     * optimize tables
     *
     * @return void
     */
     public static function optimizeDatabase() {
        $prefix = Zend_Registry::get('config')->resources->db->prefix;
        $db = Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter();
        $db->query(
            "OPTIMIZE TABLE 
                `".$prefix."categories` , 
                `".$prefix."feeds` , 
                `".$prefix."items` , 
                `".$prefix."messages` , 
                `".$prefix."settings` , 
                `".$prefix."version` "
        );
     }
}


?>