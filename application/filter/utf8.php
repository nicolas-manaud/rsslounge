<?php

/**
 * Filter for decode utf8 data
 *
 * @package    application_filter
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_filter_utf8 implements Zend_Filter_Interface {
    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string utf8 decoded
     *
     * @return string utf8 decoded string
     * @param string $value utf8 encoded string
     */
    public function filter($value) {
        return utf8_decode($value);
    }

}
