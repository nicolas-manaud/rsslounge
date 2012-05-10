<?php

/**
 * Base bootstrap class for the whole application
 *
 * @package    application
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    
    /**
     * version
     *
     * @var string
     */
    public $version = "1.7";
    
    
    /**
     * database version
     *
     * @var string
     */
    public $dbversion = "2";
    
    
    /**
     * translate object
     *
     * @var Zend_Translate
     */
    protected $language;
    
    
    /**
     * session
     *
     * @var Zend_Session_Namespace
     */
    protected $session;
    
    
    /**
     * cache
     *
     * @var Zend_Cache_Core
     */
    protected $cache;
    
    
    /**
     * logger
     *
     * @var Zend_Log
     */
    protected $logger;
    
    
    /**
     * ctor saves the bootstrap object in Zend Registry
     * saves also the config
     */
    public function __construct($app) {
        parent::__construct($app);
        Zend_Registry::set('bootstrap',$this);
        Zend_Registry::set('config', new Zend_Config($this->getOptions(),true));
        Zend_Registry::set('version', $this->version);
    }
    
    
    /**
     * returns current database version
     *
     * @return string version
     */
    public function getCurrentVersion() {
        $db = $this->getPluginResource('db')->getDbAdapter();
        $p = Zend_Registry::get('config')->resources->db->prefix;
        return $db->fetchOne(
            $db->select()->from($p.'version', 'MIN(version)')
        );
    }
    
    
    /**
     * set current database version
     *
     * @return void
     * @param string val new version
     */
    public function updateCurrentVersion($val) {
        $db = $this->getPluginResource('db')->getDbAdapter();
        $p = Zend_Registry::get('config')->resources->db->prefix;
        $db->update($p.'version', array('version' => $val ) );
    }
    
    
    /**
     * returns current application version
     */
    public function getApplicationVersion() {
        return $this->dbversion;
    }
    
    
    /**
     * init additional routes
     *
     * @return void
     */
    protected function _initRoutes() {
        // get front controller instance
        $front = Zend_Controller_Front::getInstance();
         
        // get router
        $router = $front->getRouter();
         
        // add install.php route
        $router->addRoute(
            'install',
            new Zend_Controller_Router_Route(
                    'install.php',
                    array(
                        'module'     => 'default',
                        'controller' => 'index',
                        'action'     => 'index'
                    )
                )
        );
    }
    
    
    /**
     * init additional plugins
     *
     * @return void
     */
    protected function _initPlugins() {
        // get front controller instance
        $front = Zend_Controller_Front::getInstance();
        
        // create loader
        $loader = new Zend_Loader_PluginLoader();
        $loader->addPrefixPath('Plugin', Zend_Registry::get('config')->resources->frontController->pluginsDirectory);
        $pluginAuthentication = $loader->load('Authentication');
        
        // register plugin
        $front->registerPlugin(new $pluginAuthentication());
    }
    
    
    /**
     * init helper classes
     *
     * @return void
     */
    protected function _initHelper() {
        Zend_Controller_Action_HelperBroker::addPath(
            Zend_Registry::get('config')->helpers->path,
            'Helper'
        );
    }
    
    
    /**
     * create special autoloader for extern libraries
     *
     * @return void
     */
    protected function _initAutoloader() {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $loader = new rsslounge_autoloader();
        
        // register additional autoloader for wideimage library
        $autoloader->pushAutoloader($loader, 'WideImage');
        
        // autoloader for helper
        $this->resourceLoader = new Zend_Loader_Autoloader_Resource(array(
            'basePath'  => APPLICATION_PATH . '/controllers',
            'namespace' => ''
        ));
        $this->resourceLoader->addResourceType('helpers', 'helpers', 'Helper');
    }
    
    
    /**
     * initialize cache
     *
     * @return void
     */
    protected function _initCache() {
        $optionsBackend = array(
            'cache_dir'    => Zend_Registry::get('config')->cache->path,
            'file_locking' => true,
            'read_control' => false
        );
        $optionsFrontend = array(
            'caching'                 => Zend_Registry::get('config')->cache->enable=="1",
            'automatic_serialization' => true
        );
        
        $this->cache = Zend_Cache::factory('Core', 'File', $optionsFrontend, $optionsBackend);
        
        // set cache for all locale and translate
        Zend_Locale::setCache($this->cache);
        Zend_Translate::setCache($this->cache);
        
        // save cache object for further use
        Zend_Registry::set('cache',$this->cache);
        return $this->cache;
    }
    
    
    /**
     * initialize language file
     *
     * @return void
     */
    protected function _initLanguage() {
        // auto route all languages to english
        $dirs = array_filter(glob(APPLICATION_PATH . '/locale/*'), 'is_dir');
        $route = array();
        foreach($dirs as $dir)
            $route[ substr($dir, strlen($dir)-2,2) ] = 'en';
            
        $this->language = new Zend_Translate(
            'csv', 
            APPLICATION_PATH . '/locale', 
            'en', 
            array(
                'scan' => Zend_Translate::LOCALE_DIRECTORY, 
                'delimiter' => "|",
                'route'     => $route
            ));
        
        
        
        // save language object for further use
        Zend_Registry::set('language',$this->language);
        return $this->language;
    }
    
    
    /**
     * init logger
     *
     * @return void
     */
    protected function _initLogger() {
        $writer = new Zend_Log_Writer_Stream(Zend_Registry::get('config')->logger->path);
        $this->logger = new Zend_Log($writer);
        
        // set priority
        switch (strtoupper(Zend_Registry::get('config')->logger->level)) {
            case 'EMERG':
                $level = Zend_Log::EMERG;
                break;
            case 'ALERT':
                $level = Zend_Log::ALERT;
                break;
            case 'CRIT':
                $level = Zend_Log::CRIT;
                break;
            case 'ERR':
                $level = Zend_Log::ERR;
                break;
            case 'WARN':
                $level = Zend_Log::WARN;
                break;
            case 'NOTICE':
                $level = Zend_Log::NOTICE;
                break;
            case 'INFO':
                $level = Zend_Log::INFO;
                break;
            case 'DEBUG':
                $level = Zend_Log::DEBUG;
                break;
        }
        
        $this->logger->addFilter(
            new Zend_Log_Filter_Priority($level)
        );
        
        // save logger for further use
        Zend_Registry::set('logger', $this->logger);
        return $this->logger;
    }
    
    
    /**
     * reset session with default values
     * from config file
     *
     * @return void
     */
    public function resetSession($usedatabase=false) {
        // get session object
        $this->session = new Zend_Session_Namespace("base");
        
        // get default values
        $default = Zend_Registry::get('bootstrap')->getOptions();
        $default = $default["session"]["default"];
        
        // initialize values using values in database or default values
        if($usedatabase===false) {
            foreach ($default as $key => $value)
                $this->session->__set(
                    $key,
                    $value
                );
        } else {
            foreach ($default as $key => $value)
                $this->session->__set(
                    $key,
                    $this->initializeSessionValue($key, $value)
                );
        }
        
        // always load public value from database
        $this->session->__set(
            'public',
            $this->initializeSessionValue('public', 0)
        );
        
        // set language
        $this->language->setLocale(new Zend_Locale($this->session->language));
        
        // save session object for further use
        Zend_Registry::set('session',$this->session);
    }
    
    
    /**
     * initialize session
     *
     * @return void
     */
    protected function _initSession() {
        $this->resetSession(true);
        return $this->session;
    }
    
    
    /**
     * reads settings from database
     *
     * @return value for this setting
     * @param name the name of the setting
     * @param default the default value of this setting
     */
    protected function initializeSessionValue($name, $default) {
        // get database settings object
        $settings = new application_models_settings();
    
        // get value from database
        $result = $settings->fetchAll(
                            $settings->select()
                                     ->where('name=?',$name));
        
        // value found?
        if($result->count()>0) {
            return $result->current()->value;
        
        // value not found
        } else {
                
            // save default value in database
            $settings->insert(
                array(
                    'name'  => $name, 
                    'value' => $default
                )
            );
            
            // return default value
            return $default;
        }
    }
}

