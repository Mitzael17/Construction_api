<?php

const ACCESS = true;

require_once "config.php";
require_once "core/settings/socket_settings.php";

\core\sockets\ProjectCommentsSocket::instance()->connect();