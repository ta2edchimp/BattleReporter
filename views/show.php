<?php

if (empty($battleReportID) || $battleReportID == 0) {
    $app->render("brNotFound.html");
    $app->stop();
}

// Try to fetch the specified battle report
$battleReport = new Battle();

if ($battleReport->load($battleReportID, false) == false) {
    $app->render("brNotFound.html");
    $app->stop();
}

// If unpublished and/or no access ... yell "not found"
if ($battleReport->published == false) {
	
	// Users with general "edit" permission may see an unpublished battle report
	if (!User::isLoggedIn() || !User::can("edit")) {
		$app->render("brNotFound.html");
		$app->stop();
	}
	
	// ... but only admins and owners may actually edit this one.
	if (User::isAdmin() || $battleReport->creatorUserID == User::getUserID()) {
		$twigEnv = $app->view()->getEnvironment();
		$twigEnv->addGlobal("BR_USER_CAN_EDIT", true);
		$twigEnv->addGlobal("BR_USER_CAN_UNPUBLISH", true);
	}
}

$battleReportDetailTitle = "Battle Overview";

$availableDetails = array("timeline");
if (!empty($battleReportDetail)) {
    $battleReportDetail = strtolower($battleReportDetail);
    if (!in_array($battleReportDetail, $availableDetails))
        $battleReportDetail = "overview";
}
if (empty($battleReportDetail))
    $battleReportDetail = "overview";

switch ($battleReportDetail) {
    case "timeline":
        $battleReportDetailTitle = "Battle Timeline";
        break;
}

$app->render("show.html", array(
    "battleReport" => $battleReport,
    "battleReportDetail" => $battleReportDetail,
    "battleReportDetailTitle" => $battleReportDetailTitle
));