<?PHP

    // ensure that nobody start install process on running system
    if(file_exists(APPLICATION_PATH . '/../config/config.ini'))
        exit;
    
    // global vars
    global $errors, $locale, $languages;
    
    // autoloader for zend classes
    require_once('Zend/Loader/Autoloader.php');
    $autoloader = Zend_Loader_Autoloader::getInstance();
    $autoloader->registerNamespace('Zend');
    
    // prepare language object
    if(isset($_POST['language']))
        $currentLang = $_POST['language'];
    else {
        $currentLang = 'en';
    }
    $locale = new Zend_Translate('csv', 'application/locale', 'en', array('scan' => Zend_Translate::LOCALE_DIRECTORY, 'delimiter' => "|"));
    
    // get languages
    $languages = array();
    foreach($locale->getList() as $lang)
        $languages[$lang] = $locale->translate($lang);
    
    // set language
    try {
        $languageLocale = new Zend_Locale(Zend_Locale::BROWSER);
        if(in_array($languageLocale->getLanguage(),$locale->getList())) {
            $currentLang = $languageLocale->getLanguage();
            $locale->setLocale($languageLocale);
        }
    } catch(Exception $e) {
        $currentLang = 'en';
        $locale->setLocale(new Zend_Locale('en'));
    }
    // get config
    $configDist = new Zend_Config_Ini(CONFIG_DIST_PATH);
    
    
    //
    // check system requirements
    //
    $errors = array();
    
    // writeable paths
    if(!is_writable(APPLICATION_PATH . '/../config/'))
        $errors['config'] = $locale->translate('config is not writeable');
    if(!is_writable(APPLICATION_PATH . '/../data/cache/'))
        $errors['data/cache'] = $locale->translate('data/cache is not writeable');
    if(!is_writable(APPLICATION_PATH . '/../data/favicons/'))
        $errors['data/favicons'] = $locale->translate('data/favicons is not writeable');
    if(!is_writable(APPLICATION_PATH . '/../data/thumbnails/'))
        $errors['data/thumbnails'] = $locale->translate('data/thumbnails is not writeable');
    if(!is_writable(APPLICATION_PATH . '/../data/logs/'))
        $errors['data/logs'] = $locale->translate('data/logs is not writeable');
    if(!is_writable(APPLICATION_PATH . '/../public/javascript/'))
        $errors['public/javascript'] = $locale->translate('public/javascript is not writeable');
    if(!is_writable(APPLICATION_PATH . '/../public/stylesheets/'))
        $errors['public/stylesheets'] = $locale->translate('public/stylesheets is not writeable');
    if(substr(PHP_VERSION,0,1)<5 || (substr(PHP_VERSION,0,1)==5 && substr(PHP_VERSION,2,1)<2))
        $errors['php'] = $locale->translate('you need at least php version 5.2.4');
    
    // check mod_rewrite
    if(!isset($_GET['mod_rewrite']) || $_GET['mod_rewrite']!=1)
        $errors['mod_rewrite'] = $locale->translate('the Apache Module mod_rewrite is not enabled');
    
    // check extensions
    $extensions = array(
        // zend framework
        'ctype',
        'Reflection',
        'session',
        'pdo',
        'pdo_mysql',
        'dom',
        
        // wideimage
        'gd',
        
        // simplepie
        'xml',
        'pcre',
        'mbstring'
    );
    
    foreach($extensions as $ext) 
        if(!extension_loaded($ext))
            $errors[$ext] = sprintf($locale->translate("rsslounge needs the '%s' extension"),$ext);
    
    
    //
    // install
    //
    
    function install() {
        global $errors, $locale, $languages;
        
        //
        // validate input
        //
        
        if(strlen(trim($_POST['language']))==0 || !isset($languages[trim($_POST['language'])]))
            $errors['language'] = $locale->translate('please select a valid language');

        if(strlen(trim($_POST['host']))==0)
            $errors['host'] = $locale->translate('please enter an host');

        if(strlen(trim($_POST['username']))==0)
            $errors['username'] = $locale->translate('please enter an username');
        
        if(strlen(trim($_POST['database']))==0)
            $errors['database'] = $locale->translate('please enter a database');    
        
        if(strlen(trim($_POST['login_password']))!=0 && $_POST['login_password']!=$_POST['login_password_again'])
            $errors['login_password'] = $locale->translate('given passwords not equal');
            
        if(strlen(trim($_POST['login_password']))!=0 && strlen(trim($_POST['login_username']))==0)
            $errors['login_password'] = $locale->translate('if you set a password you must set an username');
            
        if(strlen(trim($_POST['login_username']))!=0 && strlen(trim($_POST['login_password']))==0)
            $errors['login_password'] = $locale->translate('if you set a username you must set an password');
        
        if(count($errors)>0)
            return false;
        
        
        //
        // install database
        //
        
        // check database connection
        try {
            $port = false;
            $host = trim($_POST['host']);
            if(strpos($_POST['host'], ':')!==false) {
                $host = preg_split('/:/', $host);
                $port = $host[1];
                $host = $host[0];
            }
            $config = array(
                'host'     => $host,
                'username' => trim($_POST['username']),
                'password' => trim($_POST['password']),
                'dbname'   => trim($_POST['database']),
                'driver_options'  => array( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true )
            );
            if($port!==false)
                $config['port'] = $port;
            $db = Zend_Db::factory('Pdo_Mysql', $config);
            $db->getConnection();
        } catch (Zend_Db_Adapter_Exception $e) {
            $errors['database'] = $locale->translate('database connection error (ensure that the database exists)');
            return false;
        } catch (Zend_Exception $e) {
            $errors['database'] = $locale->translate('database error');
            return false;
        }
        
        // insert sql dump in database
        try {
            $sql = file_get_contents(APPLICATION_PATH . '/../updates/database-dist.sql');
            
            // rename tables
            if(strlen(trim($_POST['prefix']))>0) {
                $sql = str_replace('CREATE TABLE IF NOT EXISTS `', 'CREATE TABLE IF NOT EXISTS `' . trim($_POST['prefix']), $sql);
                $sql = str_replace('INSERT INTO `', 'INSERT INTO `' . trim($_POST['prefix']), $sql);
                $sql = str_replace('DELETE FROM `', 'DELETE FROM `' . trim($_POST['prefix']), $sql);
            }
            
            if(strlen(trim($_POST['login_password']))!=0 && strlen(trim($_POST['login_username']))!=0)
                $sql = $sql . "INSERT INTO " . trim($_POST['prefix']) . "users 
                                (username,
                                 password) 
                               VALUES 
                                ('".trim($_POST['login_username'])."',
                                 '".sha1(trim($_POST['login_password']))."');";
            
            // insert dump
            $db->exec($sql);
        } catch (Zend_Exception $e) {
            $errors['database'] = $locale->translate('database error (can\'t write dump into database)');
            return false;
        }
        
    
    
        //
        // write config file
        //
        
        $config = file_get_contents(CONFIG_DIST_PATH);
        
        $config = str_replace('resources.db.prefix =', 'resources.db.prefix = "'.trim($_POST['prefix']).'"', $config);
        $config = str_replace('resources.db.params.host =', 'resources.db.params.host = "'.$host.'"', $config);
        $config = str_replace('resources.db.params.username =', 'resources.db.params.username = "'.trim($_POST['username']).'"', $config);
        $config = str_replace('resources.db.params.password =', 'resources.db.params.password = "'.trim($_POST['password']).'"', $config);
        $config = str_replace('resources.db.params.dbname =', 'resources.db.params.dbname = "'.trim($_POST['database']).'"', $config);
        
        if($port!==false)
            $config = str_replace('resources.db.params.port =', 'resources.db.params.port = "'.$port.'"', $config);
        else
            $config = str_replace('resources.db.params.port =', '', $config);
        $config = str_replace('session.default.language = en', 'session.default.language = '.trim($_POST['language']), $config);
        
        
        if(isset($_POST['login_public']) && trim($_POST['login_public'])==1)
            $config = str_replace('session.default.public = 0', 'session.default.public = 1', $config);
        
        file_put_contents(CONFIG_PATH, $config);
        
        return true;
    }
    
    $success = false;
    if(count($_POST)>0)
        $success = install();
         
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>rsslounge aggregator</title>
    
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" media="screen, handheld, projection, tv" href="public/stylesheets/style.css" />
    <link rel="stylesheet" media="screen, handheld, projection, tv" href="public/stylesheets/install.css" />
    
    <script type="text/javascript" src="javascript/jquery-1.5.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#install').attr('action',document.location);
        });
    </script>
