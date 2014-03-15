<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Writes logs into log file (/application/logs)
 */
class Logger extends BaseLibrary {

    private $oUser;

    public function __construct() {
        parent::__construct();
        $this->oUser = $this->CI->sessionmanager->getUser();
    }

    /**
     * Logs action to log file as 'info'
     * Requires $config['log_threshold'] to be >= 3 (application/config/config.php)
     * @param string $strAction name of the action user takes
     * @param array $arrData optional details of taken action
     */
    public function logAction($strAction, array $arrData = null) {
        $strMessage = '';
        $strMessage .= 'action: ' . $strAction . ' ';
        $strMessage .= 'ip: ' . $this->CI->input->ip_address() . ' ';

        // add user id if any logged in
        if ($this->oUser->getId()) {
            $strMessage .= 'user: ' . $this->oUser->getId() . ' ';
        }

        // add data if provided
        if ($arrData) {
            $strMessage .= 'data: ' . str_replace(array("\n", "\r", "    "), '', print_r($arrData, true));
        }

        log_message('info', $strMessage);
    }

    /**
     * Logs exception to log file as 'error'
     * Requires $config['log_threshold'] to be >= 1 (application/config/config.php)
     * @param Exception $oException
     */
    public function logException(Exception $oException) {
        $strMessage = '';
        $strMessage .= $oException->getMessage() . ' ';
        $strMessage .= $oException->getCode() . ' ';
        $strMessage .= $oException->getFile() . ' ';
        $strMessage .= $oException->getLine();
        $strMessage .= "\n" .  $oException->getTraceAsString();
        
        log_message('error', $strMessage);
    }

}