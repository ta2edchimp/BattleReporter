<?php

$app = new \Slim\Slim();

$app->config('debug', $BR_DEBUGMODE);

// Give us pretty error messages ... (if in debug mode)
$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);

?>