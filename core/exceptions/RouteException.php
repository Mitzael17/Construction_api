<?php

namespace core\exceptions;



use core\traits\BaseMethods;

class RouteException extends \Exception
{

    use BaseMethods;

    public function __construct($message = "", $code = 0)
    {

        parent::__construct($message, $code);

        $this->writeLog('routeLogs.txt', "\r\n\r\n File " . $this->getFile() . ' In line ' . $this->getLine() . " was error \r\n The error message - $message. \r\n The error code - $code");

        $response = ['status' => 'error', 'message' => $message];

        exit(json_encode($response));
    }

}