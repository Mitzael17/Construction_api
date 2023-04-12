<?php

namespace core\sockets;

use core\controllers\base\SingleTon;

class BaseSocket
{

    use SingleTon;

    protected array $users = [];
    protected string $address = 'websocket://0.0.0.0';


    protected function onConnect($connection) {

        $this->users[] = $connection;

    }

    protected function createLinkForImage($image) {

        return PROTOCOL . '://' . DOMAIN . PATH . UPLOAD_DIR . $image;

    }

    protected function onClose($connection) {

        $user = array_search($connection, $this->users);

        unset($this->users[$user]);

    }

}