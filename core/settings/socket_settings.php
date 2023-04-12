<?php

const ACCESS = true;

const PROTOCOL = 'http';
const DOMAIN = 'construction';


require_once 'config.php';

function autoloadClasses($classname) {

    $classname = str_replace('\\', '/', $classname);

    @include_once $classname . '.php';

}

spl_autoload_register('autoloadClasses');

include 'vendor/autoload.php';