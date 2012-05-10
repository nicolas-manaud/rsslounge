<?PHP

/**
 * Model for accessing and edit the errormessages
 *
 * @package    application_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_models_messages extends application_models_base {
   
    /**
     * set up the table name
     *
     * @return void
     */
    protected function _setupTableName() {
        $this->_name = Zend_Registry::get('config')->resources->db->prefix . 'messages';
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
            'messages' => array(
                        'columns'       => 'feed',
                        'refTableClass' => 'application_models_feeds',
                        'refColumn'     => 'id'
                        )
        );
        parent::_setupMetadata();
    }
    
    
    /**
     * adds a new message
     *
     * @return void
     * @param Zend_Db_Table_Row $feed the related feed
     * @param string $message the message text
     */
    public function add($feed, $message) {
        // insert new error message
        $this->insert(array(
                'feed'        => $feed->id,
                'datetime'    => date('Y-m-d H:i:s'),
                'message'     => $message
            ));
            
        // set error in feed table
        $feed->error = 1;
        $feed->save();
        
        return $message;
    }
    
    
    /**
     * cleanup old messages
     *
     * @return void
     */
    public function cleanup() {
        $date = Zend_Date::now();
        $date->sub(Zend_Registry::get('config')->errormessages->lifetime, Zend_Date::DAY);
        $this->delete(
            $this->getAdapter()->quoteInto('datetime<?', $date->toString('YYYY-MM-dd') . ' 00:00:00')
        );
    }
}


?>