<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Provides common resources for extending libraries
 */
class BaseLibrary {

    protected $CI;

    public function __construct() {
        $this->CI = & get_instance();
    }

}