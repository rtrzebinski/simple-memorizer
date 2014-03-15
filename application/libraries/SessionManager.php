<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * This class is used for performing all read/write session operations
 * Native php session is utilized (MY_Session library)
 */
class SessionManager extends BaseLibrary {

    private $oUser;

    public function __construct() {
        parent::__construct();
        $this->CI->load->model('User');
    }

    public function setUser(User $oUser) {
        $this->CI->session->set_userdata('userId', $oUser->getId());
    }

    public function getUser() {
        if ($this->oUser === null) {
            $this->oUser = new User();
            if ($this->CI->session->userdata('userId')) {
                $this->oUser->setId($this->CI->session->userdata('userId'));
            }
        }

        return $this->oUser;
    }

    public function logout() {
        $this->CI->session->set_userdata('userId', NULL);
    }

}