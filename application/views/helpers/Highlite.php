<?php

/**
 * View Helper for highliting a given word
 *
 * @package    application_views
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Zend_View_Helper_Highlite {

    /** encloses all searchWords with <span class=found>$word</span>
      * for later highlitning with CSS
      *
      * @return string with highlited words
        * @param string $content which contains words
        * @param array|string $searchWords words for highlighting
      */
    public function highlite($content, $searchWords) {
        
        if(strlen(trim($searchWords))==0)
            return $content;
        
        if(!is_array($searchWords))
            $searchWords = array($searchWords);
        
        foreach($searchWords as $word)
            $content = preg_replace('/(?!<[^<>])('.$word.')(?![^<>]*>)/i','<span class=found>$0</span>',$content);
            
        return $content;
    }
    
}

?>