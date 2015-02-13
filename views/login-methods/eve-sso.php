<?php

if (User::isLoggedIn() || BR_LOGINMETHOD_EVE_SSO != true || empty(BR_LOGINMETHOD_EVE_SSO_CLIENTID) || empty(BR_LOGINMETHOD_EVE_SSO_SECRET))
	$app->redirect("/");

$state			= uniqid();

$redirect_uri	= $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/login/eve-sso-auth";
$redirect_to	= $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/";

$_SESSION['auth_state'] = $state;
$_SESSION['auth_redirect'] = $redirect_to;
session_write_close();

header(
	'Location:https://login.eveonline.com/oauth/authorize' .
	'?response_type=code&redirect_uri=' . $redirect_uri .
	'&client_id=' . BR_LOGINMETHOD_EVE_SSO_CLIENTID . '&scope=&state=' . $state
);

exit;
