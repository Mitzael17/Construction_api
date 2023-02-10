<?php

namespace core\controllers\base;

trait BaseMethods
{

    public function writeLog($file, $message) {

        file_put_contents('logs/' . $file, $message, FILE_APPEND);

    }

}