<?php

class Battle {
	
	public $battleReportID = 0;
	
	public $title = "";
	public $summary = "";
	
	public $killsTotal = 0;
	
	public $solarSystemID = 0;
	public $solarSystemName = "";
	
	public $startTime = 0;
	public $endTime = 0;
	public $timeSpan = "";
	
	public $teamA;
	public $teamB;
	public $teamC;
	
	public $published = false;
	private $deleted = false;
	
	public $totalPilots = 0;
	public $totalLost = 0.0;
	
	public $creatorUserID = -1;
	public $creatorUserName = "";
	public $createTime = 0;
	
	public $footage = array();
	public $commentCount = 0;
	
	
	public function __construct() {
		
		$this->creatorUserID = User::getUserID();
		$this->creatorUserName = User::getUserName();
		
		$this->teamA = new BattleParty("teamA");
		$this->teamB = new BattleParty("teamB");
		$this->teamC = new BattleParty("teamC");
		
	}
	
	
	public function load($id, $onlyPublished = true, $toBeEdited = false) {
		
		$db = Db::getInstance();
		
		// Fetch corresponding records from database
		$result = $db->row(
			"select br.*, ifnull(u.userName, 'Anonymous') as brCreatorUserName " .
			"from brBattles as br left outer join brUsers as u on u.userID = br.brCreatorUserID " .
			"where br.battleReportID = :battleReportID and br.brDeleteTime is NULL" .
			($onlyPublished ? " and br.brPublished = 1" : ""),
			array(
				"battleReportID" => $id
			)
		);
		if ($result == NULL)
			return false;
		
		// Load battle parties ...
		if (!$this->teamA->load($id, $toBeEdited) || !$this->teamB->load($id, $toBeEdited) || !$this->teamC->load($id, $toBeEdited))
			return false;
		
		// Assign properties
		$this->creatorUserID	= $result["brCreatorUserID"];
		$this->creatorUserName	= $result["brCreatorUserName"];
		$this->createTime		= $result["brCreateTime"];
		$this->battleReportID   = $result["battleReportID"];
		$this->title            = $result["brTitle"];
		$this->summary			= $result["brSummary"];
		$this->startTime        = $result["brStartTime"];
		$this->endTime          = $result["brEndTime"];
		$this->solarSystemID    = $result["solarSystemID"];
		$this->published        = $result["brPublished"] == 1 ? true : false;
		
		// Load associated footage
		$this->loadFootage();
		
		// Sort battle parties
		// battle parties' objects hold a sort method,
		// but its use is not necessary anymore, when
		// loading a complete battle party from the database.
		
		// Update certain properties
		$this->updateDetails();
		
		// Update comment count property
		$commentCount = $db->single(
			"select count(commentID) as commentCount " .
			"from brComments " .
			"where battleReportID = :battleReportID and commentDeleteTime is NULL",
			array(
				"battleReportID" => $this->battleReportID
			)
		);
		if ($commentCount !== FALSE)
			$this->commentCount = $commentCount;
		
		return true;
		
	}
	
	
	public function save() {
		
		$this->updateDetails();
		
		$db = Db::getInstance();
		
		$values = array(
			"title" => $this->title,
			"summary" => $this->summary,
			"startTime" => $this->startTime,
			"endTime" => $this->endTime,
			"solarSystemID" => $this->solarSystemID,
			"published" => $this->published ? 1 : 0,
			"brUniquePilotsTeamA" => $this->teamA->uniquePilots,
			"brUniquePilotsTeamB" => $this->teamB->uniquePilots,
			"brUniquePilotsTeamC" => $this->teamC->uniquePilots
		);
		if ($this->deleted === true) {
			$values["brDeleteUserID"] = User::getUserID();
			$values["brDeleteTime"] = time();
		}
		
		// Save basic battle report properties
		if ($this->battleReportID <= 0) {
			$values["brCreatorUserID"] = $this->creatorUserID;
			$result = $db->query(
				"insert into brBattles ".
				"(brTitle, brSummary, brStartTime, brEndTime, SolarSystemID, brPublished, brCreatorUserID, " .
					"brUniquePilotsTeamA, brUniquePilotsTeamB, brUniquePilotsTeamC" .
					($this->deleted ? ", brDeleteUserID, brDeleteTime" : "") .
				") " .
				"values " .
				"(:title, :summary, :startTime, :endTime, :solarSystemID, :published, :brCreatorUserID, " .
					":brUniquePilotsTeamA, :brUniquePilotsTeamB, :brUniquePilotsTeamC" .
					($this->deleted ? ", :brDeleteUserID, :brDeleteTime" : "") .
				")",
				$values,
				true	// Return last inserted row's ID instead of affected rows' count
			);
			if ($result > 0)
				$this->battleReportID = $result;
		} else {
			$values["battleReportID"] = $this->battleReportID;
			$result = $db->query(
				"update brBattles " .
				"set brTitle = :title, brSummary = :summary, brStartTime = :startTime, brEndTime = :endTime, " .
					"SolarSystemID = :solarSystemID, brPublished = :published, " .
					"brUniquePilotsTeamA = :brUniquePilotsTeamA, " .
					"brUniquePilotsTeamB = :brUniquePilotsTeamB, " .
					"brUniquePilotsTeamC = :brUniquePilotsTeamC" .
					($this->deleted ? ", brDeleteUserID = :brDeleteUserID, brDeleteTime = :brDeleteTime " : " ") .
				"where battleReportID = :battleReportID",
				$values
			);
		}
		
		// Save associated footage
		$this->saveFootage();
		
		// Save the battle parties
		$this->teamA->save($this->battleReportID);
		$this->teamB->save($this->battleReportID);
		$this->teamC->save($this->battleReportID);

		// Save additional data, that requires initial saving in before
		$this->saveAdditionalData();

		// Update statistical values
		$this::updateStats($this->battleReportID);
		
	}

