<?php

use \core\exceptions\RouteException;

const ROUTES = [
    'controllers' => 'core/controllers/',
    'models' => 'core/models/'
];

function autoloadClasses($classname) {

    $classname = str_replace('\\', '/', $classname);

    if(!@include_once $classname . '.php') {

        throw new RouteException('The url is invalid', 404);

    }

}

spl_autoload_register('autoloadClasses');