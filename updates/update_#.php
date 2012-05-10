<?PHP
    // this is an example update file for the database
    ob_start();
?>
CREATE TABLE `rsslounge`.`testtabelle1` (
`bla` INT NOT NULL
) ENGINE = MYISAM ;
<?PHP
    $sql = ob_get_contents();
    ob_end_clean();
    
    $db = Zend_Registry::get('bootstrap')->getPluginResource('db')->getDbAdapter();
    $db->exec($sql);
    