	public function saveAdditionalData() {
		$this->teamA->saveAdditionalData();
		$this->teamB->saveAdditionalData();
		$this->teamC->saveAdditionalData();
	}
	
	public function publish() {
		$this->published = true;
		$this->save();
	}
	
	public function unpublish() {
		$this->published = false;
		$this->save();
	}
	
	public function savePreparation() {
		$this->published = false;
		$this->save();
	}
	
	public function delete() {
		$this->deleted = true;
		$this->save();
	}
	
	
	public function updateDetails() {
		if ($this->solarSystemID > 0)
			$this->solarSystemName = SolarSystem::getByID($this->solarSystemID);
		else
			$this->solarSystemName = "";
		
		$this->teamA->updateDetails();
		$this->teamB->updateDetails();
		$this->teamC->updateDetails();
		
		$this->killsTotal = $this->teamA->losses + $this->teamB->losses + $this->teamC->losses;
		
		if ($this->startTime > 0 && $this->endTime > 0)
			$this->timeSpan = date("Y-m-d H:i", $this->startTime) . " - " . date("H:i", $this->endTime);
		else
			$this->timeSpan = "";
		
		$this->totalPilots = $this->teamA->uniquePilots + $this->teamB->uniquePilots + $this->teamC->uniquePilots;
		$this->totalLost = $this->teamA->brIskLost + $this->teamB->brIskLost + $this->teamC->brIskLost;
		
	}
	
	
	public function applyChanges($changes = array()) {
		
		foreach ($changes as $combatantID => $change) {
			$allTeams = array("teamA", "teamB", "teamC");
			$currentTeam = "";
			
			$combatant = null;
			
			if ($combatantID >= 0) {
				foreach ($allTeams as $team) {
					foreach ($this->$team->members as $key => $member) {
						if (is_object($member) && $member->brCombatantID == $combatantID) {
							$currentTeam = $team;
							$combatant = $member;
							
							if (isset($change->teamName) && !empty($change->teamName) && $change->teamName != $currentTeam) {
								unset($this->$team->members[$key]);
								$currentTeam = $change->teamName;
								$this->$currentTeam->members[] = $combatant;
							}
							
							break 2;
						}
					}
				}
			} else {
				if (isset($change->added) && $change->added === true
					&& isset($change->teamName) && !empty($change->teamName)
					&& isset($change->combatantInfo)
					&& (!isset($change->brDeleted) || $change->brDeleted !== true)) {
					
					$corpName = "Unknown";
					$alliName = "";
					
					if (isset($change->combatantInfo->corporationName) && !empty($change->combatantInfo->corporationName))
						$corpName = $change->combatantInfo->corporationName;
					if (isset($change->combatantInfo->allianceName) && !empty($change->combatantInfo->allianceName))
						$alliName = $change->combatantInfo->allianceName;
					
					$combatant = new Combatant(
						array(
							"characterID" => -1,
							"characterName" => "Unknown",
							"corporationID" => -1,
							"corporationName" => $corpName,
							"allianceID" => (empty($alliName) ? 0 : -1),
							"allianceName" => $alliName,
							"shipTypeID" => 0,
							"shipTypeName" => $change->combatantInfo->shipTypeName,
							"brManuallyAdded" => true
						)
					);
					
					$currentTeam = $change->teamName;
					
					if ($combatant !== null)
						$this->$currentTeam->members[] = $combatant;
					
				}
			}
			
			if ($combatant === null)
				continue;
			
			if (isset($change->brHidden))
				$combatant->brHidden = $change->brHidden;
			
			if (isset($change->brDeleted))
				$combatant->brDeleted = $change->brDeleted;
		}
		
		return true;
	}
	
	
	public function import($importedKills) {
		
		$this->append($importedKills, true);
		
	}
	
