<?PHP 

/**
 * Plugin for fetching an rss feed
 * This plugin extracts images from the feed for showing them as
 * thumbnail
 *
 * @package    plugins
 * @subpackage rss
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class plugins_rss_multimedia extends plugins_rss_feed {

    /**
     * url of the icon or false if no icon available
     *
     * @var bool|string
     */
    public $icon = 'plugins/rss/multimedia.ico';

        
    /**
     * category of this source type
     *
     * @var string
     */
    public $category = 'Default';
    
    
    /**
     * description of this source type
     *
     * @var string
     */
    public $description = 'An default RSS Feed for aggregating Images and Photos';
    
    
    /**
     * true = multimedia feed
     * false = text messages
     *
     * @var bool
     */
    public $multimedia = true;
    
    
    /**
     * returns the thumbnail of this item (for multimedia feeds)
     *
     * @return mixed thumbnail data
     */
    public function getThumbnail() {
        $item = current($this->items);
        
        // search enclosures (media tags)
        if(count(@$item->get_enclosures()) > 0) {
        
            // thumbnail given?
            if(@$item->get_enclosure(0)->get_thumbnail())
                return @$item->get_enclosure(0)->get_thumbnail();
            
            // link given?
            elseif(@$item->get_enclosure(0)->get_link())
                return @$item->get_enclosure(0)->get_link();
        
        // no enclosures: search image link in content
        } else {
            $dom = new Zend_Dom_Query(@$item->get_content());  
            $imgTags = $dom->query('img');
            if(count($imgTags))
                return $imgTags->current()->getAttribute('src');
        }
    }
    
}
