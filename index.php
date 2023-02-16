<?php

header('Content-type: json/application');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

const ACCESS = true;

require_once 'config.php';
require_once 'core/settings/internal_settings.php';

use core\controllers\base\RouteController;
use core\exceptions\RouteException;

try {

    RouteController::instance()->route();

} catch (RouteException $e) {

    exit(json_encode(['status' => 'error', 'message' => $e->getMessage()]));

}