	public function append($importedKills, $importMode = false) {
		
		if (count($importedKills) <= 0)
			return;
		
		$earliestKillTime = 0;
		$latestKillTime = 0;
		
		foreach ($importedKills as $impKill) {
			
			$existantBattleID = self::getBattleReportIDByKillID($impKill->killID);
			// Check if that kill has been imported already
			if ($existantBattleID !== null) {
				
				// If in "import mode", completely abort ...
				if ($importMode === true)
					throw new Exception("The fetched events are already part of another already existing BattleReport.");
				
				// If in "append mode", omit this kill,
				// regardless of the battle report it is already
				// in, it must not be imported again.
				continue;
				
			}
			
			$kill = Kill::fromImport($impKill);
			
			if ($kill !== null) {
				
				// Per default, the victim is member of teamB
				$tgt = "teamB";
				
				// Oh no, its a loss for the owner corp :(
				if ($kill->victim->corporationID == BR_OWNERCORP_ID)
					$tgt = "teamA";
				
				if ($importMode) {
					// In import mode, simply append victim as combatant to the designated battle party ...
					$this->$tgt->addOrUpdate($kill->victim);
				} else {
					// When just appending, first check whether to update a battle party's existing member ...
					$tempMember = $this->teamA->getMember($kill->victim);
					if ($tempMember === null) {
						$tempMember = $this->teamB->getMember($kill->victim);
						if ($tempMember === null) {
							$tempMember = $this->teamC->getMember($kill->victim);
						}
					}
					if ($tempMember === null) {
						$this->$tgt->add($kill->victim);
					} else {
						$tempMember->update($kill->victim);
					}
				}
				
				foreach ($kill->attackers as $attacker) {
					$tgt = "teamB";
					if ($attacker->corporationID == BR_OWNERCORP_ID)
						$tgt = "teamA";
					
					// Same distinguishing of import / append mode as above, but for the attacking combatants
					if ($importMode) {
						$this->$tgt->addOrUpdate($attacker);
					} else {
						$tempMember = $this->teamA->getMember($attacker);
						if ($tempMember === null) {
							$tempMember = $this->teamB->getMember($attacker);
							if ($tempMember === null) {
								$tempMember = $this->teamC->getMember($attacker);
							}
						}
						if ($tempMember === null) {
							$this->$tgt->add($attacker);
						} else {
							$tempMember->update($attacker);
						}
					}
				}
				
				if (isset($kill->killTime)) {
					$killTime = $kill->killTime;
					if ($earliestKillTime == 0 || $killTime < $earliestKillTime)
						$earliestKillTime = $killTime;
					if ($killTime > $latestKillTime)
						$latestKillTime = $killTime;
				}
				
				if (isset($kill->solarSystemID))
					$this->solarSystemID = $kill->solarSystemID;
				
			}
		}
		
		if ($importMode === false) {
			// ... and check whether to kick some of the combatants ...
			$teams = array('teamA', 'teamB', 'teamC');
			foreach ($teams as $team) {
				foreach ($this->$team->members as $combatant) {
					
					if ($combatant->died === false)
						continue;
					
					// when they did not die within the new timespan ...
					if ($combatant->killTime < $this->startTime || $combatant->killTime > $this->endTime) {
						$combatant->removeFromDatabase();
					} else {
						$killTime = $combatant->killTime;
						if ($earliestKillTime == 0 || $killTime < $earliestKillTime)
							$earliestKillTime = $killTime;
						if ($killTime > $latestKillTime)
							$latestKillTime = $killTime;
					}
					
				}
			}
		}
		
		$this->startTime	= $earliestKillTime;
		$this->endTime		= $latestKillTime;
		
		$this->teamA->sort();
		$this->teamB->sort();
		$this->teamC->sort();
		
		$this->updateDetails();
		
	}
	
	
	public function refetch($newTimeSpan = "") {
		
		if (empty($newTimeSpan) || !KBFetch::testTimespanPattern($newTimeSpan))
			return false;
		
		$allKills = KBFetch::fetchKills(
			array(
				"corporationID"	=> BR_OWNERCORP_ID,
				"solarSystemID"	=> $this->solarSystemID,
				"startTime"		=> KBFetch::getZKBStartTime($newTimeSpan),
				"endTime"		=> KBFetch::getZKBEndTime($newTimeSpan)
			)
		);
		
		// Get timestamps of the given timespan ...
		$this->startTime	= KBFetch::getDateTime($newTimeSpan)->getTimestamp();
		$this->endTime		= KBFetch::getDateTime($newTimeSpan, true)->getTimestamp();
		
		$this->append($allKills);
		
		return true;
		
	}
	
	
	public function getTimeline() {
		
		$timeline = array();
		
		$teams = array("teamA", "teamB", "teamC");
		foreach ($teams as $team) {
			foreach ($this->$team->members as $combatant) {
				if ($combatant->died) {
					$timeline[] = array(
						"occurredToTeamA" => ($team == "teamA"),
						"occurredToTeamB" => ($team == "teamB"),
						"occurredToTeamC" => ($team == "teamC"),
						"timeStamp" => $combatant->killTime,
						"timeStampString" => date("H:i", $combatant->killTime),
						"killID" => $combatant->killID,
						"combatantEventOccuredTo" => $combatant
					);
				}
			}
		}
		usort($timeline, 'Battle::timelineSorter');
		
		return $timeline;
		
	}
	
	
	public function getComments() {
		
		$db = Db::getInstance();
		
		if ($this->battleReportID <= 0 || BR_COMMENTS_ENABLED !== true)
			return array();
		
		$results = $db->query(
			"select c.*, u.userID, u.userName, u.characterID, u.corporationID, cc.corporationName, u.allianceID, al.allianceName " .
			"from brComments as c inner join brUsers as u " .
				"on c.commentUserID = u.userID left outer join brCorporations as cc " .
				"on u.corporationID = cc.corporationID left outer join brAlliances as al " .
				"on u.allianceID = al.allianceID " .
			"where c.battleReportID = :battleReportID and c.commentDeleteTime is NULL " .
			"order by c.commentTime asc",
			array(
				"battleReportID" => $this->battleReportID
			)
		);
		
		if ($results === FALSE)
			return array();
		
		return $results;
		
	}
	
	
	public function loadFootage() {
		
		if ($this->battleReportID <= 0)
			return;
		
		$db = Db::getInstance();
		
		$results = $db->query(
			"select * from brVideos " .
			"where battleReportID = :battleReportID " .
			"order by videoID",
			array(
				"battleReportID" => $this->battleReportID
			)
		);
		
		if ($results === NULL)
			return;
		
		foreach ($results as $video) {
			$embedVideoUrl = self::getEmbedVideoUrl($video["videoUrl"]);
			if (empty($embedVideoUrl))
				continue;
			
			$footage = array(
				"id" => $video["videoID"],
				"index" => (count($this->footage) + 1),
				"url" => $embedVideoUrl
			);
			
			if (isset($video["videoPoVCombatantID"]) && !empty($video["videoPoVCombatantID"])) {
				$combatant = null;
				$cmbtData = $db->row(
					"select c.*, ifnull(cc.corporationName, 'Unknown') as corporationName, ifnull(a.allianceName, '') as allianceName " .
					"from invGroups as g right outer join invTypes as t " .
						"on g.groupID = t.groupID " .
					"right outer join brCombatants as c " .
						"on t.typeID = c.shipTypeID " .
					"left outer join brCorporations as cc " .
						"on c.corporationID = cc.corporationID " .
					"left outer join brAlliances as a " .
						"on c.allianceID = a.allianceID " .
					"where c.brCombatantID = :brCombatantID",
					array(
						"brCombatantID" => $video["videoPoVCombatantID"]
					)
				);
				
				if ($cmbtData !== FALSE)
					$combatant = new Combatant($cmbtData);
				
				if ($combatant !== null) {
					$footage["combatantID"] = $video["videoPoVCombatantID"];
					$footage["combatant"] = $combatant;
				}
			}
			
			$this->footage[] = $footage;
		}
		
	}
	
