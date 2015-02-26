<?php

$app->status(200);
$app->contentType('application/json');

if (!$app->request->isPost() || $app->request->post('token') != BR_API_SLACK_TOKEN) {
	// Die silently
	$app->stop();
}

$db = Db::getInstance();

$latestBattle = $db->row(
	"select br.battleReportID, br.brTitle as title, br.brStartTime as startTime, br.brEndTime as endTime, br.brPublished as published, " .
		"br.brCreatorUserID as creatorUserID, ifnull(u.userName, '') as creatorUserName, " .
		"br.brUniquePilotsTeamA, br.brUniquePilotsTeamB, br.brUniquePilotsTeamC, " .
		"ifnull((select sum(c.priceTag) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
			"select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamA' limit 1" .
		")), 0.0) as iskLostTeamA, " .
		"ifnull((select sum(c.priceTag) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
			"select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamB' limit 1" .
		")), 0.0) as iskLostTeamB, " .
		"ifnull((select sum(c.priceTag) from brCombatants as c where c.brHidden = 0 and c.brBattlePartyID = (" .
			"select bp.brBattlePartyID from brBattleParties as bp where bp.battleReportID = br.battleReportID and bp.brTeamName = 'teamC' limit 1" .
		")), 0.0) as iskLostTeamC, " .
		"sys.solarSystemName, " .
		"(select count(commentID) from brComments as cm where cm.battleReportID = br.battleReportID and cm.commentDeleteTime is NULL) as commentCount, " .
		"(select count(videoID) from brVideos as v where v.battleReportID = br.battlereportID) as footageCount " .
	"from brBattles as br inner join mapSolarSystems as sys " .
		"on br.solarSystemID = sys.solarSystemID " .
		"left outer join brUsers as u on u.userID = br.brCreatorUserID " .
	"where br.brDeleteTime is NULL and br.brPublished = 1 " .
		($app->request->post('text') !== null && $app->request->post('text') != "" ? "and br.battleReportID = :brID " : "") .
	"order by br.brStartTime desc " .
	"limit 1",
	($app->request->post('text') !== null && $app->request->post('text') != "" ? array("brID" => $app->request->post('text')) : array())
);

if ($latestBattle === FALSE) {
	$app->stop();
}

$scheme = $_SERVER["REQUEST_SCHEME"];
if (empty($scheme)) {
	$scheme		= "http";
	if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on')
		$scheme	= "https";
}
$url = $scheme . "://" . $_SERVER["HTTP_HOST"] . "/";

$title = "Battle detected";
if (!empty($latestBattle["title"]))
	$title = "Battle: " . $latestBattle["title"];

$payload = array(
	"username" => (BR_OWNER !== "" ? (BR_OWNER . "'s ") : "") . "BattleReporter",
	"icon_url" => "http://image.eveonline.com/Corporation/" . BR_OWNERCORP_ID . "_64.png",
	"text" => "<" . $url . "show/" . $latestBattle["battleReportID"] . "|" . $title . "> in " . $latestBattle["solarSystemName"] . " on " . date("Y-m-d H:i", $latestBattle["startTime"])
);

$channel = "";
if ($app->request->post('channel_name') !== null) {
	$channel = "#" . $app->request->post('channel_name');
	$payload["channel"] = $channel;
	if ($payload["channel"] == "#privategroup") {
		echo "Cannot post to private groups, sorry.";
		$app->stop();
	}
}

$user = ""; // Currently unused ...
if ($app->request->post('user_name') !== null) {
	$user = "@" . $app->request->post('user_name');
}

try {
	Utils::curl(
		BR_API_SLACK_CHANNEL,
		array(
			"payload" => json_encode($payload)
		),
		array(
			"postParams" => true
		)
	);
} catch (Exception $ex) {
	$app->log->error("Exception caught while trying to post to Slack WebHook.\n" . $ex);
	echo "Could not post Battle to Slack" . (!empty($channel) ? ", channel #$channel" . "") . ".";
	$app->stop();
}

echo "Posted Battle" .
	(!empty($latestBattle["title"]) ? (" \"" . $latestBattle["title"] . "\"") : "") .
	" to Slack" .
	(!empty($channel) ? ", channel #$channel" . "") .
	".";
