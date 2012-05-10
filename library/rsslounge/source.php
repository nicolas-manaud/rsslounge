<?PHP 

/**
 * This abstract class defines the interface of a source (plugin)
 * template pattern
 *
 * @package    library
 * @subpackage rsslounge
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class rsslounge_source implements Iterator {

    /**
     * url of the icon or false if no icon available
     *
     * @var bool|string
     */
    public $icon = false;

    
    /**
     * set name of source (inputfield for user of this source)
     * false means that no source is necessary
     *
     * @var bool|string
     */
    public $source = false;
    
    
    /**
     * source optional
     *
     * @var bool|string
     */
    public $sourceOptional = false;
    
    
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
    public $description = '';
    
    
    /**
     * true = multimedia feed
     * false = text messages
     *
     * @var bool
     */
    public $multimedia = false;
    
    
    
    
    //
    // Source Methods
    //
    
    /**
     * returns the url for the opml export
     *
     * @return string the full feed url
     * @param string $url the url source (username, etc.)
     */
    abstract public function opml($url);
    
    
    /**
     * loads content for given source
     *
     * @return void
     * @param string $url the source of the current feed
     */
    abstract public function load($url);
    
    
    /**
     * returns the global html url for the source
     *
     * @return string url as html
     */
    abstract public function getHtmlUrl();
    
    
    /**
     * returns an unique id for this item
     *
     * @return string id as hash
     */
    abstract public function getId();
    
    
    /**
     * returns the current title as string
     *
     * @return string title
     */
    abstract public function getTitle();
    
    
    /**
     * returns the content of this item
     *
     * @return string content
     */
    abstract public function getContent();
    
    
    /**
     * returns the link of this item
     *
     * @return string link
     */
    abstract public function getLink();
    
    
    /**
     * returns the date of this item
     *
     * @return string date
     */
    abstract public function getDate();
    
    
    /**
     * returns the thumbnail of this item (for multimedia feeds)
     *
     * @return mixed thumbnail data
     */
    abstract public function getThumbnail();
    
    
    /**
     * destroy the plugin (prevent memory issues)
     *
     * @return void
     */
    public function destroy() {
        
    }
}