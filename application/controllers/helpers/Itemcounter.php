<?PHP

/**
 * Helper class for count items (messages and images)
 *
 * @package    application_controllers
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Helper_Itemcounter extends Zend_Controller_Action_Helper_Abstract {
    
    /**
     * returns unread items per category
     *
     * @return array the unread items as associative array (key = categorieid,
     * value = unread items
     * @param array $settings (optional) current settings
     */
    public function unreadItemsCategories($settings = null) {
        // prepare settings
        $settings = $this->prepareSettings($settings);
        
        // select unread
        $settings['unread'] = 1;
        
        // count items
        $itemsModel = new application_models_items();
        return $itemsModel->countPerCategory($settings);
    }
    
    
    /**
     * returns unread items per feed
     *
     * @return array the unread items as associative array (key = feedid,
     * value = unread items
     * @param array $settings (optional) current settings
     */
    public function unreadItemsFeeds($settings = null) {
        // prepare settings
        $settings = $this->prepareSettings($settings);
        
        // select unread
        $settings['unread'] = 1;
        
        // count items
        $itemsModel = new application_models_items();
        return $itemsModel->countPerFeed($settings);
    }
    
    
    /**
     * returns all items
     *
     * @return int all items
     * @param array $settings (optional) current settings
     */
    public function allItems($settings = null) {
        // prepare settings
        $settings = $this->prepareSettings($settings);
        
        // count items
        $itemsModel = new application_models_items();
        return $itemsModel->countAll($settings);
    }
    
    
    /**
     * returns starred items
     *
     * @return int starred items
     * @param array $settings (optional) current settings
     */
    public function starredItems($settings = null) {
        $itemsModel = new application_models_items();
        return $itemsModel->countStarred( 
            $this->prepareSettings($settings) 
        );
    }
    
    
    /**
     * prepare settings for counting item
     * 
     * @return array the current settings
     * @param array $settings (optional) current settings
     * no settings given: use the session
     */
    protected function prepareSettings($settings = null) {
        // use session settings if no settings given
        if($settings==null)
            $settings = $this->getSessionAsArray();
            
        // remove selected
        unset($settings['selected']);
        
        return $settings;
    }
    
    
    /**
     * get session as array
     * 
     * @return array session as array
     */
    public function getSessionAsArray() {
        $settings = array();
        foreach(Zend_Registry::get('session') as $key => $value)
            $settings[$key] = $value;
        return $settings;
    }
    
    
}