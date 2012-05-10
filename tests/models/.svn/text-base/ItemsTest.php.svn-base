<?PHP

/**
 * UnitTest for the items model class
 *
 * @package    tests_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class ItemsTest extends PHPUnit_Framework_TestCase {

    /**
     * @var application_models_feeds
     */
    protected $model;

    
    /**
     * @var Zend_Db
     */
    protected $db;
    
    
    /**
     * @var array
     */
    protected $settings;
    
    
    /**
     * prepare tests
     */
    public function setUp() {
        $this->model = new application_models_items();
        $this->db = $db = Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter();
        
        // insert test data
        $this->db->exec("INSERT INTO " . Zend_Registry::get('config')->resources->db->prefix . 'categories' . " (`id`,`name`,`position`)
                         VALUES 
                         (1 , 'category 1', '0'),
                         (2 , 'category 2', '1'),
                         (3 , 'category 3', '2');
                         
                         
                         INSERT INTO " . Zend_Registry::get('config')->resources->db->prefix . 'feeds' . " 
                         (`id`, `source`, `url`, `category`, `priority`, `favicon`, `filter`, `name`, `position`, `icon`, `multimedia`, `dirtyicon`, `htmlurl`, `lastrefresh`, `error`)
                         VALUES 
                         (1, 'plugins_rss_feed', 'http://www.n-tv.de/wirtschaft/rss', '2', '1', 'http://rsslounge.aditu.de/favicon.ico', NULL, 'n-tv', '0', '', '0', '1', '', NULL, '0'),
                         (2, 'plugins_rss_feed', 'http://newsfeed.zeit.de/', '1', '2', '', NULL, 'Zeit Online', '2', '', '1', '1', 'http://blog.aditu.de/', NULL, '0'),
                         (3, 'plugins_rss_feed', 'http://bcaptured.tumblr.co', '3', '2', '', NULL, 'bCaptured Tumblr', '3', '4711.ico', '1', '1', '', NULL, '0');
                         
                         
                         INSERT INTO " . Zend_Registry::get('config')->resources->db->prefix . 'items' . " 
                         (`id`, `title`, `content`, `feed`, `unread`, `starred`, `datetime`, `uid`, `link`) 
                         VALUES 
                         (1, 'testitem 1', 'testcontent blabla', '1', '1', '0', '2009-09-07 15:17:30', '4711', 'http://www.aditu.de'),
                         (2, 'testitem444', 'blabla', '2', '1', '0', '2009-09-02 15:40:30', '4711', 'http://rsslounge.aditu.de'),
                         (3, 'testitem909', 'thumb1.jpg', '3', '0', '1', '2009-09-01 15:47:30', '4711', 'http://aditu.de'),
                         (4, 'testitem2', 'blabla', '1', '1', '0', '2009-05-07 13:47:30', '4811', 'http://blog.aditu.de');
                         ");
                         
        // base settings for testing
        $this->settings = array(
            'language'                => 'de',
            'priorityStart'           => '1',
            'priorityEnd'             => '2',
            'deleteItems'             => '0',
            'saveOpenCategories'      => '1',
            'openCategories'          => '-1',
            'firstUnread'             => '1',
            'refresh'                 => '60',
            'lastrefresh'             => '1252413398',
            'timeout'                 => '0',
            'view'                    => 'both',
            'itemsperpage'            => '10',
            'imagesPosition'          => 'top',
            'imagesHeight'            => '1',
            'selected'                => '',
            'dateFilter'              => '0',
            'dateStart'               => '',
            'dateEnd'                 => '',
            'unread'                  => 0,
            'starred'                 => '0',
            'currentPriorityStart'    => '1',
            'currentPriorityEnd'      => '2',
            'sort'                    => 'date',
            'authenticated'           => true
        );
        
        
        // dummy thumbnails
        $handle = fopen (Zend_Registry::get('config')->thumbnails->path."thumb1.jpg", "w"); // in use
        $buffer = fwrite($handle, date("d.M.Y H:i:s") );
        fclose ($handle);
        
        $handle = fopen (Zend_Registry::get('config')->thumbnails->path."thumb2.jpg", "w"); // unused
        $buffer = fwrite($handle, date("d.M.Y H:i:s") );
        fclose ($handle);
    }
    
    
    /**
     * remove temp values
     */
    public function tearDown() {
        $this->db->exec("DELETE FROM " . Zend_Registry::get('config')->resources->db->prefix . 'categories');
        $this->db->exec("DELETE FROM " . Zend_Registry::get('config')->resources->db->prefix . 'feeds');
        $this->db->exec("DELETE FROM " . Zend_Registry::get('config')->resources->db->prefix . 'items');
        @unlink(Zend_Registry::get('config')->thumbnails->path."thumb1.jpg");
        @unlink(Zend_Registry::get('config')->thumbnails->path."thumb2.jpg");
    }
    
    
    /**
     * test configuration
     */
    public function testConfiguration() {
        $this->assertEquals($this->db, $this->model->getAdapter());
        
        $this->assertEquals(array(1 => 'id'), $this->model->info('primary'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->prefix . 'items', $this->model->info('name'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->params->dbname, $this->model->info('schema'));
        $referenceMap = $this->model->info('referenceMap');
        $expected = array(
                        'columns'       => 'feed',
                        'refTableClass' => 'application_models_feeds',
                        'refColumn'     => 'id'
                        );
        $this->assertEquals($expected, $referenceMap['feeds']);
    }
    
    
    /**
     * test loading items
     */
    public function testGet() {
        $result = $this->model->get($this->settings, 'both');
        $this->assertEquals(4, count($result));
        $this->assertEquals('1', $result[0]['id']);
        $this->assertEquals('2', $result[1]['id']);
        $this->assertEquals('3', $result[2]['id']);
        $this->assertEquals('4', $result[3]['id']);
        
        // only multimedia
        $result = $this->model->get($this->settings, 'multimedia');
        $this->assertEquals(2, count($result));
        $this->assertEquals('2', $result[0]['id']);
        $this->assertEquals('3', $result[1]['id']);
        
        // only messages
        $result = $this->model->get($this->settings, 'messages');
        $this->assertEquals(2, count($result));
        $this->assertEquals('1', $result[0]['id']);
        $this->assertEquals('4', $result[1]['id']);
        
        // category filter
        $result = $this->model->get(
            array_merge(
                $this->settings,
                array( 'selected' => 'cat_2' )
            ),
            'messages');
        $this->assertEquals(2, count($result));
        $this->assertEquals('1', $result[0]['id']);
        $this->assertEquals('4', $result[1]['id']);
        
        // feed filter
        $result = $this->model->get(
            array_merge(
                $this->settings,
                array( 'selected' => 'feed_2' )
            ),
            'multimedia');
        $this->assertEquals(1, count($result));
        $this->assertEquals('2', $result[0]['id']);
        
        // unread filter
        $result = $this->model->get(
            array_merge(
                $this->settings,
                array( 'unread' => 1 )
            ),
            'both');
        $this->assertEquals(3, count($result));
        $this->assertEquals('1', $result[0]['id']);
        $this->assertEquals('2', $result[1]['id']);
        $this->assertEquals('4', $result[2]['id']);
        
        // starred filter
        $result = $this->model->get(
            array_merge(
                $this->settings,
                array( 'starred' => 1 )
            ),
            'both');
        $this->assertEquals(1, count($result));
        $this->assertEquals('3', $result[0]['id']);
        
        // priority currentPriorityStart
        $result = $this->model->get(
            array_merge(
                $this->settings,
                array( 'currentPriorityStart' => 2 )
            ),
            'both');
        $this->assertEquals(2, count($result));
        $this->assertEquals('2', $result[0]['id']);
        $this->assertEquals('3', $result[1]['id']);
        
        // priority currentPriorityEnd
        $result = $this->model->get(
            array_merge(
                $this->settings,
                array( 'currentPriorityEnd' => 1 )
            ),
            'both');
        $this->assertEquals(2, count($result));
        $this->assertEquals('1', $result[0]['id']);
        $this->assertEquals('4', $result[1]['id']);
        
        // date filter
        $result = $this->model->get(
            array_merge(
                $this->settings,
                array( 
                    'dateFilter'     => 1,
                    'dateStart'  => '2009-08-30',
                    'dateEnd'    => '2009-09-03'
                )
            ),
            'both');
        $this->assertEquals(2, count($result));
        $this->assertEquals('2', $result[0]['id']);
        $this->assertEquals('3', $result[1]['id']);
        
        // search
        $result = $this->model->get(
            array_merge(
                $this->settings,
                array( 
                    'search' => 'testitem2'
                )
            ),
            'both');
        $this->assertEquals(1, count($result));
        $this->assertEquals('4', $result[0]['id']);
        
        // offset
        $result = $this->model->get(
            array_merge(
                $this->settings,
                array( 
                    'itemsperpage' => '2',
                    'offset' => '1'
                )
            ),
            'both');
        $this->assertEquals(2, count($result));
        $this->assertEquals('2', $result[0]['id']);
        $this->assertEquals('3', $result[1]['id']);
    }
    
    
    /**
     * test hasmore check
     */
    public function testHasMore() {
        $result = $this->model->hasMore(
            array_merge(
                $this->settings,
                array( 
                    'itemsperpage' => '2',
                    'offset' => '1'
                )
            ),
            'both');
        $this->assertTrue($result);
        
        $result = $this->model->hasMore(
            array_merge(
                $this->settings,
                array( 
                    'itemsperpage' => '2',
                    'offset' => '2'
                )
            ),
            'both');
        $this->assertFalse($result);
        
        $result = $this->model->hasMore(
            array_merge(
                $this->settings,
                array( 
                    'itemsperpage' => '2',
                    'offset' => '1'
                )
            ),
            'multimedia');
        $this->assertFalse($result);
    }
    
    
    /**
     * test count all items per category
     */
    public function testCountPerCategory() {
        $result = $this->model->countPerCategory($this->settings);
        $expected = array(
            0 => 4,
            1 => 1,
            2 => 2,
            3 => 1);
        $this->assertEquals($expected, $result);
        
        // only unread
        $result = $this->model->countPerCategory(
            array_merge(
                $this->settings,
                array( 
                    'unread' => 1
                )
            )
        );
        $expected = array(
            0 => 3,
            1 => 1,
            2 => 2);
        $this->assertEquals($expected, $result);
    }
    
    
    /**
     * test count all items per feed
     */
    public function testCountPerFeed() {
        $result = $this->model->countPerFeed($this->settings);
        $expected = array(
            1 => 2,
            2 => 1,
            3 => 1);
        $this->assertEquals($expected, $result);
        
        // only unread
        $result = $this->model->countPerFeed(
            array_merge(
                $this->settings,
                array( 
                    'unread' => 1
                )
            )
        );
        $expected = array(
            1 => 2,
            2 => 1);
        $this->assertEquals($expected, $result);
    }
    
    
    /**
     * test count items
     */
    public function testCount() {
        // count all
        $result = $this->model->countAll($this->settings);
        $this->assertEquals(4, $result);
        
        // count all unread
        $result = $this->model->countAll(
            array_merge(
                $this->settings,
                array( 
                    'unread' => 1
                )
            )
        );
        $this->assertEquals(3, $result);
        
        // count starred
        $result = $this->model->countStarred($this->settings);
        $this->assertEquals(1, $result);
        
    }
    
    
    /**
     * test thumbnail cleanup
     */
    public function testCleanupThumbnails() {
        $this->model->cleanupThumbnails();
        $this->assertTrue(file_exists(Zend_Registry::get('config')->thumbnails->path."thumb1.jpg"));
        $this->assertFalse(file_exists(Zend_Registry::get('config')->thumbnails->path."thumb2.jpg"));
    }
}
