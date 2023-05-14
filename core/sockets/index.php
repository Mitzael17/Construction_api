<?php

require_once "core/settings/socket_settings.php";
require_once "config.php";

\core\sockets\ProjectCommentsSocket::instance()->connect();