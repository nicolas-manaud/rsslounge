<?PHP

/**
 * Validator for a given category id
 *
 * @package    application_validate
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_validate_categoryid extends Zend_Validate_Abstract {
    
    /** 
     * name for not exists
     */
    const NOT_EXISTS = 'not exists';
    
    
    /**
     * errormessages for not existing category
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_EXISTS => "category doesn't exists"
    );
    
    
    /**
     * checks whether a category exists or not
     *
     * @return boolean
     * @param int $value the current category id
     */
    public function isValid($value) {
        // uncategorized
        if($value==0)
            return true;
            
        $categories = new application_models_categories();
            
        if($categories->fetchAll($categories->select()->where("id=?",$value))->count()==0) {
              $this->_error(application_validate_categoryid::NOT_EXISTS);
              return false;
        }
        
        return true;
    }

}

?>