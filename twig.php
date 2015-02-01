<?php

$twig = $app->view();

$twig->parserOptions = array(
    "charset" => "utf-8",
    "cache" => false,//__DIR__ . "/cache/templates/$theme",
    "auto_reload" => true,
    "strict_variables" => false,
    "autoescape" => true
);

$twigEnv = $twig->getEnvironment();

if (defined('BR_OWNER'))
    $twigEnv->addGlobal("BR_OWNER", BR_OWNER);

$twigEnv->addGlobal("BR_USER_LOGGEDIN", User::isLoggedIn());
$twigEnv->addGlobal("BR_USER_CAN_CREATE", User::can('create'));