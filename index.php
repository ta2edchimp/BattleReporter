<?php

require_once('vendor/autoload.php');

// By default, not in debug mode
$BR_DEBUGMODE = false;

require_once('config.php');

require_once('init.php');

require_once('routes.php');

$app->run();

?>