<?php

namespace core\controllers\base;

trait BaseMethods
{

    public function writeLog($file, $message) {

        file_put_contents('logs/' . $file, $message, FILE_APPEND);

    }

    public function createAliasForImage($image) {

        return $this->protocol . '://' . $_SERVER['SERVER_NAME'] . PATH . UPLOAD_DIR . $image;

    }

}