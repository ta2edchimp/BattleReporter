<?php

$referer = $_SERVER["HTTP_REFERER"];
if (empty($referer) || strpos(strtolower($referer), strtolower($_SERVER["HTTP_HOST"])) === FALSE)
	$referer = "/";

if (!User::isLoggedIn() || BR_COMMENTS_ENABLED !== true)
	$app->redirect($referer);

$commentMessage = $app->request->post("CommentMessage");
if ($commentMessage === null || empty($commentMessage))
	$app->redirect($referer);


if (empty($battleReportID) || $battleReportID <= 0)
	throw new Exception("Unknown BattleReport with ID $battleReportID!", 404);

$result = $db->single(
	"select * from brBattles where battleReportID = :battleReportID and brPublished = 1 and brDeleteTime is NULL",
	array(
		"battleReportID" => $battleReportID
	)
);
if ($result === FALSE)
	throw new Exception("Unknown BattleReport with ID $battleReportID!", 404);

$result = $db->query(
	"insert into brComments " .
	"(battleReportID, commentUserID, commentMessage, commentTime) " .
	"values " .
	"(:battleReportID, :commentUserID, :commentMessage, :commentTime)",
	array(
		"battleReportID" => $battleReportID,
		"commentUserID" => User::getUserID(),
		"commentMessage" => $commentMessage,
		"commentTime" => (new DateTime())->format("Y-m-d G:i:s")
	)
);
if ($result === FALSE || $result != 1)
	throw new Exception("Something bad happened while saving comment to database");

$commentID = $db->lastInsertId();

$app->redirect("/show/$battleReportID/comments#comment-$commentID");
