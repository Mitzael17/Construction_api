<?php

namespace core\traits;

trait BaseMethods
{

    public function writeLog($file, $message) {

        file_put_contents('logs/' . $file, $message, FILE_APPEND);

    }

}