</head>
<body>
    <!-- header -->
    <div id="header">
        <div id="header-left"></div>
        <div id="header-content">
	        <h1><span>rssLounge aggregator</span></h1>
	    </div>
        <div id="header-right"></div>
    </div>
    
    <!-- main area -->
    <form id="install" action="" method="<?PHP if($success) echo 'get'; else echo 'post'; ?>">
        <h1><?PHP echo $locale->translate('Installation'); ?></h1>
        
        <?PHP if($success) : ?>
            <?PHP echo $locale->translate('Installation successfully. You can now use rsslounge.'); ?>
            <br /><br />
            <input type="submit" value="<?PHP echo $locale->translate('start rsslounge') ?>" />
        <?PHP endif; ?>

        
        <?PHP if(!$success) : ?>        
            <?PHP if(count($errors)>0) : ?>
            <ul class="error">
                <?PHP foreach($errors as $key => $err) : ?>
                <li><?PHP echo $err ?></li>
                <?PHP endforeach; ?>
            </ul>
            <?PHP endif; ?>
            
            <!-- 1. system check -->
            <h2><?PHP echo $locale->translate('1. System Check'); ?></h2>
            
            <ul class="systemcheck">
                <?PHP if(!isset($errors['php'])) : ?>
                    <li class="success"><label>PHP Version:</label> <span><?PHP echo PHP_VERSION ?></span> </li>
                <?PHP endif; ?>
                
                <?PHP if(!isset($errors['data/cache'])) : ?>
                    <li class="success"><label><?PHP echo $locale->translate('data/cache writeable'); ?>:</label> <span><?PHP echo $locale->translate('success') ?></span></li>
                <?PHP endif; ?>
                
                <?PHP if(!isset($errors['data/favicons'])) : ?>
                    <li class="success"><label><?PHP echo $locale->translate('data/favicons writeable'); ?>:</label> <span><?PHP echo $locale->translate('success') ?></span></li>
                <?PHP endif; ?>
                
                <?PHP if(!isset($errors['data/thumbnails'])) : ?>
                    <li class="success"><label><?PHP echo $locale->translate('data/thumbnails writeable'); ?>:</label> <span><?PHP echo $locale->translate('success') ?></span></li>
                <?PHP endif; ?>
                
                <?PHP if(!isset($errors['data/logs'])) : ?>
                    <li class="success"><label><?PHP echo $locale->translate('data/logs writeable'); ?>:</label> <span><?PHP echo $locale->translate('success') ?></span></li>
                <?PHP endif; ?>
                
                <?PHP if(!isset($errors['config'])) : ?>
                    <li class="success"><label><?PHP echo $locale->translate('config writeable'); ?>:</label> <span><?PHP echo $locale->translate('success') ?></span></li>
                <?PHP endif; ?>    
                
                <?PHP if(!isset($errors['public/javascript'])) : ?>
                    <li class="success"><label><?PHP echo $locale->translate('public/javascript writeable'); ?>:</label> <span><?PHP echo $locale->translate('success') ?></span></li>
                <?PHP endif; ?>
                
                <?PHP if(!isset($errors['public/stylesheets'])) : ?>
                    <li class="success"><label><?PHP echo $locale->translate('public/stylesheets writeable'); ?>:</label> <span><?PHP echo $locale->translate('success') ?></span></li>
                <?PHP endif; ?>
            </ul>
            
        
            <!-- 2. language -->
            <h2><?PHP echo $locale->translate('2. Select a language');?></h2>
            
            <select name="language" id="language" class="<?PHP echo isset($errors['host']) ? 'error' : 'success' ?>">
            <?PHP foreach($languages as $val => $lang) : ?>
                <option value="<?PHP echo $val; ?>" <?PHP if($currentLang==$val) : ?>selected="selected"<?PHP endif; ?>><?PHP echo $locale->translate($val); ?></option>
            <?PHP endforeach; ?>
            </select>
            
            
            <!-- 3. database -->
            <h2><?PHP echo $locale->translate('3. Enter database settings'); ?></h2>
            <ul>
                <li class="<?PHP echo isset($errors['host']) ? 'error' : 'success' ?>"><label><?PHP echo $locale->translate('Host'); ?>:</label><input type="text" value="<?PHP echo isset($_POST['host']) ? $_POST['host'] : '' ?>" name="host" /></li>
                <li class="<?PHP echo isset($errors['username']) ? 'error' : 'success' ?>"><label><?PHP echo $locale->translate('Username'); ?>:</label><input type="text" value="<?PHP echo isset($_POST['username']) ? $_POST['username'] : '' ?>" name="username" /></li>
                <li><label><?PHP echo $locale->translate('Password'); ?>:</label><input type="password" value="<?PHP echo isset($_POST['password']) ? $_POST['password'] : '' ?>" name="password" /></li>
                <li class="<?PHP echo isset($errors['database']) ? 'error' : 'success' ?>"><label><?PHP echo $locale->translate('Database'); ?>:</label><input type="text" value="<?PHP echo isset($_POST['database']) ? $_POST['database'] : '' ?>" name="database" /></li>
                <li><label><?PHP echo $locale->translate('Prefix'); ?>:</label><input type="text" value="<?PHP echo isset($_POST['prefix']) ? $_POST['prefix'] : '' ?>" name="prefix" /></li>
            </ul>
            
            
            <!-- 4. login -->
            <h2>
                <?PHP echo $locale->translate('4. Login'); ?>
                <span><?PHP echo $locale->translate('optional: leave the fields clear for no password protection'); ?></span>
            </h2>
             <ul>
                <li><label><?PHP echo $locale->translate('Username'); ?>:</label><input type="text" value="<?PHP echo isset($_POST['login_username']) ? $_POST['login_username'] : '' ?>" name="login_username" /></li>
                <li><label><?PHP echo $locale->translate('Password'); ?>:</label><input type="password" value="<?PHP echo isset($_POST['login_password']) ? $_POST['login_password'] : '' ?>" name="login_password" /></li>
                <li><label><?PHP echo $locale->translate('again Password'); ?>:</label><input type="password" value="<?PHP echo isset($_POST['login_password_again']) ? $_POST['login_password_again'] : '' ?>" name="login_password_again" /></li>
                <li><label><?PHP echo $locale->translate('Public Access'); ?>:</label> <input type="checkbox" value="1" name="login_public" <?PHP if(isset($_POST['login_public']) && trim($_POST['login_public'])==1) echo "checked=checked" ?> /></li>
            </ul>
            
            <br />
            <input type="submit" value="<?PHP echo $locale->translate('install rsslounge') ?>" />
        <?PHP endif; ?>
    </form>
</body>
</html>