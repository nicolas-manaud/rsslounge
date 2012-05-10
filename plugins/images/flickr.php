<?PHP 

/**
 * Plugin for fetching the gallery of a given flickr url
 *
 * @package    plugins
 * @subpackage flickr
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class plugins_images_flickr extends plugins_rss_multimedia {

    /**
     * url of the icon or false if no icon available
     *
     * @var string
     */
    public $icon = 'plugins/images/flickr.ico';

        
    /**
     * category of this source type
     *
     * @var string
     */
    public $category = 'Images';
    
    
    /**
     * description of this source type
     *
     * @var string
     */
    public $description = 'This feed fetches a flickr RSS Feed';

    
     /**
     * returns the thumbnail of this item (for multimedia feeds)
     *
     * @return mixed thumbnail data
     */
    public function getThumbnail() {
        $item = current($this->items);
        
        // allways take photo from content
        $dom = new Zend_Dom_Query(@$item->get_content());  
        $imgTags = $dom->query('img');
        if(count($imgTags))
            return $imgTags->current()->getAttribute('src');
    }
    
}
