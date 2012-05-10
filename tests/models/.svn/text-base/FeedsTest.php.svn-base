<?PHP

/**
 * UnitTest for the feed model class
 *
 * @package    tests_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class FeedsTest extends PHPUnit_Framework_TestCase {

    /**
     * @var application_models_feeds
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
        $this->model = new application_models_feeds();
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
                         (1, 'plugins_rss_feed', 'http://www.n-tv.de/wirtschaft/rss', '1', '1', 'http://rsslounge.aditu.de/favicon.ico', NULL, 'n-tv', '0', '', '0', '1', '', NULL, '0'),
                         (2, 'plugins_rss_feed', 'http://newsfeed.zeit.de/', '1', '2', '', NULL, 'Zeit Online', '2', '', '1', '1', 'http://blog.aditu.de/', NULL, '0'),
                         (3, 'plugins_rss_feed', 'http://bcaptured.tumblr.co', '1', '2', '', NULL, 'bCaptured Tumblr', '3', '4711.ico', '1', '1', '', NULL, '0');
                         
                         
                         INSERT INTO " . Zend_Registry::get('config')->resources->db->prefix . 'items' . " 
                         (`id`, `title`, `content`, `feed`, `unread`, `starred`, `datetime`, `uid`, `link`) 
                         VALUES 
                         (NULL, 'testitem 1', 'testcontent blabla', '1', '1', '0', '2009-09-07 15:47:30', '4711', 'http://www.aditu.de');
                         
                         
                         INSERT INTO " . Zend_Registry::get('config')->resources->db->prefix . 'messages' . " 
                         (`id`, `feed`, `datetime`, `message`) 
                         VALUES 
                         (NULL, '3', '2009-09-09 16:27:39', 'Fehler 1'),
                         (NULL, '3', '2009-09-07 16:27:48', 'Bla Fehler');
                         ");
                         
        $handle = fopen (Zend_Registry::get('config')->favicons->path."4711.ico", "w");
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
        $this->db->exec("DELETE FROM " . Zend_Registry::get('config')->resources->db->prefix . 'messages');
        @unlink(Zend_Registry::get('config')->favicons->path."4711.ico");
        @unlink(Zend_Registry::get('config')->favicons->path . md5('http://rsslounge.aditu.de/favicon.ico') . '.ico');
        @unlink(Zend_Registry::get('config')->favicons->path . md5('http://blog.aditu.de/favicon.ico') . '.ico');
    }
    
    
    /**
     * test configuration
     */
    public function testConfiguration() {
        $this->assertEquals($this->db, $this->model->getAdapter());
        
        $this->assertEquals(array(1 => 'id'), $this->model->info('primary'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->prefix . 'feeds', $this->model->info('name'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->params->dbname, $this->model->info('schema'));
        $referenceMap = $this->model->info('referenceMap');
        $expected = array(
                        'columns'       => 'category',
                        'refTableClass' => 'application_models_categories',
                        'refColumn'     => 'id'
                        );
        $this->assertEquals($expected, $referenceMap['categories']);
    }

    
    /**
     * test max position
     * feeds has wrong positions by setUp
     */
    public function testSort() {
        $categoryModel = new application_models_categories();
        $this->model->sort($categoryModel->find('2')->current(),array('2','1'));
        
        $this->assertEquals(1, $this->model->find('1')->count());
        $this->assertEquals(1, $this->model->find('2')->count());
        $this->assertEquals(1, $this->model->find('3')->count());
        
        $this->assertEquals('0', $this->model->find('2')->current()->position); // correct position
        $this->assertEquals('2', $this->model->find('2')->current()->category); // corrent category
        $this->assertEquals('1', $this->model->find('1')->current()->position); // correct position
        $this->assertEquals('2', $this->model->find('1')->current()->category); // corrent category
        
        $this->assertEquals('0', $this->model->find('3')->current()->position); // correct position
        $this->assertEquals('1', $this->model->find('3')->current()->category); // corrent category
    }
    
    
    /**
     * test counting feeds
     */
    public function testCount() {
        $this->assertEquals(3, $this->model->count(1,2));
        $this->assertEquals(2, $this->model->count(1,2,'multimedia'));
        $this->assertEquals(1, $this->model->count(1,2,'messages'));
        $this->assertEquals(1, $this->model->count(1,1));
        $this->assertEquals(2, $this->model->count(2,2));
    }
    
    
    /**
     * test reading min and max priority 
     */
    public function testPriority() {
        $this->assertEquals(1, $this->model->minPriority());
        $this->assertEquals(2, $this->model->maxPriority());
    }
    
    
    /**
     * test add new feed
     */
    public function testAdd() {
        $newfeed = array(
            'name'          => 'Tobis Blog',
            'url'           => 'http://blog.aditu.de/feed',
            'category'      => '1',
            'priority'      => '3',
            'favicon'       => '',
            'filter'        => '',
            'source'        => 'plugins_rss_feed'
        );
        
        $id = $this->model->add($newfeed);
        
        $this->assertTrue(is_numeric($id));
        $this->assertGreaterThan(0,$id);
        
        $newfeedEntry = $this->model->find($id);
        $this->assertEquals(1,$newfeedEntry->count());
        
        $newfeedEntry = $newfeedEntry->current()->toArray();
        $expected = array_merge(
            $newfeed,
            array(
                'icon'          => 'plugins/rss/icon.ico',
                'position'      => '3',
                'multimedia'    => '0',
                'id'            => $id,
                'dirtyicon'     => '1',
                'htmlurl'       => '',
                'lastrefresh'   => null,
                'error'         => '0'
            )
        );
        
        $this->assertEquals($expected, $newfeedEntry);
    }
    
    
    /**
     * test edit feed
     */
    public function testEdit() {
        $editfeed = array(
            'id'            => '1',
            'name'          => 'n-tv all news',
            'url'           => 'http://www.n-tv.de/rss',
            'category'      => '2',
            'priority'      => '1',
            'favicon'       => '467321.ico',
            'filter'        => '/.*4711.*/',
            'source'        => 'plugins_rss_multimedia'
        );
        
        $id = $this->model->edit($editfeed);
        
        $this->assertTrue(is_numeric($id));
        $this->assertGreaterThan(0,$id);
        
        $newEntry = $this->model->find($id);
        $this->assertEquals(1,$newEntry->count());
        
        $newEntry = $newEntry->current()->toArray();
        $expected = array_merge(
            $editfeed,
            array(
                'icon'          => 'plugins/rss/multimedia.ico',
                'position'      => '0',
                'multimedia'    => '1',
                'id'            => $id,
                'dirtyicon'     => '1',
                'htmlurl'       => '',
                'lastrefresh'   => null,
                'error'         => '0'
            )
        );
        $this->assertEquals($expected, $newEntry);
        
        // check that items was deleted because of the source change
        $itemModel = new application_models_items();
        $result = $itemModel->fetchAll(
            $itemModel->select()->where('feed=?', $id)
        );
        $this->assertEquals(0, $result->count());
    }
    
    
    /**
     * test remove a feed
     */
    public function testRemove() {
        $this->model->remove(3);
        
        // feed really deleted
        $this->assertEquals(0, $this->model->find(3)->count());
        
        // all items deleted
        $itemModel = new application_models_items();
        $result = $itemModel->fetchAll(
            $itemModel->select()->where('feed=?', 3)
        );
        $this->assertEquals(0, $result->count());
        
        // no icon file
        $this->assertFalse(file_exists(Zend_Registry::get('config')->favicons->path."4711.ico"));
        
        // no messages
        $messagesModel = new application_models_messages();
        $result = $messagesModel->fetchAll(
            $messagesModel->select()->where('feed=?', 3)
        );
        $this->assertEquals(0, $result->count());
        
        // check reorder
        $this->assertEquals('0', $this->model->find('1')->current()->position); // correct position
        $this->assertEquals('1', $this->model->find('2')->current()->position); // correct position
        
        // validation
        $this->assertTrue($this->model->remove(222)!==true);
    }
    
    
    /**
     * test handling of icons
     */
    public function testIconhandling() {
        // load icon by favicon value
        $this->model->saveIcon( $this->model->find(1)->current() );
        $this->assertEquals($this->model->find(1)->current()->icon, md5('http://rsslounge.aditu.de/favicon.ico') . '.ico');
        
        // load icon by htmlurl page
        $this->model->saveIcon( $this->model->find(2)->current() );
        $this->assertEquals($this->model->find(2)->current()->icon, md5('http://blog.aditu.de/wp-content/themes/blog.aditu.de/favicon.ico') . '.ico');
        
        // use plugin icon (invalid url, no htmlurl)
        $this->model->saveIcon( $this->model->find(3)->current() );
        $this->assertEquals($this->model->find(3)->current()->icon, 'plugins/rss/icon.ico');
        
        // delete icon
        $this->model->deleteIcon( $this->model->find(1)->current() );
        $this->assertFalse(file_exists(Zend_Registry::get('config')->favicons->path . md5('http://rsslounge.aditu.de/favicon.ico') . '.ico'));
    }
    
    
    /**
     * test validation
     */
    public function testValidation() {
        $newfeed = array(
            'name'          => 'Tobis Blog',
            'url'           => 'http://blog.aditu.de/feed',
            'category'      => '1',
            'priority'      => '3',
            'favicon'       => '',
            'filter'        => '',
            'source'        => 'plugins_rss_feed'
        );
        
        // invalid name
        $testfeed = array_merge( $newfeed, array( 'name' => '' ) );
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        $testfeed = $newfeed;
        unset($testfeed['name']);
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        // invalid url
        $testfeed = array_merge( $newfeed, array( 'url' => 'http://www.n-tv.de/wirtschaft/rss' ) );
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
            // url optional => no error
        $testfeed = array_merge( $newfeed, array( 'source' => 'plugins_images_visualizeus', 'url' => '' ) );
        $id = $this->model->add($testfeed);
        $this->assertTrue(is_numeric( $id ));
        $this->assertGreaterThan(0, $id);
        $this->model->remove($id);
        
            // url not optional => error
        $testfeed = array_merge( $newfeed, array( 'url' => '' ) );
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        $testfeed = $newfeed;
        unset($testfeed['url']);
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        // invalid category
        $testfeed = array_merge( $newfeed, array( 'category' => 'abc' ) );
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        $testfeed = array_merge( $newfeed, array( 'category' => '222' ) );
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        // invalid priority
        $testfeed = array_merge( $newfeed, array( 'priority' => 'abc' ) );
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        $testfeed = $newfeed;
        unset($testfeed['priority']);
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        // invalid source
        $testfeed = $newfeed;
        unset($testfeed['source']);
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        $testfeed = array_merge( $newfeed, array( 'source' => 'abc' ) );
        $this->assertTrue(is_array( $this->model->add($testfeed) ));
        
        // validation of id
        $editfeed = array(
            'id'            => '1111',
            'name'          => 'n-tv all news',
            'url'           => 'http://www.n-tv.de/rss',
            'category'      => '2',
            'priority'      => '1',
            'favicon'       => '467321.ico',
            'filter'        => '/.*4711.*/',
            'source'        => 'plugins_rss_multimedia'
        );
        
        $this->assertTrue(is_array( $this->model->edit($editfeed) ));
    }
}