<?PHP

/**
 * UnitTest for the categories model class
 *
 * @package    tests_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class CategoriesTest extends PHPUnit_Framework_TestCase {

    /**
     * @var application_models_categories
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
        $this->model = new application_models_categories();
        $this->db = $db = Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter();
        
        // insert test data
        $this->db->exec("INSERT INTO " . Zend_Registry::get('config')->resources->db->prefix . 'categories' . " (`id`,`name`,`position`)
                         VALUES 
                         (1 , 'category 1', '0'),
                         (2 , 'category 2', '1'),
                         (3 , 'category 3', '2');
                         
                         INSERT INTO " . Zend_Registry::get('config')->resources->db->prefix . 'feeds' . " (`id`, `source`, `url`, `category`, `priority`, `favicon`, `filter`, `name`, `position`, `icon`, `multimedia`, `dirtyicon`, `htmlurl`, `lastrefresh`, `error`)
                         VALUES 
                         (NULL, 'plugins_rss_feeds', 'http://www.n-tv.de/wirtschaft/rss', '1', '1', NULL, NULL, 'n-tv', '0', '', '0', '1', '', NULL, '0'),
                         (NULL, 'plugins_rss_feeds', 'http://blog.aditu.de/feed', '1', '2', NULL, NULL, 'Tobis Blog', '2', '', '0', '1', '', NULL, '0');
                         
                         ");
    }
    
    
    /**
     * remove temp values
     */
    public function tearDown() {
        $this->db->exec("DELETE FROM " . Zend_Registry::get('config')->resources->db->prefix . 'categories');
        $this->db->exec("DELETE FROM " . Zend_Registry::get('config')->resources->db->prefix . 'feeds');
    }
    
    
    /**
     * test configuration
     */
    public function testConfiguration() {
        $this->assertEquals($this->db, $this->model->getAdapter());
        
        $this->assertEquals(array(1 => 'id'), $this->model->info('primary'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->prefix . 'categories', $this->model->info('name'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->params->dbname, $this->model->info('schema'));
        $this->assertEquals(array('application_models_feeds'), $this->model->getDependentTables());
    }

    
    /**
     * test max position
     */
    public function testMaxPosition() {
        $this->assertEquals(2, $this->model->maxPosition());
    }
    
    
    /**
     * test feed counter
     */
    public function testFeeds() {
        $this->assertEquals(2, $this->model->feeds(1));
        $this->assertEquals(2, $this->model->feeds($this->model->find(1)->current()));
    }
    
    
    /**
     * test set categories
     */
    public function testSetCategories() {
        $newdata = array(
            array(
                'id'        => 1,
                'name'      => 'cat 1',
                'position'  => 1
            ),
            array(
                'id'        => 2,
                'name'      => 'category 2',
                'position'  => 0
            ),
            array(
                'id'        => 0,
                'name'      => 'new cat',
                'position'  => 3
            )
        );
        
        // set new without delete an filled category
        $this->model->setCategories($newdata);
        
        $this->assertEquals($newdata[0], $this->model->find(1)->current()->toArray()); // update
        $this->assertEquals($newdata[1], $this->model->find(2)->current()->toArray()); // update
        $this->assertEquals( // new
            1, 
            $this->model->fetchAll( 
                $this->model->select()->where('name=?','new cat')
            )->count()
        );
        $this->assertEquals(3, $this->model->fetchAll()->count()); // old deleted
        
        
        // set with delete filled category
        $newdata = array(
            array(
                'id'        => 0,
                'name'      => 'newcat',
                'position'  => 1
            )
        );
        $this->model->setCategories($newdata);
        
        $this->assertEquals(1, $this->model->fetchAll()->count());
        $this->assertEquals(0, $this->model->feeds(1)); // no more feeds in cat 1
        $this->assertEquals(2, $this->model->feeds(-1)); // now all feeds in cat -1 (uncategorized)
    }
    
    
    /**
     * test the fix position routine
     * setUp will insert feeds with position 0 and 2
     * fixPosition corrects to 0 and 1
     */
    public function testFixPositions() {
        $this->model->fixPositions( $this->model->find(1)->current());
        $feedModel = new application_models_feeds();
        $positions = $feedModel->fetchAll(
                        $feedModel->select()
                                  ->order('position ASC')
                                  ->from($feedModel, 'position')
                    )->toArray();
        $expected = array(
            array('position' => 0), 
            array('position' => 1));
        $this->assertEquals($expected, $positions);
    }
    
    
    /**
     * test automatic generation of uncategorized cat
     * setUp will not insert the uncategrized cat
     */
    public function testCheckUncategorized() {
        $this->assertEquals(0, $this->model->find(-1)->count());
        $this->model->checkUncategorized();
        $this->assertEquals(1, $this->model->find(-1)->count());
        $this->model->checkUncategorized();
        $this->assertEquals(1, $this->model->find(-1)->count()); // dont add it twice
    }
    
    
    /**
     * test validation
     */
    public function testValidate() {
        $newdata = array(
            array(
                'id'        => 44,
                'name'      => 'cat 1',
                'position'  => 1
            ),
            array(
                'id'        => 2,
                'name'      => '  ',
                'position'  => 0
            ),
            array(
                'id'        => 0,
                'name'      => 'new cat',
                'position'  => 'aa'
            ),
            array(
                'id'        => 'abc',
                'name'      => 'cat',
                'position'  => 'aa'
            ),
            array(
                'id'        => 'abc',
                'position'  => 'aa'
            ),
            array(
                'id'        => 'abc',
                'name'      => null,
                'position'  => 'aa'
            ),
            array(
                'id'        => null,
                'name'      => 'bbb',
                'position'  => 'aa'
            ),
            array(
                'id'        => 0,
                'name'      => 'bbb',
                'position'  => null
            )
        );
        
        foreach($this->model->setCategories($newdata) as $errors)
            $this->assertGreaterThan(0,count($errors));
    }
}