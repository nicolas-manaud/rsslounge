<?PHP

// set testing configuration
define('APPLICATION_ENV', 'testing');

error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Europe/Berlin');

// base configuration
require_once('../const.php');

// Zend_Application
require_once 'Zend/Application.php';  

// delete logger file
if(file_exists('log/logger'))
    unlink('log/logger');

// Create application, bootstrap
$application = new Zend_Application(
    APPLICATION_ENV, 
    CONFIG_PATH
);


//
// get config
//
$config = new Zend_Config_Ini(CONFIG_PATH);


//
// reset database
//

$strDbLocation = 'mysql:dbname='.$config->testing->resources->db->params->dbname.';host='.$config->testing->resources->db->params->host;
$strDbUser = $config->testing->resources->db->params->username;
$strDbPassword = $config->testing->resources->db->params->password;

try {
    $db = new PDO($strDbLocation, $strDbUser, $strDbPassword);
    $prefix = trim($config->testing->resources->db->prefix);
    
    // get dump
    $sql = file_get_contents(APPLICATION_PATH . '/../updates/database-dist.sql');

    // drop old data
    $sql = 'DROP TABLE IF EXISTS `'.$prefix.'categories`, `'.$prefix.'feeds`, `'.$prefix.'items`, `'.$prefix.'messages`, `'.$prefix.'settings`, `'.$prefix.'version`;' . $sql;
    
    // rename tables
    $sql = str_replace('CREATE TABLE IF NOT EXISTS `', 'CREATE TABLE `' . $prefix, $sql);
    $sql = str_replace('INSERT INTO `', 'INSERT INTO `' . $prefix, $sql);
    
    // insert predefined setting
    $sql = $sql . 'INSERT INTO `'.$prefix.'settings` (name,value) VALUES ("refresh","100");';
    
    // insert dump
    $db->exec($sql);
    
    // close database connection
    unset($db);
    
} catch (PDOException $e) {
  echo 'Error on accessing the database. Unit Test needs correct database settings. Error: ' . $e->getMessage();
  return;
}


// bootstrap application
$application->bootstrap();