	public function saveFootage() {
		
		if ($this->battleReportID <= 0)
			return;
		
		$this->removeFootageFromDb();
		
		$db = Db::getInstance();
		
		foreach ($this->footage as $video) {
			if (!isset($video["url"]) || empty($video["url"]))
				continue;
			
			$params = array(
				"battleReportID" => $this->battleReportID,
				"videoUrl" => $video["url"]
			);
			
			$isPoV = (isset($video["combatantID"]) && !empty($video["combatantID"]));
			if ($isPoV === true)
				$params["videoPoVCombatantID"] = $video["combatantID"];
			
			$db->query(
				"insert into brVideos " .
				"(battleReportID, videoUrl" . ($isPoV === true ? ", videoPoVCombatantID" : "") . ") " .
				"values " .
				"(:battleReportID, :videoUrl" . ($isPoV === true ? ", :videoPoVCombatantID" : "") . ")",
				$params
			);
		}
		
	}
	
	public function addFootage($video = array()) {
		
		if (!isset($video["url"]) || empty($video["url"]))
			return;
		
		$embedUrl = self::getEmbedVideoUrl($video["url"]);
		if (empty($embedUrl))
			return;
		
		$footage = array(
			"url" => $embedUrl
		);
		if (isset($video["combatantID"]) && !empty($video["combatantID"]))
			$footage["combatantID"] = $video["combatantID"];
		
		$this->footage[] = $footage;
		
	}
	
