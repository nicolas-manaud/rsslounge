<?php

/**
 * View Helper for minify js
 *
 * @package    application_views
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Zend_View_Helper_Minifyjs {

    /** 
     * minify js and return js link
     * if minify is disabled: return direct js links
     *
     * @return string with html tag
     * @param array $javascripts with js files
     */
    public function minifyjs($javascripts) {
        if(Zend_Registry::get('config')->cache->enable==1 && Zend_Registry::get('config')->cache->minifyjs==1) {
            // check file
            $target = Zend_Registry::get('config')->pub->path . 'javascript/' . Zend_Registry::get('config')->cache->minifiedjsfile;
            $targeturl = 'javascript/' . Zend_Registry::get('config')->cache->minifiedjsfile;
            
            if(file_exists($target))
                return "<script type=\"text/javascript\" src=\"".$targeturl."\"></script>\n";
                
            // load and minify files
            $all = "";
            foreach($javascripts as $js) {
                $jscontent = file_get_contents(Zend_Registry::get('config')->pub->path . $js);
                $jscontent = JSMin::minify($jscontent);
                
                $all = $all . "\n\n// " . $js . "\n" . $jscontent;
            }
            
            file_put_contents($target, $all);
            return "<script type=\"text/javascript\" src=\"".$targeturl."\"></script>\n";
        
        } else {
            $ret = "";
            foreach($javascripts as $js)
                $ret = $ret . "<script type=\"text/javascript\" src=\"".$js."\"></script>\n";
            return $ret;
        }
    }
    
}

?>