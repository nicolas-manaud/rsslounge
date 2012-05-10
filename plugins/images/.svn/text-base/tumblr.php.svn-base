<?PHP 

/**
 * Plugin for fetching the items of a given tumblr blog
 *
 * @package    plugins
 * @subpackage images
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class plugins_images_tumblr extends plugins_rss_multimedia {

    /**
     * url of the icon or false if no icon available
     *
     * @var string
     */
    public $icon = 'plugins/images/tumblr.gif';

        
    /**
     * category of this source type
     *
     * @var string
     */
    public $category = 'Images';
    
    
    /**
     * set name of source (inputfield for user of this source)
     * false means that no source is necessary
     *
     * @var bool|string
     */
    public $source = 'Username';
    
    
    /**
     * description of this source type
     *
     * @var string
     */
    public $description = 'This feed fetches the gallery of a tumblr user';
    
    
    /**
     * returns the url for the opml export
     *
     * @return string the full feed url
     * @param string $url the url source (username, etc.)
     */
    public function opml($url) {
        return 'http://'.urlencode($url).'.tumblr.com/rss';
    }
    
    
    /**
     * loads content for given source
     *
     * @return void
     * @param string $url the source of the current feed
     */
    public function load($username) {
        parent::load($this->opml($username));
    }
}
