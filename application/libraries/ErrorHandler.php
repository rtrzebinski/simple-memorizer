<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class ErrorHandler extends BaseLibrary {

    protected $oException;

    public function __construct() {
        parent::__construct();
        set_exception_handler(array($this, 'handleException'));
        set_error_handler(array($this, 'handleError'));
    }

    /**
     * Handles errors regarding error_reporting value
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @throws ErrorException
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        if (error_reporting() & $errno) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    }

    /**
     * Handles exception regarding ENVIRONMENT constant
     * Execution will stop after this function is called.
     * @param Exception $oException
     */
    public function handleException(Exception $oException) {
        $this->oException = $oException;

        switch (ENVIRONMENT) {
            case 'development':
            case 'testing':
                $this->printException();
                $this->logException();
                break;
            case 'production':
                $this->logException();
                break;
        }
    }

    /**
     * Prints readable exception content to the client
     */
    private function printException() {
        print("<pre>");
        print("Code: " . $this->oException->getCode() . "<br/>");
        print("Message: " . $this->oException->getMessage() . "<br/>");
        print("File: " . $this->oException->getFile() . "<br/>");
        print("Line: " . $this->oException->getLine() . "<br/>");
        print("Trace: " . $this->oException->getTraceAsString() . "<br/>");
        print("</pre>");
    }

    /**
     * Logs exception in application/logs
     * Requires $config['log_threshold'] to be >= 1 (application/config/config.php)
     */
    private function logException() {
        $this->CI->logger->logException($this->oException);
    }

}