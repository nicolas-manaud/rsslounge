<?PHP 

/**
 * Plugin for fetching an rss feed
 *
 * @package    plugins
 * @subpackage rss
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class plugins_rss_feedfix extends plugins_rss_feed {

    /**
     * description of this source type
     *
     * @var string
     */
    public $description = 'An default RSS Feed which works without date in feeds. Use this if items appear twice.';
    
    
    /**
     * returns an unique id for this item
     *
     * @return string id as hash
     */
    public function getId() {
        return md5($this->getTitle() . $this->getContent());
    }
    
}
