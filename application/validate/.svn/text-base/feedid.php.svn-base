<?PHP

/**
 * Validator for a given feed id
 *
 * @package    application_validate
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_validate_feedid extends Zend_Validate_Abstract {
    
    /** 
     * name for not exists
     */
    const NOT_EXISTS = 'not exists';
    
    
    /**
     * errormessages for not existing feed
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_EXISTS => "feed doesn't exists"
    );
    
    
    /**
     * checks whether a feed exists or not
     *
     * @return boolean
     * @param int $value feed id
     */
    public function isValid($value) {
        
        $feeds = new application_models_feeds();
            
        if($feeds->fetchAll($feeds->select()->where("id=?",$value))->count()==0) {
              $this->_error(application_validate_feedid::NOT_EXISTS);
              return false;
        }
        
        return true;
    }

}

?>