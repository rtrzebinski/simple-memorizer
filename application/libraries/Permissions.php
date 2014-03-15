<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Custom exception for permission errors handling
 */
class AccessControlException extends Exception {
    
}

/**
 * Checks logged user's permission to the particular system module
 */
class Permissions extends BaseLibrary {

    private $oUser;

    public function __construct() {
        parent::__construct();
        $this->oUser = $this->CI->sessionmanager->getUser();
    }

    /**
     * $arrData has to contain:
     * string $arrData['moduleName']
     * int $arrData['objectId']
     * @param array $arrData
     */
    public function check(array $arrData) {
        $oObject = new $arrData['moduleName'];
        $oObject->setId($arrData['objectId']);
        $this->checkObjectPermissions($oObject);
    }

    private function checkObjectPermissions(CreatedByUserInterface $oObject) {
        // logged user is object author
        if ($oObject->getUserId() == $this->oUser->getId()) {
            return;
        }

        // logged user is admin
        if ($this->oUser->getPermissions() > 0) {
            return;
        }

        $this->runPermissionsFailureAction();
    }

    private function runPermissionsFailureAction() {
        throw new AccessControlException("Permissions error", 403);
    }

}