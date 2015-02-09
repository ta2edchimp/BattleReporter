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

if (User::isAdmin() && strtolower($battleReportEditAction) == "unpublish") {
    $battleReport->unpublish();
    $app->redirect("/");
}

// User posted changes to the current battle report
if ($app->request->isPost()) {
    
    $success = true;
    $parameters = $app->request->post();
    
    $brChanges = json_decode($parameters["battleReportChanges"]);
    if ($brChanges != null)
        $success = $battleReport->applyChanges($brChanges);
    
    $battleReport->title = $parameters["battleTitle"];
    
    if ($success) {
        $battleReport->publish();
        $app->redirect("/show/$battleReportID");
    } else {
        $output["battleReportSavingError"] = true;
	}
    
}

$output["battleReport"] = $battleReport;

$app->render("edit.html", $output);