	private function removeFootageFromDb() {
		
		if ($this->battleReportID <= 0)
			return;
		
		$db = Db::getInstance();
		
		$db->query(
			"delete from brVideos where battleReportID = :battleReportID",
			array(
				"battleReportID" => $this->battleReportID
			)
		);
		
	}
	
	public function removeFootage() {
		
		if ($this->battleReportID <= 0)
			return;
		
		$this->removeFootageFromDb();
		$this->footage = array();
		
		return;
		
	}
	
	public function toJSON() {
		
		return json_encode($this->toArray());
		
	}
	
	private function toArray() {
		
		return array(
			"type" => "battle",
			"battleReportID" => $this->battleReportID,
			"title" => $this->title,
			"killsTotal" => $this->killsTotal,
			"startTime" => $this->startTime,
			"endTime" => $this->endTime,
			"timeSpan" => $this->timeSpan,
			"solarSystemName" => $this->solarSystemName,
			"teamA" => $this->teamA->toArray(),
			"teamB" => $this->teamB->toArray(),
			"teamC" => $this->teamC->toArray()
		);
		
	}
	
	
	public static function getBattleReportIDByKillID($killID = "") {
		if (empty($killID))
			return null;
		
		$db = Db::getInstance();
		
		$id = $db->single(
			"select br.battleReportID " .
			"from brBattles as br inner join brBattleParties as bp " .
				" on br.battleReportID = bp.battleReportID " .
				" inner join brCombatants as c " .
				" on bp.brBattlePartyID = c.brBattlePartyID " .
			"where c.killID = :killID and br.brDeleteTime is NULL " .
			"limit 1",
			array(
				"killID" => $killID
			)
		);
		if ($id === FALSE)
			return null;
		
		return $id;
	}
	
	public static function timelineSorter($a, $b) {
		
		$combatantA = $a["combatantEventOccuredTo"];
		$combatantB = $b["combatantEventOccuredTo"];
		
		if ($combatantA->killTime == $combatantB->killTime)
			return Combatant::sorter($combatantA, $combatantB);
		
		return $combatantA->killTime < $combatantB->killTime ? -1 : 1;
	}
	
