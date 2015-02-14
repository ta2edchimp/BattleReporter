<?php

if (User::isLoggedIn() || BR_LOGINMETHOD_EVE_SSO != true || BR_LOGINMETHOD_EVE_SSO_CLIENTID == '' || BR_LOGINMETHOD_EVE_SSO_SECRET == '')
	$app->redirect("/");

$state			= uniqid();

$scheme			= $_SERVER["REQUEST_SCHEME"];
if (empty($scheme)) {
	$scheme		= "http";
	if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on')
		$scheme	= "https";
}
$redirect_uri	= $scheme . "://" . $_SERVER["HTTP_HOST"] . "/login/eve-sso-auth";
$ref			= $app->request->get('ref');
if ($ref == null || empty($ref) || strpos(strtolower($ref), strtolower($_SERVER["HTTP_HOST"])) === FALSE)
	$redirect_to = $scheme . "://" . $_SERVER["HTTP_HOST"] . "/";
else
	$redirect_to = $ref;

$_SESSION['auth_state'] = $state;
$_SESSION['auth_redirect'] = $redirect_to;
session_write_close();

header(
	'Location:https://login.eveonline.com/oauth/authorize' .
	'?response_type=code&redirect_uri=' . $redirect_uri .
	'&client_id=' . BR_LOGINMETHOD_EVE_SSO_CLIENTID . '&scope=&state=' . $state
);

exit;
