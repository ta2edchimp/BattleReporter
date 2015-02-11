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
    
$twigEnv->addGlobal("BR_OWNERCORP_NAME", BR_OWNERCORP_NAME);

$twigEnv->addGlobal("BR_USER_LOGGEDIN", User::isLoggedIn());
$userIsAdmin = User::isAdmin();
$twigEnv->addGlobal("BR_USER_IS_ADMIN", $userIsAdmin);
$twigEnv->addGlobal("BR_USER_CAN_CREATE", User::can('create'));
$twigEnv->addGlobal("BR_USER_CAN_EDIT", $userIsAdmin);
$twigEnv->addGlobal("BR_USER_CAN_UNPUBLISH", $userIsAdmin);
$twigEnv->addGlobal("BR_USER_CAN_DELETE", $userIsAdmin);


$twigEnv->addGlobal("BR_FETCH_SOURCE_URL", BR_FETCH_SOURCE_URL);
$twigEnv->addGlobal("BR_FETCH_SOURCE_NAME", BR_FETCH_SOURCE_NAME);