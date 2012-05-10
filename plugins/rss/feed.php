<?PHP 

/**
 * Plugin for fetching an rss feed
 *
 * @package    plugins
 * @subpackage rss
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class plugins_rss_feed extends rsslounge_source {

    /**
     * url of the icon or false if no icon available
     *
     * @var bool|string
     */
    public $icon = 'plugins/rss/icon.ico';

    
    /**
     * set name of source (inputfield for user of this source)
     * false means that no source is necessary
     *
     * @var bool|string
     */
    public $source = 'Source';
    
    
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
    public $description = 'An default RSS Feed as source';
    
    
    /**
     * true = multimedia feed
     * false = text messages
     *
     * @var bool
     */
    public $multimedia = false;
    
    
    /**
     * current fetched items
     *
     * @var array|bool
     */
    protected $items = false;
    
    
    /**
     * current html url
     *
     * @var string
     */
    protected $url = '';
    
    
    
    //
    // Iterator Interface
    //
    
    /**
     * reset iterator
     *
     * @return void
     */
    public function rewind() {
        if($this->items!==false)
            reset($this->items);
    }

    
    /**
     * receive current item
     *
     * @return SimplePie_Item current item
     */
    public function current() {
        if($this->items!==false)
            return $this;
    }

    
    /**
     * receive key of current item
     *
     * @return mixed key of current item
     */
    public function key() {
        if($this->items!==false)
            return key($this->items);
    }

    
    /**
     * select next item
     *
     * @return SimplePie_Item next item
     */
    public function next() {
        if($this->items!==false)
            next($this->items);
        return $this;
    }

    
    /**
     * end reached
     *
     * @return bool false if end reached
     */
    public function valid() {
        if($this->items!==false)
            return current($this->items) !== false;
        else
            return false;
    }
    
    
    
    //
    // Source Methods
    //
    
    /**
     * returns the url for the opml export
     *
     * @return string the full feed url
     * @param string $url the url source (username, etc.)
     */
    public function opml($url) {
        return $url;
    }
    
    
    /**
     * loads content for given source
     * I supress all Warnings of SimplePie for ensuring
     * working plugin in PHP Strict mode
     *
     * @return void
     * @param string $url the source of the current feed
     */
    public function load($url) {
        // initialize simplepie feed loader
        $this->feed = @new SimplePie();
        @$this->feed->set_cache_location(Zend_Registry::get('config')->rss->cache->path);
        @$this->feed->set_cache_duration(Zend_Registry::get('config')->rss->cache->timeout);
        @$this->feed->set_feed_url(htmlspecialchars_decode($url));
        @$this->feed->force_feed(true);
        
        // fetch items
        @$this->feed->init();
        
        // check for error
        if(@$this->feed->error()) {
            Zend_Registry::get('logger')->log('plugins_rss_feed: feed fetch error - ' . $this->feed->error(), Zend_Log::ERR);
            throw new Exception($this->feed->error());
        } else {
            // save fetched items
            $this->items = @$this->feed->get_items();
            Zend_Registry::get('logger')->log('plugins_rss_feed: '.count($this->items).' items fetched', Zend_Log::DEBUG);
        }
        
        // return html url
        $this->htmlUrl = @$this->feed->get_link();
    }
    
    
    /**
     * returns the global html url for the source
     *
     * @return string url as html
     */
    public function getHtmlUrl() {
        if(isset($this->htmlUrl))
            return $this->htmlUrl;
    }
    
    
    /**
     * returns an unique id for this item
     *
     * @return string id as hash
     */
    public function getId() {
        if($this->items!==false && $this->valid())
            return @current($this->items)->get_id();
    }
    
    
    /**
     * returns the current title as string
     *
     * @return string title
     */
    public function getTitle() {
        if($this->items!==false && $this->valid())
            return @current($this->items)->get_title();
    }
    
    
    /**
     * returns the content of this item
     *
     * @return string content
     */
    public function getContent() {
        if($this->items!==false && $this->valid())
            return @current($this->items)->get_content();
    }
    
    
    /**
     * returns the link of this item
     *
     * @return string link
     */
    public function getLink() {
        if($this->items!==false && $this->valid())
            return @current($this->items)->get_link();
    }
    
    
    /**
     * returns the date of this item
     *
     * @return string date
     */
    public function getDate() {
        if($this->items!==false && $this->valid())
            $date = @current($this->items)->get_date('Y-m-d H:i:s');
        if(strlen($date)==0)
            $date = date('Y-m-d H:i:s');
        return $date;
    }
    
    
    /**
     * returns the thumbnail of this item (for multimedia feeds)
     *
     * @return mixed thumbnail data
     */
    public function getThumbnail() {
        
    }
    
    
    /**
     * destroy the plugin (prevent memory issues)
     */
    public function destroy() {
        $this->feed->__destruct();
    }
}
