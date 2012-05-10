<?PHP

/**
 * UnitTest for the icon action helper class
 *
 * @package    tests_controllers
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class IconTest extends PHPUnit_Framework_TestCase {

    /**
     * prepare tests
     */
    public function setUp() {
        
    }
    
    
    /**
     * remove temp values
     */
    public function tearDown() {
       @unlink(Zend_Registry::get('config')->favicons->path . 'test1.ico');
    }
    
    
    /**
     * test load icon
     */
    public function testLoad() {
        $iconLoader = Zend_Controller_Action_HelperBroker::getStaticHelper('icon');
        
        // load by link tag
        $iconLoader->load(
            'http://www.n-tv.de/wirtschaft/', 
            Zend_Registry::get('config')->favicons->path . 'test1.ico');
            
        // load by link tag in base
        
        // load by file favicon.ico
    }
    
    
    /**
     * test load iconfile
     */
    public function testLoadIconFile() {
        
    }

}