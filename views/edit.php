<?php

if (!User::can('edit'))
    $app->redirect('/');

if (empty($battleReportID) || $battleReportID == 0) {
    $app->render("brNotFound.html");
    $app->stop();
}

$output = array();

// Try to fetch the specified battle report
$battleReport = new Battle();
if ($battleReport->load($battleReportID, false, true) == false) {
    $app->render("brNotFound.html");
    $app->stop();
}

// Admins may edit every battle report,
// normal users may only edit their own battle reports.
$twigEnv = $app->view()->getEnvironment();
if (User::isAdmin() || $battleReport->creatorUserID == User::getUserID()) {
	
	$twigEnv->addGlobal("BR_USER_CAN_EDIT", true);
	$twigEnv->addGlobal("BR_USER_CAN_UNPUBLISH", true);
	
	if (strtolower($battleReportEditAction) == "unpublish") {
		$battleReport->unpublish();
		$app->redirect("/");
	}
	
	if (User::isAdmin() && strtolower($battleReportEditAction) == "delete") {
		$battleReport->delete();
		$app->redirect("/");
	}
	
} else {
	$twigEnv->addGlobal("BR_USER_CAN_EDIT", false);
	$twigEnv->addGlobal("BR_USER_CAN_UNPUBLISH", false);
}

// User posted changes to the current battle report
if ($app->request->isPost()) {
    
    $success = true;
    $parameters = $app->request->post();
    
    $brChanges = json_decode($parameters["battleReportChanges"]);
    if ($brChanges != null)
        $success = $battleReport->applyChanges($brChanges);
    
    $battleReport->title = $parameters["battleTitle"];
	
	$videoUrl = $parameters["battleFootageUrl"];
	// currently, allow only one video
	$battleReport->removeFootage();
	if (!empty($videoUrl))
		$battleReport->addFootage(array($videoUrl));
    
    if ($success) {
        $battleReport->publish();
        $app->redirect("/show/$battleReportID");
    } else {
        $output["battleReportSavingError"] = true;
	}
    
}

$output["battleReport"] = $battleReport;

$app->render("edit.html", $output);
