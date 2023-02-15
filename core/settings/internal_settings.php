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

const CRYPT_KEY = 'A%D*G-KaPdSgUkXp7w!z%C*F-JaNdRgUq3t6w9z$C&F)J@NcVkYp3s6v9y$B&E)HdRgUkXp2s5v8y/B?JaNdRfUjXn2r5u8x&F)J@NcRfTjWnZr4y$B&E)H@McQeThWm5v8y/B?E(H+MbQeSn2r5u8x/A?D(G+Kb';

const ADMIN_PATH = 'admin';

const UPLOAD_DIR = 'uploads/';

const DEFAULT_STATUS_ID = 1;

function autoloadClasses($classname) {

    $classname = str_replace('\\', '/', $classname);

    if(!@include_once $classname . '.php') {

        throw new RouteException('The url is invalid', 404);

    }

}

spl_autoload_register('autoloadClasses');