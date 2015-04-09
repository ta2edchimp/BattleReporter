<?php

require_once ("$basePath/classes/API/Slack.php");

$app->status(200);
if ($app->request->isPost())
	$app->contentType('application/json');

$params = $app->request->post();
if ($app->request->isGet()) {
	$params = $app->request->get();
}

if (!$app->request->isPost() || BR_API_SLACK_ENABLED !== true || $app->request->post('token') != BR_API_SLACK_TOKEN) {
	// Die silently
	$app->stop();
}


/**

	Init

**/
$command		= "/battlereporter";

$mode			= "latest";
$battleReportID	= -1;

$channelName	= "";

$slackOpts		= array();

if (isset($params["channel_name"]) && isset($params["channel_id"])) {
	
	$channelName = "#" . $params['channel_name'];
	
	if (strtolower($channelName) == "#privategroup") {
		echo "_Sorry_, I cannot post to private groups.";
		$app->stop();
	}
	
	$slackOpts["channel"] = $params["channel_id"];
	
}


if (isset($params["user_id"]) && !empty($params["user_id"]) && isset($params["user_name"]) && !empty($params["user_name"])) {
	$slackOpts["invUserID"]	= $params["user_id"];
	$slackOpts["invUserName"]	= $params["user_name"];
}


if (isset($params["command"]) && !empty($params["command"])) {
	$command	= $params["command"];
}


/**

	Check current Command and its Argument

**/
if (isset($params['text'])) {
	
	if (is_int($params['text']) || intval($params['text']) > 0) {
		
		$mode			= 'show';
		$battleReportID	= intval($params['text']);
		
	} else {
		
		$mode = strtolower($params['text']);
		if (empty($mode))
			$mode = 'latest';
		
		if ($mode !== 'latest' && $mode !== 'list') {
			
			echo "*" . (BR_OWNER !== '' ? (BR_OWNER . "'s ") : "") . "BattleReporter Help*\n\n" .
				 "Use `$command` without any argument or together with `latest` to post the latest BattleReport.\n\n" .
				 "Use `$command list` to list the last up to 25 BattleReports.\n\n" .
				 "Use `$command 123` to post the BattleReport with ID `123`.\n\n" .
				 "*BattleReporter cannot post to private groups!*";
			
			$app->stop();
			
		}
		
	}
	
}


/**

	List the last (max.: 25) published BattleReports

**/
if ($mode === 'list') {
	
	$result = Battle::getList(array(
		"onlyPublished" => true,
		"count" => 25
	));
	
	if ($result === NULL || $result === FALSE) {
		echo "_Sorry_, something went wrong.";
		$app->stop();
	}
	
	if (count($result) < 1) {
		echo "_Sorry_, but there aren't any published BattleReports, yet.";
		$app->stop();
	}
	
	$sampleID		= 0;
	$sampleTitle	= "";
	
	foreach ($result as $battle) {
		
		if ($sampleID == 0) {
			$sampleID = $battle["battleReportID"];
			$sampleTitle = (empty($battle["title"]) ? ("Battle in " . $battle["solarSystemName"]) : $battle["title"]);
		}
		
		echo "*" . $battle["battleReportID"] . "* - " .
			 (empty($battle["title"]) ? ("*Battle in " . $battle["solarSystemName"] . "*\n") : ("*" . $battle["title"] . "*\nin " . $battle["solarSystemName"])) . 
			 " on " . date("Y-m-d H:i", $battle["startTime"]) .
			 "\n\n";
		
	}
	
	if ($sampleID == 0) {
		echo "*Hint:*\nType `$command 123` to post BattleReport #123 (example).";
	} else {
		echo "*Hint:*\nType `$command $sampleID` to post BattleReport #$sampleID\n*$sampleTitle*.";
	}
	
	
	$app->stop();
	
}


/**

	Show the latest or another specified BattleReport

**/
$battleToShow = null;

if ($mode === 'latest') {
	
	$result = Battle::getList(array(
		"onlyPublished" => true,
		"count" => 1
	));
	
	if ($result === NULL || $result === FALSE || count($result) !== 1) {
		echo "_Sorry_, something went wrong.";
		$app->stop();
	}
	
	$battleToShow = $result[0];
	
	$mode			= 'show';
	$battleReportID	= $battleToShow["battleReportID"];
	
}

if ($mode !== 'show') {
	echo "*Unknown command `$mode`*.";
	$app->stop();
}

$slack = new Slack(BR_API_SLACK_CHANNEL);
$battleToShow = $slack->postBattleWithID($battleReportID, $slackOpts);

if ($battleToShow === null) {
	echo "*Invalid or unknown BattleReport ID* `$battleReportID`.";
	$app->stop();
}


/**

	Send Response to user

**/
echo "Posted Battle" .
	 (!empty($battleToShow["title"]) ? (" \"" . $battleToShow["title"] . "\"") : (" in " . $battleToShow["solarSystemName"])) .
	 " to Slack" .
	 (!empty($channelName) && strtolower($channelName) != "#directmessage" ? (", channel `" . $channelName . "`") : "") .
	 ".";
