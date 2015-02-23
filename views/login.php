<?php

if (User::isLoggedIn())
    $app->redirect("/");

if (!$app->request->isPost()) {
    $app->render("login.html", array(
    	"loginReferer" => isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : ""
    ));
} else {
    
    $output = array();
    
    $userName = $app->request->post('userName');
    $password = $app->request->post('password');
    $autoLogin = ($app->request->post('autoLogin') == "true");
    
    $output["userName"] = $userName;
    $output["autoLogin"] = $autoLogin;
    
    if (User::login($userName, $password, $autoLogin)) {
		$ref = $app->request->post('referer');
		if (empty($ref) || strpos(strtolower($ref), strtolower($_SERVER["HTTP_HOST"])) === FALSE)
			$app->redirect("/");
		else
			$app->redirect($ref);
	}
    
    $output["loginError"] = "The username is unknown or the password is incorrect! Please try again.";
    $app->render("login.html", $output);
    
}
