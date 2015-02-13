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

$userIsAdmin = false;
if (User::isLoggedIn()) {
	$twigEnv->addGlobal("BR_USER_LOGGEDIN", User::isLoggedIn());
	$userInfo = User::getUserInfos();
	if ($userInfo !== NULL) {
		$twigEnv->addGlobal("BR_USER_NAME", $userInfo["userName"]);
		if (!empty($userInfo["characterID"]))
			$twigEnv->addGlobal("BR_USER_CHARACTERID", $userInfo["characterID"]);
		$userIsAdmin = User::isAdmin();
	}
} else {
	$twigEnv->addGlobal("BR_USER_LOGGEDIN", User::isLoggedIn());
}
$twigEnv->addGlobal("BR_USER_IS_ADMIN", $userIsAdmin);
$twigEnv->addGlobal("BR_USER_CAN_CREATE", User::can('create'));
$twigEnv->addGlobal("BR_USER_CAN_EDIT", $userIsAdmin);
$twigEnv->addGlobal("BR_USER_CAN_UNPUBLISH", $userIsAdmin);
$twigEnv->addGlobal("BR_USER_CAN_DELETE", $userIsAdmin);

$twigEnv->addGlobal("BR_LOGINMETHOD_EVE_SSO", BR_LOGINMETHOD_EVE_SSO);

$twigEnv->addGlobal("BR_FETCH_SOURCE_URL", BR_FETCH_SOURCE_URL);
$twigEnv->addGlobal("BR_FETCH_SOURCE_NAME", BR_FETCH_SOURCE_NAME);