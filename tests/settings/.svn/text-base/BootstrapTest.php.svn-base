<?PHP

/**
 * UnitTest for the base settings and bootstrap process
 *
 * @package    tests_settings
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class BootstrapTest extends PHPUnit_Framework_TestCase {

    /**
     * test the framework
     */
    public function testFramework() {
        $this->assertEquals('1.11.2', Zend_Version::VERSION);
    }
    
    
    /**
     * test the main configuration and settings
     */
    public function testConfiguration() {
        
        //
        // config
        //
        
        $config = Zend_Registry::get('config');
        $this->assertEquals('Zend_Config', get_class($config));
        
        // test config example values
        $this->assertEquals($config->phpSettings->display_startup_errors, 1);
        $this->assertEquals($config->phpSettings->display_errors, 1);
        $this->assertEquals($config->resources->frontController->throwExceptions, 1);
        
        
        //
        // database
        //
        
        $db = Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter();
        $this->assertEquals('Zend_Db_Adapter_Pdo_Mysql', get_class($db));
        
        $dbConfig = $db->getConfig();
        foreach($config->resources->db as $option => $value)
            if(isset($dbConfig[$option]))
                $this->assertEquals($value, $dbConfig[$option]);
        
        $this->assertEquals($db, Zend_Db_Table_Abstract::getDefaultAdapter());
        
        
        //
        // front controller
        //
        
        $front = Zend_Controller_Front::getInstance();
        $this->assertEquals(
            array( 'default' => $config->resources->frontController->controllerDirectory),
            $front->getControllerDirectory()
        );
        
        
        //
        // plugins
        //
        
        $expected = array(
            'Plugin_Authentication'
        );
        $plugins = array();
        foreach($front->getPlugins() as $plugin)
            $plugins[] = get_class($plugin);
        $this->assertEquals($expected, $plugins);
        
    }
    
    
    /**
     * test action helper loading
     */
    public function testActionHelper() {
        $expected = array(
            'Zend_Controller_Action_Helper_' => array('Zend/Controller/Action/Helper/'),
            'Helper_' => array(Zend_Registry::get('config')->helpers->path . "/")
        );
        $this->assertEquals($expected, Zend_Controller_Action_HelperBroker::getPluginLoader()->getPaths());
        
        $this->assertEquals(
            'Helper_List',
            get_class(Zend_Controller_Action_HelperBroker::getStaticHelper('list'))
        );
        $this->assertEquals(
            'Helper_Icon',
            get_class(Zend_Controller_Action_HelperBroker::getStaticHelper('icon'))
        );
        $this->assertEquals(
            'Helper_Itemcounter',
            get_class(Zend_Controller_Action_HelperBroker::getStaticHelper('itemcounter'))
        );
        $this->assertEquals(
            'Helper_Pluginloader',
            get_class(Zend_Controller_Action_HelperBroker::getStaticHelper('pluginloader'))
        );
        $this->assertEquals(
            'Helper_Updater',
            get_class(Zend_Controller_Action_HelperBroker::getStaticHelper('updater'))
        );        
    }
    
    
    /**
     * test language object (localization)
     */
    public function testLanguage() {
        $lang = Zend_Registry::get('language');
        $this->assertEquals('Zend_Translate',get_class($lang));
        $lang->setLocale(new Zend_Locale('de'));
        $this->assertEquals('Datenbankfehler',$lang->translate('database error'));
    }
    

    /**
     * test cache object settings
     */
    public function testCache() {
        $cache = Zend_Registry::get('cache');
        $this->assertEquals('Zend_Cache_Core',get_class($cache));
        
        $this->assertEquals($cache, Zend_Locale::getCache());
        $this->assertEquals($cache, Zend_Translate::getCache());
        
        $this->assertEquals('Zend_Cache_Backend_File', get_class($cache->getBackend()));
    }
    
    
    /**
     * test logger
     */
    public function testLogger() {
        $logger = Zend_Registry::get('logger');
        $this->assertEquals('Zend_Log',get_class($logger));
        
        $level = strtoupper(Zend_Registry::get('config')->logger->level);
        $mock = new Zend_Log_Writer_Mock();
        $logger->addWriter($mock);
        
        $this->assertEquals('ERR', strtoupper(Zend_Registry::get('config')->logger->level));
        $logger->log('test1', Zend_Log::ERR);
        $this->assertEquals(1, count($mock->events));
        $entry = $mock->events[0];
        $this->assertEquals($entry['message'], 'test1');
        $logger->log('test2', Zend_Log::CRIT);
        $this->assertEquals(2, count($mock->events));
        $entry = $mock->events[1];
        $this->assertEquals($entry['message'], 'test2');
        $logger->log('test3', Zend_Log::INFO);
        $this->assertEquals(2, count($mock->events));
    }
    
    
    /**
     * test session
     */
    public function testSession() {
        $session = Zend_Registry::get('session');
        $this->assertEquals('Zend_Session_Namespace', get_class($session));
        
        // default value by config
        $this->assertEquals(Zend_Registry::get('config')->session->default->deleteItems, $session->deleteItems);
        
        // value from database
        $this->assertEquals('100', $session->refresh);
    }
    
    
    /**
     * test the bootstrap class methods
     */
    public function testBootstrapclass() {
        $application = Zend_Registry::get('bootstrap');
        $this->assertEquals('Bootstrap', get_class($application));
        
        // application version get
        $applicationVersion = $application->getApplicationVersion();
        $this->assertEquals($application->dbversion, $applicationVersion);
        
        // db version set and get
        $currentVersion = $application->getCurrentVersion();
        $application->updateCurrentVersion(55928);
        $this->assertEquals(55928,$application->getCurrentVersion());
        $application->updateCurrentVersion($currentVersion);
    }
    
    
    /**
     * test the autoloader
     */
    public function testAutoloader() {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        
        // available autoloaders
        $expected = array(
            'Zend_Loader_Autoloader_Resource',
            'rsslounge_autoloader'
        );
        $autoloaders = array();
        foreach($autoloader->getAutoloaders() as $al)
            $autoloaders[] = get_class($al);
        $this->assertEquals($expected, $autoloaders);
        
        // check namespaces
        $expected = array(
            'Zend_',
            'ZendX_',
            'application',
            'SimplePie',
            'plugins',
            'rsslounge'
        );
        $this->assertEquals($expected, $autoloader->getRegisteredNamespaces());
        
        // load classes
        $this->assertTrue($autoloader->autoload('application_models_base'));
        $this->assertTrue(@$autoloader->autoload('SimplePie')); // SimplePie throws E_STRICT warnings
        $this->assertTrue($autoloader->autoload('plugins_rss_feed'));
        $this->assertTrue($autoloader->autoload('rsslounge_source'));
    }
    
}