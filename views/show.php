<?php

if (empty($battleReportID) || $battleReportID == 0) {
	$app->render("brNotFound.html");
	$app->stop();
}

// Try to fetch the specified battle report
$battleReport = new Battle();

if ($battleReport->load($battleReportID, false) === false) {
	$app->render("brNotFound.html");
	$app->stop();
}

// If unpublished and/or no access ... yell "not found"
if ($battleReport->published === false) {

	// Users with general "edit" permission may see an unpublished battle report
	if (!User::isLoggedIn() || !User::can("edit")) {
		$app->render("brNotFound.html");
		$app->stop();
	}

}

// ... but only admins and owners may actually edit this one.
$userIsAdminOrDirector = User::isAdmin() || User::is("Director");
if (User::isLoggedIn() && ($userIsAdminOrDirector || $battleReport->creatorUserID == User::getUserID())) {
	$twigEnv = $app->view()->getEnvironment();
	$twigEnv->addGlobal("BR_USER_CAN_EDIT", true);
	$twigEnv->addGlobal("BR_USER_CAN_UNPUBLISH", true);
	$twigEnv->addGlobal("BR_USER_CAN_DELETE", $userIsAdminOrDirector);
}

$battleReportDetailTitle = "Battle Overview";

$availableDetails = array("timeline", "damage", "json", "jsonp");
if (BR_COMMENTS_ENABLED === true)
	$availableDetails[] = "comments";
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
	case "comments":
		$battleReportDetailTitle = "Comments";
		break;
}



// Compile preview data
$previewMeta = array();

$previewNumberOfPilots = $battleReport->teamA->uniquePilots . " vs. " . $battleReport->teamB->uniquePilots . ($battleReport->teamC->uniquePilots > 0 ? (" vs. " . $battleReport->teamC->uniquePilots) : "") . " pilots";

$previewISKdestroyed = $battleReport->totalLost;
if ($previewISKdestroyed < 1000000000000) {
	if ($previewISKdestroyed < 1000000000) {
		$previewISKdestroyed = number_format($previewISKdestroyed / 1000000, 2, '.', ',') . " million ISK destroyed";
	} else {
		$previewISKdestroyed = number_format($previewISKdestroyed / 1000000000, 2, '.', ',') . " billion ISK destroyed";
	}
} else {
	$previewISKdestroyed = number_format($previewISKdestroyed / 1000000000000, 2, '.', ',') . " trillion ISK destroyed";
}

$previewMeta['title'] = (empty($battleReport->title) ? ("Battle in " . $battleReport->solarSystemName) : $battleReport->title);
$previewMeta["description"] = $previewNumberOfPilots . ", " . $previewISKdestroyed . " at " . number_format($battleReport->teamA->brEfficiency, 2, ".", ",") . "% efficiency in " . $battleReport->solarSystemName . " on " . date('Y-m-d H:i', $battleReport->startTime) . " - " . date('H:i', $battleReport->endTime);
$previewMeta['image'] = "//image.eveonline.com/corporation/" . BR_OWNERCORP_ID . "_128.png";

if ($battleReportDetail === "json") {

	Utils::renderJSON(array( "previewMeta" => $previewMeta, "battleReport" => $battleReport ));

} else if ($battleReportDetail === "jsonp") {

	Utils::renderJSONP(array( "previewMeta" => $previewMeta, "battleReport" => $battleReport ), $app->request->get('callback'));

} else {

	$app->render("show.html", array(
		"BR_PAGE_SHOW" => true,
		"previewMeta" => $previewMeta,
		"battleReport" => $battleReport,
		"battleReportDetail" => $battleReportDetail,
		"battleReportDetailTitle" => $battleReportDetailTitle
	));

}
