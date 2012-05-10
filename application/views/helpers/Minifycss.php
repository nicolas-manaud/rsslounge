<?php

/**
 * View Helper for minify css
 *
 * @package    application_views
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Zend_View_Helper_Minifycss {

    /** 
     * minify css and return css link
     * if minify is disabled: return direct css links
     *
     * @return string with html tag
     * @param array $stylesheets with css files
     */
    public function minifycss($stylesheets) {
        if(Zend_Registry::get('config')->cache->enable==1 && Zend_Registry::get('config')->cache->minifycss==1) {
            // check file
            $target = Zend_Registry::get('config')->pub->path . 'stylesheets/' . Zend_Registry::get('config')->cache->minifiedcssfile;
            $targeturl = 'stylesheets/' . Zend_Registry::get('config')->cache->minifiedcssfile;
            
            if(file_exists($target))
                return "<link rel=\"stylesheet\" media=\"screen, handheld, projection, tv\" href=\"".$targeturl."\" />\n";
                
            // load and minify files
            $all = "";
            foreach($stylesheets as $css) {
                $csscontent = file_get_contents(Zend_Registry::get('config')->pub->path . $css);
                $csscontent = CssMin::minify($csscontent);
                
                $all .= $csscontent;
            }
            
            file_put_contents($target, $all);
            return "<link rel=\"stylesheet\" media=\"screen, handheld, projection, tv\" href=\"".$targeturl."\" />\n";
        
        } else {
            $ret = "";
            foreach($stylesheets as $css)
                $ret = $ret . "<link rel=\"stylesheet\" media=\"screen, handheld, projection, tv\" href=\"".$css."\" />\n";
            return $ret;
        }
    }
    
}

?>