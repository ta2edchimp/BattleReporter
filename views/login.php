<?php

if (User::isLoggedIn())
    $app->redirect("/");

if (!$app->request->isPost()) {
    $app->render("login.html");
} else {
    
    $output = array();
    
    $userName = $app->request->post('userName');
    $password = $app->request->post('password');
    $autoLogin = ($app->request->post('autoLogin') == "true");
    
    $output["userName"] = $userName;
    $output["autoLogin"] = $autoLogin;
    
    echo "<p>$userName / $password</p>";
    
    if (User::login($userName, $password, $autoLogin))
        $app->redirect("/");
    
    $output["loginError"] = "The username is unknown or the password is incorrect! Please try again.";
    $app->render("login.html", $output);
    
}