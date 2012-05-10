<?PHP

/**
 * UnitTest for routing
 *
 * @package    tests_settings
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class RoutingTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request = null;

    /**
     * @var Zend_Controller_Router_Rewrite
     */
    protected $_router = null;

    /**
     * set request and router
     */
    function setUp() {
        $this->_request = new Zend_Controller_Request_Http();
        $this->_router = Zend_Controller_Front::getInstance()->getRouter();
    }
    
    
    /**
     * Test main page
     */
    public function testHomepage() {
        // set request and start routing
        $this->_request->setRequestUri('');
        $request = $this->_router->route($this->_request);
        
        // check result
        $this->assertEquals('default', $this->_router->getCurrentRouteName());
        $this->assertEquals('default', $request->getModuleName());
        $this->assertEquals('index', $request->getControllerName());
        $this->assertEquals('index', $request->getActionName());
    }
    

    /**
     * Test Controller
     */
    public function testController() {
        $controllers = array(
            'category',
            'error',
            'errormessages',
            'feed',
            'index',
            'item',
            'opml',
            'patch',
            'settings',
            'update'
        );
        
        foreach($controllers as $controller) {
            // set request and start routing
            $this->_request = new Zend_Controller_Request_Http();
            $this->_request->setRequestUri($controller);
            $request = $this->_router->route($this->_request);
            
            // check result
            $this->assertEquals('default', $this->_router->getCurrentRouteName());
            $this->assertEquals('default', $request->getModuleName());
            $this->assertEquals($controller, $request->getControllerName());
            $this->assertEquals('index', $request->getActionName());
        }
    }
    

    /**
     * Test Action
     */
    public function testAction() {
       $controllers = array(
            'category'          => array('index','save', 'open'),
            'error'             => array('error'),
            'errormessages'     => array('index'),
            'feed'              => array('sort','add','edit','save','delete'),
            'index'             => array('index','login','about','ie'),
            'item'              => array('list','listmore','mark','markall','star','unstarrall'),
            'opml'              => array('export','import'),
            'patch'             => array('index'),
            'settings'          => array('index','save'),
            'update'            => array('silent','finish','feed')
        );
        
        foreach($controllers as $controller => $actions) {
            foreach($actions as $action) {
                // set request and start routing
                $this->_request = new Zend_Controller_Request_Http();
                $this->_request->setRequestUri($controller.'/'.$action);
                $request = $this->_router->route($this->_request);
                
                // check result
                $this->assertEquals('default', $this->_router->getCurrentRouteName());
                $this->assertEquals('default', $request->getModuleName());
                $this->assertEquals($controller, $request->getControllerName());
                $this->assertEquals($action, $request->getActionName());
            }
        }
    }
    
    
    /**
     * Test own route
     */
    public function testInstall() {
        // set request and start routing
        $this->_request->setRequestUri('install.php');
        $request = $this->_router->route($this->_request);
        
        // check result
        $this->assertEquals('install', $this->_router->getCurrentRouteName());
        $this->assertEquals('default', $request->getModuleName());
        $this->assertEquals('index', $request->getControllerName());
        $this->assertEquals('index', $request->getActionName());
    }

}