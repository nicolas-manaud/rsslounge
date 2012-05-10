<?PHP

/**
 * Validator checks whether a feed already exists or not
 *
 * @package    application_validate
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_validate_duplicatefeed extends Zend_Validate_Abstract {
    
    /** 
     * name for already exists
     */
    const ALREADY_EXISTS = 'already exists';
    
    
    /** 
     * current source type
     * @var string
     */
    protected $source;
    
    
    /** 
     * current feed id
     * @var int
     */
    protected $id;
    
    
    /** 
     * save also the current source and feed id
     *
     * @param string source
     * @param int id
     */
    public function __construct($source,$id) {
        $this->source = $source;
        $this->id = $id;
    }
    
    
    /**
     * errormessages for feeds which already exists
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::ALREADY_EXISTS => 'already exists'
    );
    
    
    /**
     * checks whether a feed entry already exists or not
     *
     * @return boolean
     * @param string $value url
     */
    public function isValid($value) {
        
        $feeds = new application_models_feeds();
        $select = $feeds->select()
                        ->where("url=?",$value)
                        ->where("source=?",$this->source);
        if($this->id)
            $select->where("id!=?",$this->id);
        
        if($feeds->fetchAll($select)->count()>0) {
              $this->_error(application_validate_duplicatefeed::ALREADY_EXISTS);
              return false;
        }
        
        return true;
    }

}

?>