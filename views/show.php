<?php

if (empty($battleReportID) || $battleReportID == 0) {
    $app->render("brNotFound.html");
    $app->stop();
}

// Try to fetch the specified battle report
$battleReport = new Battle();
if ($battleReport->load($battleReportID, !User::isAdmin()) == false) {
    $app->render("brNotFound.html");
    $app->stop();
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