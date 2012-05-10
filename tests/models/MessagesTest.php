<?PHP

/**
 * UnitTest for the messages model class
 *
 * @package    tests_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class MessagesTest extends PHPUnit_Framework_TestCase {

    /**
     * @var application_models_messages
     */
    protected $model;

    
    /**
     * @var Zend_Db
     */
    protected $db;
    
    
    /**
     * prepare tests
     */
    public function setUp() {
        $this->model = new application_models_messages();
        $this->db = $db = Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter();
        
        // insert test data
        $this->db->exec("INSERT INTO " . Zend_Registry::get('config')->resources->db->prefix . 'feeds' . " 
                         (`id`, `source`, `url`, `category`, `priority`, `favicon`, `filter`, `name`, `position`, `icon`, `multimedia`, `dirtyicon`, `htmlurl`, `lastrefresh`, `error`)
                         VALUES 
                         (1, 'plugins_rss_feed', 'http://www.n-tv.de/wirtschaft/rss', '1', '1', 'http://rsslounge.aditu.de/favicon.ico', NULL, 'n-tv', '0', '', '0', '1', '', NULL, '0'),
                         (2, 'plugins_rss_feed', 'http://newsfeed.zeit.de/', '1', '2', '', NULL, 'Zeit Online', '2', '', '1', '1', 'http://blog.aditu.de/', NULL, '0'),
                         (3, 'plugins_rss_feed', 'http://bcaptured.tumblr.co', '1', '2', '', NULL, 'bCaptured Tumblr', '3', '4711.ico', '1', '1', '', NULL, '0');");               
    }
    
    
    /**
     * remove temp values
     */
    public function tearDown() {
        $this->db->exec("DELETE FROM " . Zend_Registry::get('config')->resources->db->prefix . 'messages');
        $this->db->exec("DELETE FROM " . Zend_Registry::get('config')->resources->db->prefix . 'feeds');
    }
    
    
    /**
     * test configuration
     */
    public function testConfiguration() {
        $this->assertEquals($this->db, $this->model->getAdapter());
        $this->assertEquals(array(1 => 'id'), $this->model->info('primary'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->prefix . 'messages', $this->model->info('name'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->params->dbname, $this->model->info('schema'));
        $referenceMap = $this->model->info('referenceMap');
        $expected = array(
                        'columns'       => 'feed',
                        'refTableClass' => 'application_models_feeds',
                        'refColumn'     => 'id'
                        );
        $this->assertEquals($expected, $referenceMap['messages']);
    }
    
    
    /**
     * test add new message
     */
    public function testAdd() {
        $feedModel = new application_models_feeds();
        $currentDatetime = date('Y-m-d H:i:s');
        $this->model->add(
            $feedModel->find(1)->current(),
            'testmessage no 1'
        );
        
        // error in feed set
        $this->assertEquals('1', $feedModel->find(1)->current()->error);
        
        // correct error message added
        $this->assertGreaterThan(0, $this->model->fetchAll()->count());
        $result = $this->model->fetchAll(
            $this->model->select()
                        ->order('id DESC')
        )->current();
        
        $this->assertEquals($currentDatetime, $result->datetime);
        $this->assertEquals('testmessage no 1', $result->message);
        $this->assertEquals('1', $result->feed);
        
    }
}