	public static function getList(array $options = array()) {
		
		$params			= array();
		
		$onlyPublished	= isset($options["onlyPublished"]) && is_bool($options["onlyPublished"]) ? $options["onlyPublished"] : true;
		$onlyIDs		= isset($options["onlyIDs"]) && is_bool($options["onlyIDs"]) ? $options["onlyIDs"] : false;
		$id				= isset($options["id"]) && is_int($options["id"]) ? intval($options["id"]) : null;
		$limit			= "";
		
		if ($id !== null) {
			$limit = " limit 1";
			$params["brID"] = $id;
		} else {
			if (isset($options["count"]) && is_int($options["count"])) {
				$limit = " limit :limit";
				$params["limit"] = intval($options["count"]);
			}
			if (isset($options["page"]) && is_int($options["page"])) {
				if (!isset($options["count"]) || empty($options["count"])) {
					$params["limit"] = 10;
				}
				$limit = " limit :offset, :limit";
				$params["offset"] = (intval($options["page"]) - 1) * $params["limit"];
			}
		}
		
		$db = Db::getInstance();
		
		return $db->query(
			"select br.battleReportID " .
				($onlyIDs === true ? "" : (", br.brTitle as title, br.brSummary as summary, br.brStartTime as startTime, br.brEndTime as endTime, br.brPublished as published, " .
				"br.brCreatorUserID as creatorUserID, ifnull(u.userName, '') as creatorUserName, " .
				"br.brUniquePilotsTeamA, br.brUniquePilotsTeamB, br.brUniquePilotsTeamC, " .
				"teamA.brIskLost as iskLostTeamA, teamB.brIskLost as iskLostTeamB, teamC.brIskLost as iskLostTeamC, " .
				"teamA.brEfficiency as efficiency, " .
				"sys.solarSystemName, " .
				"(select count(commentID) from brComments as cm where cm.battleReportID = br.battleReportID and cm.commentDeleteTime is NULL) as commentCount, " .
				"(select count(videoID) from brVideos as v where v.battleReportID = br.battlereportID) as footageCount ")) .
			"from brBattles as br " .
				($onlyIDs === true ? "" : ("inner join mapSolarSystems as sys " .
				"on br.solarSystemID = sys.solarSystemID " .
				"inner join brBattleParties as teamA " .
					"on br.battleReportID = teamA.battleReportID and teamA.brTeamName = 'teamA' " .
				"inner join brBattleParties as teamB " .
					"on br.battleReportID = teamB.battleReportID and teamB.brTeamName = 'teamB' " .
				"inner join brBattleParties as teamC " .
					"on br.battleReportID = teamC.battleReportID and teamC.brTeamName = 'teamC' " .
				"left outer join brUsers as u on u.userID = br.brCreatorUserID ")) .
			"where br.brDeleteTime is NULL " .
			($onlyPublished ? "and br.brPublished = 1 " : "") .
			($id !== null ? "and br.battleReportID = :brID " : "") .
			"order by br.brStartTime desc" .
			$limit,
			$params
		);
		
	}

	public static function getBattlesCount(array $options = array()) {

		$battlesCount = 0;

		$onlyPublished	= isset($options["onlyPublished"]) && is_bool($options["onlyPublished"]) ? $options["onlyPublished"] : true;

		$db = \Db::getInstance();

		$result = $db->single(
			"select count(battleReportID) as battlesCount " .
			"from brBattles " .
			"where brDeleteTime is NULL " .
			($onlyPublished ? "and brPublished = 1 " : "")
		);

		if ($result !== NULL && $result !== FALSE) {
			$battlesCount = $result;
		}

		return $battlesCount;

	}

