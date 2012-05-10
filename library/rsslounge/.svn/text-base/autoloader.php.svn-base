<?PHP

/**
 * Special autoloader for extern libraries (currently only wideimage)
 *
 * @package    library
 * @subpackage rsslounge
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class rsslounge_autoloader implements Zend_Loader_Autoloader_Interface {
    
    /**
     * special autoloader for extern libraries
     *
     * @return void
     * @param string $class current classname
     */
    public function autoload($class) {
        if($class=='WideImage')
            require_once(Zend_Registry::get('config')->includePaths->library . '/WideImage/WideImage.php');
    }
    
}