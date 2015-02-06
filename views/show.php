<?php

if (empty($battleReportID) || $battleReportID == 0) {
    $app->render("brNotFound.html");
    $app->stop();
}

// Try to fetch the specified battle report
$battleReport = new Battle();
if ($battleReport->load($battleReportID) == false) {
    $app->render("brNotFound.html");
    $app->stop();
}

$app->render("show.html", array(
    "battleReport" => $battleReport
));