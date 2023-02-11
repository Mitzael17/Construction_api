<?php

namespace core\controllers\admin;

use core\controllers\base\BaseController;

abstract class BaseAdmin extends BaseController
{

    protected $method;

    protected function init() {

        $this->method = $_SERVER['REQUEST_METHOD'];

    }

}