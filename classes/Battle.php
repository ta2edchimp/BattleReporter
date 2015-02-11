<?php

class Battle {
    
    public $battleReportID = 0;
    
    public $title = "";
    
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
    
    
    public function __construct() {
        
		$this->creatorUserID = User::getUserID();
		$this->creatorUserName = User::getUserName();
		
        $this->teamA = new BattleParty("teamA");
        $this->teamB = new BattleParty("teamB");
        $this->teamC = new BattleParty("teamC");
        
    }
    
    
    public function load($id, $onlyPublished = true, $toBeEdited = false) {
        
        global $db;
        
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
        $this->startTime        = $result["brStartTime"];
        $this->endTime          = $result["brEndTime"];
        $this->solarSystemID    = $result["solarSystemID"];
        $this->published        = $result["brPublished"] == 1 ? true : false;
        
        // Sort battle parties
        $this->teamA->sort();
        $this->teamB->sort();
        $this->teamC->sort();
        
        // Update certain properties
        $this->updateDetails();
        return true;
        
    }
    
    
    public function save() {
        
        global $db;
        
		$values = array(
            "title" => $this->title,
            "startTime" => $this->startTime,
            "endTime" => $this->endTime,
            "solarSystemID" => $this->solarSystemID,
            "published" => $this->published ? 1 : 0
		);
		if ($this->deleted == true) {
			$values["brDeleteUserID"] = User::getUserID();
			$values["brDeleteTime"] = time();
		}
		
        // Save basic battle report properties
        if ($this->battleReportID <= 0) {
			$values["brCreatorUserID"] = $this->creatorUserID;
            $result = $db->query(
                "insert into brBattles ".
                "(brTitle, brStartTime, brEndTime, SolarSystemID, brPublished, brCreatorUserID" .
					($this->deleted ? ", brDeleteUserID, brDeleteTime" : "") .
				") " .
                "values " .
                "(:title, :startTime, :endTime, :solarSystemID, :published, :brCreatorUserID" .
					($this->deleted ? ", :brDeleteUserID, brDeleteTime" : "") .
				")",
                $values
            );
            if ($result != NULL)
                $this->battleReportID = $db->lastInsertId();
        } else {
			$values["battleReportID"] = $this->battleReportID;
            $result = $db->query(
                "update brBattles " .
                "set brTitle = :title, brStartTime = :startTime, brEndTime = :endTime, " .
					"SolarSystemID = :solarSystemID, brPublished = :published " .
					($this->deleted ? ", brDeleteUserID = :brDeleteUserID, brDeleteTime = :brDeleteTime " : "") .
                "where battleReportID = :battleReportID",
                $values
            );
        }
        
        // Save the battle parties
        $this->teamA->save($this->battleReportID);
        $this->teamB->save($this->battleReportID);
        $this->teamC->save($this->battleReportID);
        
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
        
        $this->teamA->updateDetails(array($this->teamB, $this->teamC));
        $this->teamB->updateDetails(array($this->teamA, $this->teamC));
        $this->teamC->updateDetails(array($this->teamA, $this->teamB));
        
        $this->killsTotal = $this->teamA->losses + $this->teamB->losses + $this->teamC->losses;
        
        if ($this->startTime > 0 && $this->endTime > 0)
            $this->timeSpan = date("Y-m-d H:i", $this->startTime) . " - " . date("H:i", $this->endTime);
        else
            $this->timeSpan = "";
        
        $this->totalPilots = $this->teamA->uniquePilots + $this->teamB->uniquePilots + $this->teamC->uniquePilots;
        $this->totalLost = $this->teamA->totalLost + $this->teamB->totalLost + $this->teamC->totalLost;
        
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
                if (isset($change->added) && $change->added == true
                    && isset($change->teamName) && !empty($change->teamName)
                    && isset($change->combatantInfo)
					&& (!isset($change->brDeleted) || $change->brDeleted != true)) {
                    
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
                    
                    if ($combatant != null)
                        $this->$currentTeam->members[] = $combatant;
                    
                }
            }
            
            if ($combatant == null)
                continue;
            
            if (isset($change->brHidden))
                $combatant->brHidden = $change->brHidden;
            
            if (isset($change->brDeleted))
                $combatant->brDeleted = $change->brDeleted;
        }
        
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
    
    
    public function import($importedKills) {
        
        if (count($importedKills) <= 0)
            return;
        
        $earliestKillTime = 0;
        $latestKillTime = 0;
        
        foreach ($importedKills as $impKill) {
            
            $existantBattleID = self::getBattleReportIDByKillID($impKill->killID);
            if ($existantBattleID != null)
                throw new Exception("The fetched events are already part of an existing BattleReport.");
            
            $kill = Kill::fromImport($impKill);
            
            if ($kill != null) {
                
                // Per default, the victim is member of teamB
                $tgt = "teamB";
                
                // Oh no, its a loss for the owner corp :(
                if ($kill->victim->corporationID == BR_OWNERCORP_ID)
                    $tgt = "teamA";
                
                $this->$tgt->add($kill->victim);
                
                foreach ($kill->attackers as $attacker) {
                    $tgt = "teamB";
                    if ($attacker->corporationID == BR_OWNERCORP_ID)
                        $tgt = "teamA";
                    
                    $this->$tgt->add($attacker);
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
        
        $this->startTime = $earliestKillTime;
        $this->endTime = $latestKillTime;
        
        $this->teamA->sort();
        $this->teamB->sort();
        $this->teamC->sort();
        
        $this->updateDetails();

    }
    
    
    public function toJSON() {
        return '{' .
            '"type":"battle",' .
            '"battleReportID":' . $this->battleReportID . ',' .
            '"title":"' . $this->title . '",' .
            '"killsTotal":' . $this->killsTotal . ',' .
            '"startTime":' . $this->startTime . ',' .
            '"endTime":' . $this->endTime . ',' .
            '"timeSpan":"' . $this->timeSpan . '",' .
            '"solarSystemName":"' . $this->solarSystemName . '",' .
            '"teamA":' . $this->teamA->toJSON() . ',' .
            '"teamB":' . $this->teamB->toJSON() . ',' .
            '"teamC":' . $this->teamC->toJSON() .
        '}';
    }
    
    
    public static function getBattleReportIDByKillID($killID = "") {
        if (empty($killID))
            return null;
        
        global $db;
        
        $id = $db->single(
            "select br.battleReportID " .
            "from brBattleParties as br inner join brCombatants as c " .
            "where c.killID = :killID and brDeleteTime is NULL " .
            "limit 1",
            array(
                "killID" => $killID
            )
        );
        
        return $id;
    }
    
    public static function timelineSorter($a, $b) {
        
        $combatantA = $a["combatantEventOccuredTo"];
        $combatantB = $b["combatantEventOccuredTo"];
        
        if ($combatantA->killTime == $combatantB->killTime)
            return Combatant::sorter($combatantA, $combatantB);
        
        return $combatantA->killTime < $combatantB->killTime ? -1 : 1;
    }
    
}