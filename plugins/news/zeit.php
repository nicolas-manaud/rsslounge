<?PHP 

/**
 * Plugin for fetching the full text of zeit.de
 *
 * @package    plugins
 * @subpackage news
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class plugins_news_zeit extends plugins_rss_feed {

    /**
     * url of the icon or false if no icon available
     *
     * @var string
     */
    public $icon = 'plugins/news/zeit.ico';

        
    /**
     * category of this source type
     *
     * @var string
     */
    public $category = 'News';
    
    /**
     * set name of source (inputfield for user of this source)
     * false means that no source is necessary
     *
     * @var bool|string
     */
    public $source = false;
    
    
    /**
     * description of this source type
     *
     * @var string
     */
    public $description = 'This feed fetches the ftd news with full content (not only the header as content)';
    
    
    /**
     * returns the url for the opml export
     *
     * @return string the full feed url
     * @param string $url the url source (username, etc.)
     */    
    public function opml($url) {
        return 'http://newsfeed.zeit.de/index';
    }
    
    
    /**
     * loads content for given source
     *
     * @return void
     * @param string $url
     */
    public function load($url) {
        parent::load($this->opml(''));
    }
    
    
    /**
     * returns the content of this item
     *
     * @return string content
     */
    public function getContent() {
        if($this->items!==false && $this->valid()) {
        
            try {
                // load entry page
                $client = new Zend_Http_Client($this->getLink().'?page=all');
                $response = $client->request();  
                $content = $response->getBody();
            
                $content = utf8_decode($content);
                
                // parse content
                $dom = new Zend_Dom_Query($content); 
                $text = $dom->query('.article');
                
                $innerHTML = '';
                
                // convert innerHTML from DOM to string
                // taken from http://us2.php.net/domelement (patrick smith)
                $children = $text->current()->childNodes;
                foreach ($children as $child) {
                    $tmp_doc = new DOMDocument();
                    $tmp_doc->appendChild($tmp_doc->importNode($child,true));
                    
                    if(count($tmp_doc->getElementById('comments'))>0)
                        continue;
                    
                    // convert to text
                    $innerHTML .= @$tmp_doc->saveHTML();
                } 
                
                return $innerHTML;
            } catch(Exception $e) { // return default content
                return current($this->items)->get_content();
            }
            
        }
    }
    
}
