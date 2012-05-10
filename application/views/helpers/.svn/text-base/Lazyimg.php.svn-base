<?php

/**
 * View Helper for removing img src tags for loading the
 * images later via javascript
 *
 * @package    application_views
 * @subpackage helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Zend_View_Helper_Lazyimg {

    /** 
     * removes img src attribute and saves the value in ref for
     * loading it later
     *
     * @return string with replaced img tags
     * @param string $content which contains img tags
     */
    public function lazyimg($content) {
        return preg_replace("/<img([^<]+)src=(['\"])([^\"']*)(['\"])([^<]*)>/i","<img$1ref='$3'$5>",$content);
    }
    
}

?>