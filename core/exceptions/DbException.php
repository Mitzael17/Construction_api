<?php

namespace core\exceptions;


use core\controllers\base\BaseMethods;

class DbException extends \Exception
{

    use BaseMethods;

    public function __construct($message = "", $code = 0)
    {
        parent::__construct($message, $code);

        $this->writeLog('DbLogs.txt', "\r\n\r\n File " . $this->getFile() . ' In line ' . $this->getLine() . " was error \r\n The error message - $message. \r\n The error code - $code");

        $response = ['status' => 'error', 'message' => $message];

        http_response_code($code);
        exit(json_encode($response));

    }

}