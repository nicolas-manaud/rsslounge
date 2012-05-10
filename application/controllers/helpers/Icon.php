<?PHP

/**
 * Helper class for fetching and saving an icon
 *
 * @package    application_controllers
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Helper_Icon extends Zend_Controller_Action_Helper_Abstract {

    protected $iconPositions = false;

    /**
     * generate one big image containing all icons
     * instead of loading hundreds of icons, just
     * one file is necessary
     *
     * @return string target path
     */
    public function generateIconImage() {
        if(Zend_Registry::get('config')->cache->enable!=1 || Zend_Registry::get('config')->cache->iconcaching!=1)
            return "";
        
        $target = Zend_Registry::get('config')->favicons->path . Zend_Registry::get('config')->cache->icons;
        if(file_exists($target))
            return $target;
    
        // get all feeds
        $feedModel = new application_models_feeds();
        $feeds = $feedModel->fetchAll( $feedModel->select()->order('id ASC') );
        
        // generate big feed image
        $bigIconImage = imagecreatetruecolor(16,16*$feeds->count());
        imagealphablending($bigIconImage,false);
        imagesavealpha($bigIconImage,true);
        
        // insert icons into feed image
        $count = 0;
        // $reporting = error_reporting();
        // error_reporting(0);
        foreach($feeds as $feed) {
            
            $icon = false;
            
            // load icon file
            if(file_exists(Zend_Registry::get('config')->favicons->path . $feed->icon)) {
                $icon = $this->loadIconFileFromFilesystem(Zend_Registry::get('config')->favicons->path . $feed->icon);
                    
            // no icon file? load default icon
            } else if(strpos($feed->icon,'plugins')!==false) {
                $lastslash = strrpos($feed->icon, '/');
                $file = substr($feed->icon, 0, $lastslash) . '/public' . substr($feed->icon, $lastslash);
                $icon = $this->loadIconFileFromFilesystem(APPLICATION_PATH.'/../'.$file);
            }
            
            // no default icon: create empty image
            if($icon===false)
                $icon = $this->loadIconFileFromFilesystem(APPLICATION_PATH.'/../plugins/rss/public/icon.ico');
            
            // resize and copy
            imagecopyresampled($bigIconImage, $icon, 0, $count*16, 0, 0, 16, 16, imagesx($icon), imagesy($icon));
            
            // merge
            imagedestroy($icon);
            
            $count++;
        }
        
        // error_reporting($reporting);    
        
        //header('Content-type: image/png');
        imagepng($bigIconImage, $target);
        return $target;
    }
    
    
    /**
     * renew icon image
     *
     * @return string target path
     */
    public function resetIconImage() {
        $target = Zend_Registry::get('config')->favicons->path . Zend_Registry::get('config')->cache->icons;
        if(file_exists($target))
            unlink($target);
        return $this->generateIconImage();
    }
    
    /**
     * return feed positions
     *
     * @return array of feed positions
     */
    public function getFeedsIconPosition() {
        if($this->iconPositions == false) {
            $feedsModel = new application_models_feeds();
            $feeds = $feedsModel->fetchAll( $feedsModel->select()->order('id ASC') );
            $feedPositions = array();
            $count = 0;
            foreach($feeds as $feed)
                $feedPositions[$feed->id] = $count++;
            $this->iconPositions = $feedPositions;
        }
        return $this->iconPositions;
    }
    
    
    /**
     * loads icon using given url and stores it in a given path
     *
     * -> first search on given url (for <link rel="... tag)
     * -> then on domain url (for <link rel="... tag)
     * -> then favicon.ico file
     *
     * @return string|bool the filename of the new generated file, false if no icon was found
     * @param string $url source url
     * @param string $path target path
     */
    public function load($url, $path) {
        // search on given url
        $result = $this->searchAndDownloadIcon($url, $path);
        if($result!==false)
            return $result;
            
        // search on base page for <link rel="shortcut icon" url...
        $url = parse_url($url);
        $url = $url['scheme'] . '://'.$url['host'] . '/';
        $result = $this->searchAndDownloadIcon($url, $path);
        if($result!==false)
            return $result;
        
        // search domain/favicon.ico
        if(@file_get_contents($url . 'favicon.ico')!==false)
            return $this->loadIconFile($url . 'favicon.ico', $path);
        
        return false;
    }
    
    
    /**
     * downloads an icon file from given url in given path
     *
     * @return string|bool filename of the new icon, false on failure
     * @param string $url the url of the icon
     * @param string $path the target path
     */    
    public function loadIconFile($url, $path) {
        // get icon from source
        $data = @file_get_contents($url);
        if($data===false)
            return $data;
        
        // html text (e.g. error page) delivered
        if(strpos($data, '<html')!==false)
            return false;
        
        // empty file
        if(strlen($data)==0)
            return false;
        
        // get filetype
        $type = strtolower(substr($url, strrpos($url, '.')+1));
        if($type!='jpg' && $type!='png' && $type!='ico' && $type!='gif') {
            $tmp = $path . md5($url);
            file_put_contents($tmp, $data);
            $imgInfo = @getimagesize($tmp); 
            unlink($tmp);
            if(strtolower($imgInfo['mime'])=='image/vnd.microsoft.icon')
                $type = 'ico';
            elseif(strtolower($imgInfo['mime'])=='image/png')
                $type = 'png';
            elseif(strtolower($imgInfo['mime'])=='image/jpeg')
                $type = 'jpg';
            elseif(strtolower($imgInfo['mime'])=='image/gif')
                $type = 'gif';
            elseif($imgInfo == false){
                $icoDir = unpack('sidReserved/sidType/sidCount', substr($data, 0, 6));
                // http://msdn.microsoft.com/en-us/library/ms997538.aspx#CodeSnippetContainerCode0
                // as descripted in comments
                if ($icoDir['idReserved']!=0 || $icoDir['idType']!=1 || $icoDir['idCount']<1) return false;
            } else {
                // do not store other formats
                return false;
            }

        }
        
        // write icon in file
        $target = md5($url) . '.' . $type;
        file_put_contents($path . $target, $data);
        
        return $target;
    }
    
    
    /**
     * loads an html file and search for <link rel="shortcut icon"
     * on success: download
     *
     * @return string|bool filename on succes, false on failure
     * @param string $url source url
     * @param string $path target path
     */
    protected function searchAndDownloadIcon($url, $path) {
        $icon = $this->getLinkTag(
            $this->loadHtml($url)
        );
        
        // icon found: download it
        if($icon!==false) {
            // add http
            if(strpos($icon, 'http://') !== 0)
                $icon = $url . $icon;
            
            // download icon
            return $this->loadIconFile($icon, $path);
        }
        
        return false;
    }
    
    
    /**
     * loads html page of given url
     *
     * @return string content as string
     * @param string $url the source url
     */
    protected function loadHtml($url) {
        try {
            $client = new Zend_Http_Client($url);
            $response = $client->request();  
            return $response->getBody();
        } catch(Exception $e) {
            return false;
        }
    }
    
    
    /**
     * searches the first link tag in html page
     * with rel="shortcut icon" tag
     *
     * @return string|bool icon href as string, 
     *         false if no link tag was found
     * @param string $content of the html page
     */
    protected function getLinkTag($content) {
        if($content===false)
            return false;            
        try {
            $dom = @new Zend_Dom_Query($content);
            //$linkTags = $dom->query('link[rel="shortcut icon"]'); // don't work
            $linkTags = @$dom->query('link');
            foreach($linkTags as $link) {
                if($link->getAttribute('rel') == 'shortcut icon')
                    return $linkTags->current()->getAttribute("href");    
            }
        } catch(Exception $e) {
        
        }
        
        return false;
    }
    
    
    /**
     * load local icon file from filesystem as wideimage
     * @return WideImage_TrueColorImage
     * @param string $source of the ico file
     */
    protected function loadIconFileFromFilesystem($source) {
        $fileContent = file_get_contents($source);
        $tmp_image = @imagecreatefromstring($fileContent);
        
        if($tmp_image!==false)
            return $tmp_image;
        
        $ico = new floIcon();
        $ico->readICO($source);
        if(count($ico->images)>0)
            return $ico->images[0]->getImageResource();
    
        return false;
        
    }
}