	public static function updateStats($battleReportID = 0) {
		if ($battleReportID <= 0) {
			return false;
		}

		$db = \Db::getInstance();

		// ... and get its battle parties
		$battleParties = $db->query(
			'select * from brBattleParties where battleReportID = :battleReportID',
			array(
				"battleReportID" => $battleReportID
			)
		);

		// Oops, sth. went wrong
		if ($battleParties === FALSE) {
			return false;
		}

		$result = false;

		// ... and start over looping, this time through each battle party's memberlist
		foreach ($battleParties as $battleParty) {
			// Get the damage numbers, this battle party received ...
			$stats = $db->row(
				'select (' .
						'select ifNull(sum(brDamageDealt), 0) from brDamageComposition ' .
						'where brReceivingCombatantID in (' .
							'select brCombatantID from brCombatants ' .
							'where brBattlePartyID = :receivingBattlePartyID and brDeleted = 0 and brHidden = 0' .
						')' .
					') as dmgReceived, (' .
						'select ifNull(sum(brDamageDealt), 0) from brDamageComposition ' .
						'where brDealingCombatantID in (' .
							'select brCombatantID from brCombatants ' .
							'where brBattlePartyID = :dealingBattlePartyID and brDeleted = 0 and brHidden = 0' .
						')' .
					') as dmgDealt, (' .
						'select ifNull(sum(priceTag), 0) from brCombatants ' .
						'where brCombatantID in (' .
							'select brReceivingCombatantID from brDamageComposition ' .
							'where brDealingCombatantID in (' .
								'select brCombatantID from brCombatants ' .
								'where brBattlePartyID = :destroyingBattlePartyID and brDeleted = 0 and brHidden = 0' .
							')' .
						') and brDeleted = 0 and brBattlePartyID <> :excludedDestroyingBattlePartyID' .
					') as iskDestroyed, (' .
						'select ifNull(sum(priceTag), 0) from brCombatants ' .
						'where brBattlePartyID = :loosingBattlePartyID and brDeleted = 0 and brHidden = 0' .
					') as iskLost',
				array(
					"receivingBattlePartyID" => $battleParty["brBattlePartyID"],
					"dealingBattlePartyID" => $battleParty["brBattlePartyID"],
					"destroyingBattlePartyID" => $battleParty["brBattlePartyID"],
					"excludedDestroyingBattlePartyID" => $battleParty["brBattlePartyID"],
					"loosingBattlePartyID" => $battleParty["brBattlePartyID"]
				)
			);

			// Oh noes ...
			if ($stats === FALSE) {
				return false;
			}

			// UPDATE ALL THE STATS!
			$db->query(
				'update brBattleParties ' .
				'set brDamageDealt = :damageDealt, brDamageReceived = :damageReceived, ' .
					'brIskDestroyed = :iskDestroyed, brIskLost = :iskLost, ' .
					'brEfficiency = :efficiency ' .
				'where brBattlePartyID = :battlePartyID',
				array(
					"damageReceived" => $stats["dmgReceived"],
					"damageDealt" => $stats["dmgDealt"],
					"iskDestroyed" => $stats["iskDestroyed"],
					"iskLost" => $stats["iskLost"],
					"efficiency" => ($stats["iskDestroyed"] > 0 ? (100 * $stats["iskDestroyed"] / ($stats["iskDestroyed"] + $stats["iskLost"])) : 0.0),
					"battlePartyID" => $battleParty["brBattlePartyID"]
				)
			);

			$result = true;
		}

		return $result;
	}
	
	private static function getEmbedVideoUrl($url = "") {
		
		if (empty($url))
			return "";
		
		$matches = NULL;
		$pattern = "/" .
			"(" . // YouTube URLs (direct video link, embed link)
				"(http(s){0,1}:){0,1}(\/\/){0,1}(www.){0,1}(youtube\.com\/(embed\/|watch\?(.*?)v=)|youtu\.be\/)(?P<youTubeVideoID>[a-z0-9_-]{1,})" .
			"|" . // Vimeo URLs (direct video link, embed link)
				"(http(s){0,1}:){0,1}(\/\/){0,1}((www|player).){0,1}vimeo\.com\/(video\/){0,1}(?P<vimeoVideoID>[0-9]{1,})(.*?)" .
			")/i";
		
		// Neither pattern matches, exit
		if (preg_match($pattern, $url, $matches) != 1)
			return "";
		
		// Create EmbedUrl for YouTube video
		if (isset($matches["youTubeVideoID"]) && !empty($matches["youTubeVideoID"])) {
			return "//youtube.com/embed/" . $matches["youTubeVideoID"];
		}
		
		// Create EmbedUrl for Vimeo video
		if (isset($matches["vimeoVideoID"]) && !empty($matches["vimeoVideoID"])) {
			return "//player.vimeo.com/video/" . $matches["vimeoVideoID"] . "?title=0&amp;byline=0&amp;portrait=0: ";
		}
		
		// Something else does not fit ...
		return "";
		
	}
	
}
