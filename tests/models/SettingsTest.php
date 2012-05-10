<?PHP

/**
 * UnitTest for the settings model class
 *
 * @package    tests_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class SettingsTest extends PHPUnit_Framework_TestCase {

    /**
     * @var application_models_settings
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
        $this->model = new application_models_settings();
        $this->db = Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter();
        
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
            'selected'                => '',
            'dateFilter'              => '0',
            'dateStart'               => '',
            'dateEnd'                 => '',
            'unread'                  => 0,
            'starred'                 => '0',
            'currentPriorityStart'    => '1',
            'currentPriorityEnd'      => '2',
            'authenticated'           => true
        );
    }
    
    
    /**
     * remove temp values
     */
    public function tearDown() {
        
    }
    
    
    /**
     * test configuration
     */
    public function testConfiguration() {
        $this->assertEquals($this->db, $this->model->getAdapter());
        $this->assertEquals(array(1 => 'name'), $this->model->info('primary'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->prefix . 'settings', $this->model->info('name'));
        $this->assertEquals(Zend_Registry::get('config')->resources->db->params->dbname, $this->model->info('schema'));
    }
    
    
    /**
     * test set without validating
     */
    public function testSet() {
        $settings = array(
            'language'      => 'en',
            'new value'     => '123',
            'imagesHeight'  => 3
        );
        
        $this->model->save($settings);
        $this->model->save($settings); // double set = no two values
        
        $this->assertEquals(1, $this->model->find('language')->count());
        $this->assertEquals('en', $this->model->find('language')->current()->value );
        $this->assertEquals('en', Zend_Registry::get('session')->language ); // check session update
        $this->assertEquals(0, $this->model->find('new value')->count()); // don't add new values
    }
    
    
    /**
     * test validate
     */
    public function testValidation() {
        $this->checkWrongValue('deleteItems', 'abc');
        $this->checkWrongValue('deleteItems', 2001);
        $this->checkWrongValue('deleteItems', -3);
        $this->checkWrongValue('imagesPosition', 'middle');
        $this->checkWrongValue('imagesPosition', 33);
        $this->checkWrongValue('language', 'vv');
        $this->checkWrongValue('language', 333);
        $this->checkWrongValue('refresh', 'a');
        $this->checkWrongValue('refresh', -3);
        $this->checkWrongValue('refresh', 0);
        $this->checkWrongValue('lastrefresh', 'as');
        $this->checkWrongValue('view', 'nothing');
        $this->checkWrongValue('view', 1);
        $this->checkWrongValue('offset', 'a');
        $this->checkWrongValue('itemsperpage', 'a');
        $this->checkWrongValue('itemsperpage', 201);
        $this->checkWrongValue('itemsperpage', -1);
        $this->checkWrongValue('dateFilter', 'a');
        $this->checkWrongValue('unread', 'a');
        $this->checkWrongValue('starred', 'a');
        $this->checkWrongValue('currentPriorityStart', 'a');
        $this->checkWrongValue('currentPriorityEnd', 'a');
        $this->checkWrongValue('saveOpenCategories', 'a');
        
        $result = $this->model->validate(
                    array_merge(
                        $this->settings,
                        array(
                            'dateFilter' => 1,
                            'dateStart' => '2009-30-30'
                        )
                    ));
        $this->assertTrue(is_array($result));
        
        $result = $this->model->validate(
                    array_merge(
                        $this->settings,
                        array(
                            'dateFilter' => 1,
                            'dateEnd' => '200-01-01'
                        )
                    ));
        $this->assertTrue(is_array($result));
    }
    
    
    /**
     * validate wrong settings value
     *
     * @return void
     * @param string $name of the setting
     * @param string $value of the setting
     */
    private function checkWrongValue($name, $value) {
        $result = $this->model->validate(
                    array_merge(
                        $this->settings,
                        array(
                            $name => $value
                        )
                    ));
        $this->assertTrue(is_array($result));
    }
}
