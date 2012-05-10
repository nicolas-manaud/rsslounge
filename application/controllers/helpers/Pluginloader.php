<?PHP

/**
 * Helper class for loading plugins (special plugins which
 * defines an source for this application)
 *
 * @package    application_controllers
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Helper_Pluginloader extends Zend_Controller_Action_Helper_Abstract {
    
    /**
     * array of available plugins
     *
     * @var bool|array
     */
    public $plugins = false;
    
    
    /**
     * returns all available plugins
     *
     * @return array available plugins 
     */
    public function getPlugins() {
        // get all plugins
        $this->readPlugins();
        
        // return all plugins
        return $this->plugins;
    }
    
    
    /**
     * returns a given plugin source object
     *
     * @return mixed|boolean an instance of the plugin, false if this plugin doesn't exist
     * @param string $plugin a given plugin source type
     */
    public function getPlugin($plugin) {
        // get all plugins
        $this->readPlugins();
        
        // return plugin object
        if(!array_key_exists($plugin, $this->plugins))
            return false;
        else
            return $this->plugins[$plugin];
    }
    
    
    
    //
    // private helpers
    //
    
    
    /**
     * reads all plugins
     *
     * @return void
     */
    protected function readPlugins() {
        if($this->plugins===false) {
            // load plugins
            $this->plugins = $this->loadClass(Zend_Registry::get('config')->plugins->path, 'rsslounge_source');
            
            // load additinal language files
            foreach($this->searchDir(Zend_Registry::get('config')->plugins->path, 'locale') as $plugin => $dir) {
                Zend_Registry::get('language')->addTranslation($dir, null, array('scan' => Zend_Translate::LOCALE_DIRECTORY, 'delimiter' => "|"));
            }
            Zend_Registry::get('language')->setLocale(new Zend_Locale(Zend_Registry::get('session')->language));
        }
    }
    
    
    /**
     * returns all classes which extends a given class
     *
     * @return array with classname (key) and an instance of a class (value)
     * @param string $pluginLocation the path where all plugins in
     * @param string $parentclass the parent class which files must extend
     */
    protected function loadClass($pluginLocation, $parentclass) {
        $return = array();
        
        foreach(scandir($pluginLocation) as $dir) {
            
            if(is_dir($pluginLocation . $dir) && substr($dir,0,1)!=".") {
                
                // search for plugins
                foreach(scandir($pluginLocation . "/" . $dir) as $file) {
                    // only scan visible .php files
                    if(is_file($pluginLocation . "/" . $dir . "/" . $file) && substr($file,0,1)!="." && strpos($file,".php")!==false) {
                        // create reflection class
                        $classname = str_replace(".php","",$file);
                        $class = new ReflectionClass("plugins_".$dir."_".$classname);
                        
                        // register widget
                        if($class->isSubclassOf(new ReflectionClass($parentclass)))
                            $return["plugins_".$dir."_".$classname] = $class->newInstance();
                    }
                }
                
            }
        }
        
        return $return;
    }
    
    
    /**
     * search for a given subdir
     * 
     * @return array of plugins (key) and subdirs (value)
     * @param string $pluginLocation the path where all plugins in
     * @param string $subdir the sub directory
     */
    protected function searchDir($pluginLocation, $subdir) {
        $return = array();
        
        foreach(scandir($pluginLocation) as $dir) {
            
            if(is_dir($pluginLocation . $dir) && substr($dir,0,1)!=".") {
                
                $currentSubDir = $pluginLocation . $dir . "/" . $subdir;
                if(file_exists($currentSubDir))
                    $return[$dir] = $currentSubDir;
                    
            }
        }
        
        return $return;
    }
}