<?PHP

/**
 * Validator checks whether a given source (plugin) exists or not
 *
 * @package    application_validate
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_validate_source extends Zend_Validate_Abstract {
    
    /** 
     * name for not exists
     */
    const NOT_EXISTS = 'not exists';
    
    
    /**
     * errormessages for not existing source
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_EXISTS => "source doesn't exists"
    );
    
    
    /**
     * checks whether a source exists or not
     *
     * @return boolean
     * @param string $value source plugin name
     */
    public function isValid($value) {
        
        if(Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader')->getPlugin($value)===false) {
              $this->_error(application_validate_source::NOT_EXISTS);
              return false;
        }
        
        return true;
    }

}

?>