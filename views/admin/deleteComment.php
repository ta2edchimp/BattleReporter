<?php

$referer = $_SERVER["HTTP_REFERER"];
if (empty($referer) || strpos(strtolower($referer), strtolower($_SERVER["HTTP_HOST"])) === FALSE)
	$referer = "/";

if (!User::isLoggedIn() || !User::isAdmin() || empty($commentID) || BR_COMMENTS_ENABLED !== true)
	$app->redirect($referer);

$battleReportID = $db->single("select battleReportID from brComments where commentID = :commentID", array("commentID" => $commentID));
if ($battleReportID === FALSE)
	$app->redirect($referer);

$db->query(
	"update brComments " .
	"set commentDeleteTime = :deleteTime and commentDeleteUserID = :userID " .
	"where commentID = :commentID and battleReportID = :battleReportID",
	array(
		"deleteTime" => (new DateTime("now", new DateTimeZone("UTC")))->format("Y-m-d G:i:s"),
		"userID" => User::getUserID(),
		"commentID" => $commentID,
		"battleReportID" => $battleReportID
	)
);

$app->redirect("/show/$battleReportID/comments");
