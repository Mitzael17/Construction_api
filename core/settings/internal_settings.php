<?php

use \core\exceptions\RouteException;

const ROUTES = [
    'admin' => [
        'controllers' => 'core/controllers/admin/',
        'models' => 'core/models/admin/'
    ],
    'user' => [
        'controllers' => 'core/controllers/user/',
        'models' => 'core/models/user/'
    ],
];


const ADMIN_PATH = 'admin';

function autoloadClasses($classname) {

    $classname = str_replace('\\', '/', $classname);

    if(!@include_once $classname . '.php') {

        throw new RouteException('The url is invalid', 404);

    }

}

spl_autoload_register('autoloadClasses');