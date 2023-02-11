<?php

namespace core\controllers\base;

use core\exceptions\RouteException;

abstract class BaseController
{

    protected $controller;
    protected $args;


    public function route() {

        $controller = str_replace('/', '\\', $this->controller);

        try {

            $object= new \ReflectionMethod($controller, 'inputData');

            $object->invoke(new $controller, $this->args);

        }
        catch(\ReflectionException $error) {

            throw new RouteException($error->getMessage());

        }


    }


}