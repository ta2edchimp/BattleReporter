<?php

if (empty($battleReportID) || $battleReportID == 0) {
    $app->render("brNotFound.html");
    $app->stop();
}

// If the user is allowed to create battle reports,
// he'll also be allowed to view yet unpublished.
$showUnpublished = User::can('create');

// Try to fetch the specified battle report
$battleReport = new Battle();
if ($battleReport->load($battleReportID, !$showUnpublished) == false) {
    $app->render("brNotFound.html");
    $app->stop();
}

$app->render("show.html", array(
    "battleReport" => $battleReport
));