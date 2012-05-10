<?PHP

/**
 * Helper class for list items (messages and images)
 *
 * @package    application_controllers
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Helper_List extends Zend_Controller_Action_Helper_Abstract {

    /**
     * list of multimedia items
     *
     * @var array
     */
    private $multimedia;
    
    
    /**
     * indicates whether more multimedia
     * items available
     *
     * @var boolean
     */
    private $moreMultimedia;
    
    
    /**
     * message items
     *
     * @var array
     */
    private $messages;
    
    
    /**
     * indicates whether more message
     * items available
     *
     * @var boolean
     */
    private $moreMessages;
    
    
    /**
     * returns all message items
     *
     * @return array message items
     */
    public function getMessages() {
        return $this->messages;
    }
    
    
    /**
     * returns all multimedia items
     *
     * @return array multimedia items
     */
    public function getMultimedia() {
        return $this->multimedia;
    }
    
    
    /**
     * indicates whether more message
     * items available
     *
     * @return boolean true if more mm items available
     */
    public function hasMoreMessages() {
        return $this->moreMessages;
    }
    
    
    /**
     * indicates whether more multimedia
     * items available
     *
     * @return boolean true if more mm items available
     */
    public function hasMoreMultimedia() {
        return $this->moreMultimedia;
    }
    
    
    /**
     * read items from database
     *
     * @return void
     * @param array $settings the current settings
     */
    public function readItems($settings) {
        $itemsModel = new application_models_items();
        $settingsModel = new application_models_settings();
        
        // set current search as global var
        Zend_Registry::set('search', isset($settings['search']) ? $settings['search'] : '');
        
        // validate settings
        if(is_array($settingsModel->validate($settings)))
            throw new Exception(Zend_Registry::get('language')->translate('an error occured'));
        
        // disable icon caching if user added or deleted a feed
        if(isset($settings['iconcache']) && $settings['iconcache']=='disabled')
            Zend_Registry::get('config')->cache->iconcaching=0;
        
        // load messages
        if($settings['view']=='both' || $settings['view']=='messages') {
            $this->messages = $itemsModel->get($settings,'messages');
            $this->moreMessages = $itemsModel->hasMore($settings,'messages');
        }
        
        // load multimedia
        if($settings['view']=='both' || $settings['view']=='multimedia') {
            // set amount of images (which will be loaded)
            if($settings['view']=='both' && count($this->messages)!=0)
                $settings['itemsperpage'] = Zend_Registry::get('config')->thumbnails->imagesperline;
        
            $this->multimedia = $itemsModel->get($settings,'multimedia');
            $this->moreMultimedia = $itemsModel->hasMore($settings,'multimedia');
        }
    }
    
    
    /**
     * write items in template vars of the given view
     *
     * @return void
     * @param Zend_View $view the current view
     */
    public function setTemplateVars($view) {
        $view->messages = $this->getMessages();
        $view->moreMessages = $this->hasMoreMessages();
        $view->multimedia = $this->getMultimedia();
        $view->moreMultimedia = $this->hasMoreMultimedia();
    }

}
