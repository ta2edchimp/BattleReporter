<?php

if (User::isLoggedIn() || BR_LOGINMETHOD_EVE_SSO != true || empty(BR_LOGINMETHOD_EVE_SSO_CLIENTID) || empty(BR_LOGINMETHOD_EVE_SSO_SECRET))
	$app->redirect("/");

$code	= $app->request->get('code');
$state	= $app->request->get('state');

// The state defined before the sso redirect and the returned one match.
if (!isset($_SESSION['auth_state']) || $state == null || $_SESSION['auth_state'] != $state)
	$app->redirect("/login");

$tokenResult = Utils::curl(
	"https://login.eveonline.com/oauth/token",
	array(
		"grant_type" => "authorization_code",
		"code" => $code
	),
	array(
		"postParams" => true,
		"caching" => false,
		"headers" => array("Authorization: Basic " . base64_encode(BR_LOGINMETHOD_EVE_SSO_CLIENTID . ":" . BR_LOGINMETHOD_EVE_SSO_SECRET)),
		"sslVerify" => true
	)
);

if (empty($tokenResult) || $tokenResult === false) {
	$app->render('login.html', array("loginError" => "Could not login via EVE Online Single Sign On. Please try again later."));
	$app->stop();
}

$tokenResult	= json_decode($tokenResult);
$authToken		= $tokenResult->access_token;

$verifyResult = Utils::curl(
	"https://login.eveonline.com/oauth/verify",
	array(),
	array(
		"caching" => false,
		"headers" => array("Authorization: Bearer " . $authToken),
		"sslVerify" => true
	)
);

if (empty($verifyResult) || $verifyResult === false) {
	$app->render('login.html', array("loginError" => "Could not verify the login via EVE Online Single Sign On. Please try again later."));
	$app->stop();
}

$verifyResult	= json_decode($verifyResult);

if (!isset($verifyResult->CharacterID) || !isset($verifyResult->CharacterName) || !isset($verifyResult->CharacterOwnerHash)) {
	$app->render('login.html', array("loginError" => "Login invalid. Could not obtain valid EVE Online Character. Please try again later."));
	$app->stop();
}

$verifyResult->CharacterName . " (ID: " . $verifyResult->CharacterID . ")</p>";

if (User::checkEVESSOLogin($verifyResult->CharacterID, $verifyResult->CharacterName, $verifyResult->CharacterOwnerHash)) {
	$app->redirect("/");
} else {
	$app->render('login.html', array(
		"loginError" => "Login invalid." .
			(BR_LOGIN_ONLY_OWNERCORP ? (" Only members of \"" . BR_OWNERCORP_NAME . "\" are permitted to login.") : "")
	));
}