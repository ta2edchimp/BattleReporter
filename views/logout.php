<?php

User::logout();

include('../twig.php');

$requesturi = "";
if(isset($_SERVER["HTTP_REFERER"]))
	$requesturi = $_SERVER["HTTP_REFERER"];

if (isset($requesturi) && !empty($requesturi))
	$app->redirect($requesturi);
else
	$app->render("logout.html");
