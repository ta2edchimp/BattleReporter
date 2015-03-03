<?php

class Slack {
	
	private $channelUrl;
	
	public function __construct($channelUrl = "") {
		
		if (empty($channelUrl))
			throw new Exception("Could not instantiate Slack Integration, no Channel URL specified!");
		
		$this->channelUrl	= $channelUrl;
		
	}
	
	public function postBattleWithID($battleReportID = 0, array $options = array()) {
		
		if ($battleReportID < 1)
			return null;
		
		$result = Battle::getList(array(
			"onlyPublished" => (isset($options["onlyPublished"]) && is_bool($options["onlyPublished"]) ? $options["onlyPublished"] : true),
			"id" => $battleReportID
		));
		if (!is_int($battleReportID) || $battleReportID < 1 || $result === NULL || $result === FALSE || count($result) < 1)
			return null;
		
		$battleToShow = $result[0];
		
		
		$payload = array();
		
		// Message's Author Name
		$payload["username"] = (BR_OWNER !== "" ? (BR_OWNER . "'s ") : "") . "BattleReporter";
		
		// Message's Author Icon
		$payload["icon_url"] = "http://image.eveonline.com/Corporation/" . BR_OWNERCORP_ID . "_64.png";
		
		// Build the link
		$brLink_scheme			= $_SERVER["REQUEST_SCHEME"];
		if (empty($brLink_scheme)) {
			
			$brLink_scheme		= "http";
			if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on')
				$brLink_scheme	= "https";
			
		}
		$brLink = $brLink_scheme . "://" . $_SERVER["HTTP_HOST"] . "/show/" . $battleToShow["battleReportID"];
		
		// Build the title
		$brTitle = "Battle of " . $battleToShow["solarSystemName"];
		if (!empty($battleToShow["title"]))
			$brTitle = "Battle: " . $battleToShow["title"];

		// Message text
		$payload["attachments"] = array();
		$payload["attachments"][0] = array();
		$payload["attachments"][0]["title"] = $brTitle;
		$payload["attachments"][0]["title_link"] = $brLink;
		
		// Extra Info Fields
		$totalIskDestroyed = ($battleToShow["iskLostTeamA"] + $battleToShow["iskLostTeamB"] + $battleToShow["iskLostTeamC"]);
		if ($totalIskDestroyed < 1000000000000) {
			if ($totalIskDestroyed < 1000000000) {
				$totalIskDestroyed = number_format($totalIskDestroyed / 1000000, 2, '.', ',') . " million";
			} else {
				$totalIskDestroyed = number_format($totalIskDestroyed / 1000000000, 2, '.', ',') . " billion";
			}
		} else {
			$totalIskDestroyed = number_format($totalIskDestroyed / 1000000000000, 2, '.', ',') . " trillion";
		}
		$totalIskDestroyed .= " ISK";
		$payload["attachments"][0]["fields"] = array(
			// Timespan
			array(
				"title" => "Timespan",
				"value" => date("Y-m-d H:i", $battleToShow["startTime"]) . " - " . date("H:i", $battleToShow["endTime"]),
				"short" => true
			),
			// Location
			array(
				"title" => "Solar System",
				"value" => $battleToShow["solarSystemName"],
				"short" => true
			),
			// Total pilots involved
			array(
				"title" => "Total Pilots Involved",
				"value" => ($battleToShow["brUniquePilotsTeamA"] + $battleToShow["brUniquePilotsTeamB"] + $battleToShow["brUniquePilotsTeamC"]),
				"short" => true
			),
			// Total ISK destroyed
			array(
				"title" => "Total ISK Destroyed",
				"value" => $totalIskDestroyed,
				"short" => true
			)
		);
		
		// Invoking User's info
		if (isset($options["invUserID"]) && isset($options["invUserName"])) {
			
			$payload["text"] = "<@" . $options["invUserID"] . "|" . $options["invUserName"] . "> posted:";
			
		}
		
		// Set fallback information
		$payload["fallback"] = "BattleReport posted: " . (!empty($battleToShow["title"]) ? $battleToShow["title"] : ("Battle of " . $battleToShow["solarSystemName"]));
		
		// Colored bar
		$efficiency	= 0.0;
		$totalLost	= $battleToShow["iskLostTeamA"] + $battleToShow["iskLostTeamB"] + $battleToShow["iskLostTeamC"];
		if ($totalLost > 0.0)
			$efficiency = 1.0 - $battleToShow["iskLostTeamA"] / $totalLost;
		
		if ($efficiency == 1)
			$payload["attachments"][0]["color"] = "#5cb85c";
		elseif ($efficiency > 0.5)
			$payload["attachments"][0]["color"] = "#5bc0de";
		elseif ($efficiency > 0.1)
			$payload["attachments"][0]["color"] = "#f0ad4e";
		else
			$payload["attachments"][0]["color"] = "#d9534f";
		
		// Channel redirect
		if (isset($options["channel"]))
			$payload["channel"] = $options["channel"];
		
		
		// Send Result to Slack Channel
		try {
			Utils::curl(
				$this->channelUrl,
				array(
					"payload" => json_encode($payload)
				),
				array(
					"postParams" => true
				)
			);
		} catch (Exception $ex) {
			$app->log->error("Exception caught while trying to post to Slack WebHook.\n" . $ex);
			return null;
		}
		
		return $battleToShow;
		
	}
	
}
