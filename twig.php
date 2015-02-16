<?php

$twig = $app->view();

$twig->parserOptions = array(
    "charset" => "utf-8",
    "cache" => ($BR_DEBUGMODE === true ? false : ("$basePath/cache/templates/$theme")),
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

$twigEnv->addGlobal("BR_COMMENTS_ENABLED", BR_COMMENTS_ENABLED);

$twig_time_ago_filter = new Twig_SimpleFilter('time_ago', function (Twig_Environment $env, $date) {
	
	$date = twig_date_converter($env, $date);
	$diff = $date->diff(new DateTime());
	
	if ($diff->y > 0)
		return $diff->y . " year" . ($diff->y > 1 ? "s" : "") . " ago";
	elseif ($diff->m > 0)
		return $diff->m . " month" . ($diff->m > 1 ? "s" : "") . " ago";
	elseif ($diff->d > 0)
		return $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
	elseif ($diff->h > 0)
		return $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
	elseif ($diff->i > 0)
		return $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
	else
		return "less than a minute ago";
	
}, array("needs_environment" => true));
$twigEnv->addFilter($twig_time_ago_filter);

$twig_enable_urls_in_html_filter = new Twig_SimpleFilter('enable_urls', function ($string) {
	
	$string = nl2br($string);
	
	$output = preg_replace(
		"/(http(s){0,1}:\/\/([a-z0-9\.\/\?%#+=_-]{1,}))/i",
		"<a href=\"$1\">$1</a>",
		$string
	);
	if ($output !== NULL)
		return $output;
	
	return $string;
	
}, array('pre_escape' => 'html', 'is_safe' => array('html')));
$twigEnv->addFilter($twig_enable_urls_in_html_